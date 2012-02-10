<?php
/**
 * @package Odds Widgets
 * @version 1.1.1
 */
/*
Plugin Name: Odds Widgets
Plugin URI: http://widgets.valuechecker.co.uk/wordpress-plugin/
Description: Create and customize live updating sport odds widgets on your wordpress blog
Author: Dragos Ionescu
Version: 1.1.1
Author URI: http://widgets.valuechecker.co.uk/
*/

/**
* Check for function, class and constant definition confilcts 
* and only load the rest of the plugin if no conflicts are found
*/
$odds_widgets_functions_used = array(
	'odds_widgets_admin_register_head', 
	'odds_widgets_add_menu_item',
	'odds_widgets_admin_page', 
	'odds_widgets_refresh_widget_type_callback', 
	'odds_widgets_refresh_widget_callback', 
	'odds_widgets_preview_widget_callback', 
	'odds_widgets_get_version',
	
); 
$odds_widgets_classes_used = array(
	'Odds_Widget', 
	'Odds_Widget_Utils',
); 
$odds_widgets_constants_used = array(
	'ODDS_WIDGETS_URL', 
	'ODDS_WIDGETS_API_URL',
	'ODDS_WIDGETS_DB_VERSION',
); 

$odds_widgets_errors = array(); 

// check for wp version >= 2.8
global $wp_version;
$odds_widgets_wp_min_version = '2.8'; 
if ( version_compare( $wp_version, $odds_widgets_wp_min_version, '<' ) ) {
	$odds_widgets_errors[] = __( 'Odds Widgets plugin only works on WordPress' ) . ' ' . $odds_widgets_wp_min_version . '+<br />' . __( 'You need to upgrade your blog to use this plugin!' );
}

// check for curl php extension
$loaded_extensions = get_loaded_extensions();
if ( !in_array( 'curl', $loaded_extensions ) ) {
	$odds_widgets_errors[] = __( 'Odds Widgets plugin requires CURL PHP extension to be installed' ); 
}
	
// check for function names conflicts
foreach ( $odds_widgets_functions_used as $f_name ) {
	if ( function_exists( $f_name ) ) {
		$odds_widgets_errors[] = __( 'Function already defined: ' ) . $f_name;
	}
}

// check for class names conflicts
foreach ( $odds_widgets_classes_used as $cl_name ) {
	if ( class_exists( $cl_name ) ) {
		$odds_widgets_errors[] = __( 'Class already defined: ' ) . $cl_name;
	}
}

// check for constant names conflicts
foreach ( $odds_widgets_constants_used as $c_name ) {
	if ( defined( $c_name ) ) {
		$odds_widgets_errors[] = __( 'Constant already defined: ' ) . $c_name;
	}
}

if ( !empty( $odds_widgets_errors ) ) {
	add_action( 'admin_notices', 'odds_widgets_show_errors' );
} else {
	// Load the plugin
	include_once( 'odds-widgets-loader.php' );
}

/**
* Show the odds widgets errors if any
*/
function odds_widgets_show_errors() {
	global $odds_widgets_errors; 
	
	if ( !empty( $odds_widgets_errors ) ) {
		print '<div class="error"><p><strong>'
		.__('The &quot;Odds Widgets&quot; plugin cannot load correctly due to following errors:')
		.'</strong></p><ul>' 
		.implode('</li><li>', $odds_widgets_errors)
		.'</ul></div>';
	}
} 
 
/* End of File */