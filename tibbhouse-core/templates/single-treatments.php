<?php
/**
 * Single Treatment template — Tibb House Design System.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id             = get_the_ID();
	$price               = get_post_meta( $post_id, 'th_price', true );
	$what_it_is          = get_post_meta( $post_id, 'th_what_it_is', true );
	$how_it_works        = get_post_meta( $post_id, 'th_how_it_works', true );
	$benefits            = get_post_meta( $post_id, 'th_benefits', true );
	$risks_side_effects  = get_post_meta( $post_id, 'th_risks_side_effects', true );
	$who_should_not_use  = get_post_meta( $post_id, 'th_who_should_not_use', true );
	$seek_care           = get_post_meta( $post_id, 'th_seek_care', true );
	$duration            = get_post_meta( $post_id, 'th_duration', true );
	$booking_url         = get_post_meta( $post_id, 'th_booking_url', true );
	$cta_text            = get_post_meta( $post_id, 'th_cta_text', true );
	$cta_link            = get_post_meta( $post_id, 'th_cta_link', true );
	$hero_image          = get_post_meta( $post_id, 'th_hero_image', true );
	$faq                 = get_post_meta( $post_id, 'th_faq', true );
	$evidence_level      = get_post_meta( $post_id, 'th_evidence_level', true );
	$outcome_measurement = get_post_meta( $post_id, 'th_outcome_measurement', true );
	$gallery             = get_post_meta( $post_id, 'th_gallery', true );
	$gallery             = is_array( $gallery ) ? array_filter( array_map( 'absint', $gallery ) ) : array();
	$video_url           = get_post_meta( $post_id, 'th_video_url', true );

	// Taxonomies
	$constitutional_types = get_the_terms( $post_id, 'constitutional_type' );
	$vital_areas          = get_the_terms( $post_id, 'vital_area' );
	$evidence_terms       = get_the_terms( $post_id, 'evidence_level' );
	$remedies             = get_the_terms( $post_id, 'remedies' );

	$has_image = $hero_image || has_post_thumbnail();
	?>

<article <?php post_class( 'tibbhouse-single tibbhouse-single-treatment' ); ?>>

	<!-- ── Hero ── -->
	<div class="tibbhouse-hero-wrap<?php echo $has_image ? '' : ' no-image'; ?>">

		<?php if ( $hero_image ) : ?>
			<div class="tibbhouse-hero-bg"><?php echo wp_get_attachment_image( (int) $hero_image, 'full', false, array( 'loading' => 'eager' ) ); ?></div>
		<?php elseif ( has_post_thumbnail() ) : ?>
			<div class="tibbhouse-hero-bg"><?php the_post_thumbnail( 'full', array( 'loading' => 'eager' ) ); ?></div>
		<?php endif; ?>

		<div class="tibbhouse-hero-overlay"></div>

		<div class="tibbhouse-hero-content">
			<!-- Breadcrumb -->
			<nav class="tibbhouse-breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'treatments' ) ); ?>"><?php esc_html_e( 'Treatments', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<span><?php the_title(); ?></span>
			</nav>

			<div class="th-post-type-badge">
				<svg viewBox="0 0 16 16"><path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm.75 9.5h-1.5v-4h1.5v4zm0-5.5h-1.5V3.5h1.5V5z"/></svg>
				<?php esc_html_e( 'Treatment', 'tibbhouse-core' ); ?>
			</div>

			<h1><?php the_title(); ?></h1>

			<?php if ( $price || $duration ) : ?>
			<div class="tibbhouse-hero-meta">
				<?php if ( $price ) : ?>
				<span class="th-meta-chip">
					<svg viewBox="0 0 16 16"><path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm.75 6.5H7.5V5h1.25V7.5zm0 3H7.5V9h1.25v1.5z"/></svg>
					<?php echo esc_html( $price ); ?>
				</span>
				<?php endif; ?>
				<?php if ( $duration ) : ?>
				<span class="th-meta-chip">
					<svg viewBox="0 0 16 16"><path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm.5 7.21V4h-1v4.5l3.25 1.96.5-.87L8.5 8.21z"/></svg>
					<?php echo esc_html( $duration ); ?>
				</span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="th-btn-actions">
				<?php if ( $booking_url ) : ?>
				<a class="th-btn th-btn-hero" href="<?php echo esc_url( $booking_url ); ?>">
					<?php esc_html_e( 'Book This Treatment', 'tibbhouse-core' ); ?> →
				</a>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- ── Body ── -->
	<div class="tibbhouse-inner">

		<!-- Taxonomy Tags -->
		<?php if ( ( $constitutional_types && ! is_wp_error( $constitutional_types ) ) || ( $vital_areas && ! is_wp_error( $vital_areas ) ) || ( $evidence_terms && ! is_wp_error( $evidence_terms ) ) || ( $remedies && ! is_wp_error( $remedies ) ) ) : ?>
		<div class="tibbhouse-section th-reveal">
			<?php if ( $constitutional_types && ! is_wp_error( $constitutional_types ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Constitutional Type', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $constitutional_types as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $vital_areas && ! is_wp_error( $vital_areas ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Vital Areas', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $vital_areas as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag gold"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $evidence_terms && ! is_wp_error( $evidence_terms ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Evidence Level', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $evidence_terms as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $remedies && ! is_wp_error( $remedies ) ) : ?>
			<div class="th-tax-group">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Remedies', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $remedies as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag gold"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<!-- Structured Treatment Information -->
		<?php
		$structured_sections = array(
			__( 'What It Is', 'tibbhouse-core' )                 => $what_it_is,
			__( 'How It Works', 'tibbhouse-core' )               => $how_it_works,
			__( 'Benefits', 'tibbhouse-core' )                   => $benefits,
			__( 'Risks and Side Effects', 'tibbhouse-core' )     => $risks_side_effects,
			__( 'Who Should Not Use It', 'tibbhouse-core' )      => $who_should_not_use,
			__( 'Cost', 'tibbhouse-core' )                       => $price,
			__( 'When to Seek Professional Care', 'tibbhouse-core' ) => $seek_care,
		);
		foreach ( $structured_sections as $section_title => $section_content ) :
			if ( ! $section_content ) {
				continue;
			}
			?>
			<section class="tibbhouse-section th-reveal th-treatment-structured-section">
				<h2 class="tibbhouse-section-label th-treatment-section-title"><?php echo esc_html( $section_title ); ?></h2>
				<div class="tibbhouse-section-body"><?php echo wp_kses_post( wpautop( $section_content ) ); ?></div>
			</section>
		<?php endforeach; ?>

		<!-- Overview / Main Content -->
		<?php $content = get_the_content(); if ( $content ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Overview', 'tibbhouse-core' ); ?></div>
			<div class="tibbhouse-section-body"><?php the_content(); ?></div>
		</div>
		<?php endif; ?>

		<!-- Evidence Level -->
		<?php if ( $evidence_level ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Evidence Level', 'tibbhouse-core' ); ?></div>
			<div class="th-highlight-band"><?php echo wp_kses_post( wpautop( $evidence_level ) ); ?></div>
		</div>
		<?php endif; ?>

		<!-- Outcome Measurement -->
		<?php if ( $outcome_measurement ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Outcome Measurement', 'tibbhouse-core' ); ?></div>
			<div class="tibbhouse-section-body"><?php echo wp_kses_post( wpautop( $outcome_measurement ) ); ?></div>
		</div>
		<?php endif; ?>

		<!-- Photo Gallery -->
		<?php if ( ! empty( $gallery ) ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Photo Gallery', 'tibbhouse-core' ); ?></div>
			<div class="th-gallery-grid" role="list">
				<?php foreach ( $gallery as $index => $attachment_id ) : ?>
				<?php
				$full_url  = wp_get_attachment_image_url( $attachment_id, 'large' );
				$thumb_url = wp_get_attachment_image_url( $attachment_id, 'medium' );
				$alt       = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
				if ( ! $alt ) {
					/* translators: %d: image number */
					$alt = sprintf( __( 'Gallery image %d', 'tibbhouse-core' ), $index + 1 );
				}
				if ( ! $full_url ) { continue; }
				?>
				<button
					class="th-gallery-item"
					type="button"
					data-full="<?php echo esc_url( $full_url ); ?>"
					data-alt="<?php echo esc_attr( $alt ); ?>"
					data-index="<?php echo esc_attr( $index ); ?>"
					aria-label="<?php echo esc_attr( $alt ); ?>"
					role="listitem"
				>
					<img
						src="<?php echo esc_url( $thumb_url ); ?>"
						alt="<?php echo esc_attr( $alt ); ?>"
						loading="lazy"
					>
					<span class="th-gallery-zoom" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35M11 8v6M8 11h6"/></svg>
					</span>
				</button>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- Video -->
		<?php if ( $video_url ) : ?>
		<?php
		// Try oEmbed first (YouTube, Vimeo, etc.) — falls back to a <video> tag for MP4.
		$embed_html = wp_oembed_get( $video_url, array( 'width' => 960 ) );
		?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Video', 'tibbhouse-core' ); ?></div>
			<div class="th-video-wrap">
				<?php if ( $embed_html ) : ?>
					<div class="th-video-embed"><?php echo $embed_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php else : ?>
					<div class="th-video-embed">
						<video controls preload="metadata" style="width:100%;border-radius:var(--th-radius);">
							<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
							<?php esc_html_e( 'Your browser does not support the video tag.', 'tibbhouse-core' ); ?>
						</video>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- FAQ -->
		<?php if ( ! empty( $faq ) && is_array( $faq ) ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Frequently Asked Questions', 'tibbhouse-core' ); ?></div>
			<div class="tibbhouse-faq-wrap">
				<?php foreach ( $faq as $item ) : ?>
					<?php if ( empty( $item['label'] ) ) { continue; } ?>
					<div class="th-faq-item">
						<button class="th-faq-trigger" type="button">
							<?php echo esc_html( $item['label'] ); ?>
							<span class="th-faq-icon" aria-hidden="true">
								<svg viewBox="0 0 16 16" width="16" height="16" fill="currentColor"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
							</span>
						</button>
						<div class="th-faq-body">
							<div class="th-faq-body-inner"><?php echo wp_kses_post( $item['value'] ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- CTA -->
		<?php if ( $cta_text && $cta_link ) : ?>
		<div class="tibbhouse-cta-band th-reveal">
			<h2><?php echo esc_html( $cta_text ); ?></h2>
			<a class="th-btn th-btn-primary" href="<?php echo esc_url( $cta_link ); ?>"><?php echo esc_html( $cta_text ); ?> →</a>
		</div>
		<?php endif; ?>

	</div><!-- /.tibbhouse-inner -->

	<!-- Related Content -->
	<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'treatments' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

</article>

<?php
endwhile;

get_footer();
