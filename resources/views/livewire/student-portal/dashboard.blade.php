@php
    $todayMeals = $state['today']['meals'] ?? [];
    $allMeals = collect($state['days'] ?? [])->flatMap(fn(array $day): array => $day['meals']);
    $nextCutoff = $allMeals->where('status', 'open')->sortBy('cutoff_timestamp')->first();
    $optedInToday = collect($todayMeals)->where('opted_in', true)->count();
    $displayNow = now()->timezone(config('mess.display_timezone'));
    $greeting = match (true) {
        $displayNow->hour < 12 => 'Good morning',
        $displayNow->hour < 17 => 'Good afternoon',
        default => 'Good evening',
    };
@endphp

<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                {{ $displayNow->format('l, d F Y') }}
            </p>
            <h1 class="mt-1 font-lora text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">
                {{ $greeting }}, {{ explode(' ', trim($state['student']['name']))[0] }}.
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $optedInToday }} of {{ count($todayMeals) }} meals confirmed for today ·
                {{ $state['cutoff_hours'] }}-hour cutoff enforced on the server clock.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="badge-info">{{ $state['student']['hostel'] ?? 'Hostel not set' }}</span>
            @foreach (array_slice($state['student']['dietary_tags'] ?? [], 0, 3) as $tag)
                <span class="badge-success">{{ config('mess.dietary_tags.' . $tag, ucfirst($tag)) }}</span>
            @endforeach
        </div>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Today’s meals</p>
                <svg class="h-4 w-4 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="mt-2 font-lora text-3xl font-semibold">{{ $optedInToday }}<span
                    class="text-lg font-semibold text-slate-400">/{{ count($todayMeals) }}</span></p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Confirmed plates</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Next cutoff</p>
                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
            </div>
            @if ($nextCutoff)
                <div x-data="mealClock({{ $nextCutoff['cutoff_timestamp'] }}, {{ $nextCutoff['serve_timestamp'] }}, @js($state['server_now']))" class="mt-2">
                    <p class="font-mono text-2xl font-extrabold tabular-nums" x-text="short"></p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $nextCutoff['meal_label'] }} · locks
                        {{ $nextCutoff['cutoff_label'] }}</p>
                </div>
            @else
                <p class="mt-2 font-lora text-2xl font-semibold text-slate-400">None</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">No open cutoffs</p>
            @endif
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Your counter lane</p>
                <svg class="h-4 w-4 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </div>
            @if ($state['active_booking'])
                <p class="mt-2 font-lora text-3xl font-semibold">
                    {{ $state['active_booking']['lane'] ?? 'Pending' }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    {{ $state['active_booking']['meal_type'] ? ucfirst($state['active_booking']['meal_type']) : 'Next meal' }}
                    · {{ $state['active_booking']['serve_at_label'] }}
                </p>
            @else
                <p class="mt-2 font-lora text-2xl font-semibold text-slate-400">Not pinned</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Lane is assigned at lock-in</p>
            @endif
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Net bill ·
                    {{ $state['billing']['label'] }}</p>
                <svg class="h-4 w-4 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-2.5-2.5L14 21l-2.5-2.5L9 21l-2.5-2.5L4 21V5a2 2 0 012-2h12a2 2 0 012 2v16z" />
                </svg>
            </div>
            <p class="mt-2 font-lora text-3xl font-semibold">
                ₹{{ number_format($state['billing']['net_paise'] / 100, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                ₹{{ number_format($state['billing']['rebate_paise'] / 100, 2) }} rebated this month</p>
        </div>
    </div>

    <section aria-labelledby="student-help-heading" class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-400">Support</p>
            <h2 id="student-help-heading" class="mt-2 font-display text-xl font-semibold text-slate-900 dark:text-white">Need help?</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Reach out to your hostel team for help with meals, bookings, or your account.</p>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['label' => 'Your contractor', 'contact' => $state['help_contacts']['contractor'] ?? null],
                ['label' => 'Your warden', 'contact' => $state['help_contacts']['warden'] ?? null],
            ] as $helpContact)
                <article class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <h3 class="font-ui text-sm font-semibold text-slate-900 dark:text-white">{{ $helpContact['label'] }}</h3>
                    @if ($helpContact['contact'])
                        <p class="mt-2 text-sm font-medium text-slate-800 dark:text-slate-100">{{ $helpContact['contact']['name'] }}</p>
                        @if (filled($helpContact['contact']['email']))
                            <a href="mailto:{{ $helpContact['contact']['email'] }}" class="mt-1 block break-all text-sm text-brand-700 hover:underline dark:text-brand-400">{{ $helpContact['contact']['email'] }}</a>
                        @endif
                        @if (filled($helpContact['contact']['phone']))
                            <a href="tel:{{ $helpContact['contact']['phone'] }}" class="mt-1 block text-sm text-brand-700 hover:underline dark:text-brand-400">{{ $helpContact['contact']['phone'] }}</a>
                        @endif
                    @else
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Contact details are not available. Please contact hostel administration.</p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <div class="mt-6 flex gap-2 overflow-x-auto pb-1 lg:hidden">
        @foreach ([['meals', 'Meals'], ['qr', 'QR Check-In'], ['leave', 'Leave & Rebates'], ['profile', 'Profile']] as [$key, $label])
            <button type="button" wire:click="setTab('{{ $key }}')"
                class="shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition {{ $activeTab === $key ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 bg-white text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="mt-6">
        @if ($activeTab === 'meals')
            @include('livewire.student-portal.meals')
        @elseif ($activeTab === 'qr')
            @include('livewire.student-portal.qr')
        @elseif ($activeTab === 'leave')
            @include('livewire.student-portal.leave')
        @else
            @include('livewire.student-portal.profile')
        @endif
    </div>
</section>
