@extends('layouts.contractor')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Headcount</h1>
<div class="bg-white rounded-2xl shadow p-4">
@foreach($menus as $m)
    <div class="py-2 border-b last:border-0">
        {{ ucfirst($m->meal_type) }} - {{ $m->serve_at }} | Status: {{ $m->status }} | Locked: {{ $m->headcount_locked }}
    </div>
@endforeach
</div>
@endsection