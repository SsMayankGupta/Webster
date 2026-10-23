<div
    x-data="portalRoot()"
    x-init="connect(@js($isAuthenticated ? ($state['broadcasting'] ?? null) : null))"
    @portal-authenticated.window="connect($event.detail.broadcasting)"
    @portal-logged-out.window="disconnect()"
    @portal-toast.window="toast($event.detail.message, $event.detail.type)"
    @class(['relative', 'min-h-screen'])
>
    @include('livewire.student-portal.header')

    <main>
        @if ($isAuthenticated)
            @include('livewire.student-portal.dashboard')
        @else
            @include('livewire.student-portal.guest')
        @endif
    </main>

    @include('livewire.student-portal.auth-panel')

    <div @class(['pointer-events-none', 'fixed', 'inset-x-0', 'bottom-4', 'z-[70]', 'flex', 'flex-col', 'items-center', 'gap-2', 'px-4'])>
        <template x-for="toast in toasts" :key="toast.id">
            <div
                @class(['pointer-events-auto', 'w-full', 'max-w-md', 'rounded-xl', 'border', 'px-4', 'py-3', 'text-sm', 'font-medium', 'shadow-soft'])
                :class="toast.style"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="-translate-y-2 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-text="toast.message"
            ></div>
        </template>
    </div>
</div>
