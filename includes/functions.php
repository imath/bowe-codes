<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Echoes version of the plugin
 *
 * @uses bowe_codes_get_version()
 * @return int version
 */
function bowe_codes_version() {
	echo bowe_codes_get_version();
}
	
	/**
	 * Returns version of the plugin
	 *
	 * @uses  bowecodes()
	 * @return int version
	 */
	function bowe_codes_get_version() {
		return bowecodes()->version;
	}


/**
 * Echoes path to the plugin dir
 *
 * @uses bowe_codes_get_plugin_dir()
 * @return string plugin dir
 */
function bowe_codes_plugin_dir() {
	echo bowe_codes_get_plugin_dir();
}

	/**
	 * Returns path to the plugin dir
	 *
	 * @uses  bowecodes()
	 * @return string plugin dir
	 */
	function bowe_codes_get_plugin_dir() {
		return bowecodes()->plugin_dir;
	}


/**
 * Echoes url of the plugin
 *
 * @uses bowe_codes_get_plugin_url()
 * @return string plugin url
 */
function bowe_codes_plugin_url() {
	echo bowe_codes_get_plugin_url();
}

	/**
	 * Returns url of the plugin
	 *
	 * @uses  bowecodes()
	 * @return string plugin url
	 */
	function bowe_codes_get_plugin_url() {
		return bowecodes()->plugin_url;
	}

/**
 * Echoes url of the plugin includes
 *
 * @uses bowe_codes_get_plugin_includes_url()
 * @return string plugin includes url
 */
function bowe_codes_plugin_includes_url() {
	echo bowe_codes_get_plugin_includes_url();
}

	/**
	 * Returns url of the plugin includes
	 *
	 * @uses  bowecodes()
	 * @return string plugin includes url
	 */
	function bowe_codes_get_plugin_includes_url() {
		return bowecodes()->includes_url;
	}

/**
 * Echoes dir of the plugin includes
 *
 * @uses bowe_codes_get_plugin_includes_dir()
 * @return string plugin includes dir
 */
function bowe_codes_plugin_includes_dir() {
	echo bowe_codes_get_plugin_includes_dir();
}

	/**
	 * Returns dir of the plugin includes
	 *
	 * @uses  bowecodes()
	 * @return string plugin includes dir
	 */
	function bowe_codes_get_plugin_includes_dir() {
		return bowecodes()->includes_dir;
	}


/** Core shortcodes ******************************************************/

/**
 * Trick to return exluded profile fields based on included ones
 *
 * As BuddyPress only allows an exclude args :(
 * 
 * @param  array $include the list of profile field to include
 * @global bp the main BuddyPress global
 * @global wpdb WordPress Database class
 * @uses $wpdb::get_col() to query the profile table
 * @return string comma separated list of fields to exclude
 */
function bowe_codes_include_fields( $include = false ) {
	global $bp, $wpdb;
	
	if( empty( $include ) )
		return false;
		
	$include = implode( "','", $include );
		
	$exclude = $wpdb->get_col( "SELECT id FROM {$bp->profile->table_name_fields} WHERE parent_id = 0 AND name NOT IN('".$include."')" );
	
	if( is_array( $exclude ) && count( $exclude ) >= 1 )
		return implode( ',', $exclude);
	else
		return false;
	
}

/**
 * Builds the html part for a single member
 *
 * Since 2.0, the use of the xprofile loop makes sure
 * we respect the visibility choices of user/admin before
 * displaying the xprofile.
 * 
 * @param  int user_id
 * @param  boolean avatar
 * @param  string size
 * @param  string fields
 * @uses bp_core_get_user_domain() to get member's home page
 * @uses bp_core_fetch_avatar() to get member's avatar
 * @uses bp_core_get_user_displayname() to get member's display name
 * @uses bp_is_active() to check if xprofile component is activated
 * @uses bowe_codes_include_fields() to trick exclude parameter of xprofile loop and make it an include one
 * @uses the xprofile loop
 * @return string html for a single member
 */
