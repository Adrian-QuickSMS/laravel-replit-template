<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        
        $adminUser = $this->findAdminUser($request->email);
        
        if (!$adminUser) {
            AdminAuditService::log('admin_login_failed', [
                'email' => $request->email,
                'reason' => 'user_not_found'
            ]);
            
            return back()->withErrors([
                'email' => 'Invalid credentials'
            ])->withInput(['email' => $request->email]);
        }
        
        if ($adminUser['status'] !== 'active') {
            AdminAuditService::log('admin_login_failed', [
                'email' => $request->email,
                'reason' => 'account_inactive',
                'status' => $adminUser['status']
            ]);
            
            return back()->withErrors([
                'email' => 'Account is not active'
            ])->withInput(['email' => $request->email]);
        }
        
        if (!Hash::check($request->password, $adminUser['password_hash'])) {
            AdminAuditService::log('admin_login_failed', [
                'email' => $request->email,
                'reason' => 'invalid_password'
            ]);
            
            return back()->withErrors([
                'email' => 'Invalid credentials'
            ])->withInput(['email' => $request->email]);
        }
        
        $mfaRequired = config('admin.mfa.required', true);
        $mfaEnabled = $adminUser['mfa_enabled'] ?? false;
        $mfaSecret = $adminUser['mfa_secret'] ?? null;
        
        session()->put('admin_auth', [
            'admin_id' => $adminUser['id'],
            'email' => $adminUser['email'],
            'name' => $adminUser['name'],
            'role' => $adminUser['role'],
            'authenticated' => true,
            'mfa_verified' => false,
            'mfa_setup_required' => $mfaRequired && !$mfaEnabled,
            'mfa_enabled' => $mfaEnabled,
            'mfa_secret' => $mfaSecret,
            'last_activity' => now()->timestamp,
            'login_at' => now()->toIso8601String()
        ]);
        
        AdminAuditService::log('admin_login_success', [
            'email' => $adminUser['email'],
            'role' => $adminUser['role']
        ]);
        
        if ($mfaRequired) {
            if (!$mfaEnabled) {
                return redirect()->route('admin.mfa.setup');
            }
            return redirect()->route('admin.mfa.verify');
        }
        
        session()->put('admin_auth.mfa_verified', true);
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
        $adminSession['mfa_secret'] = $secret;
        session()->put('admin_auth', $adminSession);
        session()->forget('admin_mfa_setup_secret');
        
        // TODO: Persist MFA secret to database when backend integration is complete
        // This would update the admin user record with the new MFA secret
        
        AdminAuditService::log('admin_mfa_verified', [
            'setup' => true,
            'admin_id' => $adminSession['admin_id'] ?? null
        ]);
        
        return redirect()->route('admin.dashboard')->with('success', 'MFA has been enabled for your account');
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
            return redirect()->route('admin.login');
        }
        
        $mfaSecret = $adminSession['mfa_secret'] ?? null;
        
        if (!$mfaSecret) {
            $adminUser = $this->findAdminUser($adminSession['email']);
            $mfaSecret = $adminUser['mfa_secret'] ?? null;
        }
        
        if (!$mfaSecret) {
            AdminAuditService::log('admin_mfa_failed', [
                'reason' => 'no_mfa_secret'
            ]);
            return back()->withErrors(['code' => 'MFA configuration error. Please contact administrator.']);
        }
        
        if (!$this->verifyTotp($mfaSecret, $request->code)) {
            AdminAuditService::log('admin_mfa_failed', [
                'reason' => 'invalid_code'
            ]);
            return back()->withErrors(['code' => 'Invalid verification code']);
        }
        
        $adminSession['mfa_verified'] = true;
        session()->put('admin_auth', $adminSession);
        
        AdminAuditService::log('admin_mfa_verified', []);
        
        return redirect()->route('admin.dashboard');
    }
    
    protected function findAdminUser(string $email): ?array
    {
        $adminUsers = config('admin.users', []);
        
        foreach ($adminUsers as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                return $user;
            }
        }
        
        return null;
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
