{{--
    Canonical Review Renderer Blade Wrapper
    
    Usage:
    @include('partials.review.canonical-review-renderer', [
        'containerId' => 'myReviewContainer',
        'submissionType' => 'SENDERID', // or 'RCS_AGENT'
        'submissionVersionId' => 'abc-123',
        'mode' => 'customer_review', // or 'admin_review'
        'data' => $submissionData
    ])
--}}

@php
    $containerId = $containerId ?? 'canonicalReviewContainer_' . uniqid();
    $submissionType = $submissionType ?? 'SENDERID';
    $submissionVersionId = $submissionVersionId ?? null;
    $mode = $mode ?? 'customer_review';
    $readOnly = $readOnly ?? true;
    $data = $data ?? [];
@endphp

<div id="{{ $containerId }}"></div>

@push('scripts')
<script src="{{ asset('js/canonical-review-renderer.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewRenderer = CanonicalReviewRenderer.create({
        container: '#{{ $containerId }}',
        submissionType: CanonicalReviewRenderer.SubmissionType.{{ $submissionType }},
        submissionVersionId: {!! json_encode($submissionVersionId) !!},
        readOnly: {{ $readOnly ? 'true' : 'false' }},
        mode: CanonicalReviewRenderer.Mode.{{ $mode === 'admin_review' ? 'ADMIN_REVIEW' : 'CUSTOMER_REVIEW' }},
        data: {!! json_encode($data) !!}
    });
    
    // Expose instance on container element for external access
    document.getElementById('{{ $containerId }}').reviewRenderer = reviewRenderer;
});
</script>
@endpush
