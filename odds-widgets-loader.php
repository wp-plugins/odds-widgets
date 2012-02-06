<?php
// define plugin constants
define( 'ODDS_WIDGETS_URL', 'http://widgets.valuechecker.co.uk/' );
define( 'ODDS_WIDGETS_API_URL', ODDS_WIDGETS_URL . 'api/' );
define( 'ODDS_WIDGETS_DB_VERSION', '1.1' ); 

require_once( 'includes/Odds_Widget.php' );
require_once( 'includes/Odds_Widget_Utils.php' );

// register plugin settings page in admin menu
add_action( 'admin_menu', 'odds_widgets_add_menu_item' );

// register the widget
add_action( 'widgets_init', 'Odds_Widget::register_this_widget' );

// Register plugin styles and scripts in admin header
add_action( 'admin_enqueue_scripts', 'odds_widgets_admin_register_head' );

// register callback functions for ajax calls
add_action( 'wp_ajax_odds_widgets_refresh_widget_type', 'odds_widgets_refresh_widget_type_callback' );
add_action( 'wp_ajax_odds_widgets_refresh_widget', 'odds_widgets_refresh_widget_callback' );
add_action( 'wp_ajax_odds_widgets_preview_widget', 'odds_widgets_preview_widget_callback' );

/** 
* Register plugin styles and scripts in admin header
* Also check if installed WordPress version is under 3.0 and the admin opted to replace the default jQuery library
* and replaces it with version 1.4.2, which is the minimum jQuery version needed for the widgets admin to work in IE6-IE8
* The admin can disable this option from the plugin's settings page
*/
function odds_widgets_admin_register_head() {
	wp_enqueue_style( 'odds_widgets_css', plugins_url( 'css/odds-widgets.css', __FILE__ ) );
	
	/**
	* if current WP version is under 3.0 and the user has opted for this, we replace jquery 1.3+ that comes by default with 1.4.2, 
	* in order for some methods like "live" to work in IE
	*/
	global $wp_version;
	if ( version_compare( $wp_version, '3.0', '<' ) && 1 == esc_attr( get_option( 'odds_widgets_replace_jquery' ) ) ) {
		wp_deregister_script( 'jquery' );
		wp_enqueue_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js' ); 
	}
	wp_enqueue_script( 'odds_widgets_js', plugins_url( 'js/odds-widgets.js', __FILE__ ), array('jquery') ); 
}

/**
* Adds a menu item inside the WordPress admin
*/
function odds_widgets_add_menu_item() {
	add_submenu_page(
		'options-general.php',
		'Odds Widgets Configuration',
		'Odds Widgets',
		'manage_options',
		'odds-widgets',
		'odds_widgets_admin_page'
	);
}

