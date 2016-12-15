<?php

/**
 * Display a settings page for Multipage Plugin
 *
 * @since 0.93
 */
class Multipage_Plugin_Main_Settings {
	/**
	 * Settings page identifier.
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'multipage-settings';
	
	/**
	 * Define our option array value.
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	const OPTION_NAME = 'multipage';

	/**
	 * The hook suffix assigned by add_submenu_page()
	 *
	 * @since 0.93
	 *
	 * @var string
	 */
	protected $hook_suffix = '';
	
	/**
	 * Initialize with an options array.
	 *
	 * @since 0.95
	 *
	 * @param array $options existing options
	 */
	public function __construct( $options = array() ) {
		if ( is_array( $options ) && ! empty( $options ) )
			$this->existing_options = $options;
		else
			$this->existing_options = array();
	}
	
	/**
	 * Add a menu item to WordPress admin.
	 *
	 * @since 0.93
	 *
	 * @uses add_utility_page()
	 * @return string page hook
	 */
	public static function menu_item() {
		$main_settings = new Multipage_Plugin_Main_Settings();
		$hook_suffix = add_options_page(
			esc_html( __( 'Multipage Settings', 'sgr-nextpage-titles' ) ), // page <title>
			'Multipage', // menu title
			'manage_options', // capability needed
			self::PAGE_SLUG, // what should I call you?
			array( &$main_settings, 'settings_page' ), // pageload callback
			'none' // to be replaced by Multipage dashicon
		);
		// conditional load CSS, scripts
		if ( $hook_suffix ) {
			$main_settings->hook_suffix = $hook_suffix;
			register_setting( $hook_suffix, self::OPTION_NAME, array( 'Multipage_Plugin_Main_Settings', 'sanitize_options' ) );
			add_action( 'load-' . $hook_suffix, array( &$main_settings, 'onload' ) );
		}
		return $hook_suffix;
	}
	
	/**
	 * Load stored options and scripts on settings page view.
	 *
	 * @since 0.95
	 *
	 * @uses get_option() load existing options
	 * @return void
	 */
	public function onload() {
		$options = get_option( self::OPTION_NAME );
		if ( ! is_array( $options ) )
			$options = array();
		$this->existing_options = $options;
		$this->settings_api_init();
	}
	
	/**
	 * Load the settings page.
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function settings_page() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		add_action( 'nextpage_titles_settings_after_header_' . $this->hook_suffix, array( 'Multipage_Plugin_Main_Settings', 'after_header' ) );
		Multipage_Plugin_Settings::settings_page_template( $this->hook_suffix, esc_html( __( 'Multipage Settings', 'sgr-nextpage-titles' ) ) );
	}
	
	/**
	 * Multipages after header.
	 *
	 * @since 0.95
	 *
	 * @return void
	 */
	public static function after_header() {
		echo "";
	}
	
