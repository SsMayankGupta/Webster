@extends('layouts.contractor')
@section('title', 'Menu Builder - AI Macro Analyzer')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Menu Builder</h1>
<form method="POST" action="{{ route('contractor.menu-builder.store') }}" class="bg-white rounded-2xl shadow p-4 space-y-3 mb-6">
    @csrf
    <div>
        <label class="block text-sm">Meal Type</label>
        <select name="meal_type" class="w-full rounded border-slate-300">
            <option value="breakfast">Breakfast</option>
            <option value="lunch">Lunch</option>
            <option value="snacks">Snacks</option>
            <option value="dinner">Dinner</option>
        </select>
    </div>
    <div>
        <label class="block text-sm">Serve At</label>
        <input type="datetime-local" name="serve_at" class="w-full rounded border-slate-300" required>
    </div>
    <div>
        <label class="block text-sm">Recipe Text</label>
        <textarea name="recipe_text" class="w-full rounded border-slate-300" required></textarea>
    </div>
    <button class="bg-emerald-600 text-white px-4 py-2 rounded">Create Menu</button>
</form>
<div class="bg-white rounded-2xl shadow p-4">
    <h2 class="font-medium mb-2">Recent Menus</h2>
    @foreach($menus as $m)
        <div class="py-2 border-b last:border-0">
            {{ ucfirst($m->meal_type) }} - {{ $m->serve_at }} | P:{{ $m->protein }} C:{{ $m->carbs }} K:{{ $m->calories }}
        </div>
    @endforeach
</div>
@endsection