<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.warden')]
class WardenDashboard extends Component
{
    public string $contractorName = '';

    public string $employeeId = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?int $editingContractorId = null;

    public ?string $statusMessage = null;

    public function addContractor(): void
    {
        $warden = $this->warden();
        $existingContractor = User::query()
            ->where('email', trim($this->email))
            ->where('role', 'contractor')
            ->whereNull('archived_at')
            ->first();

        if ($existingContractor !== null && $existingContractor->warden_id !== null && $existingContractor->warden_id !== $warden->id) {
            $this->addError('email', 'This contractor account is already affiliated with another warden.');

            return;
        }

        $employeeIdRule = Rule::unique('users', 'employee_id');
        $emailRule = Rule::unique('users', 'email');
        $phoneRule = Rule::unique('users', 'phone');
        if ($existingContractor !== null) {
            $employeeIdRule->ignore($existingContractor);
            $emailRule->ignore($existingContractor);
            $phoneRule->ignore($existingContractor);
        }

        $validated = $this->validate([
            'contractorName' => ['required', 'string', 'max:255'],
            'employeeId' => ['required', 'string', 'max:255', $employeeIdRule],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'phone' => ['required', 'string', 'max:24', 'regex:/^\+?[0-9\s().-]{10,20}$/', $phoneRule],
            'password' => ['required', 'string', 'min:8', 'confirmed:passwordConfirmation'],
        ]);

        $contractorData = [
            'name' => $validated['contractorName'],
            'employee_id' => $validated['employeeId'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'role' => 'contractor',
            'hostel' => $warden->hostel,
            'warden_id' => $warden->id,
            'registered_at' => today(),
            'account_approved_at' => now(),
        ];

        if ($existingContractor !== null) {
            $existingContractor->forceFill([
                ...$contractorData,
                'must_change_password' => true,
            ])->save();
            $contractor = $existingContractor->refresh();
        } else {
            $contractor = User::query()->create($contractorData);
            $contractor->forceFill(['must_change_password' => true])->save();
        }

        $this->reset(['contractorName', 'employeeId', 'email', 'phone', 'password', 'passwordConfirmation']);
        $this->statusMessage = $existingContractor === null
            ? 'Contractor account created. Share the initial password with them; they will change it after signing in.'
            : 'Existing contractor account connected. Share the initial password with them; they will change it after signing in.';
    }

    public function editContractor(int $contractorId): void
    {
        $contractor = $this->warden()->contractors()->whereNull('archived_at')->findOrFail($contractorId);

        $this->editingContractorId = $contractor->id;
        $this->contractorName = $contractor->name;
        $this->employeeId = $contractor->employee_id;
        $this->email = $contractor->email;
        $this->phone = $contractor->phone;
        $this->reset(['password', 'passwordConfirmation']);
        $this->resetValidation();
        $this->statusMessage = null;
    }

    public function updateContractor(): void
    {
        $contractor = $this->warden()->contractors()->whereNull('archived_at')->findOrFail($this->editingContractorId);

        $validated = $this->validate([
            'contractorName' => ['required', 'string', 'max:255'],
            'employeeId' => ['required', 'string', 'max:255', Rule::unique('users', 'employee_id')->ignore($contractor)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($contractor)],
            'phone' => ['required', 'string', 'max:24', 'regex:/^\+?[0-9\s().-]{10,20}$/', Rule::unique('users', 'phone')->ignore($contractor)],
            'password' => ['nullable', 'string', 'min:8'],
            'passwordConfirmation' => ['required_with:password', 'same:password'],
        ]);

        $contractor->forceFill([
            'name' => $validated['contractorName'],
            'employee_id' => $validated['employeeId'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        if (filled($validated['password'])) {
            $contractor->password = $validated['password'];
            $contractor->must_change_password = true;
        }

        $contractor->save();
        $this->cancelContractorEdit();
        $this->statusMessage = filled($validated['password'])
            ? 'Contractor details updated. They must change the new initial password after signing in.'
            : 'Contractor details updated.';
    }

    public function archiveContractor(int $contractorId): void
    {
        $contractor = $this->warden()->contractors()->findOrFail($contractorId);
        $contractor->forceFill(['archived_at' => now()])->save();

        if ($this->editingContractorId === $contractorId) {
            $this->cancelContractorEdit();
        }

        $this->statusMessage = 'Contractor account deactivated. Their records and student affiliations have been preserved.';
    }

    public function restoreContractor(int $contractorId): void
    {
        $this->warden()->contractors()->findOrFail($contractorId)
            ->forceFill(['archived_at' => null])
            ->save();

        $this->statusMessage = 'Contractor account reactivated.';
    }

    public function permanentlyDeleteContractor(int $contractorId): void
    {
        $contractor = $this->warden()->contractors()->findOrFail($contractorId);

        if ($contractor->archived_at === null) {
            $this->addError('deletion', 'Deactivate the contractor before permanently deleting the account.');

            return;
        }

        if ($contractor->wasteTransactions()->exists() || $contractor->notices()->exists()) {
            $this->addError('deletion', 'This contractor has operational records that must be preserved. The account can remain deactivated, but cannot be permanently deleted.');

            return;
        }

        $contractor->delete();
        $this->statusMessage = 'Contractor account permanently deleted. Their affiliated students remain, but are no longer linked to this contractor.';
    }

    public function cancelContractorEdit(): void
    {
        $this->editingContractorId = null;
        $this->reset(['contractorName', 'employeeId', 'email', 'phone', 'password', 'passwordConfirmation']);
        $this->resetValidation();
    }

    public function render()
    {
        $warden = $this->warden();

        return view('livewire.warden-dashboard', [
            'contractors' => $warden->contractors()->whereNull('archived_at')->orderBy('name')->get(),
            'archivedContractors' => $warden->contractors()->whereNotNull('archived_at')->orderBy('name')->get(),
            'approvedStudentCount' => User::query()
                ->where('role', 'student')
                ->whereNull('archived_at')
                ->whereHas('contractor', fn ($query) => $query->where('warden_id', $warden->id))
                ->count(),
        ]);
    }

    private function warden(): User
    {
        $warden = Auth::guard('warden')->user();
        abort_unless($warden instanceof User && $warden->isWarden(), 403);

        return $warden;
    }
}
