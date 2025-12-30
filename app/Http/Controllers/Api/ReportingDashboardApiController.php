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
        // Delivery Rate: Delivered / (Delivered + Undelivered + Rejected)
        $delivered = rand(10000, 12000);
        $undelivered = rand(200, 500); // Network issues, temporary failures
        $rejected = rand(100, 300); // Carrier rejected, invalid numbers
        $totalForDeliveryCalc = $delivered + $undelivered + $rejected;
        $deliveryRate = round(($delivered / $totalForDeliveryCalc) * 100, 1);
        
        $totalMessages = $delivered + $undelivered + $rejected + rand(100, 400); // + pending
        
        $smsCount = (int)($totalMessages * 0.66);
        $rcsCount = $totalMessages - $smsCount;
        
        // RCS Seen Rate - only where read receipts are available
        $rcsWithReadReceipts = (int)($rcsCount * (rand(70, 85) / 100)); // Not all RCS support read receipts
        $rcsSeenCount = (int)($rcsWithReadReceipts * (rand(750, 880) / 1000));
        
        $optOutCount = rand(20, 80);
        
        // Spend calculation - matches Message Log cost rules
        $smsCostPerMessage = 0.08; // £0.08 per SMS
        $rcsCostPerMessage = 0.12; // £0.12 per RCS
        $spend = ($smsCount * $smsCostPerMessage) + ($rcsCount * $rcsCostPerMessage);
        $creditsUsed = $smsCount + ($rcsCount * 1.5); // RCS costs 1.5 credits
        
        // Billing status - estimated if any messages still pending/processing
        $hasPendingBilling = rand(0, 1);
        
        return [
            'deliveryRate' => [
                'value' => $deliveryRate,
                'delivered' => $delivered,
                'undelivered' => $undelivered,
                'rejected' => $rejected,
                'formula' => 'Delivered / (Delivered + Undelivered + Rejected)',
                'trend' => rand(-30, 50) / 10,
                'previousPeriod' => round($deliveryRate - (rand(-20, 30) / 10), 1),
            ],
            'spend' => [
                'amount' => round($spend, 2),
                'currency' => 'GBP',
                'creditsUsed' => round($creditsUsed),
                'isEstimated' => (bool)$hasPendingBilling,
                'excludesVat' => true,
                'vatNote' => 'Excludes VAT',
            ],
            'rcsSeenRate' => [
                'value' => round(($rcsSeenCount / max($rcsWithReadReceipts, 1)) * 100, 1),
                'rcsMessageCount' => $rcsCount,
                'rcsWithReadReceipts' => $rcsWithReadReceipts,
                'seenCount' => $rcsSeenCount,
                'hasRcsData' => $rcsCount > 0,
                'hasReadReceiptSupport' => $rcsWithReadReceipts > 0,
                'tooltip' => 'Based on recipients where read receipts are supported',
            ],
            'optOutRate' => [
                'value' => round(($optOutCount / $totalMessages) * 100, 2),
                'optOutCount' => $optOutCount,
                'hasOptOutData' => true,
            ],
            'messagesSent' => [
                'count' => $totalMessages,
                'trend' => round(rand(-150, 250) / 10, 1),
            ],
            'rcsPenetration' => [
                'percentage' => round(($rcsCount / max($totalMessages, 1)) * 100, 1),
                'rcsCount' => $rcsCount,
                'smsCount' => $smsCount,
                'trend' => round(rand(-50, 100) / 10, 1),
            ],
            'inboundReceived' => [
                'count' => rand(150, 500),
                'unreadCount' => rand(5, 25),
            ],
            'undeliveredMessages' => [
                'count' => $undelivered + $rejected,
                'trend' => round(rand(-80, 80) / 10, 1),
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
        $totalData = [];
        
        // Generate last 7 days of data
        for ($i = 6; $i >= 0; $i--) {
            $date = date('d M', strtotime("-{$i} days"));
            $dates[] = $date;
            $sms = rand(800, 2500);
            $rcs = rand(200, 1200);
            $smsData[] = $sms;
            $rcsData[] = $rcs;
            $totalData[] = $sms + $rcs;
        }
        
        return [
            'categories' => $dates,
            'series' => [
                ['name' => 'Total', 'data' => $totalData],
                ['name' => 'SMS', 'data' => $smsData],
                ['name' => 'RCS', 'data' => $rcsData],
            ],
            'totals' => [
                'sms' => array_sum($smsData),
                'rcs' => array_sum($rcsData),
                'total' => array_sum($totalData),
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
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        // Generate hourly distribution (8 AM to 6 PM business hours)
        $hourlyDistribution = [];
        $maxVolume = 0;
        $peakHourIndex = 0;
        
        for ($h = 8; $h <= 17; $h++) {
            // Simulate realistic distribution - higher mid-morning
            $baseVolume = 200;
            if ($h >= 9 && $h <= 11) {
                $volume = rand(800, 1500); // Morning peak
            } elseif ($h >= 14 && $h <= 16) {
                $volume = rand(600, 1000); // Afternoon secondary peak
            } else {
                $volume = rand($baseVolume, 500);
            }
            
            $hourlyDistribution[$h] = $volume;
            
            if ($volume > $maxVolume) {
                $maxVolume = $volume;
                $peakHourIndex = $h;
            }
        }
        
        // Format peak hour as "HH:00–HH:59"
        $peakHourStart = sprintf('%02d:00', $peakHourIndex);
        $peakHourEnd = sprintf('%02d:59', $peakHourIndex);
        $peakHourDisplay = "{$peakHourStart}–{$peakHourEnd}";
        
        return [
            'peakHour' => sprintf('%02d:00', $peakHourIndex),
            'peakHourDisplay' => $peakHourDisplay,
            'peakDay' => $days[array_rand($days)],
            'peakVolumeCount' => $maxVolume,
            'hourlyDistribution' => $hourlyDistribution,
            'bestDeliveryRate' => rand(960, 990) / 10,
            'recommendation' => "Consider scheduling campaigns during {$peakHourDisplay} for optimal delivery.",
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
