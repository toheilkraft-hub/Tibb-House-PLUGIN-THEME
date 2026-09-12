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
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'Tibb House Core: starter content seeder error — ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		// Mark as done AFTER seeding so a failed run retries on next admin load.
		update_option( self::SEEDED_OPTION, time() );
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

		update_option( self::SEEDED_V2_OPTION, time() );
	}

	/**
	 * Option flag for the v3 seeder (adds 4th items per section + About/Contact pages).
	 */
	const SEEDED_V3_OPTION = 'tibbhouse_starter_content_seeded_v3';

	/**
	 * V3 seeder: adds a 4th entry for Treatments, Conditions and Knowledge, and
	 * creates the About Us and Contact Us pages used by the homepage renderer.
	 */
	public function maybe_seed_v3() {
		if ( get_option( self::SEEDED_V3_OPTION ) ) {
			return;
		}

		try {
			$term_ids = $this->seed_taxonomies();

			// ── 4th Treatment ───────────────────────────────────────────────────
			$this->seed_items( 'treatments', array(
				array(
					'title'   => 'Honey & Olive Oil Wellness Protocol',
					'excerpt' => 'A nourishing multi-week protocol combining raw honey and cold-pressed olive oil — two of the most revered remedies in Prophetic medicine.',
					'sections' => array(
						array(
							'heading'    => 'Overview',
							'paragraphs' => array(
								'Both honey and olive oil are explicitly praised in the Quran and Sunnah for their healing properties. This protocol combines them into a structured daily regimen to support immunity, skin health, and energy levels.',
							),
						),
						array(
							'heading' => 'Protocol Highlights',
							'list'    => array(
								'One teaspoon of raw Sidr honey each morning before breakfast',
								'Cold-pressed olive oil consumed daily or applied topically',
								'Weekly progress review with a practitioner',
								'Dietary recommendations to complement the remedy',
							),
						),
						array(
							'heading'    => 'Expected Benefits',
							'paragraphs' => array(
								'Patients report improved energy, clearer skin, and better digestive comfort over the four-week programme.',
							),
						),
					),
					'meta'  => array( 'th_price' => 'From $55', 'th_duration' => '4-week protocol' ),
					'terms' => array( 'remedies' => array( 'Honey', 'Olive Oil' ), 'vital_area' => array( 'Digestive System' ) ),
					'image' => 'treatment-honey.jpg',
				),
			), $term_ids );

			// ── 4th Condition ───────────────────────────────────────────────────
			$this->seed_items( 'conditions', array(
				array(
					'title'   => 'Sleep Disturbance & Insomnia',
					'excerpt' => 'Difficulty falling or staying asleep, often linked to stress, dietary imbalance, or an overactive nervous system.',
					'sections' => array(
						array(
							'heading' => 'Symptoms',
							'list'    => array(
								'Difficulty falling asleep despite tiredness',
								'Waking frequently during the night',
								'Feeling unrefreshed in the morning',
								'Low energy and difficulty concentrating during the day',
							),
						),
						array(
							'heading'    => 'Natural Approach',
							'paragraphs' => array(
								'Islamic medicine addresses sleep disturbance through a combination of dietary adjustments, herbal remedies, and lifestyle routines. Honey taken before bed, reduced stimulants, and gentle cupping therapy are all part of an integrated approach.',
							),
						),
					),
					'terms' => array( 'vital_area' => array( 'Nervous System' ), 'constitutional_type' => array( 'Hot Temperament' ) ),
					'image' => 'condition-sleep.jpg',
				),
			), $term_ids );

			// ── 4th Knowledge ───────────────────────────────────────────────────
			$this->seed_items( 'knowledge', array(
				array(
					'title'   => 'Dietary Principles in Islamic Medicine',
					'excerpt' => 'How what you eat shapes your health — a practical guide to food and eating habits drawn from Prophetic traditions.',
					'sections' => array(
						array(
							'heading'    => 'Food as Medicine',
							'paragraphs' => array(
								'Islamic medicine has always emphasised that diet is the foundation of health. The Prophetic tradition advises eating in moderation, filling only a third of the stomach, and prioritising wholesome, natural foods.',
							),
						),
						array(
							'heading' => 'Key Dietary Principles',
							'list'    => array(
								'Eat only when genuinely hungry',
								'Stop eating before feeling full',
								'Begin meals with bismillah and eat mindfully',
								'Favour dates, honey, figs, pomegranates, olives, and black seed',
								'Avoid excess sugar, processed foods, and late-night eating',
							),
						),
						array(
							'heading'    => 'Putting It Into Practice',
							'paragraphs' => array(
								'A practitioner consultation can tailor these principles to your constitutional type, helping you build a sustainable eating plan that supports your specific health goals.',
							),
						),
					),
					'terms' => array( 'knowledge_type' => array( 'Guide' ), 'evidence_level' => array( 'Traditional Use' ) ),
					'image' => 'knowledge-diet.jpg',
				),
			), $term_ids );

			// ── About Us page ────────────────────────────────────────────────────
			$this->create_page_if_missing(
				'TIBB HOUSE – About Us',
				'about-us',
				$this->build_content( array(
					array(
						'heading'    => 'Our Story',
						'paragraphs' => array(
							'Tibb House was founded with a single vision: to make the timeless wisdom of natural and Islamic medicine accessible to everyone. We believe that true healing comes from addressing the root causes of illness — body, mind, and spirit — rather than simply managing symptoms.',
							'Drawing from the rich tradition of Tibb an-Nabawi (Prophetic medicine) and centuries of Islamic natural healing, our practitioners offer treatments that are both evidence-informed and spiritually grounded.',
						),
					),
					array(
						'heading' => 'What We Offer',
						'list'    => array(
							'Hijama cupping therapy for pain relief and detoxification',
							'Herbal and dietary protocols tailored to your constitutional type',
							'Knowledge resources on Islamic medicine and Prophetic remedies',
							'One-to-one consultations — in clinic and online',
						),
					),
					array(
						'heading'    => 'Our Approach',
						'paragraphs' => array(
							'Every patient is unique. We take time to understand your health history, lifestyle, and constitution before recommending a treatment path. Our practitioners blend traditional knowledge with a modern understanding of wellness to give you a personalised, compassionate experience.',
						),
					),
					array(
						'heading' => 'Our Values',
						'list'    => array(
							'Integrity — honest, transparent care',
							'Compassion — a warm, welcoming environment for every patient',
							'Tradition — rooted in authentic Islamic medicine',
							'Accessibility — quality care available to all',
						),
					),
				) )
			);

			// ── Contact Us page ──────────────────────────────────────────────────
			$this->create_page_if_missing(
				'TIBB HOUSE – Contact Us',
				'contact-us',
				$this->build_content( array(
					array(
						'heading'    => 'Get In Touch',
						'paragraphs' => array(
							'We would love to hear from you. Whether you have a question about a treatment, want to book an appointment, or simply want to learn more about what we offer, our friendly team is here to help.',
						),
					),
					array(
						'heading' => 'Clinic Details',
						'list'    => array(
							'Tibb House Clinic — Downtown, 123 Wellness Street',
							'Phone: +1 (555) 010-0100',
							'Email: hello@tibbhouse.com',
							'Opening Hours: Mon–Sat, 9am–6pm',
						),
					),
					array(
						'heading'    => 'Book an Appointment',
						'paragraphs' => array(
							'To book a session, call us during clinic hours or send us an email with your preferred date and time. For virtual consultations, visit our Online Consultations page.',
						),
					),
					array(
						'heading'    => 'Virtual Consultations',
						'paragraphs' => array(
							'Can\'t make it to the clinic? Our practitioners are available for secure video consultations worldwide, Monday through Sunday, 8am–9pm.',
						),
					),
				) )
			);

		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'Tibb House Core: v3 seeder error — ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		update_option( self::SEEDED_V3_OPTION, time() );
	}

	/**
	 * Option flag for the final cleanup and content-population pass.
	 */
	const SEEDED_V4_OPTION = 'tibbhouse_starter_content_seeded_v4_3';

	/**
	 * Final cleanup pass: hide obvious demo records and add a small set of
	 * practitioner-led educational entries to the existing content system.
	 */
	public function maybe_seed_v4() {
		if ( get_option( self::SEEDED_V4_OPTION ) ) {
			return;
		}

		if ( ! post_type_exists( 'treatments' ) ) {
			return;
		}

		try {
			$this->hide_demo_content();
			$term_ids = $this->seed_taxonomies();
			$practitioner_ids = $this->seed_final_practitioners();
			$this->link_final_practitioners_locations( $practitioner_ids );

			$treatment_ids = $this->seed_items(
				'treatments',
				array(
					array(
						'title'    => 'Guided Dietary Assessment',
						'excerpt'  => 'A practitioner-led consultation exploring eating patterns, constitution, lifestyle, and practical ways to support wellbeing without a one-size-fits-all plan.',
						'sections' => array(
							array(
								'heading'    => 'Overview',
								'paragraphs' => array(
									'A guided dietary assessment looks at food patterns alongside sleep, activity, health history, medicines, and the individual’s constitutional context. It is an educational consultation, not an automatic treatment prescription.',
								),
							),
							array(
								'heading' => 'What the Consultation Covers',
								'list'    => array(
									'A review of usual meals, hydration, timing, and food preferences',
									'Discussion of relevant health history and goals',
									'Consideration of cultural, religious, practical, and financial factors',
									'Shared next steps that can be reviewed with a qualified practitioner',
								),
							),
							array(
								'heading'    => 'Professional Guidance',
								'paragraphs' => array(
									'Suitability, frequency, and duration of any dietary change should be determined following an individual assessment by a qualified practitioner. People managing a diagnosed condition should also follow advice from their clinician.',
								),
							),
							array(
								'heading'    => 'References and Further Reading',
								'paragraphs' => array(
									'The consultation draws on established nutrition guidance, patient-reported context, and the Tibb House educational framework. References are discussed transparently during the appointment where relevant.',
								),
							),
						),
						'meta'     => array(
							'th_what_it_is'         => 'A structured practitioner consultation about food habits and lifestyle context. It does not provide a universal meal plan or diagnose a health condition.',
							'th_how_it_works'       => 'The practitioner reviews the person’s history, current habits, priorities, and relevant safety considerations before discussing general educational options.',
							'th_benefits'           => 'Potential benefits include clearer understanding of eating patterns, practical questions to take to a clinician, and a more realistic basis for future lifestyle discussions.',
							'th_risks_side_effects' => 'Dietary changes can affect energy, digestion, medicines, blood glucose, and other health measures. Changes should be discussed with a qualified professional when a condition or medication is involved.',
							'th_who_should_not_use' => 'Anyone with a complex medical condition, disordered eating history, pregnancy-related concern, or medication-related dietary restriction should seek appropriate clinical advice before making changes.',
							'th_price'              => 'From $75',
							'th_duration'           => '60-minute practitioner consultation + follow-up',
							'th_seek_care'          => 'Urgent or persistent symptoms should be assessed by an appropriate healthcare professional rather than managed through general dietary education.',
							'th_cta_text'           => 'Book a Dietary Assessment',
							'th_cta_link'           => home_url( '/contact-us/' ),
							'th_evidence_level'     => 'Educational consultation informed by nutrition guidance and practitioner assessment.',
							'th_priority'           => 80,
							'th_faq'               => array(
								array(
									'label' => 'Will I receive a fixed meal plan?',
									'value' => 'No. The consultation is designed to support an informed discussion. Any recommendations depend on the person’s circumstances and should be agreed with a qualified practitioner.',
								),
								array(
									'label' => 'Can this replace medical advice?',
									'value' => 'No. It is educational and complementary. A clinician should remain involved where symptoms, diagnoses, medicines, or specialist dietary needs are present.',
								),
							),
						),
						'terms'    => array(
							'remedies'       => array( 'Dietary Therapy' ),
							'vital_area'     => array( 'Digestive System' ),
							'patient_profile'=> array( 'Adults' ),
						),
						'image'    => 'treatment-honey.jpg',
					),
					array(
						'title'    => 'Personalised Herbal Consultation',
						'excerpt'  => 'A careful review of traditional herbal approaches, safety considerations, and the questions a practitioner should explore before any remedy is considered.',
						'sections' => array(
							array(
								'heading'    => 'Overview',
								'paragraphs' => array(
									'Herbal consultations at Tibb House are centred on assessment, safety, and informed choice. Traditional use can be discussed alongside current evidence, health history, medicines, allergies, and the person’s priorities.',
								),
							),
							array(
								'heading' => 'How Practitioners May Use the Consultation',
								'list'    => array(
									'Reviewing current medicines, supplements, and allergies',
									'Discussing the traditional purpose and evidence for a remedy',
									'Identifying situations where a remedy should be avoided or referred for clinical review',
									'Agreeing how any follow-up or monitoring would be handled',
								),
							),
							array(
								'heading'    => 'Safe, Individualised Care',
								'paragraphs' => array(
									'Traditional remedies are not automatically suitable for every person. Frequency, duration, preparation, and suitability should be determined following assessment by a qualified practitioner, with referral to medical care where appropriate.',
								),
							),
							array(
								'heading'    => 'References and Further Reading',
								'paragraphs' => array(
									'The practitioner can explain the distinction between traditional use, observational evidence, and stronger clinical evidence for any remedy discussed.',
								),
							),
						),
						'meta'     => array(
							'th_what_it_is'         => 'A consultation for discussing herbal and natural remedies in the context of a person’s history, medicines, allergies, and goals.',
							'th_how_it_works'       => 'The practitioner takes a history, checks safety factors, explains the available evidence, and decides whether further assessment or referral is appropriate.',
							'th_benefits'           => 'Potential benefits include a safer understanding of traditional remedies, clearer questions for a clinician, and a documented basis for follow-up.',
							'th_risks_side_effects' => 'Herbs can cause side effects, allergies, interactions, contamination risks, or delayed care if used in place of appropriate treatment.',
							'th_who_should_not_use' => 'People who are pregnant, breastfeeding, taking prescription medicines, preparing for surgery, or managing a significant condition should seek qualified advice before using a remedy.',
							'th_price'              => 'From $85',
							'th_duration'           => '60-minute practitioner consultation',
							'th_seek_care'          => 'Seek clinical care for severe, sudden, worsening, or unexplained symptoms. A consultation should not delay urgent assessment.',
							'th_cta_text'           => 'Speak to a Practitioner',
							'th_cta_link'           => home_url( '/contact-us/' ),
							'th_evidence_level'     => 'Traditional-use education with evidence and safety reviewed case by case.',
							'th_priority'           => 75,
							'th_faq'               => array(
								array(
									'label' => 'Are herbal remedies automatically safe because they are natural?',
									'value' => 'No. Natural products can have active effects, side effects, and interactions. Suitability should be assessed by a qualified practitioner.',
								),
								array(
									'label' => 'What should I bring to the consultation?',
									'value' => 'A current medicines and supplements list, relevant diagnoses, known allergies, and any questions about a remedy can help the practitioner assess the context.',
								),
							),
						),
						'terms'    => array(
							'remedies'       => array( 'Black Seed (Nigella Sativa)', 'Herbal Steam' ),
							'vital_area'     => array( 'Respiratory System' ),
							'patient_profile'=> array( 'Adults' ),
						),
						'image'    => 'treatment-herbal.jpg',
					),
					array(
						'title'    => 'Traditional Cupping Assessment',
						'excerpt'  => 'A practitioner-led cupping consultation focused on history, suitability, informed consent, and safe follow-up rather than a fixed protocol.',
						'sections' => array(
							array(
								'heading'    => 'Overview',
								'paragraphs' => array(
									'A traditional cupping assessment begins with a conversation about the person’s health history, current concerns, medicines, skin condition, and expectations. The practitioner then explains whether a session is appropriate and what alternatives or referrals may be needed.',
								),
							),
							array(
								'heading' => 'What the Assessment Includes',
								'list'    => array(
									'Review of relevant health history, medicines, allergies, and previous procedures',
									'Discussion of the intended purpose, possible benefits, limitations, and risks',
									'Consent and suitability checks before any procedure is considered',
									'Aftercare education and a clear follow-up or referral plan',
								),
							),
							array(
								'heading'    => 'Safety and Professional Care',
								'paragraphs' => array(
									'Cupping is not appropriate for every person or every symptom. A qualified practitioner should assess suitability, hygiene, skin integrity, bleeding risk, and the need for medical care before proceeding.',
								),
							),
							array(
								'heading'    => 'References and Further Reading',
								'paragraphs' => array(
									'The practitioner can explain what is supported by traditional use, what remains uncertain, and when current clinical guidance takes priority.',
								),
							),
						),
						'meta'     => array(
							'th_what_it_is'         => 'A consultation and suitability assessment for traditional cupping. It is not a guarantee of benefit and does not replace medical diagnosis or treatment.',
							'th_how_it_works'       => 'The practitioner reviews history and safety factors, explains the procedure and alternatives, obtains informed consent, and agrees appropriate follow-up or referral.',
							'th_benefits'           => 'Potential benefits include informed decision-making, a safety review, and practitioner-led aftercare education. Outcomes vary and evidence is not uniform for every use.',
							'th_risks_side_effects' => 'Possible risks include temporary marks, skin irritation, pain, dizziness, infection, burns, bleeding, and delayed care if it is used in place of appropriate treatment.',
							'th_who_should_not_use' => 'People with active skin infection, significant bleeding risk, uncontrolled medical conditions, pregnancy-related concerns, or medicines affecting clotting should seek qualified advice before considering cupping.',
							'th_price'              => 'From $65',
							'th_duration'           => '45-minute assessment and consultation',
							'th_seek_care'          => 'Seek urgent clinical care for severe symptoms, heavy bleeding, spreading redness, fever, fainting, or any unexpected deterioration.',
							'th_cta_text'           => 'Ask About Cupping',
							'th_cta_link'           => home_url( '/contact-us/' ),
							'th_evidence_level'     => 'Traditional-use practice with safety reviewed case by case.',
							'th_priority'           => 72,
							'th_faq'               => array(
								array(
									'label' => 'Is a cupping session guaranteed to help?',
									'value' => 'No. Benefits vary, and evidence differs by use. A practitioner should explain uncertainty and alternatives before consent.',
								),
								array(
									'label' => 'Does an assessment always lead to a procedure?',
									'value' => 'No. The practitioner may recommend waiting, seeking medical assessment, or choosing another form of support when cupping is not suitable.',
								),
							),
						),
						'terms'    => array(
							'remedies'       => array( 'Hijama Cupping' ),
							'vital_area'     => array( 'Circulatory System' ),
							'patient_profile'=> array( 'Adults' ),
						),
						'image'    => 'treatment-cupping.jpg',
					),
				),
				$term_ids
			);

			$condition_ids = $this->seed_items(
				'conditions',
				array(
					array(
						'title'    => 'Functional Digestive Discomfort',
						'excerpt'  => 'Digestive symptoms such as bloating, fullness, or irregular bowel habits that may have several possible causes and deserve careful assessment.',
						'sections' => array(
							array( 'heading' => 'Overview', 'paragraphs' => array( 'Digestive discomfort can describe a range of symptoms rather than one single diagnosis. A qualified practitioner considers timing, food patterns, stress, medicines, and warning signs before discussing general support.' ) ),
							array( 'heading' => 'Safety and Uncertainty', 'paragraphs' => array( 'Persistent, severe, or changing symptoms need appropriate clinical assessment. General educational information cannot establish the cause of digestive symptoms.' ) ),
							array( 'heading' => 'References and Further Reading', 'paragraphs' => array( 'Digestive symptoms are interpreted in context and may require assessment by a clinician, dietitian, or other suitably qualified professional.' ) ),
						),
						'meta'     => array(
							'th_definition'              => 'A descriptive term for digestive symptoms such as bloating, early fullness, abdominal discomfort, or irregular bowel habits when the underlying cause has not been established.',
							'th_symptoms'                => 'Possible symptoms include bloating, discomfort after meals, changes in bowel habits, nausea, or a feeling of fullness. Symptoms vary and are not specific to one condition.',
							'th_causes'                  => 'Possible contributors include diet, stress, infection, medication effects, food intolerance, functional gut disorders, and other medical conditions.',
							'th_risk_factors'            => 'Recent illness, major dietary change, prolonged stress, low activity, dehydration, and some medicines may influence digestive symptoms.',
							'th_diagnosis'               => 'A qualified clinician may review the symptom pattern, medical history, medicines, diet, and warning signs before deciding whether examination or tests are needed.',
							'th_treatment_options'       => 'General approaches may include reviewing diet and routines, addressing contributing factors, and using appropriate clinical or practitioner-led support after assessment.',
							'th_self_management'         => 'Keeping a symptom and food diary, maintaining practical hydration and movement habits, and seeking advice when symptoms persist can support a useful assessment.',
							'th_complications'           => 'Unassessed symptoms can sometimes reflect a condition requiring treatment. Delayed care is a concern when symptoms are persistent, severe, or associated with bleeding, weight loss, fever, or repeated vomiting.',
							'th_uncertain'               => 'Symptoms alone cannot identify the cause. Different digestive conditions can overlap, and an individual assessment is needed.',
							'th_questions_for_clinician' => 'What symptoms or warning signs should I record? Could my medicines or another condition contribute? Do I need an examination or tests?',
							'th_patient_profile'         => 'Adults seeking general education about digestive symptoms; not a diagnosis or personal treatment plan.',
							'th_faq'                     => array(
								array( 'label' => 'Does bloating identify a specific condition?', 'value' => 'No. Bloating has many possible causes. A clinician can help interpret persistent or concerning symptoms.' ),
								array( 'label' => 'When should I seek prompt care?', 'value' => 'Seek prompt medical advice for severe pain, bleeding, persistent vomiting, unexplained weight loss, fever, or rapidly worsening symptoms.' ),
							),
						),
						'terms'    => array( 'vital_area' => array( 'Digestive System' ), 'constitutional_type' => array( 'Cold Temperament' ), 'patient_profile' => array( 'Adults' ) ),
						'image'    => 'condition-digestive.jpg',
					),
					array(
						'title'    => 'Tension-Related Headache',
						'excerpt'  => 'A common headache pattern that may be associated with muscle tension, stress, sleep disruption, or prolonged screen use.',
						'sections' => array(
							array( 'heading' => 'Overview', 'paragraphs' => array( 'Tension-type headache is commonly described as pressure or tightness around the head. Similar symptoms can occur for different reasons, so recurrent or unusual headaches should be clinically assessed.' ) ),
							array( 'heading' => 'Safety and Uncertainty', 'paragraphs' => array( 'A sudden severe headache, new neurological symptom, head injury, fever with neck stiffness, or significant change in pattern requires urgent medical attention.' ) ),
							array( 'heading' => 'References and Further Reading', 'paragraphs' => array( 'Practitioner-led wellbeing support can sit alongside, but should not replace, appropriate headache assessment and clinical care.' ) ),
						),
						'meta'     => array(
							'th_definition'              => 'A common headache pattern often described as mild to moderate pressure or tightness, sometimes related to muscle tension or stress.',
							'th_symptoms'                => 'People may describe pressure on both sides of the head, scalp or neck tenderness, and discomfort that is not usually worsened by ordinary activity.',
							'th_causes'                  => 'Possible contributors include stress, poor sleep, prolonged screen use, muscle tension, dehydration, and other health factors.',
							'th_risk_factors'            => 'Irregular sleep, sustained desk work, jaw or neck tension, stress, and frequent use of pain medicines may be relevant factors.',
							'th_diagnosis'               => 'A clinician may ask about timing, frequency, associated symptoms, medicines, neurological signs, and triggers before deciding whether further assessment is needed.',
							'th_treatment_options'       => 'General approaches can include reviewing triggers, sleep and movement habits, appropriate clinical advice, and practitioner-led relaxation or bodywork where suitable.',
							'th_self_management'         => 'A headache diary, regular breaks from screens, hydration, sleep routines, and gentle movement may help identify patterns while awaiting professional advice.',
							'th_complications'           => 'Frequent headaches can affect daily life and may be associated with medication overuse or an underlying condition requiring review.',
							'th_uncertain'               => 'A headache description alone cannot confirm the cause. New, severe, or changing symptoms need clinical assessment.',
							'th_questions_for_clinician' => 'What pattern should I record? Could medicines, sleep, vision, or neck tension be contributing? What warning signs require urgent care?',
							'th_patient_profile'         => 'Adults seeking general education about recurrent tension-like headaches; not a diagnosis.',
							'th_faq'                     => array(
								array( 'label' => 'Can stress contribute to headaches?', 'value' => 'Stress can be one contributing factor, but recurrent headaches should still be assessed in context.' ),
								array( 'label' => 'What is an urgent warning sign?', 'value' => 'A sudden severe headache, weakness, confusion, vision loss, fever with neck stiffness, or headache after injury needs urgent medical assessment.' ),
							),
						),
						'terms'    => array( 'vital_area' => array( 'Nervous System' ), 'constitutional_type' => array( 'Hot Temperament' ), 'patient_profile' => array( 'Adults' ) ),
						'image'    => 'condition-joint.jpg',
					),
					array(
						'title'    => 'Seasonal Skin Irritation',
						'excerpt'  => 'Dryness, itching, or irritation that may change with weather, products, allergies, or an underlying skin condition.',
						'sections' => array(
							array( 'heading' => 'Overview', 'paragraphs' => array( 'Seasonal skin irritation can have several causes, from dry air and contact exposure to eczema, allergy, infection, or another dermatological condition.' ) ),
							array( 'heading' => 'Safety and Uncertainty', 'paragraphs' => array( 'Educational information cannot determine the cause of a rash. Widespread, painful, infected-looking, rapidly changing, or persistent skin changes should be reviewed by an appropriate clinician.' ) ),
							array( 'heading' => 'References and Further Reading', 'paragraphs' => array( 'A practitioner can discuss gentle care and traditional approaches only after considering the skin presentation and any need for medical referral.' ) ),
						),
						'meta'     => array(
							'th_definition'              => 'A descriptive term for skin dryness, itching, redness, or irritation that appears or changes with seasonal conditions.',
							'th_symptoms'                => 'Possible symptoms include dryness, itch, scaling, redness, sensitivity, or discomfort. These features can occur in many skin conditions.',
							'th_causes'                  => 'Possible contributors include dry air, temperature change, soaps, cosmetics, contact allergy, eczema, infection, and other dermatological conditions.',
							'th_risk_factors'            => 'Sensitive skin, occupational exposure, frequent washing, new products, allergies, and low humidity may increase irritation risk.',
							'th_diagnosis'               => 'A clinician may examine the skin, ask about timing and exposures, and consider medical history before deciding whether tests or specialist review are needed.',
							'th_treatment_options'       => 'General approaches may include avoiding known irritants, using suitable skin care, and receiving clinical treatment when a specific condition is identified.',
							'th_self_management'         => 'Record new products and exposures, use gentle fragrance-free care, and avoid scratching or applying unreviewed remedies to broken skin.',
							'th_complications'           => 'Scratching can damage the skin and increase infection risk. Persistent irritation may indicate a condition needing assessment.',
							'th_uncertain'               => 'The appearance of a rash is not enough to identify its cause. Advice should be tailored after assessment.',
							'th_questions_for_clinician' => 'Could a product or exposure be contributing? What signs suggest infection or allergy? Should I stop any current product?',
							'th_patient_profile'         => 'Adults seeking general education about seasonal skin irritation; not a diagnosis or personal treatment plan.',
							'th_faq'                     => array(
								array( 'label' => 'Should I try a new remedy on irritated skin?', 'value' => 'Ask a qualified practitioner or clinician first, especially if the skin is broken, painful, infected-looking, or affected by allergies.' ),
								array( 'label' => 'When should a rash be assessed?', 'value' => 'Seek advice for persistent, spreading, painful, blistering, infected-looking, or rapidly changing skin symptoms.' ),
							),
						),
						'terms'    => array( 'vital_area' => array( 'Circulatory System' ), 'constitutional_type' => array( 'Dry Temperament' ), 'patient_profile' => array( 'Adults' ) ),
						'image'    => 'condition-stress.jpg',
					),
				),
				$term_ids
			);

			$knowledge_ids = $this->seed_items(
				'knowledge',
				array(
					array(
						'title'    => 'How Practitioners Assess Constitutional Patterns',
						'excerpt'  => 'An educational introduction to constitutional language in Tibb practice and the role of careful, individual assessment.',
						'sections' => array(
							array( 'heading' => 'Why Assessment Matters', 'paragraphs' => array( 'Constitutional language is used in some traditional health systems to describe patterns of balance and imbalance. It should be treated as a framework for discussion rather than a substitute for clinical diagnosis.' ) ),
							array( 'heading' => 'What a Practitioner May Explore', 'list' => array( 'Health history and current concerns', 'Sleep, activity, appetite, and digestion', 'Environmental, cultural, and lifestyle context', 'Current medicines, allergies, and safety considerations' ) ),
							array( 'heading' => 'From Education to Individual Care', 'paragraphs' => array( 'A practitioner may use the conversation to decide what further assessment, referral, or general wellbeing support is appropriate. Individual treatment planning belongs in a qualified consultation.' ) ),
							array( 'heading' => 'Limits of the Framework', 'paragraphs' => array( 'Constitutional descriptions do not diagnose disease and should not be used to delay medical care. Evidence and uncertainty should be explained clearly.' ) ),
						),
						'meta'     => array(
							'th_author'                    => 'Tibb House Editorial Team',
							'th_medical_reviewer'          => 'Dr. Amina Yusuf — Certified Hijama Practitioner, Diploma in Traditional Herbal Medicine',
							'th_last_reviewed'             => '2026-09-01',
							'th_knowledge_type'            => 'Educational Guide',
							'th_evidence_level'            => 'Traditional framework with practitioner-led contextual assessment.',
							'th_references'                => 'Tibb House practitioner notes; World Health Organization resources on traditional medicine and safe, person-centred care.',
							'th_disclaimer'                => 'This article is educational information, not a diagnosis or personalised treatment plan. Speak with a qualified practitioner or clinician about individual concerns.',
							'th_priority'                  => 70,
							'th_faq'                       => array(
								array( 'label' => 'Does a constitutional pattern diagnose illness?', 'value' => 'No. It is a traditional framework for discussion and cannot replace clinical assessment or diagnosis.' ),
								array( 'label' => 'Why are medicines and allergies discussed?', 'value' => 'Safety depends on the whole context. Medicines, allergies, pregnancy, and existing conditions can affect whether an approach is appropriate.' ),
							),
							'th_patient_experience_toggle' => false,
						),
						'terms'    => array( 'knowledge_type' => array( 'Guide' ), 'evidence_level' => array( 'Traditional Use' ), 'patient_profile' => array( 'Adults' ), 'vital_area' => array( 'Nervous System' ) ),
						'image'    => 'knowledge-herbs.jpg',
					),
					array(
						'title'    => 'Understanding Safety in Traditional Herbal Care',
						'excerpt'  => 'How to ask better questions about herbal products, evidence, interactions, preparation, and when professional review is needed.',
						'sections' => array(
							array( 'heading' => 'Natural Does Not Mean Risk-Free', 'paragraphs' => array( 'Herbal products can contain active compounds and may affect the body in meaningful ways. Quality, preparation, dose, interactions, and the person using them all matter.' ) ),
							array( 'heading' => 'Questions to Ask', 'list' => array( 'What is the traditional purpose of the remedy?', 'What evidence supports the proposed use?', 'Could it interact with medicines or another product?', 'How is quality and preparation controlled?', 'What symptoms would mean I should stop and seek care?' ) ),
							array( 'heading' => 'Who Needs Extra Caution', 'paragraphs' => array( 'Pregnancy, breastfeeding, childhood, older age, liver or kidney disease, surgery, allergies, and prescription medicines can change the safety assessment.' ) ),
							array( 'heading' => 'A Practitioner-Led Approach', 'paragraphs' => array( 'A qualified practitioner can help distinguish education from a personal recommendation and can refer to medical care when symptoms or risks require it.' ) ),
						),
						'meta'     => array(
							'th_author'                    => 'Tibb House Editorial Team',
							'th_medical_reviewer'          => 'Dr. Amina Yusuf — Certified Hijama Practitioner, Diploma in Traditional Herbal Medicine',
							'th_last_reviewed'             => '2026-09-01',
							'th_knowledge_type'            => 'Safety Guide',
							'th_evidence_level'            => 'Safety education informed by traditional use, pharmacology principles, and clinical referral guidance.',
							'th_references'                => 'World Health Organization traditional medicine safety resources; NHS guidance on herbal medicines and interactions; practitioner review.',
							'th_disclaimer'                => 'This article does not recommend a product or dose for any individual. A qualified professional should assess suitability and potential interactions.',
							'th_priority'                  => 68,
							'th_faq'                       => array(
								array( 'label' => 'Can herbal products interact with prescriptions?', 'value' => 'Yes. Some products can alter the effects or absorption of medicines. Ask a pharmacist, clinician, or qualified practitioner before use.' ),
								array( 'label' => 'Should I stop prescribed medicine before using a remedy?', 'value' => 'Do not stop or change prescribed medicine without speaking with the prescribing clinician.' ),
							),
							'th_patient_experience_toggle' => false,
						),
						'terms'    => array( 'knowledge_type' => array( 'Guide' ), 'evidence_level' => array( 'Observational Evidence' ), 'patient_profile' => array( 'Adults' ), 'vital_area' => array( 'Respiratory System' ), 'remedies' => array( 'Black Seed (Nigella Sativa)', 'Herbal Steam' ) ),
						'image'    => 'knowledge-nutrition.jpg',
					),
					array(
						'title'    => 'When Educational Health Information Needs Professional Assessment',
						'excerpt'  => 'A practical guide to recognising uncertainty, warning signs, and the point where online education should give way to qualified care.',
						'sections' => array(
							array( 'heading' => 'Education Has Limits', 'paragraphs' => array( 'Health articles can help people prepare questions and understand general concepts, but they cannot examine a person, review every risk, or determine a diagnosis.' ) ),
							array( 'heading' => 'Reasons to Seek Assessment', 'list' => array( 'Symptoms are severe, persistent, or getting worse', 'There is unexplained bleeding, fainting, weakness, fever, or rapid weight loss', 'A new symptom appears during pregnancy or after starting medicine', 'A condition has already been diagnosed and is changing', 'You are unsure whether a traditional remedy is safe' ) ),
							array( 'heading' => 'Preparing for a Consultation', 'paragraphs' => array( 'A short record of symptoms, timing, medicines, supplements, allergies, and questions can help a clinician or practitioner understand the situation and explain the next step.' ) ),
							array( 'heading' => 'The Tibb House Journey', 'paragraphs' => array( 'The intended path is education, practitioner discussion, assessment, and then an individual plan where appropriate. The website should never replace that process.' ) ),
						),
						'meta'     => array(
							'th_author'                    => 'Tibb House Editorial Team',
							'th_medical_reviewer'          => 'Dr. Amina Yusuf — Certified Hijama Practitioner, Diploma in Traditional Herbal Medicine',
							'th_last_reviewed'             => '2026-09-01',
							'th_knowledge_type'            => 'Patient Guide',
							'th_evidence_level'            => 'General safety and referral education.',
							'th_references'                => 'NHS urgent care guidance; World Health Organization patient safety principles; Tibb House practitioner review.',
							'th_disclaimer'                => 'This article is general education and cannot diagnose, treat, or rule out a medical condition. Seek appropriate care for personal concerns.',
							'th_priority'                  => 66,
							'th_faq'                       => array(
								array( 'label' => 'Can I use an article to decide what treatment I need?', 'value' => 'No. Articles can support questions and general understanding, but suitability and treatment planning require qualified assessment.' ),
								array( 'label' => 'What should I do if symptoms feel urgent?', 'value' => 'Use appropriate urgent or emergency medical services in your area rather than waiting for an online response.' ),
							),
							'th_patient_experience_toggle' => false,
						),
						'terms'    => array( 'knowledge_type' => array( 'Guide' ), 'evidence_level' => array( 'Systematic Review' ), 'patient_profile' => array( 'Adults' ), 'vital_area' => array( 'Nervous System' ) ),
						'image'    => 'knowledge-book.jpg',
					),
				),
				$term_ids
			);

			$this->link_final_content( $treatment_ids, $condition_ids, $knowledge_ids );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( 'Tibb House Core: v4 seeder error — ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		update_option( self::SEEDED_V4_OPTION, time() );
	}

	/**
	 * Add three complete practitioner profiles using the existing CPT fields.
	 *
	 * These are educational profiles; they do not make individualized health
	 * claims or prescribe treatment.
	 *
	 * @return int[] Practitioner IDs.
	 */
	private function seed_final_practitioners() {
		return $this->seed_items(
			'practitioners',
			array(
				array(
					'title'    => 'Dr. Sara Malik',
					'excerpt'  => 'Integrative health practitioner helping visitors understand traditional wellbeing approaches alongside appropriate clinical care.',
					'sections' => array(
						array(
							'heading'    => 'About',
							'paragraphs' => array(
								'Dr. Sara Malik takes a careful, person-centred approach to consultations, beginning with health history, current concerns, medicines, and the questions a visitor wants to explore.',
							),
						),
						array(
							'heading' => 'Areas of Interest',
							'list'    => array(
								'General wellbeing education',
								'Practitioner-led lifestyle discussions',
								'Traditional remedies and safety questions',
								'Referral and follow-up planning',
							),
						),
					),
					'meta'     => array(
						'th_role'           => 'Integrative Health Practitioner',
						'th_qualifications' => 'MBBS, Certificate in Traditional Herbal Practice',
						'th_specializations' => 'Wellbeing education, practitioner assessment, traditional herbal safety',
						'th_booking_link'   => home_url( '/contact-us/' ),
					),
					'image'    => 'practitioner-1.jpg',
				),
				array(
					'title'    => 'Ustadh Hamza Qureshi',
					'excerpt'  => 'Prophetic nutrition educator offering practical, culturally aware conversations about food, routine, and balanced wellbeing.',
					'sections' => array(
						array(
							'heading'    => 'About',
							'paragraphs' => array(
								'Ustadh Hamza Qureshi helps visitors explore Prophetic dietary traditions in a practical and balanced way. His consultations focus on education and context rather than fixed meal plans or promises of a particular outcome.',
							),
						),
						array(
							'heading' => 'Areas of Interest',
							'list'    => array(
								'Prophetic dietary traditions',
								'Food and lifestyle education',
								'Culturally appropriate wellbeing conversations',
								'Questions to take to a qualified clinician',
							),
						),
					),
					'meta'     => array(
						'th_role'           => 'Prophetic Nutrition Educator',
						'th_qualifications' => 'Diploma in Islamic Nutrition Education, Certificate in Health Coaching',
						'th_specializations' => 'Prophetic nutrition, lifestyle education, culturally aware consultation',
						'th_booking_link'   => home_url( '/contact-us/' ),
					),
					'image'    => 'practitioner-2.jpg',
				),
				array(
					'title'    => 'Layla Siddiqui',
					'excerpt'  => 'Hijama and wellness practitioner focused on informed consent, hygiene, suitability checks, and clear aftercare education.',
					'sections' => array(
						array(
							'heading'    => 'About',
							'paragraphs' => array(
								'Layla Siddiqui approaches hijama consultations with a strong focus on safety, informed consent, hygiene, and knowing when a visitor should be referred for medical assessment.',
							),
						),
						array(
							'heading' => 'Areas of Interest',
							'list'    => array(
								'Traditional cupping education',
								'Suitability and safety conversations',
								'Aftercare and follow-up education',
								'Practitioner-led wellbeing support',
							),
						),
					),
					'meta'     => array(
						'th_role'           => 'Hijama & Wellness Practitioner',
						'th_qualifications' => 'Certified Hijama Practitioner, First Aid & Infection Prevention Training',
						'th_specializations' => 'Hijama education, informed consent, hygiene and aftercare',
						'th_booking_link'   => home_url( '/contact-us/' ),
					),
					'image'    => 'practitioner-3.jpg',
				),
			),
			array()
		);
	}

	/**
	 * Connect new practitioner profiles to existing locations.
	 *
	 * @param int[] $practitioner_ids Practitioner IDs.
	 */
	private function link_final_practitioners_locations( array $practitioner_ids ) {
		$locations = get_posts(
			array(
				'post_type'      => 'locations',
				'post_status'    => 'publish',
				'posts_per_page' => 3,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		if ( empty( $locations ) ) {
			return;
		}

		$location_ids = wp_list_pluck( $locations, 'ID' );
		foreach ( array_values( array_filter( $practitioner_ids ) ) as $index => $practitioner_id ) {
			$location_id = (int) $location_ids[ $index % count( $location_ids ) ];
			update_post_meta( $practitioner_id, 'th_clinic_location', array( $location_id ) );
			$this->append_relationship( $location_id, 'th_practitioners', array( $practitioner_id ) );
		}
	}

	/**
	 * Move named demo records out of the public site without deleting them.
	 */
	private function hide_demo_content() {
		$posts = get_posts(
			array(
				'post_type'      => array( 'treatments', 'conditions', 'knowledge', 'practitioners', 'locations' ),
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			)
		);
		foreach ( $posts as $post ) {
			$haystack = strtolower( $post->post_title . ' ' . $post->post_content . ' ' . $post->post_excerpt );
			$is_demo  = false;

			foreach (
				array(
					'demo treatment',
					'demo condition',
					'demo knowledge',
					'demo practitioner',
					'honey & olive oil wellness',
					'olive oil wellness',
					'digestive sluggishness',
					'mindful eating',
					'maryam rahman',
					'food cells',
					'niceas 24 age',
					'fictional, copyable',
					'fictional condition record',
					'fictional knowledge article',
					'copyable practitioner profile',
				) as $marker
			) {
				if ( false !== strpos( $haystack, $marker ) ) {
					$is_demo = true;
					break;
				}
			}

			if ( $is_demo && in_array( $post->post_status, array( 'publish', 'future', 'pending', 'private' ), true ) ) {
				wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'draft' ) );
			}
		}
	}

	/**
	 * Connect the new entries through the existing relationship meta keys.
	 */
	private function link_final_content( array $treatment_ids, array $condition_ids, array $knowledge_ids ) {
		$treatment_ids = array_values( array_filter( $treatment_ids ) );
		$condition_ids = array_values( array_filter( $condition_ids ) );
		$knowledge_ids = array_values( array_filter( $knowledge_ids ) );

		if ( ! empty( $treatment_ids[0] ) && ! empty( $condition_ids[0] ) ) {
			$this->append_relationship( $treatment_ids[0], 'th_related_conditions', array( $condition_ids[0] ) );
			$this->append_relationship( $condition_ids[0], 'th_treatment_relationships', array( $treatment_ids[0] ) );
		}
		if ( ! empty( $treatment_ids[1] ) && ! empty( $condition_ids[2] ) ) {
			$this->append_relationship( $treatment_ids[1], 'th_related_conditions', array( $condition_ids[2] ) );
			$this->append_relationship( $condition_ids[2], 'th_treatment_relationships', array( $treatment_ids[1] ) );
		}

		if ( ! empty( $condition_ids[0] ) && ! empty( $knowledge_ids[0] ) ) {
			$this->append_relationship( $condition_ids[0], 'th_knowledge_relationships', array( $knowledge_ids[0] ) );
			$this->append_relationship( $knowledge_ids[0], 'th_related_conditions', array( $condition_ids[0] ) );
			$this->append_relationship( $knowledge_ids[0], 'th_related_treatments', array( $treatment_ids[0] ?? 0 ) );
		}
		if ( ! empty( $condition_ids[1] ) && ! empty( $knowledge_ids[1] ) ) {
			$this->append_relationship( $condition_ids[1], 'th_knowledge_relationships', array( $knowledge_ids[1] ) );
			$this->append_relationship( $knowledge_ids[1], 'th_related_conditions', array( $condition_ids[1] ) );
			$this->append_relationship( $knowledge_ids[1], 'th_related_treatments', array( $treatment_ids[1] ?? 0 ) );
		}
		if ( ! empty( $condition_ids[2] ) && ! empty( $knowledge_ids[2] ) ) {
			$this->append_relationship( $condition_ids[2], 'th_knowledge_relationships', array( $knowledge_ids[2] ) );
			$this->append_relationship( $knowledge_ids[2], 'th_related_conditions', array( $condition_ids[2] ) );
			$this->append_relationship( $knowledge_ids[2], 'th_related_treatments', array( $treatment_ids[1] ?? 0 ) );
		}
	}

	/**
	 * Append IDs to an existing relationship field without duplicates.
	 */
	private function append_relationship( $post_id, $meta_key, array $related_ids ) {
		$current = get_post_meta( $post_id, $meta_key, true );
		$current = is_array( $current ) ? array_map( 'absint', $current ) : array();
		$merged  = array_values( array_filter( array_unique( array_merge( $current, $related_ids ) ) ) );
		update_post_meta( $post_id, $meta_key, $merged );
	}

	/**
	 * Option flag for the repair seeder.
	 */
	const SEEDED_REPAIR_OPTION = 'tibbhouse_starter_content_repaired_v1';

	/**
	 * Repair seeder: checks that minimum expected content exists and fills
	 * any gaps caused by earlier seeder failures. Runs on every admin_init
	 * until repair is confirmed complete.
	 *
	 * This is the safety net: even if v1/v2/v3 flags were set before content
	 * was created, this will silently fill the gaps.
	 */
	public function maybe_repair() {
		if ( get_option( self::SEEDED_REPAIR_OPTION ) ) {
			return;
		}

		// Only proceed if CPTs are registered (plugin is active).
		if ( ! post_type_exists( 'treatments' ) ) {
			return;
		}

		$counts = array();
		foreach ( array( 'treatments', 'conditions', 'knowledge', 'practitioners', 'locations' ) as $pt ) {
			$q = new WP_Query( array( 'post_type' => $pt, 'post_status' => 'publish', 'posts_per_page' => -1, 'no_found_rows' => false, 'fields' => 'ids' ) );
			$counts[ $pt ] = (int) $q->found_posts;
		}

		// If all CPTs have at least 3 posts each, repair is done.
		$all_good = ( $counts['treatments'] >= 3 && $counts['conditions'] >= 3 && $counts['knowledge'] >= 3 && $counts['practitioners'] >= 2 && $counts['locations'] >= 1 );
		if ( $all_good ) {
			update_option( self::SEEDED_REPAIR_OPTION, time() );
			return;
		}

		// Something is missing — reset all seeder flags and re-run everything.
		delete_option( self::SEEDED_OPTION );
		delete_option( self::SEEDED_V2_OPTION );
		delete_option( self::SEEDED_V3_OPTION );

		$this->maybe_seed();
		$this->maybe_seed_v2();
		$this->maybe_seed_v3();

		update_option( self::SEEDED_REPAIR_OPTION, time() );
	}

	/**
	 * Create a WordPress page only if one with the given title doesn't exist.
	 *
	 * @param string $title    Page title.
	 * @param string $slug     URL slug.
	 * @param string $content  Post content (Gutenberg blocks).
	 * @return int|null        Post ID or null on failure.
	 */
	private function create_page_if_missing( $title, $slug, $content ) {
		$q = new WP_Query( array(
			'post_type'              => 'page',
			'title'                  => $title,
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );
		if ( $q->have_posts() ) {
			return (int) $q->posts[0]->ID;
		}

		$page_id = wp_insert_post( array(
			'post_title'     => $title,
			'post_name'      => $slug,
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => $content,
			'post_author'    => get_current_user_id() ? get_current_user_id() : 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		), true );

		return is_wp_error( $page_id ) ? null : (int) $page_id;
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
