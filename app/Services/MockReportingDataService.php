<?php

namespace App\Services;

use Carbon\Carbon;

class MockReportingDataService
{
    private array $messages = [];
    private static ?MockReportingDataService $instance = null;
    
    private array $senderIds = ['QuickSMS', 'PROMO', 'ALERTS', 'INFO', 'NOTIFY', 'VERIFY', 'UPDATES', 'NEWS'];
    private array $subAccounts = ['Main Account', 'Marketing', 'Operations', 'Sales', 'Support'];
    private array $users = ['john.smith@company.com', 'jane.doe@company.com', 'admin@company.com', 'marketing@company.com'];
    private array $origins = ['API', 'Web UI', 'Scheduled', 'Email-to-SMS'];
    private array $groupNames = ['VIP Customers', 'Newsletter', 'Promotions', 'Alerts', 'General'];
    private array $countries = [
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
    private array $statuses = ['delivered', 'pending', 'undelivered', 'expired', 'rejected'];
    private array $channels = ['SMS', 'RCS'];
    private array $failureReasons = [
        ['reason' => 'Invalid Number', 'icon' => 'fa-phone-slash', 'iconColor' => 'text-danger'],
        ['reason' => 'Network Error', 'icon' => 'fa-signal', 'iconColor' => 'text-warning'],
        ['reason' => 'Carrier Rejected', 'icon' => 'fa-ban', 'iconColor' => 'text-secondary'],
        ['reason' => 'Timeout', 'icon' => 'fa-clock', 'iconColor' => 'text-info'],
        ['reason' => 'Other', 'icon' => 'fa-question-circle', 'iconColor' => 'text-muted'],
    ];

    public function __construct()
    {
        $this->generateSeedData();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function generateSeedData(): void
    {
        srand(12345);
        
        $now = Carbon::now();
        $startDate = $now->copy()->subDays(60);
        
        for ($i = 0; $i < 15000; $i++) {
            $timestamp = $startDate->copy()->addMinutes(rand(0, 60 * 24 * 60));
            $channel = $this->channels[array_rand($this->channels)];
            $senderId = $this->senderIds[array_rand($this->senderIds)];
            
            $statusRand = rand(1, 100);
            if ($statusRand <= 85) {
                $status = 'delivered';
            } elseif ($statusRand <= 90) {
                $status = 'pending';
            } elseif ($statusRand <= 95) {
                $status = 'undelivered';
            } elseif ($statusRand <= 98) {
                $status = 'expired';
            } else {
                $status = 'rejected';
            }
            
            $failureReason = null;
            if (in_array($status, ['undelivered', 'expired', 'rejected'])) {
                $failureReason = $this->failureReasons[array_rand($this->failureReasons)]['reason'];
            }
            
            $countryWeights = [40, 20, 10, 8, 6, 5, 4, 3, 2, 2];
            $countryIndex = $this->weightedRandom($countryWeights);
            $country = $this->countries[$countryIndex];
            
            $cost = $channel === 'SMS' ? 0.08 : 0.12;
            
            $this->messages[] = [
                'id' => 'MSG' . str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                'timestamp' => $timestamp->toDateTimeString(),
                'date' => $timestamp->toDateString(),
                'hour' => (int)$timestamp->format('H'),
                'dayOfWeek' => $timestamp->format('l'),
                'senderId' => $senderId,
                'subAccount' => $this->subAccounts[array_rand($this->subAccounts)],
                'user' => $this->users[array_rand($this->users)],
                'origin' => $this->origins[array_rand($this->origins)],
                'groupName' => $this->groupNames[array_rand($this->groupNames)],
                'countryCode' => $country['code'],
                'countryName' => $country['name'],
                'channel' => $channel,
                'status' => $status,
                'failureReason' => $failureReason,
                'cost' => $cost,
                'seen' => $channel === 'RCS' && $status === 'delivered' && rand(1, 100) <= 75,
            ];
        }
        
        srand();
    }

    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $cumulative = 0;
        
        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $index;
            }
        }
        
