<?php

namespace XproElementorAddons\Inc;

defined( 'ABSPATH' ) || die();

class WPML_Social_Icon extends \WPML_Elementor_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'item';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'title' );
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_title( $field ) {
		switch ( $field ) {
			case 'title':
				return __( 'Social Icon: Title', 'xpro-elementor-addons' );
			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch ( $field ) {
			case 'title':
				return 'LINE';
			default:
				return '';
		}
	}
}
