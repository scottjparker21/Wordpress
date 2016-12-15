<?php

/*
Plugin Name: Multipage Plugin
Plugin URI: http://wordpress.org/plugins/sgr-nextpage-titles/
Description: Multipage Plugin for WordPress will give you the ability to order a post in multiple subpages, giving each subpage a title and having a table of contents.
Author: Sergio De Falco
Version: 1.3.5
Author URI: http://www.envire.it/
Text Domain: sgr-nextpage-titles
Domain Path: /languages/
License: GPL v3
*/

register_activation_hook( __FILE__		, array( 'Multipage_Plugin_Loader', 'activate_plugin' ) );				// Registering plugin activation hook.
register_deactivation_hook( __FILE__	, array( 'Multipage_Plugin_Loader', 'deactivate_plugin' ) );			// Registering plugin deactivation hook.

/**
 * Load the Multipage Plugin
 *
 * @since 0.6
 */
class Multipage_Plugin_Loader {
	/**
	 * Uniquely identify plugin version
	 * Bust caches based on this value
	 *
	 * @since 0.6
	 * @var string
	 */
	const VERSION = '1.3.5';

	/**
	 * Store Multipage default settings.
	 *
	 * @since 1.1
	 *
	 * @var array {}
	 */
	public $multipage_settings_defaults = array(
		'version' 							=> '0.0.0',
		'comments-oofp' 					=> false,
		'unhide-pagination'					=> false,
		'toc-oofp' 							=> false,
		'toc-position' 						=> 'bottom',
		'toc-page-labels' 					=> 'numbers',
		'toc-hide-header' 					=> false,
		'toc-comments-link' 				=> false,
		'rewrite-title-priority' 			=> 'normal',
		'rewrite-content-priority' 			=> 'normal',
		'disable-tinymce-buttons' 			=> false
	);

	/**
	 * Let's get it started
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// load plugin files relative to this directory.
		$this->plugin_directory = dirname(__FILE__) . '/';

		// Load the textdomain for translations
		load_plugin_textdomain( 'sgr-nextpage-titles', true, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Get options with default values.
		$this->options = get_option( 'multipage', $this->multipage_settings_defaults );
		// Maybe this will be replaced.
		$this->options = array_merge( $this->multipage_settings_defaults, $this->options );

		// Check the current installed version and apply upgrades.
		//if ( !isset( $options['version'] ) || version_compare( $options['version'], self::VERSION, '<' ) ) {
			//if ( ! class_exists( 'Multipage_Plugin_Upgrade' ) )
				//require_once( $this->plugin_directory . 'classes/upgrade.php' );
			//new Multipage_Plugin_Upgrade();

		//}

		// Include Widgets
		//add_action( 'widgets_init', array( &$this, 'widgets_init' ) );

		// Load shortcodes
		if ( ! class_exists( 'Multipage_Plugin_Shortcodes' ) )
			require_once( $this->plugin_directory . 'classes/shortcodes.php' );
		Multipage_Plugin_Shortcodes::init();
		
		// Check if current user can edit posts & pages
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			
			// Check if TinyMCE is enabled
			if ( get_user_option( 'rich_editing' ) == 'true' && $this->options['disable-tinymce-buttons'] != true ) {

				// Add TinyMCE Plugin
				add_filter( 'mce_css', array( &$this, 'multipage_mce_css' ) );
				add_filter( 'mce_buttons', array( &$this, 'multipage_mce_button' ) );
				add_filter( 'mce_external_plugins', array( &$this, 'multipage_mce_external_plugin' ) );
				add_filter( 'mce_external_languages', array( &$this, 'multipage_mce_external_language' ) );
			}
			
			// Add HTML Editor button
			add_action( 'admin_print_footer_scripts', array( &$this, 'multipage_add_quicktags' ) );
		}

		if ( is_admin() ) {
			$this->admin_init();
		} else {
			add_action( 'wp', array( &$this, 'public_init' ) );
		}
	}

	/**
	 * Handles actions for the plugin activation
	 *
	 * @since 0.6
	 */
	static function activate_plugin() {
	}

