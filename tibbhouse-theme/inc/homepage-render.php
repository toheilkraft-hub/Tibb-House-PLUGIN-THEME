<?php
/**
 * Shared homepage layout renderer.
 *
 * Used by both front-page.php (when the homepage is set to "Your latest
 * posts"/front-page template automatically) and by the auto-generated
 * "TIBB FRONT PAGE REPLIT" Page template, so both entry points render the
 * exact same design pulled live from the Tibb House Core plugin.
 *
 * @package Tibbhouse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch the latest N published posts of a CPT.
 *
 * @param string $post_type Post type slug.
 * @param int    $count     Number of posts to fetch.
 * @return WP_Post[]
 */
function tibbhouse_get_featured( $post_type, $count = 6 ) {
	if ( ! post_type_exists( $post_type ) ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => $count,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
		)
	);
}

/**
 * Render a single card for a post, used inside the homepage carousels.
 *
 * @param WP_Post $p            Post.
 * @param string  $single_label Singular label (card badge).
 */
function tibbhouse_homepage_card( $p, $single_label ) {
	?>
	<a href="<?php echo esc_url( get_permalink( $p ) ); ?>" class="th-reveal-init" style="background:#fff;border-radius:16px;overflow:hidden;border:1px solid rgba(10,61,46,.1);text-decoration:none;display:flex;flex-direction:column;transition:transform .25s,box-shadow .25s;height:100%;">
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
		<div style="padding:22px;display:flex;flex-direction:column;flex:1;">
			<div style="font-family:'Cormorant Garamond',Georgia,serif;font-size:1.3rem;font-weight:600;color:#111810;line-height:1.25;margin-bottom:8px;">
				<?php echo esc_html( get_the_title( $p ) ); ?>
			</div>
			<?php $excerpt = get_the_excerpt( $p ); if ( $excerpt ) : ?>
			<p style="font-size:.86rem;color:#6b7280;line-height:1.6;margin:0 0 14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex:1;"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<span style="font-size:.82rem;font-weight:600;color:#0a3d2e;"><?php esc_html_e( 'Read more', 'tibbhouse' ); ?> &rarr;</span>
		</div>
	</a>
	<?php
}

/**
 * Render a carousel section for a given set of posts.
 *
 * @param WP_Post[] $posts        Posts to render.
 * @param string    $archive_link Archive link for the "View all" CTA.
 * @param string    $type_label   Plural label (section heading).
 * @param string    $single_label Singular label (card badge).
 * @param string    $section_id   Unique id for this carousel's track (for JS scroll buttons).
 */
