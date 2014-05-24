<?php
/**
 * okb functions and definitions
 *
 * @package okb
 * @since okb 1.0
 */

if ( ! function_exists( 'okb_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @since okb 1.0
 */
function okb_setup() {

	/**
	 * Improved var_dump. Usage krumo($obj);
	 */
  require( get_template_directory() . '/inc/krumo/class.krumo.php' );

	/**
	 * Custom template tags for this theme.
	 */
	require( get_template_directory() . '/inc/template-tags.php' );

	/**
	 * Custom helpers for this theme.
	 */
	require( get_template_directory() . '/inc/helpers.php' );

	/**
	 * Site Customizations
	 */
  require( get_template_directory() . '/inc/settings.php' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Enable support for Post Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'okb' ),
	) );

	/**
	 * Add support for the Aside Post Formats
	 */
	add_theme_support( 'post-formats', array( 'aside', ) );
}
endif; // okb_setup
add_action( 'after_setup_theme', 'okb_setup' );

/**
 * Register widgetized area and update sidebar with default widgets
 *
 * @since okb 1.0
 */
function okb_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Sidebar', 'okb' ),
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );
}
add_action( 'widgets_init', 'okb_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function okb_scripts() {
	global $post;

	wp_enqueue_style( 'grid_12', get_template_directory_uri() . '/css/grid_12.css' );
	wp_enqueue_style( 'normalize', get_template_directory_uri() . '/css/normalize.css' );
	wp_enqueue_style( 'style', get_stylesheet_uri() );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_enqueue_script( 'application', get_template_directory_uri() . '/js/application.js', array(), false, true );

}

add_action( 'wp_enqueue_scripts', 'okb_scripts' );

