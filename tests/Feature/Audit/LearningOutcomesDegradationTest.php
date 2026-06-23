<?php

namespace Tests\Feature\Audit;

use App\Models\Centre;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\InteractsWithRoles;

/**
 * Regression coverage for NF-09 (learning-outcomes hard 500 -> defensive guard).
 *
 * LearningOutcome is a non-Eloquent compatibility class, but LearningOutcomeController::index called
 * Eloquent query methods on it (::with(...)->paginate()), throwing an Error the global
 * QueryException handler does NOT catch -> hard HTTP 500. A try/catch (catch \Throwable) now
 * degrades the route to a 302 redirect with an error flash. This pins that graceful behaviour.
 * The learning-outcomes feature itself remains PHANTOM-02 (implement-or-remove is an owner decision);
 * if it is later built, this test should be updated to assert the working 200.
 */
class LearningOutcomesDegradationTest extends TestCase
{
    use DatabaseTransactions, InteractsWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

        Centre::firstOrCreate(
            ['centre_id' => '01'],
            ['centre_name' => 'Centre Alpha', 'centre_phone' => '+60100000001', 'centre_email' => 'alpha@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
    }

    public function test_learning_outcomes_index_degrades_gracefully_not_500(): void
    {
        $response = $this->actingAsAdmin('01')->get(route('activities.learning-outcomes.index'));

        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }
}
