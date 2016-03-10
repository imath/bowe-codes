=== Bowe Codes ===
Contributors: imath, bowromir
Donate link: http://imathi.eu/donations/
Tags: BuddyPress, shortcode, post, template, widget
Requires at least: 3.5
License: GNU/GPL 2
Tested up to: 3.6
Stable tag: 2.1

Allows users to simply insert BuddyPress specific data into posts, widgets or theme files by using shortcodes.

== Description ==

The name of the plugin could have been <i>BuddyPress Shortcodes</i>, but as it's Bowe's idea, i named it <b>Bowe Codes</b>. This plugin needs <a href="buddypress.org">BuddyPress</a> and it 
allows users to simply insert BuddyPress specific data into posts, widgets or theme files by using the WordPress shortcodes API. There are 12 shortcodes available in order
to display a specific member, a group, a list of members, the friends of the displayed or loggedin user, the groups of the displayed or loggedin user,
the messages and the notifications of a loggedin user, blogs of the network, posts from the blogs of the network.

http://vimeo.com/65513546

Into the toolbar of your WordPress rich text editor or into the quicktags bar of the regular text editor, a button will help you to configure your shortcodes. 
In the Settings menu of your backend, you will find the Bowe Codes Settings sub menu where you can disable the default css file to custom your own styles.
This plugin is available in french and english.

<strong>NB : Since version 2.0, this plugin requires at least BuddyPress 1.7</strong>

== Installation ==

You can download and install Bowe Codes using the built in WordPress plugin installer. If you download Bowe Codes manually, make sure it is uploaded to "/wp-content/plugins/bowe-codes/".

Activate Bowe Codes in the "Plugins" admin panel using the "Network Activate" (or "Activate" if you are not running a network) link.

== Frequently Asked Questions ==

= If you have a question =

Please add a comment <a href="http://imathi.eu/tag/bowe-codes/">here</a>

== Screenshots ==

1. Button into WordPress editors toolbars.
2. The Shortcode editor.
3. Examples of shorcodes.
4. New Widget

== Changelog ==

= 2.1 =
* Adds a widget so that it's easier to add shortcodes in sidebar.

= 2.0.1 =
* fixes a bug by checking activity component is active when building the shortcode settings
* adds an early check of BuddyPress version neutralizing the plugin if version < 1.7

= 2.0 =
* new shortcode editor
* new shortcode bc_activity
* a lot of filters customize the behavior of the plugin
* requires BuddyPress 1.7

= 1.3 =
* 1 new shortcode : displays group members of a given group

= 1.2 =
* group link bug fix for certain config (see the forum of this plugin)
* 2 new shortcodes : 1 to hide post content, and 1 other to display recent forum entries
* tested in BuddyPress 1.6

= 1.1 =
* css changes by bowromir
* bp_is_member is replaced by bp_is_user if BuddyPress > 1.5
* super admin can now hide the button to child blogs from Bowe Codes Options

= 1.0 =
* BP content shortcodes

== Upgrade Notice ==

= 2.1 =
* requires at least BuddyPress 1.7
* if BuddyPress version 1.7 is not active on the blog, the plugin will prompt you to roll back to Bowe Codes 1.3 version.

= 2.0.1 =
* requires at least BuddyPress 1.7
* if BuddyPress version 1.7 is not active on the blog, the plugin will prompt you to roll back to Bowe Codes previous version (1.3).

= 2.0 =
* requires BuddyPress 1.7

= 1.3 =
nothing particular..

= 1.2 =
nothing particular..

= 1.1 =
nothing particular..

= 1.0 =
no upgrades, just a first install..