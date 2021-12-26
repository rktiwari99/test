(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 */

	/**
	 * Adds a position to each template item via an int.
	 *
	 * @since 1.0.0
	 * @var   int posId Position of template in numerical order
	 */
	function save_template_kit_positions() {
		var posId = 0;
		$( "#template-kit-sortable-container .templates" ).each(
			function () {
				$( this ).find( ".position_id" ).val( posId );
				posId++;
			}
		)
	}

	/**
	 * Inits the jQuery sortable function on our templates
	 */
	$(
		function () {
			var $sortableContainer = $( "#template-kit-sortable-container" )
			if ($sortableContainer.length > 0) {
				save_template_kit_positions();
				$sortableContainer.sortable();
				$sortableContainer.on(
					"sortstop",
					function (event, ui) {
						save_template_kit_positions();
					}
				);
			}
		}
	);

	/**
	 * Adds the WP Media upload to each of our template previews.
	 */
	$( document ).on(
		"click",
		".upload-image",
		function (e) {
			e.preventDefault();
			var $button = $( this );

			// Find out which template the user is uploading a screenshot for.
			// We need this so we know which hidden input box to target on the page.
			var $templateWrapper = $button.parents( '.templates' ).first()
			var templateId       = $templateWrapper.data( 'template-id' );

			// Create the media frame.
			var file_frame = wp.media.frames.file_frame = wp.media(
				{
					title: 'Select or upload image',
					library: {
						type: 'image'
					},
					button: {
						text: 'Select'
					},
					multiple: false
				}
			);

			// Fire callback on select.
			file_frame.on(
				'select',
				function () {
					// We set multiple to false so only get one image from the uploader
					var uploaded_img_meta          = file_frame.state().get( 'selection' ).first().toJSON();
					var thumbnail_url              = '';
					var thumbnailWidth             = parseInt( window.template_kit_export.thumbnail_width );
					var minimumValidThumbnailWidth = 300
					if ( ! uploaded_img_meta['sizes']['tk_preview'] || uploaded_img_meta['sizes']['tk_preview']['width'] < minimumValidThumbnailWidth) {
						// The user is choosing a screenshot image that doesn't have our required thumbnail size.
						// This either means the image is <= thumbnailWidth px wide already, or they've chosen an old image from the library
						// that doesn't have a thumbnail generated.
						if (uploaded_img_meta['sizes']['full'] && uploaded_img_meta['sizes']['full']['width'] <= thumbnailWidth) {
							// The user has uploaded an image that is <= thumbnailWidth px so we can use that as the thumb.
							thumbnail_url = uploaded_img_meta['sizes']['full']['url'];
						} else {
							// The user has chosen an older image from the media library that doesn't yet have a thumbnailWidth px thumb
							// Ask them to re-upload.
							alert( 'Sorry the selected image does not have a correct thumbnail size. Please upload a new screenshot image that is at least ' + thumbnailWidth + ' pixels wide.' );
							return;
						}
					} else {
						// The user has uploaded an image and we've managed to make a thumbnailWidth px wide thumbnail successfully.
						thumbnail_url = uploaded_img_meta['sizes']['tk_preview']['url'];
					}
					// Update the hidden field with the image id.
					$( 'div[data-template-id=' + templateId + ']' ).find( ".tk-preview-image-id" ).val( uploaded_img_meta['id'] )
					// Update the feature image with the new uploaded img.
					$( 'div[data-template-id=' + templateId + '] .screenshot' ).css( 'backgroundImage', 'url( ' + thumbnail_url + ')' );
				}
			);

			// Finally, open the modal
			file_frame.open();
		}
	);

})( jQuery );
