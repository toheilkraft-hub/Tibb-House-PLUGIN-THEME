<?php
/**
 * Template Name: Contact Page
 *
 * Beautiful contact page with enquiry form.
 * Handles its own submission via wp_mail().
 *
 * @package Tibbhouse
 */

/* ── Handle form submission ─────────────────────────────────────────────── */
$th_form_sent    = false;
$th_form_error   = '';
$th_form_values  = array( 'first_name' => '', 'email' => '', 'phone' => '', 'message' => '' );
$th_contact_email = 'noreply@tibbhouse.com';
$th_contact_phone = '+44 7770 649765';

if ( isset( $_POST['th_contact_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['th_contact_nonce'] ) ), 'th_contact_form' ) ) {

	$first_name = sanitize_text_field( wp_unslash( $_POST['th_first_name'] ?? '' ) );
	$email      = sanitize_email( wp_unslash( $_POST['th_email'] ?? '' ) );
	$phone      = sanitize_text_field( wp_unslash( $_POST['th_phone'] ?? '' ) );
	$message    = sanitize_textarea_field( wp_unslash( $_POST['th_message'] ?? '' ) );

	/* Preserve values for re-display on error */
	$th_form_values = compact( 'first_name', 'email', 'phone', 'message' );

	if ( empty( $first_name ) ) {
		$th_form_error = __( 'Please enter your first name.', 'tibbhouse' );
	} elseif ( ! is_email( $email ) ) {
		$th_form_error = __( 'Please enter a valid email address.', 'tibbhouse' );
	} elseif ( empty( $message ) ) {
		$th_form_error = __( 'Please add a message before submitting.', 'tibbhouse' );
	} else {
		$to      = $th_contact_email;
		$subject = sprintf( __( 'New enquiry from %s — Tibb House', 'tibbhouse' ), $first_name );
		$body    = sprintf(
			"Name:    %s\nEmail:   %s\nPhone:   %s\n\nMessage:\n%s",
			$first_name,
			$email,
			$phone ?: __( '(not provided)', 'tibbhouse' ),
			$message
		);
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $first_name . ' <' . $email . '>',
		);

		if ( wp_mail( $to, $subject, $body, $headers ) ) {
			$th_form_sent = true;
		} else {
			$th_form_error = __( 'Sorry, your message could not be sent right now. Please try again or email us directly.', 'tibbhouse' );
		}
	}
}

get_header();
?>

<!-- ── Hero ── -->
<div class="tibbhouse-page-hero th-contact-hero">
	<div class="th-contact-hero-inner">
		<span class="th-contact-eyebrow"><?php esc_html_e( 'Get in Touch', 'tibbhouse' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p class="th-contact-hero-sub"><?php esc_html_e( 'A question, a booking, or simply a warm hello — we read every message.', 'tibbhouse' ); ?></p>
	</div>
</div>

<!-- ── Two-column layout ── -->
<div class="tibbhouse-main th-contact-wrap">

	<!-- Left panel: info -->
	<aside class="th-contact-info th-reveal-init" data-reveal="left">

		<div class="th-contact-info-card">

			<div class="th-contact-ornament" aria-hidden="true">
				<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="24" cy="24" r="23" stroke="var(--th-gold)" stroke-width="1.2" stroke-dasharray="4 3"/>
					<path d="M24 8C15.163 8 8 15.163 8 24s7.163 16 16 16 16-7.163 16-16S32.837 8 24 8Zm0 4.8A11.18 11.18 0 0 1 35.2 24 11.18 11.18 0 0 1 24 35.2 11.18 11.18 0 0 1 12.8 24 11.18 11.18 0 0 1 24 12.8Z" fill="var(--th-gold)" fill-opacity=".25"/>
					<circle cx="24" cy="24" r="4" fill="var(--th-gold)" fill-opacity=".6"/>
				</svg>
			</div>

			<h2 class="th-contact-info-heading"><?php esc_html_e( 'How can we help?', 'tibbhouse' ); ?></h2>
			<p class="th-contact-info-lead"><?php esc_html_e( 'Whether you\'d like to learn more about a treatment, book an initial consultation, or discuss a health concern, our team is here for you.', 'tibbhouse' ); ?></p>

			<ul class="th-contact-details">
				<li>
					<span class="th-contact-icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
					</span>
					<div>
						<span class="th-contact-detail-label"><?php esc_html_e( 'Email', 'tibbhouse' ); ?></span>
						<a href="mailto:<?php echo esc_attr( $th_contact_email ); ?>"><?php echo esc_html( $th_contact_email ); ?></a>
					</div>
				</li>
				<li>
					<span class="th-contact-icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.8-1.8a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					</span>
					<div>
						<span class="th-contact-detail-label"><?php esc_html_e( 'Phone', 'tibbhouse' ); ?></span>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $th_contact_phone ) ); ?>"><?php echo esc_html( $th_contact_phone ); ?></a>
					</div>
				</li>
				<li>
					<span class="th-contact-icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
					</span>
					<div>
						<span class="th-contact-detail-label"><?php esc_html_e( 'Clinic', 'tibbhouse' ); ?></span>
						<span><?php esc_html_e( 'In-person &amp; remote consultations available', 'tibbhouse' ); ?></span>
					</div>
				</li>
			</ul>

			<div class="th-contact-promise">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--th-gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
				<?php esc_html_e( 'Your information is kept strictly private.', 'tibbhouse' ); ?>
			</div>

		</div><!-- /.th-contact-info-card -->

	</aside>

	<!-- Right panel: form -->
	<div class="th-contact-form-wrap th-reveal-init" data-reveal="right">

		<?php if ( $th_form_sent ) : ?>

			<!-- Success state -->
			<div class="th-contact-success" role="alert">
				<div class="th-contact-success-icon" aria-hidden="true">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
				</div>
				<h2><?php esc_html_e( 'Message received — thank you!', 'tibbhouse' ); ?></h2>
				<p><?php esc_html_e( 'We\'ll be in touch within one business day. May your day be full of barakah.', 'tibbhouse' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="th-contact-btn">
					<?php esc_html_e( '← Back to Home', 'tibbhouse' ); ?>
				</a>
			</div>

		<?php else : ?>

			<form class="th-contact-form" method="post" action="" novalidate>
				<?php wp_nonce_field( 'th_contact_form', 'th_contact_nonce' ); ?>

				<h2 class="th-contact-form-heading"><?php esc_html_e( 'Send us a message', 'tibbhouse' ); ?></h2>

				<?php if ( $th_form_error ) : ?>
					<div class="th-contact-alert" role="alert">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
						<?php echo esc_html( $th_form_error ); ?>
					</div>
				<?php endif; ?>

				<!-- Row: first name -->
				<div class="th-field">
					<label class="th-label" for="th_first_name">
						<?php esc_html_e( 'First Name', 'tibbhouse' ); ?>
						<span class="th-required" aria-label="required">*</span>
					</label>
					<div class="th-input-wrap">
						<svg class="th-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
						<input
							class="th-input"
							type="text"
							id="th_first_name"
							name="th_first_name"
							value="<?php echo esc_attr( $th_form_values['first_name'] ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Aisha', 'tibbhouse' ); ?>"
							required
							autocomplete="given-name"
						>
					</div>
				</div>

				<!-- Row: email -->
				<div class="th-field">
					<label class="th-label" for="th_email">
						<?php esc_html_e( 'Email Address', 'tibbhouse' ); ?>
						<span class="th-required" aria-label="required">*</span>
					</label>
					<div class="th-input-wrap">
						<svg class="th-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
						<input
							class="th-input"
							type="email"
							id="th_email"
							name="th_email"
							value="<?php echo esc_attr( $th_form_values['email'] ); ?>"
							placeholder="<?php esc_attr_e( 'your@email.com', 'tibbhouse' ); ?>"
							required
							autocomplete="email"
						>
					</div>
				</div>

				<!-- Row: phone (optional) -->
				<div class="th-field">
					<label class="th-label" for="th_phone">
						<?php esc_html_e( 'Phone Number', 'tibbhouse' ); ?>
						<span class="th-optional"><?php esc_html_e( '(optional)', 'tibbhouse' ); ?></span>
					</label>
					<div class="th-input-wrap">
						<svg class="th-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.06 6.06l1.8-1.8a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
						<input
							class="th-input"
							type="tel"
							id="th_phone"
							name="th_phone"
							value="<?php echo esc_attr( $th_form_values['phone'] ); ?>"
							placeholder="<?php esc_attr_e( '+44 7700 000000', 'tibbhouse' ); ?>"
							autocomplete="tel"
						>
					</div>
				</div>

				<!-- Row: message -->
				<div class="th-field th-field-full">
					<label class="th-label" for="th_message">
						<?php esc_html_e( 'Your Message', 'tibbhouse' ); ?>
						<span class="th-required" aria-label="required">*</span>
					</label>
					<textarea
						class="th-textarea"
						id="th_message"
						name="th_message"
						rows="5"
						placeholder="<?php esc_attr_e( 'Tell us a little about what you\'re looking for — a specific treatment, a question about our approach, or anything else on your mind…', 'tibbhouse' ); ?>"
						required
					><?php echo esc_textarea( $th_form_values['message'] ); ?></textarea>
				</div>

				<!-- Submit -->
				<div class="th-field th-field-submit">
					<button type="submit" class="th-contact-btn">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
						<?php esc_html_e( 'Send Message', 'tibbhouse' ); ?>
					</button>
					<p class="th-form-note">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
						<?php esc_html_e( 'Encrypted &amp; private. We never share your details.', 'tibbhouse' ); ?>
					</p>
				</div>

			</form>

		<?php endif; ?>

	</div><!-- /.th-contact-form-wrap -->

</div><!-- /.tibbhouse-main -->

<!-- ── Medical Concerns Ghost-Page Band ── -->
<div class="th-medical-band th-reveal-init" data-reveal="up">
	<div class="th-medical-band-inner">

		<div class="th-medical-band-icon" aria-hidden="true">
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
				<line x1="12" y1="8" x2="12" y2="12"/>
				<line x1="12" y1="16" x2="12.01" y2="16"/>
			</svg>
		</div>

		<div class="th-medical-band-copy">
			<h3><?php esc_html_e( 'Want to share your medical concerns?', 'tibbhouse' ); ?></h3>
			<p><?php esc_html_e( 'For sensitive health information, we provide a secure private space — separate from this form and handled with full confidentiality.', 'tibbhouse' ); ?></p>
		</div>

		<a href="<?php echo esc_url( tibbhouse_hipaa_url() ); ?>"
		   target="_blank"
		   rel="noopener noreferrer"
		   class="th-private-btn"
		   aria-label="<?php esc_attr_e( 'Open secure private medical data form', 'tibbhouse' ); ?>"
		>
			<span class="th-private-btn-shimmer" aria-hidden="true"></span>
			<svg class="th-private-btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
				<path d="M7 11V7a5 5 0 0 1 10 0v4"/>
			</svg>
			<span class="th-private-btn-label"><?php esc_html_e( 'Enter Secure Space', 'tibbhouse' ); ?></span>
			<svg class="th-private-btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
			</svg>
		</a>

	</div>
</div>

<?php get_footer();
