<?php
/**
 * Single Knowledge Article template — Tibb House Design System.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id        = get_the_ID();
	$author         = get_post_meta( $post_id, 'th_author', true );
	$medical_reviewer = get_post_meta( $post_id, 'th_medical_reviewer', true );
	$last_reviewed  = get_post_meta( $post_id, 'th_last_reviewed', true );
	$knowledge_type = get_post_meta( $post_id, 'th_knowledge_type', true );
	$evidence_level = get_post_meta( $post_id, 'th_evidence_level', true );
	$references     = get_post_meta( $post_id, 'th_references', true );
	$disclaimer     = get_post_meta( $post_id, 'th_disclaimer', true );
	$faq            = get_post_meta( $post_id, 'th_faq', true );

	$knowledge_types  = get_the_terms( $post_id, 'knowledge_type' );
	$evidence_terms   = get_the_terms( $post_id, 'evidence_level' );
	$patient_profiles = get_the_terms( $post_id, 'patient_profile' );
	$vital_areas      = get_the_terms( $post_id, 'vital_area' );
	$remedies         = get_the_terms( $post_id, 'remedies' );

	$has_image = has_post_thumbnail();
	?>

<article <?php post_class( 'tibbhouse-single tibbhouse-single-knowledge' ); ?>>

	<!-- ── Hero ── -->
	<div class="tibbhouse-hero-wrap<?php echo $has_image ? '' : ' no-image'; ?>">

		<?php if ( $has_image ) : ?>
			<div class="tibbhouse-hero-bg"><?php the_post_thumbnail( 'full', array( 'loading' => 'eager' ) ); ?></div>
		<?php endif; ?>

		<div class="tibbhouse-hero-overlay"></div>

		<div class="tibbhouse-hero-content<?php echo $has_image ? '' : ' centered'; ?>">
			<nav class="tibbhouse-breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'knowledge' ) ); ?>"><?php esc_html_e( 'Knowledge', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<span><?php the_title(); ?></span>
			</nav>

			<div class="th-post-type-badge">
				<svg viewBox="0 0 16 16"><path d="M3 2h8l2 2v10H3V2zm2 4h6M5 9h6M5 12h4"/></svg>
				<?php
				if ( $knowledge_types && ! is_wp_error( $knowledge_types ) ) {
					echo esc_html( $knowledge_types[0]->name );
				} else {
					esc_html_e( 'Knowledge', 'tibbhouse-core' );
				}
				?>
			</div>

			<h1><?php the_title(); ?></h1>

			<?php if ( $author || $medical_reviewer || $last_reviewed ) : ?>
			<div class="tibbhouse-hero-meta">
				<?php if ( $author ) : ?><span class="th-meta-chip">
					<svg viewBox="0 0 16 16"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.31 2.69-6 6-6s6 2.69 6 6"/></svg>
					<?php echo esc_html( $author ); ?>
				</span><?php endif; ?>
				<?php if ( $medical_reviewer ) : ?><span class="th-meta-chip">
					<svg viewBox="0 0 16 16"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.31 2.69-6 6-6s6 2.69 6 6"/></svg>
					<?php echo esc_html( sprintf( __( 'Reviewed by %s', 'tibbhouse-core' ), $medical_reviewer ) ); ?>
				</span><?php endif; ?>
				<?php if ( $last_reviewed ) : ?><span class="th-meta-chip">
					<svg viewBox="0 0 16 16"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 1v4M11 1v4M2 7h12"/></svg>
					<?php echo esc_html( sprintf( __( 'Last reviewed %s', 'tibbhouse-core' ), $last_reviewed ) ); ?>
				</span><?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- ── Body (narrow for article readability) ── -->
	<div class="tibbhouse-inner narrow">

		<!-- Tags -->
		<?php if (
			( $knowledge_types && ! is_wp_error( $knowledge_types ) ) ||
			( $evidence_terms && ! is_wp_error( $evidence_terms ) ) ||
			( $patient_profiles && ! is_wp_error( $patient_profiles ) ) ||
			( $vital_areas && ! is_wp_error( $vital_areas ) ) ||
			( $remedies && ! is_wp_error( $remedies ) )
		) : ?>
		<div class="tibbhouse-section th-reveal">
			<?php if ( $evidence_terms && ! is_wp_error( $evidence_terms ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Evidence Level', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $evidence_terms as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $patient_profiles && ! is_wp_error( $patient_profiles ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Patient Profile', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $patient_profiles as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag gold"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $vital_areas && ! is_wp_error( $vital_areas ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Vital Areas', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $vital_areas as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<!-- Main Content -->
		<?php $content = get_the_content(); if ( $content ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Article', 'tibbhouse-core' ); ?></div>
			<div class="tibbhouse-section-body"><?php the_content(); ?></div>
		</div>
		<?php endif; ?>

		<!-- Evidence Level (free text) -->
		<?php if ( $evidence_level ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Evidence Level', 'tibbhouse-core' ); ?></div>
			<div class="th-highlight-band"><?php echo wp_kses_post( wpautop( $evidence_level ) ); ?></div>
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
								<svg viewBox="0 0 16 16" width="16" height="16"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
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

		<!-- References -->
		<?php if ( $references ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'References', 'tibbhouse-core' ); ?></div>
			<div class="tibbhouse-section-body" style="font-size:.85rem;"><?php echo wp_kses_post( wpautop( $references ) ); ?></div>
		</div>
		<?php endif; ?>

		<!-- Disclaimer -->
		<?php if ( $disclaimer ) : ?>
		<div class="tibbhouse-disclaimer-block th-reveal">
			<strong><?php esc_html_e( 'Disclaimer:', 'tibbhouse-core' ); ?></strong> <?php echo wp_kses_post( $disclaimer ); ?>
		</div>
		<?php endif; ?>

	</div><!-- /.tibbhouse-inner -->

	<!-- Related Content -->
	<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'knowledge' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

</article>

<?php
endwhile;

get_footer();
