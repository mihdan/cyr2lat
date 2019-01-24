/**
 * @file class Settings.
 */

class Settings {
	constructor() {
		this.hideTables();
		this.bindEvents();
	}

	/**
	 * Hide conversion tables except the first one.
	 * Create navigation tabs.
	 */
	hideTables() {
		const tables = [...document.querySelectorAll( '#ctl-options table' )];
		tables.shift();
		tables.map(
			( table, index ) => {
				table.classList.add( 'ctl-table' );
				if ( 0 === index ) {
					table.classList.add( 'active' );
					const tabs = document.createElement( 'ul' );
					tabs.classList.add( 'nav-tab-wrapper' );
					table.parentNode.insertBefore( tabs, table );
				}
			}
		);
		const headers = [...document.querySelectorAll( '#ctl-options h2' )];
		headers.shift();
		headers.map(
			( header, index ) => {
				header.classList.add( 'nav-tab' );
				header.dataset.index = index;

				const tabs = document.querySelector( '#ctl-options ul' );
				tabs.appendChild( header );
				if ( 0 === index ) {
					header.classList.add( 'nav-tab-active' );
				}
			}
		);
	}

	/**
	 * Bind events to methods.
	 */
	bindEvents() {
		const headers = [...document.querySelectorAll( '#ctl-options ul h2' )];

		headers.map(
			( header ) => {
				header.onclick = function( event ) {
					event.preventDefault();
					const index = event.target.dataset.index;

					const headers = [...document.querySelectorAll( '#ctl-options ul h2' )];
					headers.map(
						( header ) => {
							header.classList.remove( 'nav-tab-active' );
						}
					);
					event.target.classList.add( 'nav-tab-active' );

					const tables = [...document.querySelectorAll( '.ctl-table' )];
					tables.map(
						( header ) => {
							header.classList.remove( 'active' );
						}
					);

					tables[index].classList.add( 'active' );

					return false;
				};
			}
		);
	}
}

export default Settings;
