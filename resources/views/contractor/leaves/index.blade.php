@extends('layouts.contractor')
@section('title', 'Leave Approvals - Contractor')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Leave Approvals</h1>
<div class="bg-white rounded-2xl shadow p-4">
@foreach($leaves as $l)
    <div class="py-3 border-b last:border-0">
        <div>{{ $l->student->name ?? 'Student' }}: {{ $l->from_date->format('d M') }} - {{ $l->to_date->format('d M') }}</div>
        <div class="text-xs text-slate-500 mb-2">{{ $l->status }}</div>
        @if($l->status === 'pending')
        <div class="flex gap-2">
            <form method="POST" action="{{ route('contractor.leaves.approve', $l) }}">@csrf<button class="bg-emerald-600 text-white px-3 py-1 rounded text-sm">Approve</button></form>
            <form method="POST" action="{{ route('contractor.leaves.reject', $l) }}">@csrf<button class="bg-red-600 text-white px-3 py-1 rounded text-sm">Reject</button></form>
        </div>
        @endif
    </div>
@endforeach
</div>
@endsection
