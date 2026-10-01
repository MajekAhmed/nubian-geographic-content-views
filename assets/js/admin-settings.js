/**
 * NGCV admin settings — archive hero image picker.
 *
 * Vanilla JS on top of wp.media (Media Library). Loaded only on the NGCV
 * settings screen. Selecting an image stores its attachment ID in the hidden
 * option field; removing clears it. No validation beyond the ID — the server
 * re-validates on save and on render.
 */
(function () {
	'use strict';

	var input = document.getElementById( 'ngcv_archive_hero_image_id' );
	var preview = document.getElementById( 'ngcv-hero-preview' );
	var selectBtn = document.getElementById( 'ngcv-hero-select' );
	var changeBtn = document.getElementById( 'ngcv-hero-change' );
	var removeBtn = document.getElementById( 'ngcv-hero-remove' );

	if ( ! input || ! preview || ! selectBtn || ! changeBtn || ! removeBtn ) {
		return;
	}

	var frame = null;

	function isSet() {
		return parseInt( input.value, 10 ) > 0;
	}

	function showImage( id, url ) {
		input.value = String( id );
		preview.innerHTML = '';
		if ( url ) {
			var img = document.createElement( 'img' );
			img.src = url;
			img.alt = preview.getAttribute( 'data-alt' ) || '';
			preview.appendChild( img );
		}
		sync();
	}

	function sync() {
		var hasImage = isSet();
		preview.style.display = hasImage ? '' : 'none';
		selectBtn.style.display = hasImage ? 'none' : '';
		changeBtn.style.display = hasImage ? '' : 'none';
		removeBtn.style.display = hasImage ? '' : 'none';
	}

	function openFrame( event ) {
		event.preventDefault();

		if ( ! window.wp || ! window.wp.media ) {
			return;
		}

		if ( ! frame ) {
			frame = window.wp.media( {
				title: selectBtn.getAttribute( 'data-modal-title' ) || '',
				button: { text: selectBtn.getAttribute( 'data-modal-button' ) || '' },
				library: { type: 'image' },
				multiple: false
			} );

			frame.on( 'select', function () {
				var selection = frame.state().get( 'selection' ).first();
				if ( ! selection ) {
					return;
				}
				var att = selection.toJSON();
				var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
				showImage( att.id, url );
			} );
		}

		frame.open();
	}

	function removeImage( event ) {
		event.preventDefault();
		showImage( 0, '' );
	}

	selectBtn.addEventListener( 'click', openFrame );
	changeBtn.addEventListener( 'click', openFrame );
	removeBtn.addEventListener( 'click', removeImage );

	sync();
})();