	/**
	 * Handles actions for the plugin deactivation
	 *
	 * @since 0.6
	 */
	static function deactivate_plugin() {
	}
	
	/**
	 * Intialize the public.
	 *
	 * @since 1.0
	 */
	public function public_init() {
		global $post;
		
		// no need to process
		if ( is_feed() || is_404() || empty( $post ) )
			return;

		// Variables
		$page = get_query_var( 'page' );
		$content = $post->post_content;
		$pattern = "/\[nextpage[^\]]*\]/";
		$post_subpages = array();
		$p = 0;

		preg_match_all( $pattern, $content, $matches );
		foreach ( $matches[0] as $match ) {
			$atts = shortcode_parse_atts( str_replace(array( '[', ']' ), '', $match) );

			// Check if the intro has a Title
			if ( 0 == $p && 0 != strpos( $content, $match ) ) :
				$post_subpages['title'][] = __( 'Intro', 'sgr-nextpage-titles' );
				$post_subpages['scroll_to_toc'][] = false;
				$current_title = $atts["title"];
				$p++;
			else :
				$current_title = $atts["title"];
			endif;
		
			$post_subpages['title'][] = $current_title;
			$post_subpages['scroll_to_toc'][] = in_array( 'toc', $atts ) ? true : false;
			$p++;
		}
		
		// If there aren't subpages or it's a loop, exit.
		// Use is_singular because it looks for every post.
		if ( empty( $post_subpages ) )
			return;
		
		// If the requested page doesn't exist (even if there is a declared page=1 variable).
		// return 404.
		//if ( $page == 1 || $page > $p )
			//$this->return_404();
		
		// Update $post Object with new data.
		$post->post_content = preg_replace( $pattern, '<!--nextpage-->', $content );
		$post->post_subpages = $post_subpages;
		
		// Check wherever or not hide the standard WordPress pagination.
		if ( $this->options['unhide-pagination'] != true )
			add_filter( 'wp_link_pages_args',	array( &$this, 'hide_standard_pagination' ) );
		
		add_action( 'wp_enqueue_scripts',	array( &$this, 'enqueue_styles' ) );
		add_filter( 'wp_title',				array( &$this, 'enhance_title' ), $this->get_priority_value( $this->options['rewrite-title-priority'] ) );
		add_filter( 'the_content', 			array( &$this, 'enhance_content' ), $this->get_priority_value( $this->options['rewrite-content-priority'] ) );
	}

	/**
	 * Initialize the backend
	 *
	 * @since 0.93
	 */
	public function admin_init() {
		$admin_dir = $this->plugin_directory . 'admin/';

		// Multipage Plugin settings loader
		if ( ! class_exists( 'Multipage_Plugin_Settings' ) )
			require_once( $admin_dir . 'settings.php' );
		Multipage_Plugin_Settings::init();
		
		// Add settings menu to Plugins Page. Check if is_admin is required.
		// ToDo: move inside the admin class
		add_filter( 'plugin_action_links', array( &$this, 'multipage_add_action_links' ), 10, 5 );
	}

	/**
	 * Add Settings Menu inside the Plugins Page
	 *
	 * @since 1.3.3
	 */
	public function multipage_add_action_links( $actions, $plugin_file ) {
		static $plugin;
		
		if (!isset( $plugin ))
			$plugin = plugin_basename(__FILE__);

		if ($plugin == $plugin_file) {
			$settings = array( 'settings' => '<a href="options-general.php?page=multipage-settings">' . __( 'Settings' ) . '</a>' );
			//$support_link = array( 'support' => '<a href="https://www.envire.it/" target="_blank">' . __( 'Support' ) . '</a>' );					
    		$actions = array_merge( $settings, $actions );
			//$actions = array_merge( $support_link, $actions );
		}
		return $actions;
	}
	
