/**
 * @file class Tables.
 */

/* global Cyr2LatTablesObject */

/**
 * @param Cyr2LatTablesObject.ajaxUrl
 * @param Cyr2LatTablesObject.action
 * @param Cyr2LatTablesObject.nonce
 */

class Tables {
	/**
	 * Class constructor.
	 */
	constructor() {
		this.OPTIONS_FORM_SELECTOR = '#ctl-options';
		this.HEADER_SELECTOR = this.OPTIONS_FORM_SELECTOR + ' h2';
		this.TABLE_SELECTOR = this.OPTIONS_FORM_SELECTOR + ' table';
		this.SUBMIT_SELECTOR = this.OPTIONS_FORM_SELECTOR + ' #submit';
		this.CURRENT_STUB_ID = 'ctl-current';
		this.CURRENT_NAV_TAB_CLASS = 'nav-tab-current';
		this.ACTIVE_NAV_TAB_CLASS = 'nav-tab-active';
		this.ACTIVE_TABLE_CLASS = 'active';
		this.EDIT_LABEL_ID = 'ctl-edit-label';
		this.EDIT_LABEL_ERROR_CLASS = 'ctl-edit-label-error';
		this.plusButton =
			'<button type="button" aria-haspopup="true" aria-expanded="false" class="components-button block-editor-inserter__toggle has-icon" aria-label="Добавить блок">' +
			'<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" role="img" aria-hidden="true" focusable="false">' +
			'<path d="M10 1c-5 0-9 4-9 9s4 9 9 9 9-4 9-9-4-9-9-9zm0 16c-3.9 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7zm1-11H9v3H6v2h3v3h2v-3h3V9h-3V6zM10 1c-5 0-9 4-9 9s4 9 9 9 9-4 9-9-4-9-9-9zm0 16c-3.9 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7zm1-11H9v3H6v2h3v3h2v-3h3V9h-3V6z">' +
			'</path>' +
			'</svg>' +
			'</button>';
		this.PLUS_CLASS = 'ctl-plus';

		this.optionsForm = document.querySelector( this.OPTIONS_FORM_SELECTOR );
		this.tablesData = this.getTablesData();
		this.submitButton = document.querySelector( this.SUBMIT_SELECTOR );

		this.addWrapper();
		this.addMessageLines();
		this.moveTabs();
		this.addEditLabelInput();
		this.hideTables();
		this.bindEvents();
		this.setSubmitStatus();
	}

	/**
	 * Get headers.
	 *
	 * @return {*[]} Headers.
	 */
	getHeaders() {
		return [ ...document.querySelectorAll( this.HEADER_SELECTOR ) ];
	}

	/**
	 * Get active header.
	 *
	 * @return {HTMLBaseElement} Active header.
	 */
	getActiveHeader() {
		return document.querySelector(
			this.HEADER_SELECTOR + '.' + this.ACTIVE_NAV_TAB_CLASS
		);
	}

	/**
	 * Get active index.
	 *
	 * @return {*} Active index.
	 */
	getActiveIndex() {
		return this.getActiveHeader().dataset.index;
	}

	/**
	 * Get tables.
	 *
	 * @return {*[]} Tables.
	 */
	getTables() {
		return [ ...document.querySelectorAll( this.TABLE_SELECTOR ) ];
	}

	/**
	 * Get active table.
	 *
	 * @return {Element} Active table.
	 */
	getActiveTable() {
		return document.querySelector(
			this.TABLE_SELECTOR + '.' + this.ACTIVE_TABLE_CLASS
		);
	}

	/**
	 * Get inputs.
	 *
	 * @return {*[]} Inputs.
	 */
	getInputs() {
		return [
			...document.querySelectorAll(
				this.OPTIONS_FORM_SELECTOR + ' input'
			),
		];
	}

	/**
	 * Get labels.
	 *
	 * @return {*[]} Labels.
	 */
	getLabels() {
		return [
			...document.querySelectorAll(
				this.OPTIONS_FORM_SELECTOR + ' label'
			),
		];
	}

