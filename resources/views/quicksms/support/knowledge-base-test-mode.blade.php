@extends('layouts.quicksms')

@section('title', $page_title ?? 'Understanding Test Mode')

@push('styles')
<style>
    .kb-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .kb-header {
        margin-bottom: 2rem;
    }
    .kb-breadcrumb {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 1rem;
    }
    .kb-breadcrumb a {
        color: #886cc0;
        text-decoration: none;
    }
    .kb-article {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 2rem;
    }
    .kb-article h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1rem;
    }
    .kb-article h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #374151;
        margin-top: 2rem;
        margin-bottom: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #f3f4f6;
    }
    .kb-article h2:first-of-type {
        border-top: none;
        padding-top: 0;
    }
    .kb-article p {
        color: #4b5563;
        line-height: 1.7;
        margin-bottom: 1rem;
    }
    .kb-article ul, .kb-article ol {
        color: #4b5563;
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .kb-article li {
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }
    .info-box {
        background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin: 1.5rem 0;
        border-left: 4px solid #886cc0;
    }
    .info-box.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
        border-left-color: #f59e0b;
    }
    .info-box h4 {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #1f2937;
    }
    .info-box p {
        font-size: 0.875rem;
        margin-bottom: 0;
    }
    .feature-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }
    .feature-table th, .feature-table td {
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    .feature-table th {
        background: #f9fafb;
        font-weight: 600;
        font-size: 0.875rem;
        color: #374151;
    }
    .feature-table td {
        font-size: 0.875rem;
        color: #4b5563;
    }
    .kb-footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }
    .helpful-section {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .helpful-section span {
        font-size: 0.875rem;
        color: #6b7280;
    }
</style>
@endpush

@section('content')
<div class="kb-container">
    <div class="kb-header">
        <div class="kb-breadcrumb">
            <a href="{{ route('support.knowledge-base') }}">Knowledge Base</a>
            <span class="mx-2">/</span>
            <span>Getting Started</span>
        </div>
    </div>

    <article class="kb-article">
        <h1>Understanding Test Mode</h1>
        <p class="text-muted">Last updated: January 2026</p>

        <p>
            When you first create a QuickSMS account, it starts in <strong>Test Mode</strong>. This allows you to 
            explore the platform and send test messages before committing to a purchase. Here's everything you 
            need to know about Test Mode and how to activate your account.
        </p>

        <h2>What is Test Mode?</h2>
        <p>
            Test Mode is a restricted state for new accounts that lets you experience QuickSMS features without 
            incurring charges. While in Test Mode, you can send messages to approved test numbers only, giving 
            you a chance to verify that the platform meets your needs.
        </p>

        <h2>Test Mode Limitations</h2>
        <table class="feature-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Test Mode</th>
                    <th>Live Account</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Message Limit</td>
                    <td>100 fragments total</td>
                    <td>Unlimited (based on credits)</td>
                </tr>
                <tr>
                    <td>Recipients</td>
                    <td>Approved test numbers only</td>
                    <td>Any valid mobile number</td>
                </tr>
                <tr>
                    <td>SenderID</td>
                    <td>"QuickSMS Test Sender"</td>
                    <td>Your registered SenderIDs</td>
                </tr>
                <tr>
                    <td>Message Content</td>
                    <td>Includes test disclaimer</td>
                    <td>Your content only</td>
                </tr>
            </tbody>
        </table>

        <div class="info-box">
            <h4><i class="fas fa-info-circle me-2"></i>About the Disclaimer</h4>
            <p>
                All test messages include an automatic disclaimer: "Sent from QuickSMS Test Account - Not for production use". 
                This 168-character disclaimer is included in your fragment count. Once activated, this disclaimer is removed 
                and your messages will contain only your content.
            </p>
        </div>

        <h2>Approved Test Numbers</h2>
        <p>
            For security and fraud prevention, you can only send test messages to pre-approved numbers. These are:
        </p>
        <ul>
            <li><strong>Your MFA-verified mobile number</strong> - The number you used during signup</li>
            <li><strong>Admin-approved numbers</strong> - Additional numbers approved by QuickSMS support</li>
        </ul>

        <div class="info-box warning">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Important</h4>
            <p>
                Test numbers are not self-service. If you need to add additional test numbers, please contact 
                QuickSMS support with a valid business reason.
            </p>
        </div>

        <h2>How to Activate Your Account</h2>
        <p>
            Activating your account is straightforward and requires two steps:
        </p>
        <ol>
            <li>
                <strong>Complete Your Account Details</strong> - Provide your company information, business address, 
                website, sector, and VAT details in the <a href="{{ route('account.details') }}">Account Details</a> section.
            </li>
            <li>
                <strong>Make Your First Payment</strong> - Purchase message credits or a number package to unlock 
                full messaging capabilities.
            </li>
        </ol>
        <p>
            Once both steps are complete, your account is automatically activated and you can start sending 
            messages to any valid mobile number using your registered SenderIDs.
        </p>

        <div class="mt-4">
            <a href="{{ route('account.activate') }}" class="btn btn-primary">
                <i class="fas fa-rocket me-2"></i>Activate My Account
            </a>
        </div>

        <h2>Enterprise & NHS Customers</h2>
        <p>
            If you're an enterprise customer, NHS organization, or require a post-paid billing arrangement, 
            please contact our sales team. We can activate your account with custom billing terms without 
            requiring an upfront payment.
        </p>
        <p>
            <a href="{{ route('support.create-ticket') }}">Contact Sales</a> to discuss your requirements.
        </p>

        <div class="kb-footer">
            <div class="helpful-section">
                <span>Was this article helpful?</span>
                <button class="btn btn-sm btn-outline-success"><i class="fas fa-thumbs-up me-1"></i> Yes</button>
                <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-thumbs-down me-1"></i> No</button>
            </div>
        </div>
    </article>

    <div class="mt-4">
        <a href="{{ route('support.knowledge-base') }}" class="text-muted">
            <i class="fas fa-arrow-left me-1"></i> Back to Knowledge Base
        </a>
    </div>
</div>
@endsection
