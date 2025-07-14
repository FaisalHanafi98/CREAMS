<?php

namespace App\Extensions;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Session;

class MultipleUserGuard extends SessionGuard
{
    public function __construct($name, UserProvider $provider, $session, $request = null)
    {
        parent::__construct($name, $provider, $session, $request);
    }

    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        // If we have session ID, use it to fetch the user
        if (Session::has('id') && Session::has('role')) {
            $id = Session::get('id');
            $role = Session::get('role');
            
            switch ($role) {
                case 'admin':
                    $model = \App\Models\Admins::class;
                    break;
                case 'supervisor':
                    $model = \App\Models\Supervisors::class;
                    break;
                case 'teacher':
                    $model = \App\Models\Teachers::class;
                    break;
                case 'ajk':
                    $model = \App\Models\AJKs::class;
                    break;
                default:
                    return null;
            }
            
            $this->user = $model::find($id);
            
            return $this->user;
        }
        
        return null;
    }
}