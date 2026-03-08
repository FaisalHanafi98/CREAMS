<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Centre;
use App\Models\Activity;
use App\Models\Trainee;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a default centre for testing
        Centre::firstOrCreate(
            ['centre_id' => '01'],
            [
                'centre_name' => 'Test Centre',
                'centre_phone' => '+60123456789',
                'centre_email' => 'test@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );
    }

    // ========================================
    // Role Check Tests
    // ========================================

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $user = User::factory()->admin()->create();
        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_non_admin_role(): void
    {
        $user = User::factory()->teacher()->create();
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_supervisor_returns_true_for_supervisor_role(): void
    {
        $user = User::factory()->supervisor()->create();
        $this->assertTrue($user->isSupervisor());
    }

    public function test_is_supervisor_returns_false_for_non_supervisor_role(): void
    {
        $user = User::factory()->teacher()->create();
        $this->assertFalse($user->isSupervisor());
    }

    public function test_is_teacher_returns_true_for_teacher_role(): void
    {
        $user = User::factory()->teacher()->create();
        $this->assertTrue($user->isTeacher());
    }

    public function test_is_teacher_returns_false_for_non_teacher_role(): void
    {
        $user = User::factory()->admin()->create();
        $this->assertFalse($user->isTeacher());
    }

    public function test_is_ajk_returns_true_for_ajk_role(): void
    {
        $user = User::factory()->ajk()->create();
        $this->assertTrue($user->isAJK());
    }

    public function test_is_ajk_returns_false_for_non_ajk_role(): void
    {
        $user = User::factory()->teacher()->create();
        $this->assertFalse($user->isAJK());
    }

    public function test_has_role_returns_true_for_matching_role(): void
    {
        $user = User::factory()->teacher()->create();
        $this->assertTrue($user->hasRole('teacher'));
    }

    public function test_has_role_returns_false_for_non_matching_role(): void
    {
        $user = User::factory()->teacher()->create();
        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_get_role_returns_correct_role(): void
    {
        $user = User::factory()->supervisor()->create();
        $this->assertEquals('supervisor', $user->getRole());
    }

    // ========================================
    // Role Update Tests
    // ========================================

    public function test_update_role_successfully_updates_valid_role(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->admin()->create();

        $result = $user->updateRole('supervisor', $admin->id);

        $this->assertTrue($result);
        $this->assertEquals('supervisor', $user->fresh()->role);
    }

    public function test_update_role_rejects_invalid_role(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->admin()->create();

        $result = $user->updateRole('invalid_role', $admin->id);

        $this->assertFalse($result);
        $this->assertEquals('teacher', $user->fresh()->role);
    }

    public function test_update_role_accepts_all_valid_roles(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->admin()->create();

        $validRoles = ['admin', 'supervisor', 'teacher', 'ajk'];

        foreach ($validRoles as $role) {
            $result = $user->updateRole($role, $admin->id);
            $this->assertTrue($result, "Role '{$role}' should be valid");
            $this->assertEquals($role, $user->fresh()->role);
        }
    }

    // ========================================
    // Status Update Tests
    // ========================================

    public function test_update_status_successfully_changes_status(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $admin = User::factory()->admin()->create();

        $result = $user->updateStatus('inactive', $admin->id);

        $this->assertTrue($result);
        $this->assertEquals('inactive', $user->fresh()->status);
    }

    // ========================================
    // Centre Assignment Tests
    // ========================================

    public function test_assign_to_centre_successfully_changes_centre(): void
    {
        Centre::firstOrCreate(
            ['centre_id' => '02'],
            [
                'centre_name' => 'Test Centre 2',
                'centre_phone' => '+60123456790',
                'centre_email' => 'test2@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        $user = User::factory()->create(['centre_id' => '01']);
        $admin = User::factory()->admin()->create();

        $result = $user->assignToCentre('02', $admin->id);

        $this->assertTrue($result);
        $this->assertEquals('02', $user->fresh()->centre_id);
    }

    // ========================================
    // Phone Number Tests
    // ========================================

    public function test_set_phone_attribute_normalizes_malaysian_phone(): void
    {
        $user = User::factory()->make();
        $user->phone = '012-345 6789';

        // Phone should be normalized
        $this->assertStringContainsString('601234', $user->phone);
    }

    public function test_get_phone_formatted_attribute_formats_phone(): void
    {
        $user = User::factory()->create(['phone' => '+60123456789']);

        $formatted = $user->phone_formatted;

        // Should return formatted version
        $this->assertNotEmpty($formatted);
    }

    // ========================================
    // Accessor Tests
    // ========================================

    public function test_get_education_info_attribute_returns_formatted_string(): void
    {
        $user = User::factory()->create([
            'education_level' => 'Bachelor',
            'education_specialization' => 'Special Education',
        ]);

        $info = $user->education_info;

        $this->assertStringContainsString('Bachelor', $info);
        $this->assertStringContainsString('Special Education', $info);
    }

    public function test_get_education_info_attribute_returns_not_specified_when_empty(): void
    {
        $user = User::factory()->create([
            'education_level' => null,
            'education_specialization' => null,
        ]);

        $this->assertEquals('Not specified', $user->education_info);
    }

    public function test_get_specialization_attribute_returns_teaching_specialization(): void
    {
        $user = User::factory()->create([
            'teaching_specialization' => 'Autism Support',
            'education_specialization' => 'Psychology',
        ]);

        $this->assertEquals('Autism Support', $user->specialization);
    }

    public function test_get_specialization_attribute_falls_back_to_education(): void
    {
        $user = User::factory()->create([
            'teaching_specialization' => null,
            'education_specialization' => 'Psychology',
        ]);

        $this->assertEquals('Psychology', $user->specialization);
    }

    public function test_get_specialization_attribute_returns_not_specified_when_empty(): void
    {
        $user = User::factory()->create([
            'teaching_specialization' => null,
            'education_specialization' => null,
        ]);

        $this->assertEquals('Not specified', $user->specialization);
    }

    public function test_get_avatar_url_attribute_returns_default_when_no_avatar(): void
    {
        $user = User::factory()->create(['avatar' => null]);

        $url = $user->avatar_url;

        $this->assertStringContainsString('default-avatar', $url);
    }

    // ========================================
    // Teaching Capability Tests
    // ========================================

    public function test_can_teach_returns_true_for_teacher_with_specialization(): void
    {
        $user = User::factory()->teacher()->create([
            'teaching_specialization' => 'Autism Support',
        ]);

        $this->assertTrue($user->canTeach());
    }

    public function test_can_teach_returns_true_for_supervisor_with_specialization(): void
    {
        $user = User::factory()->supervisor()->create([
            'teaching_specialization' => 'Speech Therapy',
        ]);

        $this->assertTrue($user->canTeach());
    }

    public function test_can_teach_returns_false_for_teacher_without_specialization(): void
    {
        $user = User::factory()->teacher()->create([
            'teaching_specialization' => null,
        ]);

        $this->assertFalse($user->canTeach());
    }

    public function test_can_teach_returns_false_for_non_teaching_role(): void
    {
        $user = User::factory()->ajk()->create([
            'teaching_specialization' => 'Autism Support',
        ]);

        $this->assertFalse($user->canTeach());
    }

    // ========================================
    // Relationship Tests
    // ========================================

    public function test_activities_relationship_returns_has_many(): void
    {
        $user = User::factory()->teacher()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $user->activities()
        );
    }

    public function test_trainees_relationship_returns_has_many(): void
    {
        $user = User::factory()->teacher()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $user->trainees()
        );
    }

    public function test_centre_relationship_returns_belongs_to(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $user->centre()
        );
    }

    public function test_centre_relationship_retrieves_correct_centre(): void
    {
        $centre = Centre::where('centre_id', '01')->first();
        $user = User::factory()->create(['centre_id' => '01']);

        $this->assertEquals($centre->id, $user->centre->id);
    }

    // ========================================
    // Static Finder Tests
    // ========================================

    public function test_find_by_email_returns_user_when_exists(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $found = User::findByEmail('test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_email_returns_null_when_not_exists(): void
    {
        $found = User::findByEmail('nonexistent@example.com');

        $this->assertNull($found);
    }

    public function test_find_by_iium_id_returns_user_when_exists(): void
    {
        $user = User::factory()->create(['iium_id' => 'TEST001']);

        $found = User::findByIiumId('test001'); // lowercase

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_iium_id_returns_null_when_not_exists(): void
    {
        $found = User::findByIiumId('NONEXIST');

        $this->assertNull($found);
    }

    public function test_find_by_role_returns_active_users_only(): void
    {
        User::factory()->teacher()->create(['status' => 'active']);
        User::factory()->teacher()->create(['status' => 'inactive']);

        $teachers = User::findByRole('teacher');

        $this->assertCount(1, $teachers);
        $this->assertEquals('active', $teachers->first()->status);
    }

    // ========================================
    // Last Login Tests
    // ========================================

    public function test_update_last_login_sets_timestamp(): void
    {
        $user = User::factory()->create(['last_accessed_at' => null]);

        $user->updateLastLogin();

        $this->assertNotNull($user->fresh()->last_accessed_at);
    }

    // ========================================
    // Boot Event Tests
    // ========================================

    public function test_iium_id_converted_to_uppercase_on_create(): void
    {
        $user = User::factory()->create(['iium_id' => 'test123']);

        $this->assertEquals('TEST123', $user->fresh()->iium_id);
    }

    public function test_iium_id_converted_to_uppercase_on_update(): void
    {
        $user = User::factory()->create(['iium_id' => 'TEST123']);

        $user->iium_id = 'updated456';
        $user->save();

        $this->assertEquals('UPDATED456', $user->fresh()->iium_id);
    }

    // ========================================
    // Qualification Tests
    // ========================================

    // Note: isQualifiedForCategory tests removed — activity_categories table
    // does not exist in current schema. Re-add when table is created.

    // ========================================
    // Suitable Trainees Tests
    // ========================================

    public function test_get_suitable_trainees_filters_by_centre(): void
    {
        $user = User::factory()->teacher()->create(['centre_id' => '01']);

        Trainee::factory()->create([
            'centre_id' => '01',
            'trainee_condition' => 'Autism',
        ]);

        Centre::firstOrCreate(
            ['centre_id' => '02'],
            [
                'centre_name' => 'Test Centre 2',
                'centre_phone' => '+60123456790',
                'centre_email' => 'test2@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        Trainee::factory()->create([
            'centre_id' => '02',
            'trainee_condition' => 'Autism',
        ]);

        $trainees = $user->getSuitableTrainees();

        $this->assertCount(1, $trainees);
        $this->assertEquals('01', $trainees->first()->centre_id);
    }

    public function test_get_suitable_trainees_matches_specialization(): void
    {
        $user = User::factory()->teacher()->create([
            'centre_id' => '01',
            'teaching_specialization' => 'Autism, Down Syndrome',
        ]);

        Trainee::factory()->create([
            'centre_id' => '01',
            'trainee_condition' => 'Autism Spectrum Disorder',
        ]);

        Trainee::factory()->create([
            'centre_id' => '01',
            'trainee_condition' => 'Cerebral Palsy',
        ]);

        $trainees = $user->getSuitableTrainees();

        $this->assertGreaterThanOrEqual(1, $trainees->count());
        $this->assertStringContainsString('Autism', $trainees->first()->trainee_condition);
    }
}
