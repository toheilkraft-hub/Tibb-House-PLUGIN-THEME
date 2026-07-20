<?php
/**
 * Admin thumbnail column for Tibb House CPT list views.
 *
 * Adds a "Featured Image" column to every CPT list table so admins can
 * see and change the thumbnail without opening the full editor.
 * Clicking the thumbnail (or the "Set image" placeholder) opens the
 * WordPress media library and saves the chosen attachment via AJAX —
 * no page reload required.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the featured-image admin column across all Tibb House CPTs.
 */
class Tibbhouse_Admin_Thumbnails {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Admin_Thumbnails|null
	 */
	private static $instance = null;

	/**
	 * Get singleton.
	 *
	 * @return Tibbhouse_Admin_Thumbnails
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress admin.
	 */
	private function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		foreach ( Tibbhouse_Helpers::post_types() as $pt ) {
			add_filter( "manage_{$pt}_posts_columns",       array( $this, 'add_thumb_column' ), 5 );
			add_action( "manage_{$pt}_posts_custom_column", array( $this, 'render_thumb_column' ), 10, 2 );
		}

		// Enqueue JS + inline CSS for the media-picker.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// AJAX handlers (logged-in users only).
		add_action( 'wp_ajax_tibbhouse_set_thumbnail',    array( $this, 'ajax_set_thumbnail' ) );
		add_action( 'wp_ajax_tibbhouse_remove_thumbnail', array( $this, 'ajax_remove_thumbnail' ) );
	}

	/* ------------------------------------------------------------------ */
	/*  Column registration & rendering                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Prepend the thumbnail column before the title column.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_thumb_column( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['th_featured_image'] = esc_html__( 'Image', 'tibbhouse-core' );
			}
			$new[ $key ] = $label;
		}
		// If title wasn't found (unlikely), add it at the beginning.
		if ( ! isset( $new['th_featured_image'] ) ) {
			$new = array( 'th_featured_image' => esc_html__( 'Image', 'tibbhouse-core' ) ) + $columns;
		}
		return $new;
	}

	/**
	 * Render the thumbnail cell.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Post ID.
	 */
	public function render_thumb_column( $column, $post_id ) {
		if ( 'th_featured_image' !== $column ) {
			return;
		}

		$thumb_id  = get_post_thumbnail_id( $post_id );
		$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, array( 72, 72 ) ) : '';
		$nonce     = wp_create_nonce( 'tibbhouse_thumb_' . $post_id );

		echo '<div class="th-admin-thumb-cell" data-post-id="' . esc_attr( $post_id ) . '" data-nonce="' . esc_attr( $nonce ) . '" data-thumb-id="' . esc_attr( $thumb_id ?: '' ) . '" style="cursor:pointer;width:72px;">';

		if ( $thumb_url ) {
			echo '<img src="' . esc_url( $thumb_url ) . '" width="72" height="72" style="object-fit:cover;border-radius:6px;display:block;border:2px solid #e2e8f0;" title="' . esc_attr__( 'Click to change image', 'tibbhouse-core' ) . '">';
			echo '<span class="th-admin-thumb-remove" title="' . esc_attr__( 'Remove image', 'tibbhouse-core' ) . '" style="display:block;text-align:center;font-size:10px;color:#dc2626;cursor:pointer;margin-top:2px;">' . esc_html__( '✕ Remove', 'tibbhouse-core' ) . '</span>';
		} else {
			echo '<div style="width:72px;height:72px;border:2px dashed #cbd5e1;border-radius:6px;display:flex;align-items:center;justify-content:center;background:#f8fafc;" title="' . esc_attr__( 'Click to set image', 'tibbhouse-core' ) . '">';
			echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';
			echo '</div>';
			echo '<span style="display:block;text-align:center;font-size:10px;color:#64748b;margin-top:2px;">' . esc_html__( 'Set image', 'tibbhouse-core' ) . '</span>';
		}

		echo '</div>';
	}

	/* ------------------------------------------------------------------ */
	/*  Admin assets                                                        */
	/* ------------------------------------------------------------------ */

	/**
	 * Enqueue the media library and inline JS/CSS on CPT list screens.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || 'edit' !== $screen->base || ! in_array( $screen->post_type, Tibbhouse_Helpers::post_types(), true ) ) {
			return;
		}

		// WordPress media library (triggers wp_enqueue_media() only once).
		wp_enqueue_media();
		wp_enqueue_style( 'tibbhouse-admin-thumb', TIBBHOUSE_CORE_URL . 'assets/css/admin.css', array(), TIBBHOUSE_CORE_VERSION );

		// Inline JS — media picker + AJAX save.
		$js = <<<'JS'
(function(){
    var frame;

    function openPicker(postId, nonce, cell) {
        if (frame) { frame.off('select'); frame.close(); }
        frame = wp.media({
            title: 'Select Featured Image',
            button: { text: 'Use this image' },
            multiple: false,
            library: { type: 'image' }
        });
        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            jQuery.post(ajaxurl, {
                action:   'tibbhouse_set_thumbnail',
                post_id:  postId,
                thumb_id: att.id,
                nonce:    nonce
            }, function(r){
                if (r.success) {
                    cell.setAttribute('data-thumb-id', att.id);
                    var thumbUrl = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                    cell.innerHTML =
                        '<img src="'+thumbUrl+'" width="72" height="72" style="object-fit:cover;border-radius:6px;display:block;border:2px solid #e2e8f0;">' +
                        '<span class="th-admin-thumb-remove" style="display:block;text-align:center;font-size:10px;color:#dc2626;cursor:pointer;margin-top:2px;">✕ Remove</span>';
                    bindRemove(cell, postId, nonce);
                }
            });
        });
        frame.open();
    }

    function bindRemove(cell, postId, nonce) {
        var rem = cell.querySelector('.th-admin-thumb-remove');
        if (!rem) return;
        rem.addEventListener('click', function(e){
            e.stopPropagation();
            jQuery.post(ajaxurl, {
                action:  'tibbhouse_remove_thumbnail',
                post_id: postId,
                nonce:   nonce
            }, function(r){
                if (r.success) {
                    cell.setAttribute('data-thumb-id', '');
                    cell.innerHTML =
                        '<div style="width:72px;height:72px;border:2px dashed #cbd5e1;border-radius:6px;display:flex;align-items:center;justify-content:center;background:#f8fafc;">'+
                        '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>'+
                        '<span style="display:block;text-align:center;font-size:10px;color:#64748b;margin-top:2px;">Set image</span>';
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.th-admin-thumb-cell').forEach(function(cell){
            var postId = cell.getAttribute('data-post-id');
            var nonce  = cell.getAttribute('data-nonce');
            cell.addEventListener('click', function(e){
                if (e.target.classList.contains('th-admin-thumb-remove')) return;
                openPicker(postId, nonce, cell);
            });
            bindRemove(cell, postId, nonce);
        });
    });
})();
JS;
		wp_add_inline_script( 'jquery', $js );
	}

	/* ------------------------------------------------------------------ */
	/*  AJAX handlers                                                       */
	/* ------------------------------------------------------------------ */

	/**
	 * AJAX: set the featured image for a post.
	 */
	public function ajax_set_thumbnail() {
		$post_id  = absint( $_POST['post_id']  ?? 0 );
		$thumb_id = absint( $_POST['thumb_id'] ?? 0 );
		$nonce    = sanitize_text_field( $_POST['nonce'] ?? '' );

		if ( ! $post_id || ! $thumb_id || ! wp_verify_nonce( $nonce, 'tibbhouse_thumb_' . $post_id ) ) {
			wp_send_json_error( 'Invalid request' );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		set_post_thumbnail( $post_id, $thumb_id );
		wp_send_json_success( array( 'thumb_id' => $thumb_id ) );
	}

	/**
	 * AJAX: remove the featured image from a post.
	 */
	public function ajax_remove_thumbnail() {
		$post_id = absint( $_POST['post_id'] ?? 0 );
		$nonce   = sanitize_text_field( $_POST['nonce'] ?? '' );

		if ( ! $post_id || ! wp_verify_nonce( $nonce, 'tibbhouse_thumb_' . $post_id ) ) {
			wp_send_json_error( 'Invalid request' );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		delete_post_thumbnail( $post_id );
		wp_send_json_success();
	}
}
