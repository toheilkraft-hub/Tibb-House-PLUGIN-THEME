<?php
/**
 * Single post template.
 *
 * @package Tibbhouse
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>

<?php if ( has_post_thumbnail() ) : ?>
<div class="tibbhouse-page-hero" style="padding:0;min-height:400px;display:flex;align-items:flex-end;position:relative;overflow:hidden;">
	<div style="position:absolute;inset:0;"><?php the_post_thumbnail( 'tibbhouse-hero', array( 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?></div>
	<div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(10,61,46,.9) 0%,rgba(10,61,46,.3) 100%);"></div>
	<div style="position:relative;z-index:2;padding:48px 5%;">
		<h1 style="color:#fff;font-family:var(--th-font-heading);font-size:clamp(2rem,5vw,3rem);font-weight:600;margin:0;"><?php the_title(); ?></h1>
	</div>
</div>
<?php else : ?>
<div class="tibbhouse-page-hero">
	<h1><?php the_title(); ?></h1>
</div>
<?php endif; ?>

<div class="tibbhouse-main with-sidebar">
	<main>
		<?php tibbhouse_breadcrumbs(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;flex-wrap:wrap;">
				<?php if ( get_avatar( get_the_author_meta('ID'), 40 ) ) : ?>
				<div style="width:40px;height:40px;border-radius:50%;overflow:hidden;"><?php echo get_avatar( get_the_author_meta('ID'), 40 ); ?></div>
				<?php endif; ?>
				<div>
					<div style="font-weight:600;font-size:.88rem;color:var(--th-dark);"><?php the_author(); ?></div>
					<div style="font-size:.78rem;color:var(--th-muted);"><?php echo get_the_date(); ?></div>
				</div>
			</div>

			<div class="entry-content">
				<?php the_content(); ?>
			</div>

			<div style="margin-top:32px;">
				<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'tibbhouse' ),
					'after'  => '</div>',
				) );
				?>
			</div>
		</article>

		<?php
		the_post_navigation( array(
			'prev_text' => '&larr; %title',
			'next_text' => '%title &rarr;',
		) );
		?>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	</main>

	<aside class="tibbhouse-sidebar">
		<?php get_sidebar(); ?>
	</aside>
</div>

<?php
endwhile;
get_footer();
