<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = session('customer_tenant_id');

        $query = Notification::forTenant($tenantId)
            ->unread()
            ->undismissed()
            ->orderBy('created_at', 'desc');

        if ($type = $request->input('type')) {
            $query->ofType($type);
        }

        $notifications = $query->limit(20)->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function markRead(string $uuid): JsonResponse
    {
        $tenantId = session('customer_tenant_id');

        $notification = Notification::forTenant($tenantId)
            ->where('uuid', $uuid)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'error' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function dismiss(string $uuid): JsonResponse
    {
        $tenantId = session('customer_tenant_id');

        $notification = Notification::forTenant($tenantId)
            ->where('uuid', $uuid)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'error' => 'Notification not found.'], 404);
        }

        $notification->markAsDismissed();

        return response()->json(['success' => true]);
    }
}
