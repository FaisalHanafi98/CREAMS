<?php

namespace Tests\Feature\Asset;

use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use App\Models\Centre;
use App\Models\User;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    private Centre $centre;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->centre = Centre::firstOrCreate(
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

        // Use DB directly — AssetCategory fillable uses 'name' but column is 'category_name'
        $catId = DB::table('asset_categories')->where('category_name', 'Test Equipment')->value('id');
        if (!$catId) {
            $catId = DB::table('asset_categories')->insertGetId([
                'category_name'        => 'Test Equipment',
                'category_description' => 'Test category for UAT',
                'is_active'            => true,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }
        $this->category = (object)['id' => $catId];
    }

    // --- List ---

    public function test_admin_can_view_asset_list(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $response = $this->actingAs($user)->get('/assets');
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->isRedirect(),
            'Asset list should be accessible (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_supervisor_can_view_asset_list(): void
    {
        $user = User::factory()->supervisor()->create(['centre_id' => '01']);
        $response = $this->actingAs($user)->get('/assets');
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->isRedirect(),
            'Supervisor should access asset list (got ' . $response->getStatusCode() . ')'
        );
    }

    // --- Show with missing optional relationships ---

    public function test_admin_can_view_asset_show(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $asset = Asset::create([
            'asset_tag' => 'TST-' . uniqid(), 'asset_name' => 'Show Test Asset',
            'category_id' => $this->category->id, 'centre_id' => '01',
            'condition' => 'good', 'status' => 'available', 'is_active' => true,
        ]);
        $response = $this->actingAs($user)->get('/assets/' . $asset->id);
        $response->assertStatus(200);
    }

    public function test_asset_show_does_not_500_with_null_optional_relationships(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        // category_id is NOT NULL — use valid one. Test null optional fields instead.
        $asset = Asset::create([
            'asset_tag' => 'NULL-' . uniqid(), 'asset_name' => 'Null Optional Rel Asset',
            'category_id' => $this->category->id, 'centre_id' => '01',
            'type_id' => null,           // optional parent asset — null is allowed
            'location_id' => null,       // optional location — null is allowed
            'assigned_to_user' => null,  // optional assignment — null is allowed
            'condition' => 'fair', 'status' => 'available', 'is_active' => true,
        ]);
        $response = $this->actingAs($user)->get('/assets/' . $asset->id);
        $this->assertNotEquals(500, $response->getStatusCode(),
            'Null optional relationships must not cause 500 (got ' . $response->getStatusCode() . ')');
    }

    public function test_asset_show_nonexistent_returns_not_500(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $response = $this->actingAs($user)->get('/assets/999999');
        $this->assertNotEquals(500, $response->getStatusCode(), 'Missing asset must not 500');
    }

    // --- Edit ---

    public function test_admin_can_view_asset_edit(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $asset = Asset::create([
            'asset_tag' => 'EDIT-' . uniqid(), 'asset_name' => 'Edit Test Asset',
            'category_id' => $this->category->id, 'centre_id' => '01',
            'condition' => 'good', 'status' => 'available', 'is_active' => true,
        ]);
        $response = $this->actingAs($user)->get('/assets/' . $asset->id . '/edit');
        $response->assertStatus(200);
    }

    // --- Create ---

    public function test_admin_can_view_create_asset_page(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $response = $this->actingAs($user)->get('/assets/create');
        $response->assertStatus(200);
    }

    public function test_supervisor_cannot_access_asset_create(): void
    {
        $user = User::factory()->supervisor()->create(['centre_id' => '01']);
        $response = $this->actingAs($user)->get('/assets/create');
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_create_route_not_shadowed_by_wildcard(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $response = $this->actingAs($user)->get('/assets/create');
        $response->assertStatus(200);
    }

    // --- Subpages ---

    public function test_admin_can_view_asset_reports(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $this->actingAs($user)->get('/assets/reports')->assertStatus(200);
    }

    public function test_admin_can_view_asset_maintenance(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $this->actingAs($user)->get('/assets/maintenance')->assertStatus(200);
    }

    public function test_admin_can_view_asset_movements(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);
        $response = $this->actingAs($user)->get('/assets/movements');
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->isRedirect(),
            'Asset movements should load (got ' . $response->getStatusCode() . ')'
        );
    }
}
