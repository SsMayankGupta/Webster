@extends('layouts.contractor')
@section('title', 'Contractor Sign In')
@section('content')
<div class="mx-auto max-w-md">
    <div class="card p-8">
        <div class="mb-6 text-center">
            <h1 class="font-display text-2xl font-bold">Contractor Sign In</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Manage menus, headcount, and waste analytics.</p>
        </div>
        <form method="POST" action="{{ route('contractor.login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label">College ID or Email</label>
                <input name="college_id" value="{{ old('college_id') }}" class="input" required autofocus>
            </div>
            <div>
                <label class="label">Password</label>
                <input type="password" name="password" class="input" required>
            </div>
            <button class="btn-primary w-full">Sign in</button>
        </form>
    </div>
</div>
@endsection
