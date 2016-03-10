<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Bowe Codes Widget Class
 *
 * Once the bowe codes is selected, the shortcode form will display.
 * It's easier to create widgets
 *
 * @since 2.1
 * @package Bowe Codes
 * @subpackage Bowe Codes Widget
 */
class Bowe_Codes_Widget extends WP_Widget {

	/**
	 * The constructor
	 */
	function __construct() {
		$widget_ops = array('classname' => 'bowe-codes-widget', 'description' => __( 'Build widgets with Bowe Codes', 'bowe-codes' ) );
		parent::__construct( false, _x( '(Bowe Codes) Widget', 'widget name', 'bowe-codes' ), $widget_ops );
		
		add_action( 'wp_ajax_widget_changed_shortcode', array( $this, 'ajax_form' ) );
	}
	
	/**
	 * Registers the widget
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'Bowe_Codes_Widget' );
	}

	/**
	 * Displays the content of the widget
	 *
	 * @param array $args 
	 * @param array $instance 
	 * @uses do_shortcode() to display the shortcode on front
	 * @return string html the content of the shortcode
	 */
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Bowe Codes widget', 'bowe-codes' ) : $instance['title'], $instance, $this->id_base);
		$shortcode = apply_filters( 'bowe_codes_widget_shortcode', $instance['shortcode'], $instance, $this->id_base );
		$shortcode_opts = apply_filters( 'bowe_codes_widget_shortcode_opts', $instance['shortcode_opts'], $instance, $this->id_base );
		
		if( empty( $shortcode ) )
			return false;
			
		$output = '['. $shortcode;

		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;
			
		if( !empty( $shortcode_opts ) && is_array( $shortcode_opts ) && count( $shortcode_opts ) > 0 ) {
			
			foreach( $shortcode_opts as $key => $val ) {
				$output .= ' '. $key.'="' .$val. '"';
			}
			
		}
		
		$output .= ']';
		
		echo do_shortcode( $output );
		
