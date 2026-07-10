/**
 * Tibb House Core - admin meta box behaviour.
 *
 * Handles:
 *  - Media library uploads for "image" fields
 *  - Add/remove rows for repeater fields (FAQ, Social Links, etc.)
 *
 * No build step required; plain jQuery, matches WP admin conventions.
 */
( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.tibbhouse-upload-image', function ( e ) {
		e.preventDefault();
		var button = $( this );
		var targetId = button.data( 'target' );

		var frame = wp.media( {
			title: 'Select Image',
			multiple: false,
			library: { type: 'image' },
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

	$( document ).on( 'click', '.tibbhouse-repeater-add', function ( e ) {
		e.preventDefault();
		var key = $( this ).data( 'key' );
		var wrapper = $( this ).closest( '.tibbhouse-repeater' ).find( '.tibbhouse-repeater-rows' );
		var index = wrapper.find( '.tibbhouse-repeater-row' ).length;

		var row = $(
			'<div class="tibbhouse-repeater-row" style="display:flex;gap:8px;margin-bottom:6px;">' +
				'<input type="text" placeholder="Question / Label" name="' + key + '[' + index + '][label]" style="flex:1;" />' +
				'<input type="text" placeholder="Answer / Value" name="' + key + '[' + index + '][value]" style="flex:2;" />' +
				'<button type="button" class="button tibbhouse-repeater-remove">&times;</button>' +
			'</div>'
		);

		wrapper.append( row );
	} );

	$( document ).on( 'click', '.tibbhouse-repeater-remove', function ( e ) {
		e.preventDefault();
		$( this ).closest( '.tibbhouse-repeater-row' ).remove();
	} );
} )( jQuery );
