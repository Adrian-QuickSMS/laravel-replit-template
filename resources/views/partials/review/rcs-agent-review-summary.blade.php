{{-- 
    Shared RCS Agent Review Summary Partial
    CANONICAL COMPONENT: EXACT SAME UI used by both customer wizard (step 7) and admin approval overview
    DO NOT create separate layouts - this is the single source of truth
    DO NOT add admin-only sections here - admin-specific content goes in a separate partial
    
    Section Order (matches customer Step 7 Review):
    A) Agent Identity & Branding
    B) Handset Contact Details
    C) Compliance URLs
    D) Agent Classification
    E) Messaging Behaviour
    F) Test Numbers
    G) Company & Approver Details
--}}
<style>
.rcs-review-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.rcs-section-header {
    font-weight: 600;
    color: {{ $isAdmin ?? false ? 'var(--admin-primary, #1e3a5f)' : 'var(--pastel-purple, #886cc0)' }};
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rcs-section-header .section-letter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background: {{ $isAdmin ?? false ? '#1e3a5f' : '#886cc0' }};
    color: #fff;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 700;
}

.rcs-review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8fafc;
    align-items: flex-start;
}

.rcs-review-row:last-child {
    border-bottom: none;
}

.rcs-review-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
    flex-shrink: 0;
}

.rcs-review-value {
    font-size: 0.8rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
}

.rcs-review-value.mono {
    font-family: 'SF Mono', 'Monaco', monospace;
    background: #f1f5f9;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-size: 0.75rem;
}

