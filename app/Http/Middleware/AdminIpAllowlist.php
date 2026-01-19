<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Admin\AdminAuditService;

class AdminIpAllowlist
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('admin.ip_allowlist.enabled', false)) {
            return $next($request);
        }
        
        $clientIp = $request->ip();
        $allowedIps = config('admin.ip_allowlist.ips', []);
        $allowedCidrs = config('admin.ip_allowlist.cidrs', []);
        
        if (empty($allowedIps) && empty($allowedCidrs)) {
            return $next($request);
        }
        
        if (in_array($clientIp, $allowedIps)) {
            return $next($request);
        }
        
        foreach ($allowedCidrs as $cidr) {
            if ($this->ipInCidr($clientIp, $cidr)) {
                return $next($request);
            }
        }
        
        AdminAuditService::log('admin_ip_blocked', [
            'ip' => $clientIp,
            'path' => $request->path(),
            'user_agent' => $request->userAgent()
        ], 'high');
        
        abort(403, 'Access denied: IP not in allowlist');
    }
    
    protected function ipInCidr(string $ip, string $cidr): bool
    {
        if (strpos($cidr, '/') === false) {
            return $ip === $cidr;
        }
        
        list($subnet, $mask) = explode('/', $cidr);
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - (int)$mask);
            
            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }
        
        return false;
    }
}
