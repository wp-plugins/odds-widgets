<?php 

/**
 * Odds_Widget Class
 */
class Odds_Widget extends WP_Widget {
	
	public $name = 'Odds Widget';
	public $description = 'Display a configurable odds table widget';
	
	/* List all controllable options here along with a default value.
	The values can be distinct for each instance of the widget. */
	public $control_options = array(
		'title' => '',
		'affkey' => '', 
		'boxid' => '', 
		'sport_name' => '', 
		'widget_type' => '', 
		'widget' => '', 
		'style_id' => 1,
	);

	// The constructor.
	public function __construct() {
		$widget_options = array(
			'classname' => __CLASS__,
			'description' => $this->description,
		);
		
		parent::__construct( __CLASS__, $this->name, $widget_options, $this->control_options );
	}
	
	/**
	* Displays the widget form in the manager, used for editing its settings
	*
	* @param array $instance The settings for the particular instance of the widget
	* @return none No value is returned directly, but form elements are printed.
	*/
	public function form( $instance ) {
		$placeholders = array();
		foreach ( $this->control_options as $key => $val ) {
			$placeholders[ $key .'.id' ] = $this->get_field_id( $key );
			$placeholders[ $key .'.name' ] = $this->get_field_name( $key );
			
			if ( isset($instance[ $key ] ) ) {
				$placeholders[ $key .'.value' ] = esc_attr( $instance[ $key ] );
			} else {
				$placeholders[ $key .'.value' ] = $this->control_options[ $key ];
			}
		}
		
		// sport select
		$placeholders['sport_select'] = Odds_Widget_Utils::build_widgets_sports_select_box( $placeholders[ 'sport_name.name' ], $placeholders[ 'sport_name.id' ], $placeholders[ 'sport_name.value' ] );
		
		// widget type select
		// a sport was already selected
		$class = ''; 
		if ( $placeholders[ 'sport_name.value' ] ) {
			$class = ' vis'; 
		}
		$placeholders['widget_type_select_holder_class'] = $class;
		$placeholders['widget_type_select'] = Odds_Widget_Utils::build_widgets_box_type_select_box( $placeholders[ 'widget_type.name' ], $placeholders[ 'widget_type.id' ], $placeholders[ 'sport_name.value' ], $placeholders[ 'widget_type.value' ], FALSE );
		
		// widget select
		// a sport was already selected
		$class = ''; 
		if ( $placeholders[ 'widget_type.value' ] ) {
			$class = ' vis'; 
		}
		$placeholders['widget_select_holder_class'] = $class;
		$placeholders['widget_select'] = Odds_Widget_Utils::build_widgets_select_box( $placeholders[ 'widget.name' ], $placeholders[ 'widget.id' ], $placeholders[ 'widget.value' ], $placeholders[ 'sport_name.value' ], $placeholders[ 'widget_type.value' ], FALSE );
		
		// style select 
		$placeholders['style_select'] = Odds_Widget_Utils::build_widgets_style_select_box( $placeholders[ 'style_id.name' ], $placeholders[ 'style_id.id' ], $placeholders[ 'style_id.value' ] ); 		
		
		$tpl = file_get_contents( dirname( dirname(__FILE__) ) .'/tpls/widget_controls.tpl' );
		print Odds_Widget_Utils::parse($tpl, $placeholders);
		
	}

	public static function register_this_widget() {
		register_widget( __CLASS__ ); 
	}
	
	public static function preview_odds_widget( $params = array() ) {
		
		$vv = Odds_Widget::process_odds_widget_params( $params ); 		
		$curl_response = Odds_Widget_Utils::curl_api_request( "action=preview_save_box&opp=preview&" . http_build_query( $vv ) );
		
		if ( !$curl_response ) {
			$curl_response = 'Error building the widget preview!'; 
		}
		return $curl_response; 
	}
	
	private function _save_odds_widget( $params = array() ) {
		$vv = Odds_Widget::process_odds_widget_params( $params ); 		
		$box_params = Odds_Widget_Utils::curl_api_request( "action=preview_save_box&opp=save&" . http_build_query( $vv ) );
		return $box_params; 
	}
	
