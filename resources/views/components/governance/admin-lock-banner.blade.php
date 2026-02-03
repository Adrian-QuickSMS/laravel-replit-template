@props([
    'lockSource' => 'NONE',
    'lockReason' => null,
    'entityType' => 'item',
    'showContactSupport' => true,
    'compact' => false
])

@if($lockSource === 'ADMIN')
<div class="admin-lock-banner {{ $compact ? 'admin-lock-banner-compact' : '' }}" role="alert">
    <div class="admin-lock-banner-content">
        <div class="admin-lock-banner-icon">
            <i class="fas fa-lock"></i>
        </div>
        <div class="admin-lock-banner-text">
            <div class="admin-lock-banner-title">
                Disabled by QuickSMS Admin
            </div>
            @if($lockReason && !$compact)
            <div class="admin-lock-banner-reason">
                {{ $lockReason }}
            </div>
            @endif
            @if($showContactSupport && !$compact)
            <div class="admin-lock-banner-action">
                Please <a href="{{ route('support.contact') ?? '/support/contact' }}">contact support</a> for assistance.
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.admin-lock-banner {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border: 1px solid #fc8181;
    border-left: 4px solid #e53e3e;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}

.admin-lock-banner-compact {
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.5rem;
}

.admin-lock-banner-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.admin-lock-banner-icon {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e53e3e;
    border-radius: 50%;
    color: white;
    font-size: 0.875rem;
}

.admin-lock-banner-compact .admin-lock-banner-icon {
    width: 1.5rem;
    height: 1.5rem;
    font-size: 0.75rem;
}

.admin-lock-banner-text {
    flex: 1;
    min-width: 0;
}

.admin-lock-banner-title {
    font-weight: 600;
    color: #c53030;
    font-size: 0.9375rem;
    margin-bottom: 0.25rem;
}

.admin-lock-banner-compact .admin-lock-banner-title {
    font-size: 0.8125rem;
    margin-bottom: 0;
}

.admin-lock-banner-reason {
    color: #742a2a;
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 0.5rem;
}

.admin-lock-banner-action {
    color: #9b2c2c;
    font-size: 0.8125rem;
}

.admin-lock-banner-action a {
    color: #c53030;
    text-decoration: underline;
    font-weight: 500;
}

.admin-lock-banner-action a:hover {
    color: #9b2c2c;
}
</style>
@endif
