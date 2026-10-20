@extends('layouts.contractor')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Tickets</h1>
<div class="bg-white rounded-2xl shadow p-4">
@foreach($tickets as $t)
    <div class="py-3 border-b last:border-0">
        <div class="font-medium">{{ $t->student->name ?? 'Student' }} - {{ $t->category }}</div>
        <div class="text-sm">{{ $t->text }}</div>
        <div class="text-xs text-slate-500 mb-2">{{ $t->status }}</div>
        <form method="POST" action="{{ route('contractor.tickets.status', $t) }}" class="flex gap-2">
            @csrf
            <select name="status" class="rounded border-slate-300 text-sm">
                <option value="pending">Pending</option>
                <option value="under_review">Under Review</option>
                <option value="resolved">Resolved</option>
            </select>
            <button class="bg-emerald-600 text-white px-3 py-1 rounded text-sm">Update</button>
        </form>
    </div>
@endforeach
</div>
@endsection