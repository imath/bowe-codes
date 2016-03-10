<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Displays a warning message in network admin notices or admin notices
 * if required version of BuddyPress (1.7) is not activated.
 * 
 * @since  2.0.1
 * 
 * @return string html of the warning message
 */
function bowe_codes_warning_message() {
	?>
	<div id="message" class="updated fade">
		<p>Hi, Since version 2.0 of Bowe Codes, the plugin requires at least version 1.7 of BuddyPress.
		   Do not worry, you can still <a href="http://wordpress.org/extend/plugins/bowe-codes/developers/" title="List of Versions of the plugin" target="_blank">download version 1.3</a> of the plugin to roll back to it</p>
	</div>
	<?php
}

add_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', 'bowe_codes_warning_message' );