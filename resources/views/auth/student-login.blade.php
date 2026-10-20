@extends('layouts.student')
@section('content')
<div class="bg-white rounded-2xl shadow p-6">
    <h1 class="text-xl font-semibold mb-4">Student Login</h1>
    <form method="POST" action="{{ route('student.login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">College ID</label>
            <input name="college_id" value="{{ old('college_id') }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Password</label>
            <input type="password" name="password" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <button class="w-full bg-emerald-600 text-white rounded-lg py-2 font-medium">Sign in</button>
    </form>
</div>
@endsection