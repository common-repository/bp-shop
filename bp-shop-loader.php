<?php
/*
Plugin Name: BP Shop
Plugin URI: http://ovirium.com/plugins/bp-shop/
Description: Development Version for BP1.3! Allow every (or only some) members of your blog to have their own catalogue of products with the ability to sell them
Author: slaFFik
Version: 0.1
DB Version: 1
Author URI: http://cosydale.com/
Domain Path: /langs/
Text Domain: bp_shop
*/

define('BP_SHOP_VERSION', '0.1');
define('BP_SHOP_DB_VERSION', 1);
define('BP_SHOP_DIR', WP_PLUGIN_DIR .'/bp-shop'); // without trailing slash
define('BP_SHOP_URL', plugins_url( $path = '/bp-shop' )); // without trailing slash
define('BP_SHOP_THEME_DIR', BP_SHOP_DIR . '/templates'); // without trailing slash

/*
 * Activate the plugin
 */
register_activation_hook(__File__, 'bp_shop_activate');
function bp_shop_activate(){

    $bp_shop['status'] = 'on';
	$bp_shop['slug'] = 'shop';
	$bp_shop['deactivate'] = 'light';
    
    add_option('bp_shop', $bp_shop, '', 'yes');
}

/*
 * Deactivation hook
 */
register_deactivation_hook( __File__, 'bp_shop_deactivation');
function bp_shop_deactivation(){
   global $wpdb, $bp;
   $bp_shop = get_option('bp_shop');
   if ( $bp_shop['deactivate'] == 'total'){
      $wpdb->query("DROP TABLE {$bp->shop->table_goods}");
      $wpdb->query("DROP TABLE {$bp->shop->table_cats}");
      delete_option('bp_shop');
   }
   // delete it later
   delete_option('bp_shop');
}

/*
 * Load textdomain for i18n
 */
add_action('bp_loaded', 'bp_shop_load_textdomain', 3);
function bp_shop_load_textdomain() {
    $locale = apply_filters('buddypress_locale', get_locale());
    $mofile = GTM_DIR . "/langs/$locale.mo";

    if (file_exists($mofile))
        load_textdomain('bp_shop', $mofile);
}
	
