@extends('layouts.contractor')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Waste Log</h1>
<form method="POST" action="{{ route('contractor.waste.store') }}" class="bg-white rounded-2xl shadow p-4 space-y-3 mb-6">
    @csrf
    <div>
        <label class="block text-sm">Weight (kg)</label>
        <input type="number" step="0.01" name="weight_kg" class="w-full rounded border-slate-300" required>
    </div>
    <div>
        <label class="block text-sm">Type</label>
        <select name="type" class="w-full rounded border-slate-300">
            <option value="compost">Compost</option>
            <option value="biogas">Biogas</option>
            <option value="donation">Donation</option>
            <option value="sale">Sale</option>
        </select>
    </div>
    <div>
        <label class="block text-sm">Earnings</label>
        <input type="number" step="0.01" name="earnings" class="w-full rounded border-slate-300">
    </div>
    <button class="bg-emerald-600 text-white px-4 py-2 rounded">Log</button>
</form>
<div class="bg-white rounded-2xl shadow p-4">
@foreach($transactions as $t)
    <div class="py-2 border-b last:border-0">{{ $t->type }} - {{ $t->weight_kg }}kg - ${{ $t->earnings }}</div>
@endforeach
</div>
@endsection