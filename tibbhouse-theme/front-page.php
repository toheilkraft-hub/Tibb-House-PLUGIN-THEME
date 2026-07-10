<?php
/**
 * Front page template — Tibb House homepage.
 *
 * Displays a hero banner, then featured grids for Treatments,
 * Conditions, and Practitioners pulled live from the plugin's CPTs.
 *
 * @package Tibbhouse
 */

get_header();

// Helper: fetch latest N posts of a CPT
function tibbhouse_get_featured( $post_type, $count = 3 ) {
	return get_posts( array(
		'post_type'      => $post_type,
		'posts_per_page' => $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'post_status'    => 'publish',
	) );
}

$treatments    = post_type_exists( 'treatments' )    ? tibbhouse_get_featured( 'treatments',    3 ) : array();
$conditions    = post_type_exists( 'conditions' )    ? tibbhouse_get_featured( 'conditions',    3 ) : array();
$practitioners = post_type_exists( 'practitioners' ) ? tibbhouse_get_featured( 'practitioners', 3 ) : array();
?>

<!-- ── Hero ── -->
<section style="background:linear-gradient(135deg,#0a3d2e 0%,#0f5c43 100%);min-height:88vh;display:flex;align-items:center;position:relative;overflow:hidden;">
	<!-- decorative circles -->
	<div style="position:absolute;top:-100px;right:-100px;width:500px;height:500px;background:rgba(201,168,76,.06);border-radius:50%;pointer-events:none;"></div>
	<div style="position:absolute;bottom:-120px;left:-80px;width:380px;height:380px;background:rgba(201,168,76,.04);border-radius:50%;pointer-events:none;"></div>

	<div style="max-width:1200px;margin:0 auto;padding:80px 5%;position:relative;z-index:2;">
		<div style="display:inline-flex;align-items:center;gap:8px;background:rgba(201,168,76,.15);border:1px solid rgba(201,168,76,.3);color:#f0d890;font-size:.7rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;padding:6px 16px;border-radius:9999px;margin-bottom:28px;">
			<?php esc_html_e( 'Natural &amp; Islamic Medicine', 'tibbhouse' ); ?>
		</div>
		<h1 style="font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(2.5rem,7vw,5rem);font-weight:700;color:#fff;line-height:1.1;margin:0 0 24px;max-width:780px;">
			<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
		</h1>
		<p style="font-size:1.1rem;color:rgba(255,255,255,.7);max-width:560px;line-height:1.75;margin:0 0 40px;">
			<?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Explore trusted treatments, understand conditions, and connect with qualified practitioners in natural and Islamic medicine.', 'tibbhouse' ) ); ?>
		</p>
		<div style="display:flex;flex-wrap:wrap;gap:14px;">
			<?php if ( $treatments ) : ?>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'treatments' ) ); ?>" style="background:#c9a84c;color:#111;padding:15px 36px;border-radius:9999px;font-weight:700;font-size:.95rem;text-decoration:none;letter-spacing:.03em;">
				<?php esc_html_e( 'Explore Treatments', 'tibbhouse' ); ?>
			</a>
			<?php endif; ?>
			<?php if ( $practitioners ) : ?>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'practitioners' ) ); ?>" style="border:2px solid rgba(255,255,255,.3);color:#fff;padding:14px 36px;border-radius:9999px;font-weight:600;font-size:.95rem;text-decoration:none;">
				<?php esc_html_e( 'Find a Practitioner', 'tibbhouse' ); ?>
			</a>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
// ── Reusable card renderer ──
function tibbhouse_homepage_cards( $posts, $archive_link, $type_label, $single_label ) {
	if ( empty( $posts ) ) return;
	?>
	<section style="padding:80px 0;">
		<div style="max-width:1200px;margin:0 auto;padding:0 5%;">
			<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:44px;flex-wrap:wrap;">
				<div>
					<div style="font-size:.7rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:#c9a84c;margin-bottom:10px;"><?php echo esc_html( $type_label ); ?></div>
					<h2 style="font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(1.8rem,4vw,2.6rem);font-weight:600;color:#0a3d2e;margin:0;"><?php echo esc_html( $type_label ); ?></h2>
				</div>
				<a href="<?php echo esc_url( $archive_link ); ?>" style="font-size:.85rem;font-weight:600;color:#0a3d2e;text-decoration:none;display:flex;align-items:center;gap:6px;white-space:nowrap;">
					<?php esc_html_e( 'View all', 'tibbhouse' ); ?> →
				</a>
			</div>

			<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:28px;">
				<?php foreach ( $posts as $p ) : ?>
				<a href="<?php echo esc_url( get_permalink( $p ) ); ?>" style="background:#fff;border-radius:16px;overflow:hidden;border:1px solid rgba(10,61,46,.1);text-decoration:none;display:flex;flex-direction:column;transition:transform .25s,box-shadow .25s;">
					<div style="height:200px;overflow:hidden;background:#e8f2ed;position:relative;">
						<?php $thumb = get_the_post_thumbnail( $p, 'tibbhouse-card' ); ?>
						<?php if ( $thumb ) : ?>
							<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
						<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
							<svg width="56" height="56" viewBox="0 0 24 24" fill="none" opacity=".25"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm1 14H11v-4h2v4zm0-6H11V8h2v2z" fill="#0a3d2e"/></svg>
						</div>
						<?php endif; ?>
						<span style="position:absolute;top:12px;left:12px;background:#0a3d2e;color:#f0d890;font-size:.65rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;padding:4px 12px;border-radius:9999px;"><?php echo esc_html( $single_label ); ?></span>
					</div>
					<div style="padding:22px;">
						<div style="font-family:'Cormorant Garamond',Georgia,serif;font-size:1.3rem;font-weight:600;color:#111810;line-height:1.25;margin-bottom:8px;">
							<?php echo esc_html( get_the_title( $p ) ); ?>
						</div>
						<?php $excerpt = get_the_excerpt( $p ); if ( $excerpt ) : ?>
						<p style="font-size:.86rem;color:#6b7280;line-height:1.6;margin:0 0 14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?php echo esc_html( $excerpt ); ?></p>
						<?php endif; ?>
						<span style="font-size:.82rem;font-weight:600;color:#0a3d2e;"><?php esc_html_e( 'Read more', 'tibbhouse' ); ?> →</span>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}
?>

<?php if ( $treatments ) tibbhouse_homepage_cards( $treatments, get_post_type_archive_link( 'treatments' ), __( 'Treatments', 'tibbhouse' ), __( 'Treatment', 'tibbhouse' ) ); ?>

<!-- divider band -->
<div style="background:linear-gradient(135deg,#0a3d2e,#0f5c43);padding:60px 5%;text-align:center;">
	<p style="font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(1.4rem,3vw,2rem);color:#f0d890;font-style:italic;max-width:640px;margin:0 auto;">
		&ldquo;<?php esc_html_e( 'In every disease there is a cure — seek and you shall find.', 'tibbhouse' ); ?>&rdquo;
	</p>
</div>

<?php if ( $conditions ) tibbhouse_homepage_cards( $conditions, get_post_type_archive_link( 'conditions' ), __( 'Conditions', 'tibbhouse' ), __( 'Condition', 'tibbhouse' ) ); ?>

<?php if ( $practitioners ) tibbhouse_homepage_cards( $practitioners, get_post_type_archive_link( 'practitioners' ), __( 'Practitioners', 'tibbhouse' ), __( 'Practitioner', 'tibbhouse' ) ); ?>

<?php get_footer(); ?>