	/**
	 * Get plus buttons.
	 *
	 * @return {*[]} Plus buttons.
	 */
	getPlusButtons() {
		return [
			...document.querySelectorAll(
				this.OPTIONS_FORM_SELECTOR + ' .' + this.PLUS_CLASS
			),
		];
	}

	/**
	 * Check of active table was changed.
	 *
	 * @return {boolean} If active table was changed.
	 */
	isActiveTableChanged() {
		const activeIndex = this.getActiveIndex();

		return (
			JSON.stringify( this.getActiveTableData() ) !==
			JSON.stringify( this.tablesData[ activeIndex ] )
		);
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

		const activeTable = this.getActiveTable();

		const activeForm = document.createElement( 'form' );
		activeForm.action = this.optionsForm.getAttribute( 'action' );
		activeForm.method = this.optionsForm.method;
		activeForm.appendChild( activeTable.cloneNode( true ) );

		const activeInputs = [ ...activeTable.querySelectorAll( 'input' ) ];
		activeInputs.map( ( input ) => {
			return ( activeForm.querySelector( '#' + input.id ).value =
				input.value );
		} );

		const hiddenInputs = [
			...this.optionsForm.querySelectorAll( 'input[type="hidden"]' ),
		];
		hiddenInputs.map( ( input ) => {
			return activeForm.appendChild( input.cloneNode( true ) );
		} );
		document.body.appendChild( activeForm );

		const params = new URLSearchParams( [ ...new FormData( activeForm ) ] );

		params.append( 'action', Cyr2LatTablesObject.action );
		params.append( 'nonce', Cyr2LatTablesObject.nonce );

		return fetch( Cyr2LatTablesObject.ajaxUrl, {
			method: 'POST',
			body: params,
		} )
			.then( ( response ) => {
				if ( ! response.ok ) {
					throw new Error( response.statusText );
				}

				return response.json();
			} )
			.then( ( json ) => {
				if ( json.success ) {
					this.showMessage(
						this.successMessage,
						json.data
					);

					this.tablesData = this.getTablesData();
				} else {
					this.showMessage(
						this.errorMessage,
						json.data
					);
				}
			} )
			.catch( ( error ) => {
				this.showMessage(
					this.errorMessage,
					error
				);
				return {};
			} )
			.finally( () => {
				activeForm.parentNode.removeChild( activeForm );
				this.setSubmitStatus();
			} );
	}

	/**
	 * Get table data.
	 *
	 * @param {Element} table Table.
	 * @return {{}[]} Table data.
	 */
	getTableData( table ) {
		const inputs = [ ...table.querySelectorAll( 'input' ) ];

		const data = {};
		inputs.forEach( ( input ) => {
			const label = document.querySelector(
				this.OPTIONS_FORM_SELECTOR + ' label[for="' + input.id + '"]'
			);

			data[ label.innerHTML ] = input.value;
		} );

		return data;
	}

	/**
	 * Get data from all tables.
	 *
	 * @return {{}[][]} Data from all tables.
	 */
	getTablesData() {
		return this.getTables().map( ( table ) => {
			return this.getTableData( table );
		} );
	}

	/**
	 * Get active table data.
	 *
	 * @return {{}[]} Active table data.
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
		this.optionsForm.insertBefore(
			this.wrapper,
			this.optionsForm.firstChild
		);
	}

	/**
	 * Add message line.
	 *
	 * @param {string} id Message id.
	 * @return {HTMLDivElement} Message line.
	 */
	addMessageLine( id ) {
		const message = document.createElement( 'div' );
		message.id = id;
		this.optionsForm.insertBefore( message, this.optionsForm.firstChild );

		return message;
	}

	/**
	 * Add success and error message lines.
	 */
	addMessageLines() {
		this.successMessage = this.addMessageLine( 'ctl-success' );
		this.errorMessage = this.addMessageLine( 'ctl-error' );
	}

