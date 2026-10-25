<div class="font-lora mx-auto w-full max-w-6xl space-y-8">
    <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">People operations</p>
        <h1 class="mt-2 text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Students</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Register and manage student accounts.</p>
    </header>

    <section aria-labelledby="register-student-heading">
        @if ($successMessage)
            <div role="status" class="mb-5 border-y border-emerald-200 py-3 text-sm text-emerald-700 dark:border-emerald-900/50 dark:text-emerald-300">
                {{ $successMessage }}
            </div>
        @endif

        <div class="mb-5">
            <h2 id="register-student-heading" class="text-xl font-medium text-slate-900 dark:text-white">Register a student</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add account details and the student’s mess profile.</p>
        </div>

        <form wire:submit="register" class="space-y-6">
            <section class="space-y-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">Account information</h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="student-name" class="label">Full name <span class="text-rose-500">*</span></label>
                        <input id="student-name" type="text" wire:model="name" autocomplete="name" required class="input">
                        @error('name') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="student-college-id" class="label">College ID <span class="text-rose-500">*</span></label>
                        <input id="student-college-id" type="text" wire:model="collegeId" required class="input">
                        @error('collegeId') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="student-email" class="label">Email address <span class="text-rose-500">*</span></label>
                        <input id="student-email" type="email" wire:model="email" autocomplete="email" required class="input">
                        @error('email') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="student-mess-id" class="label">Mess ID <span class="text-slate-500">(optional)</span></label>
                        <input id="student-mess-id" type="number" min="1" wire:model="messId" class="input">
                        @error('messId') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="student-password" class="label">Temporary password <span class="text-rose-500">*</span></label>
                        <input id="student-password" type="password" wire:model="password" autocomplete="new-password" required class="input">
                        @error('password') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="student-password-confirmation" class="label">Confirm password <span class="text-rose-500">*</span></label>
                        <input id="student-password-confirmation" type="password" wire:model="passwordConfirmation" autocomplete="new-password" required class="input">
                    </div>
                </div>
            </section>

            <section class="space-y-4 border-t border-slate-200 pt-5 dark:border-slate-800">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">Mess profile <span class="font-normal normal-case tracking-normal text-slate-500">(optional)</span></h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="student-hostel" class="label">Hostel / residence</label>
                        <input id="student-hostel" type="text" wire:model="hostel" class="input">
                        @error('hostel') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="student-dietary-preference" class="label">Dietary preference</label>
                        <select id="student-dietary-preference" wire:model="dietaryPreference" class="input">
                            <option value="">No preference selected</option>
                            <option value="veg">Vegetarian</option>
                            <option value="non-veg">Non-vegetarian</option>
                            <option value="vegan">Vegan</option>
                            <option value="jain">Jain</option>
                        </select>
                        @error('dietaryPreference') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <fieldset>
                    <legend class="label">Dietary tags</legend>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($dietaryTagOptions as $tag => $label)
                            <label wire:key="dietary-tag-{{ $tag }}" class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 transition hover:border-emerald-300 dark:border-slate-700 dark:text-slate-300 dark:hover:border-emerald-700">
                                <input type="checkbox" wire:model="dietaryTags" value="{{ $tag }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('dietaryTags') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                    @error('dietaryTags.*') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                </fieldset>
            </section>

            <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                <p class="text-xs text-slate-500 dark:text-slate-400"><span class="text-rose-500">*</span> Required fields. Student role is assigned automatically.</p>
                <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="register">Register student</span>
                    <span wire:loading wire:target="register">Registering...</span>
                </button>
            </div>
        </form>
    </section>

    <section aria-labelledby="all-students-heading">
        <div class="mb-4 flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between dark:border-slate-800">
            <div>
                <h2 id="all-students-heading" class="text-xl font-medium text-slate-900 dark:text-white">All students</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Student accounts registered in the system.</p>
            </div>
            <div class="w-full sm:max-w-sm">
                <label for="student-search" class="sr-only">Search students</label>
                <input
                    id="student-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search name, college ID, email or hostel"
                    class="input"
                >
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left text-sm">
                <thead class="border-y border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Student</th>
                        <th scope="col" class="px-5 py-3 font-semibold">College ID</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Hostel</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Dietary preference</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Mess ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($students as $student)
                        <tr wire:key="student-{{ $student->id }}" class="text-slate-700 dark:text-slate-300">
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-900 dark:text-white">{{ $student->name }}</div>
                                <div class="mt-0.5 text-slate-500 dark:text-slate-400">{{ $student->email }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $student->college_id ?: '—' }}</td>
                            <td class="px-5 py-4">{{ $student->hostel ?: '—' }}</td>
                            <td class="px-5 py-4">{{ $student->dietary_pref ? ucfirst($student->dietary_pref) : '—' }}</td>
                            <td class="px-5 py-4">{{ $student->mess_id ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-600 dark:text-slate-400">
                                {{ $search === '' ? 'No students have been registered yet.' : 'No students match your search.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">
                {{ $students->links() }}
            </div>
        @endif
    </section>
</div>
