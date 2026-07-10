<?php
/**
 * Single Condition template.
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
	$symptoms = get_post_meta( $post_id, 'th_symptoms', true );
	$causes   = get_post_meta( $post_id, 'th_causes', true );
	$hero     = get_post_meta( $post_id, 'th_hero_image', true );
	$faq      = get_post_meta( $post_id, 'th_faq', true );
	?>
	<article <?php post_class( 'tibbhouse-single tibbhouse-single-condition' ); ?>>

		<header class="tibbhouse-hero">
			<?php if ( $hero ) : ?>
				<div class="tibbhouse-hero-media"><?php echo wp_get_attachment_image( (int) $hero, 'large' ); ?></div>
			<?php elseif ( has_post_thumbnail() ) : ?>
				<div class="tibbhouse-hero-media"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
		</header>

		<div class="tibbhouse-content">
			<?php the_content(); ?>
		</div>

		<?php if ( $symptoms ) : ?>
			<section class="tibbhouse-three-layer">
				<h2><?php esc_html_e( 'Symptoms', 'tibbhouse-core' ); ?></h2>
				<div><?php echo wp_kses_post( wpautop( $symptoms ) ); ?></div>
			</section>
		<?php endif; ?>

		<?php if ( $causes ) : ?>
			<section class="tibbhouse-three-layer">
				<h2><?php esc_html_e( 'Causes', 'tibbhouse-core' ); ?></h2>
				<div><?php echo wp_kses_post( wpautop( $causes ) ); ?></div>
			</section>
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

		<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'conditions' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</article>
	<?php
endwhile;

get_footer();
