@extends('layouts.quicksms')

@section('title', 'Inbox')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<style>
/* Full viewport height layout chain */
.inbox-page-container {
    padding: 0 !important;
    height: calc(100vh - 10rem) !important;
    display: flex !important;
    flex-direction: column !important;
}
.inbox-page-container .row {
    flex: 1 !important;
    min-height: 0 !important;
    margin: 0 !important;
}
.inbox-page-container .col-xl-12 {
    padding: 0 !important;
    height: 100% !important;
    min-height: 0 !important;
}
.inbox-main-card {
    height: 100% !important;
    min-height: 0 !important;
    border-radius: 0 !important;
    margin: 0 !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
}
.inbox-main-card > .card-body {
    flex: 1 !important;
    min-height: 0 !important;
}
.chat-left-body {
    width: 300px !important;
    min-width: 280px !important;
    max-width: 340px !important;
    flex-shrink: 0 !important;
    display: flex;
    flex-direction: column;
    height: 100%;
    border-right: 1px solid #e9ecef;
}
@media (max-width: 1440px) {
    .chat-left-body {
        width: 280px !important;
        min-width: 260px !important;
    }
}
@media (max-width: 1366px) {
    .chat-left-body {
        width: 260px !important;
        min-width: 240px !important;
    }
}
.chat-sidebar {
    flex: 1;
    overflow-y: auto;
}
#chatPaneWrapper {
    flex: 1 !important;
    min-width: 0 !important;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}
#chatHeader {
    flex: 0 0 auto !important;
    background: var(--bs-card-bg, #fff) !important;
}
/* Hide any stray form-switch/toggle elements in chat header */
#chatHeader .form-switch,
#chatHeader .form-check,
#chatHeader input[type="checkbox"] {
    display: none !important;
}
#chatSearchBar {
    flex: 0 0 auto !important;
    background: var(--bs-card-bg, #fff) !important;
}
#chatArea {
    flex: 1 1 0 !important;
    min-height: 0 !important;
    overflow-y: auto !important;
    background: var(--bs-card-bg, #fff) !important;
    padding: 15px !important;
}
#replyComposerCard {
    flex: 0 0 auto !important;
    z-index: 100 !important;
    max-height: 280px !important;
    overflow: visible !important;
    margin: 0 !important;
    border-radius: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-bottom: 0 !important;
    background: var(--bs-card-bg, #fff) !important;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.08) !important;
}
/* Dark mode overrides */
[data-theme-version="dark"] #chatHeader,
[data-theme-version="dark"] #chatSearchBar,
[data-theme-version="dark"] #chatArea,
[data-theme-version="dark"] #replyComposerCard {
    background: #1e1e28 !important;
}
[data-theme-version="dark"] #replyComposerCard {
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3) !important;
    border-top-color: #3f3f4a !important;
}
[data-theme-version="dark"] .chat-bx:hover {
    background-color: rgba(111, 66, 193, 0.15) !important;
}
[data-theme-version="dark"] .chat-bx.active {
    background-color: rgba(111, 66, 193, 0.2) !important;
}
/* Dark mode: Inbound message bubble - WHITE text on dark background */
[data-theme-version="dark"] .message-received,
[data-theme-version="dark"] .message-received p {
    background-color: #3a3a4a !important;
    border-color: #4a4a5a !important;
    color: #ffffff !important;
}
/* Dark mode: Channel button text should be WHITE - using highest specificity */
body[data-theme-version="dark"] #replyComposerCard .btn-group label.btn,
body[data-theme-version="dark"] #replyComposerCard .btn-group label.btn i,
body[data-theme-version="dark"] #replyComposerCard .btn-group label.btn-outline-primary,
body[data-theme-version="dark"] #replyComposerCard .btn-group label.btn-outline-primary i {
    color: #ffffff !important;
    border-color: rgba(136, 108, 192, 0.5) !important;
}
body[data-theme-version="dark"] #replyComposerCard .btn-group label.btn:hover,
body[data-theme-version="dark"] #replyComposerCard .btn-group label.btn-outline-primary:hover {
    background-color: rgba(136, 108, 192, 0.3) !important;
    color: #ffffff !important;
}
body[data-theme-version="dark"] #replyComposerCard .btn-group input.btn-check:checked + label.btn,
body[data-theme-version="dark"] #replyComposerCard .btn-group input.btn-check:checked + label.btn i,
body[data-theme-version="dark"] #replyComposerCard .btn-group .btn-check:checked + label.btn-outline-primary,
body[data-theme-version="dark"] #replyComposerCard .btn-group .btn-check:checked + label.btn-outline-primary i {
    background-color: var(--primary) !important;
    color: #ffffff !important;
}
/* Dark mode: Separator lines/borders should be GREY not white */
[data-theme-version="dark"] .border-bottom {
    border-bottom-color: #3f3f4a !important;
}
[data-theme-version="dark"] .date-separator::before {
    background: #3f3f4a !important;
}
[data-theme-version="dark"] .date-separator span {
    background: #1e1e28 !important;
    color: #888 !important;
}
[data-theme-version="dark"] .contact-sidebar {
    background: #1e1e28 !important;
    border-left-color: #3f3f4a !important;
}
[data-theme-version="dark"] .chat-left-body {
    background: #1e1e28 !important;
    border-right-color: #3f3f4a !important;
}
.chat-bx {
    cursor: pointer;
    padding: 0.75rem 1rem;
    transition: all 0.15s ease;
    position: relative;
}
.chat-bx:hover {
    background-color: rgba(111, 66, 193, 0.04);
}
.chat-bx.active {
    background-color: rgba(111, 66, 193, 0.08);
}
.chat-bx.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background-color: var(--primary);
}
.chat-bx.unread .chat-name {
    font-weight: 600;
}
.chat-img {
    width: 40px;
    height: 40px;
    min-width: 40px;
    max-width: 40px;
    min-height: 40px;
    max-height: 40px;
    border-radius: 50%;
    background-color: var(--primary) !important;
    color: white !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    flex-shrink: 0;
}
.chat-img-sm {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    max-width: 32px !important;
    min-height: 32px !important;
    max-height: 32px !important;
    font-size: 11px !important;
}
.message-received {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    border-bottom-left-radius: 0.25rem;
    max-width: 75%;
    word-wrap: break-word;
    overflow-wrap: break-word;
}
.message-sent {
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    border-bottom-right-radius: 0.25rem;
    max-width: 75%;
    word-wrap: break-word;
    overflow-wrap: break-word;
}
/* Responsive message bubbles */
@media (max-width: 1440px) {
    .message-received, .message-sent {
        max-width: 80%;
    }
}
@media (max-width: 1366px) {
    .message-received, .message-sent {
        max-width: 85%;
        padding: 0.6rem 0.8rem;
    }
    .message-received p, .message-sent p {
        font-size: 0.875rem;
    }
}
.message-sent small {
    color: rgba(0,0,0,0.6);
}
/* SMS sent bubble - soft green with black text */
.message-sent.sms-bubble,
.message-sent.sms-bubble p {
    background: rgba(52, 199, 89, 0.2) !important;
    color: #1a1a1a !important;
}
/* RCS sent bubble - soft purple with black text */
.message-sent.rcs-bubble,
.message-sent.rcs-bubble p {
    background: rgba(136, 108, 192, 0.2) !important;
    color: #1a1a1a !important;
}
/* ===== NEW SOFT PASTEL PILL DESIGN (Fillow Colors) ===== */
/* Base pill styling */
.qs-pill {
    display: inline-block;
    padding: 4px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 9999px;
    border: none;
    background: none;
}

/* Unread count badge - soft red/danger (Fillow danger-light derived) */
.qs-pill-unread {
    background-color: rgba(253, 96, 124, 0.15);
    color: #c53030;
}

/* Tag badges - soft neutral/grey */
.qs-pill-tag {
    background-color: rgba(108, 117, 125, 0.12);
    color: #495057;
}

/* List badges - soft pink (Fillow secondary #FFA7D7) */
.qs-pill-list {
    background-color: rgba(255, 167, 215, 0.2);
    color: #c2185b;
}

/* SMS channel badge - soft green */
.qs-pill-sms,
.badge.channel-pill-sms,
span.badge.channel-pill-sms,
.badge.rounded-pill.channel-pill-sms {
    background-color: rgba(52, 199, 89, 0.15) !important;
    color: #1b7340 !important;
    background: rgba(52, 199, 89, 0.15) !important;
}

/* RCS channel badge - soft purple (Fillow primary #886CC0) */
.qs-pill-rcs,
.badge.channel-pill-rcs,
span.badge.channel-pill-rcs,
.badge.rounded-pill.channel-pill-rcs {
    background-color: rgba(136, 108, 192, 0.15) !important;
    color: var(--primary-dark, #402c67) !important;
    background: rgba(136, 108, 192, 0.15) !important;
}

/* Waiting for reply - Fillow warning-bg-subtle yellow */
.qs-pill-waiting, .waiting-badge {
    background-color: #fff2cc;
    color: #664c00;
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 9999px;
    font-weight: 600;
}
.emoji-btn {
    font-size: 18px !important;
    line-height: 1 !important;
    padding: 0.25rem 0.4rem !important;
}
.contact-sidebar {
    border-left: 1px solid #e9ecef;
    overflow: hidden;
    flex-shrink: 0;
    width: 280px;
    min-width: 240px;
    height: 100%;
    overflow-y: auto;
}
@media (max-width: 1440px) {
    .contact-sidebar {
        width: 260px;
        min-width: 220px;
    }
}
@media (max-width: 1366px) {
    .contact-sidebar {
        width: 240px;
        min-width: 200px;
    }
}
.sort-dropdown {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background: white url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 0.5rem center/10px 12px;
    padding-right: 1.5rem !important;
}
.date-separator {
    text-align: center;
    margin: 1rem 0;
    position: relative;
}
.date-separator span {
    background: white;
    padding: 0 0.75rem;
    color: #6c757d;
    font-size: 11px;
    font-weight: 500;
    position: relative;
    z-index: 1;
}
.date-separator::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: #e9ecef;
}
.activity ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 0.5rem;
}
.activity ul li a {
    color: var(--text-gray);
    font-size: 16px;
    padding: 0.5rem;
}
.activity ul li a:hover {
    color: var(--primary);
}
.search-highlight {
    background-color: rgba(255, 193, 7, 0.4);
    padding: 0 2px;
    border-radius: 2px;
}
.waiting-badge {
    font-size: 9px;
    padding: 2px 5px;
    background-color: #fff3cd;
    color: #856404;
    border-radius: 3px;
}
.rcs-rich-card-inbox {
    max-width: 280px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    background: white;
    border: 1px solid #e0e0e0;
}
.rcs-rich-card-inbox img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}
.rcs-rich-card-inbox .rcs-card-body {
    padding: 12px;
}
.rcs-rich-card-inbox .rcs-card-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
}
.rcs-rich-card-inbox .rcs-card-desc {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
}
.rcs-rich-card-inbox .rcs-card-btn {
    display: block;
    text-align: center;
    padding: 6px;
    color: var(--primary);
    font-size: 12px;
    font-weight: 500;
    border-top: 1px solid #eee;
    text-decoration: none;
}

/* ===== COMPACT DENSITY MODE - Default (75% proportions) ===== */

/* Left pane - compact width */
.qsms-density-compact .chat-left-body {
    width: 280px !important;
    min-width: 260px !important;
    max-width: 300px !important;
}

/* Left pane header - compact */
.qsms-density-compact .meassge-left-side .p-3 {
    padding: 0.5rem 0.75rem !important;
}
.qsms-density-compact .meassge-left-side h4.mb-0 {
    font-size: 0.9rem !important;
}
.qsms-density-compact .qs-pill {
    font-size: 0.6rem !important;
    padding: 0.15rem 0.4rem !important;
}

/* Search input - compact */
.qsms-density-compact .meassge-left-side .input-group-sm .form-control {
    font-size: 0.75rem !important;
    padding: 0.25rem 0.5rem !important;
}
.qsms-density-compact .meassge-left-side .input-group-text {
    padding: 0.25rem 0.5rem !important;
}

/* Filter dropdowns - compact */
.qsms-density-compact .meassge-left-side .form-select-sm {
    font-size: 0.7rem !important;
    padding: 0.2rem 1.25rem 0.2rem 0.4rem !important;
}

