@extends('layouts.student')
@section('content')
<h1 class="text-xl font-semibold mb-4">Leave Requests</h1>
<a href="{{ route('student.leave.create') }}" class="mb-4 inline-block bg-emerald-600 text-white px-3 py-1 rounded">New Leave</a>
@foreach($leaves as $l)
<div class="bg-white rounded-2xl shadow p-4 mb-3">
    <div>{{ $l->from_date->format('d M') }} - {{ $l->to_date->format('d M') }}</div>
    <div class="text-xs text-slate-500">{{ $l->status }}</div>
</div>
@endforeach
@endsection