// ZeroWasteMess Service Worker
importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.0.0/workbox-sw.js');

if (workbox) {
    workbox.precaching.precacheAndRoute(self.__WB_MANIFEST || []);
    workbox.routing.registerRoute(
        ({request}) => request.mode === 'navigate',
        new workbox.strategies.NetworkFirst({ cacheName: 'pages' })
    );
}

const DB_NAME = 'zwm-offline';
const STORE = 'scans';

function openDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE)) {
                db.createObjectStore(STORE, { keyPath: 'seq', autoIncrement: true });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function hmacSign(payload, secret) {
    const enc = new TextEncoder();
    const key = await crypto.subtle.importKey('raw', enc.encode(secret), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']);
    const sig = await crypto.subtle.sign('HMAC', key, enc.encode(payload));
    return Array.from(new Uint8Array(sig)).map(b => b.toString(16).padStart(2, '0')).join('');
}

self.addEventListener('message', async (event) => {
    if (event.data && event.data.type === 'QUEUE_SCAN') {
        const scan = event.data.scan;
        const db = await openDB();
        const tx = db.transaction(STORE, 'readwrite');
        const store = tx.objectStore(STORE);
        const seq = await new Promise((res, rej) => {
            const r = store.add(scan);
            r.onsuccess = () => res(r.result);
            r.onerror = () => rej(r.error);
        });
        // sign
        const scannedAt = scan.scanned_at;
        const payload = `${scan.jti}|${scan.device_id}|${scannedAt}|${seq}`;
        const hmac = await hmacSign(payload, event.data.secret || 'dev-secret');
        const tx2 = db.transaction(STORE, 'readwrite');
        const s2 = tx2.objectStore(STORE);
        const rec = await new Promise((res, rej) => {
            const r = s2.get(seq);
            r.onsuccess = () => res(r.result);
            r.onerror = () => rej(r.error);
        });
        rec.hmac = hmac;
        rec.seq = seq;
        s2.put(rec);
        event.source.postMessage({ type: 'QUEUED', seq });
    }
});

async function syncScans() {
    const db = await openDB();
    const tx = db.transaction(STORE, 'readonly');
    const store = tx.objectStore(STORE);
    const all = await new Promise((res, rej) => {
        const r = store.getAll();
        r.onsuccess = () => res(r.result);
        r.onerror = () => rej(r.error);
    });
    if (!all.length) return;
    const res = await fetch('/api/v1/scans/batch-verify', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (self.registration && '') || '' },
        body: JSON.stringify({ scans: all }),
        credentials: 'include',
    });
    if (res.ok) {
        const txw = db.transaction(STORE, 'readwrite');
        const sw = txw.objectStore(STORE);
        all.forEach((s) => sw.delete(s.seq));
    }
}

self.addEventListener('sync', (event) => {
    if (event.tag === 'scan-sync') {
        event.waitUntil(syncScans());
    }
});

self.addEventListener('online', () => {
    syncScans();
});
