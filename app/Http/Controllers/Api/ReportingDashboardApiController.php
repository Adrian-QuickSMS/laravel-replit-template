<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportingDashboardApiController extends Controller
{
    /**
     * Get all dashboard data in a single call or individual endpoints
     */
    public function index(Request $request): JsonResponse
    {
        // Simulate network delay (200-500ms)
        usleep(rand(200000, 500000));
        
        return response()->json([
            'kpis' => $this->getKpiData(),
            'volumeOverTime' => $this->getVolumeData(),
            'channelSplit' => $this->getChannelSplitData(),
            'deliveryStatus' => $this->getDeliveryStatusData(),
            'topCountries' => $this->getTopCountriesData(),
            'topSenderIds' => $this->getTopSenderIdsData(),
            'peakSendingTime' => $this->getPeakSendingTimeData(),
            'failureReasons' => $this->getFailureReasonsData(),
        ]);
    }

    /**
     * KPI Tiles Data
     */
    public function kpis(Request $request): JsonResponse
    {
        usleep(rand(100000, 300000));
        return response()->json($this->getKpiData());
    }

    private function getKpiData(): array
    {
        $totalMessages = rand(10000, 15000);
        $delivered = (int)($totalMessages * (rand(920, 980) / 1000));
        $failed = rand(150, 400);
        $pending = $totalMessages - $delivered - $failed;
        
        $smsCount = (int)($totalMessages * 0.66);
        $rcsCount = $totalMessages - $smsCount;
        $rcsSeenCount = (int)($rcsCount * (rand(750, 850) / 1000));
        $optOutCount = rand(20, 80);
        
        $spend = $totalMessages * 0.10; // £0.10 per message avg
        $creditsUsed = $totalMessages;
        
        return [
            'deliveryRate' => [
                'value' => round(($delivered / $totalMessages) * 100, 1),
                'trend' => rand(-30, 50) / 10,
                'previousPeriod' => round((($delivered - rand(100, 500)) / ($totalMessages - rand(500, 1000))) * 100, 1),
            ],
            'spend' => [
                'amount' => round($spend, 2),
                'currency' => 'GBP',
                'creditsUsed' => $creditsUsed,
                'costType' => rand(0, 1) ? 'estimated' : 'final',
            ],
            'rcsSeenRate' => [
                'value' => round(($rcsSeenCount / $rcsCount) * 100, 1),
                'rcsMessageCount' => $rcsCount,
                'seenCount' => $rcsSeenCount,
                'hasRcsData' => true,
            ],
            'optOutRate' => [
                'value' => round(($optOutCount / $totalMessages) * 100, 2),
                'optOutCount' => $optOutCount,
                'hasOptOutData' => true,
            ],
        ];
    }

    /**
     * Volume Over Time Chart Data
     */
    public function volumeOverTime(Request $request): JsonResponse
    {
        usleep(rand(200000, 400000));
        return response()->json($this->getVolumeData());
    }

    private function getVolumeData(): array
    {
        $dates = [];
        $smsData = [];
        $rcsData = [];
        
        // Generate last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = date('d M', strtotime("-{$i} days"));
            $dates[] = $date;
            $smsData[] = rand(800, 2500);
            $rcsData[] = rand(200, 1200);
        }
        
        return [
            'categories' => $dates,
            'series' => [
                ['name' => 'SMS', 'data' => $smsData],
                ['name' => 'RCS', 'data' => $rcsData],
            ],
            'totals' => [
                'sms' => array_sum($smsData),
                'rcs' => array_sum($rcsData),
                'total' => array_sum($smsData) + array_sum($rcsData),
            ],
        ];
    }

    /**
     * Channel Split Data
     */
    public function channelSplit(Request $request): JsonResponse
    {
        usleep(rand(100000, 250000));
        return response()->json($this->getChannelSplitData());
    }

    private function getChannelSplitData(): array
    {
        $smsCount = rand(6000, 10000);
        $rcsCount = rand(2000, 5000);
        $total = $smsCount + $rcsCount;
        
        return [
            'sms' => [
                'count' => $smsCount,
                'percentage' => round(($smsCount / $total) * 100, 1),
            ],
            'rcs' => [
                'count' => $rcsCount,
                'percentage' => round(($rcsCount / $total) * 100, 1),
            ],
            'total' => $total,
        ];
    }

    /**
     * Delivery Status Breakdown
     */
    public function deliveryStatus(Request $request): JsonResponse
    {
        usleep(rand(150000, 350000));
        return response()->json($this->getDeliveryStatusData());
    }

    private function getDeliveryStatusData(): array
    {
        $delivered = rand(10000, 14000);
        $pending = rand(200, 600);
        $failed = rand(150, 400);
        $total = $delivered + $pending + $failed;
        
        return [
            'delivered' => [
                'count' => $delivered,
                'percentage' => round(($delivered / $total) * 100, 1),
            ],
            'pending' => [
                'count' => $pending,
                'percentage' => round(($pending / $total) * 100, 1),
            ],
            'failed' => [
                'count' => $failed,
                'percentage' => round(($failed / $total) * 100, 1),
            ],
            'total' => $total,
        ];
    }

    /**
     * Top Countries Data
     */
    public function topCountries(Request $request): JsonResponse
    {
        usleep(rand(200000, 400000));
        return response()->json($this->getTopCountriesData());
    }

    private function getTopCountriesData(): array
    {
        $countries = [
            ['code' => 'GB', 'name' => 'United Kingdom'],
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'DE', 'name' => 'Germany'],
            ['code' => 'FR', 'name' => 'France'],
            ['code' => 'IE', 'name' => 'Ireland'],
            ['code' => 'ES', 'name' => 'Spain'],
            ['code' => 'IT', 'name' => 'Italy'],
            ['code' => 'NL', 'name' => 'Netherlands'],
            ['code' => 'BE', 'name' => 'Belgium'],
            ['code' => 'AU', 'name' => 'Australia'],
        ];
        
        $data = [];
        $maxCount = rand(4000, 6000);
        
        foreach ($countries as $index => $country) {
            $count = (int)($maxCount * pow(0.7, $index) + rand(50, 200));
            $data[] = [
                'code' => $country['code'],
                'name' => $country['name'],
                'count' => $count,
            ];
        }
        
        return [
            'countries' => $data,
            'categories' => array_column($data, 'code'),
            'values' => array_column($data, 'count'),
        ];
    }

    /**
     * Top SenderIDs Data
     */
    public function topSenderIds(Request $request): JsonResponse
    {
        usleep(rand(150000, 350000));
        return response()->json($this->getTopSenderIdsData());
    }

    private function getTopSenderIdsData(): array
    {
        $senderIds = ['PROMO', 'ALERTS', 'QuickSMS', 'INFO', 'NOTIFY', 'VERIFY', 'UPDATES', 'NEWS'];
        shuffle($senderIds);
        $senderIds = array_slice($senderIds, 0, 5);
        
        $data = [];
        $maxCount = rand(3000, 5000);
        
        foreach ($senderIds as $index => $senderId) {
            $messages = (int)($maxCount * pow(0.75, $index) + rand(100, 500));
            $deliveryRate = rand(920, 990) / 10;
            $delivered = (int)($messages * ($deliveryRate / 100));
            
            $data[] = [
                'senderId' => $senderId,
                'messages' => $messages,
                'delivered' => $delivered,
                'deliveryRate' => $deliveryRate,
            ];
        }
        
        return ['senderIds' => $data];
    }

    /**
     * Peak Sending Time Insight
     */
    public function peakSendingTime(Request $request): JsonResponse
    {
        usleep(rand(100000, 250000));
        return response()->json($this->getPeakSendingTimeData());
    }

    private function getPeakSendingTimeData(): array
    {
        $hours = ['08:00', '09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00'];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        return [
            'peakHour' => $hours[array_rand($hours)],
            'peakDay' => $days[array_rand($days)],
            'peakVolumeCount' => rand(800, 1500),
            'bestDeliveryRate' => rand(960, 990) / 10,
            'recommendation' => 'Consider scheduling campaigns between 9-11 AM for optimal delivery.',
        ];
    }

    /**
     * Failure Reasons Data
     */
    public function failureReasons(Request $request): JsonResponse
    {
        usleep(rand(150000, 300000));
        return response()->json($this->getFailureReasonsData());
    }

    private function getFailureReasonsData(): array
    {
        $totalFailed = rand(150, 400);
        
        // Distribute failures across reasons
        $invalidNumber = (int)($totalFailed * (rand(35, 45) / 100));
        $networkError = (int)($totalFailed * (rand(20, 30) / 100));
        $carrierRejected = (int)($totalFailed * (rand(15, 22) / 100));
        $timeout = (int)($totalFailed * (rand(8, 12) / 100));
        $other = $totalFailed - $invalidNumber - $networkError - $carrierRejected - $timeout;
        
        $reasons = [
            [
                'reason' => 'Invalid Number',
                'icon' => 'fa-phone-slash',
                'iconColor' => 'text-danger',
                'count' => $invalidNumber,
                'percentage' => round(($invalidNumber / $totalFailed) * 100, 1),
            ],
            [
                'reason' => 'Network Error',
                'icon' => 'fa-signal',
                'iconColor' => 'text-warning',
                'count' => $networkError,
                'percentage' => round(($networkError / $totalFailed) * 100, 1),
            ],
            [
                'reason' => 'Carrier Rejected',
                'icon' => 'fa-ban',
                'iconColor' => 'text-secondary',
                'count' => $carrierRejected,
                'percentage' => round(($carrierRejected / $totalFailed) * 100, 1),
            ],
            [
                'reason' => 'Timeout',
                'icon' => 'fa-clock',
                'iconColor' => 'text-info',
                'count' => $timeout,
                'percentage' => round(($timeout / $totalFailed) * 100, 1),
            ],
            [
                'reason' => 'Other',
                'icon' => 'fa-question-circle',
                'iconColor' => 'text-muted',
                'count' => $other,
                'percentage' => round(($other / $totalFailed) * 100, 1),
            ],
        ];
        
        return [
            'totalFailed' => $totalFailed,
            'reasons' => $reasons,
        ];
    }
}
