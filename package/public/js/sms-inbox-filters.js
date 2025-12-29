/**
 * SMS INBOX - FILTER AND SEARCH FUNCTIONALITY
 * Handles all filtering, searching, and sorting of conversations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all conversation items
    const conversationItems = document.querySelectorAll('.conversation-item');

    // Get filter elements
    const searchInput = document.getElementById('searchConversations');
    const statusFilter = document.getElementById('statusFilter');
    const sourceFilter = document.getElementById('sourceFilter');
    const sortFilter = document.getElementById('sortFilter');

    // Store original state
    let allConversations = Array.from(conversationItems).map(item => ({
        element: item,
        name: item.querySelector('h6').textContent.trim(),
        message: item.querySelector('p').textContent.trim(),
        time: item.querySelector('small').textContent.trim(),
        unread: item.querySelector('.badge') !== null
    }));

    /**
     * Apply all filters and search
     */
    function applyFilters() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const status = statusFilter ? statusFilter.value : 'all';
        const source = sourceFilter ? sourceFilter.value : 'all';
        const sort = sortFilter ? sortFilter.value : 'newest';

        let filteredConversations = [...allConversations];

        // Apply search filter
        if (searchTerm) {
            filteredConversations = filteredConversations.filter(conv => {
                const nameMatch = conv.name.toLowerCase().includes(searchTerm);
                const messageMatch = conv.message.toLowerCase().includes(searchTerm);
                const numberSearch = searchTerm.replace(/\D/g, ''); // Remove non-digits
                const numberMatch = conv.name.replace(/\D/g, '').includes(numberSearch);

                return nameMatch || messageMatch || numberMatch;
            });
        }

        // Apply status filter
        if (status === 'unread') {
            filteredConversations = filteredConversations.filter(conv => conv.unread);
        }
        // Note: SMS/RCS filtering would require additional data attributes

        // Apply source filter (would require data attributes in real implementation)
        // if (source !== 'all') {
        //     filteredConversations = filteredConversations.filter(conv => conv.source === source);
        // }

        // Apply sort
        if (sort === 'oldest') {
            filteredConversations.reverse();
        }

        // Render filtered conversations
        renderConversations(filteredConversations);
    }

    /**
     * Render conversations
     */
    function renderConversations(conversations) {
        // Hide all conversations first
        allConversations.forEach(conv => {
            conv.element.style.display = 'none';
        });

        // Show filtered conversations
        conversations.forEach(conv => {
            conv.element.style.display = 'block';
        });

        // Update unread count
        const unreadCount = conversations.filter(c => c.unread).length;
        const unreadBadge = document.querySelector('.inbox-header .badge');
        if (unreadBadge) {
            unreadBadge.textContent = `${unreadCount} unread`;
            unreadBadge.style.display = unreadCount > 0 ? 'inline' : 'none';
        }

        // Show "no results" message if empty
        const scrollable = document.querySelector('.conversations-scrollable');
        const noResults = scrollable.querySelector('.no-results');

        if (conversations.length === 0) {
            if (!noResults) {
                const noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-results text-center text-muted p-4';
                noResultsDiv.innerHTML = '<p>No conversations found</p>';
                scrollable.appendChild(noResultsDiv);
            }
        } else {
            if (noResults) {
                noResults.remove();
            }
        }
    }

    /**
     * Attach event listeners
     */
    if (searchInput) {
        searchInput.addEventListener('input', debounce(applyFilters, 300));
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }

    if (sourceFilter) {
        sourceFilter.addEventListener('change', applyFilters);
    }

    if (sortFilter) {
        sortFilter.addEventListener('change', applyFilters);
    }

    /**
     * Conversation click handler
     */
    conversationItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all
            conversationItems.forEach(i => i.classList.remove('active'));

            // Add active to clicked
            this.classList.add('active');

            // Update conversation header
            const name = this.querySelector('h6').textContent.trim();
            const headerName = document.querySelector('.conversation-header-fixed h5');
            if (headerName) {
                headerName.textContent = name;
            }

            // Clear messages and load new ones (would be AJAX in real app)
            console.log('Load messages for:', name);
        });
    });

    /**
     * Debounce helper
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Send message handler
     */
    const sendButton = document.querySelector('.message-input-fixed .btn-primary');
    const messageTextarea = document.querySelector('.message-input-fixed textarea');

    if (sendButton && messageTextarea) {
        sendButton.addEventListener('click', function() {
            const message = messageTextarea.value.trim();

            if (message) {
                // Add message to thread
                const messagesContainer = document.querySelector('.messages-scrollable');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message-bubble sent';
                messageDiv.innerHTML = `
                    <p>${escapeHtml(message)}</p>
                    <small>${getCurrentTime()}</small>
                `;
                messagesContainer.appendChild(messageDiv);

                // Clear textarea
                messageTextarea.value = '';

                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;

                console.log('Message sent:', message);
            }
        });

        // Send on Enter (but not Shift+Enter)
        messageTextarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendButton.click();
            }
        });
    }

    /**
     * Channel selector
     */
    const channelButtons = document.querySelectorAll('.btn-channel');
    channelButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            channelButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            console.log('Channel changed to:', this.textContent.trim());
        });
    });

    /**
     * Utility: Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Utility: Get current time
     */
    function getCurrentTime() {
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    // Initialize
    console.log('SMS Inbox filters initialized');
});
