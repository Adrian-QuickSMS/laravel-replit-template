{{-- 
    Shared SenderID Review Summary Partial
    CANONICAL COMPONENT: EXACT SAME UI used by both customer wizard (step 5) and admin approval overview
    DO NOT create separate layouts - this is the single source of truth
    DO NOT add admin-only sections here - admin-specific content goes in a separate partial
--}}
<style>
.review-summary {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.5rem;
}

.review-section {
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.review-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.review-section-title {
    font-weight: 600;
    color: {{ $isAdmin ?? false ? 'var(--admin-primary, #1e3a5f)' : 'var(--pastel-purple, #886cc0)' }};
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.review-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8fafc;
}

.review-row:last-child {
    border-bottom: none;
}

.review-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

.review-value {
    font-size: 0.875rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
}

.review-value.mono {
    font-family: 'SF Mono', monospace;
    background: #f1f5f9;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}
</style>

{{-- EXACT SAME UI AS CUSTOMER WIZARD STEP 5 REVIEW --}}
<div class="review-summary" id="senderIdReviewSummary">
    <div class="review-section">
        <div class="review-section-title"><i class="fas fa-id-badge me-2"></i>SenderID Details</div>
        <div class="review-row">
            <span class="review-label">Type:</span>
            <span class="review-value" id="reviewType">{{ $data['type'] ?? '-' }}</span>
        </div>
        <div class="review-row">
            <span class="review-label">SenderID Value:</span>
            <span class="review-value mono" id="reviewSenderId">{{ $data['senderId'] ?? '-' }}</span>
        </div>
    </div>
    
    <div class="review-section">
        <div class="review-section-title"><i class="fas fa-building me-2"></i>Business</div>
        <div class="review-row">
            <span class="review-label">Brand:</span>
            <span class="review-value" id="reviewBrand">{{ $data['brand'] ?? '-' }}</span>
        </div>
        <div class="review-row">
            <span class="review-label">Country:</span>
            <span class="review-value" id="reviewCountry">{{ $data['country'] ?? 'United Kingdom' }}</span>
        </div>
        <div class="review-row">
            <span class="review-label">Subaccount(s):</span>
            <span class="review-value" id="reviewSubaccount">{{ $data['subaccounts'] ?? '-' }}</span>
        </div>
        <div class="review-row" id="reviewUsersRow" style="{{ empty($data['users']) ? 'display: none;' : '' }}">
            <span class="review-label">Users:</span>
            <span class="review-value" id="reviewUsers">{{ $data['users'] ?? '-' }}</span>
        </div>
    </div>
    
    <div class="review-section">
        <div class="review-section-title"><i class="fas fa-envelope me-2"></i>Use Case</div>
        <div class="review-row">
            <span class="review-label">Primary Use:</span>
            <span class="review-value" id="reviewUseCase">{{ $data['useCase'] ?? '-' }}</span>
        </div>
        <div class="review-row">
            <span class="review-label">Description:</span>
            <span class="review-value" id="reviewDescription">{{ $data['description'] ?? '-' }}</span>
        </div>
    </div>
</div>
