<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public string $mode = 'login';
    public string $name = '';
    public string $college_id = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function login()
    {
        $this->validate([
            'college_id' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('role', 'student')
            ->where(function ($q) {
                $q->where('college_id', $this->college_id)
                  ->orWhere('email', $this->college_id);
            })->first();

        if ($user && Hash::check($this->password, $user->password)) {
            Auth::guard('student')->login($user);
            return redirect()->route('student.dashboard');
        }

        $this->addError('college_id', 'Invalid credentials.');
    }

    public function register()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'college_id' => 'required|string|unique:users,college_id',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $this->name,
            'college_id' => $this->college_id,
            'email' => $this->email ?: null,
            'password' => Hash::make($this->password),
            'role' => 'student',
        ]);

        Auth::guard('student')->login($user);
        return redirect()->route('student.dashboard');
    }
};
?>

<div class="mx-auto max-w-md">
    <div class="card p-8">
        <div class="mb-6 text-center">
            <h1 class="font-display text-2xl font-bold">Student Portal</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sign in or create an account.</p>
        </div>

        <div class="mb-6 flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
            <button wire:click="$set('mode','login')" class="flex-1 rounded-lg py-2 text-sm font-medium transition {{ $mode === 'login' ? 'bg-white shadow text-slate-900 dark:bg-slate-700 dark:text-white' : 'text-slate-500' }}">Sign in</button>
            <button wire:click="$set('mode','register')" class="flex-1 rounded-lg py-2 text-sm font-medium transition {{ $mode === 'register' ? 'bg-white shadow text-slate-900 dark:bg-slate-700 dark:text-white' : 'text-slate-500' }}">Register</button>
        </div>

        @if ($mode === 'login')
            <form wire:submit="login" class="space-y-4">
                <div>
                    <label class="label">College ID or Email</label>
                    <input wire:model="college_id" class="input" required autofocus>
                    @error('college_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Password</label>
                    <input type="password" wire:model="password" class="input" required>
                </div>
                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Sign in</span>
                    <span wire:loading>Signing in...</span>
                </button>
            </form>
        @else
            <form wire:submit="register" class="space-y-4">
                <div>
                    <label class="label">Name</label>
                    <input wire:model="name" class="input" required>
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">College ID</label>
                    <input wire:model="college_id" class="input" required>
                    @error('college_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Email (optional)</label>
                    <input type="email" wire:model="email" class="input">
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Password</label>
                    <input type="password" wire:model="password" class="input" required>
                    @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Confirm Password</label>
                    <input type="password" wire:model="password_confirmation" class="input" required>
                </div>
                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Register</span>
                    <span wire:loading>Creating account...</span>
                </button>
            </form>
        @endif
    </div>
</div>
