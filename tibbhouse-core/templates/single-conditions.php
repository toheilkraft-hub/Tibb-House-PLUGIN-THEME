<?php
/**
 * Single Condition template — Tibb House Design System.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id        = get_the_ID();
	$definition     = get_post_meta( $post_id, 'th_definition', true );
	$symptoms       = get_post_meta( $post_id, 'th_symptoms', true );
	$causes         = get_post_meta( $post_id, 'th_causes', true );
	$risk_factors   = get_post_meta( $post_id, 'th_risk_factors', true );
	$diagnosis      = get_post_meta( $post_id, 'th_diagnosis', true );
	$treatment_options = get_post_meta( $post_id, 'th_treatment_options', true );
	$self_management = get_post_meta( $post_id, 'th_self_management', true );
	$complications   = get_post_meta( $post_id, 'th_complications', true );
	$uncertain       = get_post_meta( $post_id, 'th_uncertain', true );
	$questions_for_clinician = get_post_meta( $post_id, 'th_questions_for_clinician', true );
	$patient_profile= get_post_meta( $post_id, 'th_patient_profile', true );
	$hero           = get_post_meta( $post_id, 'th_hero_image', true );
	$faq            = get_post_meta( $post_id, 'th_faq', true );

	$constitutional_types = get_the_terms( $post_id, 'constitutional_type' );
	$patient_profiles     = get_the_terms( $post_id, 'patient_profile' );
	$remedies             = get_the_terms( $post_id, 'remedies' );
	$vital_areas          = get_the_terms( $post_id, 'vital_area' );

	$has_image = $hero || has_post_thumbnail();
	?>

<article <?php post_class( 'tibbhouse-single tibbhouse-single-condition' ); ?>>

	<!-- ── Hero ── -->
	<div class="tibbhouse-hero-wrap<?php echo $has_image ? '' : ' no-image'; ?>">

		<?php if ( $hero ) : ?>
			<div class="tibbhouse-hero-bg"><?php echo wp_get_attachment_image( (int) $hero, 'full', false, array( 'loading' => 'eager' ) ); ?></div>
		<?php elseif ( has_post_thumbnail() ) : ?>
			<div class="tibbhouse-hero-bg"><?php the_post_thumbnail( 'full', array( 'loading' => 'eager' ) ); ?></div>
		<?php endif; ?>

		<div class="tibbhouse-hero-overlay"></div>

		<div class="tibbhouse-hero-content">
			<nav class="tibbhouse-breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'conditions' ) ); ?>"><?php esc_html_e( 'Conditions', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<span><?php the_title(); ?></span>
			</nav>

			<div class="th-post-type-badge">
				<svg viewBox="0 0 16 16"><path d="M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm-.75 3.5h1.5v4h-1.5v-4zm0 5h1.5V12h-1.5v-1.5z"/></svg>
				<?php esc_html_e( 'Condition', 'tibbhouse-core' ); ?>
			</div>

			<h1><?php the_title(); ?></h1>
		</div>
	</div>

	<!-- ── Body ── -->
	<div class="tibbhouse-inner">

		<!-- Taxonomy Tags -->
		<?php if (
			( $constitutional_types && ! is_wp_error( $constitutional_types ) ) ||
			( $patient_profiles && ! is_wp_error( $patient_profiles ) ) ||
			( $remedies && ! is_wp_error( $remedies ) ) ||
			( $vital_areas && ! is_wp_error( $vital_areas ) )
		) : ?>
		<div class="tibbhouse-section th-reveal">
			<?php if ( $constitutional_types && ! is_wp_error( $constitutional_types ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Constitutional Type', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $constitutional_types as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $patient_profiles && ! is_wp_error( $patient_profiles ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Patient Profile', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $patient_profiles as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag gold"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $vital_areas && ! is_wp_error( $vital_areas ) ) : ?>
			<div class="th-tax-group" style="margin-bottom:10px;">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Vital Areas', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $vital_areas as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( $remedies && ! is_wp_error( $remedies ) ) : ?>
			<div class="th-tax-group">
				<span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--th-muted);margin-right:6px;"><?php esc_html_e( 'Remedies', 'tibbhouse-core' ); ?></span>
				<?php foreach ( $remedies as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="th-tax-tag gold"><?php echo esc_html( $term->name ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<!-- Overview -->
		<?php $content = get_the_content(); if ( $content ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Overview', 'tibbhouse-core' ); ?></div>
			<div class="tibbhouse-section-body"><?php the_content(); ?></div>
		</div>
		<?php endif; ?>

		<!-- Structured Condition Information -->
		<?php
		$structured_sections = array(
			__( 'Definition', 'tibbhouse-core' )                    => $definition,
			__( 'Causes', 'tibbhouse-core' )                       => $causes,
			__( 'Risk Factors', 'tibbhouse-core' )                 => $risk_factors,
			__( 'Symptoms', 'tibbhouse-core' )                     => $symptoms,
			__( 'Diagnosis', 'tibbhouse-core' )                    => $diagnosis,
			__( 'Treatment Options', 'tibbhouse-core' )            => $treatment_options,
			__( 'Self-Management', 'tibbhouse-core' )              => $self_management,
			__( 'Complications', 'tibbhouse-core' )                => $complications,
			__( 'What Is Uncertain', 'tibbhouse-core' )             => $uncertain,
			__( 'Questions to Ask Your Clinician', 'tibbhouse-core' ) => $questions_for_clinician,
		);
		foreach ( $structured_sections as $section_title => $section_content ) :
			if ( ! $section_content ) {
				continue;
			}
			?>
			<section class="tibbhouse-section th-reveal th-condition-structured-section">
				<h2 class="tibbhouse-section-label th-condition-section-title"><?php echo esc_html( $section_title ); ?></h2>
				<div class="tibbhouse-section-body"><?php echo wp_kses_post( wpautop( $section_content ) ); ?></div>
			</section>
		<?php endforeach; ?>

		<!-- Patient Profile (free text) -->
		<?php if ( $patient_profile ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Patient Profile', 'tibbhouse-core' ); ?></div>
			<div class="th-highlight-band"><?php echo wp_kses_post( wpautop( $patient_profile ) ); ?></div>
		</div>
		<?php endif; ?>

		<!-- FAQ -->
		<?php if ( ! empty( $faq ) && is_array( $faq ) ) : ?>
		<div class="tibbhouse-section th-reveal">
			<div class="tibbhouse-section-label"><?php esc_html_e( 'Frequently Asked Questions', 'tibbhouse-core' ); ?></div>
			<div class="tibbhouse-faq-wrap">
				<?php foreach ( $faq as $item ) : ?>
					<?php if ( empty( $item['label'] ) ) { continue; } ?>
					<div class="th-faq-item">
						<button class="th-faq-trigger" type="button">
							<?php echo esc_html( $item['label'] ); ?>
							<span class="th-faq-icon" aria-hidden="true">
								<svg viewBox="0 0 16 16" width="16" height="16" fill="currentColor"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg>
							</span>
						</button>
						<div class="th-faq-body">
							<div class="th-faq-body-inner"><?php echo wp_kses_post( $item['value'] ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

	</div><!-- /.tibbhouse-inner -->

	<!-- Related Content -->
	<?php echo Tibbhouse_Relationships::instance()->render_related_content( $post_id, 'conditions' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

</article>

<?php
endwhile;

get_footer();
