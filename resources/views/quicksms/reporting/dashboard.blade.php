@extends('layouts.quicksms')

@section('title', 'Reporting Dashboard')

@push('styles')
<style>
.chart-placeholder {
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reporting') }}">Reporting</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>

    <!-- Section 1: Global Filters Bar -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label small fw-bold mb-1">Date Range</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                                <span class="text-muted small">to</span>
                                <input type="date" class="form-control form-control-sm" id="filterDateTo">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold mb-1">Sub Account</label>
                            <select class="form-select form-select-sm" id="filterSubAccount">
                                <option value="">All Sub Accounts</option>
                                <option value="main">Main Account</option>
                                <option value="marketing">Marketing Team</option>
                                <option value="support">Support Team</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold mb-1">Message Type</label>
                            <select class="form-select form-select-sm" id="filterMessageType">
                                <option value="">All Types</option>
                                <option value="sms">SMS</option>
                                <option value="rcs-basic">RCS Basic</option>
                                <option value="rcs-rich">RCS Rich</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold mb-1">Country</label>
                            <select class="form-select form-select-sm" id="filterCountry">
                                <option value="">All Countries</option>
                                <option value="uk">United Kingdom</option>
                                <option value="us">United States</option>
                                <option value="de">Germany</option>
                                <option value="fr">France</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm" id="btnApplyFilters">
                                    <i class="fas fa-filter me-1"></i> Apply
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Tiles & Charts Grid -->
    
    <!-- Row 1: KPI Summary Tiles -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-primary text-primary">
                            <i class="fas fa-paper-plane"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Total Messages</p>
                            <h4 class="mb-0">--</h4>
                            <small class="text-muted">Placeholder</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-success text-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Delivered</p>
                            <h4 class="mb-0">--</h4>
                            <small class="text-muted">Placeholder</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-warning text-warning">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Pending</p>
                            <h4 class="mb-0">--</h4>
                            <small class="text-muted">Placeholder</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="widget-stat card">
                <div class="card-body p-4">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-danger text-danger">
                            <i class="fas fa-times-circle"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Failed</p>
                            <h4 class="mb-0">--</h4>
                            <small class="text-muted">Placeholder</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Main Charts -->
    <div class="row">
        <div class="col-xl-8 col-lg-12">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Message Volume Over Time</h4>
                    <div class="card-tabs">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#daily" role="tab">Daily</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#weekly" role="tab">Weekly</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#monthly" role="tab">Monthly</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="daily" role="tabpanel">
                            <div id="messageVolumeChart" class="chart-placeholder">
                                <div class="text-center text-muted">
                                    <i class="fas fa-chart-line fa-3x mb-2"></i>
                                    <p class="mb-0">Line Chart Placeholder</p>
                                    <small>Message volume trend data</small>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="weekly" role="tabpanel">
                            <div class="chart-placeholder">
                                <div class="text-center text-muted">
                                    <i class="fas fa-chart-line fa-3x mb-2"></i>
                                    <p class="mb-0">Weekly View Placeholder</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="monthly" role="tabpanel">
                            <div class="chart-placeholder">
                                <div class="text-center text-muted">
                                    <i class="fas fa-chart-line fa-3x mb-2"></i>
                                    <p class="mb-0">Monthly View Placeholder</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Delivery Status</h4>
                </div>
                <div class="card-body">
                    <div id="deliveryStatusChart" class="chart-placeholder">
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-pie fa-3x mb-2"></i>
                            <p class="mb-0">Donut Chart Placeholder</p>
                            <small>Status distribution</small>
                        </div>
                    </div>
                    <div class="chart-point mt-3">
                        <ul class="chart-point-list mb-0">
                            <li><i class="fa fa-circle text-success me-1"></i> Delivered: --</li>
                            <li><i class="fa fa-circle text-warning me-1"></i> Pending: --</li>
                            <li><i class="fa fa-circle text-danger me-1"></i> Failed: --</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Secondary Charts -->
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Messages by Country</h4>
                </div>
                <div class="card-body">
                    <div id="countryChart" class="chart-placeholder">
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-bar fa-3x mb-2"></i>
                            <p class="mb-0">Bar Chart Placeholder</p>
                            <small>Geographic distribution</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-6">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Message Type Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="messageTypeChart" class="chart-placeholder">
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-pie fa-3x mb-2"></i>
                            <p class="mb-0">Pie Chart Placeholder</p>
                            <small>SMS vs RCS breakdown</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Additional KPIs -->
    <div class="row">
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Cost Summary</h4>
                </div>
                <div class="card-body">
                    <div id="costChart" class="chart-placeholder">
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-area fa-3x mb-2"></i>
                            <p class="mb-0">Area Chart Placeholder</p>
                            <small>Cost trend over time</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Top Senders</h4>
                </div>
                <div class="card-body">
                    <div id="topSendersChart" class="chart-placeholder">
                        <div class="text-center text-muted">
                            <i class="fas fa-chart-bar fa-3x mb-2"></i>
                            <p class="mb-0">Horizontal Bar Placeholder</p>
                            <small>Most active SenderIDs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-12">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Delivery Rate Trend</h4>
                </div>
                <div class="card-body">
                    <div id="deliveryRateChart" class="chart-placeholder">
                        <div class="text-center text-muted">
                            <i class="fas fa-percentage fa-3x mb-2"></i>
                            <p class="mb-0">Gauge/Line Placeholder</p>
                            <small>Delivery % over time</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Drill-Through Links -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Detailed Reports</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                            <a href="{{ route('reporting.message-log') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-list-alt fa-2x mb-2 d-block"></i>
                                Message Log
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                            <a href="{{ route('reporting.finance-data') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-coins fa-2x mb-2 d-block"></i>
                                Finance Data
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                            <a href="{{ route('reporting.invoices') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                Invoices
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                            <a href="{{ route('reporting.download-area') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-download fa-2x mb-2 d-block"></i>
                                Download Area
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/apexchart/apexchart.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Placeholder: Initialize charts with dummy data when ApexCharts is loaded
    if (typeof ApexCharts !== 'undefined') {
        
        // Message Volume Line Chart (dummy data)
        const volumeOptions = {
            series: [{
                name: 'Messages',
                data: [120, 180, 150, 220, 190, 250, 280, 310, 275, 320, 290, 350]
            }],
            chart: {
                height: 250,
                type: 'area',
                toolbar: { show: false }
            },
            colors: ['var(--primary)'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            }
        };
        const volumeChartEl = document.querySelector("#messageVolumeChart");
        if (volumeChartEl) {
            volumeChartEl.innerHTML = '';
            new ApexCharts(volumeChartEl, volumeOptions).render();
        }

        // Delivery Status Donut Chart (dummy data)
        const statusOptions = {
            series: [68, 22, 10],
            chart: {
                type: 'donut',
                height: 200
            },
            labels: ['Delivered', 'Pending', 'Failed'],
            colors: ['#28a745', '#ffc107', '#dc3545'],
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: () => '--'
                            }
                        }
                    }
                }
            }
        };
        const statusChartEl = document.querySelector("#deliveryStatusChart");
        if (statusChartEl) {
            statusChartEl.innerHTML = '';
            new ApexCharts(statusChartEl, statusOptions).render();
        }

        // Country Bar Chart (dummy data)
        const countryOptions = {
            series: [{
                name: 'Messages',
                data: [450, 320, 180, 120, 90]
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: { show: false }
            },
            colors: ['var(--primary)'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: ['UK', 'US', 'Germany', 'France', 'Ireland'],
            }
        };
        const countryChartEl = document.querySelector("#countryChart");
        if (countryChartEl) {
            countryChartEl.innerHTML = '';
            new ApexCharts(countryChartEl, countryOptions).render();
        }

        // Message Type Pie Chart (dummy data)
        const typeOptions = {
            series: [65, 25, 10],
            chart: {
                type: 'pie',
                height: 250
            },
            labels: ['SMS', 'RCS Basic', 'RCS Rich'],
            colors: ['#6c757d', '#17a2b8', '#6f42c1'],
            legend: {
                position: 'bottom'
            }
        };
        const typeChartEl = document.querySelector("#messageTypeChart");
        if (typeChartEl) {
            typeChartEl.innerHTML = '';
            new ApexCharts(typeChartEl, typeOptions).render();
        }

        // Cost Area Chart (dummy data)
        const costOptions = {
            series: [{
                name: 'Cost',
                data: [45, 52, 38, 65, 73, 68, 82, 94, 87, 105, 98, 120]
            }],
            chart: {
                height: 200,
                type: 'area',
                toolbar: { show: false }
            },
            colors: ['#28a745'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            }
        };
        const costChartEl = document.querySelector("#costChart");
        if (costChartEl) {
            costChartEl.innerHTML = '';
            new ApexCharts(costChartEl, costOptions).render();
        }

        // Top Senders Horizontal Bar (dummy data)
        const sendersOptions = {
            series: [{
                data: [320, 280, 220, 180, 150]
            }],
            chart: {
                type: 'bar',
                height: 200,
                toolbar: { show: false }
            },
            colors: ['var(--primary)'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: ['PROMO', 'ALERTS', 'QuickSMS', 'INFO', 'NOTIFY'],
            }
        };
        const sendersChartEl = document.querySelector("#topSendersChart");
        if (sendersChartEl) {
            sendersChartEl.innerHTML = '';
            new ApexCharts(sendersChartEl, sendersOptions).render();
        }

        // Delivery Rate Line Chart (dummy data)
        const rateOptions = {
            series: [{
                name: 'Delivery Rate %',
                data: [92, 94, 91, 95, 93, 96, 94, 97, 95, 98, 96, 97]
            }],
            chart: {
                height: 200,
                type: 'line',
                toolbar: { show: false }
            },
            colors: ['#17a2b8'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4 },
            yaxis: {
                min: 80,
                max: 100,
                labels: {
                    formatter: (val) => val + '%'
                }
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            }
        };
        const rateChartEl = document.querySelector("#deliveryRateChart");
        if (rateChartEl) {
            rateChartEl.innerHTML = '';
            new ApexCharts(rateChartEl, rateOptions).render();
        }
    }
    
    // Filter button handlers (placeholder)
    document.getElementById('btnApplyFilters')?.addEventListener('click', function() {
        console.log('[Dashboard] Apply filters clicked - TODO: Implement data refresh');
    });
    
    document.getElementById('btnResetFilters')?.addEventListener('click', function() {
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.getElementById('filterSubAccount').value = '';
        document.getElementById('filterMessageType').value = '';
        document.getElementById('filterCountry').value = '';
        console.log('[Dashboard] Filters reset');
    });
});
</script>
@endpush
