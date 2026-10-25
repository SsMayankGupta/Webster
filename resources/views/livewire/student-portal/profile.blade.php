@php
    $student = $state['student'];
@endphp

<div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
    <div class="card p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="font-lora text-xl font-semibold tracking-tight">Student profile</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Read-only details from your college record.</p>
            </div>
            <span class="badge-success">Verified student</span>
        </div>

        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Full name</dt>
                <dd class="mt-1 text-sm font-semibold">{{ $student['name'] }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">College ID</dt>
                <dd class="mt-1 font-mono text-sm">{{ $student['college_id'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">College email</dt>
                <dd class="mt-1 break-all text-sm">{{ $student['email'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Hostel</dt>
                <dd class="mt-1 text-sm">{{ $student['hostel'] ?? 'Not assigned' }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
            <a href="{{ route('home') }}" class="btn-ghost !border !border-slate-200 dark:!border-slate-800">
                Back to student portal
            </a>
            <a href="{{ route('profile.edit') }}" class="btn-ghost !border !border-slate-200 dark:!border-slate-800">
                Account settings
            </a>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <h2 class="font-lora text-xl font-semibold tracking-tight">Dietary preferences</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Global tags travel with every meal confirmation so the kitchen can plan precise headcounts.
            </p>

            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach ($state['dietary_options'] as $option)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3 py-2.5 text-sm transition hover:border-brand-300 hover:bg-brand-50/40 dark:border-slate-800 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/5">
                        <input
                            type="checkbox"
                            value="{{ $option['value'] }}"
                            wire:model="selectedDietaryTags"
                            class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900"
                        >
                        <span class="font-medium">{{ $option['label'] }}</span>
                    </label>
                @endforeach
            </div>

            <button type="button" wire:click="saveDietaryTags" class="btn-primary mt-4 w-full" wire:loading.attr="disabled" wire:target="saveDietaryTags">
                <span wire:loading.remove wire:target="saveDietaryTags">Save preferences</span>
                <span wire:loading wire:target="saveDietaryTags">Saving…</span>
            </button>
        </div>

        <div class="card p-5">
            <h2 class="font-lora text-base font-semibold">Security</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                <li class="flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                    Every action re-checks the <code class="rounded bg-slate-100 px-1 py-0.5 text-[11px] dark:bg-slate-800">student</code> guard on the server.
                </li>
                <li class="flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                    Check-in tokens are signed with ES256 and expire in {{ $state['qr']['ttl_seconds'] }} seconds.
                </li>
                <li class="flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                    Scanner public key fingerprint: <span class="font-mono text-[11px]">{{ $state['qr']['fingerprint'] }}</span>
                </li>
            </ul>

            <button
                type="button"
                wire:click="logout"
                wire:confirm="Sign out of the student portal?"
                class="btn-danger mt-5 w-full"
            >
                Sign out
            </button>
        </div>
    </div>
</div>
