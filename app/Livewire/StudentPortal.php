<?php

namespace App\Livewire;

use App\Exceptions\MealCutoffReachedException;
use App\Exceptions\RedemptionRejectedException;
use App\Models\LeaveRequest;
use App\Models\Menu;
use App\Models\User;
use App\Services\MealBookingService;
use App\Services\PortalStateBuilder;
use App\Services\QrTokenService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.portal')]
#[Title('Zero WasteMess.io · Student Portal')]
class StudentPortal extends Component
{
    #[Locked]
    public bool $isAuthenticated = false;

    #[Locked]
    public array $state = [];

    public string $activeTab = 'meals';

    public bool $showAuth = false;

    public string $authMode = 'login';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $resetToken = '';

    public bool $remember = false;

    public bool $showPassword = false;

    public ?string $emailStatus = null;

    public ?string $authMessage = null;

    #[Locked]
    public ?string $qrSvg = null;

    #[Locked]
    public ?string $qrToken = null;

    #[Locked]
    public ?string $qrExpiresAt = null;

    #[Locked]
    public ?string $qrLane = null;

    #[Locked]
    public ?string $qrError = null;

    public string $billingMonth = '';

    public string $leaveFrom = '';

    public string $leaveTo = '';

    public string $leaveReason = '';

    /** @var list<string> */
    public array $selectedDietaryTags = [];

    public function mount(): void
    {
        $this->billingMonth = now()->format('Y-m');

        $student = $this->currentStudent();

        if ($student !== null) {
            $this->isAuthenticated = true;
            $this->loadPortalState($student);
        }

        if (request()->filled('reset_token')) {
            $this->authMode = 'reset';
            $this->showAuth = true;
            $this->resetToken = (string) request()->query('reset_token');
            $this->email = (string) request()->query('email', '');
        }
    }

