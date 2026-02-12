@extends('layouts.default')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Reporting Dashboard</a></li>
        </ol>
    </div>

    <!-- KPI Widgets Row -->
    <div class="row">
        {{-- 1. Total Spend - Purple pill with "Estimated" --}}
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-3">Total Spend</h4>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fs-32 font-w700 mb-0">£12,450</h2>
                                <span class="badge badge-pill ms-3" style="background-color: #7c3aed; color: white;">Estimated</span>
                            </div>
                            <p class="mb-0 mt-2 text-muted">This month</p>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-pound-sign fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Messages Sent - Green pill with percentage change --}}
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-3">Messages Sent</h4>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fs-32 font-w700 mb-0">45,892</h2>
                                <span class="badge badge-pill ms-3" style="background-color: #22c55e; color: white;">+12.5%</span>
                            </div>
                            <p class="mb-0 mt-2 text-muted">vs last month</p>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-paper-plane fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Inbound - Yellow pill with unread count --}}
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-3">Inbound</h4>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fs-32 font-w700 mb-0">1,234</h2>
                                <span class="badge badge-pill ms-3" style="background-color: #eab308; color: white;">12 unread</span>
                            </div>
                            <p class="mb-0 mt-2 text-muted">Total messages</p>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-inbox fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Delivery Rate - Magenta pill with percentage change --}}
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-3">Delivery Rate</h4>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fs-32 font-w700 mb-0">98.7%</h2>
                                <span class="badge badge-pill ms-3" style="background-color: #d946ef; color: white;">+0.3%</span>
                            </div>
                            <p class="mb-0 mt-2 text-muted">Last 7 days</p>
                        </div>
                        <div style="color: #d946ef;">
                            <i class="fas fa-check-circle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. RCS Seen Rate - Green pill with exact count --}}
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-3">RCS Seen Rate</h4>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fs-32 font-w700 mb-0">85.4%</h2>
                                <span class="badge badge-pill ms-3" style="background-color: #22c55e; color: white;">3,892</span>
                            </div>
                            <p class="mb-0 mt-2 text-muted">Messages seen</p>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-eye fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. RCS Penetration - Magenta pill with percentage change --}}
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-3">RCS Penetration</h4>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fs-32 font-w700 mb-0">42.3%</h2>
                                <span class="badge badge-pill ms-3" style="background-color: #d946ef; color: white;">+5.2%</span>
                            </div>
                            <p class="mb-0 mt-2 text-muted">RCS capable devices</p>
                        </div>
                        <div style="color: #d946ef;">
                            <i class="fas fa-mobile-alt fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. Undelivered - Red pill with percentage change --}}
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-3">Undelivered</h4>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fs-32 font-w700 mb-0">567</h2>
                                <span class="badge badge-pill ms-3" style="background-color: #ef4444; color: white;">-0.8%</span>
                            </div>
                            <p class="mb-0 mt-2 text-muted">Failed deliveries</p>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 8. Opt-Out - Red pill with count --}}
        <div class="col-xl-3 col-lg-6 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-3">Opt-Out</h4>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fs-32 font-w700 mb-0">234</h2>
                                <span class="badge badge-pill ms-3" style="background-color: #ef4444; color: white;">89</span>
                            </div>
                            <p class="mb-0 mt-2 text-muted">This month</p>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-user-slash fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Reporting Charts/Tables can go here -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title">Messaging Performance Overview</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Detailed charts and analytics will be displayed here.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add any interactive functionality here
    console.log('Reporting Dashboard loaded');
</script>
@endpush
