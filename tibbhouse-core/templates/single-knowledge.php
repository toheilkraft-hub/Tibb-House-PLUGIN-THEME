<?php
/**
 * Single Knowledge template.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$post_id     = get_the_ID();
	$author      = get_post_meta( $post_id, 'th_author', true );
	$references  = get_post_meta( $post_id, 'th_references', true );
	$disclaimer  = get_post_meta( $post_id, 'th_disclaimer', true );
	$faq         = get_post_meta( $post_id, 'th_faq', true );
	?>
	<article <?php post_class( 'tibbhouse-single tibbhouse-single-knowledge' ); ?>>

		<header class="tibbhouse-hero">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="tibbhouse-hero-media"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( $author ) : ?>
				<p class="tibbhouse-byline"><?php esc_html_e( 'By', 'tibbhouse-core' ); ?> <?php echo esc_html( $author ); ?></p>
			<?php endif; ?>
		</header>

		<div class="tibbhouse-content">
			<?php the_content(); ?>
		</div>

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

		<?php if ( $references ) : ?>
			<section class="tibbhouse-references">
				<h2><?php esc_html_e( 'References', 'tibbhouse-core' ); ?></h2>
				<div><?php echo wp_kses_post( wpautop( $references ) ); ?></div>
			</section>
		<?php endif; ?>

		<?php if ( $disclaimer ) : ?>
			<div class="tibbhouse-block tibbhouse-disclaimer"><?php echo wp_kses_post( $disclaimer ); ?></div>
		<?php endif; ?>

		<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'knowledge' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</article>
	<?php
endwhile;

get_footer();
