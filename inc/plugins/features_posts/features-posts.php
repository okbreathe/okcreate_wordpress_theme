<?php

/*
 * Plugin Name: 	Featured Posts and Custom Posts
 * Plugin URI: 		http://okbreathe.com
 * Description: 	Allows the user to feature posts and custom posts. When a post is featured it gets the post meta _featured
 * Version: 		1.0
 * Author:        	okbreathe
 * Author URI:   	http://okbreathe.com
 *
 * License:       	GNU General Public License, v2 (or newer)
 * License URI:  	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */

// USAGE
// ======
//
// $args = array(
//   'post_type'    => 'any', // Or whatever post type you want to limit it to
//   'post_status'  => 'publish',
//   'numberposts'  => -1,
//   'meta_key'     => '_featured',
//   'meta_value'   => 'yes',
// );
// $query = new WP_Query($args);
// $posts =  $query->posts;

  function install_okb_featured_posts() {			
    if ( empty( unserialize( get_option( 'okbFeaturedPosts' ) ) ) ){
      $option = array( 'installed' => 'yes', 'okbFeaturedPosts' => array( ) );            
      add_option( 'okbFeaturedPosts', serialize( $option ), '', 'yes' ); 
    } 
  } 
  register_activation_hook( __FILE__,'install_okb_featured_posts' );

  // Add featured column
  function add_okb_featured_column( $columns ){
    return array_merge( $columns, array('featured_okb_posts' => __('Featured')) );
  } 

  // Add content to the new column
  function add_okb_featured_post_column_content( $col, $id ){
    if ( $col == 'featured_okb_posts' ){
      $class = empty( get_post_meta( $id, '_featured', true ) ) ? '' : ' selected';
      echo  '<a id="postFeatured_'.$id.'" class="featured_posts_star'.$class.'"></a>';            
    }
  } 

  function okb_featured_posts_get_and_loop_through_post_types(){
    $post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names' );

    foreach ($post_types as $post_type ) {            
      add_filter('manage_'.$post_type.'_posts_columns', 'add_okb_featured_column');
      add_action('manage_'.$post_type.'_posts_custom_column', 'add_okb_featured_post_column_content', 10, 2);            
    }
  } 
  add_action('admin_init','okb_featured_posts_get_and_loop_through_post_types');

  // Add styles and ajax functionality for saving updated values
  function okb_featured_posts_admin_head(){
    ?>
    <style type='text/css'>
      #featured_okb_posts, .column-featured_okb_posts{ width:100px; text-align: center !important; }
      .featured_posts_star{ display:block; height:24px; width:24px; margin:8px auto 0 auto; border:none; cursor:pointer; color: #888; }
      .featured_posts_star:before{ content: "\2713 "; font-size: 2em; }
      .featured_posts_star.selected, .featured_posts_star:active{ color: #21759B; }
    </style>
    <?php

    // Save the meta option for this post when the link is clicked
    if ( current_user_can("administrator") ){ ?>					
      <script type="text/javascript" language="javascript">                
        jQuery(document).ready(function(){                
          jQuery('.featured_posts_star').click(function() {
            var selected = 'yes';
            if ( jQuery(this).hasClass( 'selected' ) ){ 
              jQuery(this).removeClass( 'selected' );
              selected = 'no'; 
            } else { jQuery(this).addClass( 'selected' ); }                        
              var tempID = jQuery(this).attr( 'id' ).split( '_' );
              jQuery.post( ajaxurl, 'action=jsfeatured_posts&post='+tempID[1]+'&okbFeaturedPost='+selected ); 
          }); 
        }); 
      </script> <?php
    }
  } 
  add_action('admin_head','okb_featured_posts_admin_head');

  // Ajax call handler
  function okb_featured_posts_link_add_ajax_call_to_wp(){		
    $post = $_POST['okbFeaturedPost'];
    $id   = (int)$_POST["post"];

    if( !empty( $id ) && $post !== NULL ) {
      if ( $post == 'no' ){ 
        delete_post_meta( $id, "_featured" ); 
      } else { 
        add_post_meta( $id, "_featured", 'yes' ); 
      }
    } 
    exit;
  } 
  add_action('wp_ajax_jsfeatured_posts', 'okb_featured_posts_link_add_ajax_call_to_wp');

  function remove_okb_featured_posts() {         
    delete_option( 'okbFeaturedPosts' );
  } 
  register_deactivation_hook( __FILE__, 'remove_okb_featured_posts' );
?>