function bowe_codes_html_member( $user_id, $avatar=false, $size="50", $fields='' ){
	$user_home = bp_core_get_user_domain( $user_id );
	$member_html = "";
	
	//avatar
	if($avatar) $member_html = '<li><div class="bc_avatar"><a href="'.$user_home.'">'.bp_core_fetch_avatar( 'item_id='. $user_id .'&type=full&width='. $size .'&height='. $size ) . '</a></div>';
	else $member_html .= "<li>";
	
	$member_html .= '<div class="user-infos">';
	
	$member_html .= apply_filters( 'bowe_codes_html_member_before_title', '', $user_id );
	
	$member_html .= '<h4><a href="'.$user_home.'">'.bp_core_get_user_displayname( $user_id ).'</a></h4>';
	
	$member_html .= apply_filters( 'bowe_codes_html_member_after_title', '', $user_id );
	
	//xprofile_fields
	if( $fields != '' && bp_is_active( 'xprofile' ) ){

		/* temporarly remove unwanted filters */
		remove_filter( 'bp_get_the_profile_field_value', 'wpautop' );
		remove_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 9, 2 );

		$parse_fields = explode(',',$fields);
		$user_xprofile =  bowe_codes_include_fields( $parse_fields );
		
		/* Using the xprofile's loop makes sure privacy is respected */
		if( bp_has_profile( array( 'user_id' => $user_id, 'exclude_fields' => $user_xprofile ) ) ):
			
			while ( bp_profile_groups() ) : bp_the_profile_group();
			
				while ( bp_profile_fields() ) : bp_the_profile_field();
				
					$member_html .= '<p><span class="xprofile_thead">'.bp_get_the_profile_field_name().'</span><span class="xprofile_content">'.bp_get_the_profile_field_value().'</span></p>';
				
				endwhile;
				
			endwhile;
			
		endif;

		/* restoring unwanted filters */
		add_filter( 'bp_get_the_profile_field_value', 'wpautop' );
		add_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 9, 2 );
	}

	// if some user want to add some html..
	$member_html .= apply_filters( 'bowe_codes_html_member', '', $user_id );

	$member_html .= '</div></li>';
	
	return $member_html;
}

/**
 * Handling function for bc_member shortcode
 * 
 * @param  array $args the shortcode arguments
 * @uses bp_core_get_userid() to get user's id from his login
 * @uses bowe_codes_html_member() to build the user's entry
 * @return string the html of the member
 */
function bowe_codes_member_tag( $args = '' ) {
	if( !is_array( $args ) )
		return false;

	extract( $args, EXTR_SKIP );
	
	if( !isset( $fields ) )
		$fields = false;

	$user_id = bp_core_get_userid( $name );
	
	if( empty( $user_id ) ) 
		return false;
	
	$html_member_box = '<div class="'.$class.'">';
	$html_member_box .= '<ul class="'.$class.'-ul">'.bowe_codes_html_member( $user_id, $avatar, $size, $fields ).'</ul>';
	$html_member_box .= '</div>';

	return $html_member_box;
}

/**
 * Gets user_ids form users logins
 * 
 * @param  string $login_list comma separated list of users logins
 * @uses bp_core_get_userid() to get user's id from his login
 * @return array the list of user ids
 */
function bowe_codes_get_members_by_login( $login_list = '' ) {
	if( empty( $login_list ) )
		return false;

	$user_ids = array();

	foreach( (array) $login_list as $login ) {
		$user_ids[] = bp_core_get_userid( $login ); 
	}

	if( empty( $user_ids ) || count( $user_ids ) < 1 )
		return false;

	return $user_ids;
}

/**
 * Handling function for bc_members and bc_friends
 *
 * Since 2.0, we exclusively use the members loop
 * In case of featured members, the include argument of the first
 * loop becomes the exclude one of the second loop.
 * 
 * @param  array $args the shortcode arguments
 * @uses bowe_codes_get_members_by_login() to get user ids from user logins
 * @uses the members loop
 * @uses bp_displayed_user_id() to get displayed user id (in case of bc_friends)
 * @uses bp_loggedin_user_id() to get current user id (in case of bc_friends)
 * @return string html part for the list of users
 */
