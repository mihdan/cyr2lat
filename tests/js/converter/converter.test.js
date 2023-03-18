// noinspection JSUnresolvedFunction,JSUnresolvedVariable

import Converter from '../../../src/js/converter/converter.js';

function getConverter() {
	return `
<form id="ctl-convert-existing-slugs" action="" method="post">
    <input type="hidden" name="ctl-convert">
    <input type="hidden" id="_wpnonce" name="_wpnonce" value="28715e56d8">
    <input type="hidden" name="_wp_http_referer" value="/wp-admin/options-general.php?page=cyr-to-lat">
    <p class="submit">
        <input type="submit" name="ctl-convert-button" id="ctl-convert-button" class="button"
               value="Convert Existing Slugs">
    </p>
</form>
<div id="ctl-confirm-popup">
    <div id="ctl-confirm-content">
        <p>
            <strong>Important:</strong>
            This operation is irreversible. Please make sure that you have made backup copy of your database.
        </p>
        <p>Are you sure to continue?</p>
        <div id="ctl-confirm-buttons">
            <input type="button" id="ctl-confirm-ok" class="button button-primary" value="OK">
            <button type="button" id="ctl-confirm-cancel" class="button button-secondary">
                Cancel
            </button>
        </div>
    </div>
</div>
`
		.replace( /\n/g, '' )
		.replace( />\s+</g, '><' )
		.trim();
}

describe( 'Converter', () => {
	test( 'Click convert button', () => {
		document.body.innerHTML = getConverter();
		new Converter();

		window.HTMLFormElement.prototype.submit = () => {};
		const convertForm = document.querySelector(
			'#ctl-convert-existing-slugs'
		);
		const convertButton = document.querySelector( '#ctl-convert-button' );
		const confirmPopup = document.querySelector( '#ctl-confirm-popup' );
		const confirmOK = document.querySelector( '#ctl-confirm-ok' );
		const confirmCancel = document.querySelector( '#ctl-confirm-cancel' );
		const spyConvertSubmit = jest.spyOn( convertForm, 'submit' );

		const event = new Event( 'click' );
		confirmPopup.style.display = 'none';

		convertButton.dispatchEvent( event );
		expect( confirmPopup.style.display ).toBe( 'block' );

		confirmPopup.dispatchEvent( event );
		expect( confirmPopup.style.display ).toBe( 'none' );

		convertButton.dispatchEvent( event );
		expect( confirmPopup.style.display ).toBe( 'block' );

		confirmCancel.dispatchEvent( event );
		expect( confirmPopup.style.display ).toBe( 'none' );

		convertButton.dispatchEvent( event );
		expect( confirmPopup.style.display ).toBe( 'block' );

		confirmOK.dispatchEvent( event );
		expect( confirmPopup.style.display ).toBe( 'none' );
		expect( spyConvertSubmit ).toHaveBeenCalledTimes( 1 );
	} );
} );
