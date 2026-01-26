/**
 * Table Dropdown Fix
 * Fixes dropdown positioning issues in tables with sticky columns, overflow, etc.
 * Works by cloning the dropdown menu to body and positioning it with fixed coordinates.
 */
(function() {
    'use strict';

    var activeDropdown = null;
    var clonedMenu = null;

    function positionDropdown($btn, $menu) {
        var btnRect = $btn[0].getBoundingClientRect();
        var menuWidth = $menu.outerWidth();
        var menuHeight = $menu.outerHeight();
        
        var top = btnRect.bottom + 2;
        var left = btnRect.right - menuWidth;
        
        if (left < 10) {
            left = btnRect.left;
        }
        
        if (top + menuHeight > window.innerHeight - 10) {
            top = btnRect.top - menuHeight - 2;
        }
        
        $menu.css({
            position: 'fixed',
            top: top + 'px',
            left: left + 'px',
            zIndex: 99999,
            display: 'block'
        });
    }

    function showDropdown(e) {
        var $dropdown = $(this);
        var $btn = $dropdown.find('[data-bs-toggle="dropdown"]');
        var $menu = $dropdown.find('.dropdown-menu');
        
        if (!$btn.length || !$menu.length) return;

        if (clonedMenu) {
            clonedMenu.remove();
            clonedMenu = null;
        }

        clonedMenu = $menu.clone();
        clonedMenu.removeClass('show').addClass('table-dropdown-clone');
        $('body').append(clonedMenu);
        
        clonedMenu.find('.dropdown-item').on('click', function(e) {
            var originalItem = $menu.find('.dropdown-item').eq($(this).index());
            originalItem.trigger('click');
            
            $btn.dropdown('hide');
        });

        setTimeout(function() {
            clonedMenu.addClass('show');
            positionDropdown($btn, clonedMenu);
        }, 0);

        $menu.css('visibility', 'hidden');
        
        activeDropdown = {
            $dropdown: $dropdown,
            $btn: $btn,
            $menu: $menu
        };
    }

    function hideDropdown(e) {
        if (clonedMenu) {
            clonedMenu.remove();
            clonedMenu = null;
        }
        
        if (activeDropdown && activeDropdown.$menu) {
            activeDropdown.$menu.css('visibility', '');
        }
        
        activeDropdown = null;
    }

    $(document).on('shown.bs.dropdown', '.table-action-dropdown', showDropdown);
    $(document).on('hidden.bs.dropdown', '.table-action-dropdown', hideDropdown);

    $(window).on('scroll resize', function() {
        if (activeDropdown && clonedMenu) {
            positionDropdown(activeDropdown.$btn, clonedMenu);
        }
    });

    $(document).on('click', function(e) {
        if (clonedMenu && !$(e.target).closest('.table-dropdown-clone').length && 
            !$(e.target).closest('.table-action-dropdown').length) {
            if (activeDropdown && activeDropdown.$btn) {
                activeDropdown.$btn.dropdown('hide');
            }
        }
    });
})();
