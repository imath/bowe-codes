<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'Bowe_Codes_Shortcodes' ) ) :
/**
 * Main Bowe Codes Shortcodes Class
 *
 * inspired by the beautifull bbPress shortcode class
 *
 * @since Bowe Codes 2.0
 */
class Bowe_Codes_Shortcodes {

	public $codes = array();
	public $codes_settings = array();

	/**
	 * Launches the process
	 *
	 * @uses Bowe_Codes_Shortcodes::setup_globals()
	 * @uses Bowe_Codes_Shortcodes::add_shortcodes()
	 * @uses Bowe_Codes_Shortcodes::shortcode_settings()
	 */
	public function __construct() {
		$this->setup_globals();
		$this->add_shortcodes();
		$this->shortcode_settings();
	}

	/**
	 * Shortcode globals
	 *
	 * @uses bp_is_active() to check for active BuddyPress component before registering a shortcode
	 * @uses bp_get_root_blog_id() to get the id of the blogs where BuddyPress is running
	 * @uses is_multisite() to check for the network config
	 */
	private function setup_globals() {
		global $blog_id;

		// core shortcodes are based on BuddyPress core components (always loaded)
		$bowe_shortcodes = array(

			'bc_member'        => array( $this, 'display_member'     ),
			'bc_members'       => array( $this, 'display_members'    ),
			'bc_notifications' => array( $this, 'display_notifications' )

		);

		if( bp_is_active( 'friends' ) )
			$bowe_shortcodes['bc_friends'] = array( $this, 'display_friends' );

		if( bp_is_active( 'groups' ) ) {
			$bowe_shortcodes['bc_group'] = array( $this, 'display_group' );
			$bowe_shortcodes['bc_groups'] = array( $this, 'display_groups' );
			$bowe_shortcodes['bc_group_users'] = array( $this, 'display_group_users' );
			$bowe_shortcodes['bc_user_groups'] = array( $this, 'display_user_groups' );
			$bowe_shortcodes['bc_restrict_gm'] = array( $this, 'display_restricted_content' );
		}

		if( bp_is_active( 'messages' ) )
			$bowe_shortcodes['bc_messages'] = array( $this, 'display_messages' );

		if( bp_is_active( 'blogs' ) && $blog_id == bp_get_root_blog_id() && is_multisite() )
			$bowe_shortcodes['bc_blogs'] = array( $this, 'display_blogs' );

		if( bp_is_active( 'activity' ) )
			$bowe_shortcodes['bc_activity'] = array( $this, 'display_activity' );

		// Setup the shortcodes
		$this->codes = apply_filters( 'bowe_codes_shortcodes', $bowe_shortcodes );
	}

	/**
	 * Register the bowe codes shortcodes
	 *
	 * @uses add_shortcode()
	 */
	private function add_shortcodes() {
		foreach( (array) $this->codes as $code => $function ) {
			add_shortcode( $code, $function );
		}
	}

