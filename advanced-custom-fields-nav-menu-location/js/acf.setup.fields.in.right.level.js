// since acf-location-nav-menu v.1.1
jQuery(function() {
    acfUpdateMenuLayout();
});


function acfUpdateMenuLayout() {
    
    var isDragging = false;
    
    jQuery('.menu-item-handle')
    .mousedown(function() {
        isDragging = false;
    })
    .mousemove(function() {
        isDragging = true;
     })
    .mouseup(function() {
        var wasDragging = isDragging;
        isDragging = false;
        if (wasDragging) {
            var menuItemDepth = '';
            setTimeout(function() {
                jQuery('.menu-item-handle').each(function() {
                    var listItem = jQuery(this).parent().parent();
                    var menuItemClasses = listItem.attr('class').split(/\s+/);
                    var acfFields = listItem.find('.acf-fields');
                    jQuery.each(menuItemClasses, function(index, item) {
                        if (item.indexOf('menu-item-depth-') >= 0) {
                            var menuItemDepthClass = item;
                            menuItemDepth = item.replace( 'menu-item-depth-', '' );
                        }
                    });
                    acfGroupVisibilityDepth = '';
                    acfFields.each(function() {
                        var acfFieldsGroup = jQuery(this);
                        acfGroupClasses = acfFieldsGroup.attr('class').split(/\s+/);
                        jQuery.each(acfGroupClasses, function(index, item) {
                            if (item.indexOf('level-') >= 0) {
                                var acfGroupVisibilityDepthClass = item;
                                acfGroupVisibilityDepth = item.replace( 'level-', '' );
                            }
                        });
                        
                        if (acfGroupVisibilityDepth) {
                            if (menuItemDepth == acfGroupVisibilityDepth && acfFieldsGroup.hasClass('acf-hidden')) {
                                acfFieldsGroup.removeClass('acf-hidden');
                            }
                            
                            if (menuItemDepth != acfGroupVisibilityDepth && !acfFieldsGroup.hasClass('acf-hidden')) {
                                acfFieldsGroup.addClass('acf-hidden');
                            }
                            
                            if (menuItemDepth == '0' && acfGroupVisibilityDepth == '00' && !acfFieldsGroup.hasClass('acf-hidden')) {
                                acfFieldsGroup.addClass('acf-hidden');
                            }
                            
                            if (menuItemDepth != '0' && acfGroupVisibilityDepth == '00' && acfFieldsGroup.hasClass('acf-hidden')) {
                                acfFieldsGroup.removeClass('acf-hidden');
                            }
                            
                        } else {
                            acfFieldsGroup.removeClass('acf-hidden');
                        }
                    });
                    
                })
            }, 200);
        }
    });
}