/* Conversation list items - compact */
.qsms-density-compact .chat-bx {
    padding: 0.4rem 0.6rem !important;
}
.qsms-density-compact .chat-bx .chat-name {
    font-size: 0.75rem !important;
    max-width: 110px !important;
}
.qsms-density-compact .chat-bx small {
    font-size: 0.625rem !important;
}
.qsms-density-compact .chat-bx p {
    font-size: 0.7rem !important;
    margin-bottom: 0 !important;
}
.qsms-density-compact .chat-img {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    min-height: 32px !important;
    font-size: 0.7rem !important;
}

/* Chat header - compact */
.qsms-density-compact #chatHeader {
    padding: 0.5rem 0.75rem !important;
}
.qsms-density-compact #chatHeader h5 {
    font-size: 0.875rem !important;
}
.qsms-density-compact #chatHeader small {
    font-size: 0.7rem !important;
}
.qsms-density-compact #chatHeader .chat-img {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    min-height: 36px !important;
    font-size: 0.75rem !important;
}
.qsms-density-compact #chatHeader .btn-sm {
    font-size: 0.7rem !important;
    padding: 0.25rem 0.5rem !important;
}
.qsms-density-compact #chatHeader .badge {
    font-size: 0.6rem !important;
    padding: 0.2rem 0.4rem !important;
}

/* Message bubbles - compact text */
.qsms-density-compact .message-received p,
.qsms-density-compact .message-sent p {
    font-size: 0.8rem !important;
    margin-bottom: 0.25rem !important;
}
.qsms-density-compact .message-received,
.qsms-density-compact .message-sent {
    padding: 0.5rem 0.75rem !important;
}
.qsms-density-compact .qs-chat-messages small {
    font-size: 0.625rem !important;
}
.qsms-density-compact .date-separator span {
    font-size: 0.625rem !important;
}

/* Composer - compact */
.qsms-density-compact #replyComposerCard .card-body {
    padding: 0.5rem 0.75rem !important;
}
.qsms-density-compact #replyComposerCard .form-label {
    font-size: 0.7rem !important;
    margin-bottom: 0.2rem !important;
}
.qsms-density-compact #replyComposerCard .form-control,
.qsms-density-compact #replyComposerCard .form-select {
    font-size: 0.75rem !important;
    padding: 0.25rem 0.5rem !important;
}
.qsms-density-compact #replyComposerCard textarea.form-control {
    padding: 0.375rem 0.5rem !important;
    padding-bottom: 30px !important;
}
.qsms-density-compact #replyComposerCard .btn {
    font-size: 0.7rem !important;
    padding: 0.25rem 0.5rem !important;
}
.qsms-density-compact #replyComposerCard .btn-group .btn {
    font-size: 0.65rem !important;
    padding: 0.2rem 0.4rem !important;
}
.qsms-density-compact #replyComposerCard .mb-2 {
    margin-bottom: 0.25rem !important;
}
.qsms-density-compact #replyComposerCard .mb-1 {
    margin-bottom: 0.15rem !important;
}
.qsms-density-compact #replyComposerCard .row.mb-2 {
    margin-bottom: 0.35rem !important;
}
.qsms-density-compact #replyComposerCard .text-muted.small {
    font-size: 0.65rem !important;
}
.qsms-density-compact #replyComposerCard .col-md-6,
.qsms-density-compact #replyComposerCard .col-lg-5,
.qsms-density-compact #replyComposerCard .col-lg-7 {
    padding-left: 0.375rem !important;
    padding-right: 0.375rem !important;
}

/* Contact sidebar - compact */
.qsms-density-compact .contact-sidebar {
    width: 220px !important;
    min-width: 200px !important;
    padding: 0.5rem !important;
}
.qsms-density-compact .contact-sidebar h5 {
    font-size: 0.8rem !important;
}
.qsms-density-compact .contact-sidebar h6 {
    font-size: 0.75rem !important;
}
.qsms-density-compact .contact-sidebar small {
    font-size: 0.65rem !important;
}
.qsms-density-compact .contact-sidebar .btn-sm {
    font-size: 0.7rem !important;
    padding: 0.25rem 0.5rem !important;
}
.qsms-density-compact .contact-sidebar .badge {
    font-size: 0.6rem !important;
    padding: 0.15rem 0.35rem !important;
}

/* Reduce composer max-height for compact mode */
.qsms-density-compact #replyComposerCard {
    max-height: 240px !important;
}
/* Make textarea smaller in compact mode */
.qsms-density-compact #replyComposerCard #replyMessage {
    rows: 2 !important;
    height: 50px !important;
    min-height: 40px !important;
    padding-bottom: 30px !important;
}
/* Tighten character count row */
.qsms-density-compact #replyComposerCard .d-flex.justify-content-between.align-items-center.mb-2 {
    margin-bottom: 0.25rem !important;
}
/* Reduce send button container margin */
.qsms-density-compact #replyComposerCard .d-flex.justify-content-end {
    margin-top: 0.25rem !important;
}
/* Smaller send button */
.qsms-density-compact #replyComposerCard .btn-primary {
    padding: 0.35rem 0.75rem !important;
    font-size: 0.8rem !important;
}

