/* global acf, CyrToLatAcfFieldGroup */

/**
 * ACF support.
 *
 * @param {window.jQuery} $        jQuery.
 * @param {Window}        window   Window.
 * @param {document}      document
 * @package
 */
( function( $, window, document ) {
	'use strict';

	const table = CyrToLatAcfFieldGroup.table;
	const convert = function( str ) {
		$.each(
			table,
			function( k, v ) {
				const regex = new RegExp( k, 'g' );
				str = str.replace( regex, v );
			}
		);
		str = str.replace( /[^\w\d\-_]/g, '' );
		str = str.replace( /_+/g, '_' );
		str = str.replace( /^_?(.*)$/g, '$1' );
		str = str.replace( /^(.*)_$/g, '$1' );

		return str;
	};

	acf.addFilter(
		'generate_field_object_name',
		function( val ) {
			return convert( val );
		}
	);

	$( document ).on(
		'change',
		'.acf-field .field-name',
		function() {
			if ( $( this ).is( ':focus' ) ) {
				return false;
			}

			const $this = $( this );
			let str = $this.val();
			str = convert( str );

			if ( str !== $this.val() ) {
				$this.val( str );
			}
		}
	);
}( window.jQuery, window, document ) );
