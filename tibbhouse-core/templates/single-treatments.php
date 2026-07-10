<?php
/**
 * Single Treatment template.
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
	$price        = get_post_meta( $post_id, 'th_price', true );
	$duration     = get_post_meta( $post_id, 'th_duration', true );
	$booking_url  = get_post_meta( $post_id, 'th_booking_url', true );
	$cta_text     = get_post_meta( $post_id, 'th_cta_text', true );
	$cta_link     = get_post_meta( $post_id, 'th_cta_link', true );
	$hero_image   = get_post_meta( $post_id, 'th_hero_image', true );
	$faq          = get_post_meta( $post_id, 'th_faq', true );
	?>
	<article <?php post_class( 'tibbhouse-single tibbhouse-single-treatment' ); ?>>

		<?php if ( function_exists( 'tibbhouse_breadcrumbs' ) ) : ?>
			<?php tibbhouse_breadcrumbs(); ?>
		<?php endif; ?>

		<header class="tibbhouse-hero">
			<?php if ( $hero_image ) : ?>
				<div class="tibbhouse-hero-media"><?php echo wp_get_attachment_image( (int) $hero_image, 'large' ); ?></div>
			<?php elseif ( has_post_thumbnail() ) : ?>
				<div class="tibbhouse-hero-media"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<ul class="tibbhouse-meta-row">
				<?php if ( $price ) : ?><li><strong><?php esc_html_e( 'Price:', 'tibbhouse-core' ); ?></strong> <?php echo esc_html( $price ); ?></li><?php endif; ?>
				<?php if ( $duration ) : ?><li><strong><?php esc_html_e( 'Duration:', 'tibbhouse-core' ); ?></strong> <?php echo esc_html( $duration ); ?></li><?php endif; ?>
			</ul>
			<?php if ( $booking_url ) : ?>
				<a class="tibbhouse-button" href="<?php echo esc_url( $booking_url ); ?>"><?php esc_html_e( 'Book Now', 'tibbhouse-core' ); ?></a>
			<?php endif; ?>
		</header>

		<div class="tibbhouse-content">
			<?php the_content(); ?>
		</div>

		<?php if ( $cta_text && $cta_link ) : ?>
			<div class="tibbhouse-block tibbhouse-cta">
				<a class="tibbhouse-button" href="<?php echo esc_url( $cta_link ); ?>"><?php echo esc_html( $cta_text ); ?></a>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $faq ) && is_array( $faq ) ) : ?>
			<section class="tibbhouse-block tibbhouse-faq">
				<h2><?php esc_html_e( 'Frequently Asked Questions', 'tibbhouse-core' ); ?></h2>
				<?php foreach ( $faq as $item ) : ?>
					<?php if ( empty( $item['label'] ) ) { continue; } ?>
					<details class="tibbhouse-faq-item">
						<summary><?php echo esc_html( $item['label'] ); ?></summary>
						<div class="tibbhouse-faq-answer"><?php echo wp_kses_post( $item['value'] ); ?></div>
					</details>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'treatments' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</article>
	<?php
endwhile;

get_footer();