/* ===== Extra compact for smaller laptops (<=1366px) ===== */
@media (max-width: 1366px) {
    .qsms-density-compact .chat-left-body {
        width: 240px !important;
        min-width: 220px !important;
    }
    .qsms-density-compact .contact-sidebar {
        width: 200px !important;
        min-width: 180px !important;
    }
    .qsms-density-compact .chat-bx .chat-name {
        max-width: 90px !important;
    }
    .qsms-density-compact #replyComposerCard {
        max-height: 220px !important;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid qsms-density-compact inbox-page-container">
    <div class="row">
        <div class="col-xl-12">
            <div class="card inbox-main-card mb-0">
                <div class="card-body p-0" style="display: flex; flex-direction: row; height: 100%; min-height: 0; overflow: hidden;">
                    <div class="chat-left-body">
                        <div class="meassge-left-side">
                            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <h4 class="mb-0 me-2">Inbox</h4>
                                    <span class="qs-pill qs-pill-unread" id="unreadBadge">{{ $unread_count }} unread</span>
                                    <span class="qs-pill qs-pill-waiting" id="overdueCountBadge">0 over 48 hours</span>
                                </div>
                            </div>
                            <div class="p-3 border-bottom">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0" id="conversationSearch" placeholder="Search conversations..." onkeyup="filterConversations()" oninput="filterConversations()">
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm" id="filterConversations" style="width: auto; font-size: 12px; padding-right: 28px;" onchange="console.log('Status changed'); filterConversations();">
                                            <option value="all">All</option>
                                            <option value="unread">Unread</option>
                                            <option value="sms">SMS</option>
                                            <option value="rcs">RCS</option>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm" id="filterSource" style="width: auto; font-size: 12px; padding-right: 28px;" onchange="console.log('Source changed'); filterConversations();">
                                            <option value="all">All Sources</option>
                                            <optgroup label="By Type">
                                                <option value="type:vmn">All VMNs</option>
                                                <option value="type:shortcode">All Short Codes</option>
                                                <option value="type:rcs_agent">All RCS Agents</option>
                                            </optgroup>
                                            <optgroup label="Specific Sources">
                                                <option value="source:60777">60777 (Short Code)</option>
                                                <option value="source:+447700900100">+44 7700 900100 (VMN)</option>
                                                <option value="source:QuickSMS Brand">QuickSMS Brand (RCS Agent)</option>
                                                <option value="source:RetailBot">RetailBot (RCS Agent)</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm" id="sortConversations" style="width: auto; font-size: 12px; padding-right: 28px;">
                                            <option value="newest">Newest</option>
                                            <option value="oldest">Oldest</option>
                                            <option value="alphabetical">A-Z</option>
                                            <option value="unread">Unread</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chat-sidebar" id="conversationList">
                                @forelse($conversations as $conversation)
                                <div class="chat-bx d-flex border-bottom {{ $conversation['unread'] ? 'unread' : '' }} {{ $loop->first ? 'active' : '' }}"
                                     data-id="{{ $conversation['id'] }}"
                                     data-phone="{{ $conversation['phone'] }}"
                                     data-channel="{{ $conversation['channel'] }}"
                                     data-source="{{ $conversation['source'] ?? '' }}"
                                     data-source-type="{{ $conversation['source_type'] ?? 'vmn' }}"
                                     data-unread="{{ $conversation['unread'] ? '1' : '0' }}"
                                     data-timestamp="{{ $conversation['timestamp'] ?? 0 }}"
                                     data-contact-id="{{ $conversation['contact_id'] ?? '' }}"
                                     onclick="selectConversation('{{ $conversation['id'] }}')">
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="d-flex align-items-center" style="gap: 6px;">
                                                <span class="chat-name fw-medium text-truncate" style="font-size: 14px; max-width: 120px;">{{ $conversation['name'] }}</span>
                                            </div>
                                            <div class="d-flex align-items-center" style="gap: 6px;">
                                                <small class="text-muted" style="font-size: 11px; white-space: nowrap;">{{ $conversation['last_message_time'] }}</small>
                                                @if($conversation['unread'])
                                                <span class="unread-dot" style="width: 8px; height: 8px; background-color: #886CC0; border-radius: 50%; display: inline-block;"></span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="mb-0 text-muted text-truncate" style="font-size: 13px;">{{ $conversation['last_message'] }}</p>
                                        @if(isset($conversation['awaiting_reply_48h']) && $conversation['awaiting_reply_48h'])
                                        <span class="waiting-badge mt-1 d-inline-block">Waiting for reply</span>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
                                    <p class="mb-0 text-muted">No conversations yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                    <div class="chat-pane-wrapper" id="chatPaneWrapper">
                            <div class="d-flex justify-content-between align-items-center border-bottom px-4 py-3" id="chatHeader" style="flex: 0 0 auto;">
                                <div class="d-flex align-items-center">
                                    <div class="chat-img me-3" id="chatAvatar">
                                        {{ $conversations[0]['initials'] ?? '--' }}
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h5 class="mb-0" id="chatName">{{ $conversations[0]['name'] ?? 'Select a conversation' }}</h5>
                                            <span class="badge rounded-pill channel-pill-{{ $conversations[0]['channel'] ?? 'sms' }}" id="chatChannelBadge">{{ strtoupper($conversations[0]['channel'] ?? 'SMS') }}</span>
                                        </div>
                                        <small class="text-muted" id="chatPhone">{{ $conversations[0]['phone_masked'] ?? '' }}</small>
                                        <div class="mt-1">
                                            <h5 class="mb-0" id="chatSource">To <span id="chatSourceValue">{{ $conversations[0]['source'] ?? '60777' }}</span> <span id="chatSourceType" class="text-muted fw-normal">({{ ucfirst(str_replace('_', ' ', $conversations[0]['source_type'] ?? 'Short Code')) }})</span></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="activity d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm me-2" id="markReadUnreadBtn" onclick="toggleReadStatus()">
                                        <i class="fas fa-check-double me-1"></i><span id="markReadUnreadText">Mark as Read</span>
                                    </button>
                                    <ul class="d-flex mb-0">
                                        <li>
                                            <a href="javascript:void(0);" onclick="toggleChatSearch()" title="Search in conversation">
                                                <i class="fas fa-search"></i>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="dropdown ms-2">
                                        <div class="btn-link" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="12" cy="4" r="2" fill="var(--primary)"/>
                                                <circle cx="12" cy="12" r="2" fill="var(--primary)"/>
                                                <circle cx="12" cy="20" r="2" fill="var(--primary)"/>
                                            </svg>
                                        </div>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="showComingSoon('Archive')"><i class="fas fa-archive me-2"></i>Archive</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="showComingSoon('Delete')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="px-4 py-2 border-bottom d-none" id="chatSearchBar" style="flex: 0 0 auto;">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="chatSearchInput" placeholder="Search messages in this conversation...">
                                    <button class="btn btn-outline-secondary" type="button" onclick="searchPrev()"><i class="fas fa-chevron-up"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="searchNext()"><i class="fas fa-chevron-down"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="closeChatSearch()"><i class="fas fa-times"></i></button>
                                </div>
                                <small class="text-muted" id="chatSearchResults"></small>
                            </div>
                            
                            <div class="qs-chat-messages" id="chatArea" style="flex: 1 1 0; overflow-y: auto;">
                                @if(isset($conversations[0]['messages']))
                                    @php $lastDate = null; @endphp
                                    @foreach($conversations[0]['messages'] as $msg)
                                        @if(isset($msg['date']) && $msg['date'] !== $lastDate)
                                        <div class="date-separator">
                                            <span>{{ $msg['date'] }}</span>
                                        </div>
                                        @php $lastDate = $msg['date']; @endphp
                                        @endif
                                        
                                        @if($msg['direction'] === 'inbound')
                                        <div class="media my-3 justify-content-start align-items-start">
                                            <div class="chat-img chat-img-sm me-3">
                                                {{ $conversations[0]['initials'] ?? '??' }}
                                            </div>
                                            <div>
                                                <div class="message-received">
                                                    <p class="mb-1">{{ $msg['content'] ?? '' }}</p>
                                                </div>
                                                <small class="text-muted">{{ $msg['time'] }}</small>
                                            </div>
                                        </div>
                                        @elseif(isset($msg['type']) && $msg['type'] === 'rich_card' && isset($msg['rich_card']))
                                        <div class="media my-3 justify-content-end align-items-end">
                                            <div class="text-end">
                                                <div class="rcs-rich-card-inbox">
                                                    @if(isset($msg['rich_card']['image']))
                                                    <img src="{{ $msg['rich_card']['image'] }}" alt="" onerror="this.src='https://via.placeholder.com/280x120?text=Newsletter'">
                                                    @endif
                                                    <div class="rcs-card-body">
                                                        <div class="rcs-card-title">{{ $msg['rich_card']['title'] ?? '' }}</div>
                                                        <div class="rcs-card-desc">{{ $msg['rich_card']['description'] ?? '' }}</div>
                                                    </div>
                                                    @if(isset($msg['rich_card']['button']))
                                                    <a href="javascript:void(0);" class="rcs-card-btn"><i class="fas fa-external-link-alt me-1"></i>{{ $msg['rich_card']['button'] }}</a>
                                                    @endif
                                                </div>
                                                @if(isset($msg['caption']))
                                                <p class="text-muted small mt-1 mb-0">{{ $msg['caption'] }}</p>
                                                @endif
                                                <small class="text-muted">{{ $msg['time'] }} <i class="fas fa-check-double text-primary ms-1" style="font-size: 10px;"></i></small>
                                            </div>
                                        </div>
                                        @else
                                        <div class="media my-3 justify-content-end align-items-end">
                                            <div class="text-end">
                                                <div class="message-sent {{ $conversations[0]['channel'] ?? 'sms' }}-bubble">
                                                    <p class="mb-1">{{ $msg['content'] ?? '' }}</p>
                                                </div>
                                                <small class="text-muted">{{ $msg['time'] }} <i class="fas fa-check-double text-primary ms-1" style="font-size: 10px;"></i></small>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            
                            <div class="card border-top" id="replyComposerCard">
                                <div class="card-body p-2">
                                    <div class="row mb-2">
                                        <div class="col-12 mb-1">
                                            <label class="form-label small fw-bold mb-1">Channel & Sender</label>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="replyChannel" id="replySms" value="sms" checked>
                                                <label class="btn btn-outline-primary" for="replySms"><i class="fas fa-sms me-1"></i>SMS only</label>
                                                <input type="radio" class="btn-check" name="replyChannel" id="replyRcsBasic" value="rcs_basic">
                                                <label class="btn btn-outline-primary" for="replyRcsBasic" data-bs-toggle="tooltip" title="Text-only RCS with SMS fallback"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                                                <input type="radio" class="btn-check" name="replyChannel" id="replyRcsRich" value="rcs_rich">
                                                <label class="btn btn-outline-primary" for="replyRcsRich" data-bs-toggle="tooltip" title="Rich cards, images & buttons with SMS fallback"><i class="fas fa-image me-1"></i>Rich RCS</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="senderIdSection">
                                            <label class="form-label small mb-1">SMS Sender ID <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-sm" id="senderSelect">
                                                <option value="">Select sender...</option>
                                                @foreach($sender_ids as $sid)
                                                <option value="{{ $sid['id'] }}">{{ $sid['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 d-none" id="rcsAgentSection">
                                            <label class="form-label small mb-1">RCS Agent <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-sm" id="rcsAgentSelect">
                                                <option value="">Select agent...</option>
                                                @foreach($rcs_agents as $agent)
                                                <option value="{{ $agent['id'] }}">{{ $agent['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 d-none" id="smsFallbackSection">
                                            <label class="form-label small mb-1">SMS Fallback Sender <span class="text-danger">*</span></label>
                                            <select class="form-select form-select-sm" id="smsFallbackSelect">
                                                <option value="">Select fallback...</option>
                                                @foreach($sender_ids as $sid)
                                                <option value="{{ $sid['id'] }}">{{ $sid['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row align-items-center mb-2">
                                        <div class="col-md-6 col-lg-5 mb-2 mb-md-0">
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="form-label mb-0 text-nowrap small">Template</label>
                                                <select class="form-select form-select-sm" id="templateSelector" onchange="applyInboxTemplate()">
                                                    <option value="">-- None --</option>
                                                </select>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshInboxTemplates()" title="Refresh templates">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-7 text-md-end">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAiAssistant()">
                                                <i class="fas fa-magic me-1"></i>Improve with AI
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <label class="form-label small mb-1" id="replyContentLabel">SMS Content</label>
                                    <div class="position-relative border rounded mb-2">
                                        <textarea class="form-control border-0" id="replyMessage" rows="3" placeholder="Type your message here..." oninput="updateCharCount()" style="padding-bottom: 40px; resize: none;"></textarea>
                                        <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openPersonalisationModal()" title="Insert personalisation">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border" id="emojiPickerBtn" onclick="toggleEmojiPicker()" title="Insert emoji">
                                                <i class="fas fa-smile"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="text-muted small me-3">Characters: <strong id="charCount">0</strong></span>
                                            <span class="text-muted small me-3">Encoding: <strong id="encodingType">GSM-7</strong></span>
                                            <span class="text-muted small" id="segmentDisplay">Segments: <strong id="smsPartCount">1</strong></span>
                                        </div>
                                        <span class="badge bg-warning text-dark d-none" id="unicodeWarning" data-bs-toggle="tooltip" title="This character causes the message to be sent using Unicode encoding.">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Unicode
                                        </span>
                                    </div>
                                    
                                    <div class="d-none" id="rcsRichContentSection">
                                        <div class="border rounded p-3 text-center mb-2" style="background: rgba(136, 108, 192, 0.1);">
                                            <i class="fas fa-image fa-2x mb-2" style="color: #886CC0;"></i>
                                            <h6 class="mb-2">Rich RCS Card</h6>
                                            <p class="text-muted small mb-2">Create rich media cards with images, descriptions, and interactive buttons.</p>
                                            <div id="rcsConfiguredSummaryInbox" class="d-none mb-2">
                                                <div class="alert alert-success py-2 px-3 small mb-2">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    <span id="rcsConfiguredTextInbox">RCS content configured</span>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <button type="button" class="btn btn-sm" style="background: #886CC0; color: white;" onclick="openRcsWizard()">
                                                    <i class="fas fa-magic me-1"></i><span id="rcsWizardBtnTextInbox">Create RCS Message</span>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm d-none" id="rcsClearBtnInbox" onclick="clearRcsContent()">
                                                    <i class="fas fa-times me-1"></i>Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-primary" onclick="sendReply()">
                                            <i class="far fa-paper-plane me-1"></i>Send Message
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-sidebar p-3" id="contactSidebar">
                            <div class="mb-3">
                                <h6 class="mb-0">Contact Details</h6>
                            </div>
                            
                            <div id="contactExists" class="{{ ($conversations[0]['contact_id'] ?? null) ? '' : 'd-none' }}">
                                <div class="text-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px; font-size: 20px; font-weight: 600; background-color: rgba(111, 66, 193, 0.15); color: #6f42c1;" id="contactAvatar">
                                        {{ $conversations[0]['initials'] ?? '--' }}
                                    </div>
                                    <h6 class="mb-0" id="contactName">{{ $conversations[0]['name'] ?? '' }}</h6>
                                    <small class="text-muted" id="contactPhone">{{ $conversations[0]['phone_masked'] ?? '' }}</small>
                                </div>
                                
                                <div class="d-grid gap-2 mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openViewContactModal()">
                                        <i class="fas fa-user me-1"></i>View Contact
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold mb-0">Tags</label>
                                        <a href="javascript:void(0);" class="small text-primary" onclick="openManageTagsModal()"><i class="fas fa-plus"></i> Add</a>
                                    </div>
                                    <div id="contactTags">
                                        <span class="qs-pill qs-pill-tag me-1 mb-1">Parents</span>
                                        <span class="qs-pill qs-pill-tag me-1 mb-1">School-Redwood</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold mb-0">Lists</label>
                                        <a href="javascript:void(0);" class="small text-primary" onclick="openManageListsModal()"><i class="fas fa-plus"></i> Add</a>
                                    </div>
                                    <div id="contactLists">
                                        <span class="qs-pill qs-pill-list me-1 mb-1">Greenhill Parents</span>
                                        <span class="qs-pill qs-pill-list me-1 mb-1">Newsletter</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold mb-0">Notes</label>
                                        <a href="javascript:void(0);" class="small text-primary" onclick="toggleAddNote()"><i class="fas fa-plus"></i> Add</a>
                                    </div>
                                    <div id="addNoteSection" class="d-none mb-2">
                                        <textarea class="form-control form-control-sm mb-2" id="newNoteText" rows="2" placeholder="Enter note..."></textarea>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-primary btn-sm flex-grow-1" onclick="saveNote()">
                                                <i class="fas fa-save me-1"></i>Save
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAddNote()">Cancel</button>
                                        </div>
                                    </div>
                                    <div id="contactNotes">
                                        <p class="text-muted small mb-0">No notes added</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="contactNotExists" class="{{ ($conversations[0]['contact_id'] ?? null) ? 'd-none' : '' }}">
                                <div class="text-center py-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; font-size: 20px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <p class="text-muted mb-3">This number is not in your contacts</p>
                                    <button type="button" class="btn btn-primary btn-sm mb-2 w-100" onclick="openAddContactModal()">
                                        <i class="fas fa-user-plus me-1"></i>Add to Contacts
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="openManageListsModal()">
                                        <i class="fas fa-list me-1"></i>Add to List
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="openManageTagsModal()">
                                        <i class="fas fa-tags me-1"></i>Add Tag
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add to Contact Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" class="form-control" id="newContactPhone" readonly>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="newContactFirstName" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="newContactLastName">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveNewContact()">
                    <i class="fas fa-save me-1"></i>Save Contact
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Select Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    @foreach($templates as $tpl)
                    <a href="javascript:void(0);" class="list-group-item list-group-item-action" onclick="insertTemplate('{{ addslashes($tpl['content']) }}')">
                        <strong>{{ $tpl['name'] }}</strong>
                        <p class="mb-0 text-muted small">{{ $tpl['content'] }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="comingSoonModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                <h5>Coming Soon</h5>
                <p class="text-muted mb-0" id="comingSoonMessage">This feature is under development.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="personalisationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-user-tag me-2"></i>Insert Personalisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Click a placeholder to insert it at the cursor position in your message.</p>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Contact Book Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('firstName')">@{{firstName}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('lastName')">@{{lastName}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('fullName')">@{{fullName}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('mobile')">@{{mobile}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('email')">@{{email}}</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Custom Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('appointmentDate')">@{{appointmentDate}}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('appointmentTime')">@{{appointmentTime}}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('clinicName')">@{{clinicName}}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('customField_1')">@{{customField_1}}</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emojiPickerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-smile me-2"></i>Insert Emoji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Emojis switch the message to Unicode encoding, reducing characters per segment.
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control form-control-sm" id="emojiSearch" placeholder="Search emojis..." oninput="filterEmojis(this.value)">
                </div>
                <div class="mb-3" id="recentlyUsedSection">
                    <h6 class="text-muted mb-2">Recently Used</h6>
                    <div class="d-flex flex-wrap gap-1" id="recentlyUsedEmojis">
                        <span class="text-muted small">No recent emojis</span>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Commonly Used</h6>
                    <div class="d-flex flex-wrap gap-1 emoji-category" data-category="common">
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😊')">😊</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('👍')">👍</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('❤️')">❤️</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🎉')">🎉</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('✅')">✅</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('⭐')">⭐</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('📱')">📱</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('📞')">📞</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('📧')">📧</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('📅')">📅</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('⏰')">⏰</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('💊')">💊</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Smileys & People</h6>
                    <div class="d-flex flex-wrap gap-1 emoji-category" data-category="smileys">
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😀')">😀</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😃')">😃</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😄')">😄</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😁')">😁</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😅')">😅</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😂')">😂</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🤣')">🤣</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😇')">😇</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🙂')">🙂</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😉')">😉</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😍')">😍</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🥰')">🥰</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😘')">😘</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😋')">😋</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('😎')">😎</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🤔')">🤔</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Gestures</h6>
                    <div class="d-flex flex-wrap gap-1 emoji-category" data-category="gestures">
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('👌')">👌</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('✌️')">✌️</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('👋')">👋</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('👏')">👏</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🙏')">🙏</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('✨')">✨</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('💯')">💯</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🔥')">🔥</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('👎')">👎</button>
                    </div>
                </div>
                <div class="mb-0">
                    <h6 class="text-muted mb-2">Hearts & Symbols</h6>
                    <div class="d-flex flex-wrap gap-1 emoji-category" data-category="hearts">
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('💙')">💙</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('💚')">💚</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('💜')">💜</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('💛')">💛</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🧡')">🧡</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('🖤')">🖤</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('💔')">💔</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal('💕')">💕</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aiAssistantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i>AI Content Assistant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="mb-3">Current Message</h6>
                    <div class="bg-light p-3 rounded" id="aiCurrentContent">
                        <em class="text-muted">No content to improve</em>
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="mb-3">What would you like to do?</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="aiImprove('tone')"><i class="fas fa-smile me-1"></i>Improve tone</button>
                        <button type="button" class="btn btn-outline-primary" onclick="aiImprove('shorten')"><i class="fas fa-compress-alt me-1"></i>Shorten message</button>
                        <button type="button" class="btn btn-outline-primary" onclick="aiImprove('grammar')"><i class="fas fa-spell-check me-1"></i>Correct spelling & grammar</button>
                        <button type="button" class="btn btn-outline-primary" onclick="aiImprove('clarity')"><i class="fas fa-lightbulb me-1"></i>Rephrase for clarity</button>
                    </div>
                </div>
                <div class="d-none" id="aiResultSection">
                    <h6 class="mb-3">Suggested Version</h6>
                    <div class="bg-success bg-opacity-10 border border-success p-3 rounded mb-3" id="aiSuggestedContent"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="useAiSuggestion()"><i class="fas fa-check me-1"></i>Use this</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="discardAiSuggestion()">Discard</button>
                    </div>
                </div>
                <div class="d-none" id="aiLoadingSection">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary mb-3"></div>
                        <p class="text-muted">Improving your message...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manageTagsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Manage Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Select tags to add or remove from this contact.</p>
                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="tagParents" checked>
                        <label class="form-check-label" for="tagParents">Parents</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="tagSchoolRedwood" checked>
                        <label class="form-check-label" for="tagSchoolRedwood">School-Redwood</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="tagVIP">
                        <label class="form-check-label" for="tagVIP">VIP</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="tagNewsletter">
                        <label class="form-check-label" for="tagNewsletter">Newsletter Subscriber</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveContactTags()">
                    <i class="fas fa-save me-1"></i>Save Tags
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manageListsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-list me-2"></i>Manage Lists</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Select lists to add or remove this contact from.</p>
                <div class="mb-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="listGreenhillParents" checked>
                        <label class="form-check-label" for="listGreenhillParents">Greenhill Parents</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="listNewsletter" checked>
                        <label class="form-check-label" for="listNewsletter">Newsletter</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="listPromo">
                        <label class="form-check-label" for="listPromo">Promotional Offers</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="listEvents">
                        <label class="form-check-label" for="listEvents">Event Notifications</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveContactLists()">
                    <i class="fas fa-save me-1"></i>Save Lists
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewContactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>Contact Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center border-end">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 28px; font-weight: 600; background-color: rgba(111, 66, 193, 0.15); color: #6f42c1;" id="viewContactAvatar">SM</div>
                        <h5 class="mb-1" id="viewContactName">Sarah Mitchell</h5>
                        <p class="text-muted mb-3" id="viewContactPhone">+44 77** ***111</p>
                        <div class="mb-3">
                            <span class="badge rounded-pill channel-pill-sms" id="viewContactChannel">SMS</span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">First Name</label>
                                <p class="mb-0 fw-medium" id="viewContactFirstName">Sarah</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Last Name</label>
                                <p class="mb-0 fw-medium" id="viewContactLastName">Mitchell</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Email</label>
                                <p class="mb-0 fw-medium" id="viewContactEmail">sarah.m@email.com</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Company</label>
                                <p class="mb-0 fw-medium" id="viewContactCompany">Greenhill School</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Tags</label>
                            <div id="viewContactTags">
                                <span class="qs-pill qs-pill-tag me-1">Parents</span>
                                <span class="qs-pill qs-pill-tag me-1">School-Redwood</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Lists</label>
                            <div id="viewContactLists">
                                <span class="qs-pill qs-pill-list me-1">Greenhill Parents</span>
                                <span class="qs-pill qs-pill-list me-1">Newsletter</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Created</label>
                            <p class="mb-0 small" id="viewContactCreated">15 Dec 2024 at 10:30 AM</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="/contacts/all" class="btn btn-primary">
                    <i class="fas fa-external-link-alt me-1"></i>Open in Contact Book
                </a>
            </div>
        </div>
    </div>
</div>

@include('quicksms.partials.rcs-wizard-modal')
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('css/quicksms-inbox.css') }}">
<script src="{{ asset('js/rcs-preview-renderer.js') }}?v=20260106a"></script>
<script src="{{ asset('js/rcs-wizard.js') }}?v=20260106a"></script>
<script>
// ========================================
// INBOX FILTER SYSTEM - Complete Implementation
// ========================================

// Calculate if a conversation is awaiting reply for 48+ hours
function isAwaitingReply48h(conv) {
    // Check if last message was inbound (received) and older than 48 hours
    var messages = conv.messages || [];
    if (messages.length === 0) return false;
    
    var lastMsg = messages[messages.length - 1];
    var isInbound = lastMsg.direction === 'inbound' || lastMsg.type === 'received';
    
    if (!isInbound) return false;
    
    // Calculate time difference (48 hours = 172800000 ms)
    var now = Date.now();
    var msgTimestamp = conv.timestamp ? conv.timestamp * 1000 : now;
    var hoursDiff = (now - msgTimestamp) / (1000 * 60 * 60);
    
    return hoursDiff >= 48;
}

// Conversation data from server (normalized structure)
var conversationsData = @json($conversations).map(function(conv) {
    // Normalize data structure to match filter requirements
    var normalized = {
        id: conv.id,
        contactName: conv.name,
        phoneNumber: conv.phone,
        phoneMasked: conv.phone_masked,
        initials: conv.initials,
        contactId: conv.contact_id,
        type: conv.channel.toUpperCase(), // 'SMS' or 'RCS'
        source: conv.source,
        sourceId: conv.source_type, // 'vmn', 'shortcode', 'rcs_agent'
        senderId: conv.sender_id,
        rcsAgentId: conv.rcs_agent_id,
        unread: conv.unread === true,
        unreadCount: conv.unread_count || 0,
        lastMessageText: conv.last_message,
        lastMessageTime: conv.last_message_time,
        lastMessageDate: conv.timestamp, // Unix timestamp for sorting
        firstContact: conv.first_contact,
        messages: conv.messages || [],
        // Use server-calculated 48-hour waiting flag
        awaitingReply48h: conv.awaiting_reply_48h === true,
        // Keep original for selectConversation compatibility
        _original: conv
    };
    
    return normalized;
});

// Count overdue conversations and update badge
function updateOverdueBadge() {
    var overdueCount = conversationsData.filter(function(c) { return c.awaitingReply48h; }).length;
    var badge = document.getElementById('overdueCountBadge');
    if (badge) {
        badge.textContent = overdueCount + ' over 48 hours';
        badge.style.display = overdueCount > 0 ? 'inline-block' : 'none';
    }
}

// Global filter state
var inboxFilters = {
    status: 'all',      // all, unread, sms, rcs
    source: 'all',      // all, type:vmn, type:shortcode, type:rcs_agent, source:xxx
    search: '',         // search term
    sort: 'newest'      // newest, oldest, alphabetical, unread
};

// Filtered results
var filteredConversations = [...conversationsData];

var viewContactModal = null;
var currentContactNotes = [];
var currentConversationId = '{{ $conversations[0]['id'] ?? '' }}';
var addContactModal = null;
var templateModal = null;
var comingSoonModal = null;
var manageTagsModal = null;
var manageListsModal = null;
var chatSearchMatches = [];
var chatSearchIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    console.log('[Inbox] DOMContentLoaded fired');
    console.log('[Inbox] Loaded', conversationsData.length, 'conversations');
    
    // Layout is handled by CSS with absolute positioned composer
    // Chat area uses flex: 1 1 0 with padding-bottom for composer overlay space
    console.log('[Layout] Using CSS flex layout with absolute positioned composer');
    
    // Initialize modals with error handling
    try {
        var addContactEl = document.getElementById('addContactModal');
        var templateEl = document.getElementById('templateModal');
        var comingSoonEl = document.getElementById('comingSoonModal');
        var manageTagsEl = document.getElementById('manageTagsModal');
        var manageListsEl = document.getElementById('manageListsModal');
        var viewContactEl = document.getElementById('viewContactModal');
        
        if (addContactEl) addContactModal = new bootstrap.Modal(addContactEl);
        if (templateEl) templateModal = new bootstrap.Modal(templateEl);
        if (comingSoonEl) comingSoonModal = new bootstrap.Modal(comingSoonEl);
        if (manageTagsEl) manageTagsModal = new bootstrap.Modal(manageTagsEl);
        if (manageListsEl) manageListsModal = new bootstrap.Modal(manageListsEl);
        if (viewContactEl) viewContactModal = new bootstrap.Modal(viewContactEl);
        console.log('[Inbox] Modals initialized');
    } catch (e) {
        console.error('[Inbox] Modal initialization error:', e);
    }
    
    document.querySelectorAll('input[name="replyChannel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            toggleReplyChannel(this.value);
            populateInboxTemplateSelector();
        });
    });
    
    populateInboxTemplateSelector();
    
    var replyMsg = document.getElementById('replyMessage');
    if (replyMsg) {
        replyMsg.addEventListener('input', updateCharCount);
    }
    
    // ========================================
    // FILTER EVENT LISTENERS
    // ========================================
    var searchEl = document.getElementById('conversationSearch');
    var filterEl = document.getElementById('filterConversations');
    var sourceEl = document.getElementById('filterSource');
    var sortEl = document.getElementById('sortConversations');
    
    console.log('[Inbox] Elements found - Search:', !!searchEl, 'Filter:', !!filterEl, 'Source:', !!sourceEl, 'Sort:', !!sortEl);
    
    // Search input (debounced)
    if (searchEl) {
        var searchTimeout = null;
        searchEl.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                inboxFilters.search = e.target.value.trim();
                console.log('[Filter] Search changed to:', inboxFilters.search);
                applyFilters();
            }, 150); // 150ms debounce
        });
    }
    
    // Status filter (All / Unread / SMS / RCS)
    if (filterEl) {
        filterEl.addEventListener('change', function(e) {
            inboxFilters.status = e.target.value;
            console.log('[Filter] Status changed to:', inboxFilters.status);
            applyFilters();
        });
    }
    
    // Source filter (All / type:xxx / source:xxx)
    if (sourceEl) {
        sourceEl.addEventListener('change', function(e) {
            inboxFilters.source = e.target.value;
            console.log('[Filter] Source changed to:', inboxFilters.source);
            applyFilters();
        });
    }
    
    // Sort order
    if (sortEl) {
        sortEl.addEventListener('change', function(e) {
            inboxFilters.sort = e.target.value;
            console.log('[Sort] Sort changed to:', inboxFilters.sort);
            applyFilters();
        });
    }
    
    console.log('[Inbox] All event listeners attached successfully');
    
    // Update overdue badge count
    updateOverdueBadge();
    
    // Initial filter application
    applyFilters();
    
    document.getElementById('chatSearchInput').addEventListener('input', function() {
        searchInConversation(this.value);
    });
    
    document.addEventListener('click', function(e) {
        var picker = document.getElementById('emojiPickerContainer');
        var btn = document.getElementById('emojiPickerBtn');
        if (picker && !picker.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
            picker.classList.add('d-none');
        }
    });
    
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Set initial reply channel and sender based on first conversation
    if (conversationsData.length > 0) {
        setReplyChannel(conversationsData[0].channel);
        setSender(conversationsData[0]);
    }
});

