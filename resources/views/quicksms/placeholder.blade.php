@extends('quicksms.layout')

@section('title', $page_title)

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ $page_title }}</h1>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Purpose</h5>
    </div>
    <div class="card-body">
        <p class="mb-0">{{ $purpose }}</p>
    </div>
</div>

@if(!empty($sub_modules))
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Sub-Modules</h5>
    </div>
    <div class="card-body">
        <ul class="mb-0">
            @foreach($sub_modules as $module)
                <li>{{ $module }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="placeholder-panel">
    <i class="bi bi-tools"></i>
    <h4>Coming Soon</h4>
    <p class="mb-0">This feature is currently under development and will be available in a future update.</p>
</div>
@endsection
