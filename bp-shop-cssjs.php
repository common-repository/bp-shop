<?php
// Admin page styles
if (is_admin()){
	function bp_shop_admin_css(){
		wp_enqueue_style('bp-shop-admin-style', BP_SHOP_URL . '/_inc/admin-style.css');
		wp_print_styles();
	}
	add_action('admin_head', 'bp_shop_admin_css');

	// Admin page scripts
	function bp_shop_admin_js(){
		wp_enqueue_script('bp-shop-admin-scripts', BP_SHOP_URL . '/_inc/admin-scripts.js');
		wp_print_scripts();
	}
	add_action('admin_head', 'bp_shop_admin_js');
}