	/**
	 * Make tabs the first element in the form.
	 */
	moveTabs() {
		const tabs = document.querySelector( '.ctl-settings-tabs' );
		if ( tabs ) {
			this.optionsForm.insertBefore( tabs, this.optionsForm.firstChild );
		}
	}

	/**
	 * Add edit label input.
	 */
	addEditLabelInput() {
		this.editLabelInput = document.createElement( 'input' );
		this.editLabelInput.id = this.EDIT_LABEL_ID;
		this.editLabelInput.style.display = 'none';
		document.body.appendChild( this.editLabelInput );
	}

	/**
	 * Hide edit label input.
	 */
	hideEditLabelInput() {
		this.editLabelInput.style.display = 'none';
		this.editLabelInput.classList.remove( this.EDIT_LABEL_ERROR_CLASS );
		document.body.appendChild( this.editLabelInput );
	}

	/**
	 * Get last cell in active table.
	 *
	 * @return {Element} Last cell.
	 */
	getLastCell() {
		return document.querySelector(
			this.OPTIONS_FORM_SELECTOR +
				' .' +
				this.ACTIVE_TABLE_CLASS +
				' .' +
				this.PLUS_CLASS
		).previousElementSibling;
	}

	/**
	 * Add new cell to the active table.
	 */
	addCell() {
		let lastCell = this.getLastCell();
		lastCell.parentElement.insertBefore(
			lastCell.cloneNode( true ),
			lastCell.nextElementSibling
		);

		lastCell = this.getLastCell();
		const label = lastCell.querySelector( 'label' );
		const input = lastCell.querySelector( 'input' );

		const idArr = input.id.split( '-' );
		const newId = idArr[ 0 ] + '-' + ( parseInt( idArr[ 1 ] ) + 1 );

		label.htmlFor = newId;
		label.innerHTML = '';

		input.id = newId;
		input.value = '';
		input.setAttribute( 'value', '' );
		this.replaceName( input, '' );

		this.bindEvents();

		this.editLabel( label );
	}

	/**
	 * Hide conversion tables except the first one.
	 * Create navigation tabs.
	 */
	hideTables() {
		let currentIndex = 0;

		this.getTables().map( ( table, index ) => {
			table.classList.add( 'ctl-table' );

			if ( this.CURRENT_STUB_ID === table.previousElementSibling.id ) {
				currentIndex = index;
				table.classList.add( this.ACTIVE_TABLE_CLASS );
			}

			const plus = document.createElement( 'div' );
			plus.classList.add( this.PLUS_CLASS );
			plus.innerHTML = this.plusButton;
			table.querySelector( 'td fieldset' ).appendChild( plus );

			return null;
		} );

		this.getHeaders().map( ( header, index ) => {
			header.classList.add( 'nav-tab' );
			header.dataset.index = index.toString();

			this.wrapper.appendChild( header );
			if ( index === currentIndex ) {
				header.classList.add( this.CURRENT_NAV_TAB_CLASS );
				header.classList.add( this.ACTIVE_NAV_TAB_CLASS );
			}

			return null;
		} );
	}

