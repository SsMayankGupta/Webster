@extends('layouts.contractor')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Users</h1>
<div class="bg-white rounded-2xl shadow p-4">
@foreach($users as $u)
    <div class="py-2 border-b last:border-0">{{ $u->name }} ({{ $u->college_id }}) - {{ $u->role }}</div>
@endforeach
</div>
@endsection