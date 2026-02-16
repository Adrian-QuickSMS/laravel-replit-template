<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\AdminUser;
use App\Services\Admin\AdminAuditService;
use App\Services\Admin\AdminRbacService;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if ($this->isAdminAuthenticated()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login', [
            'page_title' => 'Admin Login'
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $email = strtolower($request->email);

        if (!str_ends_with($email, '@quicksms.com')) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Invalid credentials'], 422);
            }
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput(['email' => $request->email]);
        }

        $adminUser = $this->findAdminUser($email);

        if (!$adminUser) {
            AdminAuditService::log('admin_login_failed', [
                'email' => $email,
                'reason' => 'user_not_found'
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Invalid credentials'], 422);
            }
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput(['email' => $request->email]);
        }

        if ($adminUser->status !== 'active') {
            AdminAuditService::log('admin_login_failed', [
                'email' => $email,
                'reason' => 'account_inactive',
                'status' => $adminUser->status
            ]);

            $msg = 'Account is not active';
            if ($request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }
            return back()->withErrors(['email' => $msg])->withInput(['email' => $request->email]);
        }

        if ($adminUser->isLocked()) {
            AdminAuditService::log('admin_login_failed', [
                'email' => $email,
                'reason' => 'account_locked'
            ]);

            $msg = 'Account is temporarily locked. Try again later.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }
            return back()->withErrors(['email' => $msg])->withInput(['email' => $request->email]);
        }

        if (!Hash::check($request->password, $adminUser->password)) {
            $adminUser->incrementFailedLogins();

            AdminAuditService::log('admin_login_failed', [
                'email' => $email,
                'reason' => 'invalid_password'
            ]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Invalid credentials'], 422);
            }
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput(['email' => $request->email]);
        }

        $adminUser->recordLogin($request->ip());

        $mfaRequired = config('admin.mfa.required', true);
        $mfaEnabled = $adminUser->hasMfaEnabled();

        session()->put('admin_auth', [
            'admin_id' => $adminUser->id,
            'email' => $adminUser->email,
            'name' => $adminUser->full_name,
            'role' => $adminUser->role,
            'authenticated' => true,
            'mfa_verified' => false,
            'mfa_setup_required' => $mfaRequired && !$mfaEnabled,
            'mfa_enabled' => $mfaEnabled,
            'mfa_method' => $adminUser->mfa_method,
            'has_phone' => !empty($adminUser->phone),
            'last_activity' => now()->timestamp,
            'login_at' => now()->toIso8601String(),
        ]);

        AdminAuditService::log('admin_login_success', [
            'email' => $adminUser->email,
            'role' => $adminUser->role
        ]);

        if ($request->wantsJson()) {
            if ($mfaRequired) {
                if (!$mfaEnabled) {
                    return response()->json([
                        'mfa_required' => true,
                        'mfa_setup_required' => true,
                        'mfa_method' => null,
                        'has_phone' => !empty($adminUser->phone),
                        'redirect' => route('admin.mfa.setup'),
                    ]);
                }
                return response()->json([
                    'mfa_required' => true,
                    'mfa_method' => $adminUser->mfa_method,
                    'has_phone' => !empty($adminUser->phone),
                    'mfa_setup_required' => false,
                ]);
            }

            session()->put('admin_auth.mfa_verified', true);

            if ($adminUser->needsPasswordChange() || $adminUser->force_password_change) {
                return response()->json(['redirect' => route('admin.password.change')]);
            }

            return response()->json(['redirect' => route('admin.dashboard')]);
        }

        if ($mfaRequired) {
            if (!$mfaEnabled) {
                return redirect()->route('admin.mfa.setup');
            }
            return redirect()->route('admin.mfa.verify');
        }

        session()->put('admin_auth.mfa_verified', true);

        if ($adminUser->needsPasswordChange() || $adminUser->force_password_change) {
            return redirect()->route('admin.password.change');
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        AdminAuditService::log('admin_logout', []);

        session()->forget('admin_auth');
        session()->forget('admin_impersonation');

        return redirect()->route('admin.login')->with('success', 'You have been logged out');
    }

    public function showMfaSetup()
    {
        $adminSession = session('admin_auth');

        if (!$adminSession || !isset($adminSession['mfa_setup_required']) || !$adminSession['mfa_setup_required']) {
            return redirect()->route('admin.dashboard');
        }

        $secret = $this->generateMfaSecret();
        session()->put('admin_mfa_setup_secret', $secret);

        $qrCodeUrl = $this->generateQrCodeUrl($adminSession['email'], $secret);

        return view('admin.auth.mfa-setup', [
            'page_title' => 'Setup MFA',
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl
        ]);
    }

    public function completeMfaSetup(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $secret = session('admin_mfa_setup_secret');

        if (!$secret) {
            return redirect()->route('admin.mfa.setup')->withErrors(['code' => 'Session expired. Please try again.']);
        }

        if (!$this->verifyTotp($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        $adminSession = session('admin_auth');
        $adminSession['mfa_verified'] = true;
        $adminSession['mfa_setup_required'] = false;
        $adminSession['mfa_enabled'] = true;
        $adminSession['mfa_method'] = 'authenticator';
        session()->put('admin_auth', $adminSession);
        session()->forget('admin_mfa_setup_secret');

        $adminUser = AdminUser::find($adminSession['admin_id']);
        if ($adminUser) {
            $adminUser->enableMfa($secret);
            $adminUser->update(['mfa_method' => 'authenticator']);
        }

        AdminAuditService::log('admin_mfa_verified', [
            'setup' => true,
            'method' => 'authenticator',
            'admin_id' => $adminSession['admin_id'] ?? null
        ]);

        if ($adminUser && ($adminUser->needsPasswordChange() || $adminUser->force_password_change)) {
            return redirect()->route('admin.password.change')->with('success', 'MFA has been enabled. Please change your password.');
        }

        return redirect()->route('admin.dashboard')->with('success', 'MFA has been enabled for your account');
    }

    public function skipMfaSetup(Request $request)
    {
        if (config('app.env') === 'production') {
            abort(403, 'MFA skip is not allowed in production');
        }

        $adminSession = session('admin_auth');

        if (!$adminSession) {
            return redirect()->route('admin.login');
        }

        $adminSession['mfa_verified'] = true;
        $adminSession['mfa_setup_required'] = false;
        $adminSession['mfa_enabled'] = false;
        session()->put('admin_auth', $adminSession);
        session()->forget('admin_mfa_setup_secret');

        AdminAuditService::log('admin_mfa_skipped_dev', [
            'admin_id' => $adminSession['admin_id'] ?? null,
            'reason' => 'development_bypass'
        ]);

        return redirect()->route('admin.dashboard')->with('warning', 'MFA skipped for development. Enable MFA before going to production.');
    }

    public function showMfaVerify()
    {
        $adminSession = session('admin_auth');

        if (!$adminSession || !isset($adminSession['authenticated']) || $adminSession['authenticated'] !== true) {
            return redirect()->route('admin.login');
        }

        if (isset($adminSession['mfa_verified']) && $adminSession['mfa_verified'] === true) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.mfa-verify', [
            'page_title' => 'Verify MFA'
        ]);
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $adminSession = session('admin_auth');

        if (!$adminSession) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Session expired'], 401);
            }
            return redirect()->route('admin.login');
        }

        $adminUser = AdminUser::find($adminSession['admin_id']);
        $mfaSecret = $adminUser?->getMfaSecret();

        if (!$mfaSecret) {
            AdminAuditService::log('admin_mfa_failed', [
                'reason' => 'no_mfa_secret'
            ]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'MFA configuration error. Please contact administrator.'], 422);
            }
            return back()->withErrors(['code' => 'MFA configuration error. Please contact administrator.']);
        }

        if (!$this->verifyTotp($mfaSecret, $request->code)) {
            AdminAuditService::log('admin_mfa_failed', [
                'reason' => 'invalid_code'
            ]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Invalid verification code'], 422);
            }
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        $adminSession['mfa_verified'] = true;
        session()->put('admin_auth', $adminSession);

        AdminAuditService::log('admin_mfa_verified', ['method' => 'authenticator']);

        if ($request->wantsJson()) {
            if ($adminUser && ($adminUser->needsPasswordChange() || $adminUser->force_password_change)) {
                return response()->json(['redirect' => route('admin.password.change')]);
            }
            return response()->json(['redirect' => route('admin.dashboard')]);
        }

        if ($adminUser && ($adminUser->needsPasswordChange() || $adminUser->force_password_change)) {
            return redirect()->route('admin.password.change');
        }

        return redirect()->route('admin.dashboard');
    }

    // =====================================================
    // SMS MFA
    // =====================================================

    public function sendSmsMfa(Request $request)
    {
        $adminSession = session('admin_auth');
        if (!$adminSession || !$adminSession['authenticated']) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $adminUser = AdminUser::find($adminSession['admin_id']);
        if (!$adminUser || !$adminUser->phone) {
            return response()->json(['error' => 'No phone number configured'], 422);
        }

        $code = $adminUser->generateSmsMfaCode();

        // TODO: Send SMS via your SMS gateway
        Log::info('[DEV ONLY] Admin SMS MFA code', ['code' => $code, 'email' => $adminUser->email]);

        return response()->json([
            'success' => true,
            'masked_phone' => $adminUser->masked_phone,
        ]);
    }

    public function verifySmsMfa(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $adminSession = session('admin_auth');
        if (!$adminSession) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        $adminUser = AdminUser::find($adminSession['admin_id']);
        if (!$adminUser) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        if (!$adminUser->verifySmsMfaCode($request->code)) {
            AdminAuditService::log('admin_mfa_failed', [
                'reason' => 'invalid_sms_code',
                'attempts' => $adminUser->sms_mfa_attempts,
            ]);

            if ($adminUser->sms_mfa_attempts >= 3) {
                return response()->json(['error' => 'Too many attempts. Request a new code.'], 429);
            }

            return response()->json(['error' => 'Invalid code'], 422);
        }

        $adminUser->clearSmsMfaCode();

        if (!$adminUser->mfa_method || $adminUser->mfa_method === 'authenticator') {
            $newMethod = $adminUser->mfa_method === 'authenticator' ? 'both' : 'sms';
            $adminUser->update(['mfa_method' => $newMethod]);
            if (!$adminUser->mfa_enabled) {
                $adminUser->update(['mfa_enabled' => true, 'mfa_enabled_at' => now()]);
            }
        }

        $adminSession['mfa_verified'] = true;
        session()->put('admin_auth', $adminSession);

        AdminAuditService::log('admin_mfa_verified', ['method' => 'sms']);

        if ($adminUser->needsPasswordChange() || $adminUser->force_password_change) {
            return response()->json(['redirect' => route('admin.password.change')]);
        }

        return response()->json(['redirect' => route('admin.dashboard')]);
    }

    // =====================================================
    // PASSWORD CHANGE
    // =====================================================

    public function showPasswordChange()
    {
        $adminSession = session('admin_auth');
        if (!$adminSession || !$adminSession['authenticated'] || !$adminSession['mfa_verified']) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.password-change', [
            'page_title' => 'Change Password',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:12|confirmed',
        ]);

        $adminUser = AdminUser::find(session('admin_auth.admin_id'));
        if (!$adminUser || !Hash::check($request->current_password, $adminUser->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $password = $request->new_password;
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
            return back()->withErrors(['new_password' => 'Password must contain uppercase, lowercase, number, and symbol']);
        }

        $adminUser->changePassword($password);
        $adminUser->update(['force_password_change' => false]);

        AdminAuditService::log('admin_password_changed', ['admin_id' => $adminUser->id]);

        return redirect()->route('admin.dashboard')->with('success', 'Password changed successfully');
    }

    // =====================================================
    // HELPERS
    // =====================================================

    protected function findAdminUser(string $email): ?AdminUser
    {
        return AdminUser::where('email', strtolower($email))->first();
    }

    protected function isAdminAuthenticated(): bool
    {
        $adminSession = session('admin_auth');
        return $adminSession &&
               isset($adminSession['authenticated']) &&
               $adminSession['authenticated'] === true &&
               isset($adminSession['mfa_verified']) &&
               $adminSession['mfa_verified'] === true;
    }

    protected function generateMfaSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    protected function generateQrCodeUrl(string $email, string $secret): string
    {
        $issuer = config('admin.mfa.issuer', 'QuickSMS Admin');
        $label = urlencode($issuer . ':' . $email);
        $issuerParam = urlencode($issuer);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuerParam}&algorithm=SHA1&digits=6&period=30";
    }

    protected function verifyTotp(string $secret, string $code): bool
    {
        $timeSlice = floor(time() / 30);

        for ($i = -1; $i <= 1; $i++) {
            $calculatedCode = $this->calculateTotp($secret, $timeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    protected function calculateTotp(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0, $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = (ord($hash[$offset]) & 0x7F) << 24;
        $binary |= (ord($hash[$offset + 1]) & 0xFF) << 16;
        $binary |= (ord($hash[$offset + 2]) & 0xFF) << 8;
        $binary |= (ord($hash[$offset + 3]) & 0xFF);
        $otp = $binary % 1000000;

        return str_pad((string)$otp, 6, '0', STR_PAD_LEFT);
    }

    protected function base32Decode(string $input): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;

        for ($i = 0, $j = strlen($input); $i < $j; $i++) {
            $v <<= 5;
            $v += stripos($alphabet, $input[$i]);
            $vbits += 5;

            if ($vbits >= 8) {
                $vbits -= 8;
                $output .= chr(($v >> $vbits) & 0xFF);
            }
        }

        return $output;
    }
}
