<?php

namespace App\Livewire;

use App\Events\GuestPassApproved;
use App\Models\ExtraMealCharge;
use App\Models\HeadcountLedger;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Guest Meal Passes')]
class ContractorGuestQueue extends Component
{
    public array $pendingRequests = [];

    public array $guestPrices = [];

    public function mount(): void
    {
        $this->loadPendingRequests();
    }

    #[On('echo:guest-queue,GuestPassRequested')]
    public function onGuestPassRequested(): void
    {
        $this->loadPendingRequests();
    }

    public function approve(int $bookingId): void
    {
        $contractor = $this->contractor();
        $this->pendingRequest($contractor, $bookingId);
        $validated = $this->validate([
            "guestPrices.{$bookingId}" => [
                'required',
                'numeric',
                'min:0',
                'max:10000000',
                'regex:/^\d+(?:\.\d{1,2})?$/',
            ],
        ]);
        $unitPricePaise = $this->amountToPaise($validated['guestPrices'][$bookingId]);

        $booking = DB::transaction(function () use ($contractor, $bookingId, $unitPricePaise): MealBooking {
            $booking = MealBooking::query()
                ->whereKey($bookingId)
                ->where('is_guest_pass', true)
                ->where('status', 'pending_approval')
                ->whereHas('student', fn ($query) => $query
                    ->where('contractor_id', $contractor->id)
                    ->whereNull('archived_at'))
                ->with(['menu.mealTimeSlot', 'student'])
                ->lockForUpdate()
                ->firstOrFail();

            $menu = Menu::query()
                ->whereKey($booking->menu_id)
                ->with('mealTimeSlot')
                ->lockForUpdate()
                ->firstOrFail();

            if ($menu->status !== 'open' || ! $menu->isOpenAt()) {
                throw ValidationException::withMessages([
                    "guestPrices.{$bookingId}" => 'This meal has reached its cutoff and cannot be approved.',
                ]);
            }

            User::query()->whereKey($booking->student_id)->lockForUpdate()->firstOrFail();
            $booking->update(['status' => 'booked']);

            ExtraMealCharge::query()->create([
                'booking_id' => $booking->id,
                'student_id' => $booking->student_id,
                'approved_by' => $contractor->id,
                'guest_count' => $booking->guest_count,
                'price_per_guest_paise' => $unitPricePaise,
                'amount_paise' => $unitPricePaise * $booking->guest_count,
            ]);

            $guestCount = (int) MealBooking::query()
                ->where('menu_id', $booking->menu_id)
                ->where('is_guest_pass', true)
                ->where('status', 'booked')
                ->sum('guest_count');
            $studentCount = (int) MealBooking::query()
                ->where('menu_id', $booking->menu_id)
                ->where('is_guest_pass', false)
                ->where('status', 'booked')
                ->count();

            HeadcountLedger::query()->create([
                'menu_id' => $booking->menu_id,
                'opt_in_count' => $studentCount,
                'locked_count' => $studentCount + $guestCount,
                'guest_count' => $guestCount,
                'locked_at' => now(),
            ]);

            $booking->update(['assigned_lane' => $this->assignLane($booking->menu_id)]);

            return $booking->fresh(['menu', 'student']);
        });

        broadcast(new GuestPassApproved($booking, (string) $booking->assigned_lane));
        $this->loadPendingRequests();
        $this->dispatch('portal-toast', message: 'Guest pass approved and the extra-meal charge recorded.', type: 'success');
    }

    public function reject(int $bookingId): void
    {
        $contractor = $this->contractor();
        $this->pendingRequest($contractor, $bookingId)->update(['status' => 'cancelled']);

        $this->loadPendingRequests();
        $this->dispatch('portal-toast', message: 'Guest pass rejected.', type: 'warning');
    }

    public function render()
    {
        return view('livewire.contractor-guest-queue');
    }

    private function loadPendingRequests(): void
    {
        $contractor = $this->contractor();
        $requests = MealBooking::query()
            ->with(['student', 'menu'])
            ->where('is_guest_pass', true)
            ->where('status', 'pending_approval')
            ->whereHas('student', fn ($query) => $query
                ->where('contractor_id', $contractor->id)
                ->whereNull('archived_at'))
            ->orderBy('created_at')
            ->get();

        $this->pendingRequests = $requests
            ->map(function (MealBooking $booking): array {
                return [
                    'id' => $booking->id,
                    'student_name' => $booking->student->name,
                    'student_id' => $booking->student->college_id,
                    'meal_type' => $booking->menu->meal_type,
                    'serve_at' => $booking->menu->serve_at
                        ->timezone(config('mess.display_timezone'))
                        ->format('M j, g:i A'),
                    'guest_count' => $booking->guest_count,
                    'created_at' => $booking->created_at->format('M j, g:i A'),
                ];
            })
            ->values()
            ->all();

        $pendingIds = array_column($this->pendingRequests, 'id');
        $this->guestPrices = array_intersect_key($this->guestPrices, array_fill_keys($pendingIds, true));

        foreach ($this->pendingRequests as $request) {
            $this->guestPrices[$request['id']] ??= number_format(
                config('mess.guest_fee_per_meal_paise', 15000) / 100,
                2,
                '.',
                '',
            );
        }
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

        $leastUsedLane = 'A';
        $leastUsedCount = PHP_INT_MAX;

        foreach ($lanes as $lane) {
            $count = $currentCount[$lane] ?? 0;
            if ($count < $leastUsedCount) {
                $leastUsedCount = $count;
                $leastUsedLane = $lane;
            }
        }

        return $leastUsedLane;
    }

    private function contractor(): User
    {
        $contractor = Auth::guard('contractor')->user();
        abort_unless($contractor instanceof User && $contractor->role === 'contractor', 403);

        return $contractor;
    }

    private function pendingRequest(User $contractor, int $bookingId): MealBooking
    {
        return MealBooking::query()
            ->whereKey($bookingId)
            ->where('is_guest_pass', true)
            ->where('status', 'pending_approval')
            ->whereHas('student', fn ($query) => $query
                ->where('contractor_id', $contractor->id)
                ->whereNull('archived_at'))
            ->firstOrFail();
    }

    private function amountToPaise(string $amount): int
    {
        [$rupees, $paise] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $rupees * 100) + (int) str_pad($paise, 2, '0');
    }
}
