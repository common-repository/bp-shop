<?php
/*******************************************************************************
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */
function bp_shop_screen_stat(){
	do_action( 'bp_shop_screen_stat' );
	bp_core_load_template( apply_filters( 'bp_shop_template_stat', 'default/home' ) );
}
function bp_shop_screen_goods(){
	do_action( 'bp_shop_screen_goods' );
	bp_core_load_template( apply_filters( 'bp_shop_template_goods', 'default/home' ) );
}
function bp_shop_screen_wish(){
	do_action( 'bp_shop_screen_wish' );
	bp_core_load_template( apply_filters( 'bp_shop_template_wish', 'default/home' ) );
}
function bp_shop_screen_settings(){
	do_action( 'bp_shop_screen_settings' );
	bp_core_load_template( apply_filters( 'bp_shop_template_settings', 'default/home' ) );
}
?>
