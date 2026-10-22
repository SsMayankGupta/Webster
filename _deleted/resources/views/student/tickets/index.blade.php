@extends('layouts.student')
@section('title', 'My Tickets - Complaints & Feedback')
@section('content')
<h1 class="text-xl font-semibold mb-4">My Tickets</h1>
<a href="{{ route('student.tickets.create') }}" class="mb-4 inline-block bg-emerald-600 text-white px-3 py-1 rounded">New Ticket</a>
@foreach($tickets as $t)
<div class="bg-white rounded-2xl shadow p-4 mb-3">
    <div class="font-medium">{{ $t->category }}</div>
    <div class="text-sm">{{ $t->text }}</div>
    <div class="text-xs text-slate-500">{{ $t->status }}</div>
</div>
@endforeach
@endsection