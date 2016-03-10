<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Bowe_Codes_Admin') ) :
/**
 * Main Bowe Codes Admin Class
 *
 * inspired by the beautifull bbPress & BuddyPress admin classes
 *
 * @since Bowe Codes 2.0
 */
class Bowe_Codes_Admin {

	/**
	 * Constructs the admin area
	 *
	 * @uses Bowe_Codes_Admin::includes() to include the needed file
	 * @uses Bowe_Codes_Admin::setup_actions() to reference some key actions
	 * @uses Bowe_Codes_Admin::setup_filters() to customise tinyMCE.
	 */
	public function __construct() {
		$this->includes();
		$this->setup_actions();
		$this->setup_filters();
	}

	/**
	 * Includes the needed admin functions file
	 * 
	 * @uses bowe_codes_get_plugin_includes_dir() to get the plugin's include dir
	 */
	public function includes() {
		require( bowe_codes_get_plugin_includes_dir() . 'admin-functions.php' );
	}

	/**
	 * Adds some key actions to build the admin area of the plugin
	 * 
	 * @uses is_multisite() to check for a network and eventually inject a field in BuddyPress main settings
	 */
	public function setup_actions() {
		/* 
		The bowe code editor trick :
		1/ we disable the wp admin bar by hooking init before priority 10 
		2/ we temporarly create a dashboard submenu
		3/ we remove it
		4/ we load a script for quicktags and mce plugin to load a thickbox
		to our fake admin page.
		*/
		add_action( 'init',                      array( $this, 'admin_init'           ),  9 );
		add_action( 'admin_menu',                array( $this, 'admin_menus'          ), 10 );
		add_action( 'admin_head',                array( $this, 'admin_head'           )     );
		add_action( 'admin_footer-post-new.php', array( $this, 'print_footer_scripts' )     );
		add_action( 'admin_footer-post.php',     array( $this, 'print_footer_scripts' )     );
		add_action( 'load-widgets.php',          array( $this, 'widgets_script'       ), 10 );

		// Now let's add a regular settings page to eventually play with css.
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 14 );
		
		// finally, use BuddyPress settings field to allow superadmin eventually disable bowe codes for child blogs
		if( is_multisite() )
			add_action( 'bp_register_admin_settings', array( $this, 'network_settings' ) );
	}

	/**
	 * Adds the Bowe Codes Button to WordPress Rich Editor
	 */
	public function setup_filters() {
		add_filter( 'mce_external_plugins', array( $this, 'register_tinymce_plugin'), 10, 1);
		add_filter( 'mce_buttons', array( $this, 'register_tinymce_button'), 10, 1);
	}

	/**
	 * Early hook to disable admin bar when using the Bowe Codes Editor
	 *
	 * Bowe Codes Editor 'trick' step 1
	 * 
	 * @uses is_admin() to check again we're in backend
	 */
	public function admin_init() {
		if ( is_admin() && ! empty( $_REQUEST['page'] ) && 'bowecodes-editor' == $_REQUEST['page'] )
			define( 'IFRAME_REQUEST', true );
	}

	/**
	 * Temporarly adds a submenu to dashboard
	 *
	 * Bowe Codes Editor 'trick' step 2
	 * 
	 * @uses add_dashboard_page() to reference the handling function
	 */
	public function admin_menus() {
		$hook = add_dashboard_page(  
			__('Bowe Codes Editor', 'bowe-codes'), 
			__('Bowe Codes Editor', 'bowe-codes'), 
			'manage_options', 
			'bowecodes-editor', 
			array( $this, 'bc_editor' ) );

		add_action( "load-$hook", array( $this, 'admin_load' ) );
	}

	/**
	 * Displays the Bowe Code Editor
	 *
	 * @uses screen_icon() to add the Bowe Codes icon
	 * @uses bowe_codes_admin_shortcode_selectbox() to display a select box of the available shortcodes
	 * @return string html the editor
	 */
	public function bc_editor() {
		?>
		<div class="wrap">

			<?php screen_icon( 'bowe-codes' ); ?>

			<h2><?php _e('Bowe Codes Editor', 'bowe-codes');?></h2>

			<p><?php bowe_codes_admin_shortcode_selectbox() ;?></p>

			<div id="bowe-codes-compose"></div>

			<div class="bowe-codes-action">
				<p class="submit"><a href="#" class="button-primary insertShortcode"><?php _e('Insert Shortcode', 'bowe-codes');?></a> &nbsp;<a href="#" class="button-secondary cancelShortcode"><?php _e('Cancel', 'bowe-codes');?></a></p>
			</div>

		</div>
		<?php
	}

	/**
	 * Removes the Bowe Codes editor submenu from dashboard
	 * Adds some css
	 *
	 * Bowe Codes Editor 'trick' step 3
	 * 
	 * @uses remove_submenu_page() to remove the dashboard submenu
	 * @uses bowe_codes_get_plugin_url() to get plugin's url
	 * @uses bowe_codes_get_version() to get plugin's version
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'bowecodes-editor'   );

		$icon32_url = bowe_codes_get_plugin_url() .'images/icons32.png?ver='. bowe_codes_get_version();
		?>
		<style type="text/css" media="screen">
        /*<![CDATA[*/

            /* Icon 32 */
            #icon-bowe-codes {
                    background: url('<?php echo $icon32_url; ?>');
                    background-repeat: no-repeat;
            }

            /* Icon Positions */
            #icon-bowe-codes {
                    background-position: 0px 0px;
            }

        /*]]>*/
        </style>
        <?php
	}

	/**
	 * Let's load some css and our javascript
	 * 
	 * @uses wp_enqueue_style() to add our css to the WP enqueued styles
	 * @uses bowe_codes_get_plugin_url() to get plugin's url
	 * @uses bowe_codes_get_version() to get plugin's version
	 * @uses wp_enqueue_script() to add our script to the WP enqueued scripts
	 * @uses wp_localize_script() to internationalize our messages
	 */
	public function admin_load() {
		wp_enqueue_style(  'bowe-codes-css', bowe_codes_get_plugin_url() .'css/bowe-codes.css', false, bowe_codes_get_version() );
		wp_enqueue_script( 'bowe-codes-admin-js', bowe_codes_get_plugin_url() .'js/bowe-codes-admin.js', array( 'jquery' ), bowe_codes_get_version() );
		wp_localize_script( 'bowe-codes-admin-js', 'bowe_codes_vars', array(
				'error_select'   => __( 'Please select a shorcode in the list', 'bowe-codes' ),
				'error_required' => __('is a required field', 'bowe-codes' ),
				'loader'         => bowe_codes_get_plugin_url() .'images/loading.gif',
				'loadertxt'      => __( 'Building the form, please wait...', 'bowe-codes' ),
				'restricted'     => __( 'Put your restricted content here', 'bowe-codes' )
			)
		);
		
		// if plugins needs to load their scripts in the editor.
		do_action( 'bowe_codes_editor_enqueue_scripts' );
	}

	/**
	 * Adds some javascript in admin footer when needed
	 *
	 * Bowe Codes Editor 'trick' step 4
	 * 
	 * @uses get_current_screen() to get the post type an make sure we're in a post / page
	 * @uses add_query_arg() to add an argument to the admin url
	 * @uses admin_url() to build the admin url
	 * @return string the js
	 */
	public function print_footer_scripts() {
		if ( !isset( get_current_screen()->post_type ) || !in_array( get_current_screen()->post_type, array( 'post', 'page') ) )
			return false;

		$url = add_query_arg( 'page', 'bowecodes-editor', admin_url( 'index.php' ) );
		$window_title = __( 'Build Your Bowe Code', 'bowe-codes');
		?>
		<script type="text/javascript">
		if ( typeof QTags != 'undefined' )
			QTags.addButton( 'eg_tcode', '[bc]', boweCodesLaunchEditor );
			
		function boweCodesLaunchEditor() { 
			var url = "<?php echo $url;?>";
			tb_show("<?php echo $window_title;?>", url + '&amp;TB_iframe=true');
		}
	    </script>
		<?php
	}

	/**
	 * References the bowe codes button in mce external plugins
	 * 
	 * @param  array  $plugin_array the list of mce plugins
	 * @uses get_current_screen() to get the post type an make sure we're in a post / page
	 * @uses is_admin() to make sure (again!) we're in WordPress backend..
	 * @uses bowe_codes_get_plugin_url() to get plugin's url
	 * @return array the plugin array + our plugin
	 */
	public function register_tinymce_plugin( $plugin_array = array() ) {
		if ( !isset( get_current_screen()->post_type ) || !in_array( get_current_screen()->post_type, array( 'post', 'page') ) )
			return $plugin_array;
		
		if( is_admin() )
			$plugin_array['MCEbowecodes'] = bowe_codes_get_plugin_url().'js/bowecodes/editor_plugin.js';

		return $plugin_array;
	}

	/**
	 * Refernces the bowe codes button in mce toolbar
	 * @param  string $buttons the list of mce buttons
	 * @uses get_current_screen() to get the post type an make sure we're in a post / page
	 * @uses is_admin() to make sure (again!) we're in WordPress backend..
	 * @return string the comma separated list of buttons + our button
	 */
	public function register_tinymce_button( $buttons = '' ) {
		if ( !isset( get_current_screen()->post_type ) || !in_array( get_current_screen()->post_type, array( 'post', 'page') ) )
			return $buttons;

		if( is_admin() )
			array_push( $buttons, "separator", "MCEbowecodes" );

   		return $buttons;
	}

	/**
	 * Registers a submenu to settings to let admins customize Bowe Codes settings
	 *
	 * Checks for current version of the plugin against the one in database to 
	 * eventually update it.
	 *
	 * @uses add_options_page() to add the settings page
	 * @uses bp_get_option() to get root blog option
	 * @uses bowe_codes_get_version() to get plugin's current version
	 * @uses bp_update_option() to update root blog option
	 */
	public function settings_menu() {
		add_options_page(
			__( 'Bowe Codes Settings', 'bowe-codes' ),
			__( 'Bowe Codes Settings', 'bowe-codes' ),
			'manage_options',
			'bowecodes-options',
			'bowe_codes_settings_page'
		);
		
		if( bp_get_option( 'bowe-codes-version', '' ) != bowe_codes_get_version() ) {
			
			do_action( 'bowe_codes_upgrade' );
			
			bp_update_option( 'bowe-codes-version', bowe_codes_get_version() );
		}
	}
	
	/**
	 * In the multisite case takes benefit of BuddyPress settings page
	 * in order to avoid creating one !
	 * 
	 * @uses add_settings_field to add our setting field to bp_main settings area
	 * @uses register_setting() to register it and specify the sanitization callback
	 */
	public function network_settings() {
		// Allow child blogs to use Bowe Codes
		add_settings_field( 'bc_enable_network', __( 'Allow child blogs to use Bowe Codes',   'bowe-codes' ), 'bp_admin_setting_callback_bowe_codes',   'buddypress', 'bp_main' );
		register_setting( 'buddypress', 'bc_enable_network',   'sanitize_text_field' );
	}
	
	/**
	 * Enqueues a little javascript in the widget admin for Bowe Codes Widget
	 *
	 * @uses bowe_codes_get_plugin_url() to get plugin's url
	 * @uses bowe_codes_get_version() to get plugin's version
	 * @uses wp_enqueue_script() to add our script to the WP enqueued scripts
	 * @uses wp_localize_script() to internationalize our messages
	 */
	public function widgets_script() {
		wp_enqueue_script( 'bowe-codes-widget-js', bowe_codes_get_plugin_url() .'js/bowe-codes-widget.js', array( 'jquery' ), bowe_codes_get_version() );
		wp_localize_script( 'bowe-codes-widget-js', 'bowe_codes_widgets', array(
				'loader'         => bowe_codes_get_plugin_url() .'images/loading.gif',
				'loadertxt'      => __( 'Building the form, please wait...', 'bowe-codes' )
			)
		);
		
		// if plugins needs to load their scripts.
		do_action( 'bowe_codes_widget_enqueue_scripts' );
	}
	
}

endif;
