<?php
/**
 * Footer template.
 *
 * @package Tibbhouse
 */
?>

<!-- ── Footer ── -->
<footer id="tibbhouse-footer" role="contentinfo">
	<div class="th-footer-inner">

		<div class="th-footer-grid">

			<!-- Brand column -->
			<div class="th-footer-brand">
				<?php echo tibbhouse_default_logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<p>
					<?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Connecting people with trusted natural and Islamic medicine practitioners, treatments and knowledge.', 'tibbhouse' ) ); ?>
				</p>
				<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
					<?php dynamic_sidebar( 'footer-1' ); ?>
				<?php endif; ?>
			</div>

			<!-- Navigation column -->
			<div class="th-footer-col">
				<h4><?php esc_html_e( 'Explore', 'tibbhouse' ); ?></h4>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'menu_class'     => 'th-footer-nav',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => 'tibbhouse_footer_nav_fallback',
				) );
				?>
			</div>

			<!-- Widget column -->
			<div class="th-footer-col">
				<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
					<?php dynamic_sidebar( 'footer-2' ); ?>
				<?php else : ?>
				<h4><?php esc_html_e( 'Content', 'tibbhouse' ); ?></h4>
				<ul>
					<?php if ( post_type_exists( 'treatments' ) ) : ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'treatments' ) ); ?>"><?php esc_html_e( 'Treatments', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
					<?php if ( post_type_exists( 'conditions' ) ) : ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'conditions' ) ); ?>"><?php esc_html_e( 'Conditions', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
					<?php if ( post_type_exists( 'knowledge' ) ) : ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'knowledge' ) ); ?>"><?php esc_html_e( 'Knowledge', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
					<?php if ( post_type_exists( 'practitioners' ) ) : ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'practitioners' ) ); ?>"><?php esc_html_e( 'Practitioners', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
					<?php if ( post_type_exists( 'locations' ) ) : ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'locations' ) ); ?>"><?php esc_html_e( 'Locations', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
				</ul>
				<?php endif; ?>
			</div>

		</div><!-- /.th-footer-grid -->

		<!-- Bottom bar -->
		<div class="th-footer-bottom">
			<span>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>.
				<?php esc_html_e( 'All rights reserved.', 'tibbhouse' ); ?>
			</span>
			<span>
				<?php
				/* translators: %s: WordPress link */
				printf(
					esc_html__( 'Powered by %s', 'tibbhouse' ),
					'<a href="https://wordpress.org" rel="nofollow">WordPress</a>'
				);
				?>
			</span>
		</div>

	</div><!-- /.th-footer-inner -->
</footer>

<?php wp_footer(); ?>
</body>
</html>

<?php
/**
 * Footer nav fallback: reuse the same Tibb House section links as the
 * header fallback (see header.php) instead of listing arbitrary Pages.
 */
function tibbhouse_footer_nav_fallback() {
	echo '<ul class="th-footer-nav">';
	foreach ( tibbhouse_nav_fallback_links() as $link ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $link['url'] ),
			esc_html( $link['label'] )
		);
	}
	echo '</ul>';
}
