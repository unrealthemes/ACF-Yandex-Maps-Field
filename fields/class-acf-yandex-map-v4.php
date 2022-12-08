<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class YMF_Acf_Field_Yandex_Map extends acf_field {

	public $settings,
			$defaults;

	public function __construct( $settings ) {

		$this->name = 'yandex-map';

		$this->label = __( 'Yandex Map', 'acf-yandex-map' );

		$this->category = 'jQuery';

		$this->defaults = [
			'height'     => '500',
			'center_lat' => '50.450001',
			'center_lng' => '30.523333',
			'zoom'       => '10',
			'map_type'   => 'map'
		];

		$this->l10n = [
			'error' => __( 'Error! Please enter a higher value', 'acf-yandex-map' ),
		];

		$this->settings = $settings;
		// do not delete!
		parent::__construct();

		apply_filters( 'acf/field_return_value', [$this, 'field_return_value'] );
	}

	function format_value( $value, $post_id, $field ) {

		// Bail early if no value.
		if ( ! $value ) {
			return false;
		}

		if ( $field['type'] != 'yandex-map' ) {
			return $value;
		}

		if ( empty($field['return_format']) || $field['return_format'] == 'html' ) {

			$url = $this->settings['url'];
			$version = $this->settings['version'];
			wp_register_script( 'yandex-map-api', '//api-maps.yandex.ru/2.1/?lang=' . get_bloginfo( 'language' ), ['jquery'], $version );
			wp_register_script( 'yandex-map-frontend', "{$url}js/acf-yandex-map-frontend.js", ['yandex-map-api'], 'acf-yandex-map' );
			wp_enqueue_script( 'yandex-map-frontend' );
			$map_id = uniqid('map_');
			wp_localize_script( 'yandex-map-frontend', $map_id, [
				'params' => $value
			] );
			$zoom_controll = $field['zoom_control'] ? $field['zoom_control'] : false;
			$scroll_zoom = $field['scroll_zoom'] ? $field['scroll_zoom'] : false;
			$map_height = ( (int)$field['height'] > 0 ) ? $field['height'] : $this->defaults['height'];
			// Return.
			return sprintf( 
							'<div class="yandex-map" id="%s" data-zoom-controll="%s" data-scroll-zoom="%s" style="width:auto;height:%dpx"></div>', 
							esc_attr( $map_id ), 
							esc_attr( $zoom_controll ),
							esc_attr( $scroll_zoom ),
							esc_attr( $map_height ) 
						);

		} else if ( ! $field['return_format'] || $field['return_format'] == 'array' ) {

			return json_decode($value, true);

		} else if ( ! $field['return_format'] || $field['return_format'] == 'json' ) {

			return $value;
		}
	}

	public function create_options( $field ) {

		// key is needed in the field names to correctly save the data
		$key = $field['name'];
		// Create Field Options HTML
		?>
		<tr class="field_option field_option_<?php echo esc_attr( $this->name ); ?>">
			<td class="label">
				<label><?php echo __( 'Height', 'acf-yandex-map' ); ?></label>
				<p class="description"><?php echo __( 'Set map height', 'acf-yandex-map' ); ?></p>
			</td>
			<td>
				<?php

				do_action( 'acf/create_field', [
					'type'   => 'number',
					'name'   => 'fields[' . $key . '][height]',
					'value'  => $field['height'],
					'layout' => 'horizontal',
					'append' => 'px'
				] );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo esc_attr( $this->name ); ?>">
			<td class="label">
				<label><?php echo __( 'Map type', 'acf-yandex-map' ); ?></label>
			</td>
			<td>
				<?php

				do_action( 'acf/create_field', [
					'type'    => 'select',
					'name'    => 'fields[' . $key . '][map_type]',
					'value'   => $field['map_type'],
					'layout'  => 'horizontal',
					'choices' => [
						'map'       => __( 'Map', 'acf-yandex-map' ),
						'satellite' => __( 'Satellite', 'acf-yandex-map' ),
						'hybrid'    => __( 'Hybrid', 'acf-yandex-map' ),
					]
				] );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo esc_attr( $this->name ); ?>">
			<td class="label">
				<label><?php echo __( 'Zoom', 'acf-yandex-map' ); ?></label>
				<p class="description"><?php echo __( 'Set map zoom', 'acf-yandex-map' ); ?></p>
			</td>
			<td>
				<?php

				do_action( 'acf/create_field', [
					'type'   => 'number',
					'name'   => 'fields[' . $key . '][zoom]',
					'value'  => $field['zoom'],
					'layout' => 'horizontal',
					'min'    => '0',
					'max'    => '18'
				] );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo esc_attr( $this->name ); ?>">
			<td class="label">
				<label><?php echo __( 'Zoom Control', 'acf-yandex-map' ); ?></label>
			</td>
			<td>
				<?php

				do_action( 'acf/create_field', [
					'type'   => 'radio',
					'name'   => 'fields[' . $key . '][zoom_control]',
					'value'  => $field['zoom_control'],
					'layout' => 'horizontal',
					'choices' => [
						0 => __( 'Disabled', 'acf-yandex-map' ),
						1 => __( 'Enable', 'acf-yandex-map' ),
					]
				] );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo esc_attr( $this->name ); ?>">
			<td class="label">
				<label><?php echo __( 'Scroll Zoom', 'acf-yandex-map' ); ?></label>
			</td>
			<td>
				<?php

				do_action( 'acf/create_field', [
					'type'   => 'radio',
					'name'   => 'fields[' . $key . '][scroll_zoom]',
					'value'  => $field['scroll_zoom'],
					'layout' => 'horizontal',
					'choices' => [
						0 => __( 'Disabled', 'acf-yandex-map' ),
						1 => __( 'Enable', 'acf-yandex-map' ),
					]
				] );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo esc_attr( $this->name ); ?>">
			<td class="label">
				<label><?php echo __( 'Latitude', 'acf-yandex-map' ); ?></label>
			</td>
			<td>
				<?php

				do_action( 'acf/create_field', [
					'type'        => 'text',
					'name'        => 'fields[' . $key . '][center_lat]',
					'value'       => $field['center_lat'],
					'layout'      => 'horizontal',
					'prepend'     => 'lat',
					'placeholder' => $this->defaults['center_lat']
				] );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo esc_attr( $this->name ); ?>">
			<td class="label">
				<label><?php echo __( 'Longitude', 'acf-yandex-map' ); ?></label>
			</td>
			<td>
				<?php

				do_action( 'acf/create_field', [
					'type'        => 'text',
					'name'        => 'fields[' . $key . '][center_lng]',
					'value'       => $field['center_lng'],
					'layout'      => 'horizontal',
					'prepend'     => 'lng',
					'placeholder' => $this->defaults['center_lng']
				] );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo esc_attr( $this->name ); ?>">
			<td class="label">
				<label><?php echo __( 'Return Format', 'acf-yandex-map' ); ?></label>
			</td>
			<td>
				<?php

				do_action( 'acf/create_field', [
					'type'   => 'radio',
					'name'   => 'fields[' . $key . '][return_format]',
					'value'  => $field['return_format'],
					'layout' => 'horizontal',
					'choices' => [
						'html' => __( 'Map Html', 'acf-yandex-map' ),
						'array' => __( 'Map Array', 'acf-yandex-map' ),
						'json' => __( 'Map Json', 'acf-yandex-map' ),
					]
				] );

				?>
			</td>
		</tr>
		<?php
	}

	public function create_field( $field ) {

		wp_enqueue_script( 'acf-yandex' );

		$saved = json_decode( $field['value'], true );

		$data = [];
		$data['center_lat'] = $saved['center_lat'] ?: $field['center_lat'];
		$data['center_lng'] = $saved['center_lng'] ?: $field['center_lng'];
		$data['zoom'] = $saved['zoom'] ?: $field['zoom'];
		$data['type'] = $this->get_map_type( $saved['type'], $field );
		$data['marks'] = $saved['marks'] ?: [];
		$zoom_controll = $field['zoom_control'] ? $field['zoom_control'] : false;
		$scroll_zoom = $field['scroll_zoom'] ? $field['scroll_zoom'] : false;
		$map_height = ( (int)$field['height'] > 0 ) ? $field['height'] : $this->defaults['height'];
		?>
		<input type="hidden" 
				name="<?php echo esc_attr( $field['name'] ); ?>"
				value="<?php echo esc_attr( wp_json_encode( $data ) ); ?>"
				class="map-input"/>

		<div class="map" 
				data-zoom-controll="<?php echo esc_attr( $zoom_controll ); ?>"
				data-scroll-zoom="<?php echo esc_attr( $scroll_zoom ); ?>"
				style="width: auto;height:<?php echo esc_attr( $map_height ); ?>px"></div>

		<?php
	}

	public function input_admin_enqueue_scripts() {
		
		$url = $this->settings['url'];
		$version = $this->settings['version'];

		wp_register_script( 'yandex-map-api', '//api-maps.yandex.ru/2.1/?lang=' . get_bloginfo( 'language' ), ['jquery'], $version );
		wp_register_script( 'acf-yandex', "{$url}js/acf-yandex-map-admin.js", ['yandex-map-api'], $version, true );

		wp_localize_script( 'acf-yandex', 'acf_yandex_locale', [
			'map_init_fail'      => __( 'Error init Yandex map! Field not found.', 'acf-yandex-map' ),
			'mark_hint'          => __( 'Drag mark. Right click for remove', 'acf-yandex-map' ),
			'btn_clear_all'      => __( 'Clear all', 'acf-yandex-map' ),
			'btn_clear_all_hint' => __( 'Remove all marks', 'acf-yandex-map' ),
			'mark_save'          => __( 'Save', 'acf-yandex-map' ),
			'mark_remove'        => __( 'Remove', 'acf-yandex-map' ),
		] );
	}

	private function get_map_type( $value, $field ) {

		$allowed = [
			'map',
			'satellite',
			'hybrid'
		];

		$result = in_array( trim( $value ), $allowed, true ) ? $value : $field['map_type'];

		if ( ! $result ) {
			$result = $this->defaults['map_type'];
		}

		return $result;

	}

}

new YMF_Acf_Field_Yandex_Map( [] );