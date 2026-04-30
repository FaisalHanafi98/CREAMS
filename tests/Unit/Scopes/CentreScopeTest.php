<?php

namespace Tests\Unit\Scopes;

use Tests\TestCase;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Asset;
use App\Models\Scopes\CentreScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CentreScopeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_role_bypasses_centre_scope()
    {
        session(['role' => 'admin', 'centre_id' => '01']);

        $query = Trainee::query()->toSql();

        // Admin queries should NOT contain centre_id filtering
        $this->assertStringNotContainsString(
            'centre_id',
            $query,
            'Admin queries must not be filtered by centre_id'
        );
    }

    public function test_non_admin_role_applies_centre_scope()
    {
        session(['role' => 'teacher', 'centre_id' => '01']);

        $query = Trainee::query()->toSql();

        $this->assertStringContainsString(
            'centre_id',
            $query,
            'Non-admin queries must be filtered by centre_id'
        );
    }

    public function test_centre_scope_applies_to_activity_model()
    {
        session(['role' => 'supervisor', 'centre_id' => '02']);

        $query = Activity::query()->toSql();

        $this->assertStringContainsString(
            'centre_id',
            $query,
            'Activity queries for non-admins must include centre_id filter'
        );
    }

    public function test_centre_scope_applies_to_asset_model()
    {
        session(['role' => 'teacher', 'centre_id' => '03']);

        $query = Asset::query()->toSql();

        $this->assertStringContainsString(
            'centre_id',
            $query,
            'Asset queries for non-admins must include centre_id filter'
        );
    }

    public function test_scope_uses_table_prefix_to_avoid_ambiguous_columns()
    {
        session(['role' => 'teacher', 'centre_id' => '01']);

        $query = Trainee::query()->toSql();

        // Must use table-qualified column name (trainees.centre_id, not bare centre_id)
        $this->assertStringContainsString(
            'trainees',
            $query,
            'CentreScope must qualify centre_id with table name to prevent ambiguity in joins'
        );
    }

    public function test_scope_skipped_when_no_centre_id_in_session()
    {
        session(['role' => 'teacher', 'centre_id' => null]);

        $query = Trainee::query()->toSql();

        // No centre_id in session — scope should not add a where clause
        $this->assertStringNotContainsString(
            'centre_id',
            $query,
            'Scope should not filter when session has no centre_id'
        );
    }

    public function test_scope_can_be_removed_with_without_global_scope()
    {
        session(['role' => 'teacher', 'centre_id' => '01']);

        $query = Trainee::withoutGlobalScope(CentreScope::class)->toSql();

        $this->assertStringNotContainsString(
            'centre_id',
            $query,
            'withoutGlobalScope must remove the centre filtering'
        );
    }
}
