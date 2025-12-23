@extends('layouts.quicksms')

@section('title', 'Inbox')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<style>
.conversation-list {
    height: calc(100vh - 220px);
    overflow-y: auto;
}
.conversation-item {
    cursor: pointer;
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
}
.conversation-item:hover {
    background-color: rgba(111, 66, 193, 0.05);
}
.conversation-item.active {
    background-color: rgba(111, 66, 193, 0.1);
    border-left-color: #6f42c1;
}
.conversation-item.unread {
    background-color: rgba(111, 66, 193, 0.03);
}
.conversation-item.unread .conversation-name {
    font-weight: 600;
}
.chat-messages {
    height: calc(100vh - 400px);
    overflow-y: auto;
    padding: 1rem;
}
.message-bubble {
    max-width: 75%;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    margin-bottom: 0.5rem;
}
.message-bubble.inbound {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    margin-right: auto;
    border-bottom-left-radius: 0.25rem;
}
.message-bubble.outbound {
    background-color: #6f42c1;
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 0.25rem;
}
.message-time {
    font-size: 0.75rem;
    opacity: 0.7;
}
.channel-badge-sms {
    background-color: #6c757d;
    color: white;
}
.channel-badge-rcs {
    background-color: #198754;
    color: white;
}
.contact-panel {
    border-left: 1px solid #e9ecef;
}
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #6c757d;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages') }}">Messages</a></li>
            <li class="breadcrumb-item active">Inbox</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap py-3">
                    <h5 class="card-title mb-0">Inbox</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary" id="unreadBadge">{{ $unread_count }} unread</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-4 col-xl-3 border-end">
                            <div class="p-3 border-bottom">
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="conversationSearch" placeholder="Search conversations...">
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <select class="form-select form-select-sm" id="filterConversations" style="width: auto;">
                                        <option value="all">All</option>
                                        <option value="unread">Unread</option>
                                        <option value="sms">SMS only</option>
                                        <option value="rcs">RCS only</option>
                                        <option value="waiting">Waiting reply</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="sortConversations" style="width: auto;">
                                        <option value="newest">Newest first</option>
                                        <option value="oldest">Oldest first</option>
                                        <option value="alphabetical">A-Z</option>
                                        <option value="unread">Unread first</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="conversation-list" id="conversationList">
                                @forelse($conversations as $conversation)
                                <div class="conversation-item p-3 border-bottom {{ $conversation['unread'] ? 'unread' : '' }} {{ $loop->first ? 'active' : '' }}"
                                     data-id="{{ $conversation['id'] }}"
                                     data-phone="{{ $conversation['phone'] }}"
                                     data-channel="{{ $conversation['channel'] }}"
                                     data-unread="{{ $conversation['unread'] ? '1' : '0' }}"
                                     data-contact-id="{{ $conversation['contact_id'] ?? '' }}"
                                     onclick="selectConversation('{{ $conversation['id'] }}')">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-size: 14px;">
                                                {{ $conversation['initials'] }}
                                            </div>
                                            <div>
                                                <div class="conversation-name">{{ $conversation['name'] }}</div>
                                                <small class="text-muted">{{ $conversation['phone_masked'] }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">{{ $conversation['last_message_time'] }}</small>
                                            @if($conversation['unread'])
                                            <span class="badge bg-primary rounded-pill">{{ $conversation['unread_count'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0 text-muted small text-truncate" style="max-width: 200px;">{{ $conversation['last_message'] }}</p>
                                        <span class="badge channel-badge-{{ $conversation['channel'] }} ms-2">{{ strtoupper($conversation['channel']) }}</span>
                                    </div>
                                </div>
                                @empty
                                <div class="empty-state py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">No conversations yet</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                        
                        <div class="col-lg-5 col-xl-6 d-flex flex-column" id="chatPane">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center" id="chatHeader">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-size: 14px;" id="chatAvatar">
                                        {{ $conversations[0]['initials'] ?? '--' }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0" id="chatName">{{ $conversations[0]['name'] ?? 'Select a conversation' }}</h6>
                                        <small class="text-muted" id="chatPhone">{{ $conversations[0]['phone_masked'] ?? '' }}</small>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge channel-badge-{{ $conversations[0]['channel'] ?? 'sms' }}" id="chatChannel">{{ strtoupper($conversations[0]['channel'] ?? 'SMS') }}</span>
                                </div>
                            </div>
                            
                            <div class="chat-messages flex-grow-1" id="chatMessages">
                                @if(count($conversations) > 0)
                                    @foreach($conversations[0]['messages'] ?? [] as $message)
                                    <div class="d-flex flex-column {{ $message['direction'] === 'inbound' ? 'align-items-start' : 'align-items-end' }}">
                                        <div class="message-bubble {{ $message['direction'] }}">
                                            <div>{{ $message['content'] }}</div>
                                            <div class="message-time mt-1">
                                                {{ $message['time'] }}
                                                @if($message['direction'] === 'outbound')
                                                    <i class="fas fa-check-double ms-1" title="Delivered"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <div class="empty-state h-100">
                                    <i class="fas fa-comments fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">Select a conversation to view messages</p>
                                </div>
                                @endif
                            </div>
                            
                            <div class="p-3 border-top" id="messageInputArea">
                                <div class="mb-2">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="replyChannel" id="replySMS" value="sms" checked>
                                        <label class="btn btn-outline-secondary" for="replySMS"><i class="fas fa-sms me-1"></i>SMS Reply</label>
                                        <input type="radio" class="btn-check" name="replyChannel" id="replyRCS" value="rcs">
                                        <label class="btn btn-outline-success" for="replyRCS"><i class="fas fa-comment-dots me-1"></i>RCS Reply</label>
                                    </div>
                                </div>
                                
                                <div id="smsReplySection">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <select class="form-select form-select-sm" id="replyFromNumber">
                                                <option value="">Reply from...</option>
                                                @foreach($sender_ids as $sender)
                                                <option value="{{ $sender['id'] }}">{{ $sender['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-select form-select-sm" id="smsTemplateSelect">
                                                <option value="">Use template...</option>
                                                @foreach($templates as $template)
                                                <option value="{{ $template['id'] }}" data-content="{{ addslashes($template['content']) }}">{{ $template['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="rcsReplySection" class="d-none">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <select class="form-select form-select-sm" id="rcsAgentSelect">
                                                <option value="">Send as agent...</option>
                                                @foreach($rcs_agents as $agent)
                                                <option value="{{ $agent['id'] }}">{{ $agent['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="btn-group btn-group-sm w-100" role="group">
                                                <input type="radio" class="btn-check" name="rcsType" id="rcsBasic" value="basic" checked>
                                                <label class="btn btn-outline-secondary" for="rcsBasic">Basic RCS</label>
                                                <input type="radio" class="btn-check" name="rcsType" id="rcsRich" value="rich">
                                                <label class="btn btn-outline-success" for="rcsRich">Rich RCS</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="position-relative border rounded mb-2">
                                    <textarea class="form-control border-0" id="replyContent" rows="3" placeholder="Type your reply..." oninput="updateReplyStats()" style="padding-bottom: 40px;"></textarea>
                                    <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
                                        <button type="button" class="btn btn-sm btn-light border" onclick="openPersonalisationModal()" title="Insert personalisation">
                                            <i class="fas fa-user-tag"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border" id="emojiPickerBtn" title="Insert emoji">
                                            <i class="fas fa-smile"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small me-3">Characters: <strong id="replyCharCount">0</strong></span>
                                        <span class="text-muted small me-3">Encoding: <strong id="replyEncoding">GSM-7</strong></span>
                                        <span class="text-muted small" id="replySegmentDisplay">Segments: <strong id="replySegments">1</strong></span>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="sendReplyBtn" onclick="sendReply()" disabled>
                                        <i class="fas fa-paper-plane me-1"></i>Send
                                    </button>
                                </div>
                                
                                <div class="d-none mt-2" id="richRcsToolbar">
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="openRcsWizard()">
                                        <i class="fas fa-magic me-1"></i>Open RCS Wizard
                                    </button>
                                    <small class="text-muted ms-2">Create rich cards, carousels, and action buttons</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-xl-3 contact-panel d-none d-lg-block" id="contactPanel">
                            <div class="p-3">
                                <h6 class="mb-3"><i class="fas fa-user me-2"></i>Contact Info</h6>
                                
                                <div id="contactExists" class="{{ ($conversations[0]['contact_id'] ?? null) ? '' : 'd-none' }}">
                                    <div class="text-center mb-3">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px; font-size: 20px;" id="contactAvatar">
                                            {{ $conversations[0]['initials'] ?? '--' }}
                                        </div>
                                        <h6 class="mb-0" id="contactName">{{ $conversations[0]['name'] ?? '' }}</h6>
                                        <small class="text-muted" id="contactPhone">{{ $conversations[0]['phone_masked'] ?? '' }}</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Tags</label>
                                        <div id="contactTags">
                                            <span class="badge bg-light text-dark border me-1">VIP</span>
                                            <span class="badge bg-light text-dark border me-1">Customer</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Lists</label>
                                        <div id="contactLists">
                                            <span class="badge bg-info text-white me-1">Marketing</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Notes</label>
                                        <textarea class="form-control form-control-sm" id="contactNotes" rows="2" placeholder="Add notes..."></textarea>
                                    </div>
                                    
                                    <a href="#" class="btn btn-outline-primary btn-sm w-100" id="viewContactBtn">
                                        <i class="fas fa-external-link-alt me-1"></i>View Full Profile
                                    </a>
                                </div>
                                
                                <div id="contactNotExists" class="{{ ($conversations[0]['contact_id'] ?? null) ? 'd-none' : '' }}">
                                    <div class="alert alert-light border text-center">
                                        <i class="fas fa-user-plus fa-2x mb-2 text-muted"></i>
                                        <p class="mb-2 small">This number is not in your contacts</p>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddContactModal()">
                                            <i class="fas fa-plus me-1"></i>Add to Contacts
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="openAddToListModal()">
                                            <i class="fas fa-list me-1"></i>Add to List
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="openAddTagModal()">
                                            <i class="fas fa-tag me-1"></i>Add Tag
                                        </button>
                                    </div>
                                </div>
                                
                                <hr class="my-3">
                                
                                <h6 class="mb-3"><i class="fas fa-history me-2"></i>Conversation Stats</h6>
                                <div class="small">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Messages</span>
                                        <strong id="statTotalMessages">{{ count($conversations[0]['messages'] ?? []) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">First Contact</span>
                                        <strong id="statFirstContact">{{ $conversations[0]['first_contact'] ?? '-' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Last Activity</span>
                                        <strong id="statLastActivity">{{ $conversations[0]['last_message_time'] ?? '-' }}</strong>
                                    </div>
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
                <div class="mb-3">
                    <label class="form-label">Tags</label>
                    <input type="text" class="form-control" id="newContactTags" placeholder="Enter tags separated by commas">
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
@endsection

@push('scripts')
<script>
var conversationsData = @json($conversations);
var currentConversationId = '{{ $conversations[0]['id'] ?? '' }}';
var addContactModal = null;
var comingSoonModal = null;

document.addEventListener('DOMContentLoaded', function() {
    addContactModal = new bootstrap.Modal(document.getElementById('addContactModal'));
    comingSoonModal = new bootstrap.Modal(document.getElementById('comingSoonModal'));
    
    document.querySelectorAll('input[name="replyChannel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            toggleReplyChannel(this.value);
        });
    });
    
    document.querySelectorAll('input[name="rcsType"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            toggleRcsType(this.value);
        });
    });
    
    document.getElementById('replyContent').addEventListener('input', function() {
        validateSendButton();
    });
    
    document.getElementById('conversationSearch').addEventListener('input', filterConversations);
    document.getElementById('filterConversations').addEventListener('change', filterConversations);
    document.getElementById('sortConversations').addEventListener('change', sortConversations);
});

function selectConversation(id) {
    var conversation = conversationsData.find(c => c.id === id);
    if (!conversation) return;
    
    currentConversationId = id;
    
    document.querySelectorAll('.conversation-item').forEach(function(item) {
        item.classList.remove('active');
        if (item.dataset.id === id) {
            item.classList.add('active');
            item.classList.remove('unread');
        }
    });
    
    document.getElementById('chatAvatar').textContent = conversation.initials;
    document.getElementById('chatName').textContent = conversation.name;
    document.getElementById('chatPhone').textContent = conversation.phone_masked;
    document.getElementById('chatChannel').textContent = conversation.channel.toUpperCase();
    document.getElementById('chatChannel').className = 'badge channel-badge-' + conversation.channel;
    
    renderMessages(conversation.messages || []);
    
    if (conversation.contact_id) {
        document.getElementById('contactExists').classList.remove('d-none');
        document.getElementById('contactNotExists').classList.add('d-none');
        document.getElementById('contactAvatar').textContent = conversation.initials;
        document.getElementById('contactName').textContent = conversation.name;
        document.getElementById('contactPhone').textContent = conversation.phone_masked;
    } else {
        document.getElementById('contactExists').classList.add('d-none');
        document.getElementById('contactNotExists').classList.remove('d-none');
    }
    
    document.getElementById('statTotalMessages').textContent = (conversation.messages || []).length;
    document.getElementById('statFirstContact').textContent = conversation.first_contact || '-';
    document.getElementById('statLastActivity').textContent = conversation.last_message_time || '-';
    
    if (conversation.channel === 'rcs') {
        document.getElementById('replyRCS').checked = true;
        toggleReplyChannel('rcs');
    } else {
        document.getElementById('replySMS').checked = true;
        toggleReplyChannel('sms');
    }
}

function renderMessages(messages) {
    var container = document.getElementById('chatMessages');
    if (messages.length === 0) {
        container.innerHTML = '<div class="empty-state h-100"><i class="fas fa-comments fa-3x mb-3 opacity-50"></i><p class="mb-0">No messages in this conversation</p></div>';
        return;
    }
    
    container.innerHTML = messages.map(function(msg) {
        var alignClass = msg.direction === 'inbound' ? 'align-items-start' : 'align-items-end';
        var deliveryIcon = msg.direction === 'outbound' ? '<i class="fas fa-check-double ms-1" title="Delivered"></i>' : '';
        
        return '<div class="d-flex flex-column ' + alignClass + '">' +
            '<div class="message-bubble ' + msg.direction + '">' +
                '<div>' + escapeHtml(msg.content) + '</div>' +
                '<div class="message-time mt-1">' + msg.time + deliveryIcon + '</div>' +
            '</div>' +
        '</div>';
    }).join('');
    
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleReplyChannel(channel) {
    if (channel === 'sms') {
        document.getElementById('smsReplySection').classList.remove('d-none');
        document.getElementById('rcsReplySection').classList.add('d-none');
        document.getElementById('richRcsToolbar').classList.add('d-none');
        document.getElementById('replySegmentDisplay').classList.remove('d-none');
    } else {
        document.getElementById('smsReplySection').classList.add('d-none');
        document.getElementById('rcsReplySection').classList.remove('d-none');
        toggleRcsType(document.querySelector('input[name="rcsType"]:checked').value);
    }
    validateSendButton();
}

function toggleRcsType(type) {
    if (type === 'rich') {
        document.getElementById('richRcsToolbar').classList.remove('d-none');
        document.getElementById('replySegmentDisplay').classList.add('d-none');
    } else {
        document.getElementById('richRcsToolbar').classList.add('d-none');
        document.getElementById('replySegmentDisplay').classList.add('d-none');
    }
}

function updateReplyStats() {
    var content = document.getElementById('replyContent').value;
    var charCount = content.length;
    var isUnicode = /[^\x00-\x7F]/.test(content);
    var encoding = isUnicode ? 'Unicode' : 'GSM-7';
    
    var segmentSize = isUnicode ? 70 : 160;
    var multipartSize = isUnicode ? 67 : 153;
    var segments = charCount <= segmentSize ? 1 : Math.ceil(charCount / multipartSize);
    
    document.getElementById('replyCharCount').textContent = charCount;
    document.getElementById('replyEncoding').textContent = encoding;
    document.getElementById('replySegments').textContent = segments;
    
    validateSendButton();
}

function validateSendButton() {
    var content = document.getElementById('replyContent').value.trim();
    var channel = document.querySelector('input[name="replyChannel"]:checked').value;
    var isValid = content.length > 0;
    
    if (channel === 'sms') {
        var senderId = document.getElementById('replyFromNumber').value;
        isValid = isValid && senderId !== '';
    } else {
        var agentId = document.getElementById('rcsAgentSelect').value;
        isValid = isValid && agentId !== '';
    }
    
    document.getElementById('sendReplyBtn').disabled = !isValid;
}

function sendReply() {
    var content = document.getElementById('replyContent').value.trim();
    if (!content) return;
    
    var conversation = conversationsData.find(c => c.id === currentConversationId);
    if (!conversation) return;
    
    var newMessage = {
        direction: 'outbound',
        content: content,
        time: new Date().toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})
    };
    
    conversation.messages = conversation.messages || [];
    conversation.messages.push(newMessage);
    
    renderMessages(conversation.messages);
    document.getElementById('replyContent').value = '';
    updateReplyStats();
    
    showComingSoon('Message sent! (Demo mode - no actual message was sent. TODO: POST /api/messages/send)');
}

function filterConversations() {
    var searchTerm = document.getElementById('conversationSearch').value.toLowerCase();
    var filter = document.getElementById('filterConversations').value;
    
    document.querySelectorAll('.conversation-item').forEach(function(item) {
        var name = item.querySelector('.conversation-name').textContent.toLowerCase();
        var phone = item.dataset.phone || '';
        var channel = item.dataset.channel || '';
        var isUnread = item.dataset.unread === '1';
        
        var matchesSearch = name.includes(searchTerm) || phone.includes(searchTerm);
        var matchesFilter = true;
        
        if (filter === 'unread') matchesFilter = isUnread;
        if (filter === 'sms') matchesFilter = channel === 'sms';
        if (filter === 'rcs') matchesFilter = channel === 'rcs';
        
        item.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
}

function sortConversations() {
    var sortBy = document.getElementById('sortConversations').value;
    var list = document.getElementById('conversationList');
    var items = Array.from(list.querySelectorAll('.conversation-item'));
    
    items.sort(function(a, b) {
        if (sortBy === 'alphabetical') {
            var nameA = a.querySelector('.conversation-name').textContent;
            var nameB = b.querySelector('.conversation-name').textContent;
            return nameA.localeCompare(nameB);
        } else if (sortBy === 'unread') {
            return (b.dataset.unread === '1' ? 1 : 0) - (a.dataset.unread === '1' ? 1 : 0);
        }
        return 0;
    });
    
    items.forEach(function(item) {
        list.appendChild(item);
    });
}

function openAddContactModal() {
    var conversation = conversationsData.find(c => c.id === currentConversationId);
    if (conversation) {
        document.getElementById('newContactPhone').value = conversation.phone;
    }
    addContactModal.show();
}

function saveNewContact() {
    showComingSoon('Contact saved! (Demo mode - TODO: POST /api/contacts)');
    addContactModal.hide();
}

function openAddToListModal() {
    showComingSoon('Add to list feature coming soon. (TODO: Modal with list selection)');
}

function openAddTagModal() {
    showComingSoon('Add tag feature coming soon. (TODO: Modal with tag selection)');
}

function openPersonalisationModal() {
    showComingSoon('Personalisation fields coming soon. (TODO: Integrate with Contact Book fields)');
}

function openRcsWizard() {
    showComingSoon('RCS Wizard will open here. (TODO: Reuse Campaign Send RCS Wizard)');
}

function showComingSoon(message) {
    document.getElementById('comingSoonMessage').textContent = message;
    comingSoonModal.show();
}

document.getElementById('replyFromNumber').addEventListener('change', validateSendButton);
document.getElementById('rcsAgentSelect').addEventListener('change', validateSendButton);
</script>
@endpush
