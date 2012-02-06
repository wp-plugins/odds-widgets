<?php
/**
* Odds_Widget_Utils
*
* Helper functions that assist the Odds_Widget class
*/
class Odds_Widget_Utils {
	
	/**
	* Build a select field based on params
	* @param array $params array of data to build the select
	*/
	public static function build_select_box( $params = array() ) {
		extract( $params ); 
		
		$sel = '<select name="' . $name . '" ' . $extra . '>';
		if(!empty($first_option)) {
			$sel .= '<option value="' . key($first_option) . '">' . current($first_option) . '</option>';
		}
		if(is_array($options) && !empty($options)) {
			foreach($options as $k => $v) {
				if(is_array($v)) {
					$sel .= '<optgroup label="' . $k . '">';	
					foreach($v as $kk => $vv) {
						$s = in_array($kk, $selected) ? ' selected="selected"' : '';
						$sel .= '<option value="' . $kk . '"' . $s . '>' . $vv . '</option>';
					}
					$sel .= '</optgroup>';	
				}
				else {
					$s = in_array($k, $selected) ? ' selected="selected"' : '';
					$sel .= '<option value="' . $k . '"' . $s . '>' . $v . '</option>';
				}
			}
		}
		$sel .= '</select>';
		return $sel;
	}
	
	/**
	* Build the sport select dropdown
	*/
	public static function build_widgets_sports_select_box( $name, $id, $sport_name = NULL ) {
		$sports = Odds_Widget_Utils::get_sports( TRUE );
		
		$params = array(); 
		$params['name'] = $name; 
		$params['options'] = $sports; 
		$params['selected'] = array($sport_name => $sport_name); 
		$params['first_option'] = array('Select sport'); 
		$params['extra'] = 'id="' . $id . '" class="odds_widget_sport_select"'; 
		
		$sports_select = Odds_Widget_Utils::build_select_box( $params );
		return $sports_select;
	}
	
	/**
	* Build the widget type select dropdown
	*/
	public static function build_widgets_box_type_select_box($name, $id, $sport_name = NULL, $box_type = '', $custom_widget = FALSE) {
		$box_types = array(); 
		if(!Odds_Widget_Utils::is_sport($sport_name)) {
			return ''; 
		}
		$box_types = Odds_Widget_Utils::get_widgets_box_types_by_sport($sport_name, $custom_widget);
		
		$params = array(); 
		$params['name'] = $name; 
		$params['options'] = $box_types; 
		$params['selected'] = array($box_type => $box_type); 
		$params['first_option'] = array('Select Widget Type'); 
		$params['extra'] = 'id="' . $id . '" class="odds_widget_type_select"'; 
		
		$box_types_select = Odds_Widget_Utils::build_select_box( $params );
		return $box_types_select; 
	}
	
	/**
	* Build the widget select dropdown
	*/
	public static function build_widgets_select_box($name, $id, $widget = '', $sport_name = NULL, $widget_type = '', $custom_widget = FALSE) {
		$widgets = array(); 
		
		if(!Odds_Widget_Utils::is_sport($sport_name)) {
			return ''; 
		}
		
		$widgets = Odds_Widget_Utils::curl_api_request( "action=get_default_widgets_list&sport_name=" . $sport_name . "&bt=" . $widget_type ); 
		
		if ( !$widgets ) {
			return 'Error building the widget dropdown!'; 
		}
		
		$params = array(); 
		$params['name'] = $name; 
		$params['options'] = $widgets; 
		$params['selected'] = array($widget => $widget); 
		$params['first_option'] = array('Select Widget'); 
		$params['extra'] = 'id="' . $id . '" class="odds_widget_select"'; 
		
		$widget_select = Odds_Widget_Utils::build_select_box( $params );
		return $widget_select; 
	}
	
	/**
	* Build the style select dropdown
	*/
	public static function build_widgets_style_select_box($name, $id, $style_id = NULL) {
		$styles = Odds_Widget_Utils::get_styles();
		
		$params = array(); 
		$params['name'] = $name; 
		$params['options'] = $styles; 
		$params['selected'] = array($style_id => $style_id); 
		$params['first_option'] = array(); 
		$params['extra'] = 'id="' . $id . '" class="odds_widget_style_select"'; 
		
		$styles_select = Odds_Widget_Utils::build_select_box( $params );
		return $styles_select;
	}
	
