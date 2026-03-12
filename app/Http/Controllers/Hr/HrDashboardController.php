<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\CompanyHrSettings;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\LeaveRequest;
use App\Services\Hr\LeaveCalculationService;
use App\Services\Hr\LeaveRequestService;
use Illuminate\Http\Request;

class HrDashboardController extends Controller
{
    public function __construct(
        private LeaveCalculationService $calculator,
        private LeaveRequestService $leaveService
    ) {}

    /**
     * HR Module Dashboard - shows summary cards and pending requests.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $year = (int) $request->get('year', date('Y'));

        $profile = EmployeeHrProfile::where('user_id', $user->id)->first();

        $balance = null;
        $myPendingRequests = collect();
        $myRecentRequests = collect();

        if ($profile) {
            $this->leaveService->ensureEntitlement($profile, $year);
            $balance = $this->calculator->getBalanceSummary($profile, $year);

            $myPendingRequests = LeaveRequest::where('employee_id', $profile->id)
                ->pending()
                ->orderBy('start_date')
                ->limit(5)
                ->get();

            $myRecentRequests = LeaveRequest::where('employee_id', $profile->id)
                ->orderByDesc('submitted_at')
                ->limit(10)
                ->get();
        }

        // Manager/Admin: pending requests from team
        $teamPendingRequests = collect();
        $isManagerOrAdmin = $profile && $profile->isManager();

        if ($isManagerOrAdmin) {
            $teamPendingRequests = LeaveRequest::pending()
                ->where('employee_id', '!=', $profile->id)
                ->with('employee.user')
                ->orderBy('submitted_at')
                ->limit(20)
                ->get();
        }

        // Upcoming approved absences (team-wide for managers, personal for employees)
        $upcomingAbsences = LeaveRequest::approved()
            ->where('end_date', '>=', today())
            ->with('employee.user')
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        return view('quicksms.hr.dashboard', compact(
            'profile',
            'balance',
            'year',
            'myPendingRequests',
            'myRecentRequests',
            'teamPendingRequests',
            'isManagerOrAdmin',
            'upcomingAbsences'
        ));
    }
}
