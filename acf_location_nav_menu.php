<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class acf_location_nav_menu extends acf_location
{
	/*
	*  Construct
	*
	*  @description:
	*  @since: 1.0
	*  @created: 2/07/15
	*  @updated 7/1/16
	*/

	function __construct()
	{
		
		add_filter( 'acf/location/rule_match/menu', array( $this, 'acf_location_nav_menu_rule_matches' ), 1, 3 );
		add_filter( 'acf/location/rule_types', array( $this, 'acf_location_nav_menu_rule_type' ), 1, 1 );
		add_filter( 'acf/location/rule_values/menu', array( $this, 'acf_location_nav_menu_rule_value' ), 1, 1 );
		
		add_action('acf/field_group/admin_head', array( $this, 'admin_head' ), 0, 0 );
		
		wp_register_style( 'acf-location-nav-menu', plugin_dir_url( __FILE__ ).'css/acf-location-nav-menu.css' );
		wp_register_script( 'google-map-fix-randering', plugin_dir_url( __FILE__ ).'js/google.map.fix.randering.js', array( 'jquery' ) );
		wp_register_script( 'acf-setup-fields-in-right-level', plugin_dir_url( __FILE__ ).'js/acf.setup.fields.in.right.level.js', array( 'jquery' ) );
		

	}
	
	
	 /*
	*  acf_location_nav_menu_rule_matches
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	2/07/15
	*  @since	1.0
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	 
	function acf_location_nav_menu_rule_matches( $match, $rule, $options )
	{
		
		$selected_menu_id = $rule['value'];
		
		$menu = isset($options['menu']) ? $options['menu'] : '';
		
		if( $menu ) {
		
			if( $rule['operator'] == "==" ) {
				
				$match = ( $menu == $selected_menu_id );
				
				
				// override for "all"
				if( $selected_menu_id === 'all' ) {
						
					$match = true;
					
				}
		
			} elseif( $rule['operator'] == "!=" ) {
				
				$match = ( $menu != $selected_menu_id );
				
				
				// override for "all"
				if( $selected_menu_id === 'all' ) {
			
					$match = false;
					
				}
				
			}
		
		}
		
		// return        
		return $match;
	
	}
	
	 /*
	*  acf_location_nav_menu_rule_type
	*
	*  This function will define a location rule type
	*
	*  @type	filter
	*  @date	2/07/15
	*  @since	1.0
	*
	*  @param	$choices (array) 
	*  @return	$choices (array)
	*/
	
	function acf_location_nav_menu_rule_type( $choices )
	{
		
		$choices['Forms']['menu'] = __( 'Nav Menu', 'acf-location-nav-menu' );
	
		return $choices;
	
	}
	
	
	/*
	*  acf_location_nav_menu_rule_type
	*
	*  This function will add list of nav menus to location rules
	*
	*  @type	filter
	*  @date	2/07/15
	*  @since	1.0
	*
	*  @param	$choices (array) 
	*  @return	$choices (array)
	*/
	
	function acf_location_nav_menu_rule_value( $choices )
	{
		
		$choices = array(
			'all' 		=> __( 'All', 'acf-location-nav-menu' ),
		);
		
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		
		foreach( $menus as $menu ) {
		
			$choices[ $menu->term_id ] = $menu->name;
			
		}
		
		return $choices;
	
	}
	
	/*
	*  admin_head
	*
	*  This function will add metabox to fields group settings screen
	*
	*  @type	action
	*  @date	25/12/15
	*  @since	1.1
	*
	*/
	
	function admin_head()
	{
		
		add_meta_box('acf-menu-location-settings', __("Menu Location Settings",'acf'), array($this, 'render_menu_location_settings'), 'acf-field-group', 'side', 'default');
		
	}
	
	/*
	*  admin_head
	*
	*  This function will add metabox to fields group settings screen
	*
	*  @date	25/12/15
	*  @since	1.1
	*  @update 	7/1/16
	*/
	
	function render_menu_location_settings( $field_group )
	{
		
		global $field_group;
		
		// field key (leave in for compatibility)
		if( !acf_is_field_group_key( $field_group['key']) ) {
			
			$field_group['key'] = uniqid('group_');
			
		} ?>
		
		<?php // menu_item_level
		acf_render_field_wrap(array(
			'label'			=> __('Menu Item Level','acf-location-nav-menu'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'menu_item_level',
			'prefix'		=> 'acf_field_group',
			'value'			=> isset( $field_group['menu_item_level'] ) ? $field_group['menu_item_level'] : 'all',
			'choices' 		=> array(
				'all'			=> __("All",'acf-location-nav-menu'),
				'first'			=>	__("First level only",'acf-location-nav-menu'),
				'second'		=>	__("Second level only",'acf-location-nav-menu'),
				'third'			=>	__("Third level only",'acf-location-nav-menu'),
				'not-first'		=>	__("All except first",'acf-location-nav-menu'),
			)
		)); ?>
		<div class="acf-hidden">
			<input type="hidden" name="acf_field_group[key]" value="<?php echo $field_group['key']; ?>" />
		</div>
		<script type="text/javascript">
		//added 7/1/16
		jQuery(function() {
			show_location_settings();
		});
		
		if( typeof acf !== 'undefined' ) {
				
			acf.postbox.render({
				'id': 'acf-menu-location-settings',
				'label': 'left'
			});
		
		}
		//added 7/1/16
		function show_location_settings() {
			jQuery('#acf-menu-location-settings').hide();
			jQuery('.location-rule-param').each(function() {
				var locationRuleParam = jQuery(this);
				if (locationRuleParam.val() == 'menu') {
					jQuery('#acf-menu-location-settings').show();
				}
				locationRuleParam.on('change', function() {
					if (jQuery(this).val() == 'menu') {
						jQuery('#acf-menu-location-settings').show();
					} else {
						jQuery('#acf-menu-location-settings').hide();
					}
				});
			});
		}
		
		</script>
		
	<?php }
	
}
	
new acf_location_nav_menu();