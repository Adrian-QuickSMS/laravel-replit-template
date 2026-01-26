/**
 * Table Dropdown Fix
 * Fixes dropdown positioning issues in tables with sticky columns, overflow, etc.
 * Uses a simpler approach: moves the original menu to body temporarily.
 */
(function() {
    'use strict';

    var activeData = null;

    function showDropdown(e) {
        var $dropdown = $(this);
        var $btn = $dropdown.find('[data-bs-toggle="dropdown"]');
        var $menu = $dropdown.find('.dropdown-menu');
        
        if (!$btn.length || !$menu.length) return;

        var btnRect = $btn[0].getBoundingClientRect();
        var menuWidth = $menu.outerWidth() || 160;
        
        var top = btnRect.bottom + 2;
        var left = btnRect.right - menuWidth;
        
        if (left < 10) {
            left = btnRect.left;
        }

        activeData = {
            $dropdown: $dropdown,
            $menu: $menu,
            $btn: $btn,
            originalParent: $menu.parent(),
            originalStyles: $menu.attr('style') || ''
        };

        $menu.appendTo('body');
        
        $menu.css({
            position: 'fixed',
            top: top + 'px',
            left: left + 'px',
            zIndex: 99999,
            transform: 'none',
            inset: 'auto'
        });

        setTimeout(function() {
            var menuHeight = $menu.outerHeight();
            if (top + menuHeight > window.innerHeight - 10) {
                $menu.css('top', (btnRect.top - menuHeight - 2) + 'px');
            }
        }, 10);
    }

    function hideDropdown(e) {
        if (!activeData) return;
        
        var $menu = activeData.$menu;
        var $dropdown = activeData.$dropdown;
        
        $menu.attr('style', activeData.originalStyles);
        $menu.removeClass('show');
        $dropdown.append($menu);
        
        activeData = null;
    }

    $(document).on('shown.bs.dropdown', '.table-action-dropdown', showDropdown);
    $(document).on('hidden.bs.dropdown', '.table-action-dropdown', hideDropdown);

    $(window).on('scroll resize', function() {
        if (activeData && activeData.$menu.hasClass('show')) {
            var btnRect = activeData.$btn[0].getBoundingClientRect();
            var menuWidth = activeData.$menu.outerWidth() || 160;
            var menuHeight = activeData.$menu.outerHeight();
            
            var top = btnRect.bottom + 2;
            var left = btnRect.right - menuWidth;
            
            if (left < 10) left = btnRect.left;
            if (top + menuHeight > window.innerHeight - 10) {
                top = btnRect.top - menuHeight - 2;
            }
            
            activeData.$menu.css({
                top: top + 'px',
                left: left + 'px'
            });
        }
    });
})();
