<?php
/**
 * REST API exposure for custom fields, relationships and taxonomy values.
 *
 * Custom fields are already exposed individually via register_meta()
 * (see class-fields.php). This class adds convenience aggregate fields so
 * headless/JS clients don't need N+1 requests for relationships and terms.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers extra REST fields on every Tibb House CPT.
 */
class Tibbhouse_Rest {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Rest|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Rest
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into rest_api_init.
	 */
	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_fields' ) );
	}

	/**
	 * Register the `tibbhouse_related` and `tibbhouse_taxonomies` REST fields
	 * on every managed post type.
	 */
	public function register_fields() {
		foreach ( Tibbhouse_Helpers::post_types() as $post_type ) {
			register_rest_field(
				$post_type,
				'tibbhouse_related',
				array(
					'get_callback' => array( $this, 'get_related_field' ),
					'schema'       => array(
						'description' => __( 'Resolved related content grouped by relationship label.', 'tibbhouse-core' ),
						'type'        => 'object',
					),
				)
			);

			register_rest_field(
				$post_type,
				'tibbhouse_taxonomies',
				array(
					'get_callback' => array( $this, 'get_taxonomies_field' ),
					'schema'       => array(
						'description' => __( 'All taxonomy terms attached to this post, keyed by taxonomy slug.', 'tibbhouse-core' ),
						'type'        => 'object',
					),
				)
			);
		}
	}

	/**
	 * REST callback: resolved relationships (id, title, link, thumbnail) grouped by label.
	 *
	 * @param array $object Prepared REST post array (contains 'id').
	 * @return array
	 */
	public function get_related_field( $object ) {
		$post_id   = $object['id'];
		$post_type = get_post_type( $post_id );
		$groups    = Tibbhouse_Relationships::instance()->get_related_groups( $post_id, $post_type );

		$resolved = array();
		foreach ( $groups as $label => $posts ) {
			$resolved[ $label ] = array_map(
				function ( $post ) {
					return array(
						'id'        => $post->ID,
						'title'     => get_the_title( $post ),
						'link'      => get_permalink( $post ),
						'post_type' => $post->post_type,
						'thumbnail' => get_the_post_thumbnail_url( $post, 'medium' ),
					);
				},
				$posts
			);
		}

		return $resolved;
	}

	/**
	 * REST callback: all taxonomy terms for this post, keyed by taxonomy slug.
	 *
	 * @param array $object Prepared REST post array (contains 'id').
	 * @return array
	 */
	public function get_taxonomies_field( $object ) {
		$post_id    = $object['id'];
		$taxonomies = get_object_taxonomies( get_post_type( $post_id ) );
		$result     = array();

		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $post_id, $taxonomy );
			if ( is_wp_error( $terms ) ) {
				continue;
			}
			$result[ $taxonomy ] = array_map(
				function ( $term ) {
					return array(
						'id'   => $term->term_id,
						'name' => $term->name,
						'slug' => $term->slug,
					);
				},
				$terms
			);
		}

		return $result;
	}
}