	/**
	 * Hook into the settings API.
	 *
	 * @since 0.93
	 *
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 * @return void
	 */
	private function settings_api_init() {
		if ( ! isset( $this->hook_suffix ) )
			return;

		// Multipages main settings
		$section = 'multipage';
		add_settings_section(
			$section,
			'', // no title for main section
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		add_settings_field(
			'comments-oofp',
			esc_html( __( 'Comments', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_comments_oofp' ),
			$this->hook_suffix,
			'multipage',
			array( 'label_for' => 'comments-oofp' )
		);

		add_settings_field(
			'unhide-pagination',
			esc_html( __( 'Standard Pagination', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_unhide_pagination' ),
			$this->hook_suffix,
			'multipage',
			array( 'label_for' => 'unhide-pagination' )
		);
		
		$section = 'toc';
		add_settings_section(
			$section,
			esc_html( __( 'Table of contents', 'sgr-nextpage-titles' ) ),
			array( &$this, 'section_header' ),
			$this->hook_suffix
		);

		add_settings_field(
			'toc-oofp',
			esc_html( __( 'Only on the first page', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_toc_oofp' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-oofp' )
		);
		
		add_settings_field(
			'toc-position',
			_x( 'Position', 'Desired position of a the table of the contents.', 'sgr-nextpage-titles' ),
			array( &$this, 'display_position' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-position' )
		);
		
		add_settings_field(
			'toc-page-labels',
			_x( 'Page labels', 'Select which type of page labels to display.', 'sgr-nextpage-titles' ),
			array( &$this, 'display_page_labels' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-page-labels' )
		);
		
		add_settings_field(
			'toc-hide-header',
			esc_html( __( 'Hide header', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_hide_header' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-hide-header' )
		);

		add_settings_field(
			'toc-comments-link',
			esc_html( __( 'Comments link', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_comments_link' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'toc-comments-link' )
		);
		
		$section = 'advanced';
		add_settings_section(
			$section,
			esc_html( __( 'Advanced', 'sgr-nextpage-titles' ) ),
			array( &$this, 'section_header_advanced' ),
			$this->hook_suffix
		);

		add_settings_field(
			'rewrite-title-priority',
			esc_html( __( 'Rewrite title priority', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_rewrite_title_priority' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'rewrite-title-priority' )
		);
		
		add_settings_field(
			'rewrite-content-priority',
			esc_html( __( 'Rewrite content priority', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_rewrite_content_priority' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'rewrite-content-priority' )
		);
		
		add_settings_field(
			'disable-tinymce-buttons',
			esc_html( __( 'Disable TinyMCE Buttons', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_disable_tinymce_buttons' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'disable-tinymce-buttonse' )
		);

		/* add_settings_field(
			'disable-cache',
			esc_html( __( 'Disable Cache', 'sgr-nextpage-titles' ) ),
			array( &$this, 'display_disable_multipage_cache' ),
			$this->hook_suffix,
			$section,
			array( 'label_for' => 'disable-cache' )
		); */
	}
	
	/**
	 * Introduction to the main settings section.
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function section_header() {
		//echo "";
	}

	/**
	 * Introduction to the main settings section.
	 *
	 * @since 1.3.3
	 *
	 * @return void
	 */
	public function section_header_advanced() {
		echo "<p>" . esc_html( __( 'Please leave this settings to their default values, change only if you really know what to do.', 'sgr-nextpage-titles' ) ) . "</p>";
	}

	/**
	 * Display a checkbox to set if the comments must appear only on the first page of the post.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function display_comments_oofp() {
		$key = 'comments-oofp';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Show the comments only on the first page.', 'sgr-nextpage-titles' ) );
		echo '</label>';
	}

	/**
	 * Display a checkbox to unhide the standard WordPress pagination on multipage posts.
	 *
	 * @since 1.4
	 *
	 * @return void
	 */
	public function display_unhide_pagination() {
		$key = 'unhide-pagination';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Unhide the default WordPress pagination.', 'sgr-nextpage-titles' ) );
		echo '</label>';
	}
	
	/**
	 * Display a checkbox to set if the table of contents must appear only on the first page of the post.
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function display_toc_oofp() {
		$key = 'toc-oofp';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Show the table of contents only on the first page of the post.', 'sgr-nextpage-titles' ) );
		echo '</label>';
	}
	
	/**
	 * Where would you display the table of contents?
	 *
	 * @since 0.95
	 *
	 * @param array $extra_attributes custom form attributes
	 * @return void
	 */
	public function display_position( $extra_attributes = array() ) {
		$key = 'toc-position';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';
		
		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'post-toc-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo '>' . self::position_choices( isset( $this->existing_options[$key] ) ? $this->existing_options[$key] : '' ) . '</select>';
	}
	
	/**
	 * Describe page labels choices.
	 *
	 * @since 0.95
	 *
	 * @return array page labels descriptions keyed by page labels choice
	 */
	public static function page_labels_descriptions() {
		return array(
			'numbers' => esc_html( __( 'Display numbers before the subpage title.', 'sgr-nextpage-titles' ) ),
			'pages' => esc_html( __( 'Display "Page #" before the subpage title.', 'sgr-nextpage-titles' ) ),
			'hidden' => esc_html( __( 'Hide subpage labels, display only the title.', 'sgr-nextpage-titles' ) ),
		);
	}
	
	/**
	 * Which kind of page lables do you want to display?
	 *
	 * @since 0.95
	 *
	 * @param array $extra_attributes custom form attributes
	 * @return void
	 */
	public function display_page_labels( $extra_attributes = array() ) {
		$key = 'toc-page-labels';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = 'numbers';
		
		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'page-label-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );
		$name = esc_attr( $name );

		$descriptions = self::page_labels_descriptions();

		$page_labels_choices = self::$page_labels_choices;
		$choices = array();

		foreach( $page_labels_choices as $page_labels ) {
			$choice = '<label><input type="radio" name="' . $name . '" value="' . $page_labels . '"';
			$choice .= checked( $page_labels, $existing_value, false );
			$choice .= ' /> ';

			$choice .= $page_labels;
			if ( isset( $descriptions[$page_labels] ) )
				$choice .= esc_html( ' — ' . $descriptions[$page_labels] );
			$choice .= '</label>';

			$choices[] = $choice;
			unset( $choice );
		}

		if ( ! empty( $choices ) ) {
			echo '<fieldset id="' . $id . '"';
			if ( isset( $class ) && $class )
				echo ' class="' . $class . '"';
			echo '><div>';
			echo implode( '</div><div>', $choices );
			echo '</div></fieldset>';
		}
	}
	
	/**
	 * Display a checkbox to set if hide the table of contents header
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function display_hide_header() {
		$key = 'toc-hide-header';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Hide the table of contents header.', 'sgr-nextpage-titles' ) );
		echo '</label>';
	}
	
	/**
	 * Display a checkbox to set if hide the table of contents header
	 *
	 * @since 0.93
	 *
	 * @return void
	 */
	public function display_comments_link() {
		$key = 'toc-comments-link';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Add a link for the comments inside the table of contents.', 'sgr-nextpage-titles' ) );
		echo '</label>';
		echo '<p id="comments-link-description" class="description">';
		echo esc_html( __( 'If comments are enabled, this will display, inside the table of contents, a link for the comments list.', 'sgr-nextpage-titles' ) );
		echo '</p>';	
	}

	/**
	 * Display a select box to force the priority of the rewrite title filter.
	 *
	 * @since 1.3.3
	 *
	 * @return void
	 */
	public function display_rewrite_title_priority( $extra_attributes = array() ) {
		$key = 'rewrite-title-priority';

		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';
		
		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'title-priority-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo '>' . self::priority_choices( isset( $this->existing_options[$key] ) ? $this->existing_options[$key] : '' ) . '</select>';
		echo '<p class="description">';
		echo esc_html( __( 'Some plugins need this higher in order to correctly show the subpage title instead of "Page # of #". If the title works good please leave this to normal.', 'sgr-nextpage-titles' ) );
		echo '</p>';		
	}

	/**
	 * Display a select box to force the priority of the rewrite content filter.
	 *
	 * @since 1.3.3
	 *
	 * @return void
	 */
	public function display_rewrite_content_priority( $extra_attributes = array() ) {
		global $multipage_plugin_loader;

		$key = 'rewrite-content-priority';

		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';
		
		extract( self::parse_form_field_attributes(
			$extra_attributes,
			array(
				'id' => 'content-priority-' . $key,
				'class' => '',
				'name' => self::OPTION_NAME . '[' . $key . ']'
			)
		) );

		echo '<select name="' . esc_attr( $name ) . '" id="' . $id . '"';
		if ( isset( $class ) && $class )
			echo ' class="' . $class . '"';
		echo '>' . self::priority_choices( isset( $this->existing_options[$key] ) ? $this->existing_options[$key] : '' ) . '</select>';
		echo '<p class="description">';
		echo esc_html( __( 'This value affects the position where the table of contents is displayed. e.g. social buttons or related posts. If the table of contents position looks good please leave this to normal.', 'sgr-nextpage-titles' ) );		
		echo '</p>';
	}

	/**
	 * Display a checkbox to disable the tinyMCE customization.
	 *
	 * @since 1.3.3
	 *
	 * @return void
	 */
	public function display_disable_tinymce_buttons() {
		$key = 'disable-tinymce-buttons';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Disable TinyMCE Buttons.', 'sgr-nextpage-titles' ) );
		echo '</label>';
		echo '<p id="disable-tinymce-description" class="description">';
		echo esc_html( __( 'On older WordPress versions the Multipage buttons on TinyMCE could create problems. If you are running a WordPress version > 3.9 please leave this unchecked.', 'sgr-nextpage-titles' ) );
		echo '</p>';
	}

	/**
	 * Display a checkbox to disable the object cache.
	 *
	 * @since 1.3.3
	 *
	 * @return void
	 */
	public function display_disable_multipage_cache() {
		$key = 'disable-cache';
		
		if ( isset( $this->existing_options[$key] ) && $this->existing_options[$key] )
			$existing_value = $this->existing_options[$key];
		else
			$existing_value = '';

		echo '<label><input type="checkbox" name="' . self::OPTION_NAME . '[' . $key . ']" id="' . $key . '" value="1"';
		checked( $existing_value );
		echo ' /> ';
		echo esc_html( __( 'Disable Multipage Caching System.', 'sgr-nextpage-titles' ) );
		echo '</label>';
		echo '<p class="description">';
		echo esc_html( __( 'Sometimes, after editing a multipage post, you could still have an older table of contents. If you are experiencing this problem, please disable the Multipage Caching System.', 'sgr-nextpage-titles' ) );
		echo '</p>';
	}
	
	/**
	 * Place the table of contents above the post content, below the post content, or hide it.
	 *
	 * @since 0.95
	 *
	 * @var array
	 */
	public static $position_choices = array( 'bottom', 'top', 'hidden' );

	/**
	 * Define the filters priority.
	 *
	 * @since 1.3.3
	 *
	 * @var array
	 */
	public static $priority_choices = array( 'highest', 'high', 'normal', 'low', 'lowest' );
	
	/**
	 * Choose the position of the table of contents above the post content, below the post content, or hide it.
	 *
	 * @since 0.95
	 *
	 * @param string $existing_value stored option value
	 * @return string HTML <option>s
	 */
	public static function position_choices( $existing_value = 'bottom' ) {
		if ( ! ( is_string( $existing_value) && $existing_value && in_array( $existing_value, self::$position_choices ) ) )
			$existing_value = 'bottom';

		$options = '';
		foreach( self::$position_choices as $position ) {
			$options .= '<option value="' . $position . '"' . selected( $position, $existing_value, false ) . '>';
			$options .= $position;
			$options .= '</option>';
		}
		return $options;
	}

	/**
	 * Choose the priority.
	 *
	 * @since 1.3.3
	 *
	 * @param string $existing_value stored option value
	 * @return string HTML <option>s
	 */
	public static function priority_choices( $existing_value = 'normal' ) {
		if ( ! ( is_string( $existing_value) && $existing_value && in_array( $existing_value, self::$priority_choices ) ) )
			$existing_value = 'normal';

		$options = '';
		foreach( self::$priority_choices as $priority ) {
			$options .= '<option value="' . $priority . '"' . selected( $priority, $existing_value, false ) . '>';
			$options .= $priority;
			$options .= '</option>';
		}
		return $options;
	}

	/**
	 * Declare different page label styles.
	 *
	 * @since 0.95
	 *
	 * @var array
	 */
	public static $page_labels_choices = array( 'numbers', 'pages', 'hidden' );
	
	/**
	 * Choose different page label styles.
	 *
	 * @since 0.95
	 *
	 * @param string $existing_value stored option value
	 * @return string HTML <option>s
	 */
	public static function page_labels_choices( $existing_value = 'numbers' ) {
		if ( ! ( is_string( $existing_value) && $existing_value && in_array( $existing_value, self::$page_labels_choices ) ) )
			$existing_value = 'numbers'; // Verificare questo

		$options = '';
		foreach( self::$page_labels_choices as $page_labels ) {
			$options .= '<option value="' . $page_labels . '"' . selected( $page_labels, $existing_value, false ) . '>';
			if ( isset( $descriptions[$page_labels] ) )
				$options .= esc_html( $descriptions[$page_labels] );
			else
				$options .= $page_labels;
			$options .= '</option>';
		}
		return $options;
	}

	/**
	 * Clean user inputs before saving to database.
	 *
	 * @since 0.93
	 *
	 * @param array $options form options values
	 * @return array $options sanitized options
	 */
	public static function sanitize_options( $options ) {
		global $multipage_plugin_loader;
		
		$clean_options = array(); // Fresh options
		$default_options = $multipage_plugin_loader->multipage_settings_defaults; // Default options

		$key = 'comments-oofp';
		$clean_options[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'unhide-pagination';
		$clean_options[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'toc-oofp';
		$clean_options[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];
		
		$key = 'toc-position';
		$clean_options[ $key ] = isset( $options[ $key ] ) && in_array( $options[ $key ], self::$position_choices, true ) ? $options[ $key ] : $default_options[ $key ];
		
		$key = 'toc-page-labels';
		$clean_options[ $key ] = in_array( $options[ $key ], self::$page_labels_choices, true ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'toc-hide-header';
		$clean_options[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'toc-comments-link';
		$clean_options[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'rewrite-title-priority';
		$clean_options[ $key ] = isset( $options[ $key ] ) && in_array( $options[ $key ], self::$priority_choices, true ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'rewrite-content-priority';
		$clean_options[ $key ] = isset( $options[ $key ] ) && in_array( $options[ $key ], self::$priority_choices, true ) ? $options[ $key ] : $default_options[ $key ];

		$key = 'disable-tinymce-buttons';
		$clean_options[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $default_options[ $key ];	

		return $clean_options;
	}
	
	/**
	 * Clean up custom form field attributes (fieldset, input, select) before use.
	 *
	 * @since 0.95
	 * @param array $attributes attributes that may possibly map to a HTML attribute we would like to use
	 * @param array $default_values fallback values
	 * @return array sanitized values unique to each field
	 */
	public static function parse_form_field_attributes( $attributes, $default_values ) {
		$attributes = wp_parse_args( (array) $attributes, $default_values );

		if ( ! empty( $attributes['id'] ) )
			$attributes['id'] = sanitize_html_class( $attributes['id'] );
		if ( ! empty( $attributes['class'] ) ) {
			$classes = explode( ' ', $attributes['class'] );
			array_walk( $classes, 'sanitize_html_class' );
			$attributes['class'] = implode( ' ', $classes );
		}
		return $attributes;
	}
}
?>