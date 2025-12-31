<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MockReportingDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportingDashboardApiController extends Controller
{
    private MockReportingDataService $dataService;

    public function __construct()
    {
        $this->dataService = MockReportingDataService::getInstance();
    }

    private function parseFilters(Request $request): array
    {
        return [
            'dateFrom' => $request->input('dateFrom'),
            'dateTo' => $request->input('dateTo'),
            'senderIds' => $this->parseArrayParam($request->input('senderIds')),
            'subAccounts' => $this->parseArrayParam($request->input('subAccounts')),
            'users' => $this->parseArrayParam($request->input('users')),
            'origins' => $this->parseArrayParam($request->input('origins')),
            'groupNames' => $this->parseArrayParam($request->input('groupNames')),
            'countries' => $this->parseArrayParam($request->input('countries')),
            'channels' => $this->parseArrayParam($request->input('channels')),
            'statuses' => $this->parseArrayParam($request->input('statuses')),
        ];
    }

    private function parseArrayParam($value): ?array
    {
        if (is_array($value)) {
            return !empty($value) ? $value : null;
        }
        if (is_string($value) && !empty($value)) {
            return explode(',', $value);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        usleep(rand(100000, 300000));
        
        $filters = $this->parseFilters($request);
        
        return response()->json([
            'kpis' => $this->dataService->getKpiData($filters),
            'volumeOverTime' => $this->dataService->getVolumeData($filters),
            'channelSplit' => $this->dataService->getChannelSplitData($filters),
            'deliveryStatus' => $this->dataService->getDeliveryStatusData($filters),
            'topCountries' => $this->dataService->getTopCountriesData($filters),
            'topSenderIds' => $this->dataService->getTopSenderIdsData($filters),
            'peakSendingTime' => $this->dataService->getPeakTimeData($filters),
            'failureReasons' => $this->dataService->getFailureReasonsData($filters),
        ]);
    }

    public function kpis(Request $request): JsonResponse
    {
        usleep(rand(100000, 200000));
        $filters = $this->parseFilters($request);
        return response()->json($this->dataService->getKpiData($filters));
    }

    public function volumeOverTime(Request $request): JsonResponse
    {
        usleep(rand(100000, 200000));
        $filters = $this->parseFilters($request);
        return response()->json($this->dataService->getVolumeData($filters));
    }

    public function channelSplit(Request $request): JsonResponse
    {
        usleep(rand(100000, 150000));
        $filters = $this->parseFilters($request);
        return response()->json($this->dataService->getChannelSplitData($filters));
    }

    public function deliveryStatus(Request $request): JsonResponse
    {
        usleep(rand(100000, 200000));
        $filters = $this->parseFilters($request);
        return response()->json($this->dataService->getDeliveryStatusData($filters));
    }

    public function topCountries(Request $request): JsonResponse
    {
        usleep(rand(100000, 200000));
        $filters = $this->parseFilters($request);
        return response()->json($this->dataService->getTopCountriesData($filters));
    }

    public function topSenderIds(Request $request): JsonResponse
    {
        usleep(rand(100000, 150000));
        $filters = $this->parseFilters($request);
        return response()->json($this->dataService->getTopSenderIdsData($filters));
    }

    public function peakSendingTime(Request $request): JsonResponse
    {
        usleep(rand(100000, 150000));
        $filters = $this->parseFilters($request);
        return response()->json($this->dataService->getPeakTimeData($filters));
    }

    public function failureReasons(Request $request): JsonResponse
    {
        usleep(rand(100000, 150000));
        $filters = $this->parseFilters($request);
        return response()->json($this->dataService->getFailureReasonsData($filters));
    }

    public function availableFilters(Request $request): JsonResponse
    {
        return response()->json($this->dataService->getAvailableFilters());
    }
}