function bowe_codes_members_tag( $args = '' ){
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );
	
	$html_members_box = '<div class="'.$class.'">';
	$exclude_members_from_loop = array();
	
	if( !empty( $featured ) ) {
		$featured_list = explode( ',', $featured );

		$exclude_members_from_loop = bowe_codes_get_members_by_login( $featured_list );

		if( !empty( $exclude_members_from_loop ) && is_array( $exclude_members_from_loop ) && count( $exclude_members_from_loop ) > 0  ) {

			$featured_arg = apply_filters( 'bowe_codes_members_tag_featured_args', array( 'include' => $exclude_members_from_loop ), $args );

			if( bp_has_members( $featured_arg ) ){

				$html_members_box .= '<div class="featured"><ul class="'.$class.'-ul">';

				while ( bp_members() ){

					bp_the_member();
					
					$html_members_box .= '<li>';
						
					if( !empty( $avatar ) )
							$html_members_box .= '<div class="bc_avatar"><a href="'.bp_get_member_permalink().'">'.bp_get_member_avatar('type=full&width='.$size.'&height='.$size) . '</a></div>';
						
					$html_members_box .= '<div class="user-infos">';
					$html_members_box .= '<h4><a href="'.bp_get_member_permalink().'">'.bp_get_member_name().'</a></h4>';

					// if some want to add some html
					$html_members_box .= apply_filters( 'bowe_codes_members_tag_featured', '' );
					$html_members_box .= '</div></li>';
					
				}
				
				$html_members_box .= apply_filters( 'bowe_codes_members_tag_featured_after_loop', '' );
				
				$html_members_box .= '</ul></div>';

				if( count( $exclude_members_from_loop ) == $amount ){
					$html_members_box .='</div>';
					return $html_members_box;
				}
			}

		}

	}
	
	$user_id = 0;
	
	if( isset( $friends ) && isset( $dynamic ) && !empty( $dynamic ) && bp_is_user() ) 
		$user_id = bp_displayed_user_id();
	elseif( isset( $friends ) )
		$user_id = bp_loggedin_user_id();

	$members_arg = array( 'user_id' => $user_id, 'max' => $amount );

	if( !empty( $type ) )
		$members_arg['type'] = $type;

	if( !empty( $exclude_members_from_loop ) && is_array( $exclude_members_from_loop ) && count( $exclude_members_from_loop ) > 0 ) {
		$members_arg['exclude'] = $exclude_members_from_loop;
		$members_arg['max'] = ( $amount - count( $exclude_members_from_loop ) > 0 ) ? $amount - count( $exclude_members_from_loop ) : 0 ;
	}
	
	$members_arg = apply_filters( 'bowe_codes_members_tag_args', $members_arg, $args );
	
	if( bp_has_members( $members_arg ) ){

		$html_members_box .= '<ul class="'.$class.'-ul">';

		while ( bp_members() ){

			bp_the_member();

			$html_members_box .= '<li>';
				
			if( !empty( $avatar ) )
				$html_members_box .= '<div class="bc_avatar"><a href="'.bp_get_member_permalink().'">'.bp_get_member_avatar('type=full&width='.$size.'&height='.$size) . '</a></div>';
				
			$html_members_box .= '<div class="user-infos">';
			$html_members_box .= '<h4><a href="'.bp_get_member_permalink().'">'.bp_get_member_name().'</a></h4>';
			
			// if some want to add some html
			$html_members_box .= apply_filters( 'bowe_codes_members_tag', '' );
			$html_members_box .= '</div></li>';
		}
		
		$html_members_box .='</ul>';
	}
	
	$html_members_box .= apply_filters( 'bowe_codes_members_tag_after_loop', '' );
	
	$html_members_box .='</div>';
	return $html_members_box;
}

/** Notifications ******************************************************/

/**
 * Gets the sender id for a given notification
 * 
 * @param  string $date_notified
 * @global bp the BuddyPress global
 * @uses wpdb the WordPress database class
 * @return int the sender id
 */
function bowe_codes_get_sender( $date_notified ){
	global $wpdb, $bp;

	return $wpdb->get_var( $wpdb->prepare( "SELECT sender_id FROM {$wpdb->base_prefix}bp_messages_messages WHERE date_sent = %s", $date_notified ) );
}

/**
 * Handling function for bc_notifications shortcode
 * 
 * @param  array $args the shortcode arguments
 * @uses is_user_logged_in() to check the user is logged in
 * @uses BP_Core_Notification::get_all_for_user to fetches all the notifications
 * @uses bp_loggedin_user_id() to get current user id
 * @uses bp_core_get_notifications_for_user() to get the content to output
 * @uses bp_core_fetch_avatar() to get the avatar of the item
 * @uses bowe_codes_get_sender() to get the sender id in case of a notification related to a message
 * @return string html for the notifications
 */
