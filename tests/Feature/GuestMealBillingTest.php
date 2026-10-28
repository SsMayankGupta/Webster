<?php

namespace Tests\Feature;

use App\Livewire\ContractorGuestQueue;
use App\Livewire\ContractorLeave;
use App\Livewire\ContractorStudents;
use App\Livewire\GuestPassRequest;
use App\Livewire\StudentPortal;
use App\Models\ExtraMealCharge;
use App\Models\ExtraMealCollection;
use App\Models\HeadcountLedger;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuestMealBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_request_a_group_pass_for_a_meal_they_have_already_booked(): void
    {
        $contractor = $this->contractor('CON-GUEST-1');
        $student = $this->student($contractor, 'STU-GUEST-1');
        $menu = $this->menu();
        MealBooking::query()->create([
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);
        $this->actingAs($student, 'student');

        Livewire::test(GuestPassRequest::class)
            ->set('selectedMenus', [$menu->id])
            ->set("guestCounts.{$menu->id}", 3)
            ->call('submit')
            ->assertHasNoErrors();

        $guestBooking = MealBooking::query()
            ->where('is_guest_pass', true)
            ->sole();
        $this->assertSame(3, $guestBooking->guest_count);
        $this->assertSame($student->id, $guestBooking->primary_student_id);
        $this->assertSame('pending_approval', $guestBooking->status);
    }

    public function test_contractor_approval_sets_a_per_guest_charge_and_updates_headcount(): void
    {
        $contractor = $this->contractor('CON-GUEST-2');
        $otherContractor = $this->contractor('CON-GUEST-3');
        $student = $this->student($contractor, 'STU-GUEST-2');
        $otherStudent = $this->student($otherContractor, 'STU-GUEST-3');
        $menu = $this->menu();
        $booking = $this->guestBooking($student, $menu, 2);
        $otherBooking = $this->guestBooking($otherStudent, $menu, 1);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorGuestQueue::class)
            ->assertSee($student->name)
            ->assertDontSee($otherStudent->name)
            ->set("guestPrices.{$booking->id}", '45.50')
            ->call('approve', $booking->id)
            ->assertHasNoErrors();

        $this->assertSame('booked', $booking->fresh()->status);
        $this->assertDatabaseHas('extra_meal_charges', [
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'approved_by' => $contractor->id,
            'guest_count' => 2,
            'price_per_guest_paise' => 4550,
            'amount_paise' => 9100,
        ]);
        $this->assertSame('pending_approval', $otherBooking->fresh()->status);
        $this->assertSame(2, HeadcountLedger::query()->where('menu_id', $menu->id)->latest('id')->value('guest_count'));
    }

    public function test_rejected_guest_request_does_not_create_a_charge(): void
    {
        $contractor = $this->contractor('CON-GUEST-4');
        $student = $this->student($contractor, 'STU-GUEST-4');
        $booking = $this->guestBooking($student, $this->menu(), 2);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorGuestQueue::class)
            ->call('reject', $booking->id)
            ->assertHasNoErrors();

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertDatabaseCount('extra_meal_charges', 0);
    }

    public function test_guest_meal_collections_cannot_exceed_the_due_balance(): void
    {
        $contractor = $this->contractor('CON-GUEST-5');
        $student = $this->student($contractor, 'STU-GUEST-5');
        $booking = $this->guestBooking($student, $this->menu(), 2, 'booked');
        $this->charge($student, $booking, $contractor, 5000);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set('selectedStudentId', $student->id)
            ->set('extraMealAmount', '100.01')
            ->call('addExtraMealCollection')
            ->assertHasErrors('extraMealAmount')
            ->set('extraMealAmount', '100.00')
            ->call('addExtraMealCollection')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('extra_meal_collections', [
            'student_id' => $student->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => 10000,
        ]);
        $this->assertSame(0, app(\App\Services\ExtraMealBillingService::class)->summary($student)['outstanding_paise']);
    }

    public function test_student_wallet_shows_guest_charges_collections_and_outstanding_balance(): void
    {
        $contractor = $this->contractor('CON-GUEST-6');
        $student = $this->student($contractor, 'STU-GUEST-6');
        $booking = $this->guestBooking($student, $this->menu(), 2, 'booked');
        $this->charge($student, $booking, $contractor, 5000);
        ExtraMealCollection::query()->create([
            'student_id' => $student->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => 3000,
        ]);
        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class)->call('setTab', 'leave');
        $wallet = $component->get('state')['wallet'];

        $this->assertSame(10000, $wallet['extra_meal_charged_paise']);
        $this->assertSame(3000, $wallet['extra_meal_collected_paise']);
        $this->assertSame(7000, $wallet['extra_meal_outstanding_paise']);
        $component->assertSee('Guest meals outstanding')
            ->assertSee('Guest-meal charges and collections')
            ->assertSee('₹100.00');
    }

    public function test_student_cannot_be_deactivated_until_guest_balance_is_collected(): void
    {
        $contractor = $this->contractor('CON-GUEST-7');
        $student = $this->student($contractor, 'STU-GUEST-7');
        $booking = $this->guestBooking($student, $this->menu(), 1, 'booked');
        $this->charge($student, $booking, $contractor, 5000);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorStudents::class)
            ->call('archiveStudent', $student->id)
            ->assertHasErrors('archive');
        $this->assertNull($student->fresh()->archived_at);

        ExtraMealCollection::query()->create([
            'student_id' => $student->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => 5000,
        ]);

        Livewire::test(ContractorStudents::class)
            ->call('archiveStudent', $student->id)
            ->call('permanentlyDeleteStudent', $student->id)
            ->assertHasErrors('deletion');

        $this->assertNotNull($student->fresh()->archived_at);
        $this->assertDatabaseHas('extra_meal_charges', ['booking_id' => $booking->id]);
    }

    private function contractor(string $id): User
    {
        return User::query()->create([
            'name' => 'Contractor '.$id,
            'employee_id' => $id,
            'email' => strtolower($id).'@example.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
    }

    private function student(User $contractor, string $id): User
    {
        return User::query()->create([
            'name' => 'Student '.$id,
            'college_id' => $id,
            'email' => strtolower($id).'@example.edu',
            'password' => 'password',
            'role' => 'student',
            'contractor_id' => $contractor->id,
            'must_change_password' => false,
        ]);
    }

    private function menu(): Menu
    {
        return Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => now()->addDays(2),
            'recipe_text' => 'Guest meal test',
            'status' => 'open',
        ]);
    }

    private function guestBooking(User $student, Menu $menu, int $guestCount, string $status = 'pending_approval'): MealBooking
    {
        return MealBooking::query()->create([
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => $status,
            'is_guest_pass' => true,
            'primary_student_id' => $student->id,
            'guest_count' => $guestCount,
        ]);
    }

    private function charge(User $student, MealBooking $booking, User $contractor, int $pricePerGuestPaise): ExtraMealCharge
    {
        return ExtraMealCharge::query()->create([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
            'approved_by' => $contractor->id,
            'guest_count' => $booking->guest_count,
            'price_per_guest_paise' => $pricePerGuestPaise,
            'amount_paise' => $booking->guest_count * $pricePerGuestPaise,
        ]);
    }
}