	/**
	* Send a curl request to Odds Widgets API with the specified $data as request parameters
	*/
	public static function curl_api_request( $data = '' ) { 
		$ch = curl_init( ODDS_WIDGETS_API_URL );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );  // RETURN THE CONTENTS OF THE CALL
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_exec( $ch );
		$curl_response = curl_multi_getcontent( $ch ); 
		if ( !(bool) $curl_response ) {
			$curl_response = FALSE; 
		} else {
			$curl_response = json_decode($curl_response, TRUE );
		}
		curl_close ($ch); 
		return $curl_response; 
	}

	public static function get_widgets_box_types_by_sport($sport_name = NULL, $custom_widget = FALSE) {
		$types = array(); 
		
		$sports = Odds_Widget_Utils::get_sports(); 
		if(isset($sports[$sport_name]['market_categories'])) {
			$market_categories = $sports[$sport_name]['market_categories'];
		}
		else {
			$market_categories = array(); 
		}
		
		// sports for team box
		$sports_tb = Odds_Widget_Utils::get_teams_sports();

		$box_types = Odds_Widget_Utils::get_widgets_types(NULL, FALSE); 
		
		foreach($box_types as $type_id => $type) {
			if($type_id == 'sm') {
				continue; 
			}
			
			if($custom_widget && in_array($type_id, array('tm', 'dr', 'tb'))) {
				continue; 
			}
			
			if($type_id == 'dr') {
				if($sport_name == 'horseracing') {
					$types[$type_id] = $type['name'];
				}
			}
			elseif($type_id == 'tb') {
				if(isset($sports_tb[$sport_name])) {
					$types[$type_id] = $type['name'];
				}
			}
			elseif(array_intersect($market_categories, $type['market_categories'])) {
				$types[$type_id] = $type['name']; 
			}
		}
		return $types; 
	}

	public static function update_email_subscription( $emails = array(), $subscribe = TRUE ) {
		if( !empty($emails) ) {
			
		}
		return TRUE; 
	}
	
	public static function get_current_url() {
		$page_url = 'http';
		if ( $_SERVER["HTTPS"] == "on" ) {
			$page_url .= "s";
		}
		$page_url .= "://";
		if ( $_SERVER["SERVER_PORT"] != "80" ) {
			$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $page_url;
	}
	
	public static function get_odds_widgets_config_data() {
		static $config_data; 
		if ( !isset( $config_data ) ) {
			$config_data = get_option( 'odds_widgets_config_data' );
			
			// if config_data was not yet set or it has expired
			if ( !$config_data || !isset ( $config_data['expire'] ) || $config_data['expire'] < time() ) {
				// initialize config_data
				$config_data = array(); 
				// set config_data to expire in 12 hours
				$config_data['expire'] = time() + 43200;
				$config_data['sports'] = array(); 
				$config_data['widget_types'] = array(); 
				$config_data['styles'] = array(); 
				$config_data['matches_sports'] = array();
				$config_data['teams_sports'] = array();
				
				// get config_data from odds widgets api
				$curl_config_data = Odds_Widget_Utils::curl_api_request( "action=get_odds_widgets_config_data" );  
				
				// merge with default data
				$config_data = array_merge( $config_data, $curl_config_data ); 
				
				// update config_data
				update_option( 'odds_widgets_config_data', $config_data ); 
			}
		}
		return $config_data; 
	}
	
	/**
	* Get sports list from the config_data array
	*/
	public static function get_sports( $simple = FALSE, $key_by_name = TRUE ) {
		$config_data = Odds_Widget_Utils::get_odds_widgets_config_data(); 
		$sports = $config_data['sports'];

		if($simple) {
			$simple_sports = array(); 
			foreach($sports as $k => $v) {
				if($key_by_name) {
					$key = $k;
				}
				else {
					$key = $v['sport_id']; 
				}
				$simple_sports[$key] = $v['display_name'];
			}
			return $simple_sports; 
		}
		return $sports;
	}
	
	/**
	* Get the list of available widget types from the config_data array
	* If type is specified, only that type will be returned or false if it doesn't exist
	*/
	public static function get_widgets_types($type = NULL, $simple = TRUE) {
		$config_data = Odds_Widget_Utils::get_odds_widgets_config_data(); 
		$box_types = $config_data['widget_types'];
		
		if($type && isset($box_types[$type])) {
			if($simple) {
				return $box_types[$type]['name']; 
			}
			return $box_types[$type]; 
		}
		if($simple) {
			$simple_types = array(); 
			foreach($box_types as $type_id => $type) {
				$simple_types[$type_id] = $type['name']; 
			}
			return $simple_types; 
		}
		return $box_types; 
	}
	
	/**
	* Get the list of available style from the config_data array
	*/
	public static function get_styles() {
		$config_data = Odds_Widget_Utils::get_odds_widgets_config_data(); 
		$styles = $config_data['styles'];
		return $styles; 
	}
	
	/**
	* Get the list of matches sports from the config_data array
	*/
	public static function get_matches_sports() {
		$config_data = Odds_Widget_Utils::get_odds_widgets_config_data(); 
		$matches_sports = $config_data['matches_sports'];
		return $matches_sports;
	}
	
	/**
	* Get the list of teams sports from the config_data array
	*/
	public static function get_teams_sports() {
		$config_data = Odds_Widget_Utils::get_odds_widgets_config_data(); 
		$teams_sports = $config_data['teams_sports'];
		return $teams_sports;
	}
	
	public static function is_sport($sport_name = NULL) {
		$sports = Odds_Widget_Utils::get_sports(); 
		if(!$sport_name || !isset($sports[$sport_name])) {
			return FALSE; 
		}
		return TRUE; 
	}
	
	public static function is_widget_type($box_type = NULL, $sport_name = NULL, $custom_widget = FALSE) {
		$box_types = Odds_Widget_Utils::get_widgets_box_types_by_sport($sport_name, $custom_widget);
		if(!$box_type || !isset($box_types[$box_type])) {
			return FALSE; 
		}
		return TRUE; 
	}
	
	/**
	* Parsing function for basic templating.
	*/
	public static function parse($tpl, $hash) {
		$search = array(); 
		foreach ($hash as $key => $value) {
			$search[$key] = '[['.$key.']]'; 
		}
		if ( !empty( $search ) ) {
			$tpl = str_replace( $search, $hash, $tpl);
		}
		return $tpl;
	}
	
	public static function validate_email( $email = '' ) {
		if(preg_match('#^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$#i', $email)) {
			return TRUE;
		}
		return FALSE;
	}
} // end of class Odds_Widget_Utils 
/*EOF*/