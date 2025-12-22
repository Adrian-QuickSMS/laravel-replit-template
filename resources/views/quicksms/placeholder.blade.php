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
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ $page_title }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5><i class="fas fa-info-circle me-2 text-primary"></i>Purpose</h5>
                        <p class="text-muted">{{ $purpose }}</p>
                    </div>
                    
                    @if(!empty($sub_modules))
                    <div class="mb-4">
                        <h5><i class="fas fa-list me-2 text-primary"></i>Sub-Modules</h5>
                        <ul class="list-group list-group-flush">
                            @foreach($sub_modules as $module)
                            <li class="list-group-item bg-transparent">
                                <i class="fas fa-chevron-right me-2 text-primary"></i>{{ $module }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="text-center py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px dashed #dee2e6; border-radius: 0.5rem;">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <h4 class="text-secondary">Coming Soon</h4>
                        <p class="text-muted mb-0">This feature is currently under development and will be available in a future update.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