function bowe_codes_notifications_tag( $args = '' ){
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );
	
	if( !is_user_logged_in() ) return false;
	
	$notifications = BP_Core_Notification::get_all_for_user( bp_loggedin_user_id() );
	$notifications_content = bp_core_get_notifications_for_user( bp_loggedin_user_id() );
	
	$html_notifications_box = '<div class="'.$class.'">';

	if( !empty( $notifications_content ) && count( $notifications_content ) > 0 ){
		
		$html_notifications_box .='<ul class="'.$class.'-ul">';

		for( $i=0 ;$i < count( $notifications_content ); $i++ ){

			if( $i < $amount ){
				$html_notifications_box .='<li>';

				if( !empty( $avatar ) ){
					if( $notifications[$i]->component_name == "groups" ){
						if( $notifications[$i]->secondary_item_id != 0 ) $html_notifications_box .= '<div class="bc_avatar">'.bp_core_fetch_avatar( 'item_id=' . $notifications[$i]->secondary_item_id . '&object=group&type=full&avatar_dir=group-avatars&width='.$size.'&height='.$size ) . '</div>';
						else $html_notifications_box .= '<div class="bc_avatar">'.bp_core_fetch_avatar( 'item_id=' . $notifications[$i]->item_id . '&object=group&type=full&avatar_dir=group-avatars&width='.$size.'&height='.$size ) . '</div>';
					}
					elseif( $notifications[$i]->component_name == 'messages') $html_notifications_box .= '<div class="bc_avatar">'.bp_core_fetch_avatar( 'item_id=' . bowe_codes_get_sender( $notifications[$i]->date_notified ) . '&type=full&width='.$size.'&height='.$size ) . '</div>';
					else $html_notifications_box .= '<div class="bc_avatar">'.bp_core_fetch_avatar( 'item_id=' . $notifications[$i]->item_id . '&type=full&width='.$size.'&height='.$size ) . '</div>';
				}
				$html_notifications_box .= '<div class="notification-infos">';
				$html_notifications_box .= '<p class="bc_notifications">'. $notifications_content[$i] .'</p>';
				$html_notifications_box .= '</div></li>';
			}
		}
	}else{
		$html_notifications_box .= '<ul class="'.$class.'-ul">';
		$html_notifications_box .= '<li><div class="notification-infos">';
		$html_notifications_box .= '<p class="bc_notifications">'. __( 'No new notifications.', 'bowe-codes' ) .'</p>';
		$html_notifications_box .= '</div></li>';
	}
	$html_notifications_box .='</ul></div>';
	return $html_notifications_box;
}

/** Groups ******************************************************/

/**
 * Builds the html part for a single group
 * 
 * @param  int  $id
 * @param  string  $name
 * @param  string  $slug
 * @param  string  $size
 * @param  boolean $avatar
 * @param  string $permalink
 * @param  boolean $desc
 * @uses groups_get_group() to get a group object based on its id
 * @uses bp_get_group_permalink() to build the link to this group
 * @uses bp_core_fetch_avatar() to get the avatar of the group
 * @return string the html part for a single group
 */
function bowe_codes_html_group( $id, $name, $slug, $size="50", $avatar = false, $permalink = false, $desc = false ){
	
	if( empty( $permalink ) ) {
		$group = groups_get_group( array( 'group_id' => $id ) );
		$group_home = bp_get_group_permalink( $group );
	} else {
		$group_home = $permalink;
	}
	
	$group_html ='';
	//avatar
	if( !empty( $avatar) )
		$group_html .= '<li><div class="bc_avatar"><a href="'. $group_home .'">'.bp_core_fetch_avatar( 'item_id='. $id .'&object=group&type=full&avatar_dir=group-avatars&width='. $size .'&height='. $size ) . '</a></div>';
	else 
		$group_html .='<li>';
	
	$group_html .= '<div class="group-infos">';
	
	$group_html .= apply_filters( 'bowe_codes_html_group_before_title', '', $id, $slug, $permalink );
	
	$group_html .= '<h4><a href="'. $group_home .'">'. $name .'</a></h4>';
	
	$group_html .= apply_filters( 'bowe_codes_html_group_after_title', '', $id, $slug, $permalink );
	
	if( !empty( $desc ) ){
		$group_html .= '<p><span class="group-desc">'. $desc .'</span></p>';
	}

	// if some want to add some html
	$group_html .= apply_filters( 'bowe_codes_html_group', '', $id, $slug, $permalink );
	$group_html .= '</div></li>';
	
	
	return $group_html;
}

/**
 * Handling function for bc_group shortcode
 * 
 * @param  array $args the shortcode arguments
 * @uses groups_get_id() to get the group id from its slug
 * @uses groups_get_group() to get a group object based on its id
 * @uses bp_get_group_permalink() to build the link to this group
 * @uses bowe_codes_html_group() to get the html part fot the group
 * @return string the html part for group
 */
