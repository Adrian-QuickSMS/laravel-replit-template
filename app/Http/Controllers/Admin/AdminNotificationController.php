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
        $notifications = AdminNotification::unread()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => AdminNotification::unread()->count(),
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

    public function markAllRead(): JsonResponse
    {
        AdminNotification::unread()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
