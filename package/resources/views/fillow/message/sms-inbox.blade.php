@extends('layouts.default')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/sms-inbox-layout.css') }}">
@endpush

<div class="sms-inbox-container">
    <div class="row g-0 h-100">
        {{-- LEFT PANEL: Conversation List --}}
        <div class="col-auto inbox-sidebar-left">
            {{-- FIXED HEADER --}}
            <div class="inbox-header">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="mb-0">Inbox <span class="badge bg-danger">12 unread</span></h3>
                </div>

                {{-- Search --}}
                <div class="mb-3">
                    <input type="text" id="searchConversations" class="form-control" placeholder="Search conversations...">
                </div>

                {{-- Filters --}}
                <div class="mb-2">
                    <select id="statusFilter" class="form-select form-select-sm mb-2">
                        <option value="all">All</option>
                        <option value="unread">Unread</option>
                        <option value="sms">SMS</option>
                        <option value="rcs">RCS</option>
                    </select>
                </div>

                <div class="mb-2">
                    <select id="sourceFilter" class="form-select form-select-sm mb-2">
                        <option value="all">All Sources</option>
                        <option value="60777">60777 (Short Code)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <select id="sortFilter" class="form-select form-select-sm">
                        <option value="newest">Newest</option>
                        <option value="oldest">Oldest</option>
                    </select>
                </div>
            </div>

            {{-- SCROLLABLE CONVERSATION LIST --}}
            <div class="conversations-scrollable">
                @foreach([
                    ['name' => 'Zoe Adams', 'message' => 'Still waiting for my order!', 'time' => '02:30 PM', 'unread' => 3],
                    ['name' => 'Wendy Xavier', 'message' => 'What time do you close?', 'time' => '01:15 PM', 'unread' => 1],
                    ['name' => 'Isabelle Jones', 'message' => 'Do you have this in blue?', 'time' => '12:10 PM', 'unread' => 1],
                    ['name' => 'Alice Henderson', 'message' => 'Is my package still on the way?', 'time' => '11:45 AM', 'unread' => 3],
                    ['name' => 'Sarah Mitchell', 'message' => 'When will my order arrive?', 'time' => '10:32 AM', 'unread' => 3],
                    ['name' => 'James Wilson', 'message' => 'Can I change my delivery address?', 'time' => '09:15 AM', 'unread' => 1],
                    ['name' => 'Charlotte Davies', 'message' => 'Can you call me back please?', 'time' => '08:22 AM', 'unread' => 1],
                    ['name' => 'Frederick Grant', 'message' => 'Where is my refund?', 'time' => '07:55 AM', 'unread' => 2],
                    ['name' => 'Liam Morgan', 'message' => 'URGENT: Need to speak to someone now', 'time' => '06:30 AM', 'unread' => 1],
                    ['name' => '+44 7700 9028...', 'message' => 'HELP Waiting for reply', 'time' => 'Yesterday', 'unread' => 1],
                    ['name' => 'Rachel Smith', 'message' => 'Why hasn\'t anyone replied?', 'time' => 'Yesterday', 'unread' => 3],
                    ['name' => 'Olivia Parker', 'message' => 'Is the store open on Boxing Day?', 'time' => 'Yesterday', 'unread' => 1],
                ] as $conversation)
                    <div class="conversation-item {{ $loop->first ? 'active' : '' }}" data-name="{{ $conversation['name'] }}">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle">
                                {{ strtoupper(substr($conversation['name'], 0, 2)) }}
                            </div>
                            <div class="conversation-details flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-0">{{ $conversation['name'] }}</h6>
                                    <small class="text-muted">{{ $conversation['time'] }}</small>
                                </div>
                                <p class="mb-0 text-muted small">{{ $conversation['message'] }}</p>
                            </div>
                            @if($conversation['unread'] > 0)
                                <span class="badge bg-danger ms-2">{{ $conversation['unread'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- CENTER PANEL: Message Thread --}}
        <div class="col inbox-main-center">
            {{-- FIXED HEADER --}}
            <div class="conversation-header-fixed">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-2">IJ</div>
                            <div>
                                <h5 class="mb-0">Isabelle Jones</h5>
                                <small class="text-muted">+44 77** ***515</small><br>
                                <small class="text-muted">To 60777 (Short Code)</small>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-light"><i class="fas fa-search"></i></button>
                        <button class="btn btn-sm btn-light"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>
            </div>

            {{-- SCROLLABLE MESSAGES --}}
            <div class="messages-scrollable">
                <div class="date-separator">22 Dec 2024</div>

                <div class="message-bubble received">
                    <p>Do you have this in blue?</p>
                    <small class="text-muted">12:10 PM</small>
                </div>

                <div class="message-bubble sent">
                    <p>Thanks! How long will delivery take?</p>
                    <small>09:45 AM</small>
                </div>

                <div class="message-bubble sent">
                    <p>Usually 2-3 business days. You'll receive tracking soon.</p>
                    <small>05:48 AM</small>
                </div>

                <div class="date-separator">23 Dec 2024</div>

                <div class="message-bubble received">
                    <p>Great, thank you!</p>
                    <small class="text-muted">10:02 AM</small>
                </div>

                <div class="message-bubble received">
                    <p>When will my order arrive?</p>
                    <small class="text-muted">10:32 AM</small>
                </div>
            </div>

            {{-- FIXED MESSAGE INPUT (BOTTOM) --}}
            <div class="message-input-fixed">
                <div class="channel-selector mb-3">
                    <button class="btn btn-sm btn-channel active">
                        <i class="fas fa-comment"></i> SMS only
                    </button>
                    <button class="btn btn-sm btn-channel">
                        <i class="fas fa-comments"></i> Basic RCS
                    </button>
                    <button class="btn btn-sm btn-channel">
                        <i class="fas fa-star"></i> Rich RCS
                    </button>
                </div>

                <div class="mb-2">
                    <label class="form-label small">SMS Sender ID *</label>
                    <select class="form-select form-select-sm">
                        <option>60777</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small">Template</label>
                    <div class="d-flex">
                        <select class="form-select form-select-sm me-2">
                            <option>-- None --</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-magic"></i> Improve with AI
                        </button>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small">SMS Content</label>
                    <textarea class="form-control" rows="3" placeholder="Type your message here..."></textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">Characters: 0 | Encoding: GSM-7 | Segments: 1</small>
                        <div>
                            <button class="btn btn-sm btn-light"><i class="fas fa-user-plus"></i></button>
                            <button class="btn btn-sm btn-light"><i class="fas fa-smile"></i></button>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </div>
        </div>

        {{-- RIGHT PANEL: Contact Details --}}
        <div class="col-auto inbox-sidebar-right">
            {{-- FIXED HEADER --}}
            <div class="contact-header-fixed">
                <h5 class="mb-3">Contact Details</h5>

                <div class="text-center mb-3">
                    <div class="avatar-circle-large mx-auto mb-2">IJ</div>
                    <h6 class="mb-0">Isabelle Jones</h6>
                    <small class="text-muted">+44 77** ***515</small>
                </div>

                <button class="btn btn-outline-primary btn-sm w-100 mb-3">
                    <i class="fas fa-user"></i> View Contact
                </button>
            </div>

            {{-- SCROLLABLE DETAILS --}}
            <div class="contact-details-scrollable">
                <div class="detail-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Tags</h6>
                        <button class="btn btn-sm btn-link text-primary">+ Add</button>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-secondary">Parents</span>
                        <span class="badge bg-info">School-Redwood</span>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Lists</h6>
                        <button class="btn btn-sm btn-link text-primary">+ Add</button>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-success">Greenhill Parents</span>
                        <span class="badge bg-warning text-dark">Newsletter</span>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Notes</h6>
                        <button class="btn btn-sm btn-link text-primary">+ Add</button>
                    </div>
                    <div class="note-item">
                        <p class="mb-1 small">dfhdfhdfhh</p>
                        <small class="text-muted">Added by Admin on 29 Dec 2025 at 16:23</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/sms-inbox-filters.js') }}"></script>
@endpush
