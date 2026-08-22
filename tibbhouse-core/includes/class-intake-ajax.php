<?php
/**
 * Tibb House — Medical Intake AJAX Handler
 *
 * Receives the secure intake form submission, validates it, stores a
 * private local record, and sends the record and documents to Medplum.
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

		// Send the record and uploaded documents to Medplum.
		$medplum_result = self::submit_to_medplum( $data, $record_id, self::get_uploaded_files() );
		if ( is_wp_error( $medplum_result ) ) {
			update_post_meta( $record_id, '_th_intake_medplum', 'error' );
			wp_send_json_error( array( 'message' => 'The secure record was received, but could not be sent to Medplum. Please contact the clinic.' ), 502 );
		}

		wp_send_json_success( array(
			'message'        => 'Intake record received securely.',
			'record_id'      => $record_id,
			'medplum_queued' => false,
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

	// ── Medplum FHIR submission ───────────────────────────────────────────────

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
	private static function submit_to_medplum( array $data, int $record_id, array $files ) {
		$base_url   = rtrim( (string) getenv( 'MEDPLUM_BASE_URL' ), '/' );
		$project_id = (string) getenv( 'MEDPLUM_PROJECT_ID' );
		$client_id  = (string) getenv( 'MEDPLUM_CLIENT_ID' );
		$secret     = (string) getenv( 'MEDPLUM_CLIENT_SECRET' );

		if ( ! $base_url || ! $project_id || ! $client_id || ! $secret ) {
			return new WP_Error( 'medplum_config', 'Medplum integration is not configured.' );
		}

		$token_res = wp_remote_post( $base_url . '/oauth2/token', array(
			'timeout' => 30,
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_id'     => $client_id,
				'client_secret' => $secret,
				'scope'         => 'openid',
			),
		) );
		if ( is_wp_error( $token_res ) ) {
			return $token_res;
		}
		$token_body = json_decode( wp_remote_retrieve_body( $token_res ), true );
		$token      = $token_body['access_token'] ?? '';
		if ( ! $token ) {
			return new WP_Error( 'medplum_auth', 'Medplum did not return an access token.' );
		}

		$headers = array(
			'Authorization'     => 'Bearer ' . $token,
			'Content-Type'      => 'application/fhir+json',
			'Accept'             => 'application/fhir+json',
			'x-medplum-project' => $project_id,
		);
		$personal = $data['personal'] ?? array();
		$patient  = array(
			'resourceType' => 'Patient',
			'name'         => array( array(
				'given'  => array( $personal['firstName'] ?? '' ),
				'family' => $personal['lastName'] ?? '',
			) ),
			'telecom'      => array_filter( array(
				array( 'system' => 'email', 'value' => $personal['email'] ?? '' ),
				array( 'system' => 'phone', 'value' => $personal['phone'] ?? '' ),
			), static function ( $item ) {
				return ! empty( $item['value'] );
			} ),
		);
		if ( ! empty( $personal['dob'] ) ) {
			$patient['birthDate'] = $personal['dob'];
		}
		if ( ! empty( $personal['gender'] ) && in_array( strtolower( $personal['gender'] ), array( 'male', 'female', 'other', 'unknown' ), true ) ) {
			$patient['gender'] = strtolower( $personal['gender'] );
		}

		$patient_res = self::fhir_post( $base_url . '/fhir/R4/Patient', $patient, $headers );
		if ( is_wp_error( $patient_res ) ) {
			return $patient_res;
		}
		$patient_id = $patient_res['id'] ?? '';
		if ( ! $patient_id ) {
			return new WP_Error( 'medplum_patient', 'Medplum did not return a patient ID.' );
		}

		$questionnaire = array(
			'resourceType' => 'QuestionnaireResponse',
			'status'       => 'completed',
			'subject'      => array( 'reference' => 'Patient/' . $patient_id ),
			'authored'     => gmdate( 'c' ),
			'item'         => self::questionnaire_items( $data ),
		);
		$questionnaire_res = self::fhir_post( $base_url . '/fhir/R4/QuestionnaireResponse', $questionnaire, $headers );
		if ( is_wp_error( $questionnaire_res ) ) {
			return $questionnaire_res;
		}

		$document_ids = array();
		foreach ( $files as $file ) {
			$binary = self::create_binary( $base_url, $headers, $file, $patient_id );
			if ( is_wp_error( $binary ) ) {
				return $binary;
			}
			$document = array(
				'resourceType' => 'DocumentReference',
				'status'       => 'current',
				'docStatus'    => 'final',
				'subject'      => array( 'reference' => 'Patient/' . $patient_id ),
				'date'         => gmdate( 'c' ),
				'content'      => array( array(
					'attachment' => array(
						'contentType' => $file['type'],
						'title'       => $file['name'],
						'url'         => 'Binary/' . $binary['id'],
					),
				) ),
			);
			$document_res = self::fhir_post( $base_url . '/fhir/R4/DocumentReference', $document, $headers );
			if ( is_wp_error( $document_res ) ) {
				return $document_res;
			}
			$document_ids[] = $document_res['id'] ?? '';
		}

		update_post_meta( $record_id, '_th_intake_medplum', $patient_id );
		return array(
			'patient_id'             => $patient_id,
			'questionnaire_id'       => $questionnaire_res['id'] ?? '',
			'document_reference_ids' => $document_ids,
		);
	}

	private static function fhir_post( string $url, array $resource, array $headers ) {
		$response = wp_remote_post( $url, array(
			'timeout' => 60,
			'headers' => $headers,
			'body'    => wp_json_encode( $resource ),
		) );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) {
			return new WP_Error( 'medplum_fhir', 'Medplum rejected the FHIR resource.', array( 'status' => $code ) );
		}
		return $body;
	}

	private static function create_binary( string $base_url, array $headers, array $file, string $patient_id ) {
		$contents = file_get_contents( $file['tmp_name'] );
		if ( false === $contents ) {
			return new WP_Error( 'intake_file_read', 'Uploaded document could not be read.' );
		}
		return self::fhir_post( $base_url . '/fhir/R4/Binary', array(
			'resourceType'  => 'Binary',
			'contentType'   => $file['type'],
			'securityContext' => array( 'reference' => 'Patient/' . $patient_id ),
			'data'          => base64_encode( $contents ),
		), $headers );
	}

	private static function questionnaire_items( array $data ) : array {
		$items = array();
		$flatten = static function ( $value, string $path ) use ( &$items, &$flatten ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $key => $child ) {
					$flatten( $child, $path . '.' . $key );
				}
				return;
			}
			if ( '' !== (string) $value && null !== $value ) {
				$items[] = array(
					'linkId' => ltrim( $path, '.' ),
					'text'   => ucwords( str_replace( array( '.', '_', '-' ), ' ', ltrim( $path, '.' ) ) ),
					'answer' => array( array( 'valueString' => (string) $value ) ),
				);
			}
		};
		$flatten( $data, '' );
		return $items;
	}

	private static function get_uploaded_files() : array {
		if ( empty( $_FILES['intake_files'] ) || ! isset( $_FILES['intake_files']['name'] ) ) {
			return array();
		}
		$files = array();
		$allowed = array( 'application/pdf', 'image/jpeg', 'image/png', 'image/heic', 'image/tiff', 'application/dicom', 'application/octet-stream' );
		foreach ( (array) $_FILES['intake_files']['name'] as $index => $name ) {
			$error = (int) ( $_FILES['intake_files']['error'][ $index ] ?? UPLOAD_ERR_NO_FILE );
			$size  = (int) ( $_FILES['intake_files']['size'][ $index ] ?? 0 );
			$tmp   = $_FILES['intake_files']['tmp_name'][ $index ] ?? '';
			$type  = sanitize_mime_type( $_FILES['intake_files']['type'][ $index ] ?? 'application/octet-stream' );
			if ( UPLOAD_ERR_NO_FILE === $error ) {
				continue;
			}
			if ( UPLOAD_ERR_OK !== $error || ! $tmp || $size > 20 * 1024 * 1024 || ! in_array( $type, $allowed, true ) ) {
				continue;
			}
			$files[] = array(
				'name'     => sanitize_file_name( $name ),
				'type'     => $type,
				'tmp_name' => $tmp,
				'size'     => $size,
			);
		}
		return $files;
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
