<?php

namespace Tests\Feature\Audit;

use App\Models\Centre;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\InteractsWithRoles;

/**
 * Regression coverage for the three P0 hard-500 defects fixed in the 2026-06-23 RC remediation.
 *
 * Each route previously returned a hard HTTP 500 (missing view or missing controller method) that
 * the global QueryException handler does NOT catch. These tests pin the fixed responses so the
 * regressions cannot recur silently.
 *   NF-01  GET /dashboard/modern-new      -> 302 (catch redirects to dashboard; view never existed)
 *   NF-04  GET /attendance/report         -> 200 (added report() method -> existing placeholder view)
 *   NF-05  GET /admin/centres/{id}/assets -> 200 (route repointed to the live assetParents method)
 */
class P0RegressionTest extends TestCase
{
    use DatabaseTransactions, InteractsWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

        // NF-05 loads a centre via Centre::where('centre_id', $id)->firstOrFail().
        Centre::firstOrCreate(
            ['centre_id' => '01'],
            ['centre_name' => 'Centre Alpha', 'centre_phone' => '+60100000001', 'centre_email' => 'alpha@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
    }

    public function test_nf01_dashboard_modern_new_degrades_to_redirect_not_500(): void
    {
        $response = $this->actingAsAdmin('01')->get(route('dashboard.modern-new'));

        $response->assertStatus(302);
    }

    public function test_nf04_attendance_report_renders_not_500(): void
    {
        $response = $this->actingAsAdmin('01')->get(route('attendance.report'));

        $response->assertStatus(200);
    }

    public function test_nf05_admin_centre_assets_renders_not_500(): void
    {
        $response = $this->actingAsAdmin('01')->get(route('admin.centres.assets', ['id' => '01']));

        $response->assertStatus(200);
    }
}
