/**
 * Tibb House Core - Gutenberg block editor registrations.
 *
 * Every block is server-rendered (PHP render_callback), so each `edit`
 * function here only needs to provide a lightweight, editable preview
 * plus an InspectorControls panel for the block's attributes. The actual
 * front-end markup always comes from PHP via ServerSideRender-style
 * re-render on save (`save: () => null`).
 *
 * No build step: registered directly against WP global scripts
 * (wp.blocks, wp.element, wp.blockEditor, wp.components, wp.i18n).
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var RichText = blockEditor.RichText;
	var InspectorControls = blockEditor.InspectorControls;
	var MediaUpload = blockEditor.MediaUpload;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var Button = components.Button;
	var NumberControl = components.__experimentalNumberControl || components.TextControl;

	/**
	 * Register a simple text-attribute block with an inspector panel.
	 *
	 * @param {string} name       Block name suffix (after "tibbhouse/").
	 * @param {string} title      Human readable block title.
	 * @param {Array}  fields     [{ key, label, type: 'text'|'textarea'|'number' }]
	 * @param {string} icon       Dashicon name.
	 */
	function registerSimpleBlock( name, title, fields, icon ) {
		blocks.registerBlockType( 'tibbhouse/' + name, {
			title: title,
			icon: icon,
			category: 'widgets',
			edit: function ( props ) {
				var attributes = props.attributes;
				var setAttributes = props.setAttributes;

				var controls = fields.map( function ( field ) {
					var Control = field.type === 'textarea' ? TextareaControl : TextControl;
					return el( Control, {
						key: field.key,
						label: field.label,
						value: attributes[ field.key ] || '',
						onChange: function ( value ) {
							var update = {};
							update[ field.key ] = value;
							setAttributes( update );
						},
					} );
				} );

				return el(
					'div',
					{ className: 'tibbhouse-block-editor-preview' },
					el( 'strong', {}, title ),
					el( 'div', { style: { marginTop: '8px' } }, controls )
				);
			},
			save: function () {
				return null; // Server-rendered via PHP render_callback.
			},
		} );
	}

	registerSimpleBlock(
		'hero',
		__( 'Tibb House: Hero', 'tibbhouse-core' ),
		[
			{ key: 'title', label: __( 'Title', 'tibbhouse-core' ), type: 'text' },
			{ key: 'subtitle', label: __( 'Subtitle', 'tibbhouse-core' ), type: 'text' },
			{ key: 'ctaText', label: __( 'CTA Text', 'tibbhouse-core' ), type: 'text' },
			{ key: 'ctaLink', label: __( 'CTA Link', 'tibbhouse-core' ), type: 'text' },
		],
		'cover-image'
	);

	registerSimpleBlock(
		'cta',
		__( 'Tibb House: CTA', 'tibbhouse-core' ),
		[
			{ key: 'text', label: __( 'Button Text', 'tibbhouse-core' ), type: 'text' },
			{ key: 'link', label: __( 'Button Link', 'tibbhouse-core' ), type: 'text' },
		],
		'megaphone'
	);

	registerSimpleBlock(
		'booking-form',
		__( 'Tibb House: Booking Form', 'tibbhouse-core' ),
		[ { key: 'formLink', label: __( 'Booking Embed URL', 'tibbhouse-core' ), type: 'text' } ],
		'calendar-alt'
	);

	registerSimpleBlock(
		'three-layer',
		__( 'Tibb House: Three Layer', 'tibbhouse-core' ),
		[
			{ key: 'layerOne', label: __( 'Layer One', 'tibbhouse-core' ), type: 'textarea' },
			{ key: 'layerTwo', label: __( 'Layer Two', 'tibbhouse-core' ), type: 'textarea' },
			{ key: 'layerThree', label: __( 'Layer Three', 'tibbhouse-core' ), type: 'textarea' },
		],
		'layout'
	);

	registerSimpleBlock(
		'disclaimer',
		__( 'Tibb House: Disclaimer', 'tibbhouse-core' ),
		[ { key: 'text', label: __( 'Disclaimer Text', 'tibbhouse-core' ), type: 'textarea' } ],
		'info-outline'
	);

	registerSimpleBlock(
		'card-grid',
		__( 'Tibb House: Card Grid', 'tibbhouse-core' ),
		[
			{ key: 'postType', label: __( 'Post Type', 'tibbhouse-core' ), type: 'text' },
			{ key: 'count', label: __( 'Number of Items', 'tibbhouse-core' ), type: 'text' },
			{ key: 'taxonomy', label: __( 'Filter Taxonomy (optional)', 'tibbhouse-core' ), type: 'text' },
			{ key: 'termId', label: __( 'Filter Term ID (optional)', 'tibbhouse-core' ), type: 'text' },
		],
		'grid-view'
	);

	// Related Content has no editable attributes - it's fully automatic.
	blocks.registerBlockType( 'tibbhouse/related-content', {
		title: __( 'Tibb House: Related Content', 'tibbhouse-core' ),
		icon: 'admin-links',
		category: 'widgets',
		edit: function () {
			return el(
				'div',
				{ className: 'tibbhouse-block-editor-preview' },
				el( 'strong', {}, __( 'Related Content', 'tibbhouse-core' ) ),
				el( 'p', {}, __( 'Automatically resolved from this post’s relationships on the front end.', 'tibbhouse-core' ) )
			);
		},
		save: function () {
			return null;
		},
	} );

	/**
	 * Repeater-based blocks (FAQ, Testimonials) use a simple textarea that
	 * accepts one entry per line in "Label | Value" format for a fast,
	 * dependency-free authoring UX inside the block editor.
	 */
	function registerRepeaterBlock( name, title, rowLabel ) {
		blocks.registerBlockType( 'tibbhouse/' + name, {
			title: title,
			icon: 'editor-ul',
			category: 'widgets',
			edit: function ( props ) {
				var attributes = props.attributes;
				var setAttributes = props.setAttributes;
				var items = attributes.items || [];

				var raw = items
					.map( function ( item ) {
						return ( item.label || '' ) + ' | ' + ( item.value || '' );
					} )
					.join( '\n' );

				return el(
					'div',
					{ className: 'tibbhouse-block-editor-preview' },
					el( 'strong', {}, title ),
					el( TextareaControl, {
						help: rowLabel + __( ' - one per line, format: Label | Value', 'tibbhouse-core' ),
						value: raw,
						rows: 6,
						onChange: function ( value ) {
							var parsed = value.split( '\n' ).map( function ( line ) {
								var parts = line.split( '|' );
								return {
									label: ( parts[ 0 ] || '' ).trim(),
									value: ( parts.slice( 1 ).join( '|' ) || '' ).trim(),
								};
							} );
							setAttributes( { items: parsed } );
						},
					} )
				);
			},
			save: function () {
				return null;
			},
		} );
	}

	registerRepeaterBlock( 'faq', __( 'Tibb House: FAQ', 'tibbhouse-core' ), __( 'Question | Answer', 'tibbhouse-core' ) );
	registerRepeaterBlock( 'testimonials', __( 'Tibb House: Testimonials', 'tibbhouse-core' ), __( 'Author | Quote', 'tibbhouse-core' ) );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