	/**
	 * Bind events to methods.
	 */
	bindEvents() {
		this.getHeaders().map( ( header, i, headers ) => {
			header.onclick = ( event ) => {
				event.preventDefault();

				const index = event.target.dataset.index;
				const activeIndex = this.getActiveIndex();

				if ( index === activeIndex ) {
					return false;
				}

				// noinspection JSIgnoredPromiseFromCall
				this.saveActiveTable();

				headers.map( ( aHeader ) => {
					return aHeader.classList.remove(
						this.ACTIVE_NAV_TAB_CLASS
					);
				} );
				headers[ index ].classList.add( this.ACTIVE_NAV_TAB_CLASS );

				const tables = this.getTables();
				tables.map( ( table ) => {
					return table.classList.remove( this.ACTIVE_TABLE_CLASS );
				} );
				tables[ index ].classList.add( this.ACTIVE_TABLE_CLASS );

				this.setSubmitStatus();

				return false;
			};

			return null;
		} );

		this.getInputs().map( ( input ) => {
			input.oninput = () => {
				this.setSubmitStatus();
			};

			return null;
		} );

		this.getLabels().map( ( label ) => {
			label.onclick = ( event ) => {
				event.preventDefault();
				this.editLabel( event.target );
				return false;
			};

			return null;
		} );

		this.editLabelInput.onblur = () => {
			this.saveLabel();
		};

		this.editLabelInput.onkeyup = ( e ) => {
			if ( 'Escape' === e.key ) {
				this.saveLabel( true );
			}

			if ( 'Enter' === e.key ) {
				this.saveLabel();
			}
		};

		this.getPlusButtons().map( ( plus ) => {
			plus.onclick = ( event ) => {
				event.preventDefault();
				this.addCell( event.target );
				return false;
			};

			return null;
		} );

		this.submitButton.onclick = ( event ) => {
			event.preventDefault();
			// noinspection JSIgnoredPromiseFromCall
			this.saveActiveTable();
			return false;
		};
	}

	/**
	 * Edit label.
	 *
	 * @param {Element} label Label to edit.
	 */
	editLabel( label ) {
		label.parentNode.appendChild( this.editLabelInput );
		this.editLabelInput.value = label.innerHTML;

		this.editLabelInput.classList.remove( this.EDIT_LABEL_ERROR_CLASS );
		this.editLabelInput.style.display = 'block';
		this.editLabelInput.focus();
	}

	/**
	 * Is new value of edited label unique in active table.
	 *
	 * @param {string} newValue New Value from edited label.
	 * @return {*} If new value of edited label is unique in active table.
	 */
	isUniqueLabel( newValue ) {
		return [ ...this.getActiveTable().querySelectorAll( 'label' ) ].reduce(
			( acc, label ) => {
				return acc && label.innerHTML !== newValue;
			},
			true
		);
	}

	/**
	 * Save modified label.
	 *
	 * @param {boolean} cancel Cancel.
	 */
	saveLabel( cancel = false ) {
		if ( 'none' === this.editLabelInput.style.display ) {
			return;
		}

		const newValue = this.editLabelInput.value.trim();

		if ( '' === newValue ) {
			const editedCell = document.getElementById( this.EDIT_LABEL_ID )
				.parentElement;
			this.hideEditLabelInput();
			editedCell.parentNode.removeChild( editedCell );
			this.setSubmitStatus();

			return;
		}

		const label = this.editLabelInput.parentNode.querySelector( 'label' );
		if ( cancel || newValue === label.innerHTML ) {
			this.hideEditLabelInput();

			return;
		}

		if ( ! this.isUniqueLabel( newValue ) ) {
			this.editLabelInput.classList.add( this.EDIT_LABEL_ERROR_CLASS );

			return;
		}

		const input = this.editLabelInput.parentNode.querySelector( 'input' );

		this.hideEditLabelInput();

		label.innerHTML = newValue;
		this.replaceName( input, newValue );

		this.setSubmitStatus();
	}

	/**
	 * Replace input name according to the new label value.
	 *
	 * @param {Element} input    Input
	 * @param {string}  newValue New label value
	 */
	replaceName( input, newValue ) {
		// noinspection JSUnresolvedVariable
		input.name = input.name.replace(
			/(.+\[.+])\[.*]/g,
			'$1[' + newValue + ']'
		);
	}

	/**
	 * Clear message.
	 *
	 * @param {HTMLDivElement} message Message.
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
	 * Show message.
	 *
	 * @param {HTMLDivElement} el      Element.
	 * @param {string}         message Message.
	 */
	showMessage( el, message ) {
		el.innerHTML = message;
		el.classList.add( 'active' );

		this.msgTimer = setTimeout( () => {
			this.clearMessages();
		}, 5000 );
	}
}

export default Tables;
