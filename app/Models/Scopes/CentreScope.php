<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CentreScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $role = session('role');
        $centreId = session('centre_id');

        // No session data = no filtering (console commands, queue workers, unauthenticated)
        if (!$role || !$centreId) {
            return;
        }

        // Admins see all centres
        if ($role === 'admin') {
            return;
        }

        $builder->where($model->getTable() . '.centre_id', $centreId);
    }
}
