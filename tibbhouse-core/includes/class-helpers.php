<?php
/**
 * Shared helper utilities used across the plugin.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static helper functions shared by every subsystem.
 */
class Tibbhouse_Helpers {

	/**
	 * All CPT slugs managed by this plugin.
	 *
	 * @return string[]
	 */
	public static function post_types() {
		return array( 'treatments', 'conditions', 'knowledge', 'practitioners', 'locations' );
	}

	/**
	 * Sanitize a repeater field (array of associative arrays) posted from JS.
	 *
	 * @param mixed $raw Raw repeater payload, typically JSON-decoded.
	 * @param array $field_map Map of field key => sanitize callback.
	 * @return array
	 */
	public static function sanitize_repeater( $raw, array $field_map ) {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$clean = array();

		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$clean_row = array();
			foreach ( $field_map as $key => $callback ) {
				$value               = isset( $row[ $key ] ) ? $row[ $key ] : '';
				$clean_row[ $key ]   = is_callable( $callback ) ? call_user_func( $callback, $value ) : sanitize_text_field( $value );
			}

			// Skip fully empty rows.
			if ( count( array_filter( $clean_row ) ) > 0 ) {
				$clean[] = $clean_row;
			}
		}

		return $clean;
	}

	/**
	 * Render a `<select multiple>` populated with related posts of a given type.
	 *
	 * @param string $meta_key    Meta key storing the array of related post IDs.
	 * @param int    $post_id     Current post ID.
	 * @param string $post_type   Post type to query for options.
	 * @param string $label       Field label.
	 */
	public static function render_relationship_select( $meta_key, $post_id, $post_type, $label ) {
		$selected = get_post_meta( $post_id, $meta_key, true );
		$selected = is_array( $selected ) ? $selected : array();

		$options = get_posts(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			)
		);

		echo '<p><label for="' . esc_attr( $meta_key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br />';
		echo '<select multiple="multiple" id="' . esc_attr( $meta_key ) . '" name="' . esc_attr( $meta_key ) . '[]" style="width:100%;min-height:120px;">';
		foreach ( $options as $option ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $option->ID,
				in_array( (int) $option->ID, array_map( 'intval', $selected ), true ) ? 'selected="selected"' : '',
				esc_html( $option->post_title )
			);
		}
		echo '</select></p>';
	}

	/**
	 * Get a transient-cached value, regenerating it via the callback when missing.
	 *
	 * @param string   $key       Transient key.
	 * @param callable $generator Callback that returns the fresh value.
	 * @param int      $ttl       Time to live in seconds.
	 * @return mixed
	 */
	public static function cached( $key, callable $generator, $ttl = HOUR_IN_SECONDS ) {
		$value = get_transient( $key );
		if ( false !== $value ) {
			return $value;
		}

		$value = call_user_func( $generator );
		set_transient( $key, $value, $ttl );
		return $value;
	}

	/**
	 * Locate a template file, preferring the active theme's override.
	 *
	 * Theme override path: {theme}/tibbhouse/{template}
	 *
	 * @param string $template_name e.g. 'single-treatments.php'.
	 * @return string Absolute path to the template file.
	 */
	public static function locate_template( $template_name ) {
		$theme_override = locate_template( array( 'tibbhouse/' . $template_name ) );
		if ( $theme_override ) {
			return $theme_override;
		}
		return TIBBHOUSE_CORE_PATH . 'templates/' . $template_name;
	}
}
