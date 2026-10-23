@extends('layouts.contractor')
@section('title', 'Counter Scanner - QR Redemption')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Counter Scanner</h1>
            <p class="text-sm text-slate-500">Scan a rotating ES256 token or paste a raw token / legacy jti.</p>
        </div>
        <div>
            <label for="lane" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Counter lane</label>
            <select id="lane" class="mt-1 rounded-lg border-slate-300 text-sm">
                @foreach ($lanes as $lane)
                    <option value="{{ $lane }}">Lane {{ $lane }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-4 rounded-2xl bg-white p-4 shadow">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="font-medium">Camera scan</h2>
            <span id="offlineStatus" class="rounded-full border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                Offline verifier idle
            </span>
        </div>
        <div id="reader" class="mx-auto" style="width:320px"></div>
    </div>

    <div class="rounded-2xl bg-white p-4 shadow">
        <h2 class="mb-2 font-medium">Manual redeem</h2>
        <textarea id="token" rows="3" placeholder="Paste the signed JWT here, or use a legacy jti" class="mb-2 w-full rounded-lg border-slate-300 font-mono text-xs"></textarea>
        <div class="flex flex-wrap gap-2">
            <button onclick="redeem()" class="rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white">Verify &amp; redeem</button>
            <button onclick="document.getElementById('token').value=''; document.getElementById('out').textContent=''" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-600">Clear</button>
        </div>
        <pre id="out" class="mt-3 overflow-x-auto rounded-lg bg-slate-950 p-3 text-xs text-emerald-300">Awaiting a scan…</pre>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
    const scannerConfig = @js([
        'redeemUrl' => route('api.scans.redeem'),
        'publicKeyUrl' => route('portal.qr.public-key'),
        'issuer' => config('mess.qr.issuer'),
        'audience' => config('mess.qr.audience'),
        'device' => 'counter-web',
    ]);

    let lastScan = '';
    let lastScanAt = 0;

    function output(message) {
        document.getElementById('out').textContent = message;
    }

    function base64UrlToBytes(value) {
        let normalised = value.replace(/-/g, '+').replace(/_/g, '/');
        const padding = normalised.length % 4 === 0 ? '' : '='.repeat(4 - (normalised.length % 4));
        const raw = atob(normalised + padding);
        const bytes = new Uint8Array(raw.length);

        for (let index = 0; index < raw.length; index++) {
            bytes[index] = raw.charCodeAt(index);
        }

        return bytes;
    }

    function pemToBuffer(pem) {
        const base64 = pem.replace(/-----[^-]+-----/g, '').replace(/\s+/g, '');
        const raw = atob(base64);
        const bytes = new Uint8Array(raw.length);

        for (let index = 0; index < raw.length; index++) {
            bytes[index] = raw.charCodeAt(index);
        }

        return bytes.buffer;
    }

    async function publicKeyPem() {
        try {
            const response = await fetch(scannerConfig.publicKeyUrl, { headers: { Accept: 'application/x-pem-file' } });

            if (!response.ok) {
                throw new Error('Public key fetch failed');
            }

            const pem = await response.text();
            localStorage.setItem('mess.qr.publicKey', pem);

            return pem;
        } catch (error) {
            const cached = localStorage.getItem('mess.qr.publicKey');

            if (!cached) {
                throw error;
            }

            return cached;
        }
    }

    async function verifyOffline(jwt) {
        const parts = jwt.split('.');

        if (parts.length !== 3) {
            throw new Error('Malformed token');
        }

        const header = JSON.parse(new TextDecoder().decode(base64UrlToBytes(parts[0])));

        if (header.alg !== 'ES256') {
            throw new Error('Unexpected algorithm: ' + header.alg);
        }

        const key = await crypto.subtle.importKey(
            'spki',
            pemToBuffer(await publicKeyPem()),
            { name: 'ECDSA', namedCurve: 'P-256' },
            false,
            ['verify'],
        );

        const valid = await crypto.subtle.verify(
            { name: 'ECDSA', hash: 'SHA-256' },
            key,
            base64UrlToBytes(parts[2]),
            new TextEncoder().encode(parts[0] + '.' + parts[1]),
        );

        if (!valid) {
            throw new Error('Signature verification failed');
        }

        const payload = JSON.parse(new TextDecoder().decode(base64UrlToBytes(parts[1])));

        if (payload.iss !== scannerConfig.issuer || payload.aud !== scannerConfig.audience) {
            throw new Error('Token was issued for another system');
        }

        if (Number(payload.exp) * 1000 < Date.now()) {
            throw new Error('Token expired');
        }

        return payload;
    }

    async function redeem(tokenOverride) {
        const token = (tokenOverride || document.getElementById('token').value).trim();
        const lane = document.getElementById('lane').value;

        if (!token) {
            output('Enter or scan a token first.');

            return;
        }

        const isJwt = token.split('.').length === 3;
        const status = document.getElementById('offlineStatus');

        if (isJwt) {
            try {
                const claims = await verifyOffline(token);
                status.textContent = 'Offline verified · lane ' + claims.lane + ' · ' + new Date(claims.exp * 1000).toLocaleTimeString();
                status.className = 'rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700';

                if (claims.lane && String(claims.lane).toUpperCase() !== lane.toUpperCase()) {
                    output('WRONG LANE — token is pinned to lane ' + claims.lane + '. Switch the counter lane and rescan.');

                    return;
                }
            } catch (error) {
                status.textContent = 'Offline verify failed: ' + error.message;
                status.className = 'rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700';
                output('Offline verification failed: ' + error.message);

                return;
            }
        }

        const payload = { lane, device_id: scannerConfig.device };

        if (isJwt) {
            payload.token = token;
        } else {
            payload.jti = token;
        }

        const response = await fetch(scannerConfig.redeemUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();
        output((response.ok ? '✓ ' : '✕ ') + JSON.stringify(data, null, 2));
    }

    window.addEventListener('load', () => {
        if (typeof Html5Qrcode === 'undefined') {
            return;
        }

        const scanner = new Html5Qrcode('reader');

        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: 260 },
            (decoded) => {
                const now = Date.now();

                if (decoded === lastScan && now - lastScanAt < 4000) {
                    return;
                }

                lastScan = decoded;
                lastScanAt = now;
                document.getElementById('token').value = decoded;
                redeem(decoded);
            },
            () => {},
        ).catch(() => {
            output('Camera unavailable — use the manual field below.');
        });
    });
</script>
@endsection
