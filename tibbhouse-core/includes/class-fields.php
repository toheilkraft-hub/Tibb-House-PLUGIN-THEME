<?php
/**
 * Native PHP meta boxes and structured meta fields.
 *
 * No ACF, no Carbon Fields, no Meta Box plugin - pure `add_meta_box()`,
 * `register_meta()` and manual sanitization.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers meta boxes + `register_meta()` calls for every CPT.
 */
class Tibbhouse_Fields {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Fields|null
	 */
	private static $instance = null;

	/**
	 * Nonce action/name used across all meta boxes.
	 */
	const NONCE_ACTION = 'tibbhouse_save_meta';
	const NONCE_NAME   = 'tibbhouse_meta_nonce';

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Fields
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook everything up.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_meta_fields' ), 30 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * The full field map: post_type => [ meta_key => [type, label, kind] ].
	 *
	 * kind is one of: text, url, number, textarea, image, relationship, repeater, toggle.
	 *
	 * @return array
	 */
	public function field_map() {
		return array(
			'treatments'    => array(
				'th_price'              => array( 'string', __( 'Price', 'tibbhouse-core' ), 'text' ),
				'th_duration'           => array( 'string', __( 'Duration', 'tibbhouse-core' ), 'text' ),
				'th_booking_url'        => array( 'string', __( 'Booking URL', 'tibbhouse-core' ), 'url' ),
				'th_related_conditions' => array( 'array', __( 'Related Conditions', 'tibbhouse-core' ), 'relationship:conditions' ),
				'th_evidence_level'     => array( 'string', __( 'Evidence Level (free text)', 'tibbhouse-core' ), 'text' ),
				'th_faq'                => array( 'array', __( 'FAQ', 'tibbhouse-core' ), 'repeater' ),
				'th_cta_text'           => array( 'string', __( 'CTA Text', 'tibbhouse-core' ), 'text' ),
				'th_cta_link'           => array( 'string', __( 'CTA Link', 'tibbhouse-core' ), 'url' ),
				'th_hero_image'         => array( 'integer', __( 'Hero Image', 'tibbhouse-core' ), 'image' ),
				'th_outcome_measurement'=> array( 'string', __( 'Outcome Measurement', 'tibbhouse-core' ), 'textarea' ),
				'th_gallery'            => array( 'array',  __( 'Photo Gallery', 'tibbhouse-core' ), 'gallery' ),
				'th_video_url'          => array( 'string', __( 'Video URL (YouTube / Vimeo / MP4)', 'tibbhouse-core' ), 'url' ),
			),
			'conditions'    => array(
				'th_symptoms'                => array( 'string', __( 'Symptoms', 'tibbhouse-core' ), 'textarea' ),
				'th_causes'                  => array( 'string', __( 'Causes', 'tibbhouse-core' ), 'textarea' ),
				'th_treatment_relationships' => array( 'array', __( 'Related Treatments', 'tibbhouse-core' ), 'relationship:treatments' ),
				'th_knowledge_relationships' => array( 'array', __( 'Related Knowledge', 'tibbhouse-core' ), 'relationship:knowledge' ),
				'th_patient_profile'         => array( 'string', __( 'Patient Profile (free text)', 'tibbhouse-core' ), 'text' ),
				'th_faq'                     => array( 'array', __( 'FAQ', 'tibbhouse-core' ), 'repeater' ),
				'th_hero_image'              => array( 'integer', __( 'Hero Image', 'tibbhouse-core' ), 'image' ),
			),
			'knowledge'     => array(
				'th_author'                    => array( 'string', __( 'Author', 'tibbhouse-core' ), 'text' ),
				'th_practitioner_relationship' => array( 'array', __( 'Practitioner', 'tibbhouse-core' ), 'relationship:practitioners' ),
				'th_knowledge_type'            => array( 'string', __( 'Knowledge Type (free text)', 'tibbhouse-core' ), 'text' ),
				'th_evidence_level'            => array( 'string', __( 'Evidence Level (free text)', 'tibbhouse-core' ), 'text' ),
				'th_references'                => array( 'string', __( 'References', 'tibbhouse-core' ), 'textarea' ),
				'th_disclaimer'                => array( 'string', __( 'Disclaimer', 'tibbhouse-core' ), 'textarea' ),
				'th_patient_experience_toggle' => array( 'boolean', __( 'Contains Patient Experience', 'tibbhouse-core' ), 'toggle' ),
				'th_related_remedies'          => array( 'string', __( 'Related Remedies (free text)', 'tibbhouse-core' ), 'text' ),
				'th_faq'                       => array( 'array', __( 'FAQ', 'tibbhouse-core' ), 'repeater' ),
			),
			'practitioners' => array(
				'th_role'            => array( 'string', __( 'Role', 'tibbhouse-core' ), 'text' ),
				'th_qualifications'  => array( 'string', __( 'Qualifications', 'tibbhouse-core' ), 'textarea' ),
				'th_clinic_location' => array( 'array', __( 'Clinic Location', 'tibbhouse-core' ), 'relationship:locations' ),
				'th_specializations' => array( 'string', __( 'Specializations', 'tibbhouse-core' ), 'textarea' ),
				'th_profile_image'   => array( 'integer', __( 'Profile Image', 'tibbhouse-core' ), 'image' ),
				'th_booking_link'    => array( 'string', __( 'Booking Link', 'tibbhouse-core' ), 'url' ),
				'th_social_links'    => array( 'array', __( 'Social Links', 'tibbhouse-core' ), 'repeater' ),
			),
			'locations'     => array(
				'th_address'          => array( 'string', __( 'Address', 'tibbhouse-core' ), 'textarea' ),
				'th_map_embed'        => array( 'string', __( 'Map Embed', 'tibbhouse-core' ), 'textarea' ),
				'th_opening_hours'    => array( 'string', __( 'Opening Hours', 'tibbhouse-core' ), 'textarea' ),
				'th_phone'            => array( 'string', __( 'Phone', 'tibbhouse-core' ), 'text' ),
				'th_email'            => array( 'string', __( 'Email', 'tibbhouse-core' ), 'text' ),
				'th_treatments_offered' => array( 'array', __( 'Treatments Offered', 'tibbhouse-core' ), 'relationship:treatments' ),
				'th_practitioners'    => array( 'array', __( 'Practitioners', 'tibbhouse-core' ), 'relationship:practitioners' ),
			),
		);
	}

