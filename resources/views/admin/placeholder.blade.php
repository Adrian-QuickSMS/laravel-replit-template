@extends('layouts.admin')

@section('title', $page_title ?? 'Admin')

@section('content')
<div class="container-fluid" style="padding: 1.5rem;">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <span>{{ $page_title ?? 'Page' }}</span>
    </div>

    <div class="admin-card">
        <div class="card-header">
            <h5>{{ $page_title ?? 'Admin Module' }}</h5>
        </div>
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Module Under Development</h5>
                <p class="text-muted mb-4">This admin module is being built. It will provide cross-client visibility and control for this feature area.</p>
                
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            @if(isset($module_features))
            <div class="mt-4 pt-4 border-top">
                <h6 class="mb-3">Planned Features:</h6>
                <ul class="list-unstyled">
                    @foreach($module_features as $feature)
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-muted me-2"></i>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
