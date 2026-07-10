<?php
/**
 * Single Location template.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$post_id  = get_the_ID();
	$address  = get_post_meta( $post_id, 'th_address', true );
	$map      = get_post_meta( $post_id, 'th_map_embed', true );
	$hours    = get_post_meta( $post_id, 'th_opening_hours', true );
	$phone    = get_post_meta( $post_id, 'th_phone', true );
	$email    = get_post_meta( $post_id, 'th_email', true );
	?>
	<article <?php post_class( 'tibbhouse-single tibbhouse-single-location' ); ?>>

		<header class="tibbhouse-hero">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="tibbhouse-hero-media"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
		</header>

		<div class="tibbhouse-content">
			<?php the_content(); ?>
		</div>

		<div class="tibbhouse-location-meta">
			<?php if ( $address ) : ?><p><strong><?php esc_html_e( 'Address:', 'tibbhouse-core' ); ?></strong> <?php echo wp_kses_post( nl2br( esc_html( $address ) ) ); ?></p><?php endif; ?>
			<?php if ( $phone ) : ?><p><strong><?php esc_html_e( 'Phone:', 'tibbhouse-core' ); ?></strong> <a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></p><?php endif; ?>
			<?php if ( $email ) : ?><p><strong><?php esc_html_e( 'Email:', 'tibbhouse-core' ); ?></strong> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?>
			<?php if ( $hours ) : ?><p><strong><?php esc_html_e( 'Opening Hours:', 'tibbhouse-core' ); ?></strong><br /><?php echo wp_kses_post( nl2br( esc_html( $hours ) ) ); ?></p><?php endif; ?>
		</div>

		<?php if ( $map ) : ?>
			<div class="tibbhouse-map-embed"><?php echo wp_kses( $map, array( 'iframe' => array( 'src' => true, 'width' => true, 'height' => true, 'style' => true, 'loading' => true, 'allowfullscreen' => true, 'referrerpolicy' => true, 'frameborder' => true ) ) ); ?></div>
		<?php endif; ?>

		<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'locations' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</article>
	<?php
endwhile;

get_footer();