// Create the shop component
if ( !class_exists( 'BP_Component' ) ){
	require_once( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
}
class BP_SHOP_Component extends BP_Component{

	private $installed = false;

	/**
	 * Start the BuddyShop component creation process
	 */
	function BP_SHOP_Component() {
		parent::start(
			'shop',
			__( 'Users Shops', 'bp_shop' ),
			BP_SHOP_DIR
		);
	}

	/**
	 * Include files
	 */
	function _includes() {
		$includes = array(
			'bp-shop-cssjs.php',
			'bp-shop-filters.php',
			'bp-shop-admin.php',
			'bp-shop-screens.php',
			'bp-shop-classes.php',
			'bp-shop-widgets.php',
			'bp-shop-activity.php',
			'bp-shop-template.php',
			'bp-shop-functions.php'
		);
		parent::_includes( $includes );
	}

	/**
	 * Setup globals
	 *
	 * @global obj $bp
	 */
	function _setup_globals() {
		global $bp;
		$shop = get_option('bp_shop');
		// Global tables for messaging component
		$global_tables = array(
			'table_goods' => $bp->table_prefix . 'bp_shop_goods',
			'table_cats' => $bp->table_prefix . 'bp_shop_cats'
		);

		// All globals for shop component.
		// Note that global_tables is included in this array.
		$globals = array(
			'slug'                  => $shop['slug'],
			'root_slug'             => isset( $bp->pages->shop->slug ) ? $bp->pages->shop->slug : $shop['slug'],
			'notification_callback' => 'shop_format_notifications',
			'search_string'         => __( 'Search in Shops...', 'bp_shop' ),
			'global_tables'         => $global_tables,
		);

		parent::_setup_globals( $globals );
	}

	/**
	 * Setup BuddyBar navigation
	 *
	 * @global obj $bp
	 */
	function _setup_nav() {
		global $bp;

		// Add 'Messages' to the main navigation
		$main_nav = array(
			'name'                    => __('Shop', 'bp_shop'),
			'slug'                    => $this->slug,
			'root_slug'               => $this->root_slug,
			'position'                => 22,
			'show_for_displayed_user' => false,
			'screen_function'         => 'bp_shop_screen_stat',
			'default_subnav_slug'     => 'stat',
			'item_css_id'             => $this->id
		);

		// Link to user messages
		$shop_link = trailingslashit( $bp->loggedin_user->domain . $this->slug );

		// Add the subnav items to the profile
		$sub_nav[] = array(
			'name'            => __( 'Statistics', 'bp_shop' ),
			'slug'            => 'stat',
			'parent_url'      => $shop_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'bp_shop_screen_stat',
			'position'        => 10,
			'user_has_access' => bp_is_my_profile()
		);

		$sub_nav[] = array(
			'name'            => __( 'Goods', 'bp_shop' ),
			'slug'            => 'goods',
			'parent_url'      => $shop_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'bp_shop_screen_goods',
			'position'        => 20,
			'user_has_access' => bp_is_my_profile()
		);

		$sub_nav[] = array(
			'name'            => __( 'WishList', 'bp_shop' ),
			'slug'            => 'wish',
			'parent_url'      => $shop_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'bp_shop_screen_wish',
			'position'        => 30,
			'user_has_access' => bp_is_my_profile()
		);

		$sub_nav[] = array(
			'name'            => __( 'Settings', 'bp_shop' ),
			'slug'            => 'settings',
			'parent_url'      => $shop_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'bp_shop_screen_settings',
			'position'        => 90,
			'user_has_access' => bp_is_my_profile()
		);

		parent::_setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the admin bar
	 *
	 * @global obj $bp
	 */
	function _setup_admin_bar() {
		global $bp;

		// Prevent debug notices
		$wp_admin_nav = array();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$shop_link = trailingslashit( $bp->loggedin_user->domain . $this->slug );

			// Add main Shop menu
			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __('Shop', 'bp_shop'),
				'href'   => trailingslashit( $shop_link )
			);

			// Stat
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'title'  => __('Statistics', 'bp_shop'),
				'href'   => trailingslashit( $shop_link . 'stat' )
			);

			// Goods
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'title'  => __( 'Goods', 'bp_shop' ),
				'href'   => trailingslashit( $shop_link . 'goods' )
			);

			// WishList
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'title'  => __( 'WishList', 'bp_shop' ),
				'href'   => trailingslashit( $shop_link . 'wish' )
			);

			// Settings
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'title'  => __( 'Settings', 'bp_shop' ),
				'href'   => trailingslashit( $shop_link . 'settings' )
			);
		}

		parent::_setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Sets up the title for pages and <title>
	 *
	 * @global obj $bp
	 */
	function _setup_title() {
		global $bp;

		if ( bp_is_shop_component() ) {
			if ( bp_is_my_profile() ) {
				$bp->bp_options_title = __( 'My Shop', 'bp_shop' );
			} else {
				$bp->bp_options_avatar = bp_core_fetch_avatar( array(
					'item_id' => $bp->displayed_user->id,
					'type'    => 'thumb'
				) );
				$bp->bp_options_title = $bp->displayed_user->fullname;
			}
		}

		parent::_setup_title();
	}
}

$bp->shop = new BP_SHOP_Component();

/*
 * Handy Functions
 */
 if (!function_exists('print_var')){
	function print_var($var){
		echo '<pre>';
		if(!empty($var))
			print_r($var);
		else
			var_dump($var);
		echo '</pre><br />';
	}
}

add_action('bp_adminbar_menus', 'bp_shop_queries');
function bp_shop_queries(){
    echo '<li class="no-arrow"><a>'.get_num_queries() . ' queries | ';
    echo round(memory_get_usage() / 1024 / 1024, 2) . 'Mb</a></li>';
}