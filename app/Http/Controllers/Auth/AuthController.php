<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\AuthAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'country' => 'required|string|max:2',
            'password' => 'required|string|min:12|max:128',
            'password_confirmation' => 'required|string|same:password',
            'mobile_number' => 'required|string|max:20',
            'accept_fraud_prevention' => 'required|accepted',
            'accept_marketing' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $normalizedMobile = User::normalizeMobileNumber($request->mobile_number);
            $consentVersions = config('consent.versions');
            $ipAddress = $request->ip();

            $account = Account::create([
                'company_name' => $request->company_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'country' => $request->country,
                'account_type' => 'trial',
                'status' => 'active',
                'terms_accepted_at' => now(),
                'terms_accepted_ip' => $ipAddress,
                'terms_version' => $consentVersions['terms'] ?? '1.0',
                'privacy_accepted_at' => now(),
                'privacy_accepted_ip' => $ipAddress,
                'privacy_version' => $consentVersions['privacy'] ?? '1.0',
                'fraud_consent_at' => now(),
                'fraud_consent_ip' => $ipAddress,
                'fraud_consent_version' => $consentVersions['fraud_prevention'] ?? '1.0',
                'marketing_consent_at' => $request->accept_marketing ? now() : null,
                'marketing_consent_ip' => $request->accept_marketing ? $ipAddress : null,
                'signup_ip_address' => $ipAddress,
                'signup_referrer' => $request->header('Referer'),
                'signup_details_complete' => true,
            ]);

            $user = User::create([
                'tenant_id' => $account->id,
                'user_type' => 'customer',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'job_title' => $request->job_title,
                'role' => 'owner',
                'status' => 'active',
                'mobile_number' => $normalizedMobile,
                'mobile_verified_at' => now(),
                'mfa_enabled' => true,
                'email_verified_at' => now(),
            ]);

            DB::table('account_settings')->insert([
                'account_id' => $account->id,
                'timezone' => 'Europe/London',
                'currency' => 'GBP',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('account_flags')->insert([
                'account_id' => $account->id,
                'fraud_risk_level' => 'low',
                'fraud_score' => 0,
                'payment_status' => 'current',
                'daily_message_limit' => 1000,
                'api_rate_limit_per_minute' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $account->refresh();

            AuthAuditLog::logEvent([
                'actor_type' => 'customer_user',
                'actor_id' => $user->id,
                'actor_email' => $user->email,
                'tenant_id' => $account->id,
                'event_type' => 'signup_completed',
                'result' => 'success',
                'ip_address' => $ipAddress,
                'metadata' => [
                    'account_number' => $account->account_number,
                    'marketing_consent' => $request->accept_marketing ?? false,
                ],
            ]);

            DB::commit();

            Auth::login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully.',
                'data' => [
                    'account_id' => $account->id,
                    'user_id' => $user->id,
                    'account_number' => $account->account_number,
                    'email' => $user->email,
                    'redirect' => '/?onboarding=complete',
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Signup error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during signup. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            Auth::login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'redirect' => '/',
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during login. Please try again.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function me(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $account = $user->account;

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                ],
                'account' => [
                    'id' => $account->id,
                    'account_number' => $account->account_number,
                    'company_name' => $account->company_name,
                    'status' => $account->status,
                ],
            ]
        ], 200);
    }
}
