<?php
/*
Plugin Name: Location "Nav Menu" for ACF
Plugin URI: 
Description: Add-On plugin for Advanced Custom Fields (ACF) PRO that adds a "Nav Menu" location for fields
Version: 1.1.1
Author: psd2html.com
Author URI: http://psd2html.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: acf-location-nav-menu
Domain Path: /languages
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class acf_location_nav_menu_plugin
{
	/*
	*  Construct
	*
	*  @description:
	*  @since: 1.0
	*  @created: 2/7/15
	*/

	function __construct()
	{
		//init the plugin core
		add_action( 'admin_init', array( $this, 'init' ), 1 );
		
	}
	
	
	/*
	*  Initialization
	*
	*  @description: includes add-on logic
	*  @since: 1.0
	*  @created: 2/7/15
	*/
	function init()
	{
		
		if ( class_exists( 'acf' ) )
		{
			
			// defining new acf location -> "Menu"
			include_once( 'acf_location_nav_menu.php' );
			// saving and deleting fields for menu items
			include_once( 'inc/fields_actions.php' );
			// menu walker, rendering of acf fields
			include_once( 'inc/edit_custom_walker.php' );
			
			// add custom fields to new menu items
			add_action( 'wp_update_nav_menu_item', array( $this, 'acf_location_nav_menu_add_fields' ), 1, 3 );
		
			// include acf scripts and styles on nav-menus.php page if there was no menu items before adding new items
			add_action('admin_enqueue_scripts', array( $this, 'acf_location_nav_menu_before_menu_was_saved' ), 1 );
			
			// edit menu walker
			add_filter( 'wp_edit_nav_menu_walker', array( $this, 'acf_location_nav_menu_edit_walker' ), 1, 2 );
		
		}
		
	}
	
	
	/*
	*  nav_menu_admin_enqueue_scripts_and_styles
	*
	*  @description: enqueueing acf scripts and styles for the nav menus page
	*  @since: 1.0
	*  @created: 2/7/15
	*  @updated 7/1/16
	*/
	public function nav_menu_admin_enqueue_scripts_and_styles()
	{
		
		wp_enqueue_style( 'acf-field-group' );
		wp_enqueue_style( 'acf-location-nav-menu' );
		wp_enqueue_script( 'google-map-fix-randering' );
		wp_enqueue_script( 'acf-setup-fields-in-right-level' );

	}
	
	
	/*
	*  acf_location_nav_menu_before_menu_was_saved
	*
	*  @description: enqueueing acf styles & scripts for the nav menus page after menu creation & before it was saved
	*  @since: 1.0
	*  @created: 1/10/15
	*/
	public function acf_location_nav_menu_before_menu_was_saved($hook)
	{
		
		if ( $hook == 'nav-menus.php' )
		{
			
			acf_enqueue_scripts();
			acf_enqueue_uploader();
			$this->nav_menu_admin_enqueue_scripts_and_styles();
			
		}
		
	}		
	
	
	/**
	 * acf_location_nav_menu_add_fields
	 *
	 * @description	initialize acf javascript when add new nav menu item
	 * @access      public
	 * @since       1.0
	 * @created: 	28/9/15
	 * @updated: 	7/1/16
	*/
	function acf_location_nav_menu_add_fields( $menu_id, $menu_item_db_id, $args )
	{
		
		if (defined('DOING_AJAX') && DOING_AJAX) {
		
			?>
			<script type="text/javascript">
				(function($) {
					
					var newMenuItem = $('#menu-item-<?php echo $menu_item_db_id; ?>');
					
					if( typeof acf !== 'undefined' )
					{
						
						// setup fields
						acf.do_action( 'append', $( "#menu-item-<?php echo $menu_item_db_id; ?>" ) );
						
						
					}
					
					
					//rerender google map after open clicking nav menu edit
					newMenuItem.find('.item-edit').on('click', function() {
						
						if ( newMenuItem.hasClass('menu-item-edit-inactive')) {
							
							var maps = newMenuItem.find('.acf-google-map');
							setTimeout(function() {
								maps.each(function() {
									$field = jQuery(this).children('.canvas').attr('data-key');
									var map_args = {								
										zoom:		parseInt(jQuery(this).attr('data-zoom')),
										center:		new google.maps.LatLng($(this).attr('data-lat'), $(this).attr('data-lng')),
										mapTypeId:	google.maps.MapTypeId.ROADMAP
										
									};
									
									var map = new google.maps.Map(jQuery(this).find('.canvas')[0], map_args);
									
									google.maps.event.trigger(map, "resize");
								} )
							}, 300);
						}
						
					});
					
					
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
					
				})(jQuery);
			
			</script>
			<?php

		}
		
	}
	
	
	/**
	 * acf_location_nav_menu_edit_walker
	 *
	 * @description	Define new Walker edit
	 * @access      public
	 * @since       1.0 
	 * @return      void
	 * @created: 	29/9/15
	*/
	function acf_location_nav_menu_edit_walker( $walker, $menu_id )
	{
		
		$this->nav_menu_admin_enqueue_scripts_and_styles();
		
		return 'Walker_Nav_Menu_Edit_Custom';
		
	}
	
}

new acf_location_nav_menu_plugin();
