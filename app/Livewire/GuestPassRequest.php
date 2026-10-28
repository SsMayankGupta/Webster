<?php

namespace App\Livewire;

use App\Events\GuestPassApproved;
use App\Events\GuestPassRequested;
use App\Exceptions\RedemptionRejectedException;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\User;
use App\Services\QrTokenService;
use App\Support\SafeBroadcast;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class GuestPassRequest extends Component
{
    public array $selectedMenus = [];

    public array $guestCounts = [];

    public array $guestPasses = [];

    #[Locked]
    public array $guestPassTokens = [];

    #[Locked]
    public array $guestPassErrors = [];

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->loadPasses();
    }

    public function submit(): void
    {
        $student = $this->student();
        $this->errorMessage = null;

        $rules = [
            'selectedMenus' => ['required', 'array', 'min:1'],
            'selectedMenus.*' => ['required', 'integer', 'distinct', 'exists:menus,id'],
            'guestCounts' => ['required', 'array'],
        ];

        foreach ($this->selectedMenus as $menuId) {
            $rules["guestCounts.{$menuId}"] = ['required', 'integer', 'min:1', 'max:5'];
        }

        $validated = $this->validate($rules);
        $menuIds = array_map('intval', $validated['selectedMenus']);
        $guestCounts = array_map('intval', array_intersect_key(
            $validated['guestCounts'],
            array_fill_keys(array_map('strval', $menuIds), true),
        ));

        if (count($guestCounts) !== count($menuIds)) {
            throw ValidationException::withMessages([
                'guestCounts' => 'Choose a guest count for every selected meal.',
            ]);
        }

        $bookings = DB::transaction(function () use ($student, $menuIds, $guestCounts): array {
            User::query()
                ->whereKey($student->id)
                ->where('role', 'student')
                ->whereNull('archived_at')
                ->lockForUpdate()
                ->firstOrFail();

            $menus = Menu::query()
                ->whereIn('id', $menuIds)
                ->with('mealTimeSlot')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($menus->count() !== count($menuIds)) {
                throw ValidationException::withMessages([
                    'selectedMenus' => 'One or more selected meals are no longer available.',
                ]);
            }

            $now = CarbonImmutable::now();
            $bookings = [];

            foreach ($menuIds as $menuId) {
                $menu = $menus->get($menuId);

                if ($menu->status !== 'open' || $menu->serve_at->lte($now) || ! $menu->isOpenAt($now)) {
                    throw ValidationException::withMessages([
                        'selectedMenus' => 'A selected meal has reached its booking cutoff or is no longer available.',
                    ]);
                }

                $existingPass = MealBooking::query()
                    ->where('primary_student_id', $student->id)
                    ->where('menu_id', $menu->id)
                    ->where('is_guest_pass', true)
                    ->whereIn('status', ['pending_approval', 'booked'])
                    ->exists();

                if ($existingPass) {
                    throw ValidationException::withMessages([
                        'selectedMenus' => 'A guest pass has already been requested for one of the selected meals.',
                    ]);
                }

                $bookings[] = MealBooking::query()->create([
                    'student_id' => $student->id,
                    'menu_id' => $menu->id,
                    'status' => 'pending_approval',
                    'is_guest_pass' => true,
                    'primary_student_id' => $student->id,
                    'guest_count' => $guestCounts[$menu->id],
                ]);
            }

            return $bookings;
        });

        foreach ($bookings as $booking) {
            SafeBroadcast::send(new GuestPassRequested($booking));
        }

        $this->selectedMenus = [];
        $this->errorMessage = null;
        $this->loadPasses();
        $this->dispatch('portal-toast', message: 'Guest pass requests submitted for contractor approval.', type: 'success');
    }

    public function refreshPassToken(int $bookingId): void
    {
        $student = $this->student();
        $this->guestPassErrors = [];

        $booking = MealBooking::query()
            ->whereKey($bookingId)
            ->where('student_id', $student->id)
            ->where('primary_student_id', $student->id)
            ->where('is_guest_pass', true)
            ->with('menu')
            ->firstOrFail();

        if ($booking->status !== 'booked') {
            $this->guestPassErrors[$bookingId] = 'This guest pass is not approved or is no longer active.';

            return;
        }

        try {
            $this->guestPassTokens[$bookingId] = app(QrTokenService::class)->issue($booking);
        } catch (RedemptionRejectedException $exception) {
            $this->guestPassTokens[$bookingId] = [];
            $this->guestPassErrors[$bookingId] = $exception->getMessage();
        }
    }

    #[On('guest-pass-approved')]
    public function onGuestPassApproved(array $event): void
    {
        if ((int) ($event['student_id'] ?? 0) !== $this->student()->id) {
            return;
        }

        $this->loadPasses();
        $this->dispatch('portal-toast', message: 'A guest pass was approved. Its QR is available when check-in opens.', type: 'success');
    }

    private function loadPasses(): void
    {
        $student = $this->student();

        $this->guestPassTokens = [];
        $this->guestPassErrors = [];
        $this->guestPasses = MealBooking::query()
            ->where('primary_student_id', $student->id)
            ->where('is_guest_pass', true)
            ->with(['menu.mealTimeSlot', 'extraMealCharge'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn (MealBooking $booking): array => [
                'id' => $booking->id,
                'meal_type' => $booking->menu->meal_type,
                'serve_at' => $booking->menu->serve_at
                    ->timezone(config('mess.display_timezone'))
                    ->format('D, d M g:i A'),
                'guest_count' => $booking->guest_count,
                'status' => $booking->status,
                'charge_paise' => $booking->extraMealCharge?->amount_paise,
                'menu_status' => $booking->menu->status,
            ])
            ->all();
    }

    private function student(): User
    {
        $student = Auth::guard('student')->user();
        abort_unless(
            $student instanceof User
                && $student->isStudent()
                && $student->archived_at === null
                && ! $student->must_change_password,
            403,
        );

        return $student;
    }

    public function render()
    {
        $menus = Menu::query()
            ->where('serve_at', '>', now())
            ->where('status', 'open')
            ->with('mealTimeSlot')
            ->orderBy('serve_at')
            ->get()
            ->filter(fn (Menu $menu): bool => $menu->isOpenAt())
            ->values();

        return view('livewire.guest-pass-request', [
            'menus' => $menus,
            'estimatePerGuestPaise' => (int) config('mess.guest_fee_per_meal_paise', 15000),
        ]);
    }
}
