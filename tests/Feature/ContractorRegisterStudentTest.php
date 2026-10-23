<?php

namespace Tests\Feature;

use App\Livewire\ContractorRegisterStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ContractorRegisterStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_can_register_a_student_with_mess_profile(): void
    {
        $contractor = $this->contractor();
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorRegisterStudent::class)
            ->set('name', 'Asha Rao')
            ->set('collegeId', 'STU1001')
            ->set('email', 'asha@college.edu')
            ->set('password', 'secure-password')
            ->set('passwordConfirmation', 'secure-password')
            ->set('hostel', 'Block A')
            ->set('dietaryPreference', 'veg')
            ->set('dietaryTags', ['veg', 'jain'])
            ->set('messId', '7')
            ->call('register')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Student registered successfully.')
            ->assertSet('password', '');

        $student = User::query()->where('college_id', 'STU1001')->firstOrFail();

        $this->assertSame('student', $student->role);
        $this->assertSame('Block A', $student->hostel);
        $this->assertSame('veg', $student->dietary_pref);
        $this->assertSame(['veg', 'jain'], $student->dietary_tags);
        $this->assertSame([], $student->weight_log);
        $this->assertSame(7, $student->mess_id);
        $this->assertTrue(Hash::check('secure-password', $student->password));
    }

    public function test_duplicate_student_identifiers_are_rejected(): void
    {
        $contractor = $this->contractor();
        $this->actingAs($contractor, 'contractor');

        User::query()->create([
            'name' => 'Existing Student',
            'college_id' => 'STU1001',
            'email' => 'existing@college.edu',
            'password' => 'secure-password',
            'role' => 'student',
        ]);

        Livewire::test(ContractorRegisterStudent::class)
            ->set('name', 'New Student')
            ->set('collegeId', 'STU1001')
            ->set('email', 'existing@college.edu')
            ->set('password', 'secure-password')
            ->set('passwordConfirmation', 'secure-password')
            ->call('register')
            ->assertHasErrors(['collegeId', 'email']);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_student_cannot_open_contractor_student_registration(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'college_id' => 'STU1001',
            'email' => 'student@college.edu',
            'password' => 'secure-password',
            'role' => 'student',
        ]);

        $this->actingAs($student, 'contractor')
            ->get(route('contractor.students.create'))
            ->assertForbidden();
    }

    public function test_registration_page_requires_contractor_authentication(): void
    {
        $this->get(route('contractor.students.create'))->assertRedirect();
    }

    public function test_student_list_shows_only_student_accounts(): void
    {
        $contractor = $this->contractor();
        $student = User::query()->create([
            'name' => 'Asha Rao',
            'college_id' => 'STU1001',
            'email' => 'asha@college.edu',
            'password' => 'secure-password',
            'role' => 'student',
            'hostel' => 'Block A',
            'dietary_pref' => 'veg',
        ]);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorRegisterStudent::class)
            ->assertSee($student->name)
            ->assertSee($student->college_id)
            ->assertSee($student->email)
            ->assertDontSee($contractor->email);
    }

    public function test_student_list_can_be_searched_by_college_id(): void
    {
        $contractor = $this->contractor();
        User::query()->create([
            'name' => 'Asha Rao',
            'college_id' => 'STU1001',
            'email' => 'asha@college.edu',
            'password' => 'secure-password',
            'role' => 'student',
        ]);
        User::query()->create([
            'name' => 'Ravi Kumar',
            'college_id' => 'STU2002',
            'email' => 'ravi@college.edu',
            'password' => 'secure-password',
            'role' => 'student',
        ]);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorRegisterStudent::class)
            ->set('search', 'STU2002')
            ->assertSee('Ravi Kumar')
            ->assertDontSee('Asha Rao');
    }

    private function contractor(): User
    {
        return User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR001',
            'email' => 'contractor@college.edu',
            'password' => 'secure-password',
            'role' => 'contractor',
        ]);
    }
}
