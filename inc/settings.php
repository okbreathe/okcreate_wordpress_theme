<?php
/**
 * http://wordpress.org/extend/plugins/settings-api/
 */

require( get_template_directory() . '/inc/settings-api/class.settings-api.php' );


/**
 * Registers settings section and fields
 */
function okb_admin_init() {

    $sections = array(
        array(
            'id' => 'site_general',
            'title' => __( 'Site Settings', 'okb' )
        ),
    );

    $fields = array(
        'site_general' => array(
            array(
                'name'    => 'db_name',
                'label'   => __( 'Label', 'okb' ),
                'desc'    => __( '', 'okb' ),
                'type'    => 'text',
                'default' => '',
            ),
            array(
                'name'    => 'db_name',
                'label'   => __( 'Label', 'okb' ),
                'desc'    => __( '', 'okb' ),
                'type'    => 'select',
                'default' => '',
                'options' => page_options()
            ),
        ),
    );

    $settings_api = WeDevs_Settings_API::getInstance();

    //set sections and fields
    $settings_api->set_sections( $sections );
    $settings_api->set_fields( $fields );

    //initialize them
    $settings_api->admin_init();
}

add_action( 'admin_init', 'okb_admin_init' );

function cat_options($tax_name){
  $cats = get_categories(array('taxonomy' => $tax_name));
  $ret  = array('' => '');
  foreach ($cats as $cat) {
    $ret[$cat->term_id] =  $cat->name;
  }
  return $ret;
}

function page_options() {
  $pages = get_pages(); 
  $ret  = array('' => '');
  foreach ($pages as $page) {
    $ret[$page->ID] =  $page->post_title;
  }
  return $ret;
}

function menu_options($menu_name = 'primary'){
  $ret  = array('' => '');

  if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) ) {
    $menu = wp_get_nav_menu_object( $locations[ $menu_name ] );
    $menu_items = wp_get_nav_menu_items($menu->term_id);
    foreach ( (array) $menu_items as $key => $menu_item ) {
      if ($menu_item->title != 'Spacer') {
        $ret[$menu_item->url] = $menu_item->title;
      }
    }
  }
  return $ret;
}

function post_options($type = 'post'){
  $args = array('post_type' => $type, 'posts_per_page' => -1);
  $posts = get_posts($args); 
  $ret  = array('' => '');
  foreach ($posts as $post) {
    $ret[$post->ID] =  $post->post_title;
  }
  return $ret;
}
/**
 * Register the plugin page
 * http://codex.wordpress.org/Function_Reference/add_options_page
 */
function okb_admin_menu() {
  add_options_page( 'CHANGEME', 'CHANGEME', 'delete_posts', 'site_settings', 'site_settings_page' );
}

add_action( 'admin_menu', 'okb_admin_menu' );

/**
 * Display the plugin settings options page
 */
function site_settings_page() {
  $settings_api = WeDevs_Settings_API::getInstance();

  echo '<div class="wrap">';

  settings_errors();

  $settings_api->show_navigation();
  $settings_api->show_forms();

  echo '</div>';
}


/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 * @return mixed
 */
function okb_get_option( $option, $section, $default = '' ) {
 
    $options = get_option( $section );
 
    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }
 
    return $default;
}

