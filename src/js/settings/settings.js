/**
 * @file class Settings.
 */

class Settings {

	/**
	 * Class contructor.
	 */
	constructor() {
		this.OPTIONS_FORM_SELECTOR = '#ctl-options';
		this.HEADER_SELECTOR       = this.OPTIONS_FORM_SELECTOR + ' h2';
		this.TABLE_SELECTOR        = this.OPTIONS_FORM_SELECTOR + ' table';
		this.SUBMIT_SELECTOR       = this.OPTIONS_FORM_SELECTOR + ' #submit';
		this.CURRENT_STUB_ID       = 'ctl-current';
		this.CURRENT_NAV_TAB_CLASS = 'nav-tab-current';
		this.ACTIVE_NAV_TAB_CLASS  = 'nav-tab-active';
		this.ACTIVE_TABLE_CLASS    = 'active';

		this.optionsForm  = document.querySelector( this.OPTIONS_FORM_SELECTOR );
		this.tablesData   = this.getTablesData();
		this.submitButton = document.querySelector( this.SUBMIT_SELECTOR );

		this.addWrapper();
		this.addMessageLines();
		this.hideTables();
		this.bindEvents();
		this.setSubmitStatus();
	}

	/**
	 * Get headers.
	 *
	 * @returns {*[]}
	 */
	getHeaders() {
		return [...document.querySelectorAll( this.HEADER_SELECTOR )];
	}

	/**
	 * Get active header.
	 *
	 * @returns {Element}
	 */
	getActiveHeader() {
		return document.querySelector( this.HEADER_SELECTOR + '.' + this.ACTIVE_NAV_TAB_CLASS );
	}

	/**
	 * Get active index.
	 *
	 * @returns {*}
	 */
	getActiveIndex() {
		return this.getActiveHeader().dataset.index;
	}

	/**
	 * Get tables.
	 *
	 * @returns {*[]}
	 */
	getTables() {
		return [...document.querySelectorAll( this.TABLE_SELECTOR )];
	}

	/**
	 * Get active table.
	 *
	 * @returns {Element}
	 */
	getActiveTable() {
		return document.querySelector( this.TABLE_SELECTOR + '.' + this.ACTIVE_TABLE_CLASS );
	}

	/**
	 * Get inputs of active table.
	 *
	 * @returns {*[]}
	 */
	getInputs() {
		return [...document.querySelectorAll(
			this.OPTIONS_FORM_SELECTOR + ' input'
		)];
	}

	/**
	 * Check of active table was changed.
	 *
	 * @returns {boolean}
	 */
	isActiveTableChanged() {
		const activeIndex = this.getActiveIndex();

		return JSON.stringify( this.getActiveTableData() ) !== JSON.stringify( this.tablesData[activeIndex] );
	}

	/**
	 * Set status of submit button.
	 */
	setSubmitStatus() {
		this.submitButton.disabled = ! this.isActiveTableChanged();
	}

	/**
	 * Save active table.
	 */
	saveActiveTable() {
		if ( ! this.isActiveTableChanged() ) {
			return;
		}

		const activeTable  = this.getActiveTable();

		const activeForm  = document.createElement( 'form' );
		activeForm.action = this.optionsForm.getAttribute( 'action' );
		activeForm.method = this.optionsForm.method;
		activeForm.appendChild( activeTable.cloneNode( true ) );

		const activeInputs = [...activeTable.querySelectorAll( 'input' )];
		activeInputs.map(
			( input ) => {
				activeForm.querySelector( '#' + input.id ).value = input.value;
			}
		);

		const hiddenInputs = [...this.optionsForm.querySelectorAll( 'input[type="hidden"]' )];
		hiddenInputs.map(
			( input ) => {
				activeForm.appendChild( input.cloneNode( true ) );
			}
		);
		document.body.appendChild( activeForm );

		return fetch(
			this.optionsForm.getAttribute( 'action' ),
			{
				method: activeForm.method,
				body: new URLSearchParams([...new FormData( activeForm ) ])
			}
		)
			.then(
				response => {
					if ( response.ok ) {
						this.showMessage( this.successMessage, 'Options saved.' );
						this.tablesData = this.getTablesData();
					} else {
						this.showMessage( this.errorMessage, 'Error saving options.' );
					}

					return response.json();
				}
			)
			.finally(
				() => {
					activeForm.remove();
					this.setSubmitStatus();
				}
		);
	}

