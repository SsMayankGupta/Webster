@extends('layouts.student')
@section('title', 'My QR Token - Meal Check-in')
@section('content')
<h1 class="text-xl font-semibold mb-4">My QR Token</h1>
<div class="bg-white rounded-2xl shadow p-6 text-center">
    <div id="qr-text" class="font-mono text-xs break-all mb-4">Loading...</div>
    <div id="countdown" class="text-sm text-slate-500"></div>
</div>
<script>
async function refreshToken() {
    const res = await fetch('{{ route('student.qr.token') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
    const data = await res.json();
    document.getElementById('qr-text').textContent = data.token || data.error;
}
refreshToken();
setInterval(refreshToken, 60000);
</script>
@endsection