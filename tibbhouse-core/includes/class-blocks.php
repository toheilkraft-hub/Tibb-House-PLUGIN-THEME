<?php
/**
 * Gutenberg block registration.
 *
 * Every block is server-rendered via a PHP callback (no static HTML saved
 * to post_content), and uses `register_block_type()` with a JS `edit`
 * script for the editor preview.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all Tibb House Gutenberg blocks.
 */
class Tibbhouse_Blocks {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Blocks|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Blocks
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
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * The list of blocks this plugin ships, each with its render callback.
	 *
	 * @return array<string, callable>
	 */
	private function block_render_callbacks() {
		return array(
			'hero'            => array( $this, 'render_hero' ),
			'cta'             => array( $this, 'render_cta' ),
			'faq'             => array( $this, 'render_faq' ),
			'testimonials'    => array( $this, 'render_testimonials' ),
			'booking-form'    => array( $this, 'render_booking_form' ),
			'card-grid'       => array( $this, 'render_card_grid' ),
			'related-content' => array( $this, 'render_related_content' ),
			'three-layer'     => array( $this, 'render_three_layer' ),
			'disclaimer'      => array( $this, 'render_disclaimer' ),
		);
	}

	/**
	 * Register the shared editor script and each block type.
	 */
	public function register_blocks() {
		wp_register_script(
			'tibbhouse-blocks-editor',
			TIBBHOUSE_CORE_URL . 'assets/js/blocks/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
			TIBBHOUSE_CORE_VERSION,
			true
		);

		wp_register_style(
			'tibbhouse-blocks-style',
			TIBBHOUSE_CORE_URL . 'assets/css/blocks.css',
			array(),
			TIBBHOUSE_CORE_VERSION
		);

		foreach ( $this->block_render_callbacks() as $name => $callback ) {
			register_block_type(
				'tibbhouse/' . $name,
				array(
					'editor_script'   => 'tibbhouse-blocks-editor',
					'style'           => 'tibbhouse-blocks-style',
					'render_callback' => $callback,
					'attributes'      => $this->attributes_for( $name ),
				)
			);
		}
	}

	/**
	 * Attribute schema per block.
	 *
	 * @param string $name Block name (without namespace).
	 * @return array
	 */
	private function attributes_for( $name ) {
		$common = array(
			'title' => array(
				'type'    => 'string',
				'default' => '',
			),
		);

		switch ( $name ) {
			case 'hero':
				return array_merge(
					$common,
					array(
						'subtitle'   => array( 'type' => 'string', 'default' => '' ),
						'imageId'    => array( 'type' => 'number', 'default' => 0 ),
						'ctaText'    => array( 'type' => 'string', 'default' => '' ),
						'ctaLink'    => array( 'type' => 'string', 'default' => '' ),
					)
				);

			case 'cta':
				return array(
					'text' => array( 'type' => 'string', 'default' => __( 'Book a Consultation', 'tibbhouse-core' ) ),
					'link' => array( 'type' => 'string', 'default' => '' ),
				);

			case 'faq':
				return array(
					'items' => array(
						'type'    => 'array',
						'default' => array(),
					),
				);

			case 'testimonials':
				return array(
					'items' => array(
						'type'    => 'array',
						'default' => array(),
					),
				);

			case 'booking-form':
				return array(
					'formLink' => array( 'type' => 'string', 'default' => '' ),
				);

			case 'card-grid':
				return array(
					'postType'  => array( 'type' => 'string', 'default' => 'treatments' ),
					'count'     => array( 'type' => 'number', 'default' => 6 ),
					'taxonomy'  => array( 'type' => 'string', 'default' => '' ),
					'termId'    => array( 'type' => 'number', 'default' => 0 ),
				);

			case 'related-content':
				return array();

			case 'three-layer':
				return array(
					'layerOne'   => array( 'type' => 'string', 'default' => '' ),
					'layerTwo'   => array( 'type' => 'string', 'default' => '' ),
					'layerThree' => array( 'type' => 'string', 'default' => '' ),
				);

			case 'disclaimer':
				return array(
					'text' => array(
						'type'    => 'string',
						'default' => __( 'This content is for informational purposes only and does not constitute medical advice.', 'tibbhouse-core' ),
					),
				);

			default:
				return $common;
		}
	}

