@extends('layouts.contractor')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Counter Scanner</h1>
<div class="bg-white rounded-2xl shadow p-4 mb-4">
    <div id="reader" style="width:300px"></div>
</div>
<div class="bg-white rounded-2xl shadow p-4">
    <h2 class="font-medium mb-2">Manual Redeem</h2>
    <input id="jti" placeholder="jti" class="w-full rounded border-slate-300 mb-2">
    <button onclick="redeem()" class="bg-emerald-600 text-white px-4 py-2 rounded">Redeem</button>
    <pre id="out" class="mt-2 text-xs"></pre>
</div>
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
async function redeem(jtiOverride) {
    const jti = jtiOverride || document.getElementById('jti').value;
    const res = await fetch('{{ route('api.scans.redeem') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ jti, device_id: 'counter-1' })
    });
    const data = await res.json();
    document.getElementById('out').textContent = JSON.stringify(data);
}
</script>
@endsection