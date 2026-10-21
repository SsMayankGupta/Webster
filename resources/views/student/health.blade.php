@extends('layouts.student')
@section('title', 'Health Hub - Nutrition & Weight Tracking')
@section('content')
<h1 class="text-xl font-semibold mb-4">Health Hub</h1>
<div class="bg-white rounded-2xl shadow p-4 mb-3">
    <div>Protein: {{ $totalProtein }}g</div>
    <div>Carbs: {{ $totalCarbs }}g</div>
    <div>Calories: {{ $totalCalories }}</div>
</div>
<div class="bg-white rounded-2xl shadow p-4">
    <h2 class="font-medium mb-2">Weight Log</h2>
    <pre class="text-xs">{{ json_encode($weightLog) }}</pre>
</div>
@endsection