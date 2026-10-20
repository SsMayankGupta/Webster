@extends('layouts.student')
@section('content')
<h1 class="text-xl font-semibold mb-4">Request Leave</h1>
<form method="POST" action="{{ route('student.leave.store') }}" class="bg-white rounded-2xl shadow p-4 space-y-3">
    @csrf
    <div>
        <label class="block text-sm">From</label>
        <input type="date" name="from_date" class="w-full rounded border-slate-300" required>
    </div>
    <div>
        <label class="block text-sm">To</label>
        <input type="date" name="to_date" class="w-full rounded border-slate-300" required>
    </div>
    <button class="bg-emerald-600 text-white px-4 py-2 rounded">Submit</button>
</form>
@endsection