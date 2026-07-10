<?php
/**
 * Single Location template — Tibb House Design System.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();
	$address = get_post_meta( $post_id, 'th_address', true );
	$map     = get_post_meta( $post_id, 'th_map_embed', true );
	$hours   = get_post_meta( $post_id, 'th_opening_hours', true );
	$phone   = get_post_meta( $post_id, 'th_phone', true );
	$email   = get_post_meta( $post_id, 'th_email', true );

	$has_image = has_post_thumbnail();
	?>

<article <?php post_class( 'tibbhouse-single tibbhouse-single-location' ); ?>>

	<!-- ── Hero ── -->
	<div class="tibbhouse-hero-wrap<?php echo $has_image ? '' : ' no-image'; ?>" style="min-height:320px;">
		<?php if ( $has_image ) : ?>
			<div class="tibbhouse-hero-bg"><?php the_post_thumbnail( 'full', array( 'loading' => 'eager' ) ); ?></div>
		<?php endif; ?>
		<div class="tibbhouse-hero-overlay"></div>
		<div class="tibbhouse-hero-content">
			<nav class="tibbhouse-breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'locations' ) ); ?>"><?php esc_html_e( 'Locations', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<span><?php the_title(); ?></span>
			</nav>
			<div class="th-post-type-badge">
				<svg viewBox="0 0 16 16"><path d="M8 1a5 5 0 0 1 5 5c0 4-5 9-5 9S3 10 3 6a5 5 0 0 1 5-5zm0 3a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
				<?php esc_html_e( 'Location', 'tibbhouse-core' ); ?>
			</div>
			<h1><?php the_title(); ?></h1>
		</div>
	</div>

	<!-- ── Body ── -->
	<div class="tibbhouse-inner">

		<!-- Description -->
		<?php $content = get_the_content(); if ( $content ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'About This Location', 'tibbhouse-core' ); ?></div>
			<div class="tibbhouse-section-body"><?php the_content(); ?></div>
		</div>
		<?php endif; ?>

		<!-- Contact + Map grid -->
		<?php if ( $address || $phone || $email || $hours || $map ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Contact & Find Us', 'tibbhouse-core' ); ?></div>
			<div class="th-location-grid">

				<!-- Contact list -->
				<div class="th-contact-list">
					<?php if ( $address ) : ?>
					<div class="th-contact-item">
						<div class="th-contact-icon">
							<svg viewBox="0 0 24 24"><path d="M12 2a8 8 0 0 1 8 8c0 6-8 13-8 13S4 16 4 10a8 8 0 0 1 8-8zm0 5a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
						</div>
						<div class="th-contact-info">
							<label><?php esc_html_e( 'Address', 'tibbhouse-core' ); ?></label>
							<span><?php echo wp_kses_post( nl2br( esc_html( $address ) ) ); ?></span>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $phone ) : ?>
					<div class="th-contact-item">
						<div class="th-contact-icon">
							<svg viewBox="0 0 24 24"><path d="M6.62 10.79a15.5 15.5 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C9.94 21 3 14.06 3 5.5a1 1 0 0 1 1-1H7.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.58a1 1 0 0 1-.25 1.01l-2.2 2.2z"/></svg>
						</div>
						<div class="th-contact-info">
							<label><?php esc_html_e( 'Phone', 'tibbhouse-core' ); ?></label>
							<a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $email ) : ?>
					<div class="th-contact-item">
						<div class="th-contact-icon">
							<svg viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
						</div>
						<div class="th-contact-info">
							<label><?php esc_html_e( 'Email', 'tibbhouse-core' ); ?></label>
							<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $hours ) : ?>
					<div class="th-contact-item">
						<div class="th-contact-icon">
							<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
						</div>
						<div class="th-contact-info">
							<label><?php esc_html_e( 'Opening Hours', 'tibbhouse-core' ); ?></label>
							<span><?php echo wp_kses_post( nl2br( esc_html( $hours ) ) ); ?></span>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<!-- Map -->
				<?php if ( $map ) : ?>
				<div class="th-map-embed">
					<?php echo wp_kses( $map, array( 'iframe' => array( 'src' => true, 'width' => true, 'height' => true, 'style' => true, 'loading' => true, 'allowfullscreen' => true, 'referrerpolicy' => true, 'frameborder' => true, 'allow' => true ) ) ); ?>
				</div>
				<?php else : ?>
				<div style="background:var(--th-green-light);border-radius:var(--th-radius-lg);height:360px;display:flex;align-items:center;justify-content:center;">
					<span style="color:var(--th-green);opacity:.4;font-size:.85rem;"><?php esc_html_e( 'Map not available', 'tibbhouse-core' ); ?></span>
				</div>
				<?php endif; ?>

			</div>
		</div>
		<?php endif; ?>

	</div><!-- /.tibbhouse-inner -->

	<!-- Related Content -->
	<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'locations' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

</article>

<?php
endwhile;

get_footer();
