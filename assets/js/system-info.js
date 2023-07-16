/* global Cyr2LatSystemInfoObject */

document.addEventListener( 'DOMContentLoaded', function() {
	document.querySelector( '#ctl-system-info-wrap .helper' ).addEventListener(
		'click',
		function() {
			const systemInfoTextArea = document.getElementById( 'ctl-system-info' );

			navigator.clipboard.writeText( systemInfoTextArea.value ).then(
				() => {
					// Clipboard successfully set.
				},
				() => {
					// Clipboard write failed.
				},
			);

			// noinspection JSUnresolvedVariable
			const message = Cyr2LatSystemInfoObject.copiedMsg;

			// eslint-disable-next-line no-alert
			alert( message );
		},
	);
} );
