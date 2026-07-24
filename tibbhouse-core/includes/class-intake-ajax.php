<?php
/**
 * Tibb House — Medical Intake AJAX Handler
 *
 * Receives the secure intake form submission, validates it, stores a
 * draft record in WordPress, and calls the Medplum stub when credentials
 * are available.
 *
 * Medplum integration: add TIBBHOUSE_MEDPLUM_CLIENT_ID and
 * TIBBHOUSE_MEDPLUM_CLIENT_SECRET to wp-config.php (or as env vars)
 * and implement tibbhouse_medplum_submit() below.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tibbhouse_Intake_Ajax {

	public static function register() {
		// Both logged-in and guest users can submit intake forms.
		add_action( 'wp_ajax_th_intake_submit',        array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_nopriv_th_intake_submit', array( __CLASS__, 'handle' ) );

		// Nonce endpoint — returns a fresh nonce for the standalone page.
		add_action( 'wp_ajax_th_intake_nonce',        array( __CLASS__, 'nonce' ) );
		add_action( 'wp_ajax_nopriv_th_intake_nonce', array( __CLASS__, 'nonce' ) );
	}

	// ── Nonce endpoint ────────────────────────────────────────────────────────

	public static function nonce() {
		wp_send_json_success( array( 'nonce' => wp_create_nonce( 'th_intake_submit' ) ) );
	}

	// ── Main submission handler ───────────────────────────────────────────────

	public static function handle() {
		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'th_intake_submit' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ), 403 );
		}

		// Decode payload.
		$raw = isset( $_POST['payload'] ) ? wp_unslash( $_POST['payload'] ) : '';
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			wp_send_json_error( array( 'message' => 'Invalid payload.' ), 400 );
		}

		// Sanitise top-level string values recursively.
		$data = self::sanitise_recursive( $data );

		// Basic required field check.
		$first = $data['personal']['firstName'] ?? '';
		$last  = $data['personal']['lastName']  ?? '';
		$email = $data['personal']['email']     ?? '';
		if ( empty( $first ) || empty( $last ) || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'Required personal information is missing.' ), 422 );
		}

		// Store as a draft intake post (custom post type or post meta).
		$record_id = self::store_intake( $data );

		// Attempt Medplum submission if credentials are configured.
		$medplum_result = null;
		if ( defined( 'TIBBHOUSE_MEDPLUM_CLIENT_ID' ) && TIBBHOUSE_MEDPLUM_CLIENT_ID ) {
			$medplum_result = self::submit_to_medplum( $data, $record_id );
		}

		wp_send_json_success( array(
			'message'        => 'Intake record received securely.',
			'record_id'      => $record_id,
			'medplum_queued' => is_null( $medplum_result ),
			'medplum_result' => $medplum_result,
		) );
	}

	// ── Store locally ─────────────────────────────────────────────────────────

	private static function store_intake( array $data ) : int {
		$title = sprintf(
			'Intake: %s %s — %s',
			$data['personal']['firstName'] ?? '',
			$data['personal']['lastName']  ?? '',
			current_time( 'Y-m-d H:i' )
		);

		$post_id = wp_insert_post( array(
			'post_type'    => 'th_intake',
			'post_title'   => $title,
			'post_status'  => 'private',
			'post_author'  => 1,
			'post_content' => wp_json_encode( $data, JSON_PRETTY_PRINT ),
		) );

		if ( ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_th_intake_data',      $data );
			update_post_meta( $post_id, '_th_intake_submitted', current_time( 'mysql' ) );
			update_post_meta( $post_id, '_th_intake_ip',        self::get_client_ip() );
			update_post_meta( $post_id, '_th_intake_medplum',   'pending' );
		}

		return is_wp_error( $post_id ) ? 0 : $post_id;
	}

	// ── Medplum FHIR submission stub ──────────────────────────────────────────

	/**
	 * Submit a FHIR Patient resource + related records to Medplum.
	 *
	 * To activate: define TIBBHOUSE_MEDPLUM_CLIENT_ID and
	 * TIBBHOUSE_MEDPLUM_CLIENT_SECRET in wp-config.php, then replace
	 * the stub body below with real API calls.
	 *
	 * @param  array $data      Sanitised intake payload.
	 * @param  int   $record_id Local WP post ID.
	 * @return array|WP_Error
	 */
	private static function submit_to_medplum( array $data, int $record_id ) {
		// ── 1. Obtain access token ────────────────────────────────────────────
		// $token_res = wp_remote_post( 'https://api.medplum.com/oauth2/token', [
		//     'body' => [
		//         'grant_type'    => 'client_credentials',
		//         'client_id'     => TIBBHOUSE_MEDPLUM_CLIENT_ID,
		//         'client_secret' => TIBBHOUSE_MEDPLUM_CLIENT_SECRET,
		//     ],
		// ] );
		// $token = json_decode( wp_remote_retrieve_body( $token_res ) )->access_token;

		// ── 2. Build FHIR Patient resource ───────────────────────────────────
		// $patient = [
		//     'resourceType' => 'Patient',
		//     'name'         => [[ 'given' => [$data['personal']['firstName']], 'family' => $data['personal']['lastName'] ]],
		//     'birthDate'    => $data['personal']['dob'] ?? null,
		//     'gender'       => strtolower( $data['personal']['gender'] ?? 'unknown' ),
		//     'telecom'      => [
		//         [ 'system' => 'email', 'value' => $data['personal']['email'] ],
		//         [ 'system' => 'phone', 'value' => $data['personal']['phone'] ?? '' ],
		//     ],
		// ];

		// ── 3. POST to Medplum FHIR endpoint ─────────────────────────────────
		// $res = wp_remote_post( 'https://api.medplum.com/fhir/R4/Patient', [
		//     'headers' => [ 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/fhir+json' ],
		//     'body'    => wp_json_encode( $patient ),
		// ] );

		// ── 4. Update local record with Medplum patient ID ───────────────────
		// $patient_id = json_decode( wp_remote_retrieve_body( $res ) )->id ?? null;
		// update_post_meta( $record_id, '_th_intake_medplum', $patient_id ?? 'error' );

		// STUB — remove this return when credentials are added.
		return array( 'status' => 'stub — add TIBBHOUSE_MEDPLUM_CLIENT_ID to activate' );
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	private static function sanitise_recursive( $value ) {
		if ( is_array( $value ) ) {
			return array_map( array( __CLASS__, 'sanitise_recursive' ), $value );
		}
		return is_string( $value ) ? sanitize_text_field( $value ) : $value;
	}

	private static function get_client_ip() : string {
		foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				return sanitize_text_field( explode( ',', wp_unslash( $_SERVER[ $key ] ) )[0] );
			}
		}
		return '';
	}
}
