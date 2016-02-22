jQuery(function() {
    fixGoogleMapRandaring();
});

function fixGoogleMapRandaring() {
    if ( typeof acf !== undefined && typeof google !== undefined ) {
        jQuery('.menu-item').each(function() {
            var menuItem = jQuery(this);
            menuItem.find('.item-edit').on('click', function() {
                
                if ( menuItem.hasClass('menu-item-edit-inactive')) {
                    
                    var maps = menuItem.find('.acf-google-map');
                    setTimeout(function() {
                        maps.each(function() {
                            $field = jQuery(this).children('.canvas').attr('data-key');
                            var map_args = {								
                                zoom:		parseInt(jQuery(this).attr('data-zoom')),
                                center:		new google.maps.LatLng(jQuery(this).attr('data-lat'), jQuery(this).attr('data-lng')),
                                mapTypeId:	google.maps.MapTypeId.ROADMAP
                                
                            };
                            
                            var map = new google.maps.Map(jQuery(this).find('.canvas')[0], map_args);
                            
                            google.maps.event.trigger(map, "resize");
                        } )
                    }, 300);
                }
                
            });
        });
    }
}