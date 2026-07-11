<?php
/**
 * Footer template.
 *
 * @package Tibbhouse
 */

$th_contact_email = get_option( 'admin_email' );
$th_about_page    = get_page_by_title( 'TIBB HOUSE – About Us', OBJECT, 'page' );
$th_contact_page  = get_page_by_title( 'TIBB HOUSE – Contact Us', OBJECT, 'page' );
$th_privacy_page  = get_page_by_title( 'Privacy Policy', OBJECT, 'page' );
$th_terms_page    = get_page_by_title( 'Terms & Conditions', OBJECT, 'page' );
?>

<!-- ── Footer ── -->
<footer id="tibbhouse-footer" role="contentinfo">
	<div class="th-footer-inner">

		<div class="th-footer-grid">

			<!-- Brand column -->
			<div class="th-footer-brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<?php echo tibbhouse_default_logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<p>
					<?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Connecting people with trusted natural and Islamic medicine practitioners, treatments and knowledge.', 'tibbhouse' ) ); ?>
				</p>
				<div class="th-footer-social" aria-label="<?php esc_attr_e( 'Social links', 'tibbhouse' ); ?>">
					<a href="#" aria-label="Facebook">f</a>
					<a href="#" aria-label="Instagram">ig</a>
					<a href="#" aria-label="X / Twitter">x</a>
				</div>
			</div>

			<!-- Quick Links column -->
			<div class="th-footer-col">
				<h4><?php esc_html_e( 'Quick Links', 'tibbhouse' ); ?></h4>
				<ul>
					<?php if ( $th_about_page ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $th_about_page ) ); ?>"><?php esc_html_e( 'About Us', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $th_contact_page ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $th_contact_page ) ); ?>"><?php esc_html_e( 'Contact Us', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $th_privacy_page ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $th_privacy_page ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $th_terms_page ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $th_terms_page ) ); ?>"><?php esc_html_e( 'Terms & Conditions', 'tibbhouse' ); ?></a></li>
					<?php endif; ?>
					<li>
						<a href="<?php echo esc_url( $th_contact_page ? get_permalink( $th_contact_page ) : home_url( '/' ) ); ?>" class="th-footer-cta-link">
							<?php esc_html_e( 'Book Appointment', 'tibbhouse' ); ?>
						</a>
					</li>
				</ul>
			</div>

			<!-- Explore column (Tibb House content sections) -->
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

			<!-- Contact column -->
			<div class="th-footer-col">
				<h4><?php esc_html_e( 'Contact', 'tibbhouse' ); ?></h4>
				<ul class="th-footer-contact">
					<?php if ( $th_contact_email ) : ?>
					<li>
						<a href="mailto:<?php echo esc_attr( $th_contact_email ); ?>"><?php echo esc_html( $th_contact_email ); ?></a>
					</li>
					<?php endif; ?>
					<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
						<?php dynamic_sidebar( 'footer-1' ); ?>
					<?php endif; ?>
				</ul>
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
