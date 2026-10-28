const TOAST_STYLES = {
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-200',
    warning: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/60 dark:text-amber-200',
    error: 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900 dark:bg-rose-950/60 dark:text-rose-200',
    info: 'border-slate-200 bg-white text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200',
};

function formatDuration(milliseconds) {
    const total = Math.max(0, Math.floor(milliseconds / 1000));
    const days = Math.floor(total / 86400);
    const hours = Math.floor((total % 86400) / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const seconds = total % 60;

    if (days > 0) {
        return `${days}d ${hours}h`;
    }

    if (hours > 0) {
        return `${hours}h ${String(minutes).padStart(2, '0')}m`;
    }

    return `${minutes}m ${String(seconds).padStart(2, '0')}s`;
}

function formatClock(milliseconds) {
    const total = Math.max(0, Math.floor(milliseconds / 1000));
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const seconds = total % 60;

    return hours > 0
        ? `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
        : `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Minimal Reverb / Pusher-protocol client so we do not need Echo or pusher-js.
 */
export function startRealtime(config, { onStatus, onEvent } = {}) {
    if (!config?.key) {
        onStatus?.({ label: 'Offline', tone: 'slate' });

        return () => {};
    }

    let socket = null;
    let attempts = 0;
    let stopped = false;
    let reconnectTimer = null;

    const setStatus = (label, tone) => onStatus?.({ label, tone });

    const subscribePrivate = async (socketId) => {
        const response = await fetch('/broadcasting/auth', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                socket_id: socketId,
                channel_name: config.private_channel,
            }),
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        socket?.send(JSON.stringify({
            event: 'pusher:subscribe',
            data: { channel: config.private_channel, auth: data.auth },
        }));
    };

    const connect = () => {
        if (stopped) {
            return;
        }

        const secure = config.scheme === 'https';
        const url = `${secure ? 'wss' : 'ws'}://${config.host}:${config.port}/app/${config.key}?protocol=7&client=js&version=8.4.0&flash=false`;

        setStatus('Reconnecting…', 'amber');

        try {
            socket = new WebSocket(url);
        } catch {
            scheduleReconnect();

            return;
        }

        socket.onmessage = async (message) => {
            let payload;

            try {
                payload = JSON.parse(message.data);
            } catch {
                return;
            }

            if (payload.event === 'pusher:connection_established') {
                attempts = 0;
                setStatus('Connected', 'emerald');

                const data = JSON.parse(payload.data ?? '{}');
                socket.send(JSON.stringify({
                    event: 'pusher:subscribe',
                    data: { channel: config.public_channel },
                }));

                try {
                    await subscribePrivate(data.socket_id);
                } catch {
                    // Public channel events still work without private authorisation.
                }

                return;
            }

            if (payload.event === 'pusher:ping') {
                socket.send(JSON.stringify({ event: 'pusher:pong' }));

                return;
            }

            if (payload.event?.startsWith('pusher:')) {
                return;
            }

            let data = payload.data;

            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch {
                    data = {};
                }
            }

            onEvent?.(payload.event, data ?? {});
        };

        socket.onclose = () => {
            if (stopped) {
                return;
            }

            setStatus('Reconnecting…', 'amber');
            scheduleReconnect();
        };

        socket.onerror = () => {
            socket?.close();
        };
    };

    const scheduleReconnect = () => {
        if (stopped) {
            return;
        }

        attempts += 1;
        const delay = Math.min(30000, 1000 * 2 ** Math.min(attempts, 5));

        clearTimeout(reconnectTimer);
        reconnectTimer = setTimeout(connect, delay);
    };

    connect();

    return () => {
        stopped = true;
        clearTimeout(reconnectTimer);
        socket?.close();
        setStatus('Offline', 'slate');
    };
}

