<?php
/*
 * Filter to use template files in plugin's directory
 */
function bp_shop_load_template_filter( $found_template, $templates ) {
    global $bp;

    if ( $bp->current_action == $bp->shop->slug || $bp->current_component == $bp->shop->slug ) {
      foreach ( (array) $templates as $template ) {
         if ( file_exists( STYLESHEETPATH . '/' . $template ) )
            $filtered_templates[] = STYLESHEETPATH . '/' . $template;
         else
            $filtered_templates[] = dirname( __FILE__ ) . '/templates/' . $template;
      }

      $found_template = $filtered_templates[0];

      return apply_filters( 'bp_shop_load_template_filter', $found_template );
   }else{
      return $found_template;
   }
}
add_filter( 'bp_located_template', 'bp_shop_load_template_filter', 10, 2 );
