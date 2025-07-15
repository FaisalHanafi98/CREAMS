<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Users;

class TeachersHomeControllerSupervisor extends Controller
{
    public function index()
    {
        $users = Users::query()
            ->select('id', 'user_first_name', 'user_last_name', 'role', 'user_activity_1', 'user_activity_2', 'avatar')
            ->orderBy('role', 'asc')
            ->orderBy('user_activity_1', 'asc')
            ->get();

        return view('teachershomesupervisor', ['users' => $users]);
    }

    public function updateuserpage(Request $request, $id)
    {
        $user = Users::findOrFail($id);

        return view('updateuserprofile', ['user' => $user]);
    }
}