	/**
	 * Call `register_meta()` for every field so it is exposed via REST automatically.
	 */
	public function register_meta_fields() {
		foreach ( $this->field_map() as $post_type => $fields ) {
			foreach ( $fields as $meta_key => $config ) {
				list( $type, $label, $kind ) = $config;

				$is_array = 'array' === $type;

				register_meta(
					'post',
					$meta_key,
					array(
						'object_subtype' => $post_type,
						'type'           => $is_array ? 'array' : $type,
						'description'    => $label,
						'single'         => true,
						'show_in_rest'   => $is_array
							? array(
								'schema' => array(
									'type'  => 'array',
									'items' => array( 'type' => 'string' ),
								),
							)
							: true,
						'auth_callback'  => function() {
							return current_user_can( 'edit_posts' );
						},
						'sanitize_callback' => array( $this, 'sanitize_by_kind_wrapper' ),
					)
				);
			}
		}
	}

	/**
	 * Generic sanitize wrapper used by register_meta (kept permissive; the
	 * authoritative sanitization happens field-by-field in save_meta()).
	 *
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	public function sanitize_by_kind_wrapper( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}
		return sanitize_text_field( $value );
	}

	/**
	 * Register the "Tibb House Details" meta box on every managed CPT.
	 */
	public function add_meta_boxes() {
		foreach ( array_keys( $this->field_map() ) as $post_type ) {
			add_meta_box(
				'tibbhouse_fields',
				__( 'Tibb House Details', 'tibbhouse-core' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render every field for the current post type.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$fields = $this->field_map();
		if ( empty( $fields[ $post->post_type ] ) ) {
			return;
		}

		echo '<div class="tibbhouse-fields">';
		foreach ( $fields[ $post->post_type ] as $meta_key => $config ) {
			list( $type, $label, $kind ) = $config;
			$this->render_field( $meta_key, $label, $kind, $post->ID );
		}
		echo '</div>';
	}

	/**
	 * Render one field control based on its "kind".
	 *
	 * @param string $meta_key Meta key.
	 * @param string $label    Field label.
	 * @param string $kind     Field kind (text|url|textarea|image|relationship:X|repeater|toggle).
	 * @param int    $post_id  Current post ID.
	 */
	private function render_field( $meta_key, $label, $kind, $post_id ) {
		$value = get_post_meta( $post_id, $meta_key, true );

		if ( 0 === strpos( $kind, 'relationship:' ) ) {
			$related_post_type = substr( $kind, strlen( 'relationship:' ) );
			Tibbhouse_Helpers::render_relationship_select( $meta_key, $post_id, $related_post_type, $label );
			return;
		}

		switch ( $kind ) {
			case 'textarea':
				printf(
					'<p><label for="%1$s"><strong>%2$s</strong></label><br /><textarea id="%1$s" name="%1$s" rows="4" style="width:100%%;">%3$s</textarea></p>',
					esc_attr( $meta_key ),
					esc_html( $label ),
					esc_textarea( $value )
				);
				break;

			case 'image':
				$image_html = $value ? wp_get_attachment_image( (int) $value, 'medium' ) : '';
				printf(
					'<p><label><strong>%1$s</strong></label><br />
					<div class="tibbhouse-image-preview" id="%2$s_preview">%3$s</div>
					<input type="hidden" id="%2$s" name="%2$s" value="%4$s" />
					<button type="button" class="button tibbhouse-upload-image" data-target="%2$s">%5$s</button>
					<button type="button" class="button tibbhouse-remove-image" data-target="%2$s">%6$s</button></p>',
					esc_html( $label ),
					esc_attr( $meta_key ),
					wp_kses_post( $image_html ),
					esc_attr( $value ),
					esc_html__( 'Select Image', 'tibbhouse-core' ),
					esc_html__( 'Remove', 'tibbhouse-core' )
				);
				break;

			case 'gallery':
				$ids = is_array( $value ) ? array_filter( array_map( 'absint', $value ) ) : array();
				echo '<p><label><strong>' . esc_html( $label ) . '</strong></label></p>';
				echo '<div class="tibbhouse-gallery-wrap" id="' . esc_attr( $meta_key ) . '_wrap" data-key="' . esc_attr( $meta_key ) . '">';
				echo '<div class="tibbhouse-gallery-items">';
				foreach ( $ids as $id ) {
					$img = wp_get_attachment_image( $id, 'thumbnail' );
					if ( ! $img ) { continue; }
					echo '<div class="tibbhouse-gallery-item">';
					echo wp_kses_post( $img );
					echo '<input type="hidden" name="' . esc_attr( $meta_key ) . '[]" value="' . esc_attr( $id ) . '">';
					echo '<button type="button" class="tibbhouse-gallery-remove" title="' . esc_attr__( 'Remove', 'tibbhouse-core' ) . '">&times;</button>';
					echo '</div>';
				}
				echo '</div>';
				echo '<button type="button" class="button tibbhouse-gallery-add" data-key="' . esc_attr( $meta_key ) . '">' . esc_html__( 'Add / Select Images', 'tibbhouse-core' ) . '</button>';
				if ( ! empty( $ids ) ) {
					echo ' <button type="button" class="button tibbhouse-gallery-clear" data-key="' . esc_attr( $meta_key ) . '">' . esc_html__( 'Clear All', 'tibbhouse-core' ) . '</button>';
				}
				echo '</div>';
				break;

			case 'toggle':
				printf(
					'<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s /> <strong>%3$s</strong></label></p>',
					esc_attr( $meta_key ),
					checked( $value, true, false ) ? checked( $value, '1', false ) : checked( $value, '1', false ),
					esc_html( $label )
				);
				break;

			case 'repeater':
				$this->render_repeater( $meta_key, $label, is_array( $value ) ? $value : array() );
				break;

			case 'url':
				printf(
					'<p><label for="%1$s"><strong>%2$s</strong></label><br /><input type="url" id="%1$s" name="%1$s" value="%3$s" style="width:100%%;" /></p>',
					esc_attr( $meta_key ),
					esc_html( $label ),
					esc_attr( $value )
				);
				break;

			case 'text':
			default:
				printf(
					'<p><label for="%1$s"><strong>%2$s</strong></label><br /><input type="text" id="%1$s" name="%1$s" value="%3$s" style="width:100%%;" /></p>',
					esc_attr( $meta_key ),
					esc_html( $label ),
					esc_attr( $value )
				);
				break;
		}
	}

	/**
	 * Render a native FAQ / social-links style repeater (question+answer or label+url rows).
	 *
	 * Uses a JS template (see assets/js/admin-fields.js) to add/remove rows.
	 *
	 * @param string $meta_key Meta key.
	 * @param string $label    Field label.
	 * @param array  $rows     Existing repeater rows.
	 */
	private function render_repeater( $meta_key, $label, array $rows ) {
		echo '<div class="tibbhouse-repeater" data-key="' . esc_attr( $meta_key ) . '">';
		echo '<label><strong>' . esc_html( $label ) . '</strong></label>';
		echo '<div class="tibbhouse-repeater-rows">';

		if ( empty( $rows ) ) {
			$rows = array( array( 'label' => '', 'value' => '' ) );
		}

		foreach ( $rows as $i => $row ) {
			$this->render_repeater_row( $meta_key, $i, $row );
		}

		echo '</div>';
		printf(
			'<button type="button" class="button tibbhouse-repeater-add" data-key="%s">%s</button>',
			esc_attr( $meta_key ),
			esc_html__( '+ Add Row', 'tibbhouse-core' )
		);
		echo '</div>';
	}

	/**
	 * Render a single repeater row (label/value pair, e.g. FAQ question/answer).
	 *
	 * @param string $meta_key Meta key.
	 * @param int    $index    Row index.
	 * @param array  $row      Row data.
	 */
	private function render_repeater_row( $meta_key, $index, $row ) {
		$label_val = isset( $row['label'] ) ? $row['label'] : '';
		$value_val = isset( $row['value'] ) ? $row['value'] : '';
		printf(
			'<div class="tibbhouse-repeater-row" style="display:flex;gap:8px;margin-bottom:6px;">
				<input type="text" placeholder="%1$s" name="%2$s[%3$d][label]" value="%4$s" style="flex:1;" />
				<input type="text" placeholder="%5$s" name="%2$s[%3$d][value]" value="%6$s" style="flex:2;" />
				<button type="button" class="button tibbhouse-repeater-remove">&times;</button>
			</div>',
			esc_attr__( 'Question / Label', 'tibbhouse-core' ),
			esc_attr( $meta_key ),
			(int) $index,
			esc_attr( $label_val ),
			esc_attr__( 'Answer / Value', 'tibbhouse-core' ),
			esc_attr( $value_val )
		);
	}

	/**
	 * Save all meta fields for a post, with nonce and capability checks.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta( $post_id, $post ) {
		$fields = $this->field_map();

		if ( empty( $fields[ $post->post_type ] ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( wp_unslash( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $fields[ $post->post_type ] as $meta_key => $config ) {
			list( $type, $label, $kind ) = $config;
			$this->save_field( $post_id, $meta_key, $kind );
		}
	}

	/**
	 * Sanitize and persist a single field value based on its kind.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $meta_key Meta key.
	 * @param string $kind     Field kind.
	 */
	private function save_field( $post_id, $meta_key, $kind ) {
		if ( 'toggle' === $kind ) {
			update_post_meta( $post_id, $meta_key, isset( $_POST[ $meta_key ] ) ? '1' : '' );
			return;
		}

		if ( ! isset( $_POST[ $meta_key ] ) ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		$raw = wp_unslash( $_POST[ $meta_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( 0 === strpos( $kind, 'relationship:' ) ) {
			$clean = is_array( $raw ) ? array_map( 'absint', $raw ) : array();
			update_post_meta( $post_id, $meta_key, $clean );
			return;
		}

		switch ( $kind ) {
			case 'repeater':
				$clean = Tibbhouse_Helpers::sanitize_repeater(
					$raw,
					array(
						'label' => 'sanitize_text_field',
						'value' => 'sanitize_textarea_field',
					)
				);
				update_post_meta( $post_id, $meta_key, $clean );
				break;

			case 'textarea':
				update_post_meta( $post_id, $meta_key, sanitize_textarea_field( $raw ) );
				break;

			case 'url':
				update_post_meta( $post_id, $meta_key, esc_url_raw( $raw ) );
				break;

			case 'image':
				update_post_meta( $post_id, $meta_key, absint( $raw ) );
				break;

			case 'gallery':
				$clean = is_array( $raw ) ? array_values( array_filter( array_map( 'absint', $raw ) ) ) : array();
				update_post_meta( $post_id, $meta_key, $clean );
				break;

			case 'text':
			default:
				update_post_meta( $post_id, $meta_key, sanitize_text_field( $raw ) );
				break;
		}
	}

	/**
	 * Enqueue admin JS/CSS for the meta box (media uploader + repeater UI).
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'tibbhouse-admin', TIBBHOUSE_CORE_URL . 'assets/css/admin.css', array(), TIBBHOUSE_CORE_VERSION );
		wp_enqueue_script( 'tibbhouse-admin-fields', TIBBHOUSE_CORE_URL . 'assets/js/admin-fields.js', array( 'jquery' ), TIBBHOUSE_CORE_VERSION, true );
	}
}
