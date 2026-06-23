<?php

namespace Tests\Feature\Staff;

use Tests\TestCase;
use Tests\Traits\InteractsWithRoles;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TeacherScheduleTest extends TestCase
{
    use DatabaseTransactions, InteractsWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Centre::firstOrCreate(
            ['centre_id' => '01'],
            [
                'centre_name'     => 'Test Centre',
                'centre_phone'    => '+60100000001',
                'centre_email'    => 'test@centre.com',
                'centre_capacity' => 50,
                'centre_status'   => 'active',
                'is_active'       => true,
            ]
        );
    }

    public function test_teacher_schedule_route_does_not_500_when_classes_feature_not_implemented(): void
    {
        $response = $this->actingAsTeacher('01')
            ->get(route('teacher.schedule'));

        $this->assertNotEquals(500, $response->getStatusCode(),
            'GET /teacher/schedule must not 500 — classes table is not implemented'
        );
    }

    public function test_teacher_schedule_redirects_gracefully(): void
    {
        $response = $this->actingAsTeacher('01')
            ->get(route('teacher.schedule'));

        $response->assertRedirect();
    }

    public function test_teacher_schedule_shows_error_in_session_when_classes_not_implemented(): void
    {
        $response = $this->actingAsTeacher('01')
            ->get(route('teacher.schedule'));

        // Handler catches QueryException and flashes error to session
        $response->assertSessionHas('error');
    }
}
