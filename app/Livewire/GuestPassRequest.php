<?php

namespace App\Livewire;

use App\Events\GuestPassRequested;
use App\Models\MealBooking;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class GuestPassRequest extends Component
{
    public int $menuId = 0;

    public int $guestCount = 1;

    public int $estimatedFee = 0;

    public ?string $errorMessage = null;

    public function updatedMenuId(): void
    {
        $this->calculateFee();
    }

    public function updatedGuestCount(): void
    {
        $this->calculateFee();
    }

    private function calculateFee(): void
    {
        if ($this->menuId === 0) {
            $this->estimatedFee = 0;

            return;
        }

        $menu = Menu::find($this->menuId);

        if ($menu === null) {
            $this->estimatedFee = 0;

            return;
        }

        $baseFeePerGuest = config('mess.guest_fee_per_meal_paise', 15000);
        $this->estimatedFee = $baseFeePerGuest * $this->guestCount;
    }

    public function submit(): void
    {
        $this->errorMessage = null;

        $student = Auth::guard('student')->user();

        if ($student === null) {
            $this->errorMessage = 'Authentication required.';

            return;
        }

        $this->validate([
            'menuId' => ['required', 'exists:menus,id'],
            'guestCount' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $menu = Menu::query()->findOrFail($this->menuId);

        $lockAt = $menu->serve_at->subHours((int) config('mess.cutoff_hours'));

        $isPastCutoff = DB::selectOne(
            'SELECT NOW() > ? as is_past',
            [$lockAt]
        )->is_past;

        if ($isPastCutoff) {
            $this->errorMessage = 'Guest pass requests are no longer accepted for this meal (cutoff reached).';

            return;
        }

        try {
            DB::transaction(function () use ($student, $menu) {
                $booking = MealBooking::query()->create([
                    'student_id' => $student->id,
                    'menu_id' => $menu->id,
                    'status' => 'PENDING_APPROVAL',
                    'is_guest_pass' => true,
                    'primary_student_id' => $student->id,
                    'guest_count' => $this->guestCount,
                ]);

                broadcast(new GuestPassRequested($booking));
            });

            $this->menuId = 0;
            $this->guestCount = 1;
            $this->estimatedFee = 0;

            $this->dispatch('portal-toast', message: 'Guest pass request submitted for approval.', type: 'success');
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to submit guest pass request. Please try again.';
        }
    }

    #[On('echo:guest-queue,GuestPassApproved')]
    public function onGuestPassApproved(array $event): void
    {
        $student = Auth::guard('student')->user();

        if ($student === null || $event['student_id'] !== $student->id) {
            return;
        }

        $this->dispatch('portal-toast', message: 'Your guest pass has been approved! QR code available.', type: 'success');
        $this->dispatch('guest-pass-approved', bookingId: $event['booking_id']);
    }

    public function render()
    {
        $menus = Menu::query()
            ->where('serve_at', '>', now())
            ->orderBy('serve_at')
            ->get();

        return view('livewire.guest-pass-request', [
            'menus' => $menus,
        ]);
    }
}