    public function render()
    {
        return view('livewire.student-portal');
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['meals', 'qr', 'leave', 'profile'], true)) {
            return;
        }

        $this->activeTab = $tab;

        if ($tab === 'qr') {
            $this->refreshQrToken();
        }
    }

    public function openAuth(string $mode = 'login'): void
    {
        $this->authMode = in_array($mode, ['login', 'forgot', 'reset'], true) ? $mode : 'login';
        $this->showAuth = true;
        $this->authMessage = null;
        $this->resetErrorBag(['email', 'password', 'login']);
    }

    public function closeAuth(): void
    {
        $this->showAuth = false;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag(['email', 'password', 'login']);
    }

    public function setAuthMode(string $mode): void
    {
        if (! in_array($mode, ['login', 'forgot', 'reset'], true)) {
            return;
        }

        $this->authMode = $mode;
        $this->authMessage = null;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag(['email', 'password', 'login']);
    }

    public function updatedEmail(): void
    {
        $this->emailStatus = null;
        $this->resetErrorBag('email');
    }

    public function validateStudentEmail(): void
    {
        $this->emailStatus = null;

        $identifier = trim($this->email);

        if ($identifier === '') {
            $this->resetErrorBag('email');

            return;
        }

        $student = $this->findStudentByIdentifier($identifier);

        if ($student === null) {
            $this->addError(
                'email',
                'This email / College ID is not registered as an active mess student. Please contact your hostel admin.',
            );

            return;
        }

        $this->resetErrorBag('email');
        $this->emailStatus = 'Registered student found. Enter your password to continue.';
    }

    public function login(): void
    {
        $this->authMessage = null;

        $this->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($this->email);
        $student = $this->findStudentByIdentifier($identifier);

        if ($student === null) {
            $this->addError('email', 'This email / College ID is not registered as an active mess student.');

            return;
        }

        $throttleKey = 'portal-login:'.Str::lower($identifier).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('password', "Too many attempts. Please try again in {$seconds} seconds.");

            return;
        }

        if (! Hash::check($this->password, $student->password)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('password', 'These credentials do not match our records.');

            return;
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('student')->login($student, $this->remember);
        Auth::guard('web')->login($student, $this->remember);

        session()->regenerate();

        $this->password = '';
        $this->showAuth = false;
        $this->isAuthenticated = true;
        $this->activeTab = 'meals';
        $this->loadPortalState($student);

        $this->dispatch('portal-authenticated', broadcasting: $this->state['broadcasting']);
        $this->dispatch('portal-toast', message: 'Welcome back, '.$student->name.'.', type: 'success');
    }

    public function logout(): void
    {
        Auth::guard('student')->logout();
        Auth::guard('web')->logout();

        session()->regenerate();

        $this->isAuthenticated = false;
        $this->state = [];
        $this->activeTab = 'meals';
        $this->qrSvg = null;
        $this->qrToken = null;
        $this->qrExpiresAt = null;
        $this->qrError = null;
        $this->qrLane = null;
        $this->authMode = 'login';
        $this->email = '';
        $this->emailStatus = null;

        $this->dispatch('portal-logged-out');
    }

    public function sendPasswordResetLink(): void
    {
        $this->authMessage = null;

        $this->validate([
            'email' => ['required', 'string', 'max:255'],
        ]);

        $student = $this->findStudentByIdentifier(trim($this->email));

        if ($student === null || blank($student->email)) {
            $this->addError('email', 'No student account matches this email / College ID.');

            return;
        }

        $status = Password::sendResetLink(['email' => $student->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->authMessage = 'A verification link has been sent to your college email. Open it to set a new password.';

            return;
        }

        $this->addError('email', __($status));
    }

    public function resetPassword(): void
    {
        $this->authMessage = null;

        $this->validate([
            'resetToken' => ['required', 'string'],
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker()->reset(
            [
                'email' => trim($this->email),
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->resetToken,
            ],
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->password = '';
            $this->password_confirmation = '';
            $this->authMode = 'login';
            $this->authMessage = 'Password updated. Sign in with your new password.';

            return;
        }

        throw ValidationException::withMessages(['password' => __($status)]);
    }

    public function confirmMeal(int $menuId, bool $optIn = true, ?string $dietary = null): void
    {
        $student = $this->requireStudent();
        $menu = Menu::query()->findOrFail($menuId);

        if ($dietary !== null && ! array_key_exists($dietary, config('mess.dietary_tags'))) {
            $dietary = null;
        }

        try {
            app(MealBookingService::class)->confirm($student, $menu, $optIn, $dietary);
        } catch (MealCutoffReachedException $exception) {
            $this->addError('meal-'.$menuId, $exception->getMessage());
            $this->loadPortalState($student);
            $this->dispatch('meal-cutoff-reached', menuId: $menuId);

            return;
        }

        $this->loadPortalState($student);

        $this->dispatch(
            'portal-toast',
            message: $optIn ? 'Meal confirmed. The kitchen headcount is updated.' : 'Meal cancelled. Thank you for cutting waste.',
            type: $optIn ? 'success' : 'warning',
        );
    }

    public function cancelMeal(int $menuId): void
    {
        $this->confirmMeal($menuId, false);
    }

    public function saveDietaryTags(): void
    {
        $student = $this->requireStudent();
        $allowed = array_keys((array) config('mess.dietary_tags'));
        $tags = array_values(array_intersect($this->selectedDietaryTags, $allowed));

        $student->update(['dietary_tags' => $tags]);

        $this->loadPortalState($student);
        $this->dispatch('portal-toast', message: 'Dietary preferences saved.', type: 'success');
    }

    public function requestLeave(): void
    {
        $student = $this->requireStudent();

        $validated = $this->validate([
            'leaveFrom' => ['required', 'date', 'after_or_equal:today'],
            'leaveTo' => ['required', 'date', 'after_or_equal:leaveFrom'],
            'leaveReason' => ['nullable', 'string', 'max:255'],
        ]);

        LeaveRequest::query()->create([
            'student_id' => $student->id,
            'from_date' => $validated['leaveFrom'],
            'to_date' => $validated['leaveTo'],
            'reason' => $validated['leaveReason'] ?: null,
            'status' => 'pending',
        ]);

        $this->leaveFrom = '';
        $this->leaveTo = '';
        $this->leaveReason = '';

        $this->loadPortalState($student);
        $this->dispatch('portal-toast', message: 'Leave request submitted for approval.', type: 'success');
    }

    public function updatedBillingMonth(): void
    {
        $student = $this->currentStudent();

        if ($student !== null) {
            $this->loadPortalState($student);
        }
    }

    public function refreshQrToken(): void
    {
        $student = $this->currentStudent();

        if ($student === null) {
            return;
        }

        $booking = app(MealBookingService::class)->activeBooking($student);

        if ($booking === null) {
            $this->qrError = 'No active meal booking. Confirm your next meal to unlock check-in.';
            $this->qrSvg = null;
            $this->qrToken = null;
            $this->qrExpiresAt = null;
            $this->qrLane = null;

            return;
        }

        try {
            $token = app(QrTokenService::class)->issue($booking);

            $this->qrSvg = $token['svg'];
            $this->qrToken = $token['jwt'];
            $this->qrExpiresAt = $token['expires_at'];
            $this->qrLane = $token['lane'];
            $this->qrError = null;
        } catch (RedemptionRejectedException $exception) {
            $this->qrSvg = null;
            $this->qrToken = null;
            $this->qrExpiresAt = null;
            $this->qrLane = $booking->assigned_lane;
            $this->qrError = $exception->getMessage();
        }
    }

    #[On('meal-status')]
    public function onMealStatus(): void
    {
        $student = $this->currentStudent();

        if ($student !== null) {
            $this->loadPortalState($student);
        }
    }

    #[On('cutoff-warning')]
    public function onCutoffWarning(string $message = ''): void
    {
        $this->dispatch(
            'portal-toast',
            message: $message !== '' ? $message : 'A meal cutoff is approaching. Confirm or cancel now.',
            type: 'warning',
        );
    }

    #[On('qr-redeemed')]
    public function onQrRedeemed(): void
    {
        $student = $this->currentStudent();

        if ($student !== null) {
            $this->loadPortalState($student);
        }

        $this->qrSvg = null;
        $this->qrToken = null;
        $this->qrExpiresAt = null;
        $this->qrError = 'Meal checked in at the counter. Enjoy your meal!';

        $this->dispatch('portal-toast', message: 'Check-in confirmed at the counter.', type: 'success');
    }

    private function loadPortalState(User $student): void
    {
        $this->state = app(PortalStateBuilder::class)->build($student, $this->billingMonth ?: null);
        $this->selectedDietaryTags = array_values($student->dietary_tags ?? []);
        $this->isAuthenticated = true;
    }

    private function currentStudent(): ?User
    {
        $student = Auth::guard('student')->user();

        if ($student instanceof User && $student->isStudent()) {
            return $student;
        }

        $user = Auth::user();

        return $user instanceof User && $user->isStudent() ? $user : null;
    }

    private function requireStudent(): User
    {
        $student = $this->currentStudent();

        abort_if($student === null, 403, 'Student authentication required.');

        return $student;
    }

    private function findStudentByIdentifier(string $identifier): ?User
    {
        return User::query()
            ->where('role', 'student')
            ->where(function ($query) use ($identifier): void {
                $query->where('email', $identifier)
                    ->orWhere('college_id', $identifier);
            })
            ->first();
    }
}
