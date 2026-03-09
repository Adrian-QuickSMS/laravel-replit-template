{{-- Inbox: Left sidebar — conversation list with filters --}}
<div class="inbox-sidebar" id="inboxSidebar">
    {{-- Search + Filter Bar --}}
    <div class="inbox-sidebar__header">
        <div class="inbox-sidebar__search">
            <i class="fas fa-search inbox-sidebar__search-icon"></i>
            <input type="text"
                   id="sidebarSearch"
                   class="form-control form-control-sm inbox-sidebar__search-input"
                   placeholder="Search conversations...">
        </div>
        <div class="inbox-sidebar__filters">
            <select id="filterChannel" class="form-select form-select-sm inbox-sidebar__filter">
                <option value="all">All Channels</option>
                <option value="sms">SMS</option>
                <option value="rcs">RCS</option>
            </select>
            <select id="filterStatus" class="form-select form-select-sm inbox-sidebar__filter">
                <option value="all">All</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
                <option value="awaiting">Awaiting Reply</option>
            </select>
            <select id="filterSource" class="form-select form-select-sm inbox-sidebar__filter">
                <option value="all">All Sources</option>
            </select>
            <select id="sortOrder" class="form-select form-select-sm inbox-sidebar__filter">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="alpha">A → Z</option>
                <option value="unread_first">Unread First</option>
            </select>
        </div>
    </div>

    {{-- Conversation List --}}
    <div class="inbox-sidebar__list" id="conversationList">
        @foreach ($conversations as $conv)
        <div class="conv-item{{ $conv['unread'] ? ' conv-item--unread' : '' }}"
             data-id="{{ $conv['id'] }}"
             data-channel="{{ $conv['channel'] }}"
             data-source="{{ $conv['source'] }}"
             data-source-type="{{ $conv['source_type'] }}"
             data-unread="{{ $conv['unread'] ? '1' : '0' }}"
             data-timestamp="{{ $conv['timestamp'] }}"
             data-name="{{ e($conv['name']) }}"
             data-awaiting="{{ !empty($conv['awaiting_reply_48h']) ? '1' : '0' }}">
            <div class="conv-item__avatar">
                <span class="conv-item__initials">{{ $conv['initials'] }}</span>
            </div>
            <div class="conv-item__body">
                <div class="conv-item__top">
                    <span class="conv-item__name">{{ e($conv['name']) }}</span>
                    <span class="conv-item__time">{{ $conv['last_message_time'] }}</span>
                </div>
                <div class="conv-item__bottom">
                    <span class="conv-item__snippet">{{ e(\Illuminate\Support\Str::limit($conv['last_message'], 45)) }}</span>
                    <div class="conv-item__badges">
                        <span class="conv-item__channel conv-item__channel--{{ $conv['channel'] }}">{{ strtoupper($conv['channel']) }}</span>
                        @if ($conv['unread_count'] > 0)
                        <span class="conv-item__unread-badge">{{ $conv['unread_count'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Unread summary --}}
    <div class="inbox-sidebar__footer">
        <span id="sidebarUnreadCount">{{ $unread_count }} unread</span>
    </div>
</div>