function bowe_codes_group_tag( $args = '' ){
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );
	
	$bc_group_id = groups_get_id( $slug );
	
	if( empty( $bc_group_id ) )
		return false;
	
	$group = groups_get_group( array( 'group_id' => $bc_group_id ) );
	$permalink = bp_get_group_permalink( $group );
	
	$html_group_box = '<div class="'.$class.'">';
	if( !empty( $desc ) )
		$html_group_box .= '<ul class="'.$class.'-ul">'.bowe_codes_html_group( $group->id, $group->name, $slug, $size, $avatar, $permalink, $group->description ).'</ul>';
	else 
		$html_group_box .=	'<ul class="'.$class.'-ul">'.bowe_codes_html_group( $group->id, $group->name, $slug, $size, $avatar, $permalink ).'</ul>';
	
	$html_group_box .='</div>';
	
	return $html_group_box;
}

/**
 * Builds an array of group ids from one of group slugs
 * 
 * @param  array $slug_list the list of featured group slug
 * @uses groups_get_id() to get the id of a group from its slug
 * @return array the group ids
 */
function bowe_codes_get_groups_by_slug( $slug_list ) {
	$group_ids = array();

	if( empty( $slug_list ) || !is_array( $slug_list ) )
		return false;

	foreach( $slug_list as $slug ) {
		$bc_group_id = false;
		$bc_group_id = groups_get_id( $slug );

		if( !empty( $bc_group_id ) )
			$group_ids[] = $bc_group_id;
	}

	if( empty( $group_ids ) || count( $group_ids ) < 1 )
		return false;

	return $group_ids;
}

/**
 * Handling function for the bc_groups and bc_user_groups shortcodes
 *
 * Since 2.0, we exclusively use the groups loop
 * In case of featured groups, the include argument of the first
 * loop becomes the exclude one of the second loop.
 * 
 * @param  array $args the shortcode arguments
 * @uses bowe_codes_get_groups_by_slug() to build the array of group ids form slugs
 * @uses  the groups loop
 * @uses bp_displayed_user_id() to get displayed user id in case of bc_user_groups
 * @uses bp_loggedin_user_id() to get current user id in case of bc_user_groups
 * @return string the html part for the groups
 */
function bowe_codes_groups_tag( $args = '' ){
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );
 
	$html_groups_box = '<div class="'.$class.'">';
	$exclude_groups_from_loop = array();

	if( !empty( $content ) ) 
		$html_groups_box.='<h3>'.$content.'</h3>';
	
	if( !empty( $featured ) ){

		$featured_list = explode( ',', $featured );
		$exclude_groups_from_loop = bowe_codes_get_groups_by_slug( $featured_list );

		if( !empty( $exclude_groups_from_loop ) && is_array( $exclude_groups_from_loop ) && count( $exclude_groups_from_loop ) > 0 ) {

			$featured_arg = apply_filters( 'bowe_codes_groups_tag_featured_args', array( 'include' => $exclude_groups_from_loop ), $args );

			if( bp_has_groups( $featured_arg ) ){

				$html_groups_box .='<div class="featured"><ul class="'.$class.'-ul">';
				
				while ( bp_groups() ){

					bp_the_group();
					
					$html_groups_box .= '<li>';
					
					if( !empty( $avatar ) ) 
						$html_groups_box .= '<div class="bc_avatar"><a href="'.bp_get_group_permalink().'">'.bp_get_group_avatar('type=full&width='.$size.'&height='.$size) . '</a></div>';

					$html_groups_box .= '<div class="group-infos">';
					$html_groups_box .= '<h4><a href="'.bp_get_group_permalink().'">'.bp_get_group_name().'</a></h4>';
					
					// if some want to add some html
					$html_groups_box .= apply_filters( 'bowe_codes_groups_tag_featured', '' );
					$html_groups_box .= '</div></li>';
					
				}
				
				$html_groups_box .= apply_filters( 'bowe_codes_groups_tag_featured_after_loop', '' );

				$html_groups_box .='</ul></div>';

				if( count( $exclude_groups_from_loop ) == $amount ){
					$html_groups_box .='</div>';
					return $html_groups_box;
				}

			}

		}

	}

	$group_args = array( 'type' => $type, 'per_page' => $amount, 'max' => $amount );

	if( !empty( $exclude_groups_from_loop ) && is_array( $exclude_groups_from_loop ) && count( $exclude_groups_from_loop ) > 0 ) {
		$group_args['exclude'] = $exclude_groups_from_loop;
		$group_args['max'] = ( $amount - count( $exclude_groups_from_loop ) > 0 ) ? $amount - count( $exclude_groups_from_loop ) : 0 ;
	}
	
	// faut vérifier ce truc !!
	if( !empty( $user_groups ) ){

		if ( bp_is_user() && $dynamic )
			$user_id = bp_displayed_user_id();
		else 
			$user_id = bp_loggedin_user_id();

		if( !empty( $user_id ) )
			$group_args['user_id'] = $user_id;
		else
			return $html_groups_box .='</div>';
	}
	
	$group_args = apply_filters( 'bowe_codes_groups_tag_args', $group_args, $args );
	
	if( bp_has_groups( $group_args ) ){
		
		$html_groups_box .='<ul class="'.$class.'-ul">';
		
		while ( bp_groups() ){
			bp_the_group();
			
			$html_groups_box .= '<li>';
			
			if( !empty( $avatar ) )
				$html_groups_box .= '<div class="bc_avatar"><a href="'.bp_get_group_permalink().'">'.bp_get_group_avatar('type=full&width='.$size.'&height='.$size) . '</a></div>';
			
			$html_groups_box .= '<div class="group-infos">';
			$html_groups_box .= '<h4><a href="'.bp_get_group_permalink().'">'.bp_get_group_name().'</a></h4>';
			
			$html_groups_box .= apply_filters( 'bowe_codes_groups_tag', '' );
			$html_groups_box .= '</div></li>';
			
		}

		$html_groups_box .='</ul>';
	}
	
	$html_groups_box .= apply_filters( 'bowe_codes_groups_tag_after_loop', '' );

	$html_groups_box .='</div>';
	return $html_groups_box;
}

