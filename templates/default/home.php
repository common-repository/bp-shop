<?php
get_header();
global $bp;
$bp_gtm = get_option('bp_gtm');
?>

	<div id="content">
		<div class="padder">

			<?php do_action( 'bp_before_member_home_content' ); ?>

			<div id="item-header" role="complementary">

				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>

			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
					<ul>

						<?php bp_get_displayed_user_nav(); ?>

						<?php do_action( 'bp_member_options_nav' ); ?>

					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body">

				<?php
					do_action( 'bp_before_shop_body' );

					if (bp_is_current_action ('stat')){
						include BP_SHOP_THEME_DIR. '/default/stat.php';
					}elseif (bp_is_current_action ('goods')){
						include BP_SHOP_THEME_DIR. '/default/goods.php';
					}elseif (bp_is_current_action ('wish')){
						include BP_SHOP_THEME_DIR. '/default/wish.php';
					}elseif (bp_is_current_action ('settings')){
						include BP_SHOP_THEME_DIR. '/default/settings.php';
					}

					do_action( 'bp_after_shop_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'bp_after_member_home_content' ); ?>

		</div><!-- .padder -->
	</div><!-- #content -->






<?php
locate_template( array( 'sidebar.php' ), true );

get_footer();
?>