<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\MealBooking;
use App\Models\MealRefund;
use App\Models\Menu;
use App\Models\ExtraMealCharge;
use App\Models\ExtraMealCollection;
use App\Models\RebateLedger;
use App\Models\StudentFeePayment;
use App\Models\User;
use Carbon\CarbonImmutable;

class PortalStateBuilder
{
    public function __construct(
        private readonly MealLifecycleService $lifecycle,
        private readonly MealBookingService $bookings,
        private readonly BillingService $billing,
        private readonly QrTokenService $qr,
        private readonly ApprovedLeaveService $approvedLeaves,
    ) {}

    /**
     * Everything the student portal needs for a zero-reload render.
     *
     * @return array<string, mixed>
     */
    public function build(User $student, ?string $billingMonth = null): array
    {
        $student->loadMissing([
            'contractor' => fn ($query) => $query
                ->select(
                    'id',
                    'name',
                    'email',
                    'phone',
                    'warden_id',
                    'semester_name',
                    'semester_starts_at',
                    'semester_ends_at',
                )
                ->where('role', 'contractor')
                ->whereNull('archived_at'),
            'contractor.warden' => fn ($query) => $query
                ->select('id', 'name', 'email', 'phone')
                ->where('role', 'warden')
                ->whereNull('archived_at'),
        ]);
        $contractor = $student->contractor;

        $now = CarbonImmutable::now(config('mess.display_timezone'));
        $broadcastConnection = (string) config('broadcasting.default');
        $broadcastConfig = config("broadcasting.connections.{$broadcastConnection}", []);
        $broadcastOptions = $broadcastConfig['options'] ?? [];
        $broadcastDriver = $broadcastConfig['driver'] ?? $broadcastConnection;
        $broadcastCluster = $broadcastOptions['cluster'] ?? null;
        $start = $now->startOfDay();
        $days = [];

        $menus = Menu::query()
            ->whereBetween('serve_at', [
                $start->copy()->utc(),
                $start->addDays(6)->endOfDay()->utc(),
            ])
            ->with('mealTimeSlot')
            ->orderBy('serve_at')
            ->get();

        $approvedLeaves = $this->approvedLeaves->rangesBetween(
            (int) $student->id,
            $start,
            $start->addDays(6)->endOfDay(),
        );

        $bookings = MealBooking::query()
            ->where('student_id', $student->id)
            ->whereIn('menu_id', $menus->pluck('id'))
            ->get()
            ->keyBy('menu_id');

        for ($offset = 0; $offset < 7; $offset++) {
            $date = $start->addDays($offset);

            $days[] = [
                'date' => $date->toDateString(),
                'day' => $date->format('D'),
                'label' => match ($offset) {
                    0 => 'Today',
                    1 => 'Tomorrow',
                    default => $date->format('l'),
                },
                'full_label' => $date->format('D, d M'),
                'is_today' => $offset === 0,
                'is_on_approved_leave' => $this->approvedLeaves->containsDate($approvedLeaves, $date),
                'meals' => $menus
                    ->filter(fn (Menu $menu): bool => $menu->serve_at->timezone(config('mess.display_timezone'))->toDateString() === $date->toDateString())
                    ->map(fn (Menu $menu): array => $this->meal(
                        $menu,
                        $bookings->get($menu->id),
                        $now,
                        $this->approvedLeaves->containsDate(
                            $approvedLeaves,
                            $menu->serve_at->timezone(config('mess.display_timezone'))->startOfDay(),
                        ),
                    ))
                    ->values()
                    ->all(),
            ];
        }

        $activeBooking = $this->bookings->activeBooking($student);

        return [
            'authenticated' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'college_id' => $student->college_id,
                'hostel' => $student->hostel,
                'dietary_tags' => array_values($student->dietary_tags ?? []),
            ],
            'help_contacts' => [
                'contractor' => $contractor === null ? null : [
                    'name' => $contractor->name,
                    'email' => $contractor->email,
                    'phone' => $contractor->phone,
                ],
                'warden' => $contractor?->warden === null ? null : [
                    'name' => $contractor->warden->name,
                    'email' => $contractor->warden->email,
                    'phone' => $contractor->warden->phone,
                ],
            ],
            'server_now' => $now->toIso8601String(),
            'cutoff_hours' => (int) config('mess.cutoff_hours'),
            'dietary_options' => collect(config('mess.dietary_tags'))
                ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'days' => $days,
            'today' => $days[0],
            'active_booking' => $activeBooking === null ? null : [
                'booking_id' => $activeBooking->id,
                'menu_id' => $activeBooking->menu_id,
                'meal_type' => $activeBooking->menu->meal_type,
                'serve_at' => $activeBooking->menu->serve_at->toIso8601String(),
                'serve_at_label' => $this->displayServiceWindow($activeBooking->menu),
                'lane' => $activeBooking->assigned_lane,
                'meal_status' => $this->lifecycle->stateFor($activeBooking->menu, $now),
                'can_check_in' => in_array($this->lifecycle->stateFor($activeBooking->menu, $now), ['locked', 'serving'], true),
            ],
            'leaves' => $this->leaveSummary($student),
            'billing' => $this->billing->statement($student, $billingMonth),
            'billing_months' => $this->billing->monthOptions(),
            'wallet' => $this->walletSummary($student, $contractor),
            'qr' => [
                'ttl_seconds' => (int) config('mess.qr.ttl_seconds'),
                'refresh_seconds' => (int) config('mess.qr.refresh_seconds'),
                'algorithm' => 'ES256',
                'public_key_url' => route('portal.qr.public-key'),
                'fingerprint' => $this->qr->publicKeyFingerprint(),
            ],
            'broadcasting' => [
                'key' => $broadcastConfig['key'] ?? null,
                'host' => $broadcastDriver === 'pusher' && filled($broadcastCluster)
                    ? "ws-{$broadcastCluster}.pusher.com"
                    : ($broadcastOptions['host'] ?? null),
                'port' => (int) ($broadcastDriver === 'pusher' ? 443 : ($broadcastOptions['port'] ?? 8080)),
                'scheme' => $broadcastDriver === 'pusher'
                    ? 'https'
                    : ($broadcastOptions['scheme'] ?? 'http'),
                'private_channel' => 'private-App.Models.User.'.$student->id,
                'public_channel' => 'meals',
            ],
            'csrf_token' => csrf_token(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function meal(Menu $menu, ?MealBooking $booking, CarbonImmutable $now, bool $isOnApprovedLeave): array
    {
        $status = $this->lifecycle->stateFor($menu, $now);
        $cutoff = $menu->cutoffAt();
        $open = $now->lt($cutoff);
        $isConsumed = $booking?->status === 'consumed';
        $optedIn = $booking !== null && in_array($booking->status, ['booked', 'consumed'], true);

        return [
            'id' => $menu->id,
            'meal_type' => $menu->meal_type,
            'meal_label' => ucfirst($menu->meal_type),
            'serve_at' => $menu->serve_at->toIso8601String(),
            'serve_timestamp' => $menu->serve_at->getTimestamp() * 1000,
            'serve_at_label' => $this->displayServiceWindow($menu),
            'cutoff_at' => $cutoff->toIso8601String(),
            'cutoff_timestamp' => $cutoff->getTimestamp() * 1000,
            'cutoff_label' => $cutoff->timezone(config('mess.display_timezone'))->format('D, d M g:i A'),
            'cutoff_hours' => (int) (
                $menu->mealTimeSlot?->cutoff_hours_before
                    ?? config("mess.default_meal_times.{$menu->meal_type}.cutoff_hours_before", config('mess.cutoff_hours'))
            ),
            'status' => $status,
            'status_label' => ucfirst($status),
            'is_open' => $open && $status === 'open',
            'items' => $menu->menuItems(),
            'calories' => (float) $menu->calories,
            'protein' => (float) $menu->protein,
            'carbs' => (float) $menu->carbs,
            'booking' => $booking === null ? null : [
                'id' => $booking->id,
                'status' => $booking->status,
            ],
            'is_consumed' => $isConsumed,
            'is_on_approved_leave' => $isOnApprovedLeave,
            'opted_in' => $optedIn,
            'lane' => $booking?->assigned_lane,
            'can_manage' => ! $isConsumed && ! $isOnApprovedLeave && $open && $status !== 'closed',
            'can_cancel' => ! $isConsumed && ! $isOnApprovedLeave && $open && $status !== 'closed' && $optedIn,
        ];
    }

    private function displayServiceWindow(Menu $menu): string
    {
        $startAt = $menu->serve_at->copy()->timezone(config('mess.display_timezone'));
        $endTime = $menu->mealTimeSlot?->end_time
            ?? config("mess.default_meal_times.{$menu->meal_type}.end_time");
        $endAt = $endTime === null
            ? $startAt->copy()->addHours(3)
            : $startAt->copy()->setTimeFromTimeString($endTime);

        return $startAt->format('D, d M · g:i A').' – '.$endAt->format('g:i A');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function leaveSummary(User $student): array
    {
        $leaves = LeaveRequest::query()
            ->where('student_id', $student->id)
            ->with(['rebates' => fn ($query) => $query->orderBy('created_at')])
            ->latest()
            ->limit(12)
            ->get();
        $approvedRanges = LeaveRequest::query()
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->get(['from_date', 'to_date']);

        return $leaves
            ->map(function (LeaveRequest $leave) use ($approvedRanges): array {
                $days = (int) $leave->from_date->diffInDays($leave->to_date) + 1;
                $rebatePaise = $leave->rebates->sum(
                    fn (RebateLedger $rebate): int => (int) ($rebate->amount_paise
                        ?? round(((float) $rebate->amount) * 100))
                );
                $estimatedRebatePaise = $leave->status === 'pending'
                    ? $this->approvedLeaves->uncreditedDays($leave, $approvedRanges)
                        * (int) config('mess.rebate_per_day_paise')
                    : 0;

                return [
                    'id' => $leave->id,
                    'from_date' => $leave->from_date->toDateString(),
                    'to_date' => $leave->to_date->toDateString(),
                    'from_label' => $leave->from_date->format('d M Y'),
                    'to_label' => $leave->to_date->format('d M Y'),
                    'days' => $days,
                    'reason' => $leave->reason,
                    'status' => $leave->status,
                    'rebate_paise' => $rebatePaise,
                    'estimated_rebate_paise' => $estimatedRebatePaise,
                    'created_at' => $leave->created_at->toDateString(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function walletSummary(User $student, ?User $contractor): array
    {
        $semesterRange = $contractor?->semester_starts_at !== null && $contractor->semester_ends_at !== null
            ? [
                $contractor->semester_starts_at->copy()->startOfDay(),
                $contractor->semester_ends_at->copy()->endOfDay(),
            ]
            : null;
        $feeQuery = $student->feePayments();
        $refundQuery = $student->mealRefunds();
        $rebateQuery = $student->rebateLedgers();
        $extraMealChargeQuery = $student->extraMealCharges();
        $extraMealCollectionQuery = $student->extraMealCollections();

        if ($semesterRange !== null) {
            $feeQuery->whereBetween('created_at', $semesterRange);
            $refundQuery->whereBetween('created_at', $semesterRange);
            $rebateQuery->whereBetween('created_at', $semesterRange);
            $extraMealChargeQuery->whereBetween('created_at', $semesterRange);
            $extraMealCollectionQuery->whereBetween('created_at', $semesterRange);
        }

        $feePayments = $student->feePayments()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();
        $refunds = $student->mealRefunds()
            ->with('leaveRequest:id,from_date,to_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();
        $extraMealCharges = $student->extraMealCharges()
            ->with('booking.menu:id,meal_type,serve_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();
        $extraMealCollections = $student->extraMealCollections()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();
        $leaveRebatePaise = (clone $rebateQuery)->get()->sum(
            fn (RebateLedger $rebate): int => (int) ($rebate->amount_paise
                ?? round(((float) $rebate->amount) * 100))
        );
        $periodLabel = $semesterRange === null
            ? 'All time · semester dates not set'
            : (($contractor->semester_name ?: 'Current semester').' · '
                .$contractor->semester_starts_at->format('d M Y').' – '
                .$contractor->semester_ends_at->format('d M Y'));

        return [
            'period_label' => $periodLabel,
            'period_start' => $semesterRange === null ? null : $semesterRange[0]->toDateString(),
            'period_end' => $semesterRange === null ? null : $semesterRange[1]->toDateString(),
            'fee_paid_paise' => (int) $feeQuery->sum('amount_paise'),
            'fee_payments' => $feePayments->map(fn (StudentFeePayment $payment): array => [
                'id' => $payment->id,
                'amount_paise' => $payment->amount_paise,
                'date' => $payment->created_at->format('d M Y'),
            ])->values()->all(),
            'meal_refund_paise' => (int) $refundQuery->sum('amount_paise'),
            'transferred_refund_paise' => (int) (clone $refundQuery)
                ->where('status', 'transferred')
                ->sum('amount_paise'),
            'pending_refund_paise' => (int) (clone $refundQuery)
                ->where('status', 'pending')
                ->sum('amount_paise'),
            'refunds' => $refunds->map(fn (MealRefund $refund): array => [
                'id' => $refund->id,
                'amount_paise' => $refund->amount_paise,
                'status' => $refund->status,
                'date' => $refund->created_at->format('d M Y'),
                'leave_from' => $refund->leaveRequest?->from_date?->format('d M Y'),
                'leave_to' => $refund->leaveRequest?->to_date?->format('d M Y'),
                'transferred_at' => $refund->transferred_at?->format('d M Y'),
            ])->values()->all(),
            'approved_leave_rebate_paise' => (int) $leaveRebatePaise,
            'extra_meal_charged_paise' => (int) $extraMealChargeQuery->sum('amount_paise'),
            'extra_meal_collected_paise' => (int) $extraMealCollectionQuery->sum('amount_paise'),
            'extra_meal_outstanding_paise' => max(
                0,
                (int) (clone $extraMealChargeQuery)->sum('amount_paise')
                    - (int) (clone $extraMealCollectionQuery)->sum('amount_paise'),
            ),
            'extra_meal_charges' => $extraMealCharges->map(fn (ExtraMealCharge $charge): array => [
                'id' => $charge->id,
                'amount_paise' => $charge->amount_paise,
                'guest_count' => $charge->guest_count,
                'price_per_guest_paise' => $charge->price_per_guest_paise,
                'meal_type' => $charge->booking?->menu?->meal_type,
                'date' => $charge->created_at->format('d M Y'),
            ])->values()->all(),
            'extra_meal_collections' => $extraMealCollections->map(fn (ExtraMealCollection $collection): array => [
                'id' => $collection->id,
                'amount_paise' => $collection->amount_paise,
                'date' => $collection->created_at->format('d M Y'),
            ])->values()->all(),
        ];
    }
}
