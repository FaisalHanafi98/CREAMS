<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Users;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Display forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPasswordForm()
    {
        Log::info('Forgot password form accessed');
        return view('auth.forgot-password');
    }

    /**
     * Process forgot password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitForgotPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        Log::info('Password reset requested for email', ['email' => $email]);
        
        $user = $this->findUserByEmail($email);
        
        if (!$user) {
            Log::warning('Password reset failed: User not found', ['email' => $email]);
            return back()->with('error', 'We can\'t find a user with that email address.');
        }

        Log::info('User found for password reset', [
            'email' => $email,
            'user_id' => $user->id,
            'role' => $user->role
        ]);

        // Delete any existing tokens for this email
        DB::table('password_resets')->where('email', $email)->delete();

        // Create a new token
        $token = Str::random(64);
        
        // Store token
        DB::table('password_resets')->insert([
            'email' => $email, 
            'token' => $token, 
            'created_at' => Carbon::now()
        ]);
        
        // Build reset URL with full URL
        $resetUrl = url('reset-password/' . $token);
        
        Log::info('Password reset token created', [
            'email' => $email,
            'token_length' => strlen($token),
            'reset_url' => $resetUrl
        ]);
        
        // Prepare mail data
        $mailData = [
            'token' => $token,
            'name' => $user->name,
            'resetUrl' => $resetUrl
        ];
        
        // Send reset email
        try {
            Mail::to($email)->send(new PasswordResetEmail($mailData));
            
            Log::info('Password reset email sent successfully', ['email' => $email]);
            return back()->with('success', 'We have emailed your password reset link. Please check your inbox.');
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Could not send reset email. Please try again later.');
        }
    }

    /**
     * Find user by email in users table.
     *
     * @param string $email
     * @return mixed
     */
    private function findUserByEmail($email)
    {
        $user = Users::where('email', $email)->first();
        
        if ($user) {
            Log::info('User found', [
                'user_id' => $user->id,
                'email' => $email,
                'role' => $user->role
            ]);
            return $user;
        }
        
        Log::warning('User not found', ['email' => $email]);
        return null;
    }

    /**
     * Show reset password form.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetPasswordForm($token) 
    { 
        Log::info('Reset password form accessed', ['token_length' => strlen($token)]);
        
        // Get email from token
        $tokenData = DB::table('password_resets')
            ->where('token', $token)
            ->first();
            
        if (!$tokenData) {
            Log::warning('Invalid password reset token accessed', ['token_length' => strlen($token)]);
            return redirect()->route('auth.forgotpassword')
                ->with('error', 'Invalid password reset token!');
        }
        
        // Check if token is expired (tokens valid for 60 minutes)
        $createdAt = Carbon::parse($tokenData->created_at);
        $minutesElapsed = Carbon::now()->diffInMinutes($createdAt);
        
        Log::info('Token age check', [
            'created_at' => $createdAt->toDateTimeString(),
            'minutes_elapsed' => $minutesElapsed,
            'is_expired' => $minutesElapsed > 60
        ]);
        
        if ($minutesElapsed > 60) {
            DB::table('password_resets')->where('token', $token)->delete();
            Log::warning('Expired password reset token', [
                'token_length' => strlen($token), 
                'minutes_elapsed' => $minutesElapsed
            ]);
            return redirect()->route('auth.forgotpassword')
                ->with('error', 'Password reset token has expired. Please request a new one.');
        }
            
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $tokenData->email
        ]);
    }

    /**
     * Process password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitResetPasswordForm(Request $request)
    {
        Log::info('Password reset form submitted', ['email' => $request->email]);
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:5|confirmed|regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).{5,}$/',
            'password_confirmation' => 'required',
            'token' => 'required'
        ]);

        // Find the token in database
        $passwordReset = DB::table('password_resets')
                            ->where('email', $request->email)
                            ->where('token', $request->token)
                            ->first();

        if (!$passwordReset) {
            Log::warning('Invalid token or email in password reset submission', [
                'email' => $request->email,
                'token_length' => strlen($request->token)
            ]);
            return back()->with('error', 'Invalid token or email!');
        }

        // Check if token is expired (more than 60 minutes old)
        $tokenCreatedAt = Carbon::parse($passwordReset->created_at);
        $minutesElapsed = Carbon::now()->diffInMinutes($tokenCreatedAt);
        
        Log::info('Token validation for password reset', [
            'email' => $request->email,
            'token_created_at' => $tokenCreatedAt->toDateTimeString(),
            'minutes_elapsed' => $minutesElapsed,
            'is_expired' => $minutesElapsed > 60
        ]);
        
        if ($minutesElapsed > 60) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            Log::warning('Expired token used in password reset', [
                'email' => $request->email,
                'minutes_elapsed' => $minutesElapsed
            ]);
            return back()->with('error', 'Password reset link has expired.');
        }

        // Find the user
        $user = $this->findUserByEmail($request->email);
        
        if (!$user) {
            Log::warning('User not found during password reset', ['email' => $request->email]);
            return back()->with('error', 'We can\'t find a user with that email address.');
        }
        
        Log::info('Updating password for user', [
            'email' => $request->email,
            'user_id' => $user->id,
            'role' => $user->role
        ]);
        
        // Update password
        try {
            $user->password = Hash::make($request->password);
            $user->save();
            
            // Delete token
            DB::table('password_resets')->where('email', $request->email)->delete();
            
            Log::info('Password reset successful', [
                'email' => $request->email,
                'user_id' => $user->id
            ]);
            
            return redirect()->route('auth.loginpage')
                ->with('success', 'Your password has been changed successfully! You can now log in with your new password.');
        } catch (\Exception $e) {
            Log::error('Error updating password', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'An error occurred while updating your password. Please try again.');
        }
    }
}