/**
* Controller that generates and handles admin page
*/
function odds_widgets_admin_page() {
	$msg = ''; // used to display a success message on updates
	
	if ( !empty($_POST) && check_admin_referer( 'odds_widgets_admin_options_update', 'odds_widgets_admin_nonce' ) ) {
		
		// store data to send to widgets api
		$curl_data = array(); 
		
		// Get the email setting before updating with the new value so in case of unsubscribe we can remove both values
		$odds_widgets_old_subscribe_email = esc_attr( get_option( 'odds_widgets_subscribe_email' ) );
		if ( Odds_Widget_Utils::validate_email( $odds_widgets_old_subscribe_email ) ) {
			$curl_data['old_email'] = $odds_widgets_old_subscribe_email; 
		}
		
		// get new email
		$new_odds_widgets_new_subscribe_email = stripslashes( $_POST['odds_widgets_subscribe_email'] ); 
		
		if ( Odds_Widget_Utils::validate_email( $new_odds_widgets_new_subscribe_email ) ) {
			update_option( 'odds_widgets_subscribe_email', $new_odds_widgets_new_subscribe_email );
			$curl_data['new_email'] = $new_odds_widgets_new_subscribe_email;
		}
		else {
			$msg .= '<div class="error"><p>The specified email <strong>' . esc_attr( $new_odds_widgets_new_subscribe_email ) . '</strong> is not valid! Email was not updated!</p></div>';
		}
		
		// get the option to subscribe/unsubscribe
		if ( isset( $_POST['odds_widgets_subscribe'] ) && 1 == $_POST['odds_widgets_subscribe'] ) {
			$odds_widgets_subscribe = 1; 
		} else {
			$odds_widgets_subscribe = 0;
		}
		update_option( 'odds_widgets_subscribe', $odds_widgets_subscribe );
		$curl_data['odds_widgets_subscribe'] = $odds_widgets_subscribe; 
		
		if ( !empty( $subscribe_emails ) ) {
			Odds_Widget_Utils::update_email_subscription( $subscribe_emails, $odds_widgets_subscribe ); 
		}
		
		if ( isset( $_POST['odds_widgets_link_to_us'] ) && 1 == $_POST['odds_widgets_link_to_us'] ) {
			update_option( 'odds_widgets_link_to_us', 1 );
		} else {
			update_option( 'odds_widgets_link_to_us', 0 );
		}
		
		if ( isset( $_POST['odds_widgets_replace_jquery'] ) && 1 == $_POST['odds_widgets_replace_jquery'] ) {
			update_option( 'odds_widgets_replace_jquery', 1 );
		} else {
			update_option( 'odds_widgets_replace_jquery', 0 );
		}
		$msg .= '<div class="updated"><p>Your settings have been <strong>updated</strong></p></div>';
		
		if ( isset( $curl_data['new_email'] ) && in_array( $curl_data['odds_widgets_subscribe'], array(0, 1) ) ) {
			Odds_Widget_Utils::curl_api_request( "action=save_emails_subscription&" . http_build_query( $curl_data ) );
		}
	}

	include_once( 'includes/admin-page.php' );
} 

/** 
* Ajax callback to refresh the widget type dropdown
*/
function odds_widgets_refresh_widget_type_callback() {
	global $wpdb;

	$sport_name = $_POST['sport_name']; 

	if ( !Odds_Widget_Utils::is_sport( $sport_name ) ) {
		print 'false'; 
	} else {
		$name = str_replace( 'sport_name', 'widget_type', $_POST['item_name'] ); 
		$id = str_replace( 'sport_name', 'widget_type', $_POST['item_id'] ); 
		$widget_type_select = Odds_Widget_Utils::build_widgets_box_type_select_box( $name, $id, $sport_name ); 
		print '<label>Select widget type</label>' . $widget_type_select; 
	}
	die();
}

/** 
* Ajax callback to refresh the widget dropdown
*/
function odds_widgets_refresh_widget_callback() {
	global $wpdb;
	$sport_name = $_POST['sport_name']; 
	$widget_type = $_POST['widget_type'];  
	
	if ( !Odds_Widget_Utils::is_sport( $sport_name ) || !Odds_Widget_Utils::is_widget_type( $widget_type, $sport_name, FALSE ) ) {
		print 'false';
	} else {
		$name = str_replace( 'widget_type', 'widget', $_POST['item_name'] ); 
		$id = str_replace( 'widget_type', 'widget', $_POST['item_id'] ); 
		$widget_select = Odds_Widget_Utils::build_widgets_select_box( $name, $id, '', $sport_name, $widget_type, FALSE );
		print $widget_select; 
	}
	die(); // this is required to return a proper result
}

/** 
* Ajax callback for widget preview and admin panel
*/
function odds_widgets_preview_widget_callback() {
	$params = array(); 
	$params['sport_name'] = $_POST['sport_name']; 
	$params['widget_type'] = $_POST['widget_type'];  
	$params['widget'] = $_POST['widget'];
	$params['style_id'] = $_POST['style_id'];
	
	if ( !Odds_Widget_Utils::is_sport( $params['sport_name'] ) || !Odds_Widget_Utils::is_widget_type( $params['widget_type'], $params['sport_name'], FALSE ) ) {
		print 'false';
	} else {
		$widget_preview = Odds_Widget::preview_odds_widget( $params ); 
		print $widget_preview; 
	}
	die(); 
}

/**
* Get the current plugin version in tha database
*/
function odds_widgets_get_version() {
	$plugin_data = get_plugin_data( __FILE__ );
	$plugin_version = $plugin_data['Version'];
	return $plugin_version;
}

/* End of File */