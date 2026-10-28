<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Services\ExtraMealBillingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.contractor')]
#[Title('Students')]
class ContractorStudents extends Component
{
    use WithPagination;

    public ?string $statusMessage = null;

    public string $name = '';

    public string $collegeId = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?int $editingStudentId = null;

    public function addStudent(): void
    {
        $contractor = $this->contractor();
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'collegeId' => ['required', 'string', 'max:255', Rule::unique('users', 'college_id')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:24', 'regex:/^\+?[0-9\s().-]{10,20}$/', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:8', 'confirmed:passwordConfirmation'],
        ]);

        $student = User::query()->create([
            'name' => $validated['name'],
            'college_id' => $validated['collegeId'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'password' => $validated['password'],
            'role' => 'student',
            'hostel' => $contractor->hostel,
            'contractor_id' => $contractor->id,
            'registered_at' => today(),
            'account_approved_at' => now(),
            'dietary_tags' => [],
            'weight_log' => [],
        ]);
        $student->forceFill(['must_change_password' => true])->save();

        $this->reset(['name', 'collegeId', 'email', 'phone', 'password', 'passwordConfirmation']);
        $this->statusMessage = 'Student account created. Share the initial password with them; they will change it after signing in.';
    }

    public function editStudent(int $studentId): void
    {
        $student = $this->contractor()->students()->whereNull('archived_at')->findOrFail($studentId);

        $this->editingStudentId = $student->id;
        $this->name = $student->name;
        $this->collegeId = $student->college_id;
        $this->email = $student->email;
        $this->phone = $student->phone ?? '';
        $this->reset(['password', 'passwordConfirmation']);
        $this->resetValidation();
        $this->statusMessage = null;
    }

    public function updateStudent(): void
    {
        $student = $this->contractor()->students()->whereNull('archived_at')->findOrFail($this->editingStudentId);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'collegeId' => ['required', 'string', 'max:255', Rule::unique('users', 'college_id')->ignore($student)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student)],
            'phone' => ['nullable', 'string', 'max:24', 'regex:/^\+?[0-9\s().-]{10,20}$/', Rule::unique('users', 'phone')->ignore($student)],
            'password' => ['nullable', 'string', 'min:8'],
            'passwordConfirmation' => ['required_with:password', 'same:password'],
        ]);

        $student->forceFill([
            'name' => $validated['name'],
            'college_id' => $validated['collegeId'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
        ]);

        if (filled($validated['password'])) {
            $student->password = $validated['password'];
            $student->must_change_password = true;
        }

        $student->save();
        $this->cancelStudentEdit();
        $this->statusMessage = filled($validated['password'])
            ? 'Student details updated. They must change the new initial password after signing in.'
            : 'Student details updated.';
    }

    public function archiveStudent(int $studentId): void
    {
        $student = $this->contractor()->students()->findOrFail($studentId);

        if ($student->mealBookings()->where('is_guest_pass', true)->where('status', 'pending_approval')->exists()) {
            $this->addError('archive', 'Resolve this student’s pending guest-meal requests before deactivating the account.');

            return;
        }

        if (app(ExtraMealBillingService::class)->summary($student)['outstanding_paise'] > 0) {
            $this->addError('archive', 'Collect the outstanding guest-meal balance before deactivating the account.');

            return;
        }

        $student->forceFill(['archived_at' => now()])->save();
        $this->resetPage();

        if ($this->editingStudentId === $studentId) {
            $this->cancelStudentEdit();
        }

        $this->statusMessage = 'Student account deactivated. Their records have been preserved.';
    }

    public function restoreStudent(int $studentId): void
    {
        $this->contractor()->students()->findOrFail($studentId)
            ->forceFill(['archived_at' => null])
            ->save();
        $this->resetPage();
        $this->resetPage('archivedPage');

        $this->statusMessage = 'Student account reactivated.';
    }

    public function permanentlyDeleteStudent(int $studentId): void
    {
        $student = $this->contractor()->students()->findOrFail($studentId);

        if ($student->archived_at === null) {
            $this->addError('deletion', 'Deactivate the student before permanently deleting the account.');

            return;
        }

        if (
            $student->mealBookings()->exists()
            || $student->primaryGuestBookings()->exists()
            || $student->leaveRequests()->exists()
            || $student->tickets()->exists()
            || $student->rebateLedgers()->exists()
            || $student->feePayments()->exists()
            || $student->mealRefunds()->exists()
            || $student->extraMealCharges()->exists()
            || $student->extraMealCollections()->exists()
            || $student->mealFeedbacks()->exists()
        ) {
            $this->addError('deletion', 'This student has portal or financial history that must be preserved. The account can remain deactivated, but cannot be permanently deleted.');

            return;
        }

        $student->delete();
        $this->resetPage('archivedPage');
        $this->statusMessage = 'Student account permanently deleted.';
    }

    public function cancelStudentEdit(): void
    {
        $this->editingStudentId = null;
        $this->reset(['name', 'collegeId', 'email', 'phone', 'password', 'passwordConfirmation']);
        $this->resetValidation();
    }

    public function render()
    {
        $contractor = $this->contractor();

        return view('livewire.contractor-students', [
            'students' => $contractor->students()
                ->whereNull('archived_at')
                ->select(['id', 'name', 'college_id', 'email', 'phone', 'hostel', 'registered_at'])
                ->orderByDesc('registered_at')
                ->orderByDesc('id')
                ->paginate(12),
            'archivedStudents' => $contractor->students()
                ->whereNotNull('archived_at')
                ->select(['id', 'name', 'college_id', 'email', 'phone', 'hostel', 'registered_at'])
                ->orderByDesc('archived_at')
                ->orderByDesc('id')
                ->paginate(12, pageName: 'archivedPage'),
        ]);
    }

    private function contractor(): User
    {
        $contractor = Auth::guard('contractor')->user();
        abort_unless($contractor instanceof User && $contractor->role === 'contractor', 403);

        return $contractor;
    }
}
