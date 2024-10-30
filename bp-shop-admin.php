<?php
/*
 * Load Admin page
 */
$new_bp_shop_admin = new BP_SHOP_ADMIN_PAGE();
class BP_SHOP_ADMIN_PAGE{

    //constructor of class, PHP4 compatible construction for backward compatibility (until WP 3.1)
    function bp_shop_admin_page(){
        add_filter('screen_layout_columns', array( &$this, 'on_screen_layout_columns'), 10, 2 );
        add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( &$this, 'on_admin_menu') );
    }
    function on_screen_layout_columns( $columns, $screen ){
        if ($screen == $this->pagehook)
            $columns[$this->pagehook] = 2;
        return $columns;
    }
    function on_admin_menu(){
        $this->pagehook = add_submenu_page('bp-general-settings', __('BP Shop', 'bp_shop'), __('BP Shop', 'bp_shop'), 'manage_options', 'bp-shop-admin', array( &$this, 'on_show_page') );
        add_action('load-'.$this->pagehook, array( &$this, 'on_load_page') );
    }
	
    //will be executed if wordpress core detects this page has to be rendered
    // hook to implement new blocks
    function on_load_page(){
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');

        // sidebar
add_meta_box('bp-shop-admin-debug', __('Dev print_var(bp_shop)', 'bp_shop'), array(&$this, 'on_bp_shop_admin_debug'), $this->pagehook, 'side', 'core');
	add_meta_box('bp-shop-admin-switch', __('Switchers', 'bp_shop'), array( &$this, 'on_bp_shop_admin_switch'), $this->pagehook, 'side', 'core');
        // main content - normal
		//add_meta_box('bp-shop-admin-main', __('Main Options', 'bp_shop'), array( &$this, 'on_bp_shop_admin_main'), $this->pagehook, 'normal', 'core');
add_meta_box('bp-shop-admin-test', __('Copy/Paste later', 'bp_shop'), array( &$this, 'on_bp_shop_admin_test'), $this->pagehook, 'normal', 'core');
    }
	
    // save all inputed values
    function save_data($bp_shop){
        if ( isset($_POST['saveData']) ){

			$bp_shop['status'] = $_POST['bp_shop_status'];
			$bp_shop['slug'] = $_POST['bp_shop_slug'];

			$bp_shop['test']['radio'] = $_POST['bp_shop_radio'];
			$bp_shop['test']['checkboxes'] = $_POST['bp_shop_checkbox'];
			$bp_shop['test']['input'] = stripslashes($_POST['bp_shop_input']);

			 update_option('bp_shop', $bp_shop);
        }
        return $bp_shop;
    }
	
    //executed to show the plugins complete admin page
    function on_show_page(){
        global $bp, $wpdb, $screen_layout_columns; ?>

        <div id="bp-shop-admin-general" class="wrap">
            <?php screen_icon('options-general'); ?>
            <h2><?php _e('BP Shop','bp_shop') ?> <sup><?php echo 'v' . BP_SHOP_VERSION; ?></sup> &rarr; <?php _e('Create a shop for your users', 'bp_shop') ?></h2>

            <?php 
				$bp_shop = get_option('bp_shop');
				$bp_shop = $this->save_data($bp_shop);
				if(empty($bp_shop)) $bp_shop = array();
				?>
            
            <form action="" id="bp-shop-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('bp-shop-admin-general'); ?>
                <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
                <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>

                <div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
                    <div id="side-info-column" class="inner-sidebar">
                        <div style="text-align:center;margin:0 0 15px 0">
                            <input style="padding:4px 30px;" type="submit" value="<?php _e('Save Changes', 'bp_shop') ?>" class="button-primary" name="saveData"/>
                        </div>
                        <?php do_meta_boxes($this->pagehook, 'side', $bp_shop); ?>
                    </div>
                    <div id="post-body" class="has-sidebar">
                        <div id="post-body-content" class="has-sidebar-content">
                            <?php do_meta_boxes($this->pagehook, 'normal', $bp_shop); ?>
                            <p>
                                <input type="submit" value="<?php _e('Save Changes', 'bp_shop') ?>" class="button-primary" name="saveData"/>
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready( function($){
            $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
            postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
        });
        //]]>
        </script>
    <?php
    }
	
	/*
	 * Main content blocks
	*/
	function on_bp_shop_admin_test($bp_shop){
		echo '<p>Test for all types of data that I can save:</p>';
        echo '<p>'.__('Some radios buttons','bp_shop').'</p>';
        echo '<p><input name="bp_shop_radio" id="bp_shop_radio" type="radio" value="on" '. ('on' == $bp_shop['test']['radio'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_radio">'. __('Enable', 'bp_shop') .'</label></p>
            <p><input name="bp_shop_radio" id="bp_shop_radio" type="radio" value="off" '. ('off' == $bp_shop['test']['radio'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_radio">'. __('Disable', 'bp_shop') .'</label></p>';

        echo '<hr />';

        echo '<p>'.__('Some checkboxes','bp_shop').'</p>';
        echo '<p><input name="bp_shop_checkbox[1]" id="bp_shop_checkbox" type="checkbox" value="1" '. ('1' == $bp_shop['test']['checkboxes']['1'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_checkbox">'. __('One', 'bp_shop') .'</label></p>
            <p><input name="bp_shop_checkbox[2]" id="bp_shop_checkbox" type="checkbox" value="2" '. ('2' == $bp_shop['test']['checkboxes']['2'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_checkbox">'. __('Two', 'bp_shop') .'</label></p>
				<p><input name="bp_shop_checkbox[3]" id="bp_shop_checkbox" type="checkbox" value="3" '. ('3' == $bp_shop['test']['checkboxes']['3'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_checkbox">'. __('Three', 'bp_shop') .'</label></p>';

		echo '<hr />';

        echo '<p>'.__('Input filed','bp_shop').'</p>';
        echo '<p><input name="bp_shop_input" id="bp_shop_input" type="text" value="'. $bp_shop['test']['input'] .'" /> <label for="bp_shop_input">'. __('Some description', 'bp_shop') .'</label></p>';

	}
	
	/*
	 * Sidebar blocks
	*/
	function on_bp_shop_admin_switch($bp_shop){
        echo '<p>'.__('Do you want to make users stores available for all?','bp_shop').'</p>';
        echo '<p><input name="bp_shop_status" id="bp_shop_status_on" type="radio" value="on" '. ('on' == $bp_shop['status'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_status_on">'. __('Enable', 'bp_shop') .'</label></p>
            <p><input name="bp_shop_status" id="bp_shop_status_off" type="radio" value="off" '. ('off' == $bp_shop['status'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_status_off">'. __('Disable', 'bp_shop') .'</label></p>';

        echo '<hr />';

        echo '<p>'.__('Which slug do you prefer to use across the site (it\'s used in links)?','bp_shop').'</p>';
        echo '<p><input name="bp_shop_slug" id="bp_shop_status_store" type="radio" value="store" '. ('store' == $bp_shop['slug'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_status_store">'. __('store', 'bp_shop') .'</label></p>
            <p><input name="bp_shop_slug" id="bp_shop_status_shop" type="radio" value="shop" '. ('shop' == $bp_shop['slug'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_status_shop">'. __('shop', 'bp_shop') .'</label></p>
            <p><input name="bp_shop_slug" id="bp_shop_status_market" type="radio" value="market" '. ('market' == $bp_shop['slug'] ? 'checked="checked" ' : '') .'/> <label for="bp_shop_status_market">'. __('market', 'bp_shop') .'</label></p>';

	}

	function on_bp_shop_admin_debug($bp_shop){
		echo '<p><strong>Main:</strong>';
		print_var($bp_shop);
		echo '</p>';
	}
	
}