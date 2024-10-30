<?php
/*
 * Do smth on shop component page only
 */
function bp_is_shop_component() {
	$shop = get_option('bp_shop');
	if ( bp_is_current_component( $shop['slug'] ) )
		return true;

	return false;
}