	/**
	 * Add HTML Text Editor Subpage button
	 *
	 * @since 1.3
	 */
	public static function multipage_add_quicktags() {
		if ( wp_script_is( 'quicktags' ) ) {
	?>
	<script type="text/javascript">
		QTags.addButton( 'eg_subpage', '<?php _e( 'subpage', 'sgr-nextpage-titles' ); ?>', prompt_subtitle, '', '', '<?php _e( 'Start a new Subpage', 'sgr-nextpage-titles' ); ?>', 121 );
		
		function prompt_subtitle(e, c, ed) {
			var subtitle = prompt( '<?php _e( 'Enter the subpage title', 'sgr-nextpage-titles' ); ?>' ),
				shortcode, t = this;

			if (typeof subtitle != 'undefined' && subtitle.length < 2) return;

			t.tagStart = '[nextpage title="' + subtitle + '"]\n\n';
			t.tagEnd = false;
			
			// now we've defined all the tagStart, tagEnd and openTags we process it all to the active window
			QTags.TagButton.prototype.callback.call(t, e, c, ed);
		};
	</script>
	<?php
		}
	}
	
	/**
	 * Add a new TinyMCE css.
	 *
	 * @since 1.3
	 *
	 * @return string
	 */
	public static function multipage_mce_css( $mce_css ) {
		if ( ! empty( $mce_css ) )
			$mce_css .= ',';

		$mce_css .= plugin_dir_url( __FILE__ ) . 'admin/tinymce/css/multipage' . ( ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min' ) . '.css';
		return $mce_css;
	}

	/**
	 * Add the new subpage TinyMCE button.
	 *
	 * @since 1.3
	 *
	 * @return array $buttons
	 */
	public static function multipage_mce_button( $buttons ) {
		// Insert 'Subpage' button after the 'WP More' button
		$wp_more_key = array_search( 'wp_more', $buttons ) +1;
		$buttons_after = array_splice( $buttons, $wp_more_key);
		
		array_unshift($buttons_after, 'subpage');
		
		$buttons = array_merge($buttons, $buttons_after);
		
		return $buttons;
	}

	/**
	 * Add the new TinyMCE plugin.
	 *
	 * @since 1.3
	 *
	 * @return array $plugin_array
	 */
	public static function multipage_mce_external_plugin( $plugin_array ) {
		$plugin_array['multipage'] = plugin_dir_url( __FILE__ ) . 'admin/tinymce/js/plugin' . ( ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min' ) . '.js';
		return $plugin_array;
	}
	
	/**
	 * Add the new TinyMCE plugin locale.
	 *
	 * @since 1.3
	 *
	 * @return array $locales
	 */
	public static function multipage_mce_external_language( $locales ) {
		$locales['multipage'] = plugin_dir_path ( __FILE__ ) . 'admin/tinymce/languages.php';
		return $locales;
	}
	
	/**
	 * Styles applied to public-facing pages
	 *
	 * @since 0.6
	 * @uses enqueue_styles()
	 */
	public static function enqueue_styles() {
	
		// LTR or RTL
		$file = is_rtl() ? 'css/multipage-rtl' : 'css/multipage';
		
		// Minimized version or not
		$file .= ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min' ) . '.css';

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'child-multipage';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'parent-multipage';

		// Multipage Plugin Theme Compatibility
		} else {
			$location = trailingslashit( plugin_dir_url( __FILE__ ) ) . 'static/';
			$handle   = 'default-multipage';
		}

		// Enqueue the Multipage Plugin styling
		wp_enqueue_style( $handle, $location . $file, array(), self::VERSION, 'screen' );
	}
	
	/**
	 * Add the subpages summary and other stuff to the_content.
	 *
	 * @since 1.0
	 */
	public function enhance_title( $title ) {
		global $post;
		
		// Get the page number - ToDo: create a function to retrieve the page number
		$page = ( get_query_var('page') ) ? get_query_var('page') : 1;
		
		// If it is the the first page.
		if ( $page <= 1 )
			return $title;

		// Correct the page number for over pages, consistent with the behavior of WordPress.
		if ( $page > max( array_map( 'count', $post->post_subpages ) ) )
			$page = 1;
		
		// If it is the the first page.
		if ( $page <= 1 )
			return $title;

		$subpages = $post->post_subpages;
		$subpage_title = $subpages['title'][ $page -1 ];
		
		// Eventually, manipulate WordPress SEO by Yoast custom title.
		$title = str_replace( sprintf( __( 'Page %d of %d', 'wordpress-seo' ), $page, max( array_map( 'count', $subpages ) ) ), $subpage_title, $title );
		
		// Manipulate Theme standard title.
		$title = str_replace( sprintf( __( 'Page %s', wp_get_theme()->get( 'TextDomain' ) ), $page ), $subpage_title, $title );

		return $title;		
	}

	/**
	 * Add the subpages summary and other stuff to the_content.
	 *
	 * @since 1.0
	 */
	public function enhance_content( $content ) {
		global $post;
		
		// Table of contents should not be the only content in the post.
		if ( ! $content )
			return $content;
			
		if ( ! is_singular() )
			return $content;
			
		if ( ! property_exists( $post, 'post_subpages' ) )
           return $content;
		   
		// Get the page number - ToDo: create a function to retrieve the page number
		$page = ( get_query_var('page') ) ? get_query_var('page') : 1;
		
		// Correct the page number for overflow pages, consistent with the behavior of WordPress.
		if ( $page > max( array_map( 'count', $post->post_subpages ) ) )
			$page = 1;
			
		// If not the first page, hide featured image.
		//if ( $page != 1 )
			//add_filter( 'get_post_metadata', array( &$this, 'hide_post_thumbnail' ), 999, 4 );  

		// If not the first page, hide comments.
		if ( $page != 1 && $this->options['comments-oofp'] )
			add_filter( 'comments_template', array( &$this, 'hide_comments' ) );

		$subpages = $post->post_subpages;
		$subtitle = '<h2 class="entry-subtitle">' . apply_filters( 'multipage_subtitle', $subpages['title'][ $page -1 ] ) . '</h2>';
	
		if ( $page >= max( array_map( 'count', $subpages ) ) ) {
			$multipagenav = '<div class="multipage-navlink">' . __( 'Back to: ', 'sgr-nextpage-titles' ) . ' <a rel="index" href="' . get_permalink() . '">' . $subpages['title'][ 0 ] . '</a></div>';
		} else {
			$multipagenav = '<div class="multipage-navlink">' . __( 'Continue:', 'sgr-nextpage-titles' ) . ' <a rel="next" href="' . $this->get_subpage_link( $page +1 ) . '">' . $subpages['title'][ $page ] .'</a></div>';
		}
		
		$multipagenav = apply_filters( 'multipage_navigation', $multipagenav );
		$enhanced_content = $subtitle . $content . $multipagenav;

		if ( ! $this->options['toc-oofp'] || $page == 1 ) {
		
			$toc = '<ul id="toc" class="multipage-toc multipage-toc-' . $post->ID . '">';
			if ( ! $this->options['toc-hide-header'] )					
				$toc .= '<li class="toc-header">' . __( 'Contents', 'sgr-nextpage-titles' ) . '</li>';
						
			foreach ( $subpages as $b ) {
				foreach ( $b as $c => $match ) {
				
					$current = $c+1;
					$toc .= '<li class="subpage-' . $current;
					if ( $current == $page )
						$toc .= ' current';
					$toc .= '">';
					
					// Subpage label.
					if ( $this->options['toc-page-labels'] === 'numbers' ) {
						$toc .= '<span class="numbers">' . $current . '. </span> ';	
					}
					elseif ( $this->options['toc-page-labels'] === 'pages' ) {
						$toc .= '<span class="pages">' . sprintf( __( 'Page %d:', 'sgr-nextpage-titles' ), $current ) . '</span> ';
					}
					
					// Subpage link.
					$toc .= '<a href="' . $this->get_subpage_link( $current, $subpages['scroll_to_toc'][ $c ] ) . '">' . $subpages['title'][ $c ] . '</a></li>';
				}
				break;
			}
		
			// If comments are open add the link to the table of contents.
			if ( comments_open() && $this->options['toc-comments-link'] )		
				$toc .= '<li class="toc-footer"><a href="' . get_comments_link()  . '">' . sprintf( __( 'Comments (%d)', 'sgr-nextpage-titles' ), get_comments_number() ) . '</a></li>';
				
			$toc .= '</ul><!-- #multipage-toc-' . $post->ID . '-->';
			
			if ( $this->options['toc-position'] === 'top' ) {
				$enhanced_content = $toc . $enhanced_content;
			}
			elseif ( $this->options['toc-position'] === 'bottom' ) {
				$enhanced_content .= $toc;
			}
		}

		return apply_filters( 'multipage_content', $enhanced_content );
	}
	
	/**
	 * Retrieve the subpage permalink.
	 *
	 * @since 1.0
	 */
	private function get_subpage_link( $page, $scroll_to_toc = false ) {
		$base = get_permalink();

		// If it's the first page the link is the base permalink
		if ( $page < 2 )
			return $base;
		
		if ( ! get_option('permalink_structure') || is_admin() || true == get_query_var('preview') )
			return add_query_arg( array('page' => $page ) );
		
		$subpage_link = trailingslashit( $base ) . user_trailingslashit( $page, 'page' );
		
		// Add the scroll_to_toc to table of content link
		if ( $scroll_to_toc == true )
			$subpage_link .= "#toc";

		return $subpage_link;
	}

	/**
	 * Get the int value for the priority level.
	 *
	 * @since 1.3.3
	 */
	public function get_priority_value( $priority = '' ) {
		switch ( $priority ) {
			case "lowest":
				return 100;
				break;
			case "low":
				return 50;
				break;
			case "high":
				return 10;
				break;
			case "highest":
				return 5;
				break;
			case "normal":
			default:
				return 20;
				break;
		}
	}

	/**
	 * Return a 404 page.
	 *
	 * @since 0.9
	 */
	private function return_404() {
		global $wp_query;
		
		$wp_query->set_404();
		status_header(404);
		return;
	}
	
	/**
	 * Hide the standard pagination.
	 *
	 * @since 0.6
	 */
	public static function hide_standard_pagination( $args ) {
		$args['echo'] = 0;
		return $args;
	}
	
	/**
	 * Hide featured image.
	 *
	 * @since 1.3
	 */ 
	public static function hide_post_thumbnail($content, $object_id, $meta_key, $single){
		// Return false if the current filter is that of a post thumbnail. Otherwise, return the original $content value.  
		return ( isset($meta_key) && '_thumbnail_id' === $meta_key && $single ) ? false : $content;
	}  

	/**
	 * Hide comments area.
	 *
	 * @since 1.0
	 */
	function hide_comments() {
		// Return an empty file.
		return dirname( __FILE__ ) . '/index.php';
	}

	/**
	 * Register available widgets.
	 *
	 * @since 1.3.3
	 *
	 * @uses register_widget()
	 * @return void
	 */
	public function widgets_init() {
		// Widgets loading
		if ( ! class_exists( 'Multipage_Widgets' ) )
			require_once( $this->plugin_directory . 'widgets/summary.php' );
		register_widget( 'Multipage_Widgets' );
	}
}

/**
 * Load plugin function during the WordPress init action
 *
 * @since 0.6
 */
function multipage_plugin_loader_init() {
	global $multipage_plugin_loader;

	$multipage_plugin_loader = new Multipage_Plugin_Loader();
}
add_action( 'init', 'multipage_plugin_loader_init', 0 ); // load before widgets_init at 1
