<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainees;

class TraineeRegistrationController extends Controller
{
    public function index()
    {
        return view('traineesregistration');
    }

    public function store(Request $request)
    {
        $request->validate([
            'trainee_first_name' => 'required',
            'trainee_last_name' => 'required',
            'trainee_email' => 'required|email',
            'trainee_phone_number' => 'required',
            'trainee_date_of_birth' => 'required',
            'trainee_avatar' => 'nullable|image|max:2048',
            'centre_name' => 'required',
            'trainee_condition' => 'required',
        ]);

        $trainee = new Trainees();
        $trainee->trainee_first_name = $request->input('trainee_first_name');
        $trainee->trainee_last_name = $request->input('trainee_last_name');
        $trainee->trainee_email = $request->input('trainee_email');
        $trainee->trainee_phone_number = $request->input('trainee_phone_number');
        $trainee->trainee_date_of_birth = $request->input('trainee_date_of_birth');
        $trainee->centre_name = $request->input('centre_name');
        $trainee->trainee_condition = $request->input('trainee_condition');

        if ($request->hasFile('trainee_avatar')) {
            $avatar = $request->file('trainee_avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path('avatars'), $avatarName);
            $trainee->trainee_avatar = $avatarName;
        }

        $trainee->save();

        return redirect()->route('traineeshome')->with('success', 'Trainee registered successfully.');
    }
}
