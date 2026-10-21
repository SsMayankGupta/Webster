@extends('layouts.student')
@section('title', 'Raise Ticket - Student')
@section('content')
<h1 class="text-xl font-semibold mb-4">Raise Ticket</h1>
<form method="POST" action="{{ route('student.tickets.store') }}" class="bg-white rounded-2xl shadow p-4 space-y-3">
    @csrf
    <div>
        <label class="block text-sm">Category</label>
        <select name="category" class="w-full rounded border-slate-300">
            <option value="food_quality">Food Quality</option>
            <option value="hygiene">Hygiene</option>
            <option value="service">Service</option>
            <option value="other">Other</option>
        </select>
    </div>
    <div>
        <label class="block text-sm">Description</label>
        <textarea name="text" class="w-full rounded border-slate-300" required></textarea>
    </div>
    <button class="bg-emerald-600 text-white px-4 py-2 rounded">Submit</button>
</form>
@endsection
