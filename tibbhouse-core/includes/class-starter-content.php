<?php
/**
 * Starter content seeder.
 *
 * On activation, creates professionally structured starter entries inside
 * every content type the plugin manages (Treatments, Conditions,
 * Knowledge, Practitioners, Locations) plus starter terms for every
 * taxonomy (Constitutional Types, Vital Areas, Knowledge Types, Evidence
 * Levels, Patient Profiles, Remedies) — so installing the plugin gives an
 * editable starting point instead of an empty dashboard.
 *
 * Runs once; guarded by an option flag so re-activating the plugin never
 * duplicates content.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seeds starter posts and taxonomy terms.
 */
class Tibbhouse_Starter_Content {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Starter_Content|null
	 */
	private static $instance = null;

	/**
	 * Option flag marking that seeding has already run.
	 */
	const SEEDED_OPTION = 'tibbhouse_starter_content_seeded';

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Starter_Content
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Run the full seed routine (terms first, then posts that reference them).
	 * Safe to call multiple times: no-ops if already seeded.
	 */
	public function maybe_seed() {
		if ( get_option( self::SEEDED_OPTION ) ) {
			return;
		}

		// Mark as seeded first so that even if something below throws an
		// exception or hits a server limit, re-loading the admin page does
		// not retry (and potentially loop) the heavy image-import work.
		update_option( self::SEEDED_OPTION, time() );

		try {
			$term_ids = $this->seed_taxonomies();

			$practitioner_ids = $this->seed_practitioners();
			$location_ids     = $this->seed_locations();
			$treatment_ids    = $this->seed_treatments( $term_ids );
			$condition_ids    = $this->seed_conditions( $term_ids, $treatment_ids );
			$this->seed_knowledge( $term_ids, $practitioner_ids );

			// Cross-link a couple of relationships now that both sides exist.
			$this->link_practitioners_locations( $practitioner_ids, $location_ids );
		} catch ( \Throwable $e ) {
			// Log the error but never let seeder failures break the plugin.
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'Tibb House Core: starter content seeder error — ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}

	/**
	 * Build a Gutenberg block markup string from simple section definitions.
	 *
	 * @param array $sections List of ['heading' => string|null, 'paragraphs' => string[], 'list' => string[]].
	 * @return string
	 */
	private function build_content( array $sections ) {
		$blocks = array();

		foreach ( $sections as $section ) {
			if ( ! empty( $section['heading'] ) ) {
				$blocks[] = '<!-- wp:heading --><h2>' . esc_html( $section['heading'] ) . '</h2><!-- /wp:heading -->';
			}
			foreach ( (array) ( $section['paragraphs'] ?? array() ) as $paragraph ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $paragraph ) . '</p><!-- /wp:paragraph -->';
			}
			if ( ! empty( $section['list'] ) ) {
				$items    = array_map( function ( $item ) {
					return '<li>' . esc_html( $item ) . '</li>';
				}, $section['list'] );
				$blocks[] = '<!-- wp:list --><ul>' . implode( '', $items ) . '</ul><!-- /wp:list -->';
			}
		}

		return implode( "\n\n", $blocks );
	}

	/**
	 * Insert taxonomy terms and return them keyed by taxonomy => [term_name => term_id].
	 *
	 * @return array
	 */
	private function seed_taxonomies() {
		$definitions = array(
			'constitutional_type' => array( 'Hot Temperament', 'Cold Temperament', 'Moist Temperament', 'Dry Temperament', 'Balanced Temperament' ),
			'vital_area'          => array( 'Digestive System', 'Respiratory System', 'Circulatory System', 'Nervous System', 'Musculoskeletal System' ),
			'knowledge_type'      => array( 'Guide', 'Research Summary', 'Case Study', 'FAQ' ),
			'evidence_level'      => array( 'Traditional Use', 'Observational Evidence', 'Clinical Study', 'Systematic Review' ),
			'patient_profile'     => array( 'Adults', 'Children', 'Elderly', 'Pregnant & Postnatal' ),
			'remedies'            => array( 'Cupping (Hijama)', 'Honey', 'Black Seed (Nigella Sativa)', 'Herbal Steam', 'Olive Oil', 'Dietary Therapy' ),
		);

		$term_ids = array();
		foreach ( $definitions as $taxonomy => $names ) {
			$term_ids[ $taxonomy ] = array();
			foreach ( $names as $name ) {
				$term = term_exists( $name, $taxonomy );
				if ( ! $term ) {
					$term = wp_insert_term( $name, $taxonomy );
				}
				if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
					$term_ids[ $taxonomy ][ $name ] = (int) $term['term_id'];
				}
			}
		}

		return $term_ids;
	}

