{{--
    Shared Emoji Picker Component

    Includes the CSS and JS for the QSEmojiPicker floating popover.
    Include this partial once per page. It registers the assets via @push stacks.

    Usage after including:
        var picker = new QSEmojiPicker({
            triggerEl: document.getElementById('emojiPickerBtn'),
            textareaEl: document.getElementById('smsContent'),
            onInsert: function(emoji) { handleContentChange(); }
        });
--}}

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/emoji-picker.css') }}?v=20260310">
@endpush

@push('scripts')
<script src="{{ asset('js/emoji-picker.js') }}?v=20260310"></script>
@endpush
@endonce
