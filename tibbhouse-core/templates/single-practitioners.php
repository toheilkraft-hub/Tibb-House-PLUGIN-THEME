<?php
/**
 * Single Practitioner template — Tibb House Design System.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id          = get_the_ID();
	$role             = get_post_meta( $post_id, 'th_role', true );
	$qualifications   = get_post_meta( $post_id, 'th_qualifications', true );
	$specializations  = get_post_meta( $post_id, 'th_specializations', true );
	$profile_image    = get_post_meta( $post_id, 'th_profile_image', true );
	$booking_link     = get_post_meta( $post_id, 'th_booking_link', true );
	$social_links     = get_post_meta( $post_id, 'th_social_links', true );

	$constitutional_types = get_the_terms( $post_id, 'constitutional_type' );
	?>

<article <?php post_class( 'tibbhouse-single tibbhouse-single-practitioner' ); ?>>

	<!-- ── Compact Hero (no full-bleed for practitioners) ── -->
	<div class="tibbhouse-hero-wrap no-image" style="min-height:240px;">
		<div class="tibbhouse-hero-overlay"></div>
		<div class="tibbhouse-hero-content">
			<nav class="tibbhouse-breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'practitioners' ) ); ?>"><?php esc_html_e( 'Practitioners', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<span><?php the_title(); ?></span>
			</nav>
			<div class="th-post-type-badge">
				<svg viewBox="0 0 16 16"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.31 2.69-6 6-6s6 2.69 6 6"/></svg>
				<?php esc_html_e( 'Practitioner', 'tibbhouse-core' ); ?>
			</div>
		</div>
	</div>

	<!-- ── Profile Layout ── -->
	<div class="tibbhouse-inner">
		<div class="tibbhouse-practitioner-profile">

			<!-- Photo Column -->
			<div class="th-practitioner-photo">
				<div class="th-practitioner-photo-img">
					<?php if ( $profile_image ) : ?>
						<?php echo wp_get_attachment_image( (int) $profile_image, 'large' ); ?>
					<?php elseif ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large' ); ?>
					<?php else : ?>
						<div class="th-practitioner-photo-placeholder">
							<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.42 3.58-8 8-8s8 3.58 8 8"/></svg>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $booking_link ) : ?>
				<div style="margin-top:20px;">
					<a class="th-btn th-btn-primary" href="<?php echo esc_url( $booking_link ); ?>" style="width:100%;justify-content:center;">
						<?php esc_html_e( 'Book an Appointment', 'tibbhouse-core' ); ?>
					</a>
				</div>
				<?php endif; ?>

				<?php if ( ! empty( $social_links ) && is_array( $social_links ) ) : ?>
				<div class="th-social-links" style="margin-top:12px;">
					<?php foreach ( $social_links as $link ) : ?>
						<?php if ( empty( $link['value'] ) ) { continue; } ?>
						<a href="<?php echo esc_url( $link['value'] ); ?>" class="th-social-link" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $link['label'] ? $link['label'] : $link['value'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- Info Column -->
			<div>
				<h1 style="font-family:var(--th-font-heading);font-size:clamp(1.8rem,4vw,3rem);font-weight:600;color:var(--th-green);margin:0 0 8px;">
					<?php the_title(); ?>
				</h1>
				<?php if ( $role ) : ?>
				<p style="font-size:1rem;color:var(--th-gold);font-weight:600;letter-spacing:.05em;margin:0 0 24px;"><?php echo esc_html( $role ); ?></p>
				<?php endif; ?>

				<!-- Constitutional Types -->
				<?php if ( $constitutional_types && ! is_wp_error( $constitutional_types ) ) : ?>
				<div class="th-tax-group" style="margin-bottom:28px;">
					<?php foreach ( $constitutional_types as $term ) : ?>
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag"><?php echo esc_html( $term->name ); ?></a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<!-- Bio -->
				<?php $content = get_the_content(); if ( $content ) : ?>
				<div class="tibbhouse-section th-reveal" style="padding-top:0;">
					<div class="tibbhouse-section-label"><?php esc_html_e( 'About', 'tibbhouse-core' ); ?></div>
					<div class="tibbhouse-section-body"><?php the_content(); ?></div>
				</div>
				<?php endif; ?>

				<!-- Qualifications -->
				<?php if ( $qualifications ) : ?>
				<div class="tibbhouse-section th-reveal">
					<div class="tibbhouse-section-label"><?php esc_html_e( 'Qualifications', 'tibbhouse-core' ); ?></div>
					<div class="tibbhouse-section-body"><?php echo wp_kses_post( wpautop( $qualifications ) ); ?></div>
				</div>
				<?php endif; ?>

				<!-- Specializations -->
				<?php if ( $specializations ) : ?>
				<div class="tibbhouse-section th-reveal">
					<div class="tibbhouse-section-label"><?php esc_html_e( 'Specializations', 'tibbhouse-core' ); ?></div>
					<div class="tibbhouse-section-body"><?php echo wp_kses_post( wpautop( $specializations ) ); ?></div>
				</div>
				<?php endif; ?>

			</div>
		</div>
	</div>

	<!-- Related Content -->
	<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'practitioners' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

</article>

<?php
endwhile;

get_footer();
