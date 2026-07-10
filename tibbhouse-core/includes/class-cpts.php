<?php
/**
 * Custom Post Type registration.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers Treatments, Conditions, Knowledge, Practitioners and Locations.
 */
class Tibbhouse_CPTs {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_CPTs|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_CPTs
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into init.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Register all five custom post types.
	 */
	public function register_post_types() {
		$this->register_treatments();
		$this->register_conditions();
		$this->register_knowledge();
		$this->register_practitioners();
		$this->register_locations();
	}

	/**
	 * Shared supports array for every CPT.
	 *
	 * @return string[]
	 */
	private function shared_supports() {
		return array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' );
	}

	/**
	 * Register the Treatments CPT.
	 */
	private function register_treatments() {
		register_post_type(
			'treatments',
			array(
				'labels'             => $this->build_labels( __( 'Treatments', 'tibbhouse-core' ), __( 'Treatment', 'tibbhouse-core' ) ),
				'public'             => true,
				'has_archive'        => true,
				'show_in_rest'       => true,
				'rest_base'          => 'treatments',
				'menu_icon'          => 'dashicons-heart',
				'supports'           => $this->shared_supports(),
				'rewrite'            => array( 'slug' => 'treatments' ),
				'show_in_menu'       => true,
				'menu_position'      => 20,
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
			)
		);
	}

	/**
	 * Register the Conditions CPT.
	 */
	private function register_conditions() {
		register_post_type(
			'conditions',
			array(
				'labels'          => $this->build_labels( __( 'Conditions', 'tibbhouse-core' ), __( 'Condition', 'tibbhouse-core' ) ),
				'public'          => true,
				'has_archive'     => true,
				'show_in_rest'    => true,
				'rest_base'       => 'conditions',
				'menu_icon'       => 'dashicons-clipboard',
				'supports'        => $this->shared_supports(),
				'rewrite'         => array( 'slug' => 'conditions' ),
				'show_in_menu'    => true,
				'menu_position'   => 21,
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Register the Knowledge CPT.
	 */
	private function register_knowledge() {
		register_post_type(
			'knowledge',
			array(
				'labels'          => $this->build_labels( __( 'Knowledge', 'tibbhouse-core' ), __( 'Knowledge Article', 'tibbhouse-core' ) ),
				'public'          => true,
				'has_archive'     => true,
				'show_in_rest'    => true,
				'rest_base'       => 'knowledge',
				'menu_icon'       => 'dashicons-welcome-learn-more',
				'supports'        => $this->shared_supports(),
				'rewrite'         => array( 'slug' => 'knowledge' ),
				'show_in_menu'    => true,
				'menu_position'   => 22,
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Register the Practitioners CPT.
	 */
	private function register_practitioners() {
		register_post_type(
			'practitioners',
			array(
				'labels'          => $this->build_labels( __( 'Practitioners', 'tibbhouse-core' ), __( 'Practitioner', 'tibbhouse-core' ) ),
				'public'          => true,
				'has_archive'     => true,
				'show_in_rest'    => true,
				'rest_base'       => 'practitioners',
				'menu_icon'       => 'dashicons-businessperson',
				'supports'        => $this->shared_supports(),
				'rewrite'         => array( 'slug' => 'practitioners' ),
				'show_in_menu'    => true,
				'menu_position'   => 23,
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Register the Locations CPT.
	 */
	private function register_locations() {
		register_post_type(
			'locations',
			array(
				'labels'          => $this->build_labels( __( 'Locations', 'tibbhouse-core' ), __( 'Location', 'tibbhouse-core' ) ),
				'public'          => true,
				'has_archive'     => true,
				'show_in_rest'    => true,
				'rest_base'       => 'locations',
				'menu_icon'       => 'dashicons-location',
				'supports'        => $this->shared_supports(),
				'rewrite'         => array( 'slug' => 'locations' ),
				'show_in_menu'    => true,
				'menu_position'   => 24,
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
	}

	/**
	 * Build a standard WordPress CPT labels array.
	 *
	 * @param string $plural   Plural label.
	 * @param string $singular Singular label.
	 * @return array
	 */
	private function build_labels( $plural, $singular ) {
		/* translators: %s: singular label */
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			/* translators: %s: singular label */
			'add_new_item'       => sprintf( __( 'Add New %s', 'tibbhouse-core' ), $singular ),
			/* translators: %s: singular label */
			'edit_item'          => sprintf( __( 'Edit %s', 'tibbhouse-core' ), $singular ),
			/* translators: %s: singular label */
			'new_item'           => sprintf( __( 'New %s', 'tibbhouse-core' ), $singular ),
			/* translators: %s: singular label */
			'view_item'          => sprintf( __( 'View %s', 'tibbhouse-core' ), $singular ),
			/* translators: %s: plural label */
			'search_items'       => sprintf( __( 'Search %s', 'tibbhouse-core' ), $plural ),
			/* translators: %s: plural label */
			'not_found'          => sprintf( __( 'No %s found', 'tibbhouse-core' ), strtolower( $plural ) ),
			/* translators: %s: plural label */
			'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'tibbhouse-core' ), strtolower( $plural ) ),
			'all_items'          => $plural,
			'archives'           => sprintf( __( '%s Archives', 'tibbhouse-core' ), $singular ),
			'menu_name'          => $plural,
		);
	}
}
