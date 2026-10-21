@extends('layouts.contractor')
@section('title', 'Dashboard')
@section('content')
<div class="page-header">
    <h1 class="page-title">Operations Dashboard</h1>
    <p class="page-subtitle">Real-time headcount, menus, and waste overview.</p>
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="card p-5">
        <div class="flex items-center justify-between">
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Today's headcount</div>
            <span class="badge-success">Live</span>
        </div>
        <div class="mt-2 text-3xl font-bold text-brand-600">{{ $headcount }}</div>
        <div class="mt-1 text-xs text-slate-400">Locked diners for today</div>
    </div>
    <div class="card p-5">
        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Today's menus</div>
        <div class="mt-2 text-3xl font-bold text-secondary-500">{{ $todayMenus->count() }}</div>
        <div class="mt-1 text-xs text-slate-400">Meal services scheduled</div>
    </div>
    <div class="card p-5">
        <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Recent waste logs</div>
        <div class="mt-2 text-3xl font-bold text-accent-500">{{ $waste->count() }}</div>
        <div class="mt-1 text-xs text-slate-400">Transactions recorded</div>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="card p-5">
        <h2 class="mb-3 font-display font-semibold">Today's Menus</h2>
        <div class="space-y-2">
            @forelse($todayMenus as $m)
                <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                    <div>
                        <div class="font-medium">{{ ucfirst($m->meal_type) }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $m->serve_at->format('H:i') }} · {{ $m->calories }} kcal</div>
                    </div>
                    <span class="badge {{ $m->status === 'open' ? 'badge-info' : ($m->status === 'locked' ? 'badge-warning' : 'badge-success') }}">{{ $m->status }}</span>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-slate-400">No menus scheduled today.</div>
            @endforelse
        </div>
    </div>
    <div class="card p-5">
        <h2 class="mb-3 font-display font-semibold">Recent Waste</h2>
        <div class="space-y-2">
            @forelse($waste as $w)
                <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                    <div>
                        <div class="font-medium">{{ ucfirst($w->type) }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $w->logged_at->format('d M · H:i') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold">{{ $w->weight_kg }} kg</div>
                        <div class="text-xs text-slate-400">₹{{ $w->earnings }}</div>
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-slate-400">No waste logged yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection