<?php

namespace App\Livewire;

use App\Events\GuestPassApproved;
use App\Models\HeadcountLedger;
use App\Models\MealBooking;
use App\Models\RebateLedger;
use App\Services\QrTokenService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ContractorGuestQueue extends Component
{
    public array $pendingRequests = [];

    public function mount(): void
    {
        $this->loadPendingRequests();
    }

    #[On('echo:guest-queue,GuestPassRequested')]
    public function onGuestPassRequested(): void
    {
        $this->loadPendingRequests();
    }

    private function loadPendingRequests(): void
    {
        $this->pendingRequests = MealBooking::query()
            ->with(['student', 'menu'])
            ->where('is_guest_pass', true)
            ->where('status', 'pending_approval')
            ->orderBy('created_at')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'student_name' => $booking->student->name,
                    'student_id' => $booking->student->college_id,
                    'meal_type' => $booking->menu->meal_type,
                    'serve_at' => $booking->menu->serve_at->format('M j, g:i A'),
                    'guest_count' => $booking->guest_count,
                    'created_at' => $booking->created_at->format('M j, g:i A'),
                ];
            })
            ->toArray();
    }

    public function approve(int $bookingId): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        try {
            DB::transaction(function () use ($bookingId) {
                $booking = MealBooking::query()
                    ->with(['menu', 'student'])
                    ->where('id', $bookingId)
                    ->where('status', 'pending_approval')
                    ->lockForUpdate()
                    ->first();

                if ($booking === null) {
                    throw new \RuntimeException('Booking not found or already processed.');
                }

                $booking->update(['status' => 'booked']);

                $headcountLedger = HeadcountLedger::query()
                    ->where('menu_id', $booking->menu_id)
                    ->firstOrCreate(['menu_id' => $booking->menu_id]);

                $headcountLedger->increment('locked_count', $booking->guest_count);

                $guestFeePerMeal = config('mess.guest_fee_per_meal_paise', 15000);
                $totalFee = $guestFeePerMeal * $booking->guest_count;

                RebateLedger::query()->create([
                    'student_id' => $booking->student_id,
                    'leave_request_id' => null,
                    'amount' => $totalFee / 100,
                    'amount_paise' => $totalFee,
                    'reason' => 'Guest pass for '.$booking->menu->meal_type.' on '.$booking->menu->serve_at->format('Y-m-d'),
                ]);

                $lane = $this->assignLane($booking->menu_id);
                $booking->update(['assigned_lane' => $lane]);

                app(QrTokenService::class)->issue($booking);

                broadcast(new GuestPassApproved($booking, $lane));
            });

            $this->loadPendingRequests();
            $this->dispatch('portal-toast', message: 'Guest pass approved successfully.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('portal-toast', message: 'Failed to approve guest pass: '.$e->getMessage(), type: 'error');
        }
    }

    public function reject(int $bookingId): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        MealBooking::query()
            ->where('id', $bookingId)
            ->where('status', 'pending_approval')
            ->update(['status' => 'cancelled']);

        $this->loadPendingRequests();
        $this->dispatch('portal-toast', message: 'Guest pass rejected.', type: 'warning');
    }

    private function assignLane(int $menuId): string
    {
        $lanes = config('mess.lanes', ['A', 'B', 'C', 'D']);
        $currentCount = MealBooking::query()
            ->where('menu_id', $menuId)
            ->whereNotNull('assigned_lane')
            ->selectRaw('assigned_lane, COUNT(*) as count')
            ->groupBy('assigned_lane')
            ->get()
            ->pluck('count', 'assigned_lane')
            ->toArray();

        $minLane = 'A';
        $minCount = PHP_INT_MAX;

        foreach ($lanes as $lane) {
            $count = $currentCount[$lane] ?? 0;
            if ($count < $minCount) {
                $minCount = $count;
                $minLane = $lane;
            }
        }

        return $minLane;
    }

    public function render()
    {
        return view('livewire.contractor-guest-queue');
    }
}
