<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BillingApiController extends Controller
{
    public function getData(Request $request): JsonResponse
    {
        $filters = [
            'billingMonth' => $request->input('billingMonth', []),
            'subAccount' => $request->input('subAccount', []),
            'user' => $request->input('user', []),
            'groupName' => $request->input('groupName', []),
            'productType' => $request->input('productType', []),
            'senderID' => $request->input('senderID', []),
            'messageType' => $request->input('messageType', []),
        ];

        $data = $this->generateMockBillingData($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => count($data),
                'filters' => $filters,
            ],
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $format = $request->input('format', 'csv');
        $filters = $request->all();
        $rowCount = $request->input('rowCount', 12);

        if ($rowCount > 10000) {
            return response()->json([
                'success' => true,
                'async' => true,
                'message' => 'Export queued for processing. You will be notified when ready.',
                'jobId' => 'export_' . uniqid(),
                'estimatedTime' => '5-10 minutes',
                'downloadLocation' => '/reporting/download-area',
            ]);
        }

        return response()->json([
            'success' => true,
            'async' => false,
            'downloadUrl' => '/api/billing/download/' . uniqid() . '.' . $format,
            'filename' => 'finance_data_' . date('Y-m-d_His') . '.' . $format,
        ]);
    }

    public function getSavedReports(Request $request): JsonResponse
    {
        $savedReports = [
            [
                'id' => 'rpt_001',
                'name' => 'Monthly Finance Summary',
                'createdAt' => '2025-01-15T10:30:00Z',
                'filters' => [
                    'billingMonth' => ['2025-01'],
                    'subAccount' => ['Main Account'],
                ],
            ],
            [
                'id' => 'rpt_002',
                'name' => 'Q4 2024 Billing Analysis',
                'createdAt' => '2024-12-20T14:15:00Z',
                'filters' => [
                    'billingMonth' => ['2024-10', '2024-11', '2024-12'],
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $savedReports,
        ]);
    }

    public function saveReport(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $filters = $request->input('filters', []);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => 'rpt_' . uniqid(),
                'name' => $name,
                'createdAt' => now()->toIso8601String(),
                'filters' => $filters,
            ],
            'message' => 'Report configuration saved successfully.',
        ]);
    }

    public function schedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly',
            'format' => 'required|in:csv,excel',
            'recipients' => 'array',
            'filters' => 'array',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'scheduleId' => 'sch_' . uniqid(),
                'name' => $validated['name'],
                'frequency' => $validated['frequency'],
                'format' => $validated['format'],
                'nextRun' => $this->calculateNextRun($validated['frequency']),
                'createdAt' => now()->toIso8601String(),
            ],
            'message' => 'Scheduled export created successfully.',
        ]);
    }

    private function generateMockBillingData(array $filters): array
    {
        $months = [
            '2025-12' => 'December 2025',
            '2025-11' => 'November 2025',
            '2025-10' => 'October 2025',
            '2025-09' => 'September 2025',
            '2025-08' => 'August 2025',
            '2025-07' => 'July 2025',
            '2025-06' => 'June 2025',
            '2025-05' => 'May 2025',
            '2025-04' => 'April 2025',
            '2025-03' => 'March 2025',
            '2025-02' => 'February 2025',
            '2025-01' => 'January 2025',
        ];

        $statuses = [
            '2025-12' => 'Finalised',
            '2025-11' => 'Finalised',
            '2025-10' => 'Finalised',
            '2025-09' => 'Adjusted',
            '2025-08' => 'Provisional',
            '2025-07' => 'Provisional',
            '2025-06' => 'Finalised',
            '2025-05' => 'Finalised',
            '2025-04' => 'Finalised',
            '2025-03' => 'Finalised',
            '2025-02' => 'Finalised',
            '2025-01' => 'Finalised',
        ];

        $seed = crc32(json_encode($filters));
        srand($seed);

        $data = [];
        foreach ($months as $monthKey => $monthLabel) {
            if (!empty($filters['billingMonth']) && !in_array($monthKey, $filters['billingMonth'])) {
                continue;
            }

            $billableParts = rand(80000, 150000);
            $nonBillableParts = rand(1000, 6000);
            $totalParts = $billableParts + $nonBillableParts;
            $totalCost = round($billableParts * 0.032, 2);

            $data[] = [
                'billingMonth' => $monthKey,
                'billingMonthLabel' => $monthLabel,
                'billableParts' => $billableParts,
                'nonBillableParts' => $nonBillableParts,
                'totalParts' => $totalParts,
                'totalCost' => $totalCost,
                'billingStatus' => $statuses[$monthKey],
            ];
        }

        srand();

        return $data;
    }

    private function calculateNextRun(string $frequency): string
    {
        $now = now();

        return match ($frequency) {
            'daily' => $now->addDay()->startOfDay()->toIso8601String(),
            'weekly' => $now->next('Monday')->startOfDay()->toIso8601String(),
            'monthly' => $now->addMonth()->startOfMonth()->toIso8601String(),
            default => $now->addDay()->toIso8601String(),
        };
    }
}