export function portalRoot() {
    return {
        status: { label: 'Offline', tone: 'slate' },
        toasts: [],
        disconnectRealtime: null,

        init() {
            window.addEventListener('realtime-status', (event) => {
                this.status = event.detail;
            });
        },

        connect(broadcasting) {
            this.disconnect();
            this.disconnectRealtime = startRealtime(broadcasting, {
                onStatus: (status) => {
                    this.status = status;
                },
                onEvent: (event, data) => {
                    if (event === 'meal.status') {
                        window.Livewire?.dispatch('meal-status');
                    }

                    if (event === 'meal.cutoff_warning') {
                        window.Livewire?.dispatch('cutoff-warning', { message: data.message ?? '' });
                    }

                    if (event === 'qr.redeemed') {
                        window.Livewire?.dispatch('qr-redeemed', { bookingId: data.booking_id });
                    }

                    if (event === 'App\\Events\\GuestPassApproved') {
                        window.Livewire?.dispatch('guest-pass-approved', { event: data });
                    }
                },
            });
        },

        disconnect() {
            this.disconnectRealtime?.();
            this.disconnectRealtime = null;
            this.status = { label: 'Offline', tone: 'slate' };
        },

        toast(message, type = 'info') {
            const id = `${Date.now()}-${Math.random()}`;

            this.toasts.push({ id, message, type, style: TOAST_STYLES[type] ?? TOAST_STYLES.info });

            setTimeout(() => {
                this.toasts = this.toasts.filter((toast) => toast.id !== id);
            }, 5200);
        },
    };
}

export function mealClock(cutoff, serve, serverNow) {
    return {
        cutoff: Number(cutoff),
        serve: Number(serve),
        offset: Date.now() - Date.parse(serverNow),
        now: Date.now(),
        timer: null,

        init() {
            this.timer = setInterval(() => {
                this.now = Date.now();
            }, 1000);
        },

        destroy() {
            clearInterval(this.timer);
        },

        get current() {
            return this.now - this.offset;
        },

        get remaining() {
            return Math.max(0, this.cutoff - this.current);
        },

        get phase() {
            if (this.current >= this.serve + 3 * 60 * 60 * 1000) {
                return 'closed';
            }

            if (this.current >= this.serve) {
                return 'serving';
            }

            if (this.current >= this.cutoff) {
                return 'locked';
            }

            return 'open';
        },

        get label() {
            if (this.phase === 'serving') {
                return 'Serving now';
            }

            if (this.phase === 'locked') {
                return 'Cutoff passed';
            }

            return `${formatDuration(this.remaining)} to cutoff`;
        },

        get short() {
            if (this.phase === 'serving') {
                return 'Serving';
            }

            if (this.phase === 'locked') {
                return 'Locked';
            }

            return formatClock(this.remaining);
        },
    };
}

export function qrClock(expiresAt, serverNow) {
    return {
        expiresAt: Date.parse(expiresAt),
        offset: Date.now() - Date.parse(serverNow),
        now: Date.now(),
        timer: null,

        init() {
            this.timer = setInterval(() => {
                this.now = Date.now();
            }, 1000);
        },

        destroy() {
            clearInterval(this.timer);
        },

        get seconds() {
            return Math.max(0, Math.floor((this.expiresAt - (this.now - this.offset)) / 1000));
        },

        get label() {
            return `${String(Math.floor(this.seconds / 60)).padStart(2, '0')}:${String(this.seconds % 60).padStart(2, '0')}`;
        },
    };
}

export async function copyText(value) {
    try {
        await navigator.clipboard.writeText(value);

        return true;
    } catch {
        return false;
    }
}

function registerAlpineComponents() {
    if (!window.Alpine || window.__portalAlpineRegistered) {
        return;
    }

    window.__portalAlpineRegistered = true;
    window.Alpine.data('portalRoot', portalRoot);
    window.Alpine.data('mealClock', mealClock);
    window.Alpine.data('qrClock', qrClock);
}

document.addEventListener('alpine:init', registerAlpineComponents);
window.addEventListener('load', registerAlpineComponents);