	/**
	 * Insert a single starter post if a post with that title doesn't already exist.
	 *
	 * @param string $post_type Post type slug.
	 * @param string $title     Post title.
	 * @param string $excerpt   Short excerpt.
	 * @param string $content   Full block content.
	 * @return int|null Post ID, or null on failure.
	 */
	private function insert_post( $post_type, $title, $excerpt, $content ) {
		// get_page_by_title() is deprecated since WP 6.2 — use WP_Query instead.
		$existing_query = new WP_Query(
			array(
				'post_type'              => $post_type,
				'title'                  => $title,
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( $existing_query->have_posts() ) {
			return (int) $existing_query->posts[0]->ID;
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_type'    => $post_type,
				'post_status'  => 'publish',
				'post_content' => $content,
				'post_excerpt' => $excerpt,
				'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
			),
			true
		);

		return is_wp_error( $post_id ) ? null : (int) $post_id;
	}

	/**
	 * Attach one of the bundled starter photos (assets/img/starter/*.jpg) to a
	 * post as its Featured Image, so every seeded content type ships with a
	 * real, on-brand photo instead of an empty thumbnail.
	 *
	 * Safe to call repeatedly: does nothing if the post already has a
	 * featured image (e.g. an admin already replaced it).
	 *
	 * @param int    $post_id  Post to attach the image to.
	 * @param string $filename Filename inside assets/img/starter/.
	 */
	private function attach_starter_image( $post_id, $filename ) {
		if ( ! $post_id || has_post_thumbnail( $post_id ) ) {
			return;
		}

		$source_path = TIBBHOUSE_CORE_PATH . 'assets/img/starter/' . $filename;
		if ( ! file_exists( $source_path ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$filetype = wp_check_filetype( $filename, null );
		$upload   = wp_upload_bits( $filename, null, file_get_contents( $source_path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( ! empty( $upload['error'] ) ) {
			return;
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$post_id
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return;
		}

		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );
		set_post_thumbnail( $post_id, $attachment_id );
	}

	/**
	 * Seed the Treatments CPT.
	 *
	 * @param array $term_ids Taxonomy term ids from seed_taxonomies().
	 * @return int[] Inserted/existing post IDs.
	 */
	private function seed_treatments( array $term_ids ) {
		$items = array(
			array(
				'title'   => 'Hijama Cupping Therapy',
				'excerpt' => 'A traditional Prophetic medicine practice that draws stagnant blood to the surface to relieve pain and support detoxification.',
				'sections' => array(
					array(
						'heading'    => 'Overview',
						'paragraphs' => array(
							'Hijama, or cupping therapy, is a longstanding practice in Islamic and natural medicine that uses suction cups placed on the skin to draw blood to the surface, relieving muscle tension and promoting circulation.',
						),
					),
					array(
						'heading' => 'What to Expect',
						'list'    => array(
							'A brief consultation to identify treatment points',
							'Dry or wet cupping applied for 5-15 minutes per point',
							'Mild bruising that fades within a week',
							'A rest period with water and light food afterward',
						),
					),
					array(
						'heading'    => 'Benefits',
						'paragraphs' => array( 'Patients commonly report reduced muscle and joint pain, improved circulation, and an overall sense of lightness after a session.' ),
					),
				),
				'meta'    => array(
					'th_price'    => 'From $60',
					'th_duration' => '45 minutes',
					'th_cta_text' => 'Book Hijama Session',
				),
				'terms'   => array( 'remedies' => array( 'Cupping (Hijama)' ), 'vital_area' => array( 'Circulatory System' ) ),
				'image'   => 'treatment-cupping.jpg',
			),
			array(
				'title'   => 'Black Seed Oil Therapy',
				'excerpt' => 'A remedy protocol built around Nigella sativa, described in Prophetic medicine as a remedy for every disease except death.',
				'sections' => array(
					array(
						'heading'    => 'Overview',
						'paragraphs' => array( 'Black seed (Nigella sativa) is one of the most widely used remedies in Islamic natural medicine, taken internally or applied topically to support the immune and respiratory systems.' ),
					),
					array(
						'heading' => 'Typical Protocol',
						'list'    => array(
							'One teaspoon of black seed oil with honey, twice daily',
							'Steam inhalation with a few drops for respiratory support',
							'Topical application blended with olive oil for skin conditions',
						),
					),
				),
				'meta'    => array( 'th_price' => 'From $35', 'th_duration' => '4-week protocol' ),
				'terms'   => array( 'remedies' => array( 'Black Seed (Nigella Sativa)' ), 'vital_area' => array( 'Respiratory System' ) ),
				'image'   => 'treatment-herbal.jpg',
			),
			array(
				'title'   => 'Herbal Steam Respiratory Therapy',
				'excerpt' => 'A guided steam inhalation session using traditional herbs to ease congestion and support respiratory health.',
				'sections' => array(
					array(
						'heading'    => 'Overview',
						'paragraphs' => array( 'This therapy combines warm herbal steam with traditional botanicals to loosen congestion, soothe the airways and support natural breathing.' ),
					),
					array(
						'heading' => 'Session Includes',
						'list'    => array( 'Herbal steam blend selected for your symptoms', '15-20 minute guided inhalation', 'Take-home herbal blend for continued use' ),
					),
				),
				'meta'    => array( 'th_price' => 'From $40', 'th_duration' => '30 minutes' ),
				'terms'   => array( 'remedies' => array( 'Herbal Steam' ), 'vital_area' => array( 'Respiratory System' ) ),
				'image'   => 'treatment-massage.jpg',
			),
		);

		return $this->seed_items( 'treatments', $items, $term_ids );
	}

	/**
	 * Seed the Conditions CPT.
	 *
	 * @param array $term_ids       Taxonomy term ids.
	 * @param int[] $treatment_ids  Related treatment post IDs (title => id not tracked; index-based cross link).
	 * @return int[]
	 */
	private function seed_conditions( array $term_ids, array $treatment_ids ) {
		$items = array(
			array(
				'title'   => 'Chronic Lower Back Pain',
				'excerpt' => 'Persistent lower back discomfort often linked to muscular tension, poor circulation, or prolonged sitting.',
				'sections' => array(
					array( 'heading' => 'Symptoms', 'list' => array( 'Dull or sharp pain in the lower back', 'Stiffness after sitting or standing', 'Pain radiating to the hips or legs' ) ),
					array( 'heading' => 'Natural Approach', 'paragraphs' => array( 'Cupping therapy and targeted herbal remedies are commonly used to relieve muscular tension and support recovery alongside gentle movement.' ) ),
				),
				'terms' => array( 'vital_area' => array( 'Musculoskeletal System' ), 'constitutional_type' => array( 'Cold Temperament' ) ),
				'image' => 'condition-joint.jpg',
			),
			array(
				'title'   => 'Seasonal Respiratory Congestion',
				'excerpt' => 'Common cold-weather congestion affecting the sinuses and airways.',
				'sections' => array(
					array( 'heading' => 'Symptoms', 'list' => array( 'Blocked or runny nose', 'Chest tightness', 'Mild cough' ) ),
					array( 'heading' => 'Natural Approach', 'paragraphs' => array( 'Herbal steam and black seed oil are traditionally used to loosen congestion and support easier breathing.' ) ),
				),
				'terms' => array( 'vital_area' => array( 'Respiratory System' ), 'constitutional_type' => array( 'Moist Temperament' ) ),
				'image' => 'condition-stress.jpg',
			),
			array(
				'title'   => 'Digestive Sluggishness',
				'excerpt' => 'A feeling of heaviness and slow digestion often tied to diet and lifestyle.',
				'sections' => array(
					array( 'heading' => 'Symptoms', 'list' => array( 'Bloating after meals', 'Low energy', 'Irregular digestion' ) ),
					array( 'heading' => 'Natural Approach', 'paragraphs' => array( 'Dietary therapy and honey-based remedies are used to support healthy digestion and restore balance.' ) ),
				),
				'terms' => array( 'vital_area' => array( 'Digestive System' ), 'constitutional_type' => array( 'Cold Temperament' ) ),
				'image' => 'condition-digestive.jpg',
			),
		);

		$ids = $this->seed_items( 'conditions', $items, $term_ids );

		// Cross-link the first condition to the first treatment as a related example.
		if ( ! empty( $ids[0] ) && ! empty( $treatment_ids[0] ) ) {
			update_post_meta( $ids[0], 'th_treatment_relationships', array( $treatment_ids[0] ) );
		}

		return $ids;
	}

	/**
	 * Seed the Knowledge CPT.
	 *
	 * @param array $term_ids          Taxonomy term ids.
	 * @param int[] $practitioner_ids  Related practitioner IDs.
	 * @return int[]
	 */
	private function seed_knowledge( array $term_ids, array $practitioner_ids ) {
		$items = array(
			array(
				'title'   => 'An Introduction to Prophetic Medicine',
				'excerpt' => 'A beginner-friendly overview of Tibb an-Nabawi and how it complements modern wellness practices.',
				'sections' => array(
					array( 'heading' => 'What is Prophetic Medicine?', 'paragraphs' => array( 'Tibb an-Nabawi refers to the body of guidance on health and healing found in the Quran and Sunnah, covering diet, remedies, and lifestyle practices.' ) ),
					array( 'heading' => 'Core Principles', 'list' => array( 'Balance of the four temperaments', 'Prevention through diet and lifestyle', 'Use of natural remedies such as honey and black seed' ) ),
				),
				'terms' => array( 'knowledge_type' => array( 'Guide' ) ),
				'image' => 'knowledge-book.jpg',
			),
			array(
				'title'   => 'Understanding the Four Temperaments',
				'excerpt' => 'How constitutional types shape recommended treatments and lifestyle guidance.',
				'sections' => array(
					array( 'heading' => 'The Four Temperaments', 'list' => array( 'Hot', 'Cold', 'Moist', 'Dry' ) ),
					array( 'heading' => 'Why It Matters', 'paragraphs' => array( 'Identifying a patient\'s dominant temperament helps practitioners recommend remedies and dietary adjustments suited to their constitution.' ) ),
				),
				'terms' => array( 'knowledge_type' => array( 'Guide' ) ),
				'image' => 'knowledge-herbs.jpg',
			),
			array(
				'title'   => 'The Evidence Behind Honey as a Remedy',
				'excerpt' => 'A research summary of honey\'s traditional and modern-studied therapeutic uses.',
				'sections' => array(
					array( 'heading' => 'Traditional Use', 'paragraphs' => array( 'Honey has been used for centuries in Islamic medicine for wound care, digestive support, and general wellness.' ) ),
					array( 'heading' => 'Modern Findings', 'paragraphs' => array( 'Contemporary studies have examined honey\'s antimicrobial and anti-inflammatory properties, lending support to several traditional uses.' ) ),
				),
				'terms' => array( 'knowledge_type' => array( 'Research Summary' ), 'evidence_level' => array( 'Observational Evidence' ) ),
				'image' => 'knowledge-nutrition.jpg',
			),
		);

		$ids = $this->seed_items( 'knowledge', $items, $term_ids );

		if ( ! empty( $ids[0] ) && ! empty( $practitioner_ids[0] ) ) {
			update_post_meta( $ids[0], 'th_practitioner_relationship', array( $practitioner_ids[0] ) );
		}

		return $ids;
	}

	/**
	 * Seed the Practitioners CPT.
	 *
	 * @return int[]
	 */
	private function seed_practitioners() {
		$items = array(
			array(
				'title'   => 'Dr. Amina Yusuf',
				'excerpt' => 'Practitioner of Prophetic medicine specializing in cupping therapy and herbal remedies.',
				'sections' => array(
					array( 'heading' => 'About', 'paragraphs' => array( 'Dr. Amina Yusuf has over a decade of experience combining traditional Islamic medicine with modern wellness practices, with a focus on cupping therapy and herbal protocols.' ) ),
				),
				'meta' => array( 'th_role' => 'Lead Practitioner', 'th_qualifications' => 'Certified Hijama Practitioner, Diploma in Traditional Herbal Medicine' ),
				'image' => 'practitioner-1.jpg',
			),
			array(
				'title'   => 'Imam Bilal Ahmed',
				'excerpt' => 'Specialist in Prophetic dietary guidance and constitutional-type consultations.',
				'sections' => array(
					array( 'heading' => 'About', 'paragraphs' => array( 'Imam Bilal Ahmed guides patients through personalized dietary and lifestyle plans rooted in the four-temperament model of Islamic medicine.' ) ),
				),
				'meta' => array( 'th_role' => 'Dietary & Lifestyle Consultant', 'th_qualifications' => 'Certified Islamic Nutrition Counselor' ),
				'image' => 'practitioner-2.jpg',
			),
		);

		return $this->seed_items( 'practitioners', $items, array() );
	}

	/**
	 * Seed the Locations CPT.
	 *
	 * @return int[]
	 */
	private function seed_locations() {
		$items = array(
			array(
				'title'   => 'Tibb House Clinic — Downtown',
				'excerpt' => 'Our flagship clinic offering cupping therapy, consultations, and herbal remedies.',
				'sections' => array(
					array( 'heading' => 'Visit Us', 'paragraphs' => array( 'Our downtown clinic is open six days a week and offers walk-in consultations as well as scheduled treatments.' ) ),
				),
				'meta' => array( 'th_address' => '123 Wellness Street, Downtown', 'th_opening_hours' => 'Mon-Sat: 9am - 6pm', 'th_phone' => '+1 (555) 010-0100' ),
				'image' => 'location-1.jpg',
			),
		);

		return $this->seed_items( 'locations', $items, array() );
	}

	/**
	 * Shared insertion loop: builds content, inserts the post, attaches terms and meta.
	 *
	 * @param string $post_type Post type slug.
	 * @param array  $items     Item definitions (title, excerpt, sections, terms, meta).
	 * @param array  $term_ids  Taxonomy term ids from seed_taxonomies().
	 * @return int[] Inserted/existing post IDs, indexed the same as $items.
	 */
	private function seed_items( $post_type, array $items, array $term_ids ) {
		$ids = array();

		foreach ( $items as $index => $item ) {
			$content = $this->build_content( $item['sections'] );
			$post_id = $this->insert_post( $post_type, $item['title'], $item['excerpt'], $content );

			if ( ! $post_id ) {
				continue;
			}

			$ids[ $index ] = $post_id;

			if ( ! empty( $item['terms'] ) ) {
				foreach ( $item['terms'] as $taxonomy => $names ) {
					$ids_for_tax = array();
					foreach ( $names as $name ) {
						if ( isset( $term_ids[ $taxonomy ][ $name ] ) ) {
							$ids_for_tax[] = $term_ids[ $taxonomy ][ $name ];
						}
					}
					if ( $ids_for_tax ) {
						wp_set_object_terms( $post_id, $ids_for_tax, $taxonomy );
					}
				}
			}

			if ( ! empty( $item['meta'] ) ) {
				foreach ( $item['meta'] as $meta_key => $meta_value ) {
					update_post_meta( $post_id, $meta_key, $meta_value );
				}
			}

			if ( ! empty( $item['image'] ) ) {
				$this->attach_starter_image( $post_id, $item['image'] );
			}
		}

		return $ids;
	}

	/**
	 * Option flag for the v2 seeder (adds 3rd practitioner + 2 extra locations).
	 */
	const SEEDED_V2_OPTION = 'tibbhouse_starter_content_seeded_v2';

	/**
	 * V2 seeder: top-ups to reach 3 entries for Practitioners and Locations.
	 * Called from admin_init so it runs automatically after an in-place update.
	 */
	public function maybe_seed_v2() {
		if ( get_option( self::SEEDED_V2_OPTION ) ) {
			return;
		}
		update_option( self::SEEDED_V2_OPTION, time() );

		try {
			// Extra practitioner.
			$extra_practitioners = array(
				array(
					'title'   => 'Sister Fatima Al-Rashid',
					'excerpt' => 'Women\'s health specialist combining Prophetic dietary guidance with herbal protocols for hormonal balance and postnatal care.',
					'sections' => array(
						array( 'heading' => 'About', 'paragraphs' => array( 'Sister Fatima Al-Rashid brings a gentle, holistic approach to women\'s health, specialising in postnatal recovery, hormonal balance, and dietary therapy rooted in Islamic natural medicine.' ) ),
						array( 'heading' => 'Specialisms', 'list' => array( 'Postnatal recovery protocols', 'Hormonal balance through diet', 'Herbal consultations for women\'s health' ) ),
					),
					'meta'  => array( 'th_role' => 'Women\'s Health Practitioner', 'th_qualifications' => 'Certified Islamic Natural Medicine Practitioner, Postnatal Care Diploma' ),
					'image' => 'practitioner-3.jpg',
				),
			);

			// Extra locations.
			$extra_locations = array(
				array(
					'title'   => 'Tibb House Clinic — East End',
					'excerpt' => 'Our East End satellite clinic offering consultations, cupping therapy, and herbal remedy dispensing.',
					'sections' => array(
						array( 'heading' => 'About This Location', 'paragraphs' => array( 'Conveniently located in the East End, this clinic serves local patients with the same quality treatments and consultations as our flagship location.' ) ),
						array( 'heading' => 'Services Available', 'list' => array( 'Hijama cupping therapy', 'Herbal consultation', 'Dietary and lifestyle guidance' ) ),
					),
					'meta'  => array( 'th_address' => '47 Heritage Lane, East End', 'th_opening_hours' => 'Tue-Sat: 10am - 5pm', 'th_phone' => '+1 (555) 020-0200' ),
					'image' => 'location-1.jpg',
				),
				array(
					'title'   => 'Tibb House Online — Virtual Consultations',
					'excerpt' => 'Book a secure video consultation with any of our practitioners from the comfort of your home.',
					'sections' => array(
						array( 'heading' => 'How It Works', 'paragraphs' => array( 'Our virtual consultation service connects you with qualified practitioners via secure video call. Receive personalised advice on treatments, diet, and lifestyle — wherever you are in the world.' ) ),
						array( 'heading' => 'What Is Covered', 'list' => array( 'Initial assessment and health history review', 'Personalised treatment recommendations', 'Dietary and herbal protocol guidance', 'Written follow-up summary' ) ),
					),
					'meta'  => array( 'th_address' => 'Online — Worldwide', 'th_opening_hours' => 'Mon-Sun: 8am - 9pm', 'th_phone' => '+1 (555) 030-0300' ),
					'image' => 'location-2.jpg',
				),
			);

			// Use plugin's bundled images dir for the new images (theme has them too,
			// but seeder looks in TIBBHOUSE_CORE_PATH/assets/img/starter/).
			// We need to add location-2.jpg to that directory.
			$this->seed_items( 'practitioners', $extra_practitioners, array() );
			$this->seed_items( 'locations',     $extra_locations,     array() );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'Tibb House Core: v2 seeder error — ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}

	/**
	 * Cross-link the seeded practitioners to the seeded location.
	 *
	 * @param int[] $practitioner_ids Practitioner post IDs.
	 * @param int[] $location_ids     Location post IDs.
	 */
	private function link_practitioners_locations( array $practitioner_ids, array $location_ids ) {
		if ( empty( $location_ids[0] ) ) {
			return;
		}

		foreach ( $practitioner_ids as $practitioner_id ) {
			update_post_meta( $practitioner_id, 'th_clinic_location', array( $location_ids[0] ) );
		}

		update_post_meta( $location_ids[0], 'th_practitioners', array_values( $practitioner_ids ) );
	}
}