	/**
	 * The shortcode settings ! 
	 *
	 * It's a bit boaring but saves time for the Bowe Codes editor ;)
	 *
	 * @uses bp_is_active() to check for active BuddyPress component before registering a shortcode
	 * @uses bowe_codes_activity_list_types() to get all the available BuddyPress activity actions
	 * @uses is_multisite() to check for the network config
	 * @return the settings for the available shortcodes
	 */
	private function shortcode_settings() {
		
		$all_shortcodes_settings = array( 

			'bc_member' => array( 
				'attributes' => array( 
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'name', 'type' => 'string', 'default' => '', 'required' => true, 'caption' => __( 'Login of the user', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_member', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Displays a specific member.','bowe-codes' )
			),
			'bc_members' => array( 
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 10, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'type', 'type' => 'select', 'default' => 'active', 'required' => false, 'caption' => __( 'Order type', 'bowe-codes' ), 'choices' => array( 
							'active' => __( 'Last Active', 'bowe-codes' ), 
							'newest' => __( 'Newest Registered', 'bowe-codes' ),
							'random' => __( 'Random order', 'bowe-codes' ),
							'popular' => __( 'By popularity', 'bowe-codes' )
						)
					),
					array( 'id' => 'featured', 'type' => 'string', 'default' => '', 'required' => false, 'caption' => __( 'Member Login or comma separated list of member logins to stick at the top', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_members', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Displays members with or without avatars.', 'bowe-codes' )
			),
			'bc_notifications' => array( 
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 5, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_notifications', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Displays the latest notifications from the currently logged in user.', 'bowe-codes' )
			),
			'bc_friends' => array(
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 10, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'type', 'type' => 'select', 'default' => 'newest', 'required' => false, 'caption' => __( 'Order type', 'bowe-codes' ), 'choices' => array( 
							'active' => __( 'Last Active', 'bowe-codes' ), 
							'newest' => __( 'Newest Registered', 'bowe-codes' ),
							'random' => __( 'Random order', 'bowe-codes' ),
							'popular' => __( 'By popularity', 'bowe-codes' )
						)
					),
					array( 'id' => 'dynamic', 'type' => 'boolean', 'default' => 0, 'required' => false, 'caption' => __( 'Dynamic', 'bowe-codes' ) ),
					array( 'id' => 'friends', 'type' => 'hidden', 'default' => 1, 'required' => false ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_friends', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Displays the friends of the current logged in user, or the friends of the member profile currently being viewed if the dynamic option is on.', 'bowe-codes' )
			),
			'bc_group' => array( 
				'attributes' => array(
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'slug', 'type' => 'string', 'default' => '', 'required' => true, 'caption' => __( 'Group slug', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_group', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) ),
					array( 'id' => 'desc', 'type' => 'boolean', 'default' => true, 'required' => false, 'caption' => __( 'Display group description', 'bowe-codes' ) )
				),
				'description' => __( 'Displays a specific group.', 'bowe-codes' )
			),
			'bc_groups' => array( 
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 10, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'type', 'type' => 'select', 'default' => 'popular', 'required' => false, 'caption' => __( 'Order type', 'bowe-codes' ), 'choices' => array( 
							'active' => __( 'Last Active', 'bowe-codes' ), 
							'newest' => __( 'Newest Created', 'bowe-codes' ),
							'random' => __( 'Random order', 'bowe-codes' ),
							'popular' => __( 'By popularity', 'bowe-codes' ),
							'alphabetical' => __( 'Alphabetical', 'bowe-codes' )
						)
					),
					array( 'id' => 'featured', 'type' => 'string', 'default' => '', 'required' => false, 'caption' => __( 'Group slug or comma  separated list of group slugs to stick at the top', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_groups', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Displays groups with or without avatars.', 'bowe-codes' )
			),
			'bc_group_users' => array( 
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 10, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'slug', 'type' => 'string', 'default' => '', 'required' => true, 'caption' => __( 'Group slug', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'group_users', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Displays users for a given group.', 'bowe-codes' )
			),
			'bc_user_groups' => array( 
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 10, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'type', 'type' => 'select', 'default' => 'popular', 'required' => false, 'caption' => __( 'Order type', 'bowe-codes' ), 'choices' => array( 
							'active' => __( 'Last Active', 'bowe-codes' ), 
							'newest' => __( 'Newest Created', 'bowe-codes' ),
							'random' => __( 'Random order', 'bowe-codes' ),
							'popular' => __( 'By popularity', 'bowe-codes' ),
							'alphabetical' => __( 'Alphabetical', 'bowe-codes' )
						)
					),
					array( 'id' => 'dynamic', 'type' => 'boolean', 'default' => 0, 'required' => false, 'caption' => __( 'Dynamic', 'bowe-codes' ) ),
					array( 'id' => 'user_groups', 'type' => 'hidden', 'default' => 1, 'required' => false ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'user_groups', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Displays the groups of the currently logged in user or the groups of the member profile currently being viewed if dynamic option is on.', 'bowe-codes' )
			),
			'bc_restrict_gm' => array( 
				'attributes' => array(
					array( 'id' => 'group_id', 'type' => 'string', 'default' => '', 'required' => true, 'caption' => __( 'Group id or slug', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_restrict_message', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Restrict post content to group members', 'bowe-codes' )
			),
			'bc_messages' => array( 
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 5, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'subject', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Display subject', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 30, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'excerpt', 'type' => 'int', 'default' => 75, 'required' => false, 'caption' => __( 'Excerpt length', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_messages', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Displays the latest messages from the currently logged in user.', 'bowe-codes' )
			),
			'bc_blogs' => array( 
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 5, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'type', 'type' => 'select', 'default' => 'active', 'required' => false, 'caption' => __( 'Order type', 'bowe-codes' ), 'choices' => array( 
							'active' => __( 'Last Active', 'bowe-codes' ), 
							'random' => __( 'Random order', 'bowe-codes' ),
							'alphabetical' => __( 'Alphabetical', 'bowe-codes' )
						) 
					),
					array( 'id' => 'featured', 'type' => 'string', 'default' => '', 'required' => false, 'caption' => __( 'Blog id or comma separated list of blog ids to stick at the top', 'bowe-codes' ) ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_blogs', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) ),
					array( 'id' => 'desc', 'type' => 'boolean', 'default' => true, 'required' => false, 'caption' => __( 'Display blog description', 'bowe-codes' ) )
				),
				'description' => __( 'Shows blogs from across the site.', 'bowe-codes' )
			),
			'bc_activity' => array( 
				'attributes' => array(
					array( 'id' => 'amount', 'type' => 'int', 'default' => 5, 'required' => false, 'caption' => __( 'Amount', 'bowe-codes' ) ),
					array( 'id' => 'avatar', 'type' => 'boolean', 'default' => 1, 'required' => false, 'caption' => __( 'Avatar', 'bowe-codes' ) ),
					array( 'id' => 'size', 'type' => 'int', 'default' => 50, 'required' => false, 'caption' => __( 'Size in pixels', 'bowe-codes' ) ),
					array( 'id' => 'type', 'type' => 'select', 'default' => 'activity_update', 'required' => false, 'caption' => __( 'Activity type', 'bowe-codes' ), 'choices' => bowe_codes_activity_list_types() ),
					array( 'id' => 'class', 'type' => 'string', 'default' => 'my_activity', 'required' => false, 'caption' => __( 'Css Class', 'bowe-codes' ) )
				),
				'description' => __( 'Shows the latest activities for a given type', 'bowe-codes' )
			)
		);

		if( bp_is_active( 'xprofile' ) ) {
			$all_shortcodes_settings['bc_member']['attributes'][] = array( 'id' => 'fields', 'type' => 'string', 'default' => '', 'required' => false, 'caption' => __( 'xProfile field Name (separated by a comma if more than one)', 'bowe-codes' ) );
			$all_shortcodes_settings['bc_members']['attributes'][3]['choices']['alphabetical'] = __( 'Alphabetical', 'bowe-codes' );
			$all_shortcodes_settings['bc_friends']['attributes'][3]['choices']['alphabetical'] = __( 'Alphabetical', 'bowe-codes' );
		}

		if( is_multisite() )
			$all_shortcodes_settings['bc_blogs']['attributes'][3]['choices']['newest'] = __( 'Newest Created', 'bowe-codes' );
		
		// let people add their shortcode settings..
		$all_shortcodes_settings = apply_filters( 'bowe_codes_shortcodes_settings', $all_shortcodes_settings );

		$this->codes_settings = array_intersect_assoc( $all_shortcodes_settings, $this->codes );

	}

	/**
	 * Returns the default attributes of a shortcode
	 * 
	 * @param  string $shortcode the shortcode identifier
	 * @return array  (associative) the default attributes
	 */
	private function default_attributes( $shortcode = '' ) {
		if( empty( $shortcode ) )
			return false;

		$default_atts = array();

		foreach( $this->codes_settings[$shortcode]['attributes'] as $setting ) {
			$default_atts[$setting['id']] = $setting['default'];
		}
		
		if( is_array( $default_atts ) )
			return $default_atts;
		else
			return false;
	} 

	/** Core shortcodes ******************************************************/

	public function display_member( $atts = '') {

		$bc_member = shortcode_atts( $this->default_attributes( 'bc_member' ), $atts );

		// Return content
		return bowe_codes_member_tag( $bc_member );
	}

	public function display_members( $atts = '') {

		$bc_members = shortcode_atts( $this->default_attributes( 'bc_members' ), $atts );

		// Return contents of output buffer
		return bowe_codes_members_tag( $bc_members );
	}
	
	/** Notifications ******************************************************/

	public function display_notifications( $atts = '') {

		$bc_notifications = shortcode_atts( $this->default_attributes( 'bc_notifications' ), $atts );

		// Return contents of output buffer
		return bowe_codes_notifications_tag( $bc_notifications );
	}

	/** Friends ******************************************************/
	
	public function display_friends( $atts = '' ) {

		$bc_friends = shortcode_atts( $this->default_attributes( 'bc_friends' ), $atts );

		// Return contents of output buffer
		return bowe_codes_members_tag( $bc_friends );
	}

	/** Groups ******************************************************/
	
	public function display_group( $atts = '' ) {

		$bc_group = shortcode_atts( $this->default_attributes( 'bc_group' ), $atts );

		// Return contents of output buffer
		return bowe_codes_group_tag( $bc_group );
	}

	public function display_groups( $atts = '', $content = '' ) {

		$bc_groups = shortcode_atts( $this->default_attributes( 'bc_groups' ), $atts );

		if( !empty( $content ) )
			$bc_groups['content'] = $content;

		// Return contents of output buffer
		return bowe_codes_groups_tag( $bc_groups );
	}

	public function display_group_users( $atts = '', $content = '' ) {

		$bc_group_users = shortcode_atts( $this->default_attributes( 'bc_group_users' ), $atts );

		if( !empty( $content ) )
			$bc_group_users['content'] = $content;

		// Return contents of output buffer
		return bowe_codes_group_users_tag( $bc_group_users );
	}

	public function display_user_groups( $atts = '' ) {

		$bc_user_groups = shortcode_atts( $this->default_attributes( 'bc_user_groups' ), $atts );

		// Return contents of output buffer
		return bowe_codes_groups_tag( $bc_user_groups );
	}
	
	public function display_restricted_content( $atts = '', $content = '' ) {

		$bc_restrict_gm = shortcode_atts( $this->default_attributes( 'bc_restrict_gm' ), $atts );

		if( !empty( $content ) )
			$bc_restrict_gm['content'] = $content;

		// Return contents of output buffer
		return bowe_code_hide_post_content_tag( $bc_restrict_gm );
	}

	/** Messages ******************************************************/
	
	public function display_messages( $atts = '' ) {

		$bc_messages = shortcode_atts( $this->default_attributes( 'bc_messages' ), $atts );

		// Return contents of output buffer
		return bowe_codes_messages_tag( $bc_messages );
	}

	/** Blogs ******************************************************/
	
	public function display_blogs( $atts = '' ) {

		$bc_blogs = shortcode_atts( $this->default_attributes( 'bc_blogs' ), $atts );

		// Return contents of output buffer
		return bowe_codes_blogs_tag( $bc_blogs );
	}

	/** Activities ******************************************************/
	
	public function display_activity( $atts = '' ) {

		$bc_activity = shortcode_atts( $this->default_attributes( 'bc_activity' ), $atts );

		// Return contents of output buffer
		return bowe_codes_activity_tag( $bc_activity );
	}
}
endif;