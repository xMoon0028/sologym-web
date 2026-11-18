<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        if (!config('services.google.client_id')) {
            return redirect()->route('login')->with('error', 'Google Sign-In is not yet configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in .env.');
        }

        $driver = Socialite::driver('google')
            ->with(['prompt' => 'select_account']);

        return $driver->redirect();
    }

    /**
     * Obtain the user information from Google and authenticate.
     */
    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('error')) {
            $errorMsg = $request->get('error_description') ?: $request->get('error');
            return redirect()->route('login')->with('error', 'Google Sign-In was cancelled or failed: ' . $errorMsg);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            // Fallback to stateless retrieval if session state was lost across external redirect
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
            } catch (\Throwable $statelessEx) {
                \Illuminate\Support\Facades\Log::error('Google OAuth callback failed (stateless fallback also failed): ' . $statelessEx->getMessage(), [
                    'exception' => $statelessEx,
                    'query' => $request->all(),
                ]);
                return redirect()->route('login')->with('error', 'Google authentication failed: ' . $statelessEx->getMessage() . '. Please try again or use email & password sign-in below.');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth callback error: ' . $e->getMessage(), [
                'exception' => $e,
                'query' => $request->all(),
            ]);

            $msg = $e->getMessage();
            if (str_contains($msg, 'redirect_uri_mismatch')) {
                $msg = 'Redirect URI mismatch. Please verify that Authorized Redirect URI in Google Cloud Console matches ' . config('services.google.redirect');
            }

            return redirect()->route('login')->with('error', 'Google authentication failed: ' . $msg . '. Please try again or use email & password sign-in below.');
        }

        $email = strtolower(trim($googleUser->getEmail() ?? ''));
        if (empty($email)) {
            return redirect()->route('login')->with('error', 'Invalid email address returned by Google.');
        }

        $googleId = (string) $googleUser->getId();

        // 1. Find existing user by email or by google_id
        $userByEmail = User::where('email', $email)->first();
        $userByGoogleId = $googleId ? User::where('google_id', $googleId)->first() : null;

        $matchedEmployee = Employee::where('email', $email)->first();
        $adminEmails = config('hris.admin_emails', []);
        $isAdminEmail = in_array(strtolower($email), $adminEmails)
            || ($matchedEmployee && in_array($matchedEmployee->approval_role, ['hr_officer', 'executive_office', 'executive_director', 'it_officer']))
            || ($matchedEmployee && $matchedEmployee->department && (strtoupper($matchedEmployee->department->code ?? '') === 'IT' || stripos($matchedEmployee->department->name ?? '', 'Information Technology') !== false));

        \Illuminate\Support\Facades\Log::info("Google OAuth login callback received for: {$email}", [
            'found_user_by_email' => (bool) $userByEmail,
            'found_employee' => (bool) $matchedEmployee,
            'is_admin' => $isAdminEmail,
        ]);

        // If not found in directory, not registered, and not configured as admin, reject
        if (!$matchedEmployee && !$isAdminEmail && !$userByEmail && !$userByGoogleId) {
            \Illuminate\Support\Facades\Log::warning("Unauthorized Google OAuth login attempt for email: {$email}");
            return redirect()->route('login')->with('error', "The email '{$email}' is not registered in the HRIS directory. Please contact HR or IT administrator.");
        }

        // Handle collision where google_id was previously tied to an old or temporary account
        User::where('google_id', $googleId)->where('email', '!=', $email)->update(['google_id' => null]);

        if ($userByEmail) {
            $user = $userByEmail;
        } elseif ($userByGoogleId) {
            $user = $userByGoogleId;
        } else {
            $user = new User();
            $user->password = Hash::make(Str::random(32));
        }

        // Update user attributes
        $user->name = $googleUser->getName() ?: ($matchedEmployee ? $matchedEmployee->name : ($user->name ?: 'Administrator'));
        $user->email = $email;
        $user->google_id = $googleId;

        $rawAvatar = $googleUser->getAvatar();
        if ($rawAvatar) {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `users` MODIFY `avatar` TEXT NULL");
                $user->avatar = $rawAvatar;
            } catch (\Throwable $e) {
                // Defensive fallback: prevent MySQL 1406 truncation error if column is still varchar(255)
                $user->avatar = substr($rawAvatar, 0, 255);
            }
        }
        $user->save();

        // Ensure system roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'employee']);

        // Assign appropriate roles (all staff accounts get employee role)
        if (!$user->hasRole('employee')) {
            $user->assignRole('employee');
        }

        if ($isAdminEmail && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        // Link employee record if exists
        if ($matchedEmployee) {
            $oldUserId = $matchedEmployee->user_id;
            $matchedEmployee->update([
                'user_id' => $user->id,
                'email' => $email,
            ]);

            if ($oldUserId && $oldUserId !== $user->id) {
                try {
                    $oldUser = User::find($oldUserId);
                    if ($oldUser && !$oldUser->hasRole('admin') && Employee::where('user_id', $oldUser->id)->count() === 0) {
                        $oldUser->delete();
                    }
                } catch (\Throwable $e) {
                    // Safe fallback: never crash login if old user has foreign key records
                }
            }
        } elseif (!$user->employee) {
            $emp = Employee::where('email', $email)->first();
            if ($emp) {
                $emp->update(['user_id' => $user->id]);
            }
        }

        // Sync admin privileges for approval bodies or admin email list
        $user->syncAdminRoleFromApprovalBody();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        // 4. Redirect based on role (clear url.intended completely to prevent any redirect loops)
        session()->forget('url.intended');

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')->with('success', "Welcome, {$user->name}!");
        }

        return redirect()->route('portal.dashboard')->with('success', "Welcome, {$user->name}!");
    }
}