/**
 * Handling function for bc_group_users shortcode
 * 
 * @param  array $args the shortcode arguments
 * @uses groups_get_id() to get group id from its slug
 * @uses the group members loop
 * @uses bowe_codes_html_member() to build html for each member
 * @return string html the list of members for a given group
 */
function bowe_codes_group_users_tag( $args = '' ) {
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );
	
	$bc_group_id = groups_get_id( $slug );
	$html_members_box = '';
	
	if( empty( $bc_group_id ) )
		return false;

	$group_users_arg = array( 
			'exclude_admins_mods' => 0,
			'group_id' => $bc_group_id,
			'max' => $amount,
			'per_page' => $amount,
			);
			
	$group_users_arg = apply_filters( 'bowe_codes_group_users_tag_args', $group_users_arg, $args );
	
	if ( bp_group_has_members( $group_users_arg ) ) {
		
		$html_members_box .= '<div class="'.$class.'">';

		if( !empty( $content ) ) 
			$html_members_box .= '<h3>'.$content.'</h3>';

		$html_members_box .= '<ul class="'.$class.'-ul">';
		
		while ( bp_group_members() ) {
		
			bp_group_the_member();
			
			$html_members_box .= bowe_codes_html_member( bp_get_group_member_id(), $avatar, $size );
		
		}
		
		$html_members_box .='</ul></div>';
	}
	
	return $html_members_box;
}

/**
 * Handling function for bc_restrict_gm shortcode
 * 
 * @param  array $args the shortcode arguments
 * @uses groups_get_id() to get group id from its slug
 * @uses is_user_logged_in() to check if the user is logged in
 * @uses groups_is_user_member() to check if the user is member of the group
 * @uses groups_get_group() to get the group object
 * @uses bp_get_group_permalink() to build the link to the group
 * @return string the restricted content or a warning message
 */
function bowe_code_hide_post_content_tag( $args = '' ) {
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );

	$bc_group_id = false;
	
	// if no group id content is return
	if( empty( $group_id ) )
		return __( 'No group id or group slug were given', 'bowe-codes' );

	if( is_numeric( $group_id ) )
		$bc_group_id = $group_id;
	else
		$bc_group_id = groups_get_id( $group_id );

	if( empty( $bc_group_id ) )
		return __( 'The group slug given is unknown', 'bowe-codes' );

	// if user not logeddin, he's asked to
	if( !is_user_logged_in() ) {
		
		$message_unlogged = '<p class="'.$class.'">' . __('You must be loggedin to access to this content', 'bowe-codes') . '</p>';
		return apply_filters( 'bowe_code_hide_post_unconnect_message', $message_unlogged );
	}
		

	$user_id = bp_loggedin_user_id();

	// if the user is a group member, let's return the content	
	if ( groups_is_user_member( $user_id, $bc_group_id ) )
		return $content;

	else {

		$group = groups_get_group( array( 'group_id' => $bc_group_id ) );
		$group_home = bp_get_group_permalink( $group );
		$group_name = $group->name;
		$message_notgm = '<p class="'.$class.'">' . sprintf(__('You must be a member of the group %s to access this content', 'bowe-codes'), '<a href='.$group_home.'>'.$group_name.'</a>') . '</p>';

		return apply_filters( 'bowe_code_hide_post_connect_message', $message_notgm, $bc_group_id );

		}
	
}

/** Messages ******************************************************/

