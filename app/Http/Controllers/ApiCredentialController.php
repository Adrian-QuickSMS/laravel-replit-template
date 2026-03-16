<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ApiCredential;
use App\Models\AccountAuditLog;
use App\Services\Audit\AuditContext;

class ApiCredentialController extends Controller
{
    /**
     * List credentials (metadata only — never return secrets).
     */
    public function index()
    {
        $accountId = session('customer_tenant_id');

        $credentials = ApiCredential::forAccount($accountId)
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'auth_type' => $c->auth_type,
                'description' => $c->description,
                'last_used_at' => $c->last_used_at?->diffForHumans(),
                'created_at' => $c->created_at->format('Y-m-d'),
            ]);

        return response()->json([
            'success' => true,
            'credentials' => $credentials,
        ]);
    }

    /**
     * Create a new credential.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'auth_type' => 'required|in:none,basic,bearer,api_key,custom_header',
            'credentials' => 'required|array',
            'description' => 'nullable|string|max:500',
        ]);

        $credential = ApiCredential::create([
            'account_id' => session('customer_tenant_id'),
            'created_by' => session('customer_user_id'),
            'name' => $request->name,
            'auth_type' => $request->auth_type,
            'credentials' => $request->credentials,
            'description' => $request->description,
        ]);

        try {
            $actor = AuditContext::actor();
            AccountAuditLog::record(
                session('customer_tenant_id'),
                'api_credential_created',
                $actor['user_id'],
                $actor['user_name'],
                "API credential '{$credential->name}' created",
                ['credential_id' => $credential->id, 'auth_type' => $credential->auth_type]
            );
        } catch (\Throwable $e) {
            Log::warning('[AuditLog] Failed to record api_credential_created', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'credential' => [
                'id' => $credential->id,
                'name' => $credential->name,
                'auth_type' => $credential->auth_type,
            ],
        ]);
    }

    /**
     * Update a credential.
     */
    public function update(Request $request, $id)
    {
        $accountId = session('customer_tenant_id');
        $credential = ApiCredential::forAccount($accountId)->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'auth_type' => 'sometimes|in:none,basic,bearer,api_key,custom_header',
            'credentials' => 'sometimes|array',
            'description' => 'nullable|string|max:500',
        ]);

        $oldValues = $credential->only(['name', 'auth_type', 'description']);

        if ($request->has('name')) $credential->name = $request->name;
        if ($request->has('auth_type')) $credential->auth_type = $request->auth_type;
        if ($request->has('credentials')) $credential->credentials = $request->credentials;
        if ($request->has('description')) $credential->description = $request->description;

        $credential->save();

        try {
            $actor = AuditContext::actor();
            $changes = AuditContext::diff($oldValues, $credential->only(['name', 'auth_type', 'description']));
            if ($request->has('credentials')) {
                $changes['credentials'] = ['from' => '[REDACTED]', 'to' => '[REDACTED]'];
            }
            AccountAuditLog::record(
                $accountId,
                'api_credential_updated',
                $actor['user_id'],
                $actor['user_name'],
                "API credential '{$credential->name}' updated",
                ['credential_id' => $credential->id, 'changes' => $changes]
            );
        } catch (\Throwable $e) {
            Log::warning('[AuditLog] Failed to record api_credential_updated', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'credential' => [
                'id' => $credential->id,
                'name' => $credential->name,
                'auth_type' => $credential->auth_type,
            ],
        ]);
    }

    /**
     * Delete a credential.
     */
    public function destroy($id)
    {
        $accountId = session('customer_tenant_id');
        $credential = ApiCredential::forAccount($accountId)->findOrFail($id);
        $credentialName = $credential->name;
        $credentialId = $credential->id;
        $credential->delete();

        try {
            $actor = AuditContext::actor();
            AccountAuditLog::record(
                $accountId,
                'api_credential_deleted',
                $actor['user_id'],
                $actor['user_name'],
                "API credential '{$credentialName}' deleted",
                ['credential_id' => $credentialId]
            );
        } catch (\Throwable $e) {
            Log::warning('[AuditLog] Failed to record api_credential_deleted', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Credential deleted.',
        ]);
    }
}
