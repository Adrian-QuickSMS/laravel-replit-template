<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\CompanyHrSettings;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\LeaveRequest;
use App\Services\Hr\LeaveCalculationService;
use App\Services\Hr\LeaveRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MyLeaveController extends Controller
{
    public function __construct(
        private LeaveCalculationService $calculator,
        private LeaveRequestService $leaveService
    ) {}

    /**
     * My Leave page - balances, request form, history.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $year = (int) $request->get('year', date('Y'));

        $profile = EmployeeHrProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return view('quicksms.hr.my-leave', [
                'profile' => null,
                'balance' => null,
                'requests' => collect(),
                'year' => $year,
            ]);
        }

        $this->leaveService->ensureEntitlement($profile, $year);
        $balance = $this->calculator->getBalanceSummary($profile, $year);

        $requests = LeaveRequest::where('employee_id', $profile->id)
            ->forYear($year)
            ->orderByDesc('submitted_at')
            ->get();

        return view('quicksms.hr.my-leave', compact('profile', 'balance', 'requests', 'year'));
    }

    /**
     * Submit a new leave request (AJAX).
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $profile = EmployeeHrProfile::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'leave_type' => 'required|in:annual_leave,sickness,medical',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'day_portion' => 'required|in:full,half_am,half_pm,quarter',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $leaveRequest = $this->leaveService->submitRequest(
                $profile,
                $validated['leave_type'],
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date']),
                $validated['day_portion'],
                $validated['note'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Leave request submitted successfully.',
                'data' => [
                    'id' => $leaveRequest->id,
                    'duration_days' => $leaveRequest->duration_days_display,
                    'status' => $leaveRequest->status,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Cancel a leave request (AJAX).
     */
    public function cancel(Request $request, string $id)
    {
        $user = $request->user();
        $profile = EmployeeHrProfile::where('user_id', $user->id)->firstOrFail();

        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('employee_id', $profile->id)
            ->firstOrFail();

        try {
            $this->leaveService->cancelRequest($leaveRequest, $profile);

            return response()->json([
                'status' => 'success',
                'message' => 'Leave request cancelled.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }
}
