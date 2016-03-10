<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Returns the settings of the available shortcodes
 *
 * @uses bowecodes() the main Bowe Codes function
 * @return array the code settings
 */
function bowe_codes_get_shortcode_settings() {
	return bowecodes()->shortcodes->codes_settings;
}

/**
 * Builds a form for the needed shortcode thanks to the attributes settings
 * 
 * @param  string $shortcode  the shortcode identifier
 * @param  array $attributes the attributes settings for this shortcode
 * @return string the form
 */
function bowe_codes_admin_shortode_build_form( $shortcode, $attributes ) {
	$form = $class = false;

	if( empty( $shortcode ) || empty( $attributes ) )
		return $form;

	foreach( $attributes as $attribute ) {

		switch( $attribute['type'] ) {
			case 'hidden' :
				break;
			case 'boolean' :
				$form .= '<tr valign="top"><th scope="row"><label for="'. $shortcode . '-'. $attribute['id'].'" data-defaultvalue="'.$attribute['default'].'">'.$attribute['caption'] .'</label></th>';
				$form .= '<td><input type="radio" name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'-yes" value="1" '.checked( $attribute['default'],1 , false).'> '.__('Yes', 'bowe-codes') .'&nbsp;';
				$form .= '<input type="radio" name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'-no" value="0" '.checked( $attribute['default'],0 , false).'> '.__('No', 'bowe-codes').'</td>';
				$form .='</tr>';
				break;
			case 'select' :
				$form .= '<tr valign="top"><th scope="row"><label for="'. $shortcode . '-'. $attribute['id'].'" data-defaultvalue="'.$attribute['default'].'">'.$attribute['caption'] .'</label></th>';
				$form .= '<td><select name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'">';
					if( !empty( $attribute['choices'] ) ) {
						foreach( $attribute['choices'] as $kchoice => $vchoice ) {
							$form .= '<option value="'.$kchoice.'" '.selected( $attribute['default'], $kchoice, false ).'>'.$vchoice.'</option>';
						}
					}
				$form .= '</select></td>';
				break;
			case 'int' :
				$form .= '<tr valign="top"><th scope="row"><label for="'. $shortcode . '-'. $attribute['id'].'" data-defaultvalue="'.$attribute['default'].'">'.$attribute['caption'] .'</label></th>';
				$form .= '<td><input type="number" min="1" step="1" name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'" value="'.$attribute['default'].'"/></td>';
				$form .='</tr>';
				break;
			default:
				$form .= '<tr valign="top"><th scope="row"><label for="'. $shortcode . '-'. $attribute['id'].'" data-defaultvalue="'.$attribute['default'].'">'.$attribute['caption'] .'</label></th>';
				
				$class = !empty( $attribute['required'] ) ? ' class="required"' : '';
				$form .= '<td><input type="text" name="'. $shortcode . '-'. $attribute['id'].'" id="'. $shortcode . '-'. $attribute['id'].'" value="'.$attribute['default'].'" '.$class.'/></td>';
				$form .='</tr>';
				break;
		}
		
	}

	$form = apply_filters( 'bowe_codes_admin_shortode_build_form', $form, $shortcode, $attributes );

	$form = '<table class="form-table"><tbody>' . $form . '</tbody></table>';

	return $form;
}

/**
 * Intercepts ajax action to display the shortcode form
 *
 * @uses bowe_codes_get_shortcode_settings() to get all shortcodes settings
 * @uses bowe_codes_admin_shortode_build_form() to build the form
 * @return string output a description of the shortcode and the form
 */
function bowe_codes_admin_shortcode_get_shortcode_form() {
	$bowe_codes_settings = bowe_codes_get_shortcode_settings();

	$requested_code = !empty( $_POST['requested_code'] ) ? $_POST['requested_code'] : false;
	$output = false;

	if( !empty( $requested_code ) ) {
		$output .= '<p class="description">' . $bowe_codes_settings[$requested_code]['description'] .'</p>';
		$output .= bowe_codes_admin_shortode_build_form( $requested_code, $bowe_codes_settings[$requested_code]['attributes'] );
		echo $output;
	}
	die();
}

add_action( 'wp_ajax_bowecodes_get_shortcode_form', 'bowe_codes_admin_shortcode_get_shortcode_form' );

