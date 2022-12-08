<?php
/**
 * Plugin Name:       ACF: Yandex Maps Field
 * Plugin URI:        https://wordpress.org/plugins/acf-yandex-map-fields/
 * Description:       A new field type for Advanced Custom Fields (ACF) that allows you to place single marker (with description) for each map.
 * Version:           1.0
 * Requires at least: 6.1.1
 * Requires PHP:      7.4 or higher
 * Author:            Roman Bondarenko
 * Author URI:        https://unrealthemes.site/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       acf-yandex-map
 * Domain Path:       /languages
 */

// Define constants.
defined( 'YMF_PLUGIN_BASE_NAME' ) or define( 'YMF_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
defined( 'YMF_PLUGIN_PATH' ) or define( 'YMF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
defined( 'YMF_PLUGIN_URL' ) or define( 'YMF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined('YMF_PLUGIN_FILE' ) or define( 'YMF_PLUGIN_FILE', __FILE__ );

class YMF_Yandex_Map_Plugin {

	// vars
	var $settings;

	function __construct() {

		// settings
		// - these will be passed into the field class.
		$this->settings = [
			'version' => '1.0.0',
			'url' => YMF_PLUGIN_URL,
			'path' => YMF_PLUGIN_PATH,
		];

		// include field
		add_action( 'acf/include_field_types', [$this, 'include_field'] ); 
		add_action( 'acf/register_fields', [$this, 'include_field'] );
	}

	function include_field( $version = false ) {
		
		// support empty $version
		if ( ! $version ) {
			$version = 5;
		}

		// load textdomain
		load_plugin_textdomain( 'acf-yandex-map', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );

		// include
		include_once( 'fields/class-acf-yandex-map-v' . $version . '.php' );

	}

}

// initialize
new YMF_Yandex_Map_Plugin();