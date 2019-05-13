import Settings from '../../js/settings/settings.js';

function getTables() {
	return '<form id="ctl-options" action="http://test.test/wp-admin/options.php" method="post">' +
		'<h2>ISO9 Table</h2>' +
		'<table class="form-table"><tbody><tr><th scope="row">ISO9 Table</th><td></td></tr></tbody></table>' +
		'<h2>bel Table</h2>' +
		'<table class="form-table"><tbody><tr><th scope="row">bel Table</th><td></td></tr></tbody></table>' +
		'<h2>uk Table</h2>' +
		'<table class="form-table"><tbody><tr><th scope="row">uk Table</th><td></td></tr></tbody></table>' +
		'<h2>bg_BG Table</h2>' +
		'<table class="form-table"><tbody><tr><th scope="row">bg_BG Table</th><td></td></tr></tbody></table>' +
		'<h2>mk_MK Table</h2>' +
		'<table class="form-table"><tbody><tr><th scope="row">mk_MK Table</th><td></td></tr></tbody></table>' +
		'<h2>ka_GE Table</h2>' +
		'<table class="form-table"><tbody><tr><th scope="row">ka_GE Table</th><td></td></tr></tbody></table>' +
		'<h2>kk Table</h2>' +
		'<table class="form-table"><tbody><tr><th scope="row">kk Table</th><td></td></tr></tbody></table>' +
		'</form>';
}

describe( 'Settings', () => {
		test( 'Hide tables', () => {
				document.body.innerHTML = getTables();

				new Settings();

				const tables = [...document.querySelectorAll( '#ctl-options table' )];
				tables.map(
					( table, index ) => {
						expect( table.classList.contains( 'ctl-table' ) ).toBe( true );
						if ( 0 === index ) {
							expect( table.classList.contains( 'active' ) ).toBe( true );
						}
					}
				);
				const tabs = [...document.querySelector( '#ctl-options ul.nav-tab-wrapper' )];
				expect( typeof tabs !== undefined ).toBe( true );

				const headers = [...document.querySelectorAll( '#ctl-options ul.nav-tab-wrapper h2' )];
				headers.map(
					( header, index ) => {
						expect( header.classList.contains( 'nav-tab' ) ).toBe( true );
						expect( parseInt( header.dataset.index ) ).toBe( index );
						if ( 0 === index ) {
							expect( header.classList.contains( 'nav-tab-active' ) ).toBe( true );
						}
					}
				);
			}
		);

		test( 'Bind events', () => {
				document.body.innerHTML = getTables();

				new Settings();

				const headers = [...document.querySelectorAll( '#ctl-options ul h2' )];

				headers[2].click();
				headers.map(
					( header, index ) => {
						if ( 2 === index ) {
							expect( header.classList.contains( 'nav-tab-active' ) ).toBe( true );
						} else {
							expect( header.classList.contains( 'nav-tab-active' ) ).toBe( false );
						}
					}
				);

				const tables = [...document.querySelectorAll( '.ctl-table' )];
				tables.map(
					( table, index ) => {
						if ( 2 === index ) {
							expect( table.classList.contains( 'active' ) ).toBe( true );
						} else {
							expect( table.classList.contains( 'active' ) ).toBe( false );
						}
					}
				);
			}
		);
	}
);