	public static function process_odds_widget_params( $params = array() ) {
		
		// initiate default params
		$default_params = array(
			'sport_name' => '', 
			'widget_type' => 0, 
			'widget' => 0,
		); 
		
		$params = array_merge( $default_params, $params ); 
		
		$v['bc'] = 0;
		$v['be'] = 0;
		$v['bi'] = 0; 
		$v['bm'] = ''; 
		$v['bs'] = ''; 
		$v['bt'] = ''; 
		$v['bw'] = ''; 
		$v['bon'] = 12; 
		$v['bst'] = 1; 
		$v['bn'] = 'A-Widget';
		
		extract( $params ); 
		if ( isset( $title ) && $title ) {
			$v['bn'] = $title; 
		}
		$v['bs'] = $sport_name; 
		$v['bt'] = $widget_type;
		$v['bst'] = $style_id;
		
		$box_type = $widget_type; 
		$box_widget = $widget;
		if ( $box_widget ) {
			
			$values_str = str_replace( $sport_name . '_', '', $box_widget );
			$values_array = explode( '_', $values_str ); 
			
			if ( $box_type == 'st' ) {
				$v['be'] = $values_array[1]; 
				$v['bm'] = $values_array[3];
			} elseif ( $box_type == 'ml' ) {
				preg_match_all( '#([a-z]{2}_)([0-9_]+[0-9]+)#im', $values_str, $values_array );  
				$v['bl'] = str_replace( 'bl_', '', $values_array[0][0] );
				$v['bm'] = str_replace( 'bm_', '', $values_array[0][1] );
			} elseif ( $box_type == 'tb' || $box_type == 'tm' ) {
				$v['bteam'] = $box_widget;
				if ( $box_type == 'tb' ) {
					$v['showm'] = 1;
					$v['showt'] = 1;
				}
			}
		}
		
		$vv['v'] = $v; 	
		return $vv; 
	}
	
	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		
		// if wrong data was submitted, reset widget data: affkey and boxid
		if ( !isset( $new_instance['sport_name'] ) || !Odds_Widget_Utils::is_sport( $new_instance['sport_name'] ) || !isset( $new_instance['widget_type'] ) || !Odds_Widget_Utils::is_widget_type( $new_instance['widget_type'], $new_instance['sport_name']) || !isset( $new_instance['widget'] ) || $new_instance['widget'] < 1 ) {
			$new_instance['affkey'] = ''; 
			$new_instance['boxid'] = 0;
		}

		$box_params = $this->_save_odds_widget( $new_instance ); 
		//print_r($box_params); 

		if( $box_params['affkey'] && $box_params['boxid'] ) {
			$new_instance['affkey'] = $box_params['affkey'];
			$new_instance['boxid'] = $box_params['boxid'];
		}
		
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		return $new_instance;
	}
	
	/**
	* Displays content to the front-end.
	*
	* @param array $args Display arguments
	* @param array $instance The settings for the particular instance of the widget
	* @return none No direct output. This should instead print output directly.
	*/
	public function widget( $args, $instance ) {
		$placeholders = array_merge( $args, $instance );
		$placeholders['unit_serve_src'] = ODDS_WIDGETS_URL . 'scripts/unit-serve.php'; 
		
		if ( !isset( $placeholders['affkey'] ) || !preg_match( '#^[a-z0-9]{32}$#', $placeholders['affkey'] ) || !isset( $placeholders['boxid'] ) || $placeholders['boxid'] < 1 ) {
			// show nothing if we don't have parameters to build the odds widget
			print ''; 
		}
		else {
			if ( 1 == get_option( 'odds_widgets_link_to_us' ) ) {
				$placeholders['link_to_us'] = '<div class="ab_ref_link" style="padding-top: 4px; text-align: center;"><a target="_blank" href="' . ODDS_WIDGETS_URL . 'rid/ue63f16f0176f286fb013e1658134425" title="Get a widget like this on my site!">Get a widget like this on my site!</a></div>'; 
			}
			else {
				$placeholders['link_to_us'] = ''; 
			}
			
			$tpl = file_get_contents( dirname( dirname( __FILE__ ) ) .'/tpls/widget.tpl' );
			print Odds_Widget_Utils::parse( $tpl, $placeholders );
		}
	}

	

} // end of class Odds_Widget 
/*EOF*/