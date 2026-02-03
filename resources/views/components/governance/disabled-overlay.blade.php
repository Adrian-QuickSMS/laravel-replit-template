@props([
    'lockSource' => 'NONE',
    'message' => 'This item has been disabled by QuickSMS Admin'
])

@if($lockSource === 'ADMIN')
<div class="admin-disabled-overlay">
    <div class="admin-disabled-overlay-content">
        <i class="fas fa-lock admin-disabled-overlay-icon"></i>
        <span class="admin-disabled-overlay-text">{{ $message }}</span>
    </div>
</div>

<style>
.admin-disabled-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: inherit;
}

.admin-disabled-overlay-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    text-align: center;
}

.admin-disabled-overlay-icon {
    font-size: 2rem;
    color: #e53e3e;
}

.admin-disabled-overlay-text {
    color: #742a2a;
    font-size: 0.875rem;
    font-weight: 500;
    max-width: 200px;
}
</style>
@endif
