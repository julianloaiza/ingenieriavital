<?php
/**
 * Theme functions and definitions.
 *
 * Sets up the theme and provides some helper functions
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 *
 * For more information on hooks, actions, and filters,
 * see http://codex.wordpress.org/Plugin_API
 *
 * @package OceanWP WordPress theme
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Core Constants.
define( 'OCEANWP_THEME_DIR', get_template_directory() );
define( 'OCEANWP_THEME_URI', get_template_directory_uri() );

/**
 * OceanWP theme class
 */
final class OCEANWP_Theme_Class {

	/**
	 * Main Theme Class Constructor
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		// Define theme constants.
		$this->oceanwp_constants();

		// Load required files.
		$this->oceanwp_has_setup();

		// Load framework classes.
		add_action( 'after_setup_theme', array( 'OCEANWP_Theme_Class', 'classes' ), 4 );

		// Setup theme => add_theme_support, register_nav_menus, load_theme_textdomain, etc.
		add_action( 'after_setup_theme', array( 'OCEANWP_Theme_Class', 'theme_setup' ), 10 );

		// Setup theme => Generate the custom CSS file.
		add_action( 'admin_bar_init', array( 'OCEANWP_Theme_Class', 'save_customizer_css_in_file' ), 9999 );

		// register sidebar widget areas.
		add_action( 'widgets_init', array( 'OCEANWP_Theme_Class', 'register_sidebars' ) );

		// Registers theme_mod strings into Polylang.
		if ( class_exists( 'Polylang' ) ) {
			add_action( 'after_setup_theme', array( 'OCEANWP_Theme_Class', 'polylang_register_string' ) );
		}

		/** Admin only actions */
		if ( is_admin() ) {

			// Load scripts in the WP admin.
			add_action( 'admin_enqueue_scripts', array( 'OCEANWP_Theme_Class', 'admin_scripts' ) );

			// Outputs custom CSS for the admin.
			add_action( 'admin_head', array( 'OCEANWP_Theme_Class', 'admin_inline_css' ) );

			/** Non Admin actions */
		} else {

			// Load theme CSS.
			add_action( 'wp_enqueue_scripts', array( 'OCEANWP_Theme_Class', 'theme_css' ) );

			// Load his file in last.
			add_action( 'wp_enqueue_scripts', array( 'OCEANWP_Theme_Class', 'custom_style_css' ), 9999 );

			// Remove Customizer CSS script from Front-end.
			add_action( 'init', array( 'OCEANWP_Theme_Class', 'remove_customizer_custom_css' ) );

			// Load theme js.
			add_action( 'wp_enqueue_scripts', array( 'OCEANWP_Theme_Class', 'theme_js' ) );

			// Add a pingback url auto-discovery header for singularly identifiable articles.
			add_action( 'wp_head', array( 'OCEANWP_Theme_Class', 'pingback_header' ), 1 );

			// Add meta viewport tag to header.
			add_action( 'wp_head', array( 'OCEANWP_Theme_Class', 'meta_viewport' ), 1 );

			// Add an X-UA-Compatible header.
			add_filter( 'wp_headers', array( 'OCEANWP_Theme_Class', 'x_ua_compatible_headers' ) );

			// Loads html5 shiv script.
			add_action( 'wp_head', array( 'OCEANWP_Theme_Class', 'html5_shiv' ) );

			// Outputs custom CSS to the head.
			add_action( 'wp_head', array( 'OCEANWP_Theme_Class', 'custom_css' ), 9999 );

			// Minify the WP custom CSS because WordPress doesn't do it by default.
			add_filter( 'wp_get_custom_css', array( 'OCEANWP_Theme_Class', 'minify_custom_css' ) );

			// Alter the search posts per page.
			add_action( 'pre_get_posts', array( 'OCEANWP_Theme_Class', 'search_posts_per_page' ) );

			// Alter WP categories widget to display count inside a span.
			add_filter( 'wp_list_categories', array( 'OCEANWP_Theme_Class', 'wp_list_categories_args' ) );

			// Add a responsive wrapper to the WordPress oembed output.
			add_filter( 'embed_oembed_html', array( 'OCEANWP_Theme_Class', 'add_responsive_wrap_to_oembeds' ), 99, 4 );

			// Adds classes the post class.
			add_filter( 'post_class', array( 'OCEANWP_Theme_Class', 'post_class' ) );

			// Add schema markup to the authors post link.
			add_filter( 'the_author_posts_link', array( 'OCEANWP_Theme_Class', 'the_author_posts_link' ) );

			// Add support for Elementor Pro locations.
			add_action( 'elementor/theme/register_locations', array( 'OCEANWP_Theme_Class', 'register_elementor_locations' ) );

			// Remove the default lightbox script for the beaver builder plugin.
			add_filter( 'fl_builder_override_lightbox', array( 'OCEANWP_Theme_Class', 'remove_bb_lightbox' ) );

		}

	}

	/**
	 * Define Constants
	 *
	 * @since   1.0.0
	 */
	public static function oceanwp_constants() {

		$version = self::theme_version();

		// Theme version.
		define( 'OCEANWP_THEME_VERSION', $version );

		// Javascript and CSS Paths.
		define( 'OCEANWP_JS_DIR_URI', OCEANWP_THEME_URI . '/assets/js/' );
		define( 'OCEANWP_CSS_DIR_URI', OCEANWP_THEME_URI . '/assets/css/' );

		// Include Paths.
		define( 'OCEANWP_INC_DIR', OCEANWP_THEME_DIR . '/inc/' );
		define( 'OCEANWP_INC_DIR_URI', OCEANWP_THEME_URI . '/inc/' );

		// Check if plugins are active.
		define( 'OCEAN_EXTRA_ACTIVE', class_exists( 'Ocean_Extra' ) );
		define( 'OCEANWP_ELEMENTOR_ACTIVE', class_exists( 'Elementor\Plugin' ) );
		define( 'OCEANWP_BEAVER_BUILDER_ACTIVE', class_exists( 'FLBuilder' ) );
		define( 'OCEANWP_WOOCOMMERCE_ACTIVE', class_exists( 'WooCommerce' ) );
		define( 'OCEANWP_EDD_ACTIVE', class_exists( 'Easy_Digital_Downloads' ) );
		define( 'OCEANWP_LIFTERLMS_ACTIVE', class_exists( 'LifterLMS' ) );
		define( 'OCEANWP_ALNP_ACTIVE', class_exists( 'Auto_Load_Next_Post' ) );
		define( 'OCEANWP_LEARNDASH_ACTIVE', class_exists( 'SFWD_LMS' ) );
	}

	/**
	 * Load all core theme function files
	 *
	 * @since 1.0.0oceanwp_has_setup
	 */
	public static function oceanwp_has_setup() {

		$dir = OCEANWP_INC_DIR;

		require_once $dir . 'helpers.php';
		require_once $dir . 'header-content.php';
		require_once $dir . 'oceanwp-strings.php';
		require_once $dir . 'oceanwp-theme-icons.php';
		require_once $dir . 'customizer/controls/typography/webfonts.php';
		require_once $dir . 'walker/init.php';
		require_once $dir . 'walker/menu-walker.php';
		require_once $dir . 'third/class-gutenberg.php';
		require_once $dir . 'third/class-elementor.php';
		require_once $dir . 'third/class-beaver-themer.php';
		require_once $dir . 'third/class-bbpress.php';
		require_once $dir . 'third/class-buddypress.php';
		require_once $dir . 'third/class-lifterlms.php';
		require_once $dir . 'third/class-learndash.php';
		require_once $dir . 'third/class-sensei.php';
		require_once $dir . 'third/class-social-login.php';
		require_once $dir . 'third/class-amp.php';
		require_once $dir . 'third/class-pwa.php';

		// WooCommerce.
		if ( OCEANWP_WOOCOMMERCE_ACTIVE ) {
			require_once $dir . 'woocommerce/woocommerce-config.php';
		}

		// Easy Digital Downloads.
		if ( OCEANWP_EDD_ACTIVE ) {
			require_once $dir . 'edd/edd-config.php';
		}

	}

	/**
	 * Returns current theme version
	 *
	 * @since   1.0.0
	 */
	public static function theme_version() {

		// Get theme data.
		$theme = wp_get_theme();

		// Return theme version.
		return $theme->get( 'Version' );

	}

	/**
	 * Compare WordPress version
	 *
	 * @access public
	 * @since 1.8.3
	 * @param  string $version - A WordPress version to compare against current version.
	 * @return boolean
	 */
	public static function is_wp_version( $version = '5.4' ) {

		global $wp_version;

		// WordPress version.
		return version_compare( strtolower( $wp_version ), strtolower( $version ), '>=' );

	}


	/**
	 * Check for AMP endpoint
	 *
	 * @return bool
	 * @since 1.8.7
	 */
	public static function oceanwp_is_amp() {
		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
	}

	/**
	 * Load theme classes
	 *
	 * @since   1.0.0
	 */
	public static function classes() {

		// Admin only classes.
		if ( is_admin() ) {

			// Recommend plugins.
			require_once OCEANWP_INC_DIR . 'plugins/class-tgm-plugin-activation.php';
			require_once OCEANWP_INC_DIR . 'plugins/tgm-plugin-activation.php';

			// Front-end classes.
		} else {

			// Breadcrumbs class.
			require_once OCEANWP_INC_DIR . 'breadcrumbs.php';

		}

		// Customizer class.
		require_once OCEANWP_INC_DIR . 'customizer/customizer.php';

	}

	/**
	 * Theme Setup
	 *
	 * @since   1.0.0
	 */
	public static function theme_setup() {

		// Load text domain.
		load_theme_textdomain( 'oceanwp', OCEANWP_THEME_DIR . '/languages' );

		// Get globals.
		global $content_width;

		// Set content width based on theme's default design.
		if ( ! isset( $content_width ) ) {
			$content_width = 1200;
		}

		// Register navigation menus.
		register_nav_menus(
			array(
				'topbar_menu' => esc_html__( 'Top Bar', 'oceanwp' ),
				'main_menu'   => esc_html__( 'Main', 'oceanwp' ),
				'footer_menu' => esc_html__( 'Footer', 'oceanwp' ),
				'mobile_menu' => esc_html__( 'Mobile (optional)', 'oceanwp' ),
			)
		);

		// Enable support for Post Formats.
		add_theme_support( 'post-formats', array( 'video', 'gallery', 'audio', 'quote', 'link' ) );

		// Enable support for <title> tag.
		add_theme_support( 'title-tag' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		// Enable support for Post Thumbnails on posts and pages.
		add_theme_support( 'post-thumbnails' );

		/**
		 * Enable support for header image
		 */
		add_theme_support(
			'custom-header',
			apply_filters(
				'ocean_custom_header_args',
				array(
					'width'       => 2000,
					'height'      => 1200,
					'flex-height' => true,
					'video'       => true,
				)
			)
		);

		/**
		 * Enable support for site logo
		 */
		add_theme_support(
			'custom-logo',
			apply_filters(
				'ocean_custom_logo_args',
				array(
					'height'      => 45,
					'width'       => 164,
					'flex-height' => true,
					'flex-width'  => true,
				)
			)
		);

		/*
		 * Switch default core markup for search form, comment form, comments, galleries, captions and widgets
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'widgets',
			)
		);

		// Declare WooCommerce support.
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		// Add editor style.
		add_editor_style( 'assets/css/editor-style.min.css' );

		// Declare support for selective refreshing of widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

	}

	/**
	 * Adds the meta tag to the site header
	 *
	 * @since 1.1.0
	 */
	public static function pingback_header() {

		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
		}

	}

	/**
	 * Adds the meta tag to the site header
	 *
	 * @since 1.0.0
	 */
	public static function meta_viewport() {

		// Meta viewport.
		$viewport = '<meta name="viewport" content="width=device-width, initial-scale=1">';

		// Apply filters for child theme tweaking.
		echo apply_filters( 'ocean_meta_viewport', $viewport ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}

	/**
	 * Load scripts in the WP admin
	 *
	 * @since 1.0.0
	 */
	public static function admin_scripts() {
		global $pagenow;
		if ( 'nav-menus.php' === $pagenow ) {
			wp_enqueue_style( 'oceanwp-menus', OCEANWP_INC_DIR_URI . 'walker/assets/menus.css', false, OCEANWP_THEME_VERSION );
		}
	}

	/**
	 * Load front-end scripts
	 *
	 * @since   1.0.0
	 */
	public static function theme_css() {

		// Define dir.
		$dir           = OCEANWP_CSS_DIR_URI;
		$theme_version = OCEANWP_THEME_VERSION;

		// Remove font awesome style from plugins.
		wp_deregister_style( 'font-awesome' );
		wp_deregister_style( 'fontawesome' );

		// Load font awesome style.
		wp_enqueue_style( 'font-awesome', OCEANWP_THEME_URI . '/assets/fonts/fontawesome/css/all.min.css', false, '5.15.1' );

		// Register simple line icons style.
		wp_enqueue_style( 'simple-line-icons', $dir . 'third/simple-line-icons.min.css', false, '2.4.0' );

		// Register the lightbox style.
		wp_enqueue_style( 'magnific-popup', $dir . 'third/magnific-popup.min.css', false, '1.0.0' );

		// Register the slick style.
		wp_enqueue_style( 'slick', $dir . 'third/slick.min.css', false, '1.6.0' );

		// Main Style.css File.
		wp_enqueue_style( 'oceanwp-style', $dir . 'style.min.css', false, $theme_version );

		// Register hamburgers buttons to easily use them.
		wp_register_style( 'oceanwp-hamburgers', $dir . 'third/hamburgers/hamburgers.min.css', false, $theme_version );

		// Register hamburgers buttons styles.
		$hamburgers = oceanwp_hamburgers_styles();
		foreach ( $hamburgers as $class => $name ) {
			wp_register_style( 'oceanwp-' . $class . '', $dir . 'third/hamburgers/types/' . $class . '.css', false, $theme_version );
		}

		// Get mobile menu icon style.
		$mobileMenu = get_theme_mod( 'ocean_mobile_menu_open_hamburger', 'default' );

		// Enqueue mobile menu icon style.
		if ( ! empty( $mobileMenu ) && 'default' !== $mobileMenu ) {
			wp_enqueue_style( 'oceanwp-hamburgers' );
			wp_enqueue_style( 'oceanwp-' . $mobileMenu . '' );
		}

		// If Vertical header style.
		if ( 'vertical' === oceanwp_header_style() ) {
			wp_enqueue_style( 'oceanwp-hamburgers' );
			wp_enqueue_style( 'oceanwp-spin' );
		}

	}

	/**
	 * Returns all js needed for the front-end
	 *
	 * @since 1.0.0
	 */
	public static function theme_js() {

		if ( self::oceanwp_is_amp() ) {
			return;
		}

		// Get js directory uri.
		$dir = OCEANWP_JS_DIR_URI;

		// Get current theme version.
		$theme_version = OCEANWP_THEME_VERSION;

		// Get localized array.
		$localize_array = self::localize_array();

		// Comment reply.
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Add images loaded.
		wp_enqueue_script( 'imagesloaded' );

		// Register nicescroll script to use it in some extensions.
		wp_register_script( 'nicescroll', $dir . 'third/nicescroll.min.js', array( 'jquery' ), $theme_version, true );

		// Enqueue nicescroll script if vertical header style.
		if ( 'vertical' === oceanwp_header_style() ) {
			wp_enqueue_script( 'nicescroll' );
		}

		// Register Infinite Scroll script.
		wp_register_script( 'infinitescroll', $dir . 'third/infinitescroll.min.js', array( 'jquery' ), $theme_version, true );

		// WooCommerce scripts.
		if ( OCEANWP_WOOCOMMERCE_ACTIVE
			&& 'yes' !== get_theme_mod( 'ocean_woo_remove_custom_features', 'no' ) ) {
			wp_enqueue_script( 'oceanwp-woocommerce', $dir . 'third/woo/woo-scripts.min.js', array( 'jquery' ), $theme_version, true );
		}

		// Load the lightbox scripts.
		wp_enqueue_script( 'magnific-popup', $dir . 'third/magnific-popup.min.js', array( 'jquery' ), $theme_version, true );
		wp_enqueue_script( 'oceanwp-lightbox', $dir . 'third/lightbox.min.js', array( 'jquery' ), $theme_version, true );

		// Load minified js.
		wp_enqueue_script( 'oceanwp-main', $dir . 'main.min.js', array( 'jquery' ), $theme_version, true );

		// Localize array.
		wp_localize_script( 'oceanwp-main', 'oceanwpLocalize', $localize_array );

	}

	/**
	 * Functions.js localize array
	 *
	 * @since 1.0.0
	 */
	public static function localize_array() {

		// Create array.
		$sidr_side   = get_theme_mod( 'ocean_mobile_menu_sidr_direction', 'left' );
		$sidr_side   = $sidr_side ? $sidr_side : 'left';
		$sidr_target = get_theme_mod( 'ocean_mobile_menu_sidr_dropdown_target', 'link' );
		$sidr_target = $sidr_target ? $sidr_target : 'link';
		$vh_target   = get_theme_mod( 'ocean_vertical_header_dropdown_target', 'link' );
		$vh_target   = $vh_target ? $vh_target : 'link';
		$array       = array(
			'isRTL'                => is_rtl(),
			'menuSearchStyle'      => oceanwp_menu_search_style(),
			'sidrSource'           => oceanwp_sidr_menu_source(),
			'sidrDisplace'         => get_theme_mod( 'ocean_mobile_menu_sidr_displace', true ) ? true : false,
			'sidrSide'             => $sidr_side,
			'sidrDropdownTarget'   => $sidr_target,
			'verticalHeaderTarget' => $vh_target,
			'customSelects'        => '.woocommerce-ordering .orderby, #dropdown_product_cat, .widget_categories select, .widget_archive select, .single-product .variations_form .variations select',
		);

		// WooCart.
		if ( OCEANWP_WOOCOMMERCE_ACTIVE ) {
			$array['wooCartStyle'] = oceanwp_menu_cart_style();
		}

		// Apply filters and return array.
		return apply_filters( 'ocean_localize_array', $array );
	}

	/**
	 * Add headers for IE to override IE's Compatibility View Settings
	 *
	 * @param obj $headers   header settings.
	 * @since 1.0.0
	 */
	public static function x_ua_compatible_headers( $headers ) {
		$headers['X-UA-Compatible'] = 'IE=edge';
		return $headers;
	}

	/**
	 * Load HTML5 dependencies for IE8
	 *
	 * @since 1.0.0
	 */
	public static function html5_shiv() {
		wp_register_script( 'html5shiv', OCEANWP_JS_DIR_URI . 'third/html5.min.js', array(), OCEANWP_THEME_VERSION, false );
		wp_enqueue_script( 'html5shiv' );
		wp_script_add_data( 'html5shiv', 'conditional', 'lt IE 9' );
	}

	/**
	 * Registers sidebars
	 *
	 * @since   1.0.0
	 */
	public static function register_sidebars() {

		$heading = 'h4';
		$heading = apply_filters( 'ocean_sidebar_heading', $heading );

		// Default Sidebar.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Default Sidebar', 'oceanwp' ),
				'id'            => 'sidebar',
				'description'   => esc_html__( 'Widgets in this area will be displayed in the left or right sidebar area if you choose the Left or Right Sidebar layout.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="sidebar-box %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $heading . ' class="widget-title">',
				'after_title'   => '</' . $heading . '>',
			)
		);

		// Left Sidebar.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Left Sidebar', 'oceanwp' ),
				'id'            => 'sidebar-2',
				'description'   => esc_html__( 'Widgets in this area are used in the left sidebar region if you use the Both Sidebars layout.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="sidebar-box %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $heading . ' class="widget-title">',
				'after_title'   => '</' . $heading . '>',
			)
		);

		// Search Results Sidebar.
		if ( get_theme_mod( 'ocean_search_custom_sidebar', true ) ) {
			register_sidebar(
				array(
					'name'          => esc_html__( 'Search Results Sidebar', 'oceanwp' ),
					'id'            => 'search_sidebar',
					'description'   => esc_html__( 'Widgets in this area are used in the search result page.', 'oceanwp' ),
					'before_widget' => '<div id="%1$s" class="sidebar-box %2$s clr">',
					'after_widget'  => '</div>',
					'before_title'  => '<' . $heading . ' class="widget-title">',
					'after_title'   => '</' . $heading . '>',
				)
			);
		}

		// Footer 1.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer 1', 'oceanwp' ),
				'id'            => 'footer-one',
				'description'   => esc_html__( 'Widgets in this area are used in the first footer region.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="footer-widget %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $heading . ' class="widget-title">',
				'after_title'   => '</' . $heading . '>',
			)
		);

		// Footer 2.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer 2', 'oceanwp' ),
				'id'            => 'footer-two',
				'description'   => esc_html__( 'Widgets in this area are used in the second footer region.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="footer-widget %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $heading . ' class="widget-title">',
				'after_title'   => '</' . $heading . '>',
			)
		);

		// Footer 3.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer 3', 'oceanwp' ),
				'id'            => 'footer-three',
				'description'   => esc_html__( 'Widgets in this area are used in the third footer region.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="footer-widget %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $heading . ' class="widget-title">',
				'after_title'   => '</' . $heading . '>',
			)
		);

		// Footer 4.
		register_sidebar(
			array(
				'name'          => esc_html__( 'Footer 4', 'oceanwp' ),
				'id'            => 'footer-four',
				'description'   => esc_html__( 'Widgets in this area are used in the fourth footer region.', 'oceanwp' ),
				'before_widget' => '<div id="%1$s" class="footer-widget %2$s clr">',
				'after_widget'  => '</div>',
				'before_title'  => '<' . $heading . ' class="widget-title">',
				'after_title'   => '</' . $heading . '>',
			)
		);

	}

	/**
	 * Registers theme_mod strings into Polylang.
	 *
	 * @since 1.1.4
	 */
	public static function polylang_register_string() {

		if ( function_exists( 'pll_register_string' ) && $strings = oceanwp_register_tm_strings() ) {
			foreach ( $strings as $string => $default ) {
				pll_register_string( $string, get_theme_mod( $string, $default ), 'Theme Mod', true );
			}
		}

	}

	/**
	 * All theme functions hook into the oceanwp_head_css filter for this function.
	 *
	 * @param obj $output output value.
	 * @since 1.0.0
	 */
	public static function custom_css( $output = null ) {

		// Add filter for adding custom css via other functions.
		$output = apply_filters( 'ocean_head_css', $output );

		// If Custom File is selected.
		if ( 'file' === get_theme_mod( 'ocean_customzer_styling', 'head' ) ) {

			global $wp_customize;
			$upload_dir = wp_upload_dir();

			// Render CSS in the head.
			if ( isset( $wp_customize ) || ! file_exists( $upload_dir['basedir'] . '/oceanwp/custom-style.css' ) ) {

				// Minify and output CSS in the wp_head.
				if ( ! empty( $output ) ) {
					echo "<!-- OceanWP CSS -->\n<style type=\"text/css\">\n" . wp_strip_all_tags( oceanwp_minify_css( $output ) ) . "\n</style>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		} else {

			// Minify and output CSS in the wp_head.
			if ( ! empty( $output ) ) {
				echo "<!-- OceanWP CSS -->\n<style type=\"text/css\">\n" . wp_strip_all_tags( oceanwp_minify_css( $output ) ) . "\n</style>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

	}

	/**
	 * Minify the WP custom CSS because WordPress doesn't do it by default.
	 *
	 * @param obj $css minify css.
	 * @since 1.1.9
	 */
	public static function minify_custom_css( $css ) {

		return oceanwp_minify_css( $css );

	}

	/**
	 * Save Customizer CSS in a file
	 *
	 * @param obj $output output value.
	 * @since 1.4.12
	 */
	public static function save_customizer_css_in_file( $output = null ) {

		// If Custom File is not selected.
		if ( 'file' !== get_theme_mod( 'ocean_customzer_styling', 'head' ) ) {
			return;
		}

		// Get all the customier css.
		$output = apply_filters( 'ocean_head_css', $output );

		// Get Custom Panel CSS.
		$output_custom_css = wp_get_custom_css();

		// Minified the Custom CSS.
		$output .= oceanwp_minify_css( $output_custom_css );

		// We will probably need to load this file.
		require_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'file.php';

		global $wp_filesystem;
		$upload_dir = wp_upload_dir(); // Grab uploads folder array.
		$dir        = trailingslashit( $upload_dir['basedir'] ) . 'oceanwp' . DIRECTORY_SEPARATOR; // Set storage directory path.

		WP_Filesystem(); // Initial WP file system.
		$wp_filesystem->mkdir( $dir ); // Make a new folder 'oceanwp' for storing our file if not created already.
		$wp_filesystem->put_contents( $dir . 'custom-style.css', $output, 0644 ); // Store in the file.

	}

	/**
	 * Include Custom CSS file if present.
	 *
	 * @param obj $output output value.
	 * @since 1.4.12
	 */
	public static function custom_style_css( $output = null ) {

		// If Custom File is not selected.
		if ( 'file' !== get_theme_mod( 'ocean_customzer_styling', 'head' ) ) {
			return;
		}

		global $wp_customize;
		$upload_dir = wp_upload_dir();

		// Get all the customier css.
		$output = apply_filters( 'ocean_head_css', $output );

		// Get Custom Panel CSS.
		$output_custom_css = wp_get_custom_css();

		// Minified the Custom CSS.
		$output .= oceanwp_minify_css( $output_custom_css );

		// Render CSS from the custom file.
		if ( ! isset( $wp_customize ) && file_exists( $upload_dir['basedir'] . '/oceanwp/custom-style.css' ) && ! empty( $output ) ) {
			wp_enqueue_style( 'oceanwp-custom', trailingslashit( $upload_dir['baseurl'] ) . 'oceanwp/custom-style.css', false, false );
		}
	}

	/**
	 * Remove Customizer style script from front-end
	 *
	 * @since 1.4.12
	 */
	public static function remove_customizer_custom_css() {

		// If Custom File is not selected.
		if ( 'file' !== get_theme_mod( 'ocean_customzer_styling', 'head' ) ) {
			return;
		}

		global $wp_customize;

		// Disable Custom CSS in the frontend head.
		remove_action( 'wp_head', 'wp_custom_css_cb', 11 );
		remove_action( 'wp_head', 'wp_custom_css_cb', 101 );

		// If custom CSS file exists and NOT in customizer screen.
		if ( isset( $wp_customize ) ) {
			add_action( 'wp_footer', 'wp_custom_css_cb', 9999 );
		}
	}

	/**
	 * Adds inline CSS for the admin
	 *
	 * @since 1.0.0
	 */
	public static function admin_inline_css() {
		echo '<style>div#setting-error-tgmpa{display:block;}</style>';
	}

	/**
	 * Alter the search posts per page
	 *
	 * @param obj $query query.
	 * @since 1.3.7
	 */
	public static function search_posts_per_page( $query ) {
		$posts_per_page = get_theme_mod( 'ocean_search_post_per_page', '8' );
		$posts_per_page = $posts_per_page ? $posts_per_page : '8';

		if ( $query->is_main_query() && is_search() ) {
			$query->set( 'posts_per_page', $posts_per_page );
		}
	}

	/**
	 * Alter wp list categories arguments.
	 * Adds a span around the counter for easier styling.
	 *
	 * @param obj $links link.
	 * @since 1.0.0
	 */
	public static function wp_list_categories_args( $links ) {
		$links = str_replace( '</a> (', '</a> <span class="cat-count-span">(', $links );
		$links = str_replace( ' )', ' )</span>', $links );
		return $links;
	}

	/**
	 * Alters the default oembed output.
	 * Adds special classes for responsive oembeds via CSS.
	 *
	 * @param obj $cache     cache.
	 * @param url $url       url.
	 * @param obj $attr      attributes.
	 * @param obj $post_ID   post id.
	 * @since 1.0.0
	 */
	public static function add_responsive_wrap_to_oembeds( $cache, $url, $attr, $post_ID ) {

		// Supported video embeds.
		$hosts = apply_filters(
			'ocean_oembed_responsive_hosts',
			array(
				'vimeo.com',
				'youtube.com',
				'blip.tv',
				'money.cnn.com',
				'dailymotion.com',
				'flickr.com',
				'hulu.com',
				'kickstarter.com',
				'vine.co',
				'soundcloud.com',
				'#http://((m|www)\.)?youtube\.com/watch.*#i',
				'#https://((m|www)\.)?youtube\.com/watch.*#i',
				'#http://((m|www)\.)?youtube\.com/playlist.*#i',
				'#https://((m|www)\.)?youtube\.com/playlist.*#i',
				'#http://youtu\.be/.*#i',
				'#https://youtu\.be/.*#i',
				'#https?://(.+\.)?vimeo\.com/.*#i',
				'#https?://(www\.)?dailymotion\.com/.*#i',
				'#https?://dai\.ly/*#i',
				'#https?://(www\.)?hulu\.com/watch/.*#i',
				'#https?://wordpress\.tv/.*#i',
				'#https?://(www\.)?funnyordie\.com/videos/.*#i',
				'#https?://vine\.co/v/.*#i',
				'#https?://(www\.)?collegehumor\.com/video/.*#i',
				'#https?://(www\.|embed\.)?ted\.com/talks/.*#i',
			)
		);

		// Supports responsive.
		$supports_responsive = false;

		// Check if responsive wrap should be added.
		foreach ( $hosts as $host ) {
			if ( strpos( $url, $host ) !== false ) {
				$supports_responsive = true;
				break; // no need to loop further.
			}
		}

		// Output code.
		if ( $supports_responsive ) {
			return '<p class="responsive-video-wrap clr">' . $cache . '</p>';
		} else {
			return '<div class="oceanwp-oembed-wrap clr">' . $cache . '</div>';
		}

	}

	/**
	 * Adds extra classes to the post_class() output
	 *
	 * @param obj $classes   Return classes.
	 * @since 1.0.0
	 */
	public static function post_class( $classes ) {

		// Get post.
		global $post;

		// Add entry class.
		$classes[] = 'entry';

		// Add has media class.
		if ( has_post_thumbnail()
			|| get_post_meta( $post->ID, 'ocean_post_oembed', true )
			|| get_post_meta( $post->ID, 'ocean_post_self_hosted_media', true )
			|| get_post_meta( $post->ID, 'ocean_post_video_embed', true )
		) {
			$classes[] = 'has-media';
		}

		// Return classes.
		return $classes;

	}

	/**
	 * Add schema markup to the authors post link
	 *
	 * @param obj $link   Author link.
	 * @since 1.0.0
	 */
	public static function the_author_posts_link( $link ) {

		// Add schema markup.
		$schema = oceanwp_get_schema_markup( 'author_link' );
		if ( $schema ) {
			$link = str_replace( 'rel="author"', 'rel="author" ' . $schema, $link );
		}

		// Return link.
		return $link;

	}

	/**
	 * Add support for Elementor Pro locations
	 *
	 * @param obj $elementor_theme_manager    Elementor Instance.
	 * @since 1.5.6
	 */
	public static function register_elementor_locations( $elementor_theme_manager ) {
		$elementor_theme_manager->register_all_core_location();
	}

	/**
	 * Add schema markup to the authors post link
	 *
	 * @since 1.1.5
	 */
	public static function remove_bb_lightbox() {
		return true;
	}

}

/**--------------------------------------------------------------------------------
#region Freemius - This logic will only be executed when Ocean Extra is active and has the Freemius SDK
---------------------------------------------------------------------------------*/

if ( ! function_exists( 'owp_fs' ) ) {
	if ( class_exists( 'Ocean_Extra' ) &&
			defined( 'OE_FILE_PATH' ) &&
			file_exists( dirname( OE_FILE_PATH ) . '/includes/freemius/start.php' )
	) {
		/**
		 * Create a helper function for easy SDK access.
		 */
		function owp_fs() {
			global $owp_fs;

			if ( ! isset( $owp_fs ) ) {
				// Include Freemius SDK.
				require_once dirname( OE_FILE_PATH ) . '/includes/freemius/start.php';

				$owp_fs = fs_dynamic_init(
					array(
						'id'                             => '3752',
						'bundle_id'                      => '3767',
						'slug'                           => 'oceanwp',
						'type'                           => 'theme',
						'public_key'                     => 'pk_043077b34f20f5e11334af3c12493',
						'bundle_public_key'              => 'pk_c334eb1ae413deac41e30bf00b9dc',
						'is_premium'                     => false,
						'has_addons'                     => true,
						'has_paid_plans'                 => true,
						'menu'                           => array(
							'slug'    => 'oceanwp-panel',
							'account' => true,
							'contact' => false,
							'support' => false,
						),
						'bundle_license_auto_activation' => true,
						'navigation'                     => 'menu',
						'is_org_compliant'               => true,
					)
				);
			}

			return $owp_fs;
		}

		// Init Freemius.
		owp_fs();
		// Signal that SDK was initiated.
		do_action( 'owp_fs_loaded' );
	}
}

function custom_department_list_dropdown() {
	$departments = array( 
	"1" => "Antioquia",
    "2" => "Atlántico",
    "3" => "Bogotá, D.C.",
    "4" => "Bolívar",
    "5" => "Boyacá",
    "6" => "Caldas",
    "7" => "Caquetá",
    "8" => "Cauca",
    "9" => "Cesar",
    "10" => "Córdoba",
    "11" => "Cundinamarca",
    "12" => "Chocó",
    "13" => "Huila",
    "14" => "La Guajira",
    "15" => "Magdalena",
    "16" => "Meta",
    "17" => "Nariño",
    "18" => "Norte De Santander",
    "19" => "Quindio",
    "20" => "Risaralda",
    "21" => "Santander",
    "22" => "Sucre",
    "23" => "Tolima",
    "24" => "Valle Del Cauca",
    "25" => "Arauca",
    "26" => "Casanare",
    "27" => "Putumayo",
    "28" => "Archipiélago De San Andrés, Providencia Y Santa Catalina",
    "29" => "Amazonas",
    "30" => "Guainía",
    "31" => "Guaviare",
    "32" => "Vaupés",
    "33" => "Vichada",
	);
	return $departments;
}

function get_cities() {
    //get the value from the 'parent' field, sent via the AJAX post.
    $choice = $_POST['parent_option'];
    
    //Depending on the value of $choice, return a different array.
    switch($choice) {
    case "1":
        $cities = array(
        "Cáceres" => "Cáceres",
        "Caucasia" => "Caucasia",
        "El Bagre" => "El Bagre",
        "Nechí" => "Nechí",
        "Tarazá" => "Tarazá",
        "Zaragoza" => "Zaragoza",
        "Caracolí" => "Caracolí",
        "Maceo" => "Maceo",
        "Puerto Berrío" => "Puerto Berrío",
        "Puerto Nare" => "Puerto Nare",
        "Puerto Triunfo" => "Puerto Triunfo",
        "Yondó" => "Yondó",
        "Amalfi" => "Amalfi",
        "Anorí" => "Anorí",
        "Cisneros" => "Cisneros",
        "Remedios" => "Remedios",
        "San Roque" => "San Roque",
        "Santo Domingo" => "Santo Domingo",
        "Segovia" => "Segovia",
        "Vegachí" => "Vegachí",
        "Yalí" => "Yalí",
        "Yolombó" => "Yolombó",
        "Angostura" => "Angostura",
        "Belmira" => "Belmira",
        "Briceó±o" => "Briceó±o",
        "Campamento" => "Campamento",
        "Carolina del Príncipe" => "Carolina del Príncipe",
        "Donmatías" => "Donmatías",
        "Entrerríos" => "Entrerríos",
        "Gómez Plata" => "Gómez Plata",
        "Guadalupe" => "Guadalupe",
        "Ituango" => "Ituango",
        "San Andrés de Cuerquia" => "San Andrés de Cuerquia",
        "San José de la Montaó±a" => "San José de la Montaó±a",
        "San Pedro de los Milagros" => "San Pedro de los Milagros",
        "Santa Rosa de Osos" => "Santa Rosa de Osos",
        "Toledo" => "Toledo",
        "Valdivia" => "Valdivia",
        "Yarumal" => "Yarumal",
        "Abriaquí" => "Abriaquí",
        "Antioquia" => "Antioquia",
        "Anzá" => "Anzá",
        "Armenia" => "Armenia",
        "Buriticá" => "Buriticá",
        "Caicedo" => "Caicedo",
        "Caó±asgordas" => "Caó±asgordas",
        "Dabeiba" => "Dabeiba",
        "Ebó©jico" => "Ebó©jico",
        "Frontino" => "Frontino",
        "Giraldo" => "Giraldo",
        "Heliconia" => "Heliconia",
        "Liborina" => "Liborina",
        "Olaya" => "Olaya",
        "Peque" => "Peque",
        "Sabanalarga" => "Sabanalarga",
        "San Jerónimo" => "San Jerónimo",
        "Sopetrán" => "Sopetrán",
        "Uramita" => "Uramita",
        "Abejorral" => "Abejorral",
        "Alejandría" => "Alejandría",
        "Argelia" => "Argelia",
        "Carmen de Viboral" => "Carmen de Viboral",
        "Cocorná" => "Cocorná",
        "Concepción" => "Concepción",
        "El Peó±ol" => "El Peó±ol",
        "El Retiro" => "El Retiro",
        "El Santuario" => "El Santuario",
        "Granada" => "Granada",
        "Guarne" => "Guarne",
        "Guatapó©" => "Guatapó©",
        "La Ceja" => "La Ceja",
        "La Unión" => "La Unión",
        "Marinilla" => "Marinilla",
        "Narió±o" => "Narió±o",
        "Rionegro" => "Rionegro",
        "San Carlos" => "San Carlos",
        "San Francisco" => "San Francisco",
        "San Luis" => "San Luis",
        "San Rafael" => "San Rafael",
        "San Vicente" => "San Vicente",
        "Sonsón" => "Sonsón",
        "Amagá" => "Amagá",
        "Andes" => "Andes",
        "Angelópolis" => "Angelópolis",
        "Betania" => "Betania",
        "Betulia" => "Betulia",
        "Caramanta" => "Caramanta",
        "Ciudad Bolívar" => "Ciudad Bolívar",
        "Concordia" => "Concordia",
        "Fredonia" => "Fredonia",
        "Hispania" => "Hispania",
        "Jardín" => "Jardín",
        "Jericó" => "Jericó",
        "La Pintada" => "La Pintada",
        "Montebello" => "Montebello",
        "Pueblorrico" => "Pueblorrico",
        "Salgar" => "Salgar",
        "Santa Bárbara" => "Santa Bárbara",
        "Támesis" => "Támesis",
        "Tarso" => "Tarso",
        "Titiribí" => "Titiribí",
        "Urrao" => "Urrao",
        "Valparaíso" => "Valparaíso",
        "Venecia" => "Venecia",
        "Apartadó" => "Apartadó",
        "Arboletes" => "Arboletes",
        "Carepa" => "Carepa",
        "Chigorodó" => "Chigorodó",
        "Murindó" => "Murindó",
        "Mutatá" => "Mutatá",
        "Necoclí" => "Necoclí",
        "San Juan de Urabá" => "San Juan de Urabá",
        "San Pedro de Urabá" => "San Pedro de Urabá",
        "Turbo" => "Turbo",
        "Vigía del Fuerte" => "Vigía del Fuerte",
        "Barbosa" => "Barbosa",
        "Bello" => "Bello",
        "Caldas" => "Caldas",
        "Copacabana" => "Copacabana",
        "Envigado" => "Envigado",
        "Girardota" => "Girardota",
        "Itaguí" => "Itaguí",
        "La Estrella" => "La Estrella",
        "Medellín" => "Medellín",
        "Sabaneta" => "Sabaneta"
    );
    break;
    case "2":
        $cities = array(
        "Barranquilla" => "Barranquilla",
        "Baranoa" => "Baranoa",
        "Campo De La Cruz" => "Campo De La Cruz",
        "Candelaria" => "Candelaria",
        "Galapa" => "Galapa",
        "Juan De Acosta" => "Juan De Acosta",
        "Luruaco" => "Luruaco",
        "Malambo" => "Malambo",
        "Manati" => "Manati",
        "Palmar De Varela" => "Palmar De Varela",
        "Piojo" => "Piojo",
        "Polonuevo" => "Polonuevo",
        "Ponedera" => "Ponedera",
        "Puerto Colombia" => "Puerto Colombia",
        "Repelon" => "Repelon",
        "Sabanagrande" => "Sabanagrande",
        "Sabanalarga" => "Sabanalarga",
        "Santa Lucia" => "Santa Lucia",
        "Santo Tomas" => "Santo Tomas",
        "Soledad" => "Soledad",
        "Suan" => "Suan",
        "Tubara" => "Tubara",
        "Usiacuri" => "Usiacuri"
    	);
    break;
    case "3":
    	$cities = array(
        "Bogotá D.C" => "Bogotá D.C"
    	);
    break;
    case "4":
    	$cities = array(
    	"Cartagena" => "Cartagena",
        "Achi" => "Achi",
        "Altos Del Rosario" => "Altos Del Rosario",
        "Arenal" => "Arenal",
        "Arjona" => "Arjona",
        "Arroyohondo" => "Arroyohondo",
        "Barranco De Loba" => "Barranco De Loba",
        "Calamar" => "Calamar",
        "Cantagallo" => "Cantagallo",
        "Cicuco" => "Cicuco",
        "Cordoba" => "Cordoba",
        "Clemencia" => "Clemencia",
        "El Carmen De Bolivar" => "El Carmen De Bolivar",
        "El Guamo" => "El Guamo",
        "El Peñon" => "El Peñon",
        "Hatillo De Loba" => "Hatillo De Loba",
        "Magangue" => "Magangue",
        "Mahates" => "Mahates",
        "Margarita" => "Margarita",
        "Maria La Baja" => "Maria La Baja",
        "Montecristo" => "Montecristo",
        "Mompos" => "Mompos",
        "Norosi" => "Norosi",
        "Morales" => "Morales",
        "Pinillos" => "Pinillos",
        "Regidor" => "Regidor",
        "Rio Viejo" => "Rio Viejo",
        "San Cristobal" => "San Cristobal",
        "San Estanislao" => "San Estanislao",
        "San Fernando" => "San Fernando",
        "San Jacinto" => "San Jacinto",
        "San Jacinto Del Cauca" => "San Jacinto Del Cauca",
        "San Juan Nepomuceno" => "San Juan Nepomuceno",
        "San Martin De Loba" => "San Martin De Loba",
        "San Pablo" => "San Pablo",
        "Santa Catalina" => "Santa Catalina",
        "Santa Rosa" => "Santa Rosa",
        "Santa Rosa Del Sur" => "Santa Rosa Del Sur",
        "Simiti" => "Simiti",
        "Soplaviento" => "Soplaviento",
        "Talaigua Nuevo" => "Talaigua Nuevo",
        "Tiquisio" => "Tiquisio",
        "Turbaco" => "Turbaco",
        "Turbana" => "Turbana",
        "Villanueva" => "Villanueva",
        "Zambrano" => "Zambrano"
    	);
    break;
    case "5":
    	$cities = array(
        "Tunja" => "Tunja",
        "Almeida" => "Almeida",
        "Aquitania" => "Aquitania",
        "Arcabuco" => "Arcabuco",
        "Belen" => "Belen",
        "Berbeo" => "Berbeo",
        "Beteitiva" => "Beteitiva",
        "Boavita" => "Boavita",
        "Boyaca" => "Boyaca",
        "Briceño" => "Briceño",
        "Buenavista" => "Buenavista",
        "Busbanza" => "Busbanza",
        "Caldas" => "Caldas",
        "Campohermoso" => "Campohermoso",
        "Cerinza" => "Cerinza",
        "Chinavita" => "Chinavita",
        "Chiquinquira" => "Chiquinquira",
        "Chiscas" => "Chiscas",
        "Chita" => "Chita",
        "Chitaraque" => "Chitaraque",
        "Chivata" => "Chivata",
        "Cienega" => "Cienega",
        "Combita" => "Combita",
        "Coper" => "Coper",
        "Corrales" => "Corrales",
        "Covarachia" => "Covarachia",
        "Cubara" => "Cubara",
        "Cucaita" => "Cucaita",
        "Cuitiva" => "Cuitiva",
        "Chiquiza" => "Chiquiza",
        "Chivor" => "Chivor",
        "Duitama" => "Duitama",
        "El Cocuy" => "El Cocuy",
        "El Espino" => "El Espino",
        "Firavitoba" => "Firavitoba",
        "Floresta" => "Floresta",
        "Gachantiva" => "Gachantiva",
        "Gameza" => "Gameza",
        "Garagoa" => "Garagoa",
        "Guacamayas" => "Guacamayas",
        "Guateque" => "Guateque",
        "Guayata" => "Guayata",
        "Gsican" => "Gsican",
        "Iza" => "Iza",
        "Jenesano" => "Jenesano",
        "Jerico" => "Jerico",
        "Labranzagrande" => "Labranzagrande",
        "La Capilla" => "La Capilla",
        "La Victoria" => "La Victoria",
        "La Uvita" => "La Uvita",
        "Villa De Leyva" => "Villa De Leyva",
        "Macanal" => "Macanal",
        "Maripi" => "Maripi",
        "Miraflores" => "Miraflores",
        "Mongua" => "Mongua",
        "Mongui" => "Mongui",
        "Moniquira" => "Moniquira",
        "Motavita" => "Motavita",
        "Muzo" => "Muzo",
        "Nobsa" => "Nobsa",
        "Nuevo Colon" => "Nuevo Colon",
        "Oicata" => "Oicata",
        "Otanche" => "Otanche",
        "Pachavita" => "Pachavita",
        "Paez" => "Paez",
        "Paipa" => "Paipa",
        "Pajarito" => "Pajarito",
        "Panqueba" => "Panqueba",
        "Pauna" => "Pauna",
        "Paya" => "Paya",
        "Paz De Rio" => "Paz De Rio",
        "Pesca" => "Pesca",
        "Pisba" => "Pisba",
        "Puerto Boyaca" => "Puerto Boyaca",
        "Quipama" => "Quipama",
        "Ramiriqui" => "Ramiriqui",
        "Raquira" => "Raquira",
        "Rondon" => "Rondon",
        "Saboya" => "Saboya",
        "Sachica" => "Sachica",
        "Samaca" => "Samaca",
        "San Eduardo" => "San Eduardo",
        "San Jose De Pare" => "San Jose De Pare",
        "San Luis De Gaceno" => "San Luis De Gaceno",
        "San Mateo" => "San Mateo",
        "San Miguel De Sema" => "San Miguel De Sema",
        "San Pablo De Borbur" => "San Pablo De Borbur",
        "Santana" => "Santana",
        "Santa Maria" => "Santa Maria",
        "Santa Rosa De Viterbo" => "Santa Rosa De Viterbo",
        "Santa Sofia" => "Santa Sofia",
        "Sativanorte" => "Sativanorte",
        "Sativasur" => "Sativasur",
        "Siachoque" => "Siachoque",
        "Soata" => "Soata",
        "Socota" => "Socota",
        "Socha" => "Socha",
        "Sogamoso" => "Sogamoso",
        "Somondoco" => "Somondoco",
        "Sora" => "Sora",
        "Sotaquira" => "Sotaquira",
        "Soraca" => "Soraca",
        "Susacon" => "Susacon",
        "Sutamarchan" => "Sutamarchan",
        "Sutatenza" => "Sutatenza",
        "Tasco" => "Tasco",
        "Tenza" => "Tenza",
        "Tibana" => "Tibana",
        "Tibasosa" => "Tibasosa",
        "Tinjaca" => "Tinjaca",
        "Tipacoque" => "Tipacoque",
        "Toca" => "Toca",
        "Togsi" => "Togsi",
        "Topaga" => "Topaga",
        "Tota" => "Tota",
        "Tunungua" => "Tunungua",
        "Turmeque" => "Turmeque",
        "Tuta" => "Tuta",
        "Tutaza" => "Tutaza",
        "Umbita" => "Umbita",
        "Ventaquemada" => "Ventaquemada",
        "Viracacha" => "Viracacha",
        "Zetaquira" => "Zetaquira"
    	);
    break;
    case "6":
    	$cities = array(
        "MANIZALES" => "MANIZALES",
        "AGUADAS" => "AGUADAS",
        "ANSERMA" => "ANSERMA",
        "ARANZAZU" => "ARANZAZU",
        "BELALCAZAR" => "BELALCAZAR",
        "CHINCHINA" => "CHINCHINA",
        "FILADELFIA" => "FILADELFIA",
        "LA DORADA" => "LA DORADA",
        "LA MERCED" => "LA MERCED",
        "MANZANARES" => "MANZANARES",
        "MARMATO" => "MARMATO",
        "MARQUETALIA" => "MARQUETALIA",
        "MARULANDA" => "MARULANDA",
        "NEIRA" => "NEIRA",
        "NORCASIA" => "NORCASIA",
        "PACORA" => "PACORA",
        "PALESTINA" => "PALESTINA",
        "PENSILVANIA" => "PENSILVANIA",
        "RIOSUCIO" => "RIOSUCIO",
        "RISARALDA" => "RISARALDA",
        "SALAMINA" => "SALAMINA",
        "SAMANA" => "SAMANA",
        "SAN JOSE" => "SAN JOSE",
        "SUPIA" => "SUPIA",
        "VICTORIA" => "VICTORIA",
        "VILLAMARIA" => "VILLAMARIA",
        "VITERBO" => "VITERBO"
    	);
    break;
    case "7":
    	$cities = array(
    	 "Manizales" => "Manizales",
        "Aguadas" => "Aguadas",
        "Anserma" => "Anserma",
        "Aranzazu" => "Aranzazu",
        "Belalcazar" => "Belalcazar",
        "Chinchina" => "Chinchina",
        "Filadelfia" => "Filadelfia",
        "La Dorada" => "La Dorada",
        "La Merced" => "La Merced",
        "Manzanares" => "Manzanares",
        "Marmato" => "Marmato",
        "Marquetalia" => "Marquetalia",
        "Marulanda" => "Marulanda",
        "Neira" => "Neira",
        "Norcasia" => "Norcasia",
        "Pacora" => "Pacora",
        "Palestina" => "Palestina",
        "Pensilvania" => "Pensilvania",
        "Riosucio" => "Riosucio",
        "Risaralda" => "Risaralda",
        "Salamina" => "Salamina",
        "Samana" => "Samana",
        "San Jose" => "San Jose",
        "Supia" => "Supia",
        "Victoria" => "Victoria",
        "Villamaria" => "Villamaria",
        "Viterbo" => "Viterbo"
    	);
    break;
    case "8":
    	$cities = array(
        "Popayan" => "Popayan",
        "Almaguer" => "Almaguer",
        "Argelia" => "Argelia",
        "Balboa" => "Balboa",
        "Bolivar" => "Bolivar",
        "Buenos Aires" => "Buenos Aires",
        "Cajibio" => "Cajibio",
        "Caldono" => "Caldono",
        "Caloto" => "Caloto",
        "Corinto" => "Corinto",
        "El Tambo" => "El Tambo",
        "Florencia" => "Florencia",
        "Guachene" => "Guachene",
        "Guapi" => "Guapi",
        "Inza" => "Inza",
        "Jambalo" => "Jambalo",
        "La Sierra" => "La Sierra",
        "La Vega" => "La Vega",
        "Lopez" => "Lopez",
        "Mercaderes" => "Mercaderes",
        "Miranda" => "Miranda",
        "Morales" => "Morales",
        "Padilla" => "Padilla",
        "Paez" => "Paez",
        "Patia" => "Patia",
        "Piamonte" => "Piamonte",
        "Piendamo" => "Piendamo",
        "Puerto Tejada" => "Puerto Tejada",
        "Purace" => "Purace",
        "Rosas" => "Rosas",
        "San Sebastian" => "San Sebastian",
        "Santander De Quilichao" => "Santander De Quilichao",
        "Santa Rosa" => "Santa Rosa",
        "Silvia" => "Silvia",
        "Sotara" => "Sotara",
        "Suarez" => "Suarez",
        "Sucre" => "Sucre",
        "Timbio" => "Timbio",
        "Timbiqui" => "Timbiqui",
        "Toribio" => "Toribio",
        "Totoro" => "Totoro",
        "Villa Rica" => "Villa Rica"
    	);
    break;
    case "9":
    	$cities = array(
        "Valledupar" => "Valledupar",
        "Aguachica" => "Aguachica",
        "Agustin Codazzi" => "Agustin Codazzi",
        "Astrea" => "Astrea",
        "Becerril" => "Becerril",
        "Bosconia" => "Bosconia",
        "Chimichagua" => "Chimichagua",
        "Chiriguana" => "Chiriguana",
        "Curumani" => "Curumani",
        "El Copey" => "El Copey",
        "El Paso" => "El Paso",
        "Gamarra" => "Gamarra",
        "Gonzalez" => "Gonzalez",
        "La Gloria" => "La Gloria",
        "La Jagua De Ibirico" => "La Jagua De Ibirico",
        "Manaure" => "Manaure",
        "Pailitas" => "Pailitas",
        "Pelaya" => "Pelaya",
        "Pueblo Bello" => "Pueblo Bello",
        "Rio De Oro" => "Rio De Oro",
        "La Paz" => "La Paz",
        "San Alberto" => "San Alberto",
        "San Diego" => "San Diego",
        "San Martin" => "San Martin",
        "Tamalameque" => "Tamalameque"
    	);
    break;
    case "10":
    	$cities = array(
        "Monteria" => "Monteria",
        "Ayapel" => "Ayapel",
        "Buenavista" => "Buenavista",
        "Canalete" => "Canalete",
        "Cerete" => "Cerete",
        "Chima" => "Chima",
        "Chinu" => "Chinu",
        "Cienaga De Oro" => "Cienaga De Oro",
        "Cotorra" => "Cotorra",
        "La Apartada" => "La Apartada",
        "Lorica" => "Lorica",
        "Los Cordobas" => "Los Cordobas",
        "Momil" => "Momil",
        "Montelibano" => "Montelibano",
        "Moñitos" => "Moñitos",
        "Planeta Rica" => "Planeta Rica",
        "Pueblo Nuevo" => "Pueblo Nuevo",
        "Puerto Escondido" => "Puerto Escondido",
        "Puerto Libertador" => "Puerto Libertador",
        "Purisima" => "Purisima",
        "Sahagun" => "Sahagun",
        "San Andres Sotavento" => "San Andres Sotavento",
        "San Antero" => "San Antero",
        "San Bernardo Del Viento" => "San Bernardo Del Viento",
        "San Carlos" => "San Carlos",
        "San Pelayo" => "San Pelayo",
        "Tierralta" => "Tierralta",
        "Valencia" => "Valencia"
    	);
    break;
    case "11":
    	$cities = array(
        "Agua De Dios" => "Agua De Dios",
        "Alban" => "Alban",
        "Anapoima" => "Anapoima",
        "Anolaima" => "Anolaima",
        "Arbelaez" => "Arbelaez",
        "Beltran" => "Beltran",
        "Bituima" => "Bituima",
        "Bojaca" => "Bojaca",
        "Cabrera" => "Cabrera",
        "Cachipay" => "Cachipay",
        "Cajica" => "Cajica",
        "Caparrapi" => "Caparrapi",
        "Caqueza" => "Caqueza",
        "Carmen De Carupa" => "Carmen De Carupa",
        "Chaguani" => "Chaguani",
        "Chia" => "Chia",
        "Chipaque" => "Chipaque",
        "Choachi" => "Choachi",
        "Choconta" => "Choconta",
        "Cogua" => "Cogua",
        "Cota" => "Cota",
        "Cucunuba" => "Cucunuba",
        "El Colegio" => "El Colegio",
        "El Peñon" => "El Peñon",
        "El Rosal" => "El Rosal",
        "Facatativa" => "Facatativa",
        "Fomeque" => "Fomeque",
        "Fosca" => "Fosca",
        "Funza" => "Funza",
        "Fuquene" => "Fuquene",
        "Fusagasuga" => "Fusagasuga",
        "Gachala" => "Gachala",
        "Gachancipa" => "Gachancipa",
        "Gacheta" => "Gacheta",
        "Gama" => "Gama",
        "Girardot" => "Girardot",
        "Granada" => "Granada",
        "Guacheta" => "Guacheta",
        "Guaduas" => "Guaduas",
        "Guasca" => "Guasca",
        "Guataqui" => "Guataqui",
        "Guatavita" => "Guatavita",
        "Guayabal De Siquima" => "Guayabal De Siquima",
        "Guayabetal" => "Guayabetal",
        "Gutierrez" => "Gutierrez",
        "Jerusalen" => "Jerusalen",
        "Junin" => "Junin",
        "La Calera" => "La Calera",
        "La Mesa" => "La Mesa",
        "La Palma" => "La Palma",
        "La Peña" => "La Peña",
        "La Vega" => "La Vega",
        "Lenguazaque" => "Lenguazaque",
        "Macheta" => "Macheta",
        "Madrid" => "Madrid",
        "Manta" => "Manta",
        "Medina" => "Medina",
        "Mosquera" => "Mosquera",
        "Nariño" => "Nariño",
        "Nemocon" => "Nemocon",
        "Nilo" => "Nilo",
        "Nimaima" => "Nimaima",
        "Nocaima" => "Nocaima",
        "Venecia" => "Venecia",
        "Pacho" => "Pacho",
        "Paime" => "Paime",
        "Pandi" => "Pandi",
        "Paratebueno" => "Paratebueno",
        "Pasca" => "Pasca",
        "Puerto Salgar" => "Puerto Salgar",
        "Puli" => "Puli",
        "Quebradanegra" => "Quebradanegra",
        "Quetame" => "Quetame",
        "Quipile" => "Quipile",
        "Apulo" => "Apulo",
        "Ricaurte" => "Ricaurte",
        "San Antonio Del Tequendama" => "San Antonio Del Tequendama",
        "San Bernardo" => "San Bernardo",
        "San Cayetano" => "San Cayetano",
        "San Francisco" => "San Francisco",
        "San Juan De Rio Seco" => "San Juan De Rio Seco",
        "Sasaima" => "Sasaima",
        "Sesquile" => "Sesquile",
        "Sibate" => "Sibate",
        "Silvania" => "Silvania",
        "Simijaca" => "Simijaca",
        "Soacha" => "Soacha",
        "Sopo" => "Sopo",
        "Subachoque" => "Subachoque",
        "Suesca" => "Suesca",
        "Supata" => "Supata",
        "Susa" => "Susa",
        "Sutatausa" => "Sutatausa",
        "Tabio" => "Tabio",
        "Tausa" => "Tausa",
        "Tena" => "Tena",
        "Tenjo" => "Tenjo",
        "Tibacuy" => "Tibacuy",
        "Tibirita" => "Tibirita",
        "Tocaima" => "Tocaima",
        "Tocancipa" => "Tocancipa",
        "Topaipi" => "Topaipi",
        "Ubala" => "Ubala",
        "Ubaque" => "Ubaque",
        "Villa De San Diego De Ubate" => "Villa De San Diego De Ubate",
        "Une" => "Une",
        "Utica" => "Utica",
        "Vergara" => "Vergara",
        "Viani" => "Viani",
        "Villagomez" => "Villagomez",
        "Villapinzon" => "Villapinzon",
        "Villeta" => "Villeta",
        "Viota" => "Viota",
        "Yacopi" => "Yacopi",
        "Zipacon" => "Zipacon",
        "Zipaquira" => "Zipaquira"
    	);
    break;
    case "12":
    	$cities = array(
        "QUIBDO" => "QUIBDO",
        "ACANDI" => "ACANDI",
        "ALTO BAUDO" => "ALTO BAUDO",
        "ATRATO" => "ATRATO",
        "BAGADO" => "BAGADO",
        "BAHIA SOLANO" => "BAHIA SOLANO",
        "BAJO BAUDO" => "BAJO BAUDO",
        "BOJAYA" => "BOJAYA",
        "EL CANTON DEL SAN PABLO" => "EL CANTON DEL SAN PABLO",
        "CARMEN DEL DARIEN" => "CARMEN DEL DARIEN",
        "CERTEGUI" => "CERTEGUI",
        "CONDOTO" => "CONDOTO",
        "EL CARMEN DE ATRATO" => "EL CARMEN DE ATRATO",
        "EL LITORAL DEL SAN JUAN" => "EL LITORAL DEL SAN JUAN",
        "ISTMINA" => "ISTMINA",
        "JURADO" => "JURADO",
        "LLORO" => "LLORO",
        "MEDIO ATRATO" => "MEDIO ATRATO",
        "MEDIO BAUDO" => "MEDIO BAUDO",
        "MEDIO SAN JUAN" => "MEDIO SAN JUAN",
        "NOVITA" => "NOVITA",
        "NUQUI" => "NUQUI",
        "RIO IRO" => "RIO IRO",
        "RIO QUITO" => "RIO QUITO",
        "RIOSUCIO" => "RIOSUCIO",
        "SAN JOSE DEL PALMAR" => "SAN JOSE DEL PALMAR",
        "SIPI" => "SIPI",
        "TADO" => "TADO",
        "UNGUIA" => "UNGUIA",
        "UNION PANAMERICANA" => "UNION PANAMERICANA"
    	);
    break;
    case "13":
    	$cities = array(
        "Neiva" => "Neiva",
        "Acevedo" => "Acevedo",
        "Agrado" => "Agrado",
        "Aipe" => "Aipe",
        "Algeciras" => "Algeciras",
        "Altamira" => "Altamira",
        "Baraya" => "Baraya",
        "Campoalegre" => "Campoalegre",
        "Colombia" => "Colombia",
        "Elias" => "Elias",
        "Garzon" => "Garzon",
        "Gigante" => "Gigante",
        "Guadalupe" => "Guadalupe",
        "Hobo" => "Hobo",
        "Iquira" => "Iquira",
        "Isnos" => "Isnos",
        "La Argentina" => "La Argentina",
        "La Plata" => "La Plata",
        "Nataga" => "Nataga",
        "Oporapa" => "Oporapa",
        "Paicol" => "Paicol",
        "Palermo" => "Palermo",
        "Palestina" => "Palestina",
        "Pital" => "Pital",
        "Pitalito" => "Pitalito",
        "Rivera" => "Rivera",
        "Saladoblanco" => "Saladoblanco",
        "San Agustin" => "San Agustin",
        "Santa Maria" => "Santa Maria",
        "Suaza" => "Suaza",
        "Tarqui" => "Tarqui",
        "Tesalia" => "Tesalia",
        "Tello" => "Tello",
        "Teruel" => "Teruel",
        "Timana" => "Timana",
        "Villavieja" => "Villavieja",
        "Yaguara" => "Yaguara"
    	);
    break;
    case "14":
    	$cities = array(
        "Riohacha" => "Riohacha",
        "Albania" => "Albania",
        "Barrancas" => "Barrancas",
        "Dibulla" => "Dibulla",
        "Distraccion" => "Distraccion",
        "El Molino" => "El Molino",
        "Fonseca" => "Fonseca",
        "Hatonuevo" => "Hatonuevo",
        "La Jagua Del Pilar" => "La Jagua Del Pilar",
        "Maicao" => "Maicao",
        "Manaure" => "Manaure",
        "San Juan Del Cesar" => "San Juan Del Cesar",
        "Uribia" => "Uribia",
        "Urumita" => "Urumita",
        "Villanueva" => "Villanueva"
    	);
    break;
    case "15":
    	$cities = array(
        "SANTA MARTA" => "SANTA MARTA",
        "ALGARROBO" => "ALGARROBO",
        "ARACATACA" => "ARACATACA",
        "ARIGUANI" => "ARIGUANI",
        "CERRO SAN ANTONIO" => "CERRO SAN ANTONIO",
        "CHIBOLO" => "CHIBOLO",
        "CIENAGA" => "CIENAGA",
        "CONCORDIA" => "CONCORDIA",
        "EL BANCO" => "EL BANCO",
        "EL PIÑON" => "EL PIÑON",
        "EL RETEN" => "EL RETEN",
        "FUNDACION" => "FUNDACION",
        "GUAMAL" => "GUAMAL",
        "NUEVA GRANADA" => "NUEVA GRANADA",
        "PEDRAZA" => "PEDRAZA",
        "PIJIÑO DEL CARMEN" => "PIJIÑO DEL CARMEN",
        "PIVIJAY" => "PIVIJAY",
        "PLATO" => "PLATO",
        "PUEBLOVIEJO" => "PUEBLOVIEJO",
        "REMOLINO" => "REMOLINO",
        "SABANAS DE SAN ANGEL" => "SABANAS DE SAN ANGEL",
        "SALAMINA" => "SALAMINA",
        "SAN SEBASTIAN DE BUENAVISTA" => "SAN SEBASTIAN DE BUENAVISTA",
        "SAN ZENON" => "SAN ZENON",
        "SANTA ANA" => "SANTA ANA",
        "SANTA BARBARA DE PINTO" => "SANTA BARBARA DE PINTO",
        "SITIONUEVO" => "SITIONUEVO",
        "TENERIFE" => "TENERIFE",
        "ZAPAYAN" => "ZAPAYAN",
        "ZONA BANANERA" => "ZONA BANANERA"
    	);
    break;
    case "16":
    	$cities = array(
        "Villavicencio" => "Villavicencio",
        "Acacias" => "Acacias",
        "Barranca De Upia" => "Barranca De Upia",
        "Cabuyaro" => "Cabuyaro",
        "Castilla La Nueva" => "Castilla La Nueva",
        "Cubarral" => "Cubarral",
        "Cumaral" => "Cumaral",
        "El Calvario" => "El Calvario",
        "El Castillo" => "El Castillo",
        "El Dorado" => "El Dorado",
        "Fuente De Oro" => "Fuente De Oro",
        "Granada" => "Granada",
        "Guamal" => "Guamal",
        "Mapiripan" => "Mapiripan",
        "Mesetas" => "Mesetas",
        "La Macarena" => "La Macarena",
        "Uribe" => "Uribe",
        "Lejanias" => "Lejanias",
        "Puerto Concordia" => "Puerto Concordia",
        "Puerto Gaitan" => "Puerto Gaitan",
        "Puerto Lopez" => "Puerto Lopez",
        "Puerto Lleras" => "Puerto Lleras",
        "Puerto Rico" => "Puerto Rico",
        "Restrepo" => "Restrepo",
        "San Carlos De Guaroa" => "San Carlos De Guaroa",
        "San Juan De Arama" => "San Juan De Arama",
        "San Juanito" => "San Juanito",
        "San Martin" => "San Martin",
        "Vistahermosa" => "Vistahermosa"
    	);
    break;
    case "17":
    	$cities = array(
        "Pasto" => "Pasto",
        "Alban" => "Alban",
        "Aldana" => "Aldana",
        "Ancuya" => "Ancuya",
        "Arboleda" => "Arboleda",
        "Barbacoas" => "Barbacoas",
        "Belen" => "Belen",
        "Buesaco" => "Buesaco",
        "Colon" => "Colon",
        "Consaca" => "Consaca",
        "Contadero" => "Contadero",
        "Cordoba" => "Cordoba",
        "Cuaspud" => "Cuaspud",
        "Cumbal" => "Cumbal",
        "Cumbitara" => "Cumbitara",
        "Chachagsi" => "Chachagsi",
        "El Charco" => "El Charco",
        "El Peñol" => "El Peñol",
        "El Rosario" => "El Rosario",
        "El Tablon De Gomez" => "El Tablon De Gomez",
        "El Tambo" => "El Tambo",
        "Funes" => "Funes",
        "Guachucal" => "Guachucal",
        "Guaitarilla" => "Guaitarilla",
        "Gualmatan" => "Gualmatan",
        "Iles" => "Iles",
        "Imues" => "Imues",
        "Ipiales" => "Ipiales",
        "La Cruz" => "La Cruz",
        "La Florida" => "La Florida",
        "La Llanada" => "La Llanada",
        "La Tola" => "La Tola",
        "La Union" => "La Union",
        "Leiva" => "Leiva",
        "Linares" => "Linares",
        "Los Andes" => "Los Andes",
        "Magsi" => "Magsi",
        "Mallama" => "Mallama",
        "Mosquera" => "Mosquera",
        "Nariño" => "Nariño",
        "Olaya Herrera" => "Olaya Herrera",
        "Ospina" => "Ospina",
        "Francisco Pizarro" => "Francisco Pizarro",
        "Policarpa" => "Policarpa",
        "Potosi" => "Potosi",
        "Providencia" => "Providencia",
        "Puerres" => "Puerres",
        "Pupiales" => "Pupiales",
        "Ricaurte" => "Ricaurte",
        "Roberto Payan" => "Roberto Payan",
        "Samaniego" => "Samaniego",
        "Sandona" => "Sandona",
        "San Bernardo" => "San Bernardo",
        "San Lorenzo" => "San Lorenzo",
        "San Pablo" => "San Pablo",
        "San Pedro De Cartago" => "San Pedro De Cartago",
        "Santa Barbara" => "Santa Barbara",
        "Santacruz" => "Santacruz",
        "Sapuyes" => "Sapuyes",
        "Taminango" => "Taminango",
        "Tangua" => "Tangua",
        "San Andres De Tumaco" => "San Andres De Tumaco",
        "Tuquerres" => "Tuquerres",
        "Yacuanquer" => "Yacuanquer"
    	);
    break;
    case "18":
    	$cities = array(
        "Cucuta" => "Cucuta",
        "Abrego" => "Abrego",
        "Arboledas" => "Arboledas",
        "Bochalema" => "Bochalema",
        "Bucarasica" => "Bucarasica",
        "Cacota" => "Cacota",
        "Cachira" => "Cachira",
        "Chinacota" => "Chinacota",
        "Chitaga" => "Chitaga",
        "Convencion" => "Convencion",
        "Cucutilla" => "Cucutilla",
        "Durania" => "Durania",
        "El Carmen" => "El Carmen",
        "El Tarra" => "El Tarra",
        "El Zulia" => "El Zulia",
        "Gramalote" => "Gramalote",
        "Hacari" => "Hacari",
        "Herran" => "Herran",
        "Labateca" => "Labateca",
        "La Esperanza" => "La Esperanza",
        "La Playa" => "La Playa",
        "Los Patios" => "Los Patios",
        "Lourdes" => "Lourdes",
        "Mutiscua" => "Mutiscua",
        "Ocaña" => "Ocaña",
        "Pamplona" => "Pamplona",
        "Pamplonita" => "Pamplonita",
        "Puerto Santander" => "Puerto Santander",
        "Ragonvalia" => "Ragonvalia",
        "Salazar" => "Salazar",
        "San Calixto" => "San Calixto",
        "San Cayetano" => "San Cayetano",
        "Santiago" => "Santiago",
        "Sardinata" => "Sardinata",
        "Silos" => "Silos",
        "Teorama" => "Teorama",
        "Tibu" => "Tibu",
        "Toledo" => "Toledo",
        "Villa Caro" => "Villa Caro",
        "Villa Del Rosario" => "Villa Del Rosario"
    	);
    break;
    case "19":
    	$cities = array(
        "Armenia" => "Armenia",
        "Buenavista" => "Buenavista",
        "Calarca" => "Calarca",
        "Circasia" => "Circasia",
        "Cordoba" => "Cordoba",
        "Filandia" => "Filandia",
        "Genova" => "Genova",
        "La Tebaida" => "La Tebaida",
        "Montenegro" => "Montenegro",
        "Pijao" => "Pijao",
        "Quimbaya" => "Quimbaya",
        "Salento" => "Salento"
    	);
    break;
    case "20":
    	$cities = array(
        "Pereira" => "Pereira",
        "Apia" => "Apia",
        "Balboa" => "Balboa",
        "Belen De Umbria" => "Belen De Umbria",
        "Dosquebradas" => "Dosquebradas",
        "Guatica" => "Guatica",
        "La Celia" => "La Celia",
        "La Virginia" => "La Virginia",
        "Marsella" => "Marsella",
        "Mistrato" => "Mistrato",
        "Pueblo Rico" => "Pueblo Rico",
        "Quinchia" => "Quinchia",
        "Santa Rosa De Cabal" => "Santa Rosa De Cabal",
        "Santuario" => "Santuario"
    	);
    break;
    case "21":
    	$cities = array(
        "Bucaramanga" => "Bucaramanga",
        "Aguada" => "Aguada",
        "Albania" => "Albania",
        "Aratoca" => "Aratoca",
        "Barbosa" => "Barbosa",
        "Barichara" => "Barichara",
        "Barrancabermeja" => "Barrancabermeja",
        "Betulia" => "Betulia",
        "Bolivar" => "Bolivar",
        "Cabrera" => "Cabrera",
        "California" => "California",
        "Capitanejo" => "Capitanejo",
        "Carcasi" => "Carcasi",
        "Cepita" => "Cepita",
        "Cerrito" => "Cerrito",
        "Charala" => "Charala",
        "Charta" => "Charta",
        "Chima" => "Chima",
        "Chipata" => "Chipata",
        "Cimitarra" => "Cimitarra",
        "Concepcion" => "Concepcion",
        "Confines" => "Confines",
        "Contratacion" => "Contratacion",
        "Coromoro" => "Coromoro",
        "Curiti" => "Curiti",
        "El Carmen De Chucuri" => "El Carmen De Chucuri",
        "El Guacamayo" => "El Guacamayo",
        "El Peñon" => "El Peñon",
        "El Playon" => "El Playon",
        "Encino" => "Encino",
        "Enciso" => "Enciso",
        "Florian" => "Florian",
        "Floridablanca" => "Floridablanca",
        "Galan" => "Galan",
        "Gambita" => "Gambita",
        "Giron" => "Giron",
        "Guaca" => "Guaca",
        "Guadalupe" => "Guadalupe",
        "Guapota" => "Guapota",
        "Guavata" => "Guavata",
        "Gsepsa" => "Gsepsa",
        "Hato" => "Hato",
        "Jesus Maria" => "Jesus Maria",
        "Jordan" => "Jordan",
        "La Belleza" => "La Belleza",
        "Landazuri" => "Landazuri",
        "La Paz" => "La Paz",
        "Lebrija" => "Lebrija",
        "Los Santos" => "Los Santos",
        "Macaravita" => "Macaravita",
        "Malaga" => "Malaga",
        "Matanza" => "Matanza",
        "Mogotes" => "Mogotes",
        "Molagavita" => "Molagavita",
        "Ocamonte" => "Ocamonte",
        "Oiba" => "Oiba",
        "Onzaga" => "Onzaga",
        "Palmar" => "Palmar",
        "Palmas Del Socorro" => "Palmas Del Socorro",
        "Paramo" => "Paramo",
        "Piedecuesta" => "Piedecuesta",
        "Pinchote" => "Pinchote",
        "Puente Nacional" => "Puente Nacional",
        "Puerto Parra" => "Puerto Parra",
        "Puerto Wilches" => "Puerto Wilches",
        "Rionegro" => "Rionegro",
        "Sabana De Torres" => "Sabana De Torres",
        "San Andres" => "San Andres",
        "San Benito" => "San Benito",
        "San Gil" => "San Gil",
        "San Joaquin" => "San Joaquin",
        "San Jose De Miranda" => "San Jose De Miranda",
        "San Miguel" => "San Miguel",
        "San Vicente De Chucuri" => "San Vicente De Chucuri",
        "Santa Barbara" => "Santa Barbara",
        "Santa Helena Del Opon" => "Santa Helena Del Opon",
        "Simacota" => "Simacota",
        "Socorro" => "Socorro",
        "Suaita" => "Suaita",
        "Sucre" => "Sucre",
        "Surata" => "Surata",
        "Tona" => "Tona",
        "Valle De San Jose" => "Valle De San Jose",
        "Velez" => "Velez",
        "Vetas" => "Vetas",
        "Villanueva" => "Villanueva",
        "Zapatoca" => "Zapatoca"
    	);
    break;
    case "22":
    	$cities = array(
        "Sincelejo" => "Sincelejo",
        "Buenavista" => "Buenavista",
        "Caimito" => "Caimito",
        "Coloso" => "Coloso",
        "Corozal" => "Corozal",
        "Coveñas" => "Coveñas",
        "Chalan" => "Chalan",
        "El Roble" => "El Roble",
        "Galeras" => "Galeras",
        "Guaranda" => "Guaranda",
        "La Union" => "La Union",
        "Los Palmitos" => "Los Palmitos",
        "Majagual" => "Majagual",
        "Morroa" => "Morroa",
        "Ovejas" => "Ovejas",
        "Palmito" => "Palmito",
        "Sampues" => "Sampues",
        "San Benito Abad" => "San Benito Abad",
        "San Juan De Betulia" => "San Juan De Betulia",
        "San Marcos" => "San Marcos",
        "San Onofre" => "San Onofre",
        "San Pedro" => "San Pedro",
        "San Luis De Since" => "San Luis De Since",
        "Sucre" => "Sucre",
        "Santiago De Tolu" => "Santiago De Tolu"
    	);
    break;
    case "23":
    	$cities = array(
        "Tolu Viejo" => "Tolu Viejo",
        "Ibague" => "Ibague",
        "Alpujarra" => "Alpujarra",
        "Alvarado" => "Alvarado",
        "Ambalema" => "Ambalema",
        "Anzoategui" => "Anzoategui",
        "Armero" => "Armero",
        "Ataco" => "Ataco",
        "Cajamarca" => "Cajamarca",
        "Carmen De Apicala" => "Carmen De Apicala",
        "Casabianca" => "Casabianca",
        "Chaparral" => "Chaparral",
        "Coello" => "Coello",
        "Coyaima" => "Coyaima",
        "Cunday" => "Cunday",
        "Dolores" => "Dolores",
        "Espinal" => "Espinal",
        "Falan" => "Falan",
        "Flandes" => "Flandes",
        "Fresno" => "Fresno",
        "Guamo" => "Guamo",
        "Herveo" => "Herveo",
        "Honda" => "Honda",
        "Icononzo" => "Icononzo",
        "Lerida" => "Lerida",
        "Libano" => "Libano",
        "Mariquita" => "Mariquita",
        "Melgar" => "Melgar",
        "Murillo" => "Murillo",
        "Natagaima" => "Natagaima",
        "Ortega" => "Ortega",
        "Palocabildo" => "Palocabildo",
        "Piedras" => "Piedras",
        "Planadas" => "Planadas",
        "Prado" => "Prado",
        "Purificacion" => "Purificacion",
        "Rioblanco" => "Rioblanco",
        "Roncesvalles" => "Roncesvalles",
        "Rovira" => "Rovira",
        "Saldaña" => "Saldaña",
        "San Antonio" => "San Antonio",
        "San Luis" => "San Luis",
        "Santa Isabel" => "Santa Isabel",
        "Suarez" => "Suarez",
        "Valle De San Juan" => "Valle De San Juan",
        "Venadillo" => "Venadillo",
        "Villahermosa" => "Villahermosa",
        "Villarrica" => "Villarrica"
    	);
    break;
    case "24":
    	$cities = array(
        "Cali" => "Cali",
        "Alcala" => "Alcala",
        "Andalucia" => "Andalucia",
        "Ansermanuevo" => "Ansermanuevo",
        "Argelia" => "Argelia",
        "Bolivar" => "Bolivar",
        "Buenaventura" => "Buenaventura",
        "Guadalajara De Buga" => "Guadalajara De Buga",
        "Bugalagrande" => "Bugalagrande",
        "Caicedonia" => "Caicedonia",
        "Calima" => "Calima",
        "Candelaria" => "Candelaria",
        "Cartago" => "Cartago",
        "Dagua" => "Dagua",
        "El Aguila" => "El Aguila",
        "El Cairo" => "El Cairo",
        "El Cerrito" => "El Cerrito",
        "El Dovio" => "El Dovio",
        "Florida" => "Florida",
        "Ginebra" => "Ginebra",
        "Guacari" => "Guacari",
        "Jamundi" => "Jamundi",
        "La Cumbre" => "La Cumbre",
        "La Union" => "La Union",
        "La Victoria" => "La Victoria",
        "Obando" => "Obando",
        "Palmira" => "Palmira",
        "Pradera" => "Pradera",
        "Restrepo" => "Restrepo",
        "Riofrio" => "Riofrio",
        "Roldanillo" => "Roldanillo",
        "San Pedro" => "San Pedro",
        "Sevilla" => "Sevilla",
        "Toro" => "Toro",
        "Trujillo" => "Trujillo",
        "Tulua" => "Tulua",
        "Ulloa" => "Ulloa",
        "Versalles" => "Versalles",
        "Vijes" => "Vijes",
        "Yotoco" => "Yotoco",
        "Yumbo" => "Yumbo",
        "Zarzal" => "Zarzal"
    	);
    break;
    case "25":
    	$cities = array(
        "Arauca" => "Arauca",
        "Arauquita" => "Arauquita",
        "Cravo Norte" => "Cravo Norte",
        "Fortul" => "Fortul",
        "Puerto Rondon" => "Puerto Rondon",
        "Saravena" => "Saravena",
        "Tame" => "Tame"
    	);
    break;
    case "26":
    	$cities = array(
        "Yopal" => "Yopal",
        "Aguazul" => "Aguazul",
        "Chameza" => "Chameza",
        "Hato Corozal" => "Hato Corozal",
        "La Salina" => "La Salina",
        "Mani" => "Mani",
        "Monterrey" => "Monterrey",
        "Nunchia" => "Nunchia",
        "Orocue" => "Orocue",
        "Paz De Ariporo" => "Paz De Ariporo",
        "Pore" => "Pore",
        "Recetor" => "Recetor",
        "Sabanalarga" => "Sabanalarga",
        "Sacama" => "Sacama",
        "San Luis De Palenque" => "San Luis De Palenque",
        "Tamara" => "Tamara",
        "Tauramena" => "Tauramena",
        "Trinidad" => "Trinidad",
        "Villanueva" => "Villanueva"
    	);
    break;
    case "27":
    	$cities = array(
        "Mocoa" => "Mocoa",
        "Colon" => "Colon",
        "Orito" => "Orito",
        "Puerto Asis" => "Puerto Asis",
        "Puerto Caicedo" => "Puerto Caicedo",
        "Puerto Guzman" => "Puerto Guzman",
        "Leguizamo" => "Leguizamo",
        "Sibundoy" => "Sibundoy",
        "San Francisco" => "San Francisco",
        "San Miguel" => "San Miguel",
        "Santiago" => "Santiago",
        "Valle Del Guamuez" => "Valle Del Guamuez",
        "Villagarzon" => "Villagarzon"
    	);
    break;
    case "28":
    	$cities = array(
        "San Andrés" => "San Andrés",
        "Providencia" => "Providencia",
        "Leticia" => "Leticia"
    	);
    break;
    case "29":
    	$cities = array(
        "El Encanto" => "El Encanto",
        "La Chorrera" => "La Chorrera",
        "La Pedrera" => "La Pedrera",
        "La Victoria" => "La Victoria",
        "Miriti - Parana" => "Miriti - Parana",
        "Puerto Alegria" => "Puerto Alegria",
        "Puerto Arica" => "Puerto Arica",
        "Puerto Nariño" => "Puerto Nariño",
        "Puerto Santander" => "Puerto Santander",
        "Tarapaca" => "Tarapaca"
    	);
    break;
    case "30":
    	$cities = array(
        "Inirida" => "Inirida",
        "Barranco Minas" => "Barranco Minas",
        "Mapiripana" => "Mapiripana",
        "San Felipe" => "San Felipe",
        "Puerto Colombia" => "Puerto Colombia",
        "La Guadalupe" => "La Guadalupe",
        "Cacahual" => "Cacahual",
        "Pana Pana" => "Pana Pana",
        "Morichal" => "Morichal"
    	);
    break;
    case "31":
    	$cities = array(
        "San Jose Del Guaviare" => "San Jose Del Guaviare",
        "Calamar" => "Calamar",
        "El Retorno" => "El Retorno",
        "Miraflores" => "Miraflores"
    	);
    break;
    case "32":
    	$cities = array(
        "Mitu" => "Mitu",
        "Caruru" => "Caruru",
        "Pacoa" => "Pacoa",
        "Taraira" => "Taraira",
        "Papunaua" => "Papunaua",
        "Yavarate" => "Yavarate"
    	);
    break;
    case "33":
    	$cities = array(
    	"Puerto Carreño" => "Puerto Carreño",
        "La Primavera" => "La Primavera",
        "Santa Rosalia" => "Santa Rosalia",
        "Cumaribo" => "Cumaribo"
    	);
    break;
    default:
    //code to do something if other options are not selected (throw an error, or set $cities to a default array)
    $cities = array("Ninguno");
    }
    return $cities;

}

#endregion

new OCEANWP_Theme_Class();