/**
 * Handling function for the shortcode bc_messages
 * 
 * @param  array $args the shortcode arguments
 * @global messages_template
 * @uses is_user_logged_in() to check for loggedin user
 * @uses  the messages loop
 * @uses bp_core_get_user_domain() to get member's home page link
 * @uses bp_core_get_user_displayname() to get member's display name
 * @uses bp_core_fetch_avatar() to get member's avatar
 * @uses bp_create_excerpt() to truncate the message content
 * @return string the list of messages
 */
function bowe_codes_messages_tag( $args = '' ){
	global $messages_template;
	
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );
	
	if( !is_user_logged_in() ) 
		return false;
	
	$html_messages_box = '<div class="'.$class.'">';
	
	if( bp_has_message_threads( array( 'box'=> 'inbox', 'per_page' => $amount ) ) ){

		$html_messages_box .='<ul class="'.$class.'-ul">';

		while ( bp_message_threads() ){
			bp_message_thread();
			$html_messages_box .='<li>';
			$sender_home = bp_core_get_user_domain( $messages_template->thread->last_sender_id );
			if( !empty( $avatar ) )
				$html_messages_box .= '<div class="bc_avatar"><a href="'.$sender_home.'" title="'.__('From:','bowe-codes').' '.bp_core_get_user_displayname( $messages_template->thread->last_sender_id ).'">'.bp_core_fetch_avatar('item_id=' . $messages_template->thread->last_sender_id . '&type=full&width='.$size.'&height='.$size ) . '</a></div>';
			
			$html_messages_box .= '<div class="message-infos">';

			if( empty( $avatar ) && !empty( $subject ) )
				$html_messages_box .= '<span class="bc_from">'.__('From:','bowe-codes').' '.bp_get_message_thread_from().' </span>';
			
			if( empty( $avatar ) && empty( $subject ) )
				$html_messages_box .= '<span class="bc_from">'.__('From:','bowe-codes').'<a href="'.bp_get_message_thread_view_link().'" title="'.__( "View Message", "buddypress" ).'">'.bp_core_get_user_displayname( $messages_template->thread->last_sender_id ).'</a>';
			
			if( !empty( $subject ) )
				$html_messages_box .= '<span class="bc_subject"><a href="'.bp_get_message_thread_view_link().'" title="'.__( "View Message", "buddypress" ).'">'.bp_get_message_thread_subject().'</a></span>';
			
			$html_messages_box .= '<p class="bc_excerpt">'.strip_tags( bp_create_excerpt( $messages_template->thread->last_message_content, intval( $excerpt ) ) ).'</p>';
			$html_messages_box .= '</div></li>';
		}
	}
	$html_messages_box .='</ul></div>';
	return $html_messages_box;
}

/** Blogs ******************************************************/

/**
 * Builds the html part for a single blog
 * 
 * @param  int $blog_id
 * @param  boolean $avatar
 * @param  string $size
 * @param  boolean $desc
 * @uses get_blog_option()
 * @uses get_avatar() to fetch blog's administrator avatar
 * @return string the blog html part
 */
function bowe_codes_html_blog( $blog_id, $avatar, $size, $desc ){
	$blog_html = '';

	if( !empty( $avatar ) )
		$blog_html .= '<div class="bc_avatar"><a href="'.get_blog_option( $blog_id, 'siteurl' ).'">'.get_avatar(get_blog_option( $blog_id, 'admin_email' ), $size ).'</a></div>';
	
	$blog_html .= '<div class="blog-infos">';
	$blog_html .= '<h4><a href="'.get_blog_option('siteurl',$blog_id).'">'.get_blog_option( $blog_id, 'blogname' ).'</a></h4>';
	
	if( !empty( $desc ) )
		$blog_html .= '<p>'.get_blog_option( $blog_id, 'blogdescription' ).'</p>';
	
	$blog_html .= '</div>';
	
	return $blog_html;
}

/**
 * Handling function for bc_blogs shortcode
 * 
 * @param  array $args the shortcode arguments
 * @global blog_id
 * @global blogs_template
 * @uses bp_get_root_blog_id() to get the blog id BuddyPress is running on
 * @uses bowe_codes_html_blog() to get a single blog html part (in case of featured blog)
 * @uses the blogs loop
 * @return string html of the list of blogs
 */