/**
 * Lists the available shortcodes in a select box
 *
 * On change, it will call the ajax action to load
 * the selected shortcode form.
 *
 * @uses bowe_codes_get_shortcode_settings() to get all shortcodes settings
 * @return string html of the select box
 */
function bowe_codes_admin_shortcode_selectbox() {
	$bowe_codes_settings = bowe_codes_get_shortcode_settings();
	?>
	<select id="available-bowe-codes">
		<option value="0"><?php _e('Choose the shortcode you want to use', 'bowe-codes');?></option>

		<?php foreach( $bowe_codes_settings as $key => $val ):?>
			<option value="<?php echo $key;?>"><?php echo $key;?></option>
		<?php endforeach;?>
	</select>
	<?php
}

/**
 * Displays the blog/child blog settings page
 *
 * @uses check_admin_referer() for security reasons
 * @uses update_option() to store the settings in the current blog
 * @uses sanitize_text_field() to sanitize the option to save
 * @uses bowe_codes_get_plugin_dir() to get plugin's dir
 * @uses get_option() to get saved option
 * @uses screen_icon() to display the Bowe Codes icon
 * @uses checked() to eventually activate the radio button
 * @uses wp_nonce_field() the security token
 * @return string html the settings page
 */
function bowe_codes_settings_page() {
	$updated = false;
	
	if( !empty( $_POST['_bowec_save'] ) ) {

		check_admin_referer( 'bowe-codes-admin' );
		
		if( !empty( $_POST['bc_default_css'] ) )
			update_option( 'bc_default_css', sanitize_text_field( $_POST['bc_default_css'] ) );
			
		$updated = true;
		
	}
	$defaultcss = file_get_contents( bowe_codes_get_plugin_dir() .'css/bowe-codes.css' );
	$viewable = explode( '/* administration */', $defaultcss );
	$css_enable = get_option( 'bc_default_css', 'no' );
	?>
	<div class="wrap">
		
		<?php screen_icon( 'bowe-codes' ); ?>

		<h2><?php _e( 'Bowe Codes Settings', 'bowe-codes' );?></h2>

		<form action="" method="post">

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Default css file','bowe-codes' );?>
							<p class="description"><?php _e( 'By creating a file named bowe-codes.css in the css folder of your active theme, it will be loaded instead of this one', 'bowe-codes');?></p>
						</th>
						<td>
							<div style="overflow:auto; height:300px;;width:500px;border:solid 1px #CCC;border-radius:3px;background-color:#f1f1f1;">
								<?php echo nl2br( htmlentities( $viewable[0] ) );?>
							</div>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Or you can disable default css..','bowe-codes' );?></th>
						<td>
							<input type="radio" name="bc_default_css" value="yes" <?php checked( $css_enable, 'yes' );?>> <?php _e( 'Yes', 'bowe-codes' );?>&nbsp;
							<input type="radio" name="bc_default_css" value="no" <?php checked( $css_enable, 'no' );?>> <?php _e( 'No', 'bowe-codes' );?>&nbsp;
						</td>
					</tr>
				</tbody>
			</table>

			<div class="submit">
				<?php wp_nonce_field( 'bowe-codes-admin' );?>
				<input type="submit" class="button-primary" value="<?php _e( 'Save Settings', 'bowe-codes');?>" name="_bowec_save">
			</div>
		</form>
	</div>
	<?php
}

/**
 * Displays a new setting field in BuddyPress main settings area
 *
 * @uses bp_get_option() to get the network settings of bowe codes
 * @uses checked() to eventually activate the radio button
 * @return string html the fields.
 */
function bp_admin_setting_callback_bowe_codes() {
	$bc_enable_network = bp_get_option( 'bc_enable_network', 'yes' );
	?>

		<input id="bc-enable-network-yes" name="bc_enable_network" type="radio" value="yes" <?php checked( $bc_enable_network, 'yes' ); ?> />
		<label for="bc-enable-network-yes"><?php _e( 'Yes', 'bowe-codes' ); ?></label>
		<input id="bc-enable-network-no" name="bc_enable_network" type="radio" value="no" <?php checked( $bc_enable_network, 'no' ); ?> />
		<label for="bc-enable-network-no"><?php _e( 'No', 'bowe-codes' ); ?></label>

	<?php
}