	/**
	 * Get table data.
	 *
	 * @param table Table.
	 * @returns {{}[]}
	 */
	getTableData( table ) {
		const inputs = [...table.querySelectorAll( 'input' )];

		let data = {};
		inputs.forEach(
			( input ) => {
				const label = document.querySelector( this.OPTIONS_FORM_SELECTOR + ' label[for="' + input.id + '"]' );

				data[label.innerText] = input.value;
			}
		);

		return data;
	}

	/**
	 * Get data from all tables.
	 *
	 * @returns {{}[][]}
	 */
	getTablesData() {
		return this.getTables().map(
			( table ) => {
				return this.getTableData( table );
			}
		);
	}

	/**
	 * Get active table data.
	 *
	 * @returns {{}[]}
	 */
	getActiveTableData() {
		return this.getTableData( this.getActiveTable() );
	}

	/**
	 * Add wrapper.
	 */
	addWrapper() {
		this.wrapper = document.createElement( 'ul' );
		this.wrapper.classList.add( 'nav-tab-wrapper' );
		this.optionsForm.prepend( this.wrapper );
	}

	/**
	 * Add message line.
	 *
	 * @param id
	 * @returns {HTMLDivElement}
	 */
	addMessageLine( id ) {
		const message = document.createElement( 'div' );
		message.id = id;
		this.optionsForm.prepend( message );

		return message;
	}

	/**
	 * Add success and error message lines.
	 */
	addMessageLines() {
		this.successMessage = this.addMessageLine( 'ctl-success' );
		this.errorMessage   = this.addMessageLine( 'ctl-error' );
	}

	/**
	 * Hide conversion tables except the first one.
	 * Create navigation tabs.
	 */
	hideTables() {
		let currentIndex = 0;

		this.getTables().map(
			( table, index ) => {
				table.classList.add( 'ctl-table' );
				if ( this.CURRENT_STUB_ID === table.previousSibling.id ) {
					currentIndex = index;
					table.classList.add( this.ACTIVE_TABLE_CLASS );
				}
			}
		);

		this.getHeaders().map(
			( header, index ) => {
				header.classList.add( 'nav-tab' );
				header.dataset.index = index;

				this.wrapper.appendChild( header );
				if ( index === currentIndex ) {
					header.classList.add( this.CURRENT_NAV_TAB_CLASS, this.ACTIVE_NAV_TAB_CLASS );
				}
			}
		);
	}

	/**
	 * Bind events to methods.
	 */
	bindEvents() {
		this.getHeaders().map(
			( header, i, headers ) => {
				header.onclick = ( event ) => {
					event.preventDefault();

					const index = event.target.dataset.index;
					const activeIndex = this.getActiveIndex();

					if ( index === activeIndex ) {
						return false;
					}

					this.saveActiveTable();

					headers.map(
						( header ) => {
							header.classList.remove( this.ACTIVE_NAV_TAB_CLASS );
						}
					);
					headers[index].classList.add( this.ACTIVE_NAV_TAB_CLASS );

					const tables = this.getTables();
					tables.map(
						( table ) => {
							table.classList.remove( this.ACTIVE_TABLE_CLASS );
						}
					);
					tables[index].classList.add( this.ACTIVE_TABLE_CLASS );

					this.setSubmitStatus();

					return false;
				};
			}
		);

		this.getInputs().map(
			( input ) => {
				input.oninput = () => {
					this.setSubmitStatus();
				};
			}
		);

		document.querySelector( this.SUBMIT_SELECTOR ).onclick = ( event ) => {
			event.preventDefault();

			this.saveActiveTable();

			return false;
		};
	}

	/**
	 * Clear message.
	 *
	 * @param message
	 */
	clearMessage( message ) {
		message.innerHTML = '';
		message.classList.remove( 'active' );
	}

	/**
	 * Clear messages.
	 */
	clearMessages() {
		this.clearMessage( this.successMessage );
		this.clearMessage( this.errorMessage );
		clearTimeout( this.msgTimer );
	}

	/**
	 * Show messages.
	 *
	 * @param el Element.
	 * @param message Message.
	 */
	showMessage( el, message ) {
		el.innerHTML  = message;
		el.classList.add( 'active' );

		this.msgTimer = setTimeout(
			() => {
				this.clearMessages();
			},
			5000
		);
	}
}

export default Settings;
