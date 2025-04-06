<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Trainees;
use Illuminate\Support\Facades\Log;


class TraineeProfileController extends Controller
{
    public function index($id)
    {
        $trainee = Trainees::findOrFail($id);

        return view('traineeprofile', ['trainee' => $trainee]);
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'trainee_avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Example validation for the avatar image
                'trainee_first_name' => 'required|string',
                'trainee_last_name' => 'required|string',
                'trainee_email' => [
                    'required',
                    'email',
                    Rule::unique('trainees')->ignore($id)
                ],
                'trainee_date_of_birth' => 'required|date',
                'centre_name' => 'required|string',
                'trainee_condition' => 'required|string',
            ]);

            // Find the trainee by ID
            $trainee = Trainees::findOrFail($id);

            // Log the original trainee data
            Log::info('Original Trainee Data:', ['trainee' => $trainee]);

            // Update the trainee model with the validated data
            $trainee->trainee_first_name = $validatedData['trainee_first_name'];
            $trainee->trainee_last_name = $validatedData['trainee_last_name'];
            $trainee->trainee_email = $validatedData['trainee_email'];
            $trainee->trainee_date_of_birth = $validatedData['trainee_date_of_birth'];
            $trainee->centre_name = $validatedData['centre_name'];
            $trainee->trainee_condition = $validatedData['trainee_condition'];

            // Handle the avatar image upload if provided
            if ($request->hasFile('trainee_avatar')) {
                $avatar = $request->file('trainee_avatar');
                $avatarPath = $avatar->store('trainee_avatar', 'public');
                $trainee->trainee_avatar = asset('storage/' . $avatarPath);
                $trainee->save();
            }

            


            // Save the updated trainee model
            $trainee->save();

            // Log the updated trainee data
            Log::info('Updated Trainee Data:', ['trainee' => $trainee]);

            // Redirect the user back to the profile page with a success message
            return redirect()->route('traineeprofile', ['id' => $trainee->id])->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            // Log the error message and stack trace
            Log::error('Error updating trainee:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            // Redirect the user back to the profile page with an error message
            return redirect()->route('traineeprofile', ['id' => $id])->with('error', 'Failed to update profile. Please try again.');
        }
    }
}
