<?php
/**
 * Taxonomy registration.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all shared taxonomies and attaches them to the correct CPTs.
 */
class Tibbhouse_Taxonomies {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Taxonomies|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Taxonomies
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into init (after CPTs, priority 20 to be safe).
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_taxonomies' ), 20 );
	}

	/**
	 * Register every taxonomy defined by the spec.
	 */
	public function register_taxonomies() {
		$this->register(
			'constitutional_type',
			__( 'Constitutional Types', 'tibbhouse-core' ),
			__( 'Constitutional Type', 'tibbhouse-core' ),
			array( 'treatments', 'conditions', 'knowledge', 'practitioners', 'locations' ),
			'constitutional-type'
		);

		$this->register(
			'vital_area',
			__( 'Vital Areas', 'tibbhouse-core' ),
			__( 'Vital Area', 'tibbhouse-core' ),
			array( 'treatments', 'conditions', 'knowledge' ),
			'vital-area'
		);

		$this->register(
			'knowledge_type',
			__( 'Knowledge Types', 'tibbhouse-core' ),
			__( 'Knowledge Type', 'tibbhouse-core' ),
			array( 'knowledge' ),
			'knowledge-type'
		);

		$this->register(
			'evidence_level',
			__( 'Evidence Levels', 'tibbhouse-core' ),
			__( 'Evidence Level', 'tibbhouse-core' ),
			array( 'treatments', 'knowledge' ),
			'evidence-level'
		);

		$this->register(
			'patient_profile',
			__( 'Patient Profiles', 'tibbhouse-core' ),
			__( 'Patient Profile', 'tibbhouse-core' ),
			array( 'conditions', 'knowledge' ),
			'patient-profile'
		);

		$this->register(
			'remedies',
			__( 'Remedies', 'tibbhouse-core' ),
			__( 'Remedy', 'tibbhouse-core' ),
			array( 'treatments', 'conditions', 'knowledge' ),
			'remedies'
		);
	}

	/**
	 * Register a single hierarchical taxonomy across the given post types.
	 *
	 * @param string   $key        Taxonomy key.
	 * @param string   $plural     Plural label.
	 * @param string   $singular   Singular label.
	 * @param string[] $post_types Post types to attach to.
	 * @param string   $slug       Rewrite slug.
	 */
	private function register( $key, $plural, $singular, array $post_types, $slug ) {
		register_taxonomy(
			$key,
			$post_types,
			array(
				'labels'            => array(
					'name'          => $plural,
					'singular_name' => $singular,
					/* translators: %s: singular label */
					'search_items'  => sprintf( __( 'Search %s', 'tibbhouse-core' ), $plural ),
					/* translators: %s: singular label */
					'add_new_item'  => sprintf( __( 'Add New %s', 'tibbhouse-core' ), $singular ),
					/* translators: %s: singular label */
					'edit_item'     => sprintf( __( 'Edit %s', 'tibbhouse-core' ), $singular ),
					'menu_name'     => $plural,
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'show_ui'           => true,
				'rewrite'           => array( 'slug' => $slug ),
			)
		);
	}
}
