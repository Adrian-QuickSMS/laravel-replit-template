@extends('layouts.quicksms')

@section('title', 'Inbox')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<style>
.chat-left-body {
    min-height: calc(100vh - 180px);
    max-height: calc(100vh - 180px);
    overflow-y: auto;
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
    border-radius: 50%;
    background-color: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 500;
}
.chat-box-area {
    min-height: calc(100vh - 380px);
    max-height: calc(100vh - 380px);
    overflow-y: auto;
    padding: 1rem;
}
.message-received {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    border-bottom-left-radius: 0.25rem;
    max-width: 75%;
}
.message-sent {
    background: linear-gradient(135deg, var(--primary) 0%, #8a5cd8 100%);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    border-bottom-right-radius: 0.25rem;
    max-width: 75%;
}
.message-sent small {
    color: rgba(255,255,255,0.8);
}
.channel-pill {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: 500;
}
.channel-pill-sms {
    background-color: #6c757d;
    color: white;
}
.channel-pill-rcs {
    background-color: #198754;
    color: white;
}
.contact-sidebar {
    border-left: 1px solid #e9ecef;
    transition: width 0.3s ease, opacity 0.3s ease;
    overflow: hidden;
}
.contact-sidebar.collapsed {
    width: 0 !important;
    min-width: 0 !important;
    padding: 0 !important;
    opacity: 0;
}
.chat-pane-wrapper {
    transition: all 0.3s ease;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-0 h-auto">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-xl-3 col-xxl-3 border-end pe-0 chat-left-body">
                            <div class="meassge-left-side">
                                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <h4 class="mb-0 me-2">Inbox</h4>
                                        <span class="badge bg-primary-light text-primary" id="unreadBadge">{{ $unread_count }} unread</span>
                                    </div>
                                </div>
                                <div class="p-3 border-bottom">
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" class="form-control border-start-0" id="conversationSearch" placeholder="Search conversations...">
                                    </div>
                                    <div class="d-flex gap-2">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-filter text-muted me-1" style="font-size: 11px;"></i>
                                            <select class="form-select form-select-sm border-0 bg-transparent p-0 ps-1" id="filterConversations" style="width: auto; font-size: 13px;">
                                                <option value="all">All</option>
                                                <option value="unread">Unread</option>
                                                <option value="sms">SMS</option>
                                                <option value="rcs">RCS</option>
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center ms-2">
                                            <i class="fas fa-sort text-muted me-1" style="font-size: 11px;"></i>
                                            <select class="form-select form-select-sm border-0 bg-transparent p-0 ps-1" id="sortConversations" style="width: auto; font-size: 13px;">
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
                                     data-unread="{{ $conversation['unread'] ? '1' : '0' }}"
                                     data-contact-id="{{ $conversation['contact_id'] ?? '' }}"
                                     onclick="selectConversation('{{ $conversation['id'] }}')">
                                    <div class="chat-img me-3">
                                        {{ $conversation['initials'] }}
                                    </div>
                                    <div class="w-100">
                                        <div class="d-flex mb-1 align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="mb-0 chat-name">{{ $conversation['name'] }}</h6>
                                                <span class="channel-pill channel-pill-{{ $conversation['channel'] }}">{{ strtoupper($conversation['channel']) }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <small class="text-muted">{{ $conversation['last_message_time'] }}</small>
                                                @if($conversation['unread'])
                                                <span class="badge bg-primary rounded-pill" style="font-size: 10px;">{{ $conversation['unread_count'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="mb-0 text-muted lh-base" style="font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $conversation['last_message'] }}</p>
                                        @if(!$conversation['contact_id'])
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
                        
                        <div class="col chat-pane-wrapper" id="chatPaneWrapper">
                            <div class="d-flex justify-content-between align-items-center border-bottom px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="chat-img me-3" id="chatAvatar">
                                        {{ $conversations[0]['initials'] ?? '--' }}
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h5 class="mb-0" id="chatName">{{ $conversations[0]['name'] ?? 'Select a conversation' }}</h5>
                                            <span class="channel-pill channel-pill-{{ $conversations[0]['channel'] ?? 'sms' }}" id="chatChannelBadge">{{ strtoupper($conversations[0]['channel'] ?? 'SMS') }}</span>
                                        </div>
                                        <small class="text-muted" id="chatPhone">{{ $conversations[0]['phone_masked'] ?? '' }}</small>
                                    </div>
                                </div>
                                <div class="activity d-flex align-items-center">
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
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="toggleContactSidebar()"><i class="fas fa-user me-2"></i>Toggle Contact Details</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="markAsRead()"><i class="fas fa-check-double me-2"></i>Mark as Read</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="showComingSoon('Archive')"><i class="fas fa-archive me-2"></i>Archive</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="showComingSoon('Delete')"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="px-4 py-2 border-bottom d-none" id="chatSearchBar">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="chatSearchInput" placeholder="Search messages in this conversation...">
                                    <button class="btn btn-outline-secondary" type="button" onclick="searchPrev()"><i class="fas fa-chevron-up"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="searchNext()"><i class="fas fa-chevron-down"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="closeChatSearch()"><i class="fas fa-times"></i></button>
                                </div>
                                <small class="text-muted" id="chatSearchResults"></small>
                            </div>
                            
                            <div class="chat-box-area dz-scroll" id="chatArea">
                                @if(isset($conversations[0]['messages']))
                                    @foreach($conversations[0]['messages'] as $msg)
                                        @if($msg['direction'] === 'inbound')
                                        <div class="media my-3 justify-content-start align-items-start">
                                            <div class="chat-img me-3" style="width: 32px; height: 32px; font-size: 11px;">
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
                                                <div class="message-sent">
                                                    <p class="mb-1">{{ $msg['content'] ?? '' }}</p>
                                                </div>
                                                <small class="text-muted">{{ $msg['time'] }} <i class="fas fa-check-double text-primary ms-1" style="font-size: 10px;"></i></small>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            
                            <div class="card-footer border-0 type-massage px-4 py-3">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="replyChannel" id="replySms" value="sms" checked>
                                        <label class="btn btn-outline-secondary" for="replySms"><i class="fas fa-comment me-1"></i>SMS</label>
                                        <input type="radio" class="btn-check" name="replyChannel" id="replyRcs" value="rcs">
                                        <label class="btn btn-outline-secondary" for="replyRcs"><i class="fas fa-mobile-alt me-1"></i>RCS</label>
                                    </div>
                                    <select class="form-select form-select-sm" id="senderSelect" style="width: auto;">
                                        @foreach($sender_ids as $sid)
                                        <option value="{{ $sid['id'] }}">{{ $sid['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="d-none" id="rcsAgentSelect">
                                        <select class="form-select form-select-sm" style="width: auto;">
                                            @foreach($rcs_agents as $agent)
                                            <option value="{{ $agent['id'] }}">{{ $agent['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showTemplateModal()">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                </div>
                                <div class="input-group">
                                    <textarea class="form-control" id="replyMessage" rows="2" placeholder="Type your message..."></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted"><span id="charCount">0</span> characters</small>
                                    <button type="button" class="btn btn-primary" onclick="sendReply()">
                                        <i class="far fa-paper-plane me-1"></i>Send
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-xxl-3 contact-sidebar p-3" id="contactSidebar">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Contact Details</h6>
                                <button type="button" class="btn-close" onclick="toggleContactSidebar()" aria-label="Close"></button>
                            </div>
                            
                            <div id="contactExists" class="{{ ($conversations[0]['contact_id'] ?? null) ? '' : 'd-none' }}">
                                <div class="text-center mb-3">
                                    <div class="chat-img mx-auto mb-2" style="width: 60px; height: 60px; font-size: 20px;" id="contactAvatar">
                                        {{ $conversations[0]['initials'] ?? '--' }}
                                    </div>
                                    <h6 class="mb-0" id="contactName">{{ $conversations[0]['name'] ?? '' }}</h6>
                                    <small class="text-muted" id="contactPhone">{{ $conversations[0]['phone_masked'] ?? '' }}</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Tags</label>
                                    <div id="contactTags">
                                        <span class="badge bg-light text-dark border me-1 mb-1">Parents</span>
                                        <span class="badge bg-light text-dark border me-1 mb-1">School-Redwood</span>
                                    </div>
                                    <a href="javascript:void(0);" class="small" onclick="showComingSoon('Manage Tags')"><i class="fas fa-plus me-1"></i>Manage Tags</a>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Lists</label>
                                    <div id="contactLists">
                                        <span class="badge bg-light text-dark border me-1 mb-1">Greenhill Parents</span>
                                        <span class="badge bg-light text-dark border me-1 mb-1">Newsletter</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Notes</label>
                                    <p class="text-muted small mb-0" id="contactNotes">No notes added</p>
                                </div>
                            </div>
                            
                            <div id="contactNotExists" class="{{ ($conversations[0]['contact_id'] ?? null) ? 'd-none' : '' }}">
                                <div class="text-center py-3">
                                    <div class="chat-img mx-auto mb-3 bg-secondary" style="width: 60px; height: 60px; font-size: 20px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <p class="text-muted mb-3">This number is not in your contacts</p>
                                    <button type="button" class="btn btn-primary btn-sm mb-2 w-100" onclick="openAddContactModal()">
                                        <i class="fas fa-user-plus me-1"></i>Add to Contacts
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="showComingSoon('Add to List')">
                                        <i class="fas fa-list me-1"></i>Add to List
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
@endsection

@push('scripts')
<script>
var conversationsData = @json($conversations);
var currentConversationId = '{{ $conversations[0]['id'] ?? '' }}';
var addContactModal = null;
var templateModal = null;
var comingSoonModal = null;
var sidebarVisible = true;
var chatSearchMatches = [];
var chatSearchIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    addContactModal = new bootstrap.Modal(document.getElementById('addContactModal'));
    templateModal = new bootstrap.Modal(document.getElementById('templateModal'));
    comingSoonModal = new bootstrap.Modal(document.getElementById('comingSoonModal'));
    
    document.querySelectorAll('input[name="replyChannel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            toggleReplyChannel(this.value);
        });
    });
    
    var replyMsg = document.getElementById('replyMessage');
    if (replyMsg) {
        replyMsg.addEventListener('input', updateCharCount);
    }
    
    document.getElementById('conversationSearch').addEventListener('input', filterConversations);
    document.getElementById('filterConversations').addEventListener('change', filterConversations);
    document.getElementById('sortConversations').addEventListener('change', sortConversations);
    
    document.getElementById('chatSearchInput').addEventListener('input', function() {
        searchInConversation(this.value);
    });
});

function selectConversation(id) {
    currentConversationId = id;
    var conv = conversationsData.find(function(c) { return c.id === id; });
    if (!conv) return;
    
    document.querySelectorAll('.chat-bx').forEach(function(el) {
        el.classList.remove('active');
    });
    document.querySelector('.chat-bx[data-id="' + id + '"]').classList.add('active');
    
    document.getElementById('chatAvatar').textContent = conv.initials;
    document.getElementById('chatName').textContent = conv.name;
    document.getElementById('chatPhone').textContent = conv.phone_masked;
    
    var channelBadge = document.getElementById('chatChannelBadge');
    channelBadge.textContent = conv.channel.toUpperCase();
    channelBadge.className = 'channel-pill channel-pill-' + conv.channel;
    
    var chatArea = document.getElementById('chatArea');
    chatArea.innerHTML = '';
    
    conv.messages.forEach(function(msg) {
        var html = '';
        if (msg.direction === 'inbound') {
            html = '<div class="media my-3 justify-content-start align-items-start">' +
                '<div class="chat-img me-3" style="width: 32px; height: 32px; font-size: 11px;">' + conv.initials + '</div>' +
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
                '<div class="text-end"><div class="message-sent"><p class="mb-1">' + escapeHtml(msg.content || '') + '</p></div>' +
                '<small class="text-muted">' + msg.time + ' <i class="fas fa-check-double text-primary ms-1" style="font-size: 10px;"></i></small></div></div>';
        }
        chatArea.innerHTML += html;
    });
    
    chatArea.scrollTop = chatArea.scrollHeight;
    
    updateContactPanel(conv);
}

function updateContactPanel(conv) {
    document.getElementById('contactAvatar').textContent = conv.initials;
    document.getElementById('contactName').textContent = conv.name;
    document.getElementById('contactPhone').textContent = conv.phone_masked;
    
    if (conv.contact_id) {
        document.getElementById('contactExists').classList.remove('d-none');
        document.getElementById('contactNotExists').classList.add('d-none');
    } else {
        document.getElementById('contactExists').classList.add('d-none');
        document.getElementById('contactNotExists').classList.remove('d-none');
    }
}

function toggleContactSidebar() {
    var sidebar = document.getElementById('contactSidebar');
    sidebarVisible = !sidebarVisible;
    
    if (sidebarVisible) {
        sidebar.classList.remove('collapsed');
        sidebar.classList.add('col-xl-3', 'col-xxl-3');
    } else {
        sidebar.classList.add('collapsed');
        sidebar.classList.remove('col-xl-3', 'col-xxl-3');
    }
}

function toggleReplyChannel(channel) {
    var senderSelect = document.getElementById('senderSelect');
    var rcsAgentSelect = document.getElementById('rcsAgentSelect');
    
    if (channel === 'rcs') {
        senderSelect.classList.add('d-none');
        rcsAgentSelect.classList.remove('d-none');
    } else {
        senderSelect.classList.remove('d-none');
        rcsAgentSelect.classList.add('d-none');
    }
}

function updateCharCount() {
    var text = document.getElementById('replyMessage').value;
    document.getElementById('charCount').textContent = text.length;
}

function filterConversations() {
    var searchTerm = document.getElementById('conversationSearch').value.toLowerCase();
    var filterVal = document.getElementById('filterConversations').value;
    
    document.querySelectorAll('.chat-bx').forEach(function(el) {
        var name = el.querySelector('.chat-name').textContent.toLowerCase();
        var channel = el.dataset.channel;
        var unread = el.dataset.unread === '1';
        
        var matchesSearch = name.includes(searchTerm) || searchTerm === '';
        var matchesFilter = filterVal === 'all' ||
            (filterVal === 'unread' && unread) ||
            (filterVal === 'sms' && channel === 'sms') ||
            (filterVal === 'rcs' && channel === 'rcs');
        
        el.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
}

function sortConversations() {
    var sortVal = document.getElementById('sortConversations').value;
    var container = document.getElementById('conversationList');
    var items = Array.from(container.querySelectorAll('.chat-bx'));
    
    items.sort(function(a, b) {
        if (sortVal === 'alphabetical') {
            return a.querySelector('.chat-name').textContent.localeCompare(b.querySelector('.chat-name').textContent);
        } else if (sortVal === 'unread') {
            return (b.dataset.unread === '1' ? 1 : 0) - (a.dataset.unread === '1' ? 1 : 0);
        }
        return 0;
    });
    
    items.forEach(function(item) {
        container.appendChild(item);
    });
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
    var message = document.getElementById('replyMessage').value.trim();
    if (!message) return;
    
    var channel = document.querySelector('input[name="replyChannel"]:checked').value;
    
    var chatArea = document.getElementById('chatArea');
    var now = new Date();
    var time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    var html = '<div class="media my-3 justify-content-end align-items-end">' +
        '<div class="text-end"><div class="message-sent"><p class="mb-1">' + escapeHtml(message) + '</p></div>' +
        '<small class="text-muted">' + time + ' <i class="fas fa-check text-muted ms-1" style="font-size: 10px;"></i></small></div></div>';
    
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
    }
    console.log('TODO: PATCH /api/conversations/' + currentConversationId + '/read');
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
</script>
@endpush
