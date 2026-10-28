<div class="mx-auto w-full max-w-6xl space-y-8 font-lora">
    <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">People operations</p>
        <h1 class="mt-2 text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Your students</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create student accounts and give each student their initial password.</p>
    </header>

    @if ($statusMessage)
        <p role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ $statusMessage }}</p>
    @endif
    @error('deletion')
        <p role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300">{{ $message }}</p>
    @enderror
    @error('archive')
        <p role="alert" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ $message }}</p>
    @enderror

    <section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900 sm:p-8">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $editingStudentId ? 'Edit student details' : 'Add a student' }}</h2>
        @if ($editingStudentId)
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Leave the password blank to keep it unchanged. If you set a new password, the student must change it after signing in.</p>
        @endif
        <form wire:submit="{{ $editingStudentId ? 'updateStudent' : 'addStudent' }}" class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="student-name" class="label">Full name</label>
                <input id="student-name" type="text" wire:model="name" required class="input">
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="student-college-id" class="label">Student / college ID</label>
                <input id="student-college-id" type="text" wire:model="collegeId" required class="input">
                @error('collegeId') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="student-email" class="label">Email address</label>
                <input id="student-email" type="email" wire:model="email" required class="input">
                @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="student-phone" class="label">Mobile number (optional)</label>
                <input id="student-phone" type="tel" wire:model="phone" class="input">
                @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="student-password" class="label">{{ $editingStudentId ? 'New password (optional)' : 'Initial password' }}</label>
                <div x-data="{ visible: false }" class="relative">
                    <input id="student-password" :type="visible ? 'text' : 'password'" wire:model="password" autocomplete="new-password" @if (!$editingStudentId) required @endif class="input pr-12">
                    <x-password-visibility-toggle />
                </div>
                @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="student-password-confirmation" class="label">Confirm {{ $editingStudentId ? 'new password' : 'initial password' }}</label>
                <div x-data="{ visible: false }" class="relative">
                    <input id="student-password-confirmation" :type="visible ? 'text' : 'password'" wire:model="passwordConfirmation" autocomplete="new-password" @if (!$editingStudentId) required @endif class="input pr-12">
                    <x-password-visibility-toggle />
                </div>
                @error('passwordConfirmation') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">{{ $editingStudentId ? 'Save student details' : 'Create student account' }}</button>
                    @if ($editingStudentId)
                        <button type="button" wire:click="cancelStudentEdit" class="btn-ghost">Cancel editing</button>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-4">Student</th>
                        <th class="px-5 py-4">Contact</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($students as $student)
                        <tr wire:key="student-{{ $student->id }}">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $student->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $student->college_id }} · {{ $student->hostel }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                                <p>{{ $student->email }}</p>
                                <p class="mt-1 text-xs">{{ $student->phone }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="editStudent({{ $student->id }})" class="btn-ghost !px-3 !py-2 text-sm">Edit</button>
                                    <button type="button" wire:click="archiveStudent({{ $student->id }})" wire:confirm="Deactivate this student? Their account and history will be preserved, and they will no longer be able to sign in." class="rounded-lg px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950">Deactivate</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-12 text-center text-slate-500">No students have been added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $students->links() }}</div>
    </section>

    @if ($archivedStudents->isNotEmpty())
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h2 class="text-lg font-semibold">Deactivated students</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Student</th>
                            <th class="px-5 py-4">Contact</th>
                            <th class="px-5 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($archivedStudents as $student)
                            <tr wire:key="archived-student-{{ $student->id }}">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $student->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $student->college_id }} · {{ $student->hostel }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                                    <p>{{ $student->email }}</p>
                                    <p class="mt-1 text-xs">{{ $student->phone }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="restoreStudent({{ $student->id }})" class="btn-ghost !px-3 !py-2 text-sm">Reactivate</button>
                                        <button type="button" wire:click="permanentlyDeleteStudent({{ $student->id }})" wire:confirm="Permanently delete this deactivated student account? This cannot be undone. Students with portal or financial history cannot be permanently deleted." class="rounded-lg px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950">Permanently delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $archivedStudents->links() }}</div>
        </section>
    @endif
</div>
