<?php

/*
*  ACF Location "Nav Menu" Fields Actions Class
*
*  All the logic for adding and deleting fields in location "Nav Menu"
*
*  @class 		acf_location_nav_menu_fields_actions
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists('acf_location_nav_menu_fields_actions') ) :
	
	class acf_location_nav_menu_fields_actions {
		
		function __construct() {
			
			// save all non empty fields of all menu items
			add_action( 'wp_update_nav_menu', array($this, 'acf_location_nav_menu_save_post'), 5 );
			
			// delete all fields of all menu items when deleting the whole menu
			add_action( 'wp_delete_nav_menu', array($this, 'acf_location_nav_menu_delete_all_menu_fields'), 5 );
			
			// delete specific menu item fields
			add_action( 'delete_post', array($this, 'acf_location_nav_menu_delete_menu_item_fields'), 5, 1 );
			
		}
		
		
		function acf_location_nav_menu_save_post($menu_id)
		{
			// bail early if no ACF data
			if( empty($_POST['acf']) )
			{
				
				return;
				
			}
			
			// save $_POST data
			foreach( $_POST['acf'] as $item_id => $item_fields )
			{
				
				foreach ( $item_fields as $k => $v ) {
					
					// get field
					$field = acf_get_field( $k );
					
					// update field
					if( $field && $v != '' ) {
						
						acf_update_value( $v, $item_id, $field );
						
					}
					
				}
				
			}
		
		}
		
		function acf_location_nav_menu_delete_all_menu_fields($menu_id)
		{
			
			$menu_items = wp_get_nav_menu_items($menu_id);
			
			if ( $menu_items )
			{
				
				foreach ( $menu_items as $item )
				{
					
					$meta_fields = get_post_meta($item->id);
					
					if ( $meta_fields )
					{
					
						foreach ( $meta_fields as $k => $v )
						{
							
							delete_post_meta($item, $k);
							
						}
						
					}
					
				}
				
			}
			
		}
		
		function acf_location_nav_menu_delete_menu_item_fields($post_id)
		{
			$post = get_post($post_id);
			
			if ( $post->post_type == 'nav_menu_item' )
			{
					
				$meta_fields = get_post_meta($post_id);
				
				if ( $meta_fields )
				{
				
					foreach ( $meta_fields as $k => $v )
					{
						
						delete_post_meta($post_id, $k);
						
					}
					
				}
								
			}
			
		}
	}
	
	new acf_location_nav_menu_fields_actions();

endif;

?>