function tibbhouse_homepage_cards( $posts, $archive_link, $type_label, $single_label, $section_id ) {
	if ( empty( $posts ) ) {
		return;
	}
	?>
	<section style="padding:80px 0;">
		<div style="max-width:1200px;margin:0 auto;padding:0 5%;">
			<div class="th-reveal-init" data-reveal="up" style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:44px;flex-wrap:wrap;">
				<div>
					<div style="font-size:.7rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:#c9a84c;margin-bottom:10px;"><?php echo esc_html( $type_label ); ?></div>
					<h2 style="font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(1.8rem,4vw,2.6rem);font-weight:600;color:#0a3d2e;margin:0;text-align:left;"><?php echo esc_html( $type_label ); ?></h2>
				</div>
				<div style="display:flex;align-items:center;gap:20px;">
					<a href="<?php echo esc_url( $archive_link ); ?>" style="font-size:.85rem;font-weight:600;color:#0a3d2e;text-decoration:none;display:flex;align-items:center;gap:6px;white-space:nowrap;">
						<?php esc_html_e( 'View all', 'tibbhouse' ); ?> &rarr;
					</a>
					<?php if ( count( $posts ) > 2 ) : ?>
					<div class="th-carousel-nav">
						<button type="button" class="th-carousel-btn" data-carousel-prev="<?php echo esc_attr( $section_id ); ?>" aria-label="<?php esc_attr_e( 'Previous', 'tibbhouse' ); ?>">
							<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 3 5 8l5 5"/></svg>
						</button>
						<button type="button" class="th-carousel-btn" data-carousel-next="<?php echo esc_attr( $section_id ); ?>" aria-label="<?php esc_attr_e( 'Next', 'tibbhouse' ); ?>">
							<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 3l5 5-5 5"/></svg>
						</button>
					</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="th-carousel-wrap">
				<div class="th-carousel-track" id="<?php echo esc_attr( $section_id ); ?>">
					<?php foreach ( $posts as $p ) : ?>
						<?php tibbhouse_homepage_card( $p, $single_label ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render the full Tibb House homepage layout: hero, Treatments carousel,
 * quote divider, Conditions carousel, Practitioners carousel, Knowledge
 * carousel, About Us section, and a Contact/CTA band — all pulled live
 * from the Tibb House Core plugin CPTs.
 *
 * Shared by front-page.php and the "TIBB FRONT PAGE REPLIT" page template
 * so the design is identical regardless of which one WordPress uses.
 */
function tibbhouse_render_homepage() {
	$treatments    = tibbhouse_get_featured( 'treatments', 6 );
	$conditions    = tibbhouse_get_featured( 'conditions', 6 );
	$practitioners = tibbhouse_get_featured( 'practitioners', 6 );
	$knowledge     = tibbhouse_get_featured( 'knowledge', 6 );

	$about_page   = get_page_by_title( 'TIBB HOUSE – About Us', OBJECT, 'page' );
	$contact_page = get_page_by_title( 'TIBB HOUSE – Contact Us', OBJECT, 'page' );
	?>

	<!-- ── Hero ── -->
	<section class="th-home-hero" style="background:linear-gradient(160deg,#f7f3ea 0%,#edf4e8 55%,#f5f0e4 100%);min-height:88vh;display:flex;align-items:center;position:relative;overflow:hidden;">
		<!-- Soft gold orb — top right (parallax) -->
		<div data-parallax="0.18" style="position:absolute;top:-180px;right:-180px;width:680px;height:680px;background:radial-gradient(circle,rgba(188,144,79,.12) 0%,transparent 70%);border-radius:50%;pointer-events:none;will-change:transform;"></div>
		<!-- Soft green orb — bottom left (parallax) -->
		<div data-parallax="-0.12" style="position:absolute;bottom:-200px;left:-160px;width:580px;height:580px;background:radial-gradient(circle,rgba(34,58,23,.08) 0%,transparent 70%);border-radius:50%;pointer-events:none;will-change:transform;"></div>
		<!-- Subtle orb — centre right (parallax) -->
		<div data-parallax="0.28" style="position:absolute;top:10%;right:8%;width:320px;height:320px;background:radial-gradient(circle,rgba(188,144,79,.08) 0%,transparent 70%);border-radius:50%;pointer-events:none;will-change:transform;"></div>

		<div style="max-width:1200px;margin:0 auto;padding:80px 5%;position:relative;z-index:2;text-align:left;">
			<div class="th-reveal-init" data-reveal="up" data-delay="0" style="display:inline-flex;align-items:center;gap:8px;background:rgba(188,144,79,.12);border:1px solid rgba(188,144,79,.35);color:#8a6020;font-size:.7rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;padding:6px 16px;border-radius:9999px;margin-bottom:28px;">
				<?php esc_html_e( 'Natural &amp; Islamic Medicine', 'tibbhouse' ); ?>
			</div>
			<h1 class="th-reveal-init" data-reveal="up" data-delay="1" style="font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(2.5rem,7vw,5rem);font-weight:700;color:#0a3d2e;line-height:1.1;margin:0 0 24px;max-width:780px;text-align:left;">
				<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
			</h1>
			<p class="th-reveal-init" data-reveal="up" data-delay="2" style="font-size:1.1rem;color:#3d4a35;max-width:560px;line-height:1.75;margin:0 0 40px;text-align:left;">
				<?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Explore trusted treatments, understand conditions, and connect with qualified practitioners in natural and Islamic medicine.', 'tibbhouse' ) ); ?>
			</p>
			<div class="th-reveal-init" data-reveal="up" data-delay="3" style="display:flex;flex-wrap:wrap;gap:14px;">
				<?php
				$treatments_url    = get_post_type_archive_link( 'treatments' )    ?: home_url( '/' );
				$practitioners_url = get_post_type_archive_link( 'practitioners' ) ?: home_url( '/' );
				?>
				<a href="<?php echo esc_url( $treatments_url ); ?>" style="background:#bc904f;color:#fff;padding:15px 36px;border-radius:9999px;font-weight:700;font-size:.95rem;text-decoration:none;letter-spacing:.03em;box-shadow:0 4px 16px rgba(188,144,79,.35);">
					<?php esc_html_e( 'Explore Treatments', 'tibbhouse' ); ?>
				</a>
				<a href="<?php echo esc_url( $practitioners_url ); ?>" style="border:2px solid rgba(10,61,46,.3);color:#0a3d2e;padding:14px 36px;border-radius:9999px;font-weight:600;font-size:.95rem;text-decoration:none;background:rgba(10,61,46,.05);">
					<?php esc_html_e( 'Find a Practitioner', 'tibbhouse' ); ?>
				</a>
			</div>
		</div>
	</section>

	<?php if ( $treatments ) : ?>
		<?php tibbhouse_homepage_cards( $treatments, get_post_type_archive_link( 'treatments' ), __( 'Treatments', 'tibbhouse' ), __( 'Treatment', 'tibbhouse' ), 'th-carousel-treatments' ); ?>
	<?php endif; ?>

	<!-- divider band -->
	<div class="th-reveal-init" data-reveal="fade" style="background:linear-gradient(135deg,#0a3d2e,#0f5c43);padding:60px 5%;text-align:center;">
		<p class="th-reveal-init" data-reveal="scale" data-delay="1" style="font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(1.4rem,3vw,2rem);color:#f0d890;font-style:italic;max-width:640px;margin:0 auto;">
			&ldquo;<?php esc_html_e( 'In every disease there is a cure — seek and you shall find.', 'tibbhouse' ); ?>&rdquo;
		</p>
	</div>

	<?php if ( $conditions ) : ?>
		<?php tibbhouse_homepage_cards( $conditions, get_post_type_archive_link( 'conditions' ), __( 'Conditions', 'tibbhouse' ), __( 'Condition', 'tibbhouse' ), 'th-carousel-conditions' ); ?>
	<?php endif; ?>

	<?php if ( $knowledge ) : ?>
		<?php tibbhouse_homepage_cards( $knowledge, get_post_type_archive_link( 'knowledge' ), __( 'Knowledge', 'tibbhouse' ), __( 'Article', 'tibbhouse' ), 'th-carousel-knowledge' ); ?>
	<?php endif; ?>

	<?php if ( $practitioners ) : ?>
		<?php tibbhouse_homepage_cards( $practitioners, get_post_type_archive_link( 'practitioners' ), __( 'Practitioners', 'tibbhouse' ), __( 'Practitioner', 'tibbhouse' ), 'th-carousel-practitioners' ); ?>
	<?php endif; ?>

	<!-- ── About Us ── -->
	<section class="th-home-about">
		<div class="th-home-about-inner">
			<div class="th-reveal-init" data-reveal="left">
				<div style="font-size:.7rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:#c9a84c;margin-bottom:10px;"><?php esc_html_e( 'About Us', 'tibbhouse' ); ?></div>
				<h2 style="font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(1.8rem,4vw,2.6rem);font-weight:600;color:#0a3d2e;margin:0 0 20px;text-align:left;">
					<?php esc_html_e( 'Rooted in Tradition, Guided by Care', 'tibbhouse' ); ?>
				</h2>
				<p style="font-size:1rem;color:#6b7280;line-height:1.8;margin:0 0 28px;max-width:520px;">
					<?php esc_html_e( 'Tibb House brings together natural and Islamic medicine — Hijama cupping, herbal remedies, and Prophetic dietary guidance — delivered by qualified practitioners in a calm, welcoming setting.', 'tibbhouse' ); ?>
				</p>
				<a href="<?php echo esc_url( $about_page ? get_permalink( $about_page ) : home_url( '/' ) ); ?>" style="display:inline-flex;align-items:center;gap:8px;background:#0a3d2e;color:#fff;padding:14px 32px;border-radius:9999px;font-weight:600;font-size:.9rem;text-decoration:none;">
					<?php esc_html_e( 'Learn More About Us', 'tibbhouse' ); ?> &rarr;
				</a>
			</div>
			<div class="th-home-about-visual th-reveal-init" data-reveal="right"></div>
		</div>
	</section>

	<!-- ── Contact / CTA ── -->
	<section class="th-home-cta-band">
		<div class="th-home-cta-inner th-reveal-init" data-reveal="up">
			<div>
				<div style="font-size:.7rem;font-weight:700;letter-spacing:.25em;text-transform:uppercase;color:#bc904f;margin-bottom:10px;"><?php esc_html_e( 'Get In Touch', 'tibbhouse' ); ?></div>
				<h2 style="font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(1.8rem,4vw,2.6rem);font-weight:600;color:#223a17;margin:0 0 12px;max-width:520px;text-align:left;">
					<?php esc_html_e( 'Ready to Begin Your Healing Journey?', 'tibbhouse' ); ?>
				</h2>
				<p style="font-size:1rem;color:#6b7280;margin:0;max-width:480px;">
					<?php esc_html_e( 'Book an appointment or reach out with any questions — our team is here to help.', 'tibbhouse' ); ?>
				</p>
			</div>
			<div class="th-reveal-init" data-reveal="up" data-delay="2" style="display:flex;flex-wrap:wrap;gap:14px;">
				<a href="<?php echo esc_url( $contact_page ? get_permalink( $contact_page ) : home_url( '/' ) ); ?>" style="background:#223a17;color:#fff;padding:15px 36px;border-radius:9999px;font-weight:700;font-size:.95rem;text-decoration:none;">
					<?php esc_html_e( 'Book Appointment', 'tibbhouse' ); ?>
				</a>
				<a href="<?php echo esc_url( $contact_page ? get_permalink( $contact_page ) : home_url( '/' ) ); ?>" style="border:2px solid rgba(34,58,23,.3);color:#223a17;padding:14px 36px;border-radius:9999px;font-weight:600;font-size:.95rem;text-decoration:none;">
					<?php esc_html_e( 'Contact Us', 'tibbhouse' ); ?>
				</a>
			</div>
		</div>
	</section>
	<?php
}