		echo $after_widget;
	}

	/**
	 * Updates the title of the widget
	 *
	 * @param array $new_instance 
	 * @param array $old_instance 
	 * @return array the instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['shortcode'] = strip_tags( $new_instance['shortcode'] );
		$instance['shortcode_opts'] = array_map( 'sanitize_text_field', $new_instance['shortcode'] );

		return $instance;
	}

	/**
	 * Displays the form in the admin of Widgets
	 *
	 * @param array $instance 
	 * @uses wp_parse_args() to merge args with defaults
	 * @uses esc_attr() to sanitize the title
	 * @uses bowe_codes_get_shortcode_settings() to get all the shortcodes settings
	 * @uses self::shortcode_form() to build the shortcode form.
	 * @return string html the form
	 */
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'shortcode' => '', 'shortcode_opts' => array() ) );
		$title = esc_attr( $instance['title'] );
		$shortcode = esc_attr( $instance['shortcode'] );		
		$shortcode_ops = $instance['shortcode_opts'];

		
		$bowe_codes_settings = bowe_codes_get_shortcode_settings();
		unset( $bowe_codes_settings['bc_restrict_gm'] );
		
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'bowe-codes'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('shortcode'); ?>"><?php _e('Shortcode')?></label>
			<select id="<?php echo $this->get_field_id('shortcode'); ?>" name="<?php echo $this->get_field_name('shortcode'); ?>" data-subform="<?php echo $this->get_field_id('shortcode-form'); ?>" class="widefat shortcode-selector">
				<option value="0"><?php _e('Choose shortcode', 'bowe-codes');?></option>

				<?php foreach( $bowe_codes_settings as $key => $val ):?>
					<option value="<?php echo $key;?>" <?php selected( $shortcode, $key );?>><?php echo $key;?></option>
				<?php endforeach;?>
			</select>
		</p>
		<div id="<?php echo $this->get_field_id('shortcode-form'); ?>">
		<?php if( !empty( $shortcode ) ):
		
			$this->shortcode_form( $shortcode, $bowe_codes_settings[$shortcode]['attributes'], $instance );
			
	 	endif;?>
		</div>
		<?php
	}
	
	/**
	 * Displays the shortcode form
	 *
	 * @param string $shortcode ( the identifier of the shortcode )
	 * @param string $attributes ( the attributes of the shortcode )
	 * @param string $instance ( the instance of the widget )
	 * @uses bowe_codes_get_shortcode_settings() to get all the shortcodes settings
	 * @uses wp_parse_args() to merge args with defaults
	 * @uses checked() to activate the radio option if needed
	 * @uses selected() to activate the select option if needed
	 * @return string html of the shortcode form
	 */
	function shortcode_form( $shortcode = '', $attributes = array(), $instance = array() ) {
		$form = $class = false;

		if( empty( $shortcode ) )
			return $form;
			
		if( empty( $settings ) || !is_array( $settings ) ) {
			$bowe_codes_settings = bowe_codes_get_shortcode_settings();
			
			$attributes = $bowe_codes_settings[$shortcode]['attributes'];
		}
		
		$shortcode_ops = !empty( $instance['shortcode_opts'] ) ? $instance['shortcode_opts'] : array() ;
		
		$defaults = array();
		
		foreach( $attributes as $default_value ) {
			$defaults[$default_value['id']] = $default_value['default'];
		}
		
		$r = wp_parse_args( $shortcode_ops, $defaults );

		foreach( $attributes as $attribute ) {

			switch( $attribute['type'] ) {
				case 'hidden' :
					break;
				case 'boolean' :
					$form .= '<p><label for="'. $shortcode . '-'. $attribute['id'].'">'.$attribute['caption'] .'</label><br/>';
					$form .= '<input type="radio" name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'-yes" value="1" '.checked( $r[$attribute['id']],1 , false).' class="checkbox"> '.__('Yes', 'bowe-codes') .'&nbsp;';
					$form .= '<input type="radio" name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'-no" value="0" '.checked( $r[$attribute['id']],0 , false).' class="checkbox"> '.__('No', 'bowe-codes');
					$form .='</p>';
					break;
				case 'select' :
					$form .= '<p><label for="'. $shortcode . '-'. $attribute['id'].'">'.$attribute['caption'] .'</label>';
					$form .= '<select name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'" class="widefat">';
						if( !empty( $attribute['choices'] ) ) {
							foreach( $attribute['choices'] as $kchoice => $vchoice ) {
								$form .= '<option value="'.$kchoice.'" '.selected( $r[$attribute['id']], $kchoice, false ).'>'.$vchoice.'</option>';
							}
						}
					$form .= '</select></p>';
					break;
				case 'int' :
					$form .= '<p><label for="'. $shortcode . '-'. $attribute['id'].'">'.$attribute['caption'] .'</label>';
					$form .= '<input type="number" min="1" step="1" name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'" value="'.$r[$attribute['id']].'" class="widefat"/>';
					$form .='</p>';
					break;
				default:
					$form .= '<p><label for="'. $shortcode . '-'. $attribute['id'].'">'.$attribute['caption'] .'</label>';
					$form .= '<input type="text" name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'" value="'.$r[$attribute['id']].'" class="widefat"/>';
					
					if( !empty( $attribute['required'] ) )
						$form .= '<strong>' . __( '* required', 'bowe-codes' ). '</strong>';
					$form .='</p>';
					break;
			}
			
		}
		
		echo $form;
		
	}
	
	/**
	 * Loads the shortcode form when a shortcode has been selected
	 *
	 * @uses sanitize_text_field() to sanitize the shortcode identifier
	 * @uses self::shortcode_form() to build the shortcode form.
	 * @return html the default shortcode form
	 */
	function ajax_form() {
		$shortcode = !empty( $_POST['shortcode_selected'] ) ? sanitize_text_field( $_POST['shortcode_selected'] ) : false ;
		
		if( !empty( $shortcode ) )
			$this->shortcode_form( $shortcode );
		
		exit();
	}

}

add_action( 'bowe_codes_widgets_init', array( 'Bowe_Codes_Widget', 'register_widget' ), 10 );


/**
 * Catches the datas once the widget is updated in order to add the shortcode attributes to the widget instance
 *
 * @param array $instance 
 * @param array $new_instance 
 * @param array $old_instance 
 * @param object $widget 
 * @return array the instance with the shortcode options
 */
function bowe_codes_widget_update_filter( $instance, $new_instance, $old_instance, $widget ) {
	if( $widget->id_base != 'bowe_codes_widget' )
		return $instance;
	
	$shortcode_opts = array();
	$shortcode = $_POST['widget-'.$widget->id_base][$widget->number]['shortcode'];
	if( !empty( $shortcode ) ) {
		
		foreach( $_POST as $key => $val ) {
			if( strpos( $key, $shortcode ) === 0 )
				$shortcode_opts[ str_replace( $shortcode.'-', '', $key ) ] = $val;
		}
		
		if( !empty( $shortcode_opts ) && is_array( $shortcode_opts ) && count( $shortcode_opts ) > 0 )
			$instance['shortcode_opts'] = $shortcode_opts;
	}
	
	return $instance;
}

add_filter( 'widget_update_callback', 'bowe_codes_widget_update_filter', 10, 4 );