function setReplyChannel(channel) {
    var smsRadio = document.getElementById('replySms');
    var rcsBasicRadio = document.getElementById('replyRcsBasic');
    
    if (channel === 'rcs') {
        rcsBasicRadio.checked = true;
        rcsBasicRadio.dispatchEvent(new Event('change', { bubbles: true }));
    } else {
        smsRadio.checked = true;
        smsRadio.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function setSender(conv) {
    var senderSelect = document.getElementById('senderSelect');
    var rcsAgentSelect = document.getElementById('rcsAgentSelect');
    var smsFallbackSelect = document.getElementById('smsFallbackSelect');
    
    // Always clear SMS fallback - user must choose
    smsFallbackSelect.value = '';
    
    if (conv.channel === 'rcs') {
        // Pre-populate RCS Agent based on conversation source
        var agentOptions = rcsAgentSelect.options;
        var matched = false;
        for (var i = 0; i < agentOptions.length; i++) {
            if (agentOptions[i].text === conv.source || 
                (conv.rcs_agent_id && agentOptions[i].value === conv.rcs_agent_id)) {
                rcsAgentSelect.value = agentOptions[i].value;
                matched = true;
                break;
            }
        }
        if (!matched) {
            rcsAgentSelect.value = '';
        }
        // Clear SMS sender when on RCS
        senderSelect.value = '';
    } else {
        // Pre-populate SMS Sender based on conversation source
        var senderOptions = senderSelect.options;
        var matched = false;
        for (var i = 0; i < senderOptions.length; i++) {
            if (senderOptions[i].text === conv.source || 
                (conv.sender_id && senderOptions[i].value === conv.sender_id)) {
                senderSelect.value = senderOptions[i].value;
                matched = true;
                break;
            }
        }
        if (!matched) {
            senderSelect.value = '';
        }
        // Clear RCS agent when on SMS
        rcsAgentSelect.value = '';
    }
}

function selectConversation(id) {
    currentConversationId = id;
    
    // Find conversation in normalized data
    var convData = conversationsData.find(function(c) { return c.id === id; });
    if (!convData) {
        console.error('[Select] Conversation not found:', id);
        return;
    }
    
    // Use original data for backward compatibility with message rendering
    var conv = convData._original || convData;
    
    // Update active state in list
    document.querySelectorAll('.chat-bx').forEach(function(el) {
        el.classList.remove('active');
    });
    var activeEl = document.querySelector('.chat-bx[data-id="' + id + '"]');
    if (activeEl) {
        activeEl.classList.add('active');
    }
    
    // Update chat header
    document.getElementById('chatAvatar').textContent = convData.initials;
    document.getElementById('chatName').textContent = convData.contactName;
    document.getElementById('chatPhone').textContent = convData.phoneMasked;
    
    // Set channel and sender
    var channel = convData.type.toLowerCase(); // 'sms' or 'rcs'
    setReplyChannel(channel);
    setSender(conv); // Uses original format for sender matching
    
    // Update channel badge
    var channelBadge = document.getElementById('chatChannelBadge');
    channelBadge.textContent = convData.type;
    channelBadge.className = 'badge rounded-pill channel-pill-' + channel;
    
    // Update source info
    var sourceValue = document.getElementById('chatSourceValue');
    sourceValue.textContent = convData.source || (channel === 'rcs' ? 'RCS Agent' : '60777');
    
    var sourceType = document.getElementById('chatSourceType');
    var typeLabel = convData.sourceId === 'vmn' ? 'VMN' : 
                    convData.sourceId === 'shortcode' ? 'Short Code' : 
                    convData.sourceId === 'rcs_agent' ? 'RCS Agent' : 'Short Code';
    sourceType.textContent = '(' + typeLabel + ')';
    
    // Render messages
    var chatArea = document.getElementById('chatArea');
    chatArea.innerHTML = '';
    
    var bubbleClass = channel === 'rcs' ? 'rcs-bubble' : 'sms-bubble';
    
    convData.messages.forEach(function(msg) {
        var html = '';
        if (msg.direction === 'inbound') {
            html = '<div class="media my-3 justify-content-start align-items-start">' +
                '<div class="chat-img chat-img-sm me-3">' + convData.initials + '</div>' +
                '<div><div class="message-received"><p class="mb-1">' + escapeHtml(msg.content || '') + '</p></div>' +
                '<small class="text-muted">' + msg.time + '</small></div></div>';
        } else if (msg.type === 'rich_card' && msg.rich_card) {
            var card = msg.rich_card;
            html = '<div class="media my-3 justify-content-end align-items-end">' +
                '<div class="text-end">' +
                '<div class="rcs-rich-card-inbox">' +
                (card.image ? '<img src="' + card.image + '" alt="" onerror="this.src=\'https://via.placeholder.com/280x120?text=Newsletter\'">' : '') +
                '<div class="rcs-card-body">' +
                '<div class="rcs-card-title">' + escapeHtml(card.title) + '</div>' +
                '<div class="rcs-card-desc">' + escapeHtml(card.description) + '</div>' +
                '</div>' +
                (card.button ? '<a href="javascript:void(0);" class="rcs-card-btn"><i class="fas fa-external-link-alt me-1"></i>' + escapeHtml(card.button) + '</a>' : '') +
                '</div>' +
                (msg.caption ? '<p class="text-muted small mt-1 mb-0">' + escapeHtml(msg.caption) + '</p>' : '') +
                '<small class="text-muted">' + msg.time + ' <i class="fas fa-check-double text-primary ms-1" style="font-size: 10px;"></i></small>' +
                '</div></div>';
        } else {
            html = '<div class="media my-3 justify-content-end align-items-end">' +
                '<div class="text-end"><div class="message-sent ' + bubbleClass + '"><p class="mb-1">' + escapeHtml(msg.content || '') + '</p></div>' +
                '<small class="text-muted">' + msg.time + ' <i class="fas fa-check-double text-primary ms-1" style="font-size: 10px;"></i></small></div></div>';
        }
        chatArea.innerHTML += html;
    });
    
    chatArea.scrollTop = chatArea.scrollHeight;
    
    // Update contact panel
    updateContactPanel(convData);
    
    // Update the mark as read/unread button
    updateMarkReadButton();
    
    console.log('[Select] Selected conversation:', id, convData.contactName);
}

function updateContactPanel(conv) {
    document.getElementById('contactAvatar').textContent = conv.initials;
    document.getElementById('contactName').textContent = conv.contactName;
    document.getElementById('contactPhone').textContent = conv.phoneMasked;
    
    if (conv.contactId) {
        document.getElementById('contactExists').classList.remove('d-none');
        document.getElementById('contactNotExists').classList.add('d-none');
    } else {
        document.getElementById('contactExists').classList.add('d-none');
        document.getElementById('contactNotExists').classList.remove('d-none');
    }
}

function openManageTagsModal() {
    manageTagsModal.show();
}

function openManageListsModal() {
    manageListsModal.show();
}

function saveContactTags() {
    // TODO: POST /api/contacts/{id}/tags
    manageTagsModal.hide();
    alert('Tags updated successfully!');
}

function saveContactLists() {
    // TODO: POST /api/contacts/{id}/lists
    manageListsModal.hide();
    alert('Lists updated successfully!');
}

function openViewContactModal() {
    var conv = conversationsData.find(function(c) { return c.id === currentConversationId; });
    if (!conv) return;
    
    document.getElementById('viewContactAvatar').textContent = conv.initials;
    document.getElementById('viewContactName').textContent = conv.name;
    document.getElementById('viewContactPhone').textContent = conv.phone_masked;
    
    var channelBadge = document.getElementById('viewContactChannel');
    channelBadge.textContent = conv.channel.toUpperCase();
    channelBadge.className = 'badge rounded-pill channel-pill-' + conv.channel;
    
    var nameParts = conv.name.split(' ');
    document.getElementById('viewContactFirstName').textContent = nameParts[0] || '-';
    document.getElementById('viewContactLastName').textContent = nameParts.slice(1).join(' ') || '-';
    
    viewContactModal.show();
}

function toggleAddNote() {
    var section = document.getElementById('addNoteSection');
    section.classList.toggle('d-none');
    if (!section.classList.contains('d-none')) {
        document.getElementById('newNoteText').focus();
    }
}

function saveNote() {
    var noteText = document.getElementById('newNoteText').value.trim();
    if (!noteText) {
        alert('Please enter a note.');
        return;
    }
    
    var now = new Date();
    var dateStr = now.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    var timeStr = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    
    var noteHtml = '<div class="border-bottom pb-2 mb-2">' +
        '<p class="mb-1 small">' + escapeHtml(noteText) + '</p>' +
        '<small class="text-muted">Added by Admin on ' + dateStr + ' at ' + timeStr + '</small>' +
        '</div>';
    
    var notesContainer = document.getElementById('contactNotes');
    if (notesContainer.querySelector('p.text-muted')) {
        notesContainer.innerHTML = noteHtml;
    } else {
        notesContainer.insertAdjacentHTML('afterbegin', noteHtml);
    }
    
    document.getElementById('newNoteText').value = '';
    toggleAddNote();
    
    // TODO: POST /api/contacts/{id}/notes
}

function toggleReplyChannel(channel) {
    var senderIdSection = document.getElementById('senderIdSection');
    var rcsAgentSection = document.getElementById('rcsAgentSection');
    var smsFallbackSection = document.getElementById('smsFallbackSection');
    var rcsRichSection = document.getElementById('rcsRichContentSection');
    var contentLabel = document.getElementById('replyContentLabel');
    var segmentDisplay = document.getElementById('segmentDisplay');
    var composerCard = document.getElementById('replyComposerCard');
    var composerBody = composerCard ? composerCard.querySelector('.card-body') : null;
    
    if (channel === 'sms') {
        senderIdSection.classList.remove('d-none');
        rcsAgentSection.classList.add('d-none');
        smsFallbackSection.classList.add('d-none');
        rcsRichSection.classList.add('d-none');
        contentLabel.textContent = 'SMS Content';
        segmentDisplay.classList.remove('d-none');
        if (composerBody) {
            composerBody.style.overflowY = 'visible';
            composerBody.style.maxHeight = 'none';
        }
    } else if (channel === 'rcs_basic') {
        senderIdSection.classList.add('d-none');
        rcsAgentSection.classList.remove('d-none');
        smsFallbackSection.classList.remove('d-none');
        rcsRichSection.classList.add('d-none');
        contentLabel.textContent = 'RCS Content';
        segmentDisplay.classList.add('d-none');
        if (composerBody) {
            composerBody.style.overflowY = 'visible';
            composerBody.style.maxHeight = 'none';
        }
    } else if (channel === 'rcs_rich') {
        senderIdSection.classList.add('d-none');
        rcsAgentSection.classList.remove('d-none');
        smsFallbackSection.classList.remove('d-none');
        rcsRichSection.classList.remove('d-none');
        contentLabel.textContent = 'RCS Content (Optional Fallback Text)';
        segmentDisplay.classList.add('d-none');
        if (composerBody) {
            composerBody.style.overflowY = 'auto';
            composerBody.style.maxHeight = '460px';
        }
    }
    updateCharCount();
}

var GSM7_CHARS = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
var GSM7_EXTENDED = '^{}\\[~]|€';

function isGSM7(text) {
    for (var i = 0; i < text.length; i++) {
        var char = text[i];
        if (GSM7_CHARS.indexOf(char) === -1 && GSM7_EXTENDED.indexOf(char) === -1) {
            return false;
        }
    }
    return true;
}

function countGSM7Length(text) {
    var len = 0;
    for (var i = 0; i < text.length; i++) {
        len += GSM7_EXTENDED.indexOf(text[i]) !== -1 ? 2 : 1;
    }
    return len;
}

function updateCharCount() {
    var text = document.getElementById('replyMessage').value;
    var charCount = text.length;
    var isGsm = isGSM7(text);
    var encoding = isGsm ? 'GSM-7' : 'Unicode';
    var segments = 1;
    
    if (isGsm) {
        var gsm7Len = countGSM7Length(text);
        if (gsm7Len <= 160) {
            segments = 1;
        } else {
            segments = Math.ceil(gsm7Len / 153);
        }
    } else {
        if (charCount <= 70) {
            segments = 1;
        } else {
            segments = Math.ceil(charCount / 67);
        }
    }
    
    document.getElementById('charCount').textContent = charCount;
    document.getElementById('encodingType').textContent = encoding;
    document.getElementById('smsPartCount').textContent = segments;
    
    var unicodeWarning = document.getElementById('unicodeWarning');
    if (!isGsm && charCount > 0) {
        unicodeWarning.classList.remove('d-none');
    } else {
        unicodeWarning.classList.add('d-none');
    }
}

function toggleEmojiPicker() {
    var modal = new bootstrap.Modal(document.getElementById('emojiPickerModal'));
    loadRecentlyUsedEmojis();
    modal.show();
}

var recentlyUsedEmojis = JSON.parse(localStorage.getItem('recentEmojis') || '[]');

function loadRecentlyUsedEmojis() {
    var container = document.getElementById('recentlyUsedEmojis');
    if (recentlyUsedEmojis.length === 0) {
        container.innerHTML = '<span class="text-muted small">No recent emojis</span>';
    } else {
        container.innerHTML = recentlyUsedEmojis.map(function(emoji) {
            return '<button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmojiFromModal(\'' + emoji + '\')">' + emoji + '</button>';
        }).join('');
    }
}

function addToRecentEmojis(emoji) {
    recentlyUsedEmojis = recentlyUsedEmojis.filter(function(e) { return e !== emoji; });
    recentlyUsedEmojis.unshift(emoji);
    if (recentlyUsedEmojis.length > 12) recentlyUsedEmojis = recentlyUsedEmojis.slice(0, 12);
    localStorage.setItem('recentEmojis', JSON.stringify(recentlyUsedEmojis));
}

function insertEmojiFromModal(emoji) {
    var textarea = document.getElementById('replyMessage');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    textarea.value = text.substring(0, start) + emoji + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
    textarea.focus();
    updateCharCount();
    addToRecentEmojis(emoji);
    bootstrap.Modal.getInstance(document.getElementById('emojiPickerModal')).hide();
}

function insertEmoji(emoji) {
    insertEmojiFromModal(emoji);
}

function filterEmojis(searchTerm) {
    var emojiNames = {
        '😊': 'smile happy', '👍': 'thumbs up like', '❤️': 'heart love red', '🎉': 'party celebrate',
        '✅': 'check done complete', '⭐': 'star favorite', '📱': 'phone mobile', '📞': 'call phone',
        '📧': 'email mail', '📅': 'calendar date', '⏰': 'clock time alarm', '💊': 'pill medicine',
        '😀': 'grin smile', '😃': 'smiley happy', '😄': 'grin happy', '😁': 'beam smile',
        '😅': 'sweat smile nervous', '😂': 'laugh tears joy', '🤣': 'rofl laugh', '😇': 'angel innocent',
        '🙂': 'smile slight', '😉': 'wink', '😍': 'heart eyes love', '🥰': 'love hearts',
        '😘': 'kiss love', '😋': 'yummy delicious', '😎': 'cool sunglasses', '🤔': 'thinking hmm',
        '👌': 'ok perfect', '✌️': 'peace victory', '👋': 'wave hello hi bye', '👏': 'clap applause',
        '🙏': 'pray please thanks', '✨': 'sparkle magic', '💯': 'hundred perfect', '🔥': 'fire hot',
        '👎': 'thumbs down dislike', '💙': 'blue heart', '💚': 'green heart', '💜': 'purple heart',
        '💛': 'yellow heart', '🧡': 'orange heart', '🖤': 'black heart', '💔': 'broken heart', '💕': 'hearts love'
    };
    searchTerm = searchTerm.toLowerCase();
    document.querySelectorAll('.emoji-category .emoji-btn').forEach(function(btn) {
        var emoji = btn.textContent.trim();
        var names = emojiNames[emoji] || '';
        var match = emoji.includes(searchTerm) || names.includes(searchTerm);
        btn.style.display = searchTerm === '' || match ? '' : 'none';
    });
}

var inboxTemplates = @json($templates ?? []);

function getInboxCompatibleTemplates(currentChannel) {
    var channelMap = {
        'sms': ['sms'],
        'rcs_basic': ['rcs_basic', 'sms'],
        'rcs_rich': ['rcs_rich', 'rcs_basic', 'sms']
    };
    var allowedChannels = channelMap[currentChannel] || ['sms'];
    
    return inboxTemplates.filter(function(t) {
        if (t.trigger === 'API') return false;
        if (t.status === 'Archived') return false;
        var templateChannel = t.channel || 'sms';
        if (templateChannel === 'Basic RCS + SMS') templateChannel = 'rcs_basic';
        if (templateChannel === 'Rich RCS + SMS') templateChannel = 'rcs_rich';
        if (templateChannel === 'SMS') templateChannel = 'sms';
        return allowedChannels.indexOf(templateChannel) !== -1;
    });
}

function populateInboxTemplateSelector() {
    var channel = document.querySelector('input[name="replyChannel"]:checked')?.value || 'sms';
    var selector = document.getElementById('templateSelector');
    if (!selector) return;
    
    var currentValue = selector.value;
    selector.innerHTML = '<option value="">-- None --</option>';
    
    var compatible = getInboxCompatibleTemplates(channel);
    compatible.forEach(function(t) {
        var opt = document.createElement('option');
        opt.value = t.id;
        opt.setAttribute('data-content', (t.content || '').replace(/'/g, "\\'"));
        opt.setAttribute('data-channel', t.channel || 'SMS');
        opt.setAttribute('data-rcs-payload', t.rcs_payload ? JSON.stringify(t.rcs_payload) : '');
        opt.textContent = t.name + ' (v' + (t.version || '1') + ')';
        selector.appendChild(opt);
    });
    
    if (currentValue) selector.value = currentValue;
}

function refreshInboxTemplates() {
    var btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    setTimeout(function() {
        populateInboxTemplateSelector();
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i>';
    }, 300);
}

function applyInboxTemplate() {
    var selector = document.getElementById('templateSelector');
    var selectedOption = selector.options[selector.selectedIndex];
    
    if (!selectedOption.value) {
        document.getElementById('replyMessage').value = '';
        updateCharCount();
        return;
    }
    
    var channel = selectedOption.getAttribute('data-channel') || 'SMS';
    var content = selectedOption.getAttribute('data-content') || '';
    var rcsPayloadStr = selectedOption.getAttribute('data-rcs-payload');
    
    content = content.replace(/\\'/g, "'");
    
    if (channel === 'Rich RCS + SMS' && rcsPayloadStr) {
        try {
            var payload = JSON.parse(rcsPayloadStr);
            document.querySelector('#replyRcsRich').click();
            
            setTimeout(function() {
                if (typeof openRcsWizard === 'function') {
                    openRcsWizard();
                    setTimeout(function() {
                        if (typeof loadRcsPayloadIntoWizard === 'function') {
                            loadRcsPayloadIntoWizard(payload);
                        }
                    }, 300);
                }
            }, 200);
        } catch (e) {
            console.warn('Failed to parse RCS payload:', e);
        }
    } else if (channel === 'Basic RCS + SMS') {
        document.querySelector('#replyRcsBasic').click();
        document.getElementById('replyMessage').value = content;
        updateCharCount();
    } else {
        document.getElementById('replyMessage').value = content;
        updateCharCount();
    }
}

function applyTemplate() {
    applyInboxTemplate();
}

function openAiAssistant() {
    var content = document.getElementById('replyMessage').value.trim();
    var displayEl = document.getElementById('aiCurrentContent');
    if (content) {
        displayEl.innerHTML = '<p class="mb-0">' + escapeHtml(content) + '</p>';
    } else {
        displayEl.innerHTML = '<em class="text-muted">No content to improve</em>';
    }
    document.getElementById('aiResultSection').classList.add('d-none');
    document.getElementById('aiLoadingSection').classList.add('d-none');
    var modal = new bootstrap.Modal(document.getElementById('aiAssistantModal'));
    modal.show();
}

function aiImprove(action) {
    var content = document.getElementById('replyMessage').value.trim();
    if (!content) {
        alert('Please enter some message content first.');
        return;
    }
    
    document.getElementById('aiLoadingSection').classList.remove('d-none');
    document.getElementById('aiResultSection').classList.add('d-none');
    
    setTimeout(function() {
        var improved = content;
        if (action === 'tone') {
            improved = 'Hi there! ' + content + ' Thank you for your patience!';
        } else if (action === 'shorten') {
            improved = content.split('.')[0] + '.';
        } else if (action === 'grammar') {
            improved = content.charAt(0).toUpperCase() + content.slice(1);
            if (!improved.endsWith('.') && !improved.endsWith('!') && !improved.endsWith('?')) {
                improved += '.';
            }
        } else if (action === 'clarity') {
            improved = 'To clarify: ' + content;
        }
        
        document.getElementById('aiSuggestedContent').innerHTML = '<p class="mb-0">' + escapeHtml(improved) + '</p>';
        document.getElementById('aiLoadingSection').classList.add('d-none');
        document.getElementById('aiResultSection').classList.remove('d-none');
        window.aiSuggestedText = improved;
    }, 1000);
}

function useAiSuggestion() {
    if (window.aiSuggestedText) {
        document.getElementById('replyMessage').value = window.aiSuggestedText;
        updateCharCount();
    }
    bootstrap.Modal.getInstance(document.getElementById('aiAssistantModal')).hide();
}

function discardAiSuggestion() {
    document.getElementById('aiResultSection').classList.add('d-none');
    window.aiSuggestedText = null;
}

function openPersonalisationModal() {
    var modal = new bootstrap.Modal(document.getElementById('personalisationModal'));
    modal.show();
}

function insertPlaceholder(field) {
    var textarea = document.getElementById('replyMessage');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    var placeholder = '{' + '{' + field + '}' + '}';
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
    updateCharCount();
    bootstrap.Modal.getInstance(document.getElementById('personalisationModal')).hide();
}

// ========================================
// FILTER SYSTEM - Core Functions
// ========================================

/**
 * Apply all filters and re-render the conversation list
 */
function applyFilters() {
    console.log('[Filter] Applying filters:', JSON.stringify(inboxFilters));
    
    var results = conversationsData.slice(); // Copy array
    
    // 1. Apply status filter
    if (inboxFilters.status !== 'all') {
        results = results.filter(function(conv) {
            return matchesStatus(conv, inboxFilters.status);
        });
    }
    
    // 2. Apply source filter
    if (inboxFilters.source !== 'all') {
        results = results.filter(function(conv) {
            return matchesSource(conv, inboxFilters.source);
        });
    }
    
    // 3. Apply search (matches contact name OR phone number)
    if (inboxFilters.search) {
        results = results.filter(function(conv) {
            return matchesSearch(conv, inboxFilters.search);
        });
    }
    
    // 4. Apply sort
    results = sortConversations(results, inboxFilters.sort);
    
    // Store filtered results
    filteredConversations = results;
    
    console.log('[Filter] Results:', results.length, 'of', conversationsData.length, 'conversations');
    
    // Re-render the list
    renderConversationList();
    
    // Update unread badge in header
    updateUnreadBadge();
}

/**
 * Check if conversation matches status filter
 */
function matchesStatus(conv, status) {
    switch (status) {
        case 'unread':
            return conv.unread === true;
        case 'sms':
            return conv.type === 'SMS';
        case 'rcs':
            return conv.type === 'RCS';
        default:
            return true;
    }
}

/**
 * Check if conversation matches source filter
 * Supports: all, type:vmn, type:shortcode, type:rcs_agent, source:xxx
 */
function matchesSource(conv, source) {
    if (source === 'all') {
        return true;
    } else if (source.startsWith('type:')) {
        var filterType = source.substring(5);
        return conv.sourceId === filterType;
    } else if (source.startsWith('source:')) {
        var filterSource = source.substring(7);
        return conv.source === filterSource;
    }
    // Legacy fallback
    return conv.source === source;
}

/**
 * Check if conversation matches search term
 * Searches both contact name AND phone number
 */
function matchesSearch(conv, searchTerm) {
    var searchLower = searchTerm.toLowerCase();
    // Remove non-digits for phone number matching
    var searchDigits = searchTerm.replace(/\D/g, '');
    
    // Match against contact name (case-insensitive)
    var nameMatch = conv.contactName && conv.contactName.toLowerCase().includes(searchLower);
    
    // Match against phone number (digits only for flexible matching)
    var phoneDigits = conv.phoneNumber ? conv.phoneNumber.replace(/\D/g, '') : '';
    var numberMatch = searchDigits && phoneDigits.includes(searchDigits);
    
    // Also check masked phone for partial matches
    var maskedMatch = conv.phoneMasked && conv.phoneMasked.toLowerCase().includes(searchLower);
    
    return nameMatch || numberMatch || maskedMatch;
}

/**
 * Sort conversations by specified order
 */
function sortConversations(conversations, order) {
    return conversations.slice().sort(function(a, b) {
        switch (order) {
            case 'newest':
                return (b.lastMessageDate || 0) - (a.lastMessageDate || 0);
            case 'oldest':
                return (a.lastMessageDate || 0) - (b.lastMessageDate || 0);
            case 'alphabetical':
                return (a.contactName || '').localeCompare(b.contactName || '');
            case 'unread':
                // Unread first, then by date
                if (a.unread !== b.unread) {
                    return a.unread ? -1 : 1;
                }
                return (b.lastMessageDate || 0) - (a.lastMessageDate || 0);
            default:
                return 0;
        }
    });
}

/**
 * Render the conversation list based on filtered results
 */
function renderConversationList() {
    var container = document.getElementById('conversationList');
    if (!container) {
        console.error('[Render] conversationList container not found');
        return;
    }
    
    // Check if current selection is still in filtered results
    var currentStillVisible = filteredConversations.some(function(c) {
        return c.id === currentConversationId;
    });
    
    // Build HTML for all filtered conversations
    var html = '';
    filteredConversations.forEach(function(conv, index) {
        var isActive = conv.id === currentConversationId;
        // If current selection is not visible, make first item active
        if (!currentStillVisible && index === 0) {
            isActive = true;
            currentConversationId = conv.id;
        }
        html += createConversationHTML(conv, isActive);
    });
    
    // Show empty state if no results
    if (filteredConversations.length === 0) {
        html = '<div class="text-center py-5">' +
            '<i class="fas fa-search fa-3x text-muted mb-3 opacity-50"></i>' +
            '<p class="mb-0 text-muted">No conversations match your filters</p>' +
            '<button class="btn btn-sm btn-outline-primary mt-2" onclick="resetFilters()">Clear Filters</button>' +
            '</div>';
    }
    
    container.innerHTML = html;
    
    // Re-attach click handlers
    container.querySelectorAll('.chat-bx').forEach(function(el) {
        el.addEventListener('click', function() {
            selectConversation(el.dataset.id);
        });
    });
    
    // If selection changed, update chat pane
    if (!currentStillVisible && filteredConversations.length > 0) {
        selectConversation(filteredConversations[0].id);
    }
    
    console.log('[Render] Rendered', filteredConversations.length, 'conversations');
}

/**
 * Create HTML for a single conversation item
 */
function createConversationHTML(conv, isActive) {
    var activeClass = isActive ? 'active' : '';
    var unreadClass = conv.unread ? 'unread' : '';
    var waitingBadge = conv.awaitingReply48h ? '<span class="waiting-badge mt-1 d-inline-block">Waiting for reply</span>' : '';
    var unreadBadge = conv.unread ? '<span class="unread-dot" style="width: 8px; height: 8px; background-color: #886CC0; border-radius: 50%; display: inline-block;"></span>' : '';
    
    return '<div class="chat-bx d-flex border-bottom ' + unreadClass + ' ' + activeClass + '"' +
        ' data-id="' + conv.id + '"' +
        ' data-phone="' + (conv.phoneNumber || '') + '"' +
        ' data-channel="' + conv.type.toLowerCase() + '"' +
        ' data-source="' + (conv.source || '') + '"' +
        ' data-source-type="' + (conv.sourceId || '') + '"' +
        ' data-unread="' + (conv.unread ? '1' : '0') + '"' +
        ' data-timestamp="' + (conv.lastMessageDate || 0) + '"' +
        ' data-contact-id="' + (conv.contactId || '') + '">' +
        '<div class="flex-grow-1 min-width-0">' +
        '<div class="d-flex align-items-center justify-content-between mb-1">' +
        '<div class="d-flex align-items-center" style="gap: 6px;">' +
        '<span class="chat-name fw-medium text-truncate" style="font-size: 14px; max-width: 120px;">' + escapeHtml(conv.contactName) + '</span>' +
        '</div>' +
        '<div class="d-flex align-items-center" style="gap: 6px;">' +
        '<small class="text-muted" style="font-size: 11px; white-space: nowrap;">' + conv.lastMessageTime + '</small>' +
        unreadBadge +
        '</div>' +
        '</div>' +
        '<p class="mb-0 text-muted text-truncate" style="font-size: 13px;">' + escapeHtml(conv.lastMessageText) + '</p>' +
        waitingBadge +
        '</div>' +
        '</div>';
}

/**
 * Update the unread badge in the inbox header
 */
function updateUnreadBadge() {
    var totalUnread = conversationsData.filter(function(c) { return c.unread; }).length;
    var filteredUnread = filteredConversations.filter(function(c) { return c.unread; }).length;
    
    var badge = document.getElementById('unreadBadge');
    if (badge) {
        badge.textContent = totalUnread + ' unread';
        badge.style.display = totalUnread > 0 ? '' : 'none';
    }
    
    // Also update header navbar badge
    var navBadge = document.getElementById('navInboxBadge');
    if (navBadge) {
        navBadge.textContent = totalUnread;
        navBadge.style.display = totalUnread > 0 ? '' : 'none';
    }
}

/**
 * Reset all filters to default
 */
function resetFilters() {
    inboxFilters.status = 'all';
    inboxFilters.source = 'all';
    inboxFilters.search = '';
    inboxFilters.sort = 'newest';
    
    // Reset UI elements
    document.getElementById('conversationSearch').value = '';
    document.getElementById('filterConversations').value = 'all';
    document.getElementById('filterSource').value = 'all';
    document.getElementById('sortConversations').value = 'newest';
    
    applyFilters();
}

// Legacy function name for compatibility with inline onclick handlers
function filterConversations() {
    applyFilters();
}

function toggleChatSearch() {
    var searchBar = document.getElementById('chatSearchBar');
    searchBar.classList.toggle('d-none');
    if (!searchBar.classList.contains('d-none')) {
        document.getElementById('chatSearchInput').focus();
    }
}

function closeChatSearch() {
    document.getElementById('chatSearchBar').classList.add('d-none');
    document.getElementById('chatSearchInput').value = '';
    document.getElementById('chatSearchResults').textContent = '';
    clearSearchHighlights();
}

function searchInConversation(term) {
    clearSearchHighlights();
    chatSearchMatches = [];
    chatSearchIndex = 0;
    
    if (!term || term.length < 2) {
        document.getElementById('chatSearchResults').textContent = '';
        return;
    }
    
    var messages = document.querySelectorAll('#chatArea .message-received p, #chatArea .message-sent p');
    messages.forEach(function(msg, idx) {
        var text = msg.textContent;
        if (text.toLowerCase().includes(term.toLowerCase())) {
            chatSearchMatches.push(msg);
            var regex = new RegExp('(' + escapeRegex(term) + ')', 'gi');
            msg.innerHTML = text.replace(regex, '<span class="search-highlight">$1</span>');
        }
    });
    
    document.getElementById('chatSearchResults').textContent = chatSearchMatches.length + ' matches found';
    
    if (chatSearchMatches.length > 0) {
        chatSearchMatches[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function searchPrev() {
    if (chatSearchMatches.length === 0) return;
    chatSearchIndex = (chatSearchIndex - 1 + chatSearchMatches.length) % chatSearchMatches.length;
    chatSearchMatches[chatSearchIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function searchNext() {
    if (chatSearchMatches.length === 0) return;
    chatSearchIndex = (chatSearchIndex + 1) % chatSearchMatches.length;
    chatSearchMatches[chatSearchIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearSearchHighlights() {
    document.querySelectorAll('.search-highlight').forEach(function(el) {
        el.outerHTML = el.textContent;
    });
}

function sendReply() {
    var channel = document.querySelector('input[name="replyChannel"]:checked').value;
    
    // Handle Rich RCS channel - send using draft payload
    if (channel === 'rcs_rich' || channel === 'rcs') {
        if (hasInboxRcsDraft()) {
            sendRichRcsMessage();
            return;
        }
        // If no draft, check for text message
    }
    
    // Handle SMS/Basic RCS text message
    var message = document.getElementById('replyMessage').value.trim();
    if (!message) return;
    
    var chatArea = document.getElementById('chatArea');
    var now = new Date();
    var time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    // Apply channel-specific styling
    var bubbleClass = channel === 'rcs' ? 'rcs-bubble' : 'sms-bubble';
    var channelBadge = channel === 'rcs' 
        ? '<span class="badge rounded-pill channel-pill-rcs" style="font-size: 9px; margin-left: 4px;">RCS</span>'
        : '<span class="badge rounded-pill channel-pill-sms" style="font-size: 9px; margin-left: 4px;">SMS</span>';
    
    var html = '<div class="media my-3 justify-content-end align-items-end">' +
        '<div class="text-end"><div class="message-sent ' + bubbleClass + '"><p class="mb-1">' + escapeHtml(message) + '</p></div>' +
        '<small class="text-muted">' + time + ' <i class="fas fa-check text-muted ms-1" style="font-size: 10px;"></i>' + channelBadge + '</small></div></div>';
    
    chatArea.innerHTML += html;
    chatArea.scrollTop = chatArea.scrollHeight;
    
    document.getElementById('replyMessage').value = '';
    updateCharCount();
    
    console.log('TODO: POST /api/messages/send', { conversationId: currentConversationId, message: message, channel: channel });
}

function showTemplateModal() {
    templateModal.show();
}

function insertTemplate(content) {
    document.getElementById('replyMessage').value = content;
    updateCharCount();
    templateModal.hide();
}

function openAddContactModal() {
    var conv = conversationsData.find(function(c) { return c.id === currentConversationId; });
    if (conv) {
        document.getElementById('newContactPhone').value = conv.phone_masked;
    }
    addContactModal.show();
}

function saveNewContact() {
    var firstName = document.getElementById('newContactFirstName').value.trim();
    if (!firstName) {
        alert('Please enter a first name');
        return;
    }
    
    console.log('TODO: POST /api/contacts', {
        phone: document.getElementById('newContactPhone').value,
        firstName: firstName,
        lastName: document.getElementById('newContactLastName').value
    });
    
    addContactModal.hide();
    showComingSoon('Contact saved');
}

function markAsRead() {
    var item = document.querySelector('.chat-bx[data-id="' + currentConversationId + '"]');
    if (item) {
        item.classList.remove('unread');
        item.dataset.unread = '0';
        var badge = item.querySelector('.badge.bg-primary');
        if (badge) badge.remove();
        var unreadDot = item.querySelector('.unread-dot');
        if (unreadDot) unreadDot.remove();
        // Update in-memory data
        var conv = conversationsData.find(function(c) { return c.id == currentConversationId; });
        if (conv) conv.unread = false;
        updateUnreadCount();
        updateMarkReadButton();
    }
    console.log('TODO: PATCH /api/conversations/' + currentConversationId + '/read');
}

function markAsUnread() {
    var item = document.querySelector('.chat-bx[data-id="' + currentConversationId + '"]');
    if (item) {
        item.classList.add('unread');
        item.dataset.unread = '1';
        var existingDot = item.querySelector('.unread-dot');
        if (!existingDot) {
            var timeContainer = item.querySelector('.justify-content-between .d-flex.align-items-center:last-child');
            if (timeContainer) {
                var newDot = document.createElement('span');
                newDot.className = 'unread-dot';
                newDot.style.cssText = 'width: 8px; height: 8px; background-color: #886CC0; border-radius: 50%; display: inline-block;';
                timeContainer.appendChild(newDot);
            }
        }
        // Update in-memory data
        var conv = conversationsData.find(function(c) { return c.id == currentConversationId; });
        if (conv) conv.unread = true;
        updateUnreadCount();
        updateMarkReadButton();
    }
    console.log('TODO: PATCH /api/conversations/' + currentConversationId + '/unread');
}

// Toggle read/unread status from sidebar button
function toggleReadStatus() {
    var conv = conversationsData.find(function(c) { return c.id == currentConversationId; });
    if (conv && conv.unread) {
        markAsRead();
    } else {
        markAsUnread();
    }
}

// Update the Mark as Read/Unread button text based on current conversation
function updateMarkReadButton() {
    var conv = conversationsData.find(function(c) { return c.id == currentConversationId; });
    var textSpan = document.getElementById('markReadUnreadText');
    if (textSpan) {
        textSpan.textContent = conv && conv.unread ? 'Mark as Read' : 'Mark as Unread';
    }
}

function updateUnreadCount() {
    var unreadCount = document.querySelectorAll('.chat-bx[data-unread="1"]').length;
    var countBadge = document.getElementById('unreadBadge');
    if (countBadge) {
        countBadge.textContent = unreadCount + ' unread';
    }
    var navBadge = document.getElementById('navInboxBadge');
    if (navBadge) {
        navBadge.textContent = unreadCount;
    }
}

function showComingSoon(feature) {
    document.getElementById('comingSoonMessage').textContent = feature + ' is under development.';
    comingSoonModal.show();
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Rich RCS Inbox Integration - Draft state and send via composer
var inboxRcsDraftPayload = null; // Stores the applied RCS payload until sent

function getInboxRcsDraft() {
    return inboxRcsDraftPayload;
}

function clearInboxRcsDraft() {
    inboxRcsDraftPayload = null;
}

function hasInboxRcsDraft() {
    return inboxRcsDraftPayload !== null;
}

// Apply RCS Content - saves draft only, does NOT send
function applyRcsContentToInbox() {
    if (typeof saveCurrentCardData === 'function') {
        saveCurrentCardData();
    }
    
    var validation = typeof validateRcsContent === 'function' ? validateRcsContent() : { valid: true, errors: [], warnings: [] };
    
    if (typeof hideRcsValidationErrors === 'function') {
        hideRcsValidationErrors();
    }
    
    if (!validation.valid) {
        if (typeof showRcsValidationErrors === 'function') {
            showRcsValidationErrors(validation.errors, validation.warnings);
        }
        return;
    }
    
    var payload = typeof buildRcsPayload === 'function' ? buildRcsPayload() : null;
    if (!payload) {
        console.error('Failed to build RCS payload');
        return;
    }
    
    // Save to draft state (NOT sending)
    inboxRcsDraftPayload = payload;
    
    // Update composer UI to show configured state
    updateRcsComposerConfigured(payload);
    
    // Close wizard
    var wizardModal = bootstrap.Modal.getInstance(document.getElementById('rcsWizardModal'));
    if (wizardModal) wizardModal.hide();
    
    console.log('[Inbox] Rich RCS content applied to draft', payload);
}

// Update composer UI to show RCS content is configured
function updateRcsComposerConfigured(payload) {
    var configuredSummaryInbox = document.getElementById('rcsConfiguredSummaryInbox');
    var clearBtnInbox = document.getElementById('rcsClearBtnInbox');
    var wizardBtnTextInbox = document.getElementById('rcsWizardBtnTextInbox');
    
    if (configuredSummaryInbox) {
        var cardCount = payload.cards ? payload.cards.length : 1;
        var cardText = cardCount > 1 ? cardCount + ' cards' : '1 card';
        configuredSummaryInbox.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>Rich Card configured (' + cardText + ')';
        configuredSummaryInbox.classList.remove('d-none');
    }
    if (clearBtnInbox) clearBtnInbox.classList.remove('d-none');
    if (wizardBtnTextInbox) wizardBtnTextInbox.textContent = 'Edit Rich Card';
}

// Send Rich RCS - called from sendReply when channel is Rich RCS
function sendRichRcsMessage() {
    var payload = inboxRcsDraftPayload;
    if (!payload) {
        console.error('[Inbox] No Rich RCS draft payload to send');
        return false;
    }
    
    // Get conversation context
    var conv = conversationsData.find(function(c) { return c.id === currentConversationId; });
    var recipientPhone = conv ? conv.phone : null;
    var rcsAgent = document.querySelector('#rcsAgentSelect')?.value || null;
    var smsFallbackSender = document.querySelector('#senderSelect')?.value || null;
    
    // TODO: Replace with actual API call to Send RCS endpoint
    console.log('[Inbox RCS] TODO: POST /api/rcs/send', {
        conversation_id: currentConversationId,
        recipient: recipientPhone,
        rcs_agent_id: rcsAgent,
        sms_fallback_sender_id: smsFallbackSender,
        payload: payload
    });
    
    // Append rich message to chat thread
    renderRichRcsInThread(payload);
    
    // Update conversation list snippet
    updateConversationSnippet(payload);
    
    // Clear draft and reset composer
    inboxRcsDraftPayload = null;
    resetRcsComposer();
    
    console.log('[Inbox] Rich RCS message sent successfully', payload);
    return true;
}

function renderRichRcsInThread(payload) {
    var chatArea = document.getElementById('chatArea');
    if (!chatArea) return;
    
    var now = new Date();
    var time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    var html = '';
    
    if (payload.type === 'carousel' && payload.cards.length > 1) {
        // Render carousel using shared renderer if available
        var carouselHtml = '';
        if (typeof RcsPreviewRenderer !== 'undefined' && RcsPreviewRenderer.renderCarousel) {
            carouselHtml = RcsPreviewRenderer.renderCarousel({
                cardWidth: payload.cardWidth || 'medium',
                mediaHeight: payload.mediaHeight || 'medium',
                cards: payload.cards.map(mapPayloadCardToRendererFormat)
            });
        } else {
            // Fallback: render each card inline
            carouselHtml = '<div class="rcs-inbox-carousel" style="display: flex; gap: 8px; overflow-x: auto; padding: 4px; max-width: 320px;">';
            payload.cards.forEach(function(card) {
                carouselHtml += '<div style="flex: 0 0 auto;">' + renderRichCardHtmlInbox(card) + '</div>';
            });
            carouselHtml += '</div>';
        }
        
        html = '<div class="media my-3 justify-content-end align-items-end">' +
            '<div class="text-end" style="max-width: 320px;">' +
            carouselHtml +
            '<small class="text-muted d-block mt-1">' + time + ' <i class="fas fa-check text-muted ms-1" style="font-size: 10px;"></i> <span class="badge rounded-pill channel-pill-rcs" style="font-size: 9px;">RCS</span></small>' +
            '</div></div>';
    } else {
        // Render single card using shared renderer if available
        var card = payload.cards[0];
        var cardHtml = '';
        if (typeof RcsPreviewRenderer !== 'undefined' && RcsPreviewRenderer.renderRichCard) {
            cardHtml = RcsPreviewRenderer.renderRichCard(mapPayloadCardToRendererFormat(card), { isCarousel: false });
        } else {
            cardHtml = renderRichCardHtmlInbox(card);
        }
        
        html = '<div class="media my-3 justify-content-end align-items-end">' +
            '<div class="text-end" style="max-width: 280px;">' +
            '<div class="rcs-rich-card-inbox-wrapper">' + cardHtml + '</div>' +
            '<small class="text-muted d-block mt-1">' + time + ' <i class="fas fa-check text-muted ms-1" style="font-size: 10px;"></i> <span class="badge rounded-pill channel-pill-rcs" style="font-size: 9px;">RCS</span></small>' +
            '</div></div>';
    }
    
    chatArea.innerHTML += html;
    chatArea.scrollTop = chatArea.scrollHeight;
}

// Map payload card format to RcsPreviewRenderer expected format
function mapPayloadCardToRendererFormat(card) {
    var heightMap = {
        'vertical_short': 'short',
        'vertical_medium': 'medium', 
        'vertical_tall': 'tall',
        'short': 'short',
        'medium': 'medium',
        'tall': 'tall'
    };
    
    // In RCS wizard payload:
    // - "description" is the title/heading
    // - "textBody" is the main body content
    var titleText = card.title || card.description || '';
    var bodyText = card.textBody || card.body || card.text || card.content || '';
    
    return {
        title: titleText,
        description: bodyText,
        textBody: bodyText,
        media: card.media ? {
            url: card.media.hostedUrl || card.media.url || '',
            hostedUrl: card.media.hostedUrl || '',
            height: heightMap[card.media.height] || 'medium',
            altText: card.media.altText || ''
        } : null,
        buttons: (card.buttons || []).map(function(btn) {
            return {
                label: btn.label || '',
                type: btn.type || 'url',
                action: btn.action || { type: btn.type || 'url' }
            };
        })
    };
}

// Fallback inbox card renderer (used when RcsPreviewRenderer not available)
function renderRichCardHtmlInbox(card) {
    var heightMap = {
        'vertical_short': '98px',
        'vertical_medium': '147px', 
        'vertical_tall': '180px',
        'short': '98px',
        'medium': '147px',
        'tall': '180px'
    };
    
    var mediaHtml = '';
    if (card.media && (card.media.hostedUrl || card.media.url)) {
        var imgUrl = card.media.hostedUrl || card.media.url;
        var height = card.media.height || 'vertical_medium';
        var heightPx = heightMap[height] || '147px';
        mediaHtml = '<img src="' + escapeHtml(imgUrl) + '" alt="" style="width: 100%; height: ' + heightPx + '; object-fit: cover;" onerror="this.style.display=\'none\'">';
    }
    
    // Title - in RCS wizard payload, "description" is actually the title field
    var titleText = card.title || card.description || '';
    var titleHtml = titleText ? '<div class="rcs-card-title">' + escapeHtml(titleText) + '</div>' : '';
    
    // Body text - in RCS wizard payload, "textBody" is the main content
    var bodyText = card.textBody || card.body || card.text || card.content || '';
    // If description was used as title, don't duplicate it in body
    if (bodyText === '' && card.description && !card.title) {
        bodyText = ''; // description already used as title
    }
    var bodyHtml = bodyText ? '<div class="rcs-card-desc">' + escapeHtml(bodyText) + '</div>' : '';
    
    // Buttons
    var buttonsHtml = '';
    if (card.buttons && card.buttons.length > 0) {
        card.buttons.forEach(function(btn) {
            var icon = btn.type === 'url' ? 'fa-external-link-alt' : 
                       btn.type === 'phone' ? 'fa-phone' : 
                       btn.type === 'calendar' ? 'fa-calendar' : 'fa-reply';
            buttonsHtml += '<a href="javascript:void(0);" class="rcs-card-btn"><i class="fas ' + icon + ' me-1"></i>' + escapeHtml(btn.label) + '</a>';
        });
    }
    
    return '<div class="rcs-rich-card-inbox">' +
        mediaHtml +
        '<div class="rcs-card-body">' + titleHtml + bodyHtml + '</div>' +
        buttonsHtml +
        '</div>';
}

function updateConversationSnippet(payload) {
    var convItem = document.querySelector('.chat-bx[data-id="' + currentConversationId + '"]');
    if (!convItem) return;
    
    // Update snippet text
    var snippetEl = convItem.querySelector('.snippet-text, .text-muted.text-truncate');
    if (snippetEl) {
        var snippetText = payload.type === 'carousel' 
            ? '[RCS Carousel: ' + payload.cards.length + ' cards]'
            : '[RCS Rich Card: ' + (payload.cards[0].title || 'Media message') + ']';
        snippetEl.textContent = snippetText;
    }
    
    // Update time
    var timeEl = convItem.querySelector('.time-text, small.text-muted');
    if (timeEl) {
        var now = new Date();
        timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    // Move to top of list
    var chatList = document.querySelector('.chat-list-area, #conversationList');
    if (chatList && convItem.parentElement === chatList) {
        chatList.insertBefore(convItem, chatList.firstChild);
    }
}

function resetRcsComposer() {
    // Hide configured summary
    var configuredSummaryInbox = document.getElementById('rcsConfiguredSummaryInbox');
    var clearBtnInbox = document.getElementById('rcsClearBtnInbox');
    var wizardBtnTextInbox = document.getElementById('rcsWizardBtnTextInbox');
    
    if (configuredSummaryInbox) configuredSummaryInbox.classList.add('d-none');
    if (clearBtnInbox) clearBtnInbox.classList.add('d-none');
    if (wizardBtnTextInbox) wizardBtnTextInbox.textContent = 'Create Rich Card';
    
    // Reset wizard cards data
    if (typeof rcsCards !== 'undefined') {
        rcsCards = [{ title: '', description: '', media: null, buttons: [] }];
    }
    if (typeof currentRcsCard !== 'undefined') {
        currentRcsCard = 1;
    }
}

function showRcsWizardSendError(message) {
    var errContainer = document.getElementById('rcsValidationErrors');
    if (errContainer) {
        errContainer.innerHTML = '<div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i>' + escapeHtml(message) + '</div>';
        errContainer.classList.remove('d-none');
    }
}

// Hook into the apply button for Inbox mode - saves draft only, does NOT send
document.addEventListener('DOMContentLoaded', function() {
    var applyBtn = document.getElementById('rcsApplyContentBtn');
    if (applyBtn) {
        applyBtn.onclick = function(e) {
            // Check if we're in inbox RCS mode (i.e., channel is RCS and wizard was opened from inbox)
            var channel = document.querySelector('input[name="replyChannel"]:checked');
            if (channel && (channel.value === 'rcs' || channel.value === 'rcs_rich')) {
                e.preventDefault();
                e.stopPropagation();
                // Apply to draft only - send happens via Send Message button
                applyRcsContentToInbox();
                return false;
            }
            // Otherwise use original behavior
            if (typeof handleRcsApplyContent === 'function') {
                handleRcsApplyContent();
            }
        };
    }
});
</script>
@endpush
