/**
 * Callback function for the 'click' event of the 'Set Footer Image'
 * anchor in its meta box.
 *
 * Displays the media uploader for selecting an image.
 *
 * @since 0.1.0
 */
 function renderCoverUploader( $ ) {
 	'use strict';

 	var cover_file_frame, image_data, json;

 	/**
     * If an instance of cover_file_frame already exists, then we can open it
     * rather than creating a new instance.
     */
     if ( undefined !== cover_file_frame ) {

     	cover_file_frame.open();
     	return;

     }

     /**
     * If we're this far, then an instance does not exist, so we need to
     * create our own.
     *
     * Here, use the wp.media library to define the settings of the Media
     * Uploader. We're opting to use the 'post' frame which is a template
     * defined in WordPress core and are initializing the file frame
     * with the 'insert' state.
     *
     * We're also not allowing the user to select more than one image.
     */
     cover_file_frame = wp.media.frames.cover_file_frame = wp.media({
     	// frame: 'post',
     	// state: 'insert',
          title: 'Add Cover Image',
          button: {
             text: 'Use this image'
           },
     	multiple: false
     });

     /**
     * Setup an event handler for what to do when an image has been
     * selected.
     *
     * Since we're using the 'view' state when initializing
     * the file_frame, we need to make sure that the handler is attached
     * to the insert event.
     */
     cover_file_frame.on( 'select', function() {
     	
     	// Read the JSON data returned from the Media Uploader
     	json = cover_file_frame.state().get('selection').first().toJSON();

     	// See the json object
     	console.log(json);

     	// Make sure we have the URL of an image to display
     	if ( 0 > $.trim( json.url.length ) ) {
     		return;
     	}

     	// Set the properties of the image and display it 
     	$( '#cover-image-container' )
     		.children( 'img' )
     			.attr( 'src', json.url )
     			.attr( 'alt', json.caption )
     			.attr( 'title', json.title )
     				.show()
     		.parent()
     		.removeClass( 'hidden' );

     	// Hide the anchor responsible for allowing the user to select an image
     	$( '#cover-image-container' )
     		.prev()
     		.hide();

     	// Display the anchor for removing the featured image
     	$( '#cover-image-container' )
     		.next()
     		.show();

     	// Store the image's information into the meta data fields
     	$( '#cover-image' ).val( json.id );
     	$( '#cover-image-title' ).val( json.title );
     	$( '#cover-image-alt' ).val( json.title );


     });

     cover_file_frame.open();

 }

/**
 * Callback function for the 'click' event of the 'Remove Footer Image'
 * anchor in its meta box.
 *
 * Resets the meta box by hiding the image and by hiding the 'Remove
 * Footer Image' container.
 *
 * @param    object    $    A reference to the jQuery object
 * @since    0.2.0
 */
 function resetCoverForm( $ ) {
 	'use strict';

 	// Hide the image
 	$( '#cover-image-container' )
 		.children( 'img' )
 		.hide();

 	// Display the previous container
 	$( '#cover-image-container' )
 		.prev()
 		.show();

 	// Add the 'hidden' class back to this acnhor's parent
 	$( '#cover-image-container' )
 		.next()
 		.hide()
 		.addClass( 'hidden' );

 	// Reset the meta data input fields
 	$( '#cover-image-meta' )
 		.children()
 		.val( '' );

 }

/**
 * Checks to see if the input field for the thumbnail source has a value.
 * If so, then the image and the 'Remove featured image' anchor are displayed.
 *
 * Otherwise, the standard anchor is rendered.
 *
 * @param    object    $    A reference to the jQuery object
 * @since    1.0.0
 */
 function renderCoverImage( $ ) {

 	/* If a thumbnail URL has been associated with this image
     * Then we need to display the image and the reset link.
     */
     if ( '' !== $.trim ( $( '#cover-image' ).val() ) ) {

     	$( '#cover-image-container' ).removeClass('hidden');

     	$( '#set-cover-image' )
     		.parent()
     		.hide();

     	$( '#remove-cover-image' )
     		.parent()
     		.removeClass( 'hidden' );
     }
 }

 (function( $ ) {
 	'use strict';

 	$(function() {

 		renderCoverImage( $ );
 		
 		$( '#set-cover-image' ).on( 'click', function( evt ) {

 			// stop the anchor's default behavior
 			evt.preventDefault();

 			// Display the media uploader
 			renderCoverUploader( $ );

 		});

 		$( '#remove-cover-image' ).on( 'click', function( evt ) {

 			// Stop the anchor's default behavior
 			evt.preventDefault();

 			// Remove the image, toggle the anchors
 			resetCoverForm( $ );

 		});
 	});

 })( jQuery );