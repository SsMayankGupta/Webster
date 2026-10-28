<?php

namespace Tests\Feature;

use App\Livewire\ContractorTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ContractorTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_only_sees_and_updates_tickets_from_their_students(): void
    {
        $contractor = $this->contractor('CON-TICKET-1');
        $student = $this->student($contractor, 'STU-TICKET-1');
        $otherContractor = $this->contractor('CON-TICKET-2');
        $otherStudent = $this->student($otherContractor, 'STU-TICKET-2');
        $ticket = $this->ticket($student, 'Food Quality', 'My student ticket text');
        $otherTicket = $this->ticket($otherStudent, 'Hygiene', 'Another contractor ticket text');
        $this->actingAs($contractor, 'contractor');

        $component = Livewire::test(ContractorTicket::class)
            ->assertSee('Food Quality')
            ->assertSee('My student ticket text')
            ->assertDontSee('Another contractor ticket text');

        $this->assertSame([$ticket->id], array_column($component->get('tickets'), 'id'));
        Livewire::test(ContractorTicket::class)
            ->call('updateStatus', $otherTicket->id, 'under_review')
            ->assertNotFound();

        $this->assertDatabaseHas('tickets', [
            'id' => $otherTicket->id,
            'status' => 'pending',
        ]);
    }

    public function test_contractor_ticket_status_changes_follow_allowed_transitions(): void
    {
        $contractor = $this->contractor('CON-TICKET-3');
        $student = $this->student($contractor, 'STU-TICKET-3');
        $ticket = $this->ticket($student, 'Service', 'Service request text');
        $this->actingAs($contractor, 'contractor');

        $component = Livewire::test(ContractorTicket::class)
            ->call('updateStatus', $ticket->id, 'under_review')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'under_review',
        ]);

        $component->call('updateStatus', $ticket->id, 'resolved')
            ->assertHasNoErrors()
            ->call('updateStatus', $ticket->id, 'pending')
            ->assertHasErrors('status');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
        ]);
    }

    private function contractor(string $id): User
    {
        return User::query()->create([
            'name' => 'Contractor '.$id,
            'employee_id' => $id,
            'email' => strtolower($id).'@example.edu',
            'password' => Hash::make('password'),
            'role' => 'contractor',
        ]);
    }

    private function student(User $contractor, string $id): User
    {
        return User::query()->create([
            'name' => 'Student '.$id,
            'college_id' => $id,
            'email' => strtolower($id).'@example.edu',
            'password' => Hash::make('password'),
            'role' => 'student',
            'contractor_id' => $contractor->id,
            'must_change_password' => false,
        ]);
    }

    private function ticket(User $student, string $category, string $text): Ticket
    {
        return Ticket::query()->create([
            'student_id' => $student->id,
            'category' => strtolower(str_replace(' ', '_', $category)),
            'text' => $text,
        ]);
    }
}
