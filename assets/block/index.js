/**
 * Term Image block for the block editor. The image is rendered on the server for the term being viewed,
 * so the editor shows a placeholder.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'taxonomy-term-images/term-image', {
		edit: function ( props ) {
			var attributes = props.attributes;
			return el(
				'div',
				blockEditor.useBlockProps(),
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Settings', 'taxonomy-term-images' ) },
						el( components.SelectControl, {
							label: __( 'Image size', 'taxonomy-term-images' ),
							value: attributes.size,
							options: [
								{ label: __( 'Thumbnail', 'taxonomy-term-images' ), value: 'thumbnail' },
								{ label: __( 'Medium', 'taxonomy-term-images' ), value: 'medium' },
								{ label: __( 'Medium large', 'taxonomy-term-images' ), value: 'medium_large' },
								{ label: __( 'Large', 'taxonomy-term-images' ), value: 'large' },
								{ label: __( 'Full size', 'taxonomy-term-images' ), value: 'full' },
							],
							onChange: function ( value ) {
								props.setAttributes( { size: value } );
							},
						} ),
						el( components.ToggleControl, {
							label: __( 'Link to the term archive', 'taxonomy-term-images' ),
							checked: !! attributes.linkToTerm,
							onChange: function ( value ) {
								props.setAttributes( { linkToTerm: value } );
							},
						} )
					)
				),
				el(
					components.Placeholder,
					{
						icon: 'format-image',
						label: __( 'Term Image', 'taxonomy-term-images' ),
						instructions: __( 'Shows the image of the category, tag, or term being viewed on archive pages.', 'taxonomy-term-images' ),
					}
				)
			);
		},
		save: function () {
			return null;
		},
	} );
}( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n ) );
