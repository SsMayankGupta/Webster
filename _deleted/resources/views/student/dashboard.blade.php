@extends('layouts.student')
@section('title', 'Student Dashboard - Meal Overview')
@section('content')
<div class="page-header">
    <h1 class="page-title">Welcome back, {{ $student->name }} 👋</h1>
    <p class="page-subtitle">Here's your dining overview for today.</p>
</div>

<div class="grid grid-cols-2 gap-3 mb-5">
    <div class="card p-4">
        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Upcoming meals</div>
        <div class="mt-1 text-2xl font-bold text-brand-600">{{ $upcoming->count() }}</div>
    </div>
    <div class="card p-4">
        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">My bookings</div>
        <div class="mt-1 text-2xl font-bold text-secondary-500">{{ $bookings->count() }}</div>
    </div>
</div>

<div class="card p-5 mb-5">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="font-display font-semibold">Upcoming Meals</h2>
        <a href="{{ route('student.menus') }}" class="text-xs font-medium text-brand-600">View all →</a>
    </div>
    <div class="space-y-3">
        @forelse($upcoming as $m)
            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-3 dark:border-slate-800 dark:bg-slate-800/40">
                <div>
                    <div class="font-medium">{{ ucfirst($m->meal_type) }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $m->serve_at->format('D, d M · H:i') }}</div>
                </div>
                <span class="badge-info">{{ $m->calories }} kcal</span>
            </div>
        @empty
            <div class="py-6 text-center text-sm text-slate-400">No upcoming meals.</div>
        @endforelse
    </div>
</div>

<div class="card p-5">
    <h2 class="mb-3 font-display font-semibold">My Bookings</h2>
    <div class="space-y-3">
        @forelse($bookings as $b)
            <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                <div>
                    <div class="font-medium">{{ ucfirst($b->menu->meal_type) }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $b->menu->serve_at->format('d M · H:i') }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge {{ $b->status === 'booked' ? 'badge-info' : ($b->status === 'consumed' ? 'badge-success' : 'badge-warning') }}">{{ $b->status }}</span>
                    @if($b->status === 'booked')
                        <form method="POST" action="{{ route('student.bookings.cancel', $b) }}">
                            @csrf
                            <button class="btn-danger !px-2.5 !py-1.5 text-xs">Cancel</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-6 text-center text-sm text-slate-400">No bookings yet.</div>
        @endforelse
    </div>
</div>
@endsection