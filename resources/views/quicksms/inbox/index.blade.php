@extends('layouts.quicksms')

@section('title', 'Inbox')

@section('body_class', 'qsms-fullbleed')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/inbox-v2.css') }}">
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<link rel="stylesheet" href="{{ asset('css/emoji-picker.css') }}?v=20260310">
@endpush

@section('content')
<div class="inbox-container">
    <div class="inbox-layout">

        {{-- Left: Conversation list --}}
        @include('quicksms.inbox.partials.conversation-list')

        {{-- Centre: Chat thread + composer --}}
        <div class="chat-pane-with-composer">
            @include('quicksms.inbox.partials.chat-pane')
            @include('quicksms.inbox.partials.composer')
        </div>

    </div>
</div>

{{-- Contact details modal --}}
@include('quicksms.inbox.partials.contact-sidebar')

{{-- Conversation data for JS --}}
<script>
window.__inbox = {
    conversations: @json($conversations),
    unreadCount: {{ $unread_count }},
    senderIds: @json($sender_ids),
    rcsAgents: @json($rcs_agents),
    templates: @json($templates),
    csrfToken: '{{ csrf_token() }}',
    routes: {
        conversations: '{{ url("/api/inbox/conversations") }}',
        messages: '{{ url("/api/inbox/conversations") }}',
        sendReply: '{{ url("/api/inbox/conversations") }}',
    }
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/emoji-picker.js') }}?v=20260310"></script>
<script src="{{ asset('js/inbox/conversation-list.js') }}"></script>
<script src="{{ asset('js/inbox/chat-thread.js') }}"></script>
<script src="{{ asset('js/inbox/composer.js') }}?v=20260318f"></script>
<script src="{{ asset('js/inbox/inbox-app.js') }}"></script>
{{-- v1 RCS Wizard integration --}}
<script src="{{ asset('js/rcs-preview-renderer.js') }}"></script>
<script src="{{ asset('js/rcs-wizard.js') }}?v=20260318f"></script>
<script src="{{ asset('js/shared-image-editor.js') }}"></script>
@endpush