function bowe_codes_blogs_tag( $args = '' ){
	global $blog_id, $blogs_template;

	// not available for children !
	if( $blog_id != bp_get_root_blog_id() )
		return false;
	
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );
	
	$html_blogs_box = '<div class="'.$class.'">';
	$exclude_blogs_from_loop = array();
	
	if( !empty( $featured ) ){

		$featured_list = explode( ',', $featured );
		
		if( count( $featured_list ) > 0 ){

			$html_blogs_box .= '<div class="featured">';
			$html_blogs_box .='<ul class="'.$class.'-ul">';

			foreach( $featured_list as $feat_ids ){
				$exclude_blogs_from_loop[]=$feat_ids;
				$html_blogs_box .= '<li>'.bowe_codes_html_blog( $feat_ids, $avatar, $size, $desc ).'</li>';
			}
			$html_blogs_box .='</ul></div>';
		}

		if( count( $featured_list ) == $amount ){
			return $html_blogs_box.'</div>';
		}
		
	}
	if( bp_has_blogs( array( 'type' => $type, 'per_page' => $amount, 'max' => $amount ) ) ) {

		$html_blogs_box .= '<ul class="'.$class.'-ul">';
		$i=1;
		$j=0;
		while ( bp_blogs() ){
			bp_the_blog();
			
			$check = $amount - count($exclude_blogs_from_loop);

			if( isset( $exclude_blogs_from_loop ) && in_array( $blogs_template->blogs[$j]->blog_id, $exclude_blogs_from_loop ) )
					$i-=1 ;
			elseif( $i <= $amount - count( $exclude_blogs_from_loop ) ){
				if( !empty( $avatar ) )
					$html_blogs_box .= '<li><div class="bc_avatar"><a href="'.bp_get_blog_permalink().'">'.bp_get_blog_avatar('width='.$size.'&height='.$size) . '</a></div>';
				
				else 
					$html_blogs_box .= '<li>';
				
				$html_blogs_box .= '<div class="blog-infos">';
				$html_blogs_box .= '<h4><a href="'.bp_get_blog_permalink().'">'.bp_get_blog_name().'</a></h4>';
				
				if( !empty( $desc ) )
					$html_blogs_box .= '<p>'.bp_get_blog_description().'</p>';
				
				$html_blogs_box .= '</div></li>';
			}
			
			$i+=1;
			$j+=1;
		}
	}
	$html_blogs_box .='</ul></div>';
	return $html_blogs_box;
}

/** Activities ******************************************************/

/**
 * Gets the activity types after checking 1.7 function exists
 * 
 * @since  2.0.1
 * 
 * @uses bp_is_active to check for activity component
 * @uses bp_activity_get_types() to return the available activity types.
 * @return array the list of activities or warning message
 */
function bowe_codes_activity_list_types() {

	if( bp_is_active( 'activity' ) && function_exists( 'bp_activity_get_types' ) )
		return bp_activity_get_types();
	else
		return array( 'activity_update' => 'BuddyPress 1.7 is required' );
}

/**
 * Handling function for the bc_activity shortcode 
 * 
 * @param  array $args the shortcode arguments
 * @uses the activity loop
 * @return string html for the list of activities
 */
function bowe_codes_activity_tag( $args = '' ) {
	if( !is_array( $args ) )
		return false;
 
	extract( $args, EXTR_SKIP );

	$html_activity_box = '<div class="'.$class.'">';

	$activity_args = array( 
			'action' => $type,
			'max' => $amount,
			'per_page' => $amount
	);
	
	$activity_args = apply_filters( 'bowe_codes_activity_tag_arg', $activity_args, $args );

	if( bp_has_activities( $activity_args ) ) {

		$html_activity_box .= '<ul class="'.$class.'-ul">';

		while ( bp_activities() ) {
			bp_the_activity();

			$html_activity_box .= '<li>';
			
			if( !empty( $avatar ) )
				$html_activity_box .= '<div class="bc_avatar"><a href="'.bp_get_activity_user_link().'">'.bp_core_fetch_avatar( 'item_id='. bp_get_activity_user_id() .'&type=full&width='. $size .'&height='. $size ) . '</a></div>';
			
			$html_activity_box .= '<div class="activity-infos">';
			$html_activity_box .= apply_filters( 'bowe_codes_activity_before_action', '' );
			$html_activity_box .= '<div class="activity-action">'. bp_get_activity_action() .'</div>';
			$html_activity_box .= apply_filters( 'bowe_codes_activity_before_content', '' );
			$html_activity_box .= '<div class="activity-content">'. bp_get_activity_content_body() .'</div>';
			$html_activity_box .= apply_filters( 'bowe_codes_activity_after_content', '' );
			$html_activity_box .= '</div></li>';
		} 
		$html_activity_box .= '</ul>';
	}

	$html_activity_box .= '</div>';

	return $html_activity_box;
}