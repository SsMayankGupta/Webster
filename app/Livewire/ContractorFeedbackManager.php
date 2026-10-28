<?php

namespace App\Livewire;

use App\Events\FeedbackResponded;
use App\Models\MealFeedback;
use App\Support\SafeBroadcast;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ContractorFeedbackManager extends Component
{
    public string $filter = 'DAILY';

    public array $feedbacks = [];

    public array $responses = [];

    public function mount(): void
    {
        $this->loadFeedbacks();
    }

    public function updatedFilter(): void
    {
        $this->loadFeedbacks();
    }

    private function loadFeedbacks(): void
    {
        $this->feedbacks = MealFeedback::query()
            ->with(['student', 'menu'])
            ->where('feedback_type', $this->filter)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'student_name' => $feedback->student->name,
                    'student_id' => $feedback->student->college_id,
                    'meal_type' => $feedback->menu->meal_type,
                    'serve_at' => $feedback->menu->serve_at->format('M j, g:i A'),
                    'rating' => $feedback->rating,
                    'hygiene_rating' => $feedback->hygiene_rating,
                    'comment' => $feedback->comment,
                    'contractor_action' => $feedback->contractor_action,
                    'contractor_note' => $feedback->contractor_note,
                    'created_at' => $feedback->created_at->format('M j, g:i A'),
                ];
            })
            ->toArray();
    }

    public function respond(int $feedbackId, string $action, ?string $note = null): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $feedback = MealFeedback::query()->findOrFail($feedbackId);

        $feedback->update([
            'contractor_action' => $action,
            'contractor_note' => $note,
        ]);

        SafeBroadcast::send(new FeedbackResponded($feedback));

        $this->loadFeedbacks();
        $this->dispatch('portal-toast', message: 'Response recorded successfully.', type: 'success');
    }

    public function render()
    {
        return view('livewire.contractor-feedback-manager');
    }
}
