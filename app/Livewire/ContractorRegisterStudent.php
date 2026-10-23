<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.contractor')]
#[Title('Register Student')]
class ContractorRegisterStudent extends Component
{
    use WithPagination;

    public string $name = '';

    public string $collegeId = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public string $hostel = '';

    public string $dietaryPreference = '';

    public array $dietaryTags = [];

    public string $messId = '';

    public ?string $successMessage = null;

    public string $search = '';

    public function mount(): void
    {
        $this->authorizeContractor();
    }

    public function register(): void
    {
        $this->authorizeContractor();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'collegeId' => ['required', 'string', 'max:255', Rule::unique('users', 'college_id')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed:passwordConfirmation'],
            'hostel' => ['nullable', 'string', 'max:255'],
            'dietaryPreference' => ['nullable', Rule::in(['veg', 'non-veg', 'vegan', 'jain'])],
            'dietaryTags' => ['array'],
            'dietaryTags.*' => ['string', Rule::in(array_keys((array) config('mess.dietary_tags')))],
            'messId' => ['nullable', 'integer', 'min:1'],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'college_id' => $validated['collegeId'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'student',
            'hostel' => $validated['hostel'] ?: null,
            'dietary_pref' => $validated['dietaryPreference'] ?: null,
            'dietary_tags' => $validated['dietaryTags'],
            'weight_log' => [],
            'mess_id' => $validated['messId'] ?: null,
        ]);

        $this->reset([
            'name',
            'collegeId',
            'email',
            'password',
            'passwordConfirmation',
            'hostel',
            'dietaryPreference',
            'dietaryTags',
            'messId',
        ]);

        $this->successMessage = 'Student registered successfully.';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.contractor-register-student', [
            'dietaryTagOptions' => (array) config('mess.dietary_tags'),
            'students' => User::query()
                ->where('role', 'student')
                ->select(['id', 'name', 'college_id', 'email', 'hostel', 'dietary_pref', 'mess_id'])
                ->when(trim($this->search) !== '', function ($query): void {
                    $search = '%'.trim($this->search).'%';

                    $query->where(function ($query) use ($search): void {
                        $query->where('name', 'like', $search)
                            ->orWhere('college_id', 'like', $search)
                            ->orWhere('email', 'like', $search)
                            ->orWhere('hostel', 'like', $search);
                    });
                })
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    private function authorizeContractor(): void
    {
        abort_unless(Auth::guard('contractor')->user()?->role === 'contractor', 403);
    }
}
