@extends('layouts.student')
@section('content')
<h1 class="text-xl font-semibold mb-4">Hello, {{ $student->name }}</h1>
<div class="bg-white rounded-2xl shadow p-4 mb-4">
    <h2 class="font-medium mb-2">Upcoming Meals</h2>
    @foreach($upcoming as $m)
        <div class="flex justify-between py-2 border-b last:border-0">
            <span>{{ ucfirst($m->meal_type) }} - {{ $m->serve_at->format('d M H:i') }}</span>
            <span class="text-xs text-slate-500">{{ $m->calories }} kcal</span>
        </div>
    @endforeach
</div>
<div class="bg-white rounded-2xl shadow p-4">
    <h2 class="font-medium mb-2">My Bookings</h2>
    @foreach($bookings as $b)
        <div class="flex justify-between py-2 border-b last:border-0">
            <span>{{ ucfirst($b->menu->meal_type) }} ({{ $b->status }})</span>
            @if($b->status === 'booked')
            <form method="POST" action="{{ route('student.bookings.cancel', $b) }}">
                @csrf
                <button class="text-red-600 text-xs">Cancel</button>
            </form>
            @endif
        </div>
    @endforeach
</div>
@endsection