	/**
	 * Hero block: title, subtitle, image, CTA.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_hero( $attrs ) {
		$title    = isset( $attrs['title'] ) ? $attrs['title'] : '';
		$subtitle = isset( $attrs['subtitle'] ) ? $attrs['subtitle'] : '';
		$image_id = isset( $attrs['imageId'] ) ? (int) $attrs['imageId'] : 0;
		$cta_text = isset( $attrs['ctaText'] ) ? $attrs['ctaText'] : '';
		$cta_link = isset( $attrs['ctaLink'] ) ? $attrs['ctaLink'] : '';

		ob_start();
		?>
		<section class="tibbhouse-block tibbhouse-hero">
			<?php if ( $image_id ) : ?>
				<div class="tibbhouse-hero-media"><?php echo wp_get_attachment_image( $image_id, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
			<div class="tibbhouse-hero-copy">
				<?php if ( $title ) : ?><h1><?php echo esc_html( $title ); ?></h1><?php endif; ?>
				<?php if ( $subtitle ) : ?><p class="tibbhouse-hero-subtitle"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
				<?php if ( $cta_text && $cta_link ) : ?>
					<a class="tibbhouse-button" href="<?php echo esc_url( $cta_link ); ?>"><?php echo esc_html( $cta_text ); ?></a>
				<?php endif; ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	/**
	 * CTA block: text + link button.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_cta( $attrs ) {
		$text = isset( $attrs['text'] ) ? $attrs['text'] : '';
		$link = isset( $attrs['link'] ) ? $attrs['link'] : '';

		if ( ! $text || ! $link ) {
			return '';
		}

		return sprintf(
			'<div class="tibbhouse-block tibbhouse-cta"><a class="tibbhouse-button" href="%s">%s</a></div>',
			esc_url( $link ),
			esc_html( $text )
		);
	}

	/**
	 * FAQ block: accordion of question/answer pairs.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_faq( $attrs ) {
		$items = isset( $attrs['items'] ) && is_array( $attrs['items'] ) ? $attrs['items'] : array();

		if ( empty( $items ) ) {
			return '';
		}

		ob_start();
		echo '<div class="tibbhouse-block tibbhouse-faq">';
		foreach ( $items as $item ) {
			$question = isset( $item['label'] ) ? $item['label'] : ( isset( $item['question'] ) ? $item['question'] : '' );
			$answer   = isset( $item['value'] ) ? $item['value'] : ( isset( $item['answer'] ) ? $item['answer'] : '' );
			if ( ! $question ) {
				continue;
			}
			printf(
				'<details class="tibbhouse-faq-item"><summary>%s</summary><div class="tibbhouse-faq-answer">%s</div></details>',
				esc_html( $question ),
				wp_kses_post( $answer )
			);
		}
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Testimonials block: quote carousel markup (JS optional in theme).
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_testimonials( $attrs ) {
		$items = isset( $attrs['items'] ) && is_array( $attrs['items'] ) ? $attrs['items'] : array();

		if ( empty( $items ) ) {
			return '';
		}

		ob_start();
		echo '<div class="tibbhouse-block tibbhouse-testimonials">';
		foreach ( $items as $item ) {
			$quote  = isset( $item['value'] ) ? $item['value'] : '';
			$author = isset( $item['label'] ) ? $item['label'] : '';
			if ( ! $quote ) {
				continue;
			}
			printf(
				'<blockquote class="tibbhouse-testimonial"><p>%s</p><cite>%s</cite></blockquote>',
				wp_kses_post( $quote ),
				esc_html( $author )
			);
		}
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Booking form block: renders a link/embed to the external booking system.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_booking_form( $attrs ) {
		$link = isset( $attrs['formLink'] ) ? $attrs['formLink'] : '';

		if ( ! $link ) {
			return '';
		}

		return sprintf(
			'<div class="tibbhouse-block tibbhouse-booking-form"><iframe src="%s" loading="lazy" title="%s"></iframe></div>',
			esc_url( $link ),
			esc_attr__( 'Booking Form', 'tibbhouse-core' )
		);
	}

	/**
	 * Card grid block: query-driven grid of posts (any Tibb House CPT, optionally by taxonomy term).
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_card_grid( $attrs ) {
		$post_type = isset( $attrs['postType'] ) ? sanitize_key( $attrs['postType'] ) : 'treatments';
		$count     = isset( $attrs['count'] ) ? absint( $attrs['count'] ) : 6;
		$taxonomy  = isset( $attrs['taxonomy'] ) ? sanitize_key( $attrs['taxonomy'] ) : '';
		$term_id   = isset( $attrs['termId'] ) ? absint( $attrs['termId'] ) : 0;

		if ( ! in_array( $post_type, Tibbhouse_Helpers::post_types(), true ) ) {
			return '';
		}

		$query_args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $count ? $count : 6,
			'post_status'    => 'publish',
		);

		if ( $taxonomy && $term_id ) {
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			);
		}

		$posts = get_posts( $query_args );

		if ( empty( $posts ) ) {
			return '';
		}

		ob_start();
		echo '<div class="tibbhouse-block tibbhouse-card-grid">';
		foreach ( $posts as $post ) {
			printf(
				'<a class="tibbhouse-card" href="%1$s">%2$s<span class="tibbhouse-card-title">%3$s</span></a>',
				esc_url( get_permalink( $post ) ),
				get_the_post_thumbnail( $post, 'medium' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html( get_the_title( $post ) )
			);
		}
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Related content block: wraps Tibbhouse_Relationships output for use inside Gutenberg.
	 *
	 * @return string
	 */
	public function render_related_content() {
		if ( ! is_singular( Tibbhouse_Helpers::post_types() ) ) {
			return '';
		}
		return Tibbhouse_Relationships::instance()->render_related_content( get_the_ID(), get_post_type() );
	}

	/**
	 * Three-layer block: presents constitutional/vital/patient layers side by side.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_three_layer( $attrs ) {
		$one   = isset( $attrs['layerOne'] ) ? $attrs['layerOne'] : '';
		$two   = isset( $attrs['layerTwo'] ) ? $attrs['layerTwo'] : '';
		$three = isset( $attrs['layerThree'] ) ? $attrs['layerThree'] : '';

		ob_start();
		?>
		<div class="tibbhouse-block tibbhouse-three-layer">
			<div class="tibbhouse-layer"><?php echo wp_kses_post( $one ); ?></div>
			<div class="tibbhouse-layer"><?php echo wp_kses_post( $two ); ?></div>
			<div class="tibbhouse-layer"><?php echo wp_kses_post( $three ); ?></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Disclaimer block: a small-print medical disclaimer banner.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	public function render_disclaimer( $attrs ) {
		$text = isset( $attrs['text'] ) ? $attrs['text'] : '';

		if ( ! $text ) {
			return '';
		}

		return sprintf( '<div class="tibbhouse-block tibbhouse-disclaimer">%s</div>', wp_kses_post( $text ) );
	}
}
