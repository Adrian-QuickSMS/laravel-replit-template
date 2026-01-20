{{-- 
    Shared SenderID Review Summary Partial
    CANONICAL COMPONENT: EXACT SAME UI used by both customer wizard (step 5) and admin approval overview
    DO NOT create separate layouts - this is the single source of truth
    DO NOT add admin-only sections here - admin-specific content goes in a separate partial
    
    Section Order (matches customer Final Review):
    A) SenderID Details
    B) Brand Representation
    C) Intended Usage
    D) Validation Summary
--}}
<style>
.senderid-review-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.senderid-section-header {
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

.senderid-section-header .section-letter {
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

.senderid-review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8fafc;
    align-items: flex-start;
}

.senderid-review-row:last-child {
    border-bottom: none;
}

.senderid-review-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
    flex-shrink: 0;
}

.senderid-review-value {
    font-size: 0.8rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
}

.senderid-review-value.mono {
    font-family: 'SF Mono', 'Monaco', monospace;
    background: #f1f5f9;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.senderid-review-value.preserve-case {
    text-transform: none;
}

.senderid-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.senderid-type-badge.alphanumeric { background: #dbeafe; color: #1e40af; }
.senderid-type-badge.vmn { background: #d9f99d; color: #3f6212; }
.senderid-type-badge.shortcode { background: #fce7f3; color: #9d174d; }

.senderid-yes-no {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.senderid-yes-no.yes { background: #d9f99d; color: #3f6212; }
.senderid-yes-no.no { background: #fecaca; color: #991b1b; }

.senderid-channel-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: flex-end;
}

.senderid-channel-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.senderid-channel-pill.enabled { background: #d9f99d; color: #3f6212; }
.senderid-channel-pill.disabled { background: #f1f5f9; color: #94a3b8; }

.senderid-validation-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0;
    border-bottom: 1px solid #f8fafc;
}

.senderid-validation-item:last-child {
    border-bottom: none;
}

.senderid-validation-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    flex-shrink: 0;
}

.senderid-validation-icon.pass { background: #d9f99d; color: #3f6212; }
.senderid-validation-icon.fail { background: #fecaca; color: #991b1b; }
.senderid-validation-icon.warn { background: #fef3c7; color: #92400e; }

.senderid-validation-text {
    font-size: 0.8rem;
    color: #475569;
}

.senderid-validation-text strong {
    color: #1e293b;
}

.senderid-explanation {
    font-style: italic;
    color: #64748b;
    font-size: 0.8rem;
    background: #f8fafc;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    border-left: 3px solid {{ $isAdmin ?? false ? '#1e3a5f' : '#886cc0' }};
}
</style>

<div class="senderid-review-summary" id="senderIdReviewSummary">
    {{-- Section A: SenderID Details --}}
    <div class="senderid-review-section">
        <div class="senderid-section-header">
            <span class="section-letter">A</span>
            <i class="fas fa-id-badge"></i> SenderID Details
        </div>
        <div class="senderid-review-row">
            <span class="senderid-review-label">SenderID Value</span>
            <span class="senderid-review-value mono preserve-case" id="reviewSenderId">{{ $data['senderId'] ?? '-' }}</span>
        </div>
        <div class="senderid-review-row">
            <span class="senderid-review-label">Type</span>
            <span class="senderid-review-value">
                @php
                    $type = strtolower($data['type'] ?? 'alphanumeric');
                    $typeClass = match($type) {
                        'vmn', 'virtual mobile number' => 'vmn',
                        'shortcode', 'short code' => 'shortcode',
                        default => 'alphanumeric'
                    };
                    $typeLabel = match($type) {
                        'vmn', 'virtual mobile number' => 'VMN',
                        'shortcode', 'short code' => 'Shortcode',
                        default => 'Alphanumeric'
                    };
                @endphp
                <span class="senderid-type-badge {{ $typeClass }}" id="reviewType">{{ $typeLabel }}</span>
            </span>
        </div>
        @if(!empty($data['normalisedValue']) && $data['normalisedValue'] !== $data['senderId'])
        <div class="senderid-review-row">
            <span class="senderid-review-label">Normalised Value</span>
            <span class="senderid-review-value mono" id="reviewNormalisedValue">{{ $data['normalisedValue'] }}</span>
        </div>
        @endif
    </div>

    {{-- Section B: Brand Representation --}}
    <div class="senderid-review-section">
        <div class="senderid-section-header">
            <span class="section-letter">B</span>
            <i class="fas fa-building"></i> Brand Representation
        </div>
        <div class="senderid-review-row">
            <span class="senderid-review-label">Brand / Business Name</span>
            <span class="senderid-review-value" id="reviewBrand">{{ $data['brand'] ?? '-' }}</span>
        </div>
        <div class="senderid-review-row">
            <span class="senderid-review-label">Has permission to use this brand?</span>
            <span class="senderid-review-value" id="reviewPermission">
                @if($data['hasPermission'] ?? true)
                    <span class="senderid-yes-no yes"><i class="fas fa-check"></i> Yes</span>
                @else
                    <span class="senderid-yes-no no"><i class="fas fa-times"></i> No</span>
                @endif
            </span>
        </div>
        @if(!empty($data['explanation']))
        <div class="senderid-review-row">
            <span class="senderid-review-label">Explanation</span>
            <span class="senderid-review-value" style="max-width: 70%;">
                <div class="senderid-explanation" id="reviewExplanation">"{{ $data['explanation'] }}"</div>
            </span>
        </div>
        @endif
    </div>

    {{-- Section C: Intended Usage --}}
    <div class="senderid-review-section">
        <div class="senderid-section-header">
            <span class="section-letter">C</span>
            <i class="fas fa-broadcast-tower"></i> Intended Usage
        </div>
        <div class="senderid-review-row">
            <span class="senderid-review-label">Enabled Channels</span>
            <span class="senderid-review-value senderid-channel-pills" id="reviewChannels">
                <span class="senderid-channel-pill {{ ($data['channels']['portal'] ?? false) ? 'enabled' : 'disabled' }}">
                    <i class="fas {{ ($data['channels']['portal'] ?? false) ? 'fa-check' : 'fa-times' }}"></i> Portal
                </span>
                <span class="senderid-channel-pill {{ ($data['channels']['inbox'] ?? false) ? 'enabled' : 'disabled' }}">
                    <i class="fas {{ ($data['channels']['inbox'] ?? false) ? 'fa-check' : 'fa-times' }}"></i> Inbox
                </span>
                <span class="senderid-channel-pill {{ ($data['channels']['emailToSms'] ?? false) ? 'enabled' : 'disabled' }}">
                    <i class="fas {{ ($data['channels']['emailToSms'] ?? false) ? 'fa-check' : 'fa-times' }}"></i> Email-to-SMS
                </span>
                <span class="senderid-channel-pill {{ ($data['channels']['api'] ?? false) ? 'enabled' : 'disabled' }}">
                    <i class="fas {{ ($data['channels']['api'] ?? false) ? 'fa-check' : 'fa-times' }}"></i> API
                </span>
            </span>
        </div>
        @if(!empty($data['useCase']))
        <div class="senderid-review-row">
            <span class="senderid-review-label">Primary Use Case</span>
            <span class="senderid-review-value" id="reviewUseCase">{{ $data['useCase'] }}</span>
        </div>
        @endif
        @if(!empty($data['description']))
        <div class="senderid-review-row">
            <span class="senderid-review-label">Description</span>
            <span class="senderid-review-value" id="reviewDescription">{{ $data['description'] }}</span>
        </div>
        @endif
    </div>

    {{-- Section D: Validation Summary --}}
    <div class="senderid-review-section">
        <div class="senderid-section-header">
            <span class="section-letter">D</span>
            <i class="fas fa-check-double"></i> Validation Summary
        </div>
        @php
            $validation = $data['validation'] ?? [
                'characterCompliance' => true,
                'lengthCompliance' => true,
                'restrictedChars' => true,
                'ukRules' => true
            ];
        @endphp
        <div class="senderid-validation-item">
            <span class="senderid-validation-icon {{ ($validation['characterCompliance'] ?? true) ? 'pass' : 'fail' }}">
                <i class="fas {{ ($validation['characterCompliance'] ?? true) ? 'fa-check' : 'fa-times' }}"></i>
            </span>
            <span class="senderid-validation-text">
                <strong>Character Compliance:</strong> 
                {{ ($validation['characterCompliance'] ?? true) ? 'Only allowed characters used (A-Z, a-z, 0-9)' : 'Contains invalid characters' }}
            </span>
        </div>
        <div class="senderid-validation-item">
            <span class="senderid-validation-icon {{ ($validation['lengthCompliance'] ?? true) ? 'pass' : 'fail' }}">
                <i class="fas {{ ($validation['lengthCompliance'] ?? true) ? 'fa-check' : 'fa-times' }}"></i>
            </span>
            <span class="senderid-validation-text">
                <strong>Length Compliance:</strong> 
                {{ ($validation['lengthCompliance'] ?? true) ? 'Within 3-11 character limit' : 'Exceeds maximum length' }}
            </span>
        </div>
        <div class="senderid-validation-item">
            <span class="senderid-validation-icon {{ ($validation['restrictedChars'] ?? true) ? 'pass' : 'fail' }}">
                <i class="fas {{ ($validation['restrictedChars'] ?? true) ? 'fa-check' : 'fa-times' }}"></i>
            </span>
            <span class="senderid-validation-text">
                <strong>Restricted Characters:</strong> 
                {{ ($validation['restrictedChars'] ?? true) ? 'No restricted characters detected' : 'Contains restricted characters' }}
            </span>
        </div>
        <div class="senderid-validation-item">
            <span class="senderid-validation-icon {{ ($validation['ukRules'] ?? true) ? 'pass' : 'warn' }}">
                <i class="fas {{ ($validation['ukRules'] ?? true) ? 'fa-check' : 'fa-exclamation' }}"></i>
            </span>
            <span class="senderid-validation-text">
                <strong>UK Rules:</strong> 
                {{ ($validation['ukRules'] ?? true) ? 'Complies with UK carrier requirements' : 'May require additional validation' }}
            </span>
        </div>
    </div>
</div>
