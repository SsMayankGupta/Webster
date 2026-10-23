@extends('layouts.contractor')
@section('title', 'Counter Scanner - QR Redemption')

@section('content')
<div class="font-lora">
    <div class="mx-auto max-w-4xl space-y-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 dark:border-slate-800 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Daily operations</p>
                <h2 class="mt-2 text-2xl font-medium tracking-tight text-slate-900 dark:text-white">Counter scanner</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Scan a rotating ES256 token or enter a signed token or legacy ID.</p>
            </div>
            <div class="w-full sm:max-w-xs">
                <label for="lane" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Counter lane</label>
                <select id="lane" class="input">
                @foreach ($lanes as $lane)
                    <option value="{{ $lane }}">Lane {{ $lane }}</option>
                @endforeach
                </select>
            </div>
        </header>

        <section aria-labelledby="camera-scan-heading" class="border-t border-slate-300 pt-4 dark:border-slate-700">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 id="camera-scan-heading" class="text-xl font-medium text-slate-900 dark:text-white">Camera scan</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Point the camera at the student’s check-in QR.</p>
                </div>
                <span id="offlineStatus" role="status" class="border-l-2 border-slate-300 pl-3 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    Offline verifier idle
                </span>
            </div>
            <div class="mt-5 flex min-h-64 items-center justify-center overflow-hidden border-y border-slate-200 bg-slate-50 py-4 dark:border-slate-800 dark:bg-slate-900/40">
                <div id="reader" class="mx-auto max-w-full" style="width:320px"></div>
            </div>
        </section>

        <section aria-labelledby="manual-redeem-heading" class="border-t border-slate-300 pt-4 dark:border-slate-700">
            <div>
                <h3 id="manual-redeem-heading" class="text-xl font-medium text-slate-900 dark:text-white">Manual redemption</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Use this if the camera is unavailable. Tokens are verified before redemption.</p>
            </div>
            <div class="mt-4 space-y-3">
                <label for="token" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Signed token or legacy ID</label>
                <textarea id="token" rows="3" placeholder="Paste the signed JWT here, or use a legacy jti" class="input font-mono text-xs"></textarea>
                <div class="flex flex-wrap gap-2">
                    <button onclick="redeem()" class="btn-primary">Verify &amp; redeem</button>
                    <button onclick="document.getElementById('token').value=''; document.getElementById('out').textContent=''" class="btn-ghost border border-slate-200 dark:border-slate-700">Clear</button>
                </div>
                <pre id="out" role="status" aria-live="polite" class="overflow-x-auto border-l-2 border-slate-300 bg-slate-50 p-4 font-mono text-xs leading-5 text-slate-700 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">Awaiting a scan…</pre>
            </div>
        </section>
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
                status.className = 'border-l-2 border-emerald-600 pl-3 text-sm text-emerald-700 dark:text-emerald-400';

                if (claims.lane && String(claims.lane).toUpperCase() !== lane.toUpperCase()) {
                    output('WRONG LANE — token is pinned to lane ' + claims.lane + '. Switch the counter lane and rescan.');

                    return;
                }
            } catch (error) {
                status.textContent = 'Offline verify failed: ' + error.message;
                status.className = 'border-l-2 border-rose-600 pl-3 text-sm text-rose-700 dark:text-rose-400';
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
