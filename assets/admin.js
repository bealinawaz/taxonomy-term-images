/**
 * Term image picker on the category, tag, and taxonomy term screens.
 */
( function ( $ ) {
	'use strict';

	var strings = window.taxonomyTermImages || {};
	var frame = null;
	var current = null;

	function setImage( field, id, html ) {
		field.find( '.ttimg-field__input' ).val( id ? String( id ) : '' );
		field.find( '.ttimg-field__preview' ).html( html || '' );
		field.find( '.ttimg-field__remove' ).prop( 'hidden', ! id );
	}

	$( document ).on( 'click', '.ttimg-field__select', function ( event ) {
		event.preventDefault();
		current = $( this ).closest( '.ttimg-field' );

		if ( ! frame ) {
			frame = wp.media( {
				title: strings.title,
				button: { text: strings.button },
				library: { type: 'image' },
				multiple: false,
			} );
			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var sizes = attachment.sizes || {};
				var preview = sizes.thumbnail || sizes.medium || sizes.full || { url: attachment.url };
				var img = $( '<img />' ).attr( {
					src: preview.url,
					alt: '',
					width: 150,
				} );
				setImage( current, attachment.id, img );
				current.find( '.ttimg-field__select' ).trigger( 'focus' );
			} );
		}
		frame.open();
	} );

	$( document ).on( 'click', '.ttimg-field__remove', function ( event ) {
		event.preventDefault();
		var field = $( this ).closest( '.ttimg-field' );
		setImage( field, 0, '' );
		field.find( '.ttimg-field__select' ).trigger( 'focus' );
	} );

	// The Add New term form is submitted with Ajax and not reloaded, so clear the field after a successful add.
	$( document ).ajaxComplete( function ( event, xhr, settings ) {
		if ( ! settings || 'string' !== typeof settings.data || -1 === settings.data.indexOf( 'action=add-tag' ) ) {
			return;
		}
		if ( xhr.responseXML && $( xhr.responseXML ).find( 'term_id' ).length ) {
			$( '#addtag .ttimg-field' ).each( function () {
				setImage( $( this ), 0, '' );
			} );
		}
	} );
}( window.jQuery ) );
