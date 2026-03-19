@extends('layouts.quicksms')

@section('title', $page_title)

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">{{ $page_title }}</li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="qs-nav-header mb-4">
                <h3 class="qs-nav-title">{{ $page_title }}</h3>
                <p class="qs-nav-subtitle">{{ $purpose }}</p>
            </div>

            @if(!empty($sub_modules))
            <div class="qs-nav-tiles">
                @foreach($sub_modules as $module)
                <a href="{{ $module['route'] ?? '#' }}" class="qs-nav-tile">
                    <div class="qs-nav-tile-icon">
                        <i class="{{ $module['icon'] ?? 'fas fa-circle' }}"></i>
                    </div>
                    <h5 class="qs-nav-tile-title">{{ $module['title'] ?? 'Untitled' }}</h5>
                    <p class="qs-nav-tile-desc">{{ $module['description'] ?? '' }}</p>
                    <div class="qs-nav-tile-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px dashed #dee2e6; border-radius: 0.5rem;">
                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                <h4 class="text-secondary">Coming Soon</h4>
                <p class="text-muted mb-0">This feature is currently under development and will be available in a future update.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