.rcs-color-swatch {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.rcs-color-swatch .swatch {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 2px solid #e2e8f0;
}

.rcs-toggle-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.rcs-toggle-badge.shown { background: #d9f99d; color: #3f6212; }
.rcs-toggle-badge.hidden { background: #f1f5f9; color: #64748b; }

.rcs-asset-preview {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.rcs-logo-preview {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.rcs-hero-preview {
    width: 120px;
    height: auto;
    max-height: 80px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.rcs-overlap-preview {
    position: relative;
    width: 140px;
    height: 90px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.rcs-overlap-preview .hero-bg {
    width: 100%;
    height: 60px;
    object-fit: cover;
}

.rcs-overlap-preview .logo-overlay {
    position: absolute;
    bottom: 5px;
    left: 10px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #fff;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.rcs-test-numbers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    justify-content: flex-end;
}

.rcs-test-number-pill {
    background: #f1f5f9;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-family: 'SF Mono', monospace;
}

.rcs-yes-no {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.rcs-yes-no.yes { background: #d9f99d; color: #3f6212; }
.rcs-yes-no.no { background: #fecaca; color: #991b1b; }
</style>

<div class="rcs-agent-review-summary" id="rcsAgentReviewSummary">
    {{-- Section A: Agent Identity & Branding --}}
    <div class="rcs-review-section">
        <div class="rcs-section-header">
            <span class="section-letter">A</span>
            <i class="fas fa-palette"></i> Agent Identity & Branding
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Agent Name <span class="text-muted" style="font-size: 0.65rem;">(max 25 char)</span></span>
            <span class="rcs-review-value" id="reviewAgentName">{{ $data['agentName'] ?? '-' }}</span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Agent Description</span>
            <span class="rcs-review-value" id="reviewDescription">{{ $data['description'] ?? '-' }}</span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Brand Colour</span>
            <span class="rcs-review-value rcs-color-swatch">
                <span class="swatch" id="reviewColorSwatch" style="background: {{ $data['brandColor'] ?? '#886CC0' }};"></span>
                <span class="mono" id="reviewColorHex">{{ $data['brandColor'] ?? '-' }}</span>
            </span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Logo <span class="text-muted" style="font-size: 0.65rem;">(circle-crop)</span></span>
            <span class="rcs-review-value" id="reviewLogo">
                @if(!empty($data['logoUrl']))
                    <img src="{{ $data['logoUrl'] }}" class="rcs-logo-preview" alt="Logo">
                @else
                    <span class="text-muted">Not uploaded</span>
                @endif
            </span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Hero/Banner Image</span>
            <span class="rcs-review-value" id="reviewHero">
                @if(!empty($data['heroUrl']))
                    <img src="{{ $data['heroUrl'] }}" class="rcs-hero-preview" alt="Hero">
                @else
                    <span class="text-muted">Not uploaded</span>
                @endif
            </span>
        </div>
        @if(!empty($data['logoUrl']) && !empty($data['heroUrl']))
        <div class="rcs-review-row">
            <span class="rcs-review-label">Overlap Preview</span>
            <span class="rcs-review-value">
                <div class="rcs-overlap-preview" id="reviewOverlapPreview">
                    <img src="{{ $data['heroUrl'] }}" class="hero-bg" alt="Hero BG">
                    <img src="{{ $data['logoUrl'] }}" class="logo-overlay" alt="Logo Overlay">
                </div>
            </span>
        </div>
        @endif
    </div>

    {{-- Section B: Handset Contact Details --}}
    <div class="rcs-review-section">
        <div class="rcs-section-header">
            <span class="section-letter">B</span>
            <i class="fas fa-mobile-alt"></i> Handset Contact Details
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Call</span>
            <span class="rcs-review-value">
                <span id="reviewPhone">{{ $data['supportPhone'] ?? '-' }}</span>
                @if($data['showPhone'] ?? false)
                    <span class="rcs-toggle-badge shown ms-2"><i class="fas fa-eye"></i> Displayed</span>
                @else
                    <span class="rcs-toggle-badge hidden ms-2"><i class="fas fa-eye-slash"></i> Hidden</span>
                @endif
            </span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Website</span>
            <span class="rcs-review-value" id="reviewWebsite">{{ $data['website'] ?? '-' }}</span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Email</span>
            <span class="rcs-review-value">
                <span id="reviewEmail">{{ $data['supportEmail'] ?? '-' }}</span>
                @if($data['showEmail'] ?? false)
                    <span class="rcs-toggle-badge shown ms-2"><i class="fas fa-eye"></i> Displayed</span>
                @else
                    <span class="rcs-toggle-badge hidden ms-2"><i class="fas fa-eye-slash"></i> Hidden</span>
                @endif
            </span>
        </div>
    </div>

    {{-- Section C: Compliance URLs --}}
    <div class="rcs-review-section">
        <div class="rcs-section-header">
            <span class="section-letter">C</span>
            <i class="fas fa-shield-alt"></i> Compliance URLs
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Privacy Policy URL</span>
            <span class="rcs-review-value" id="reviewPrivacy">{{ $data['privacyUrl'] ?? '-' }}</span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Terms of Service URL</span>
            <span class="rcs-review-value" id="reviewTerms">{{ $data['termsUrl'] ?? '-' }}</span>
        </div>
    </div>

    {{-- Section D: Agent Classification --}}
    <div class="rcs-review-section">
        <div class="rcs-section-header">
            <span class="section-letter">D</span>
            <i class="fas fa-tags"></i> Agent Classification
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Billing Category</span>
            <span class="rcs-review-value" id="reviewBilling">{{ $data['billingCategory'] ?? '-' }}</span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Use Case</span>
            <span class="rcs-review-value" id="reviewUseCase">{{ $data['useCase'] ?? '-' }}</span>
        </div>
    </div>

    {{-- Section E: Messaging Behaviour --}}
    <div class="rcs-review-section">
        <div class="rcs-section-header">
            <span class="section-letter">E</span>
            <i class="fas fa-comments"></i> Messaging Behaviour
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Use Case Description</span>
            <span class="rcs-review-value" id="reviewUseCaseOverview">{{ $data['useCaseOverview'] ?? '-' }}</span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Opt-in Consent</span>
            <span class="rcs-review-value" id="reviewUserConsent">
                @if($data['userConsent'] ?? false)
                    <span class="rcs-yes-no yes"><i class="fas fa-check"></i> Yes</span>
                    <span class="ms-1" style="font-size: 0.7rem; color: #64748b;">{{ $data['userConsentType'] ?? 'Opted in' }}</span>
                @else
                    <span class="rcs-yes-no no"><i class="fas fa-times"></i> No</span>
                @endif
            </span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Opt-out Supported</span>
            <span class="rcs-review-value" id="reviewOptOut">
                @if($data['optOutAvailable'] ?? false)
                    <span class="rcs-yes-no yes"><i class="fas fa-check"></i> Yes</span>
                @else
                    <span class="rcs-yes-no no"><i class="fas fa-times"></i> No</span>
                @endif
            </span>
        </div>
        <div class="rcs-review-row">
            <span class="rcs-review-label">Monthly Volume Estimate</span>
            <span class="rcs-review-value" id="reviewVolume">{{ $data['monthlyVolume'] ?? '-' }}</span>
        </div>
    </div>

    {{-- Section F: Test Numbers --}}
    <div class="rcs-review-section">
        <div class="rcs-section-header">
            <span class="section-letter">F</span>
            <i class="fas fa-phone"></i> Test Numbers
            <span class="badge bg-secondary ms-auto" style="font-size: 0.65rem;">{{ count($data['testNumbers'] ?? []) }} / 20</span>
        </div>
        @if(!empty($data['testNumbers']) && count($data['testNumbers']) > 0)
        <div class="rcs-review-row">
            <span class="rcs-review-label">Numbers</span>
            <span class="rcs-review-value rcs-test-numbers-list" id="reviewTestNumbersList">
                @foreach($data['testNumbers'] as $number)
                <span class="rcs-test-number-pill">{{ $number }}</span>
                @endforeach
            </span>
        </div>
        @else
        <div class="rcs-review-row">
            <span class="rcs-review-label">Numbers</span>
            <span class="rcs-review-value text-muted">No test numbers added</span>
        </div>
        @endif
    </div>

    {{-- Section G: Company & Approver Details --}}
    <div class="rcs-review-section">
        <div class="rcs-section-header">
            <span class="section-letter">G</span>
            <i class="fas fa-building"></i> Company & Approver Details
        </div>
        
        <div style="margin-bottom: 0.75rem;">
            <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Company Information</div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Company Name</span>
                <span class="rcs-review-value" id="reviewCompanyName">{{ $data['companyName'] ?? '-' }}</span>
            </div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Trading Name</span>
                <span class="rcs-review-value" id="reviewTradingName">{{ $data['tradingName'] ?? $data['companyName'] ?? '-' }}</span>
            </div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Company Number</span>
                <span class="rcs-review-value mono" id="reviewCompanyNumber">{{ $data['companyNumber'] ?? '-' }}</span>
            </div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Website</span>
                <span class="rcs-review-value" id="reviewCompanyWebsite">{{ $data['companyWebsite'] ?? '-' }}</span>
            </div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Sector</span>
                <span class="rcs-review-value" id="reviewSector">{{ $data['sector'] ?? '-' }}</span>
            </div>
        </div>
        
        <div style="margin-bottom: 0.75rem;">
            <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Registered Address</div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Address</span>
                <span class="rcs-review-value" id="reviewAddress">
                    {{ $data['addressLine1'] ?? '' }}{{ !empty($data['addressLine2']) ? ', ' . $data['addressLine2'] : '' }}<br>
                    {{ $data['addressCity'] ?? '' }} {{ $data['addressPostCode'] ?? '' }}<br>
                    {{ $data['addressCountry'] ?? 'United Kingdom' }}
                </span>
            </div>
        </div>
        
        <div>
            <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Approver Details</div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Approver Name</span>
                <span class="rcs-review-value" id="reviewApproverName">{{ $data['approverName'] ?? '-' }}</span>
            </div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Job Title</span>
                <span class="rcs-review-value" id="reviewApproverJobTitle">{{ $data['approverJobTitle'] ?? '-' }}</span>
            </div>
            <div class="rcs-review-row">
                <span class="rcs-review-label">Email</span>
                <span class="rcs-review-value" id="reviewApproverEmail">{{ $data['approverEmail'] ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
