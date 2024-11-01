<?php

class VkCommerce_Admin_Form {
	const FIELD_TEXT = 'text';
	const FIELD_PASSWORD = 'password';
	const FIELD_NUMBER = 'number';
	const FIELD_EMAIL = 'email';
	const FIELD_URL = 'url';
	const FIELD_TEL = 'tel';
	const FIELD_TEXTAREA = 'textarea';
	const FIELD_SELECT = 'select';
	const FIELD_MULTISELECT = 'multiselect';
	const FIELD_RADIO = 'radio';
	const FIELD_CHECKBOX = 'checkbox';

	const CUSTOM_FIELD_OUTPUT_ACTION = 'vkcommerce_admin_form_output_field_%s';


	private static function output_template( $template, $data ) {
		extract( $data );

		require __DIR__ . '/views/fields/' . $template;
	}

	public static function output_field( $field_data ) {
		if ( empty( $field_data['type'] ) ) {
			return;
		}

		if ( ! isset( $field_data['name'] ) ) {
			$field_data['name'] = '';
		}
		if ( ! isset( $field_data['label'] ) ) {
			$field_data['label'] = ! empty( $field_data['name'] ) ? $field_data['name'] : '';
		}
		if ( ! isset( $field_data['id'] ) ) {
			$field_data['id'] = '';
		}
		if ( ! isset( $field_data['class'] ) ) {
			$field_data['class'] = '';
		}
		if ( ! isset( $field_data['css'] ) ) {
			$field_data['css'] = '';
		}
		if ( ! isset( $field_data['description'] ) ) {
			$field_data['description'] = '';
		}
		if ( ! isset( $field_data['placeholder'] ) ) {
			$field_data['placeholder'] = '';
		}
		if ( ! isset( $field_data['default'] ) ) {
			$field_data['default'] = '';
		}
		if ( ! isset( $field_data['value'] ) ) {
			$field_data['value'] = ! isset( $field_data['no_option'] ) ?
				( ! empty( $field_data['name'] )
					? get_option( $field_data['name'], $field_data['default'] )
					: $field_data['default'] )
				: $field_data['default'];
		}

		$attributes = array();
		if ( 'multiselect' === $field_data['type'] ) {
			$attributes['multiple'] = 'multiple';
		}

		if ( ! empty( $field_data['attributes'] ) && is_array( $field_data['attributes'] ) ) {
			foreach ( $field_data['attributes'] as $attribute => $attribute_value ) {
				$attributes[ $attribute ] = $attribute_value;
			}
		}

		$field_data['attributes'] = $attributes;

		// Switch based on type.
		switch ( $field_data['type'] ) {
			// Standard text inputs and subtypes like 'number'.
			case self::FIELD_TEXT:
			case self::FIELD_PASSWORD:
			case self::FIELD_NUMBER:
			case self::FIELD_EMAIL:
			case self::FIELD_URL:
			case self::FIELD_TEL:
				self::output_template( 'html-text.php', $field_data );

				break;

			// Textarea.
			case self::FIELD_TEXTAREA:
				self::output_template( 'html-textarea.php', $field_data );

				break;

			// Select boxes.
			case self::FIELD_SELECT:
			case self::FIELD_MULTISELECT:
				self::output_template( 'html-select.php', $field_data );

				break;

			// Radio inputs.
			case self::FIELD_RADIO:
				self::output_template( 'html-radio.php', $field_data );

				break;

			// Checkbox input.
			case self::FIELD_CHECKBOX:
				self::output_template( 'html-checkbox.php', $field_data );

				break;

			// Default: run an action.
			default:
				do_action( sprintf( self::CUSTOM_FIELD_OUTPUT_ACTION, $field_data['type'] ), $field_data );
				break;
		}
	}
}
