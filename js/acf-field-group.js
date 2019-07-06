/**
 * ACF support.
 *
 * @package cyr-to-lat
 */

( function( $ ) {
		var table   = CyrToLatAcfFieldGroup.table;
		var convert = function( str ) {
			$.each(
				table,
				function( k, v ) {
					var regex = new RegExp( k, 'g' );
					str       = str.replace( regex, v );
				}
			);
			str = str.replace( /[^\w\d-_]/g, '' );
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
				var $this = $( this );
				var str   = '';

				if ( $( this ).is( ':focus' ) ) {
					return false;
				} else {
					str = $this.val();
					str = convert( str );

					if ( str !== $this.val() ) {
						$this.val( str );
					}
				}
			}
		);
	}
)( jQuery );