        return 0;
    }

    public function getFilteredMessages(array $filters): array
    {
        return array_filter($this->messages, function ($msg) use ($filters) {
            if (!empty($filters['dateFrom'])) {
                $dateFrom = Carbon::parse($filters['dateFrom'])->startOfDay();
                if (Carbon::parse($msg['timestamp'])->lt($dateFrom)) {
                    return false;
                }
            }
            
            if (!empty($filters['dateTo'])) {
                $dateTo = Carbon::parse($filters['dateTo'])->endOfDay();
                if (Carbon::parse($msg['timestamp'])->gt($dateTo)) {
                    return false;
                }
            }
            
            if (!empty($filters['senderIds']) && is_array($filters['senderIds'])) {
                if (!in_array($msg['senderId'], $filters['senderIds'])) {
                    return false;
                }
            }
            
            if (!empty($filters['subAccounts']) && is_array($filters['subAccounts'])) {
                if (!in_array($msg['subAccount'], $filters['subAccounts'])) {
                    return false;
                }
            }
            
            if (!empty($filters['users']) && is_array($filters['users'])) {
                if (!in_array($msg['user'], $filters['users'])) {
                    return false;
                }
            }
            
            if (!empty($filters['origins']) && is_array($filters['origins'])) {
                if (!in_array($msg['origin'], $filters['origins'])) {
                    return false;
                }
            }
            
            if (!empty($filters['groupNames']) && is_array($filters['groupNames'])) {
                if (!in_array($msg['groupName'], $filters['groupNames'])) {
                    return false;
                }
            }
            
            if (!empty($filters['countries']) && is_array($filters['countries'])) {
                if (!in_array($msg['countryCode'], $filters['countries'])) {
                    return false;
                }
            }
            
            if (!empty($filters['channels']) && is_array($filters['channels'])) {
                if (!in_array($msg['channel'], $filters['channels'])) {
                    return false;
                }
            }
            
            if (!empty($filters['statuses']) && is_array($filters['statuses'])) {
                if (!in_array($msg['status'], $filters['statuses'])) {
                    return false;
                }
            }
            
            return true;
        });
    }

    public function getKpiData(array $filters): array
    {
        $messages = $this->getFilteredMessages($filters);
        $total = count($messages);
        
        if ($total === 0) {
            return $this->getEmptyKpiData();
        }
        
        $delivered = count(array_filter($messages, fn($m) => $m['status'] === 'delivered'));
        $pending = count(array_filter($messages, fn($m) => $m['status'] === 'pending'));
        $undelivered = count(array_filter($messages, fn($m) => $m['status'] === 'undelivered'));
        $expired = count(array_filter($messages, fn($m) => $m['status'] === 'expired'));
        $rejected = count(array_filter($messages, fn($m) => $m['status'] === 'rejected'));
        
        $smsCount = count(array_filter($messages, fn($m) => $m['channel'] === 'SMS'));
        $rcsCount = count(array_filter($messages, fn($m) => $m['channel'] === 'RCS'));
        $rcsSeenCount = count(array_filter($messages, fn($m) => $m['channel'] === 'RCS' && $m['seen']));
        
        $totalCost = array_sum(array_column($messages, 'cost'));
        
        $deliveryDenominator = $delivered + $undelivered + $rejected;
        $deliveryRate = $deliveryDenominator > 0 ? round(($delivered / $deliveryDenominator) * 100, 1) : 0;
        
        $rcsDelivered = count(array_filter($messages, fn($m) => $m['channel'] === 'RCS' && $m['status'] === 'delivered'));
        $rcsSeenRate = $rcsDelivered > 0 ? round(($rcsSeenCount / $rcsDelivered) * 100, 1) : 0;
        
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
                'amount' => round($totalCost, 2),
                'currency' => 'GBP',
                'creditsUsed' => round($smsCount + ($rcsCount * 1.5)),
                'isEstimated' => $pending > 0,
                'excludesVat' => true,
                'vatNote' => 'Excludes VAT',
            ],
            'rcsSeenRate' => [
                'value' => $rcsSeenRate,
                'rcsMessageCount' => $rcsCount,
                'rcsWithReadReceipts' => $rcsDelivered,
                'seenCount' => $rcsSeenCount,
                'hasRcsData' => $rcsCount > 0,
                'hasReadReceiptSupport' => $rcsDelivered > 0,
                'tooltip' => 'Based on recipients where read receipts are supported',
            ],
            'optOutRate' => [
                'value' => round((rand(10, 50) / $total) * 100, 2),
                'optOutCount' => rand(10, 50),
                'hasOptOutData' => true,
            ],
            'messagesSent' => [
                'count' => $total,
                'trend' => round(rand(-150, 250) / 10, 1),
            ],
            'rcsPenetration' => [
                'percentage' => $total > 0 ? round(($rcsCount / $total) * 100, 1) : 0,
                'rcsCount' => $rcsCount,
                'smsCount' => $smsCount,
                'trend' => round(rand(-50, 100) / 10, 1),
            ],
            'inboundReceived' => [
                'count' => rand(50, 200),
                'unreadCount' => rand(5, 25),
            ],
            'undeliveredMessages' => [
                'count' => $undelivered + $rejected + $expired,
                'trend' => round(rand(-80, 80) / 10, 1),
            ],
        ];
    }

    private function getEmptyKpiData(): array
    {
        return [
            'deliveryRate' => ['value' => 0, 'delivered' => 0, 'undelivered' => 0, 'rejected' => 0, 'trend' => 0],
            'spend' => ['amount' => 0, 'currency' => 'GBP', 'creditsUsed' => 0, 'isEstimated' => false],
            'rcsSeenRate' => ['value' => 0, 'hasRcsData' => false],
            'optOutRate' => ['value' => 0, 'optOutCount' => 0],
            'messagesSent' => ['count' => 0, 'trend' => 0],
            'rcsPenetration' => ['percentage' => 0, 'rcsCount' => 0, 'smsCount' => 0],
            'inboundReceived' => ['count' => 0, 'unreadCount' => 0],
            'undeliveredMessages' => ['count' => 0, 'trend' => 0],
        ];
    }

    public function getVolumeData(array $filters): array
    {
        $messages = $this->getFilteredMessages($filters);
        
        $dateFrom = !empty($filters['dateFrom']) ? Carbon::parse($filters['dateFrom']) : Carbon::now()->subDays(6);
        $dateTo = !empty($filters['dateTo']) ? Carbon::parse($filters['dateTo']) : Carbon::now();
        
        $dates = [];
        $current = $dateFrom->copy()->startOfDay();
        while ($current->lte($dateTo)) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }
        
        if (count($dates) > 30) {
            $dates = array_slice($dates, -30);
        }
        
        $dailyData = [];
        foreach ($dates as $date) {
            $dailyData[$date] = ['sms' => 0, 'rcs' => 0, 'total' => 0];
        }
        
        foreach ($messages as $msg) {
            $date = $msg['date'];
            if (isset($dailyData[$date])) {
                $dailyData[$date]['total']++;
                if ($msg['channel'] === 'SMS') {
                    $dailyData[$date]['sms']++;
                } else {
                    $dailyData[$date]['rcs']++;
                }
            }
        }
        
        $categories = [];
        $smsData = [];
        $rcsData = [];
        $totalData = [];
        
        foreach ($dates as $date) {
            $categories[] = Carbon::parse($date)->format('d M');
            $smsData[] = $dailyData[$date]['sms'];
            $rcsData[] = $dailyData[$date]['rcs'];
            $totalData[] = $dailyData[$date]['total'];
        }
        
        return [
            'categories' => $categories,
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

    public function getInboundVolumeData(array $filters): array
    {
        $dateFrom = !empty($filters['dateFrom']) ? Carbon::parse($filters['dateFrom']) : Carbon::now()->subDays(6);
        $dateTo = !empty($filters['dateTo']) ? Carbon::parse($filters['dateTo']) : Carbon::now();
        
        $dates = [];
        $current = $dateFrom->copy()->startOfDay();
        while ($current->lte($dateTo)) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }
        
        if (count($dates) > 30) {
            $dates = array_slice($dates, -30);
        }
        
        $dailyData = [];
        foreach ($dates as $date) {
            $smsCount = rand(5, 30);
            $rcsCount = rand(2, 15);
            $dailyData[$date] = [
                'sms' => $smsCount, 
                'rcs' => $rcsCount, 
                'total' => $smsCount + $rcsCount
            ];
        }
        
        $categories = [];
        $smsData = [];
        $rcsData = [];
        $totalData = [];
        
        foreach ($dates as $date) {
            $categories[] = Carbon::parse($date)->format('d M');
            $smsData[] = $dailyData[$date]['sms'];
            $rcsData[] = $dailyData[$date]['rcs'];
            $totalData[] = $dailyData[$date]['total'];
        }
        
        return [
            'categories' => $categories,
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

    public function getDeliveryStatusData(array $filters): array
    {
        $messages = $this->getFilteredMessages($filters);
        $total = count($messages);
        
        if ($total === 0) {
            return [
                'delivered' => ['count' => 0, 'percentage' => 0],
                'pending' => ['count' => 0, 'percentage' => 0],
                'undelivered' => ['count' => 0, 'percentage' => 0],
                'expired' => ['count' => 0, 'percentage' => 0],
                'rejected' => ['count' => 0, 'percentage' => 0],
                'total' => 0,
            ];
        }
        
        $statusCounts = array_count_values(array_column($messages, 'status'));
        
        return [
            'delivered' => [
                'count' => $statusCounts['delivered'] ?? 0,
                'percentage' => round((($statusCounts['delivered'] ?? 0) / $total) * 100, 1),
            ],
            'pending' => [
                'count' => $statusCounts['pending'] ?? 0,
                'percentage' => round((($statusCounts['pending'] ?? 0) / $total) * 100, 1),
            ],
            'undelivered' => [
                'count' => $statusCounts['undelivered'] ?? 0,
                'percentage' => round((($statusCounts['undelivered'] ?? 0) / $total) * 100, 1),
            ],
            'expired' => [
                'count' => $statusCounts['expired'] ?? 0,
                'percentage' => round((($statusCounts['expired'] ?? 0) / $total) * 100, 1),
            ],
            'rejected' => [
                'count' => $statusCounts['rejected'] ?? 0,
                'percentage' => round((($statusCounts['rejected'] ?? 0) / $total) * 100, 1),
            ],
            'total' => $total,
        ];
    }

    public function getTopCountriesData(array $filters): array
    {
        $messages = $this->getFilteredMessages($filters);
        
        $countryCounts = [];
        foreach ($messages as $msg) {
            $key = $msg['countryCode'];
            if (!isset($countryCounts[$key])) {
                $countryCounts[$key] = ['code' => $msg['countryCode'], 'name' => $msg['countryName'], 'count' => 0];
            }
            $countryCounts[$key]['count']++;
        }
        
        usort($countryCounts, fn($a, $b) => $b['count'] - $a['count']);
        $topCountries = array_slice($countryCounts, 0, 10);
        
        return [
            'countries' => $topCountries,
            'categories' => array_column($topCountries, 'code'),
            'values' => array_column($topCountries, 'count'),
        ];
    }

    public function getTopSenderIdsData(array $filters): array
    {
        $messages = $this->getFilteredMessages($filters);
        
        $senderStats = [];
        foreach ($messages as $msg) {
            $sid = $msg['senderId'];
            if (!isset($senderStats[$sid])) {
                $senderStats[$sid] = ['senderId' => $sid, 'messages' => 0, 'delivered' => 0];
            }
            $senderStats[$sid]['messages']++;
            if ($msg['status'] === 'delivered') {
                $senderStats[$sid]['delivered']++;
            }
        }
        
        foreach ($senderStats as &$stat) {
            $stat['deliveryRate'] = $stat['messages'] > 0 
                ? round(($stat['delivered'] / $stat['messages']) * 100, 1) 
                : 0;
        }
        
        usort($senderStats, fn($a, $b) => $b['messages'] - $a['messages']);
        
        return ['senderIds' => array_slice($senderStats, 0, 10)];
    }

    public function getPeakTimeData(array $filters): array
    {
        $messages = $this->getFilteredMessages($filters);
        
        $hourlyDistribution = array_fill(0, 24, 0);
        $dayDistribution = [];
        
        foreach ($messages as $msg) {
            $hour = $msg['hour'];
            $day = $msg['dayOfWeek'];
            $hourlyDistribution[$hour]++;
            $dayDistribution[$day] = ($dayDistribution[$day] ?? 0) + 1;
        }
        
        $peakHour = array_search(max($hourlyDistribution), $hourlyDistribution);
        $peakDay = !empty($dayDistribution) ? array_search(max($dayDistribution), $dayDistribution) : 'Monday';
        $peakVolume = $hourlyDistribution[$peakHour];
        
        $peakHourStart = sprintf('%02d:00', $peakHour);
        $peakHourEnd = sprintf('%02d:59', $peakHour);
        
        return [
            'peakHour' => $peakHourStart,
            'peakHourDisplay' => "{$peakHourStart}–{$peakHourEnd}",
            'peakDay' => $peakDay,
            'peakVolumeCount' => $peakVolume,
            'hourlyDistribution' => $hourlyDistribution,
            'bestDeliveryRate' => rand(960, 990) / 10,
            'recommendation' => "Consider scheduling campaigns during {$peakHourStart}–{$peakHourEnd} for optimal delivery.",
        ];
    }

    public function getFailureReasonsData(array $filters): array
    {
        $messages = $this->getFilteredMessages($filters);
        
        $failedMessages = array_filter($messages, fn($m) => in_array($m['status'], ['undelivered', 'expired', 'rejected']));
        $totalFailed = count($failedMessages);
        
        if ($totalFailed === 0) {
            return ['totalFailed' => 0, 'reasons' => []];
        }
        
        $reasonCounts = [];
        foreach ($failedMessages as $msg) {
            $reason = $msg['failureReason'] ?? 'Other';
            $reasonCounts[$reason] = ($reasonCounts[$reason] ?? 0) + 1;
        }
        
        arsort($reasonCounts);
        
        $reasons = [];
        foreach ($reasonCounts as $reason => $count) {
            $reasonInfo = collect($this->failureReasons)->firstWhere('reason', $reason) ?? $this->failureReasons[4];
            $reasons[] = [
                'reason' => $reason,
                'icon' => $reasonInfo['icon'],
                'iconColor' => $reasonInfo['iconColor'],
                'count' => $count,
                'percentage' => round(($count / $totalFailed) * 100, 1),
            ];
        }
        
        return [
            'totalFailed' => $totalFailed,
            'reasons' => $reasons,
        ];
    }

    public function getChannelSplitData(array $filters): array
    {
        $messages = $this->getFilteredMessages($filters);
        $total = count($messages);
        
        if ($total === 0) {
            return ['sms' => ['count' => 0, 'percentage' => 0], 'rcs' => ['count' => 0, 'percentage' => 0], 'total' => 0];
        }
        
        $smsCount = count(array_filter($messages, fn($m) => $m['channel'] === 'SMS'));
        $rcsCount = count(array_filter($messages, fn($m) => $m['channel'] === 'RCS'));
        
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

    public function getAvailableFilters(): array
    {
        return [
            'senderIds' => $this->senderIds,
            'subAccounts' => $this->subAccounts,
            'users' => $this->users,
            'origins' => $this->origins,
            'groupNames' => $this->groupNames,
            'countries' => $this->countries,
            'channels' => $this->channels,
            'statuses' => $this->statuses,
        ];
    }
}
