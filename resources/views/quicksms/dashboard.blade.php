@extends('quicksms.layout')

@section('title', $page_title)

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $page_title }}</h1>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="bi bi-send-check text-primary" style="font-size: 2.5rem;"></i>
                <h3 class="mt-3 mb-1">0</h3>
                <p class="text-muted mb-0">Messages Sent Today</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="bi bi-inbox text-success" style="font-size: 2.5rem;"></i>
                <h3 class="mt-3 mb-1">0</h3>
                <p class="text-muted mb-0">Messages Received</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="bi bi-people text-info" style="font-size: 2.5rem;"></i>
                <h3 class="mt-3 mb-1">0</h3>
                <p class="text-muted mb-0">Total Contacts</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="bi bi-wallet2 text-warning" style="font-size: 2.5rem;"></i>
                <h3 class="mt-3 mb-1">0</h3>
                <p class="text-muted mb-0">Credits Remaining</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Purpose</h5>
    </div>
    <div class="card-body">
        <p class="mb-0">{{ $purpose }}</p>
    </div>
</div>

<div class="placeholder-panel">
    <i class="bi bi-speedometer2"></i>
    <h4>Coming Soon</h4>
    <p class="mb-0">Dashboard analytics and quick action widgets will be available here.</p>
</div>
@endsection
