/**
 * Tibb House Core - admin meta box behaviour.
 *
 * Handles:
 *  - Single-image upload / remove  (.tibbhouse-upload-image / .tibbhouse-remove-image)
 *  - Multi-image gallery picker    (.tibbhouse-gallery-add / .tibbhouse-gallery-remove / .tibbhouse-gallery-clear)
 *  - Add/remove rows for repeater fields (FAQ, Social Links, etc.)
 *
 * No build step; plain jQuery, matches WP admin conventions.
 */
( function ( $ ) {
	'use strict';

	/* ----------------------------------------------------------------
	   Single-image upload
	---------------------------------------------------------------- */
	$( document ).on( 'click', '.tibbhouse-upload-image', function ( e ) {
		e.preventDefault();
		var button   = $( this );
		var targetId = button.data( 'target' );

		var frame = wp.media( {
			title:    'Select Image',
			multiple: false,
			library:  { type: 'image' },
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			$( '#' + targetId ).val( attachment.id );
			$( '#' + targetId + '_preview' ).html(
				'<img src="' + attachment.url + '" style="max-width:200px;height:auto;" />'
			);
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.tibbhouse-remove-image', function ( e ) {
		e.preventDefault();
		var targetId = $( this ).data( 'target' );
		$( '#' + targetId ).val( '' );
		$( '#' + targetId + '_preview' ).html( '' );
	} );

	/* ----------------------------------------------------------------
	   Gallery: Add images
	---------------------------------------------------------------- */
	$( document ).on( 'click', '.tibbhouse-gallery-add', function ( e ) {
		e.preventDefault();
		var metaKey = $( this ).data( 'key' );
		var wrap    = $( '#' + metaKey + '_wrap' );
		var items   = wrap.find( '.tibbhouse-gallery-items' );

		// Collect IDs already in the gallery so we can pre-select them
		var existingIds = [];
		items.find( 'input[type="hidden"]' ).each( function () {
			existingIds.push( parseInt( $( this ).val(), 10 ) );
		} );

		var frame = wp.media( {
			title:    'Select Gallery Images',
			multiple: 'add',
			library:  { type: 'image' },
		} );

		// Pre-select already-chosen images when the frame opens
		frame.on( 'open', function () {
			var selection = frame.state().get( 'selection' );
			existingIds.forEach( function ( id ) {
				var attachment = wp.media.attachment( id );
				attachment.fetch();
				selection.add( attachment );
			} );
		} );

		frame.on( 'select', function () {
			var selection = frame.state().get( 'selection' );

			// Clear existing items and re-render the full selection
			items.empty();

			selection.each( function ( attachment ) {
				var a        = attachment.toJSON();
				var thumbUrl = ( a.sizes && a.sizes.thumbnail ) ? a.sizes.thumbnail.url : a.url;
				var item     = $(
					'<div class="tibbhouse-gallery-item">' +
						'<img src="' + thumbUrl + '" width="80" height="80" />' +
						'<input type="hidden" name="' + metaKey + '[]" value="' + a.id + '">' +
						'<button type="button" class="tibbhouse-gallery-remove" title="Remove">&times;</button>' +
					'</div>'
				);
				items.append( item );
			} );

			// Show "Clear All" if it wasn't already there
			if ( ! wrap.find( '.tibbhouse-gallery-clear' ).length ) {
				var clearBtn = $( '<button type="button" class="button tibbhouse-gallery-clear" data-key="' + metaKey + '"> Clear All</button>' );
				wrap.append( clearBtn );
			}
		} );

		frame.open();
	} );

	/* ----------------------------------------------------------------
	   Gallery: Remove single item
	---------------------------------------------------------------- */
	$( document ).on( 'click', '.tibbhouse-gallery-remove', function ( e ) {
		e.preventDefault();
		$( this ).closest( '.tibbhouse-gallery-item' ).remove();
	} );

	/* ----------------------------------------------------------------
	   Gallery: Clear all
	---------------------------------------------------------------- */
	$( document ).on( 'click', '.tibbhouse-gallery-clear', function ( e ) {
		e.preventDefault();
		var metaKey = $( this ).data( 'key' );
		$( '#' + metaKey + '_wrap' ).find( '.tibbhouse-gallery-items' ).empty();
		$( this ).remove();
	} );

	/* ----------------------------------------------------------------
	   Repeater: Add row
	---------------------------------------------------------------- */
	$( document ).on( 'click', '.tibbhouse-repeater-add', function ( e ) {
		e.preventDefault();
		var key     = $( this ).data( 'key' );
		var wrapper = $( this ).closest( '.tibbhouse-repeater' ).find( '.tibbhouse-repeater-rows' );
		var index   = wrapper.find( '.tibbhouse-repeater-row' ).length;

		var row = $(
			'<div class="tibbhouse-repeater-row" style="display:flex;gap:8px;margin-bottom:6px;">' +
				'<input type="text" placeholder="Question / Label" name="' + key + '[' + index + '][label]" style="flex:1;" />' +
				'<input type="text" placeholder="Answer / Value"   name="' + key + '[' + index + '][value]" style="flex:2;" />' +
				'<button type="button" class="button tibbhouse-repeater-remove">&times;</button>' +
			'</div>'
		);

		wrapper.append( row );
	} );

	/* ----------------------------------------------------------------
	   Repeater: Remove row
	---------------------------------------------------------------- */
	$( document ).on( 'click', '.tibbhouse-repeater-remove', function ( e ) {
		e.preventDefault();
		$( this ).closest( '.tibbhouse-repeater-row' ).remove();
	} );

} )( jQuery );
