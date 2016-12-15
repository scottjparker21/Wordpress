<?php
/**
 * Upgrade Multipage
 *
 * @since 1.3.3
 */
class Multipage_Plugin_Upgrade {
	/**
	 * Multipage Plugins Options
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->options = get_option( 'multipage' );

		//if ( version_compare( $this->options['version'], '1.3.0', '<' ) ) {
			//$this->upgrade_13( $this->options['version'] );
		//}
		
		echo var_dump( $this->options );
	}

	/**
	 * Run the Yoast SEO 1.5 upgrade routine
	 *
	 * @param string $version Current plugin version.
	 */
	private function upgrade_15( $version ) {
		// Clean up options and meta.
		//WPSEO_Options::clean_up( null, $version );
		//WPSEO_Meta::clean_up();

		// Add new capabilities on upgrade.
		//wpseo_add_capabilities();
		//'rewrite-title-priority' 			=> 'normal',
		//'rewrite-content-priority' 			=> 'normal',
		//'disable-tinymce-buttons' 			=> false
	}
}
?>