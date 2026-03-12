@extends('layouts.admin')

@section('title', 'Team Calendar')

@section('content')
<div class="container-fluid">
    <div class="page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.hr.dashboard') }}">HR</a></li>
            <li class="breadcrumb-item active">Team Calendar</li>
        </ol>
    </div>

    @php
        $currentDate = \Carbon\Carbon::create($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $startDayOfWeek = $startOfMonth->dayOfWeekIso;
        $daysInMonth = $endOfMonth->day;

        $bankHolidayDates = $bankHolidays->pluck('name', 'holiday_date')->mapWithKeys(fn($name, $date) => [\Carbon\Carbon::parse($date)->format('Y-m-d') => $name])->toArray();

        $absencesByDate = [];
        foreach ($absences as $abs) {
            $d = $abs->start_date->copy();
            while ($d->lte($abs->end_date)) {
                $key = $d->format('Y-m-d');
                if (!isset($absencesByDate[$key])) $absencesByDate[$key] = [];
                $absencesByDate[$key][] = $abs;
                $d->addDay();
            }
        }
    @endphp

    <div class="card">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <a href="?year={{ $prevMonth->year }}&month={{ $prevMonth->month }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-chevron-left"></i></a>
            <h4 class="card-title mb-0">{{ $currentDate->format('F Y') }}</h4>
            <a href="?year={{ $nextMonth->year }}&month={{ $nextMonth->month }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-chevron-right"></i></a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 14.28%;">Mon</th>
                            <th class="text-center" style="width: 14.28%;">Tue</th>
                            <th class="text-center" style="width: 14.28%;">Wed</th>
                            <th class="text-center" style="width: 14.28%;">Thu</th>
                            <th class="text-center" style="width: 14.28%;">Fri</th>
                            <th class="text-center bg-light" style="width: 14.28%;">Sat</th>
                            <th class="text-center bg-light" style="width: 14.28%;">Sun</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $dayCounter = 1; $started = false; @endphp
                        @for($week = 0; $week < 6; $week++)
                            @if($dayCounter > $daysInMonth) @break @endif
                            <tr>
                                @for($dow = 1; $dow <= 7; $dow++)
                                    @if(!$started && $dow < $startDayOfWeek)
                                        <td class="bg-light" style="height: 90px;"></td>
                                    @elseif($dayCounter <= $daysInMonth)
                                        @php
                                            $started = true;
                                            $cellDate = \Carbon\Carbon::create($year, $month, $dayCounter)->format('Y-m-d');
                                            $isWeekend = $dow >= 6;
                                            $isBankHol = isset($bankHolidayDates[$cellDate]);
                                            $isToday = $cellDate === today()->format('Y-m-d');
                                            $cellAbsences = $absencesByDate[$cellDate] ?? [];
                                        @endphp
                                        <td style="height: 90px; vertical-align: top; {{ $isWeekend ? 'background: #f8f9fa;' : '' }} {{ $isToday ? 'background: #e8f4fd; border: 2px solid #3699ff;' : '' }}">
                                            <div class="d-flex justify-content-between mb-1">
                                                <strong style="font-size: 0.85rem;">{{ $dayCounter }}</strong>
                                                @if($isBankHol)
                                                    <span class="badge bg-info badge-xs" title="{{ $bankHolidayDates[$cellDate] }}" style="font-size: 0.55rem;">BH</span>
                                                @endif
                                            </div>
                                            @foreach(array_slice($cellAbsences, 0, 3) as $abs)
                                                @php
                                                    $color = $abs->status === 'pending' ? '#ffc107' : '#28a745';
                                                @endphp
                                                <div style="font-size: 0.6rem; background: {{ $color }}20; color: {{ $color }}; border-left: 2px solid {{ $color }}; padding: 1px 4px; margin-bottom: 1px; border-radius: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $abs->employee?->full_name }} - Leave{{ $abs->status === 'pending' ? ' (pending)' : '' }}">
                                                    {{ Str::limit($abs->employee?->adminUser?->first_name ?? '?', 8) }}
                                                    @if($abs->status === 'pending') <i class="fas fa-clock" style="font-size: 0.5rem;"></i> @endif
                                                </div>
                                            @endforeach
                                            @if(count($cellAbsences) > 3)
                                                <div style="font-size: 0.55rem; color: #999;">+{{ count($cellAbsences) - 3 }} more</div>
                                            @endif
                                        </td>
                                        @php $dayCounter++; @endphp
                                    @else
                                        <td class="bg-light" style="height: 90px;"></td>
                                    @endif
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex gap-3 flex-wrap">
                <span><span style="display: inline-block; width: 12px; height: 12px; background: #28a745; border-radius: 2px;"></span> Leave</span>
                <span><span style="display: inline-block; width: 12px; height: 12px; background: #ffc107; border-radius: 2px;"></span> Pending</span>
                <span><span class="badge bg-info badge-xs" style="font-size: 0.55rem;">BH</span> Bank Holiday</span>
            </div>
        </div>
    </div>
</div>
@endsection
