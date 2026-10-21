@extends('layouts.contractor')
@section('title', 'Meal Configuration - Admin')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Meals</h1>
<div class="bg-white rounded-2xl shadow p-4">
@foreach($menus as $m)
    <div class="py-2 border-b last:border-0">{{ $m->meal_type }} - {{ $m->serve_at }}</div>
@endforeach
</div>
@endsection
