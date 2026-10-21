@extends('layouts.contractor')
@section('title', 'Staff Directory - Contractor')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Staff Directory</h1>
<div class="bg-white rounded-2xl shadow p-4">
@foreach($staff as $s)
    <div class="py-2 border-b last:border-0">{{ $s->name }} ({{ $s->college_id }})</div>
@endforeach
</div>
@endsection
