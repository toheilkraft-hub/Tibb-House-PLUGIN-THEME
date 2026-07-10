<?php
/**
 * Single Practitioner template.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$post_id      = get_the_ID();
	$role         = get_post_meta( $post_id, 'th_role', true );
	$qualifications = get_post_meta( $post_id, 'th_qualifications', true );
	$specializations = get_post_meta( $post_id, 'th_specializations', true );
	$profile_image = get_post_meta( $post_id, 'th_profile_image', true );
	$booking_link  = get_post_meta( $post_id, 'th_booking_link', true );
	$social_links  = get_post_meta( $post_id, 'th_social_links', true );
	?>
	<article <?php post_class( 'tibbhouse-single tibbhouse-single-practitioner' ); ?>>

		<header class="tibbhouse-hero tibbhouse-practitioner-header">
			<?php if ( $profile_image ) : ?>
				<div class="tibbhouse-hero-media"><?php echo wp_get_attachment_image( (int) $profile_image, 'medium' ); ?></div>
			<?php elseif ( has_post_thumbnail() ) : ?>
				<div class="tibbhouse-hero-media"><?php the_post_thumbnail( 'medium' ); ?></div>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( $role ) : ?><p class="tibbhouse-role"><?php echo esc_html( $role ); ?></p><?php endif; ?>
			<?php if ( $booking_link ) : ?>
				<a class="tibbhouse-button" href="<?php echo esc_url( $booking_link ); ?>"><?php esc_html_e( 'Book Now', 'tibbhouse-core' ); ?></a>
			<?php endif; ?>
		</header>

		<div class="tibbhouse-content">
			<?php the_content(); ?>
		</div>

		<?php if ( $qualifications ) : ?>
			<section><h2><?php esc_html_e( 'Qualifications', 'tibbhouse-core' ); ?></h2><div><?php echo wp_kses_post( wpautop( $qualifications ) ); ?></div></section>
		<?php endif; ?>

		<?php if ( $specializations ) : ?>
			<section><h2><?php esc_html_e( 'Specializations', 'tibbhouse-core' ); ?></h2><div><?php echo wp_kses_post( wpautop( $specializations ) ); ?></div></section>
		<?php endif; ?>

		<?php if ( ! empty( $social_links ) && is_array( $social_links ) ) : ?>
			<ul class="tibbhouse-social-links">
				<?php foreach ( $social_links as $link ) : ?>
					<?php if ( empty( $link['value'] ) ) { continue; } ?>
					<li><a href="<?php echo esc_url( $link['value'] ); ?>"><?php echo esc_html( $link['label'] ? $link['label'] : $link['value'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'practitioners' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</article>
	<?php
endwhile;

get_footer();
