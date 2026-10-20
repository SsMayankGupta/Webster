@extends('layouts.student')
@section('content')
<h1 class="text-xl font-semibold mb-4">Menus</h1>
@foreach($menus as $m)
<div class="bg-white rounded-2xl shadow p-4 mb-3">
    <div class="font-medium">{{ ucfirst($m->meal_type) }} - {{ $m->serve_at->format('d M H:i') }}</div>
    <div class="text-sm text-slate-500">P: {{ $m->protein }}g | C: {{ $m->carbs }}g | {{ $m->calories }} kcal</div>
    <div class="text-xs text-slate-400 mt-1">{{ $m->recipe_text }}</div>
</div>
@endforeach
@endsection