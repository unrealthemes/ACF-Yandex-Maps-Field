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
		// settings page
		add_action( 'admin_init', [$this, 'ymf_settings_init'] );
		add_action( 'admin_menu', [$this, 'options_page'] );

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

	function ymf_settings_init() {

		// Register a new setting for "ymf" page.
		register_setting( 'acf-yandex-map', 'ymf_options' );
	
		// Register a new section in the "ymf" page.
		add_settings_section(
			'ymf_section_developers',
			'', [$this, 'section_developers_callback'],
			'acf-yandex-map'
		);
		// Register a new field in the "ymf_section_developers" section, inside the "ymf" page.
		add_settings_field(
			'ymf_field_api_key', // As of WP 4.6 this value is used only internally.
							  	 // Use $args' label_for to populate the id inside the callback.
			__( 'Yandex api key', 'acf-yandex-map' ),
			[$this, 'field_api_key'],
			'acf-yandex-map',
			'ymf_section_developers',
			[
				'label_for' => 'ymf_field_api_key',
				'class' => 'ymf_row',
				'ymf_custom_data' => 'custom',
			]
		);
	}
	
	function section_developers_callback( $args ) {

	}
	
	function field_api_key( $args ) {

		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'ymf_options' );
		?>
		<input 	type="text"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				data-custom="<?php echo esc_attr( $args['ymf_custom_data'] ); ?>"
				name="ymf_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
				value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : null; ?>">

		<?php
	}
	
	function options_page() {
		
		add_submenu_page(
			'options-general.php',
			'ACF Yandex Map Settings',
			'ACF Yandex Map Settings',
			'manage_options',
			'acf-yandex-map',
			[$this, 'options_page_html']
		);
	}
	
	function options_page_html() {

		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
	
		// add error/update messages
	
		// check if the user have submitted the settings
		// WordPress will add the "settings-updated" $_GET parameter to the url
		// if ( isset( $_GET['settings-updated'] ) ) {
		// 	// add settings saved message with the class of "updated"
		// 	add_settings_error( 'ymf_messages', 'ymf_message', __( 'Settings Saved', 'acf-yandex-map' ), 'updated' );
		// }
	
		// show error/update messages
		settings_errors( 'ymf_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "ymf"
				settings_fields( 'acf-yandex-map' );
				// output setting sections and their fields
				// (sections are registered for "ymf", each field is registered to a specific section)
				do_settings_sections( 'acf-yandex-map' );
				// output save settings button
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

}

// initialize
new YMF_Yandex_Map_Plugin();