@props([
    'lockSource' => 'NONE',
    'size' => 'default'
])

@php
$sizeClasses = [
    'small' => 'lock-badge-sm',
    'default' => '',
    'large' => 'lock-badge-lg'
];
$sizeClass = $sizeClasses[$size] ?? '';
@endphp

@if($lockSource === 'ADMIN')
<span class="lock-status-badge lock-status-admin {{ $sizeClass }}" title="Locked by QuickSMS Admin">
    <i class="fas fa-lock me-1"></i>Admin Locked
</span>
@elseif($lockSource === 'CUSTOMER')
<span class="lock-status-badge lock-status-customer {{ $sizeClass }}" title="Locked by customer">
    <i class="fas fa-user-lock me-1"></i>Locked
</span>
@endif

<style>
.lock-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
}

.lock-status-badge.lock-badge-sm {
    padding: 0.125rem 0.375rem;
    font-size: 0.6875rem;
}

.lock-status-badge.lock-badge-lg {
    padding: 0.375rem 0.625rem;
    font-size: 0.8125rem;
}

.lock-status-admin {
    background-color: #fed7d7;
    color: #c53030;
    border: 1px solid #fc8181;
}

.lock-status-customer {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
}
</style>
