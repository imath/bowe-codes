<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'bp_init',             'bowe_codes_init',                10 );
add_action( 'bowe_codes_init',     'bowe_codes_register',             0 );
add_action( 'bowe_codes_register', 'bowe_codes_register_shortcodes', 10 );
add_action( 'bp_enqueue_scripts',  'bowe_codes_enqueue_scripts',     10 );
add_action( 'bp_admin_init',       'bowe_codes_admin_init',          10 );
add_action( 'widgets_init',        'bowe_codes_widgets_init',       100 );

function bowe_codes_init() {
	do_action( 'bowe_codes_init' );
}

function bowe_codes_register() {
	do_action( 'bowe_codes_register' );
}

function bowe_codes_register_shortcodes() {
	do_action( 'bowe_codes_register_shortcodes' );
}

function bowe_codes_enqueue_scripts() {
	do_action( 'bowe_codes_enqueue_scripts' );
}

function bowe_codes_admin_init() {
	do_action( 'bowe_codes_admin_init' );
}

function bowe_codes_widgets_init() {
	do_action( 'bowe_codes_widgets_init' );
}