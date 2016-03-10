<?php
/*
Plugin Name: Bowe Codes
Plugin URI: http://imathi.eu/tag/bowe-codes/
Description: adds BuddyPress specific shortcodes to display members/groups/blogs/forums
Version: 2.1
Requires at least: 3.5.1
Tested up to: 3.6
License: GNU/GPL 2
Author: imath
Author URI: http://imathi.eu/
Network: true
Text Domain: bowe-codes
Domain Path: /languages/
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BoweStrap' ) ) :
/**
 * Main Bowe Codes Class
 *
 * Register the shortcodes depending on the active BuddyPress components
 * inspired by the beautifull bbPress final class
 *
 * @since Bowe Codes 2.0
 */
final class BoweStrap {

	private $data;

	private static $instance;

	/**
	 * Main BoweStrap instance
	 *
	 * @uses BoweStrap::setup_globals() to register global vars
	 * @uses BoweStrap::includes() to include needed files
	 * @uses BoweStrap::setup_actions() to add some key actions
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BoweStrap;
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	/** Magic Methods *********************************************************/

	private function __construct() { /* Do nothing here */ }

	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'bowe-codes' ), '2.1' ); }

	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'bowe-codes' ), '2.1' ); }

	public function __isset( $key ) { return isset( $this->data[$key] ); }

	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	public function __set( $key, $value ) { $this->data[$key] = $value; }

	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }

	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/**
	 * Sets some globals
	 * 
	 * @uses plugin_basename() to get plugin name
	 * @uses plugin_dir_path() to get plugin dir path
	 * @uses plugin_dir_url() to get plugin dir url
	 * @uses trailingslashit() to add a final slash
	 */
	private function setup_globals() {

		/** Version **********************************************************/

		$this->version    = '2.1';

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes'  );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes'  );

		// Languages
		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );

		/** translation ***********************************************************/

		$this->domain       = 'bowe-codes';
		
	}

	/**
	 * Includes the needed files
	 * 
	 * @uses is_admin() to check for backend area before including admin file
	 */
	private function includes() {
		// required files..
		require( $this->includes_dir . 'functions.php'  );
		require( $this->includes_dir . 'actions.php'    );
		require( $this->includes_dir . 'shortcodes.php' );
		require( $this->includes_dir . 'widget.php' );

		if( is_admin() )
			require( $this->includes_dir . 'admin.php' );
	}

	/**
	 * Sets some key actions
	 * 
	 * @uses is_admin() to check for backend area before hooking bp_loaded
	 */
	private function setup_actions() {
		// some actions..
		add_action( 'bowe_codes_register_shortcodes', array( $this, 'register_shortcodes' ),   10 );
		add_action( 'bowe_codes_enqueue_scripts',     array( $this, 'enqueue_scripts' ), 10 );

		if( is_admin() )
			add_action( 'bp_loaded', array( $this, 'load_admin' ), 10 );
			
		// loads the languages..
		add_action( 'bp_init', array( $this, 'load_textdomain' ), 6 );
	}

	/**
	 * Register the different Bowe Codes shortcodes
	 * 
	 * @uses Bowe_Codes_Shortcodes() the main shortcode class
	 */
	public function register_shortcodes() {
		// allows people to create new shortcodes ;)
		do_action_ref_array( 'bowe_codes_ready', array( &$this ) );
		
		$this->shortcodes = new Bowe_Codes_Shortcodes();
	}

	/**
	 * Loads the css by checking first if enable and then in theme dirs
	 * 
	 * @uses get_option() to eventually abort if css is disabled by admin
	 * @uses trailingslashit() to add a final slash to url/path
	 * @uses get_stylesheet_directory() to get child theme directory
	 * @uses get_stylesheet_directory_uri() to get child theme url
	 * @uses get_template_directory() to get parent theme directory
	 * @uses get_template_directory_uri() to get parent theme url
	 * @uses wp_enqueue_style() to finally load the best css file
	 */
	public function enqueue_scripts() {
		if( 'yes' == get_option( 'bc_default_css', 'no' ) )
			return;
		
		$file = 'css/bowe-codes.css';
		
		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() ) . $file ; 
			$handle   = 'bowe-codes-child-css';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() ) . $file ;
			$handle   = 'bowe-codes-parent-css';

		// use our style
		} else {
			$location = $this->plugin_url . $file;
			$handle   = 'bowe-codes-css';
		}
		
		wp_enqueue_style(  $handle, $location, false, $this->version );
	}

	/**
	 * Loads the admin part of Bowe Codes
	 * 
	 * @uses Bowe_Codes_Admin() the main admin class
	 */
	public function load_admin() {
		$this->admin = new Bowe_Codes_Admin();
	}

	/**
	 * Loads the translation files
	 * 
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_texdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// try to get locale
		$locale = apply_filters( 'bowecodes_load_textdomain_get_locale', get_locale() );

		// if we found a locale, try to load .mo file
		if ( !empty( $locale ) ) {
			// default .mo file path
			$mofile_default = sprintf( '%s/languages/%s-%s.mo', $this->plugin_dir, $this->domain, $locale );
			// final filtered file path
			$mofile = apply_filters( 'bowecodes_textdomain_mofile', $mofile_default );
			// make sure file exists, and load it
			if ( file_exists( $mofile ) ) {
				load_textdomain( $this->domain, $mofile );
			}
		}
	}
}

/**
 * Main Bowe Codes Function
 *
 * Loads the BoweStrap class once BuddyPress is loaded 
 * First checks for multsite config and super admin settings
 *
 * @global int blog_id the current blog id
 * @uses plugin_dir_path() to build the path to the plugin
 * @uses is_multisite() to check for multisite config
 * @uses bp_get_root_blog_id() to get the root blog id where BuddyPress is running
 * @uses bp_get_option() to get the super admin setting
 * @return object the instance of BoweStrap
 */
function bowecodes() {
	global $blog_id;

	if( !defined( 'BP_VERSION' ) || version_compare( BP_VERSION, '1.7', '<' ) ) {
		require( plugin_dir_path( __FILE__ ) . 'includes/1.3.php'  );
		return false;
	}
	
	if( is_multisite() && $blog_id != bp_get_root_blog_id() && bp_get_option( 'bc_enable_network', 'yes' ) != 'yes' )
		return false;
	
	return BoweStrap::instance();
}

add_action( 'bp_include', 'bowecodes' );

endif;
