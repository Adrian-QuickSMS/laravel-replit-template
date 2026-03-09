{{-- Inbox: Centre — chat thread pane --}}
<div class="chat-pane" id="chatPaneWrapper">

    {{-- Empty state (shown when no conversation selected) --}}
    <div class="chat-pane__empty" id="chatEmpty">
        <div class="chat-pane__empty-inner">
            <i class="far fa-comments chat-pane__empty-icon"></i>
            <h5>Select a conversation</h5>
            <p class="text-muted">Choose a conversation from the list to view messages</p>
        </div>
    </div>

    {{-- Chat header --}}
    <div class="chat-pane__header d-none" id="chatHeader">
        <div class="chat-pane__header-left">
            <button class="btn btn-sm d-xl-none me-2" id="backToListBtn" title="Back to list">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="chat-pane__header-avatar" id="chatHeaderAvatar"></div>
            <div class="chat-pane__header-info">
                <span class="chat-pane__header-name" id="chatHeaderName"></span>
                <span class="chat-pane__header-meta" id="chatHeaderMeta"></span>
            </div>
        </div>
        <div class="chat-pane__header-actions">
            <button class="btn btn-sm" id="toggleChatSearch" title="Search messages">
                <i class="fas fa-search"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm" data-bs-toggle="dropdown" title="More options">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="javascript:void(0)" id="toggleReadBtn"><i class="far fa-envelope me-2"></i>Mark as read</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" id="toggleContactPanel"><i class="far fa-address-card me-2"></i>Contact details</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-muted" href="javascript:void(0)" onclick="InboxApp.comingSoon('Block contact')"><i class="fas fa-ban me-2"></i>Block contact</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- In-chat search bar --}}
    <div class="chat-pane__search d-none" id="chatSearchBar">
        <div class="input-group input-group-sm">
            <input type="text" class="form-control" id="chatSearchInput" placeholder="Search in conversation...">
            <button class="btn btn-outline-secondary" id="chatSearchPrev" title="Previous"><i class="fas fa-chevron-up"></i></button>
            <button class="btn btn-outline-secondary" id="chatSearchNext" title="Next"><i class="fas fa-chevron-down"></i></button>
            <button class="btn btn-outline-secondary" id="chatSearchClose" title="Close"><i class="fas fa-times"></i></button>
        </div>
        <small class="text-muted ms-2" id="chatSearchCount"></small>
    </div>

    {{-- Messages area --}}
    <div class="chat-pane__messages d-none" id="chatArea"></div>

</div>
