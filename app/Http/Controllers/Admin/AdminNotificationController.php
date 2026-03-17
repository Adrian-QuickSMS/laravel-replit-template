<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AdminNotification::orderBy('created_at', 'desc');

        if ($request->input('unread_only', true)) {
            $query->unread()->undismissed();
        }

        if ($category = $request->input('category')) {
            $query->forCategory($category);
        }

        if ($severity = $request->input('severity')) {
            $query->ofSeverity($severity);
        }

        if ($type = $request->input('type')) {
            $query->ofType($type);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $notifications = $query->paginate($perPage);

        $unreadCount = AdminNotification::unread()->undismissed()->count();
        $unreadByCategory = AdminNotification::unread()
            ->undismissed()
            ->whereNotNull('category')
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'unread_count' => $unreadCount,
            'unread_by_category' => $unreadByCategory,
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    public function markRead(string $uuid): JsonResponse
    {
        $notification = AdminNotification::where('uuid', $uuid)->first();

        if (!$notification) {
            return response()->json(['success' => false, 'error' => 'Notification not found.'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function dismiss(string $uuid): JsonResponse
    {
        $notification = AdminNotification::where('uuid', $uuid)->first();

        if (!$notification) {
            return response()->json(['success' => false, 'error' => 'Notification not found.'], 404);
        }

        $notification->markAsDismissed();

        return response()->json(['success' => true]);
    }

    public function resolve(string $uuid): JsonResponse
    {
        $notification = AdminNotification::where('uuid', $uuid)->first();

        if (!$notification) {
            return response()->json(['success' => false, 'error' => 'Notification not found.'], 404);
        }

        $notification->markAsResolved();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $query = AdminNotification::unread();

        if ($category = $request->input('category')) {
            $query->forCategory($category);
        }

        $query->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
