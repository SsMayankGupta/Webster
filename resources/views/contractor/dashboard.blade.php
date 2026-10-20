@extends('layouts.contractor')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Dashboard</h1>
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl shadow p-4">
        <div class="text-sm text-slate-500">Today's Headcount</div>
        <div class="text-2xl font-bold">{{ $headcount }}</div>
    </div>
</div>
<div class="mt-6 bg-white rounded-2xl shadow p-4">
    <h2 class="font-medium mb-2">Today's Menus</h2>
    @foreach($todayMenus as $m)
        <div>{{ ucfirst($m->meal_type) }} - {{ $m->serve_at }} ({{ $m->status }})</div>
    @endforeach
</div>
@endsection