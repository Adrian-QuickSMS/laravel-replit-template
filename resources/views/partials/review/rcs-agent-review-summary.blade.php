{{-- 
    Shared RCS Agent Review Summary Partial
    CANONICAL COMPONENT: EXACT SAME UI used by both customer wizard (step 7) and admin approval overview
    DO NOT create separate layouts - this is the single source of truth
    DO NOT add admin-only sections here - admin-specific content goes in a separate partial
--}}
<style>
.review-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.review-section h6 {
    font-weight: 600;
    color: {{ $isAdmin ?? false ? 'var(--admin-primary, #1e3a5f)' : 'var(--pastel-purple, #886cc0)' }};
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
}

.review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8fafc;
}

.review-row:last-child {
    border-bottom: none;
}

.review-label {
    font-size: 0.8rem;
    color: #64748b;
}

.review-value {
    font-size: 0.8rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
}

.color-preview {
    display: inline-block;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
}

.review-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
    cursor: pointer;
}

.review-thumbnail:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

.review-thumbnail-logo {
    border-radius: 50%;
}

.review-thumbnail-hero {
    width: 120px;
    height: auto;
}
</style>

<div class="rcs-agent-review-summary" id="rcsAgentReviewSummary">
    <div class="row">
        <div class="col-lg-6">
            <div class="review-section">
                <h6>Branding & Identity</h6>
                <div class="review-row">
                    <span class="review-label">Agent Name</span>
                    <span class="review-value" id="reviewAgentName">{{ $data['agentName'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Description</span>
                    <span class="review-value" id="reviewDescription">{{ $data['description'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Brand Colour</span>
                    <span class="review-value d-flex align-items-center gap-2">
                        <span class="color-preview" id="reviewColorPreview" style="width: 18px; height: 18px; background: {{ $data['brandColor'] ?? '#886CC0' }};"></span>
                        <span id="reviewColor">{{ $data['brandColor'] ?? '-' }}</span>
                    </span>
                </div>
                <div class="review-row">
                    <span class="review-label">Logo</span>
                    <span class="review-value" id="reviewLogo">
                        @if(!empty($data['logoUrl']))
                            <img src="{{ $data['logoUrl'] }}" class="review-thumbnail review-thumbnail-logo" alt="Logo">
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </span>
                </div>
                <div class="review-row">
                    <span class="review-label">Hero Image</span>
                    <span class="review-value" id="reviewHero">
                        @if(!empty($data['heroUrl']))
                            <img src="{{ $data['heroUrl'] }}" class="review-thumbnail review-thumbnail-hero" alt="Hero Image">
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </span>
                </div>
            </div>
            
            <div class="review-section">
                <h6>Handset Contact Details</h6>
                <div class="review-row">
                    <span class="review-label">Phone</span>
                    <span class="review-value" id="reviewPhone">{{ $data['supportPhone'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Show Phone on Handset</span>
                    <span class="review-value" id="reviewShowPhone">{{ ($data['showPhone'] ?? false) ? 'Yes' : 'No' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Email</span>
                    <span class="review-value" id="reviewEmail">{{ $data['supportEmail'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Show Email on Handset</span>
                    <span class="review-value" id="reviewShowEmail">{{ ($data['showEmail'] ?? false) ? 'Yes' : 'No' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Website</span>
                    <span class="review-value" id="reviewWebsite">{{ $data['website'] ?? '-' }}</span>
                </div>
            </div>
            
            <div class="review-section">
                <h6>Compliance</h6>
                <div class="review-row">
                    <span class="review-label">Privacy Policy</span>
                    <span class="review-value" id="reviewPrivacy">{{ $data['privacyUrl'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Terms of Service</span>
                    <span class="review-value" id="reviewTerms">{{ $data['termsUrl'] ?? '-' }}</span>
                </div>
            </div>
            
            <div class="review-section">
                <h6>Billing & Use Case</h6>
                <div class="review-row">
                    <span class="review-label">Billing Category</span>
                    <span class="review-value" id="reviewBilling">{{ $data['billingCategory'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Use Case</span>
                    <span class="review-value" id="reviewUseCase">{{ $data['useCase'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Use Case Overview</span>
                    <span class="review-value" id="reviewUseCaseOverview">{{ $data['useCaseOverview'] ?? '-' }}</span>
                </div>
            </div>
            
            <div class="review-section">
                <h6>Messaging Behaviour</h6>
                <div class="review-row">
                    <span class="review-label">Campaign Frequency</span>
                    <span class="review-value" id="reviewFrequency">{{ $data['campaignFrequency'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">User Consent</span>
                    <span class="review-value" id="reviewUserConsent">{{ $data['userConsent'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Opt-out Available</span>
                    <span class="review-value" id="reviewOptOut">{{ $data['optOutAvailable'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Monthly Volume</span>
                    <span class="review-value" id="reviewVolume">{{ $data['monthlyVolume'] ?? '-' }}</span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="review-section">
                <h6>Company Information</h6>
                <div class="review-row">
                    <span class="review-label">Company Name</span>
                    <span class="review-value" id="reviewCompanyName">{{ $data['companyName'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Company Number</span>
                    <span class="review-value" id="reviewCompanyNumber">{{ $data['companyNumber'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Company Website</span>
                    <span class="review-value" id="reviewCompanyWebsite">{{ $data['companyWebsite'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Sector</span>
                    <span class="review-value" id="reviewSector">{{ $data['sector'] ?? '-' }}</span>
                </div>
            </div>
            
            <div class="review-section">
                <h6>Registered Address</h6>
                <div class="review-row">
                    <span class="review-label">Address Line 1</span>
                    <span class="review-value" id="reviewAddressLine1">{{ $data['addressLine1'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Address Line 2</span>
                    <span class="review-value" id="reviewAddressLine2">{{ $data['addressLine2'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">City / Town</span>
                    <span class="review-value" id="reviewAddressCity">{{ $data['addressCity'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Post Code</span>
                    <span class="review-value" id="reviewAddressPostCode">{{ $data['addressPostCode'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Country</span>
                    <span class="review-value" id="reviewAddressCountry">{{ $data['addressCountry'] ?? '-' }}</span>
                </div>
            </div>
            
            <div class="review-section">
                <h6>Approver Details</h6>
                <div class="review-row">
                    <span class="review-label">Approver Name</span>
                    <span class="review-value" id="reviewApproverName">{{ $data['approverName'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Approver Job Title</span>
                    <span class="review-value" id="reviewApproverJobTitle">{{ $data['approverJobTitle'] ?? '-' }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Approver Email</span>
                    <span class="review-value" id="reviewApproverEmail">{{ $data['approverEmail'] ?? '-' }}</span>
                </div>
            </div>
            
            <div class="review-section">
                <h6>Test Numbers</h6>
                <div class="review-row">
                    <span class="review-label">Numbers Added</span>
                    <span class="review-value" id="reviewTestNumbers">{{ count($data['testNumbers'] ?? []) }}</span>
                </div>
                @if(!empty($data['testNumbers']))
                <div class="review-row" id="reviewTestNumbersListRow">
                    <span class="review-label">Test Numbers</span>
                    <span class="review-value" id="reviewTestNumbersList">{{ implode(', ', $data['testNumbers'] ?? []) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
