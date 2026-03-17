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
            ->orderBy('created_at', 'desc');

        // Filter options
        if ($request->input('unread_only', true)) {
            $query->unread()->undismissed();
        }

        if ($type = $request->input('type')) {
            $query->ofType($type);
        }

        if ($category = $request->input('category')) {
            $query->forCategory($category);
        }

        if ($severity = $request->input('severity')) {
            $query->ofSeverity($severity);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $notifications = $query->paginate($perPage);

        // Unread counts
        $unreadCount = Notification::forTenant($tenantId)->unread()->undismissed()->count();
        $unreadByCategoryQuery = Notification::forTenant($tenantId)
            ->unread()
            ->undismissed()
            ->whereNotNull('category')
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'unread_count' => $unreadCount,
            'unread_by_category' => $unreadByCategoryQuery,
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $tenantId = session('customer_tenant_id');

        $query = Notification::forTenant($tenantId)->unread();

        if ($category = $request->input('category')) {
            $query->forCategory($category);
        }

        $query->update(['read_at' => now()]);

        return response()->json(['success' => true]);
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
