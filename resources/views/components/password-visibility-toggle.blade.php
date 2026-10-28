<button type="button" @click="visible = !visible"
    class="absolute inset-y-0 right-2 my-auto flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
    :aria-label="visible ? 'Hide password' : 'Show password'">
    <svg x-show="!visible" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
        viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        <circle cx="12" cy="12" r="3" />
    </svg>
    <svg x-show="visible" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
        viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M3 3l18 18M10.585 10.587a2 2 0 002.828 2.828M9.88 5.09A10.7 10.7 0 0112 4.875c4.478 0 8.269 2.943 9.543 7a10.7 10.7 0 01-3.024 4.464M6.228 6.228A10.7 10.7 0 002.457 12c1.274 4.057 5.065 7 9.543 7a10.7 10.7 0 003.772-.683" />
    </svg>
</button>
