<?php

namespace App\Livewire;

use App\Models\WasteTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Waste Management')]
class ContractorWaste extends Component
{
    public array $transactions = [];

    public string $weightKg = '';

    public string $type = 'compost';

    public string $earnings = '';

    public array $types = [
        'compost' => 'Compost',
        'biogas' => 'Biogas',
        'donation' => 'Donation',
        'sale' => 'Sale',
    ];

    public function mount(): void
    {
        $this->loadTransactions();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->loadTransactions();
    }

    public function loadTransactions(): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            return;
        }

        $transactions = WasteTransaction::query()
            ->where('contractor_id', $contractor->id)
            ->latest()
            ->get();

        $this->transactions = $transactions->map(fn (WasteTransaction $transaction): array => [
            'id' => $transaction->id,
            'date' => $transaction->created_at->format('d M Y · g:i A'),
            'weight_kg' => $transaction->weight_kg,
            'type' => $transaction->type,
            'type_label' => $this->types[$transaction->type] ?? $transaction->type,
            'earnings' => $transaction->earnings,
        ])->values()->all();
    }

    public function logWaste(): void
    {
        $this->validate([
            'weightKg' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:compost,biogas,donation,sale'],
            'earnings' => ['nullable', 'numeric', 'min:0'],
        ]);

        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        WasteTransaction::query()->create([
            'contractor_id' => $contractor->id,
            'weight_kg' => (float) $this->weightKg,
            'type' => $this->type,
            'earnings' => (float) ($this->earnings ?: 0),
            'logged_at' => now(),
        ]);

        $this->weightKg = '';
        $this->type = 'compost';
        $this->earnings = '';
        $this->loadTransactions();

        $this->dispatch('portal-toast', message: 'Waste logged successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.contractor-waste');
    }
}
