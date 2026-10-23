<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Staff Management')]
class ContractorStaff extends Component
{
    public array $staff = [];

    public function mount(): void
    {
        $this->loadStaff();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->loadStaff();
    }

    public function loadStaff(): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            return;
        }

        $staffMembers = User::query()
            ->where('role', 'contractor')
            ->get();

        $this->staff = $staffMembers->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'college_id' => $user->college_id,
            'email' => $user->email,
            'hostel' => $user->hostel,
        ])->values()->all();
    }

    public function render()
    {
        return view('livewire.contractor-staff');
    }
}
