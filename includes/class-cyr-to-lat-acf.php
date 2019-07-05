<?php
/**
 * ACF Support.
 *
 * @package cyr-to-lat
 */

/**
 * Class Cyr_To_Lat_ACF
 */
class Cyr_To_Lat_ACF {
	/**
	 * Cyr_To_Lat_ACF constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init class hooks.
	 */
	public function init_hooks() {
		add_action( 'acf/field_group/admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
	}

	/**
	 * Enqueue script in ACF field group page.
	 */
	public function enqueue_script() {
		$table = Cyr_To_Lat_Conversion_Tables::get();
		ob_start();
		?>
		( function( $ ) {
			var table = <?php echo wp_json_encode( $table, JSON_UNESCAPED_UNICODE ); ?>;
			var convert = function( str ) {
				$.each( table, function( k, v ) {
					var regex = new RegExp( k, 'g' );
					str = str.replace( regex, v );
				} );
				str = str.replace( /[^\w\d-_]/g, '' );
				str = str.replace( /_+/g, '_' );
				str = str.replace( /^_?(.*)$/g, '$1' );
				str = str.replace( /^(.*)_$/g, '$1' );

				return str;
			}
			acf.addFilter( 'generate_field_object_name', function( val ) {
				return convert( val );
			} );

			$( document ).on( 'change', '.acf-field .field-name', function() {
				var $this = $( this );

				if ( $(this).is(':focus') ) {
					return false;
				} else {
					var str = $this.val();

					str = convert( str );

					if ( str !== $this.val() ) {
						$this.val( str );
					}
				}
			} );
		} )( jQuery );
		<?php
		$data = ob_get_contents();
		ob_end_clean();
		wp_add_inline_script( 'acf-field-group', $data );
	}
}
