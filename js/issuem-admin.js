( function( $ )  {

	$(document).ready( function() {
			
		// custom media uploader for settings page
		
		var custom_uploader;
	 
	 
	    $('#upload_image_button').click(function(e) {
	 
	        e.preventDefault();
	 
	        //If the uploader object has already been created, reopen the dialog
	        if (custom_uploader) {
	            custom_uploader.open();
	            return;
	        }
	 
	        //Extend the wp.media object
	        custom_uploader = wp.media.frames.file_frame = wp.media({
	            title: 'Choose Image',
	            button: {
	                text: 'Choose Image'
	            },
	            multiple: false
	        });
	 
	        //When a file is selected, grab the URL and set it as the text field's value
	        custom_uploader.on('select', function() {
	            attachment = custom_uploader.state().get('selection').first().toJSON();
	            $('#default_issue_image').val(attachment.url);
	        });
	 
	        //Open the uploader dialog
	        custom_uploader.open();
	 
	    });


	    $('#issuem-tabs').find('a').click(function() {
			$('#issuem-tabs').find('a').removeClass('nav-tab-active');
			$('.issuemtab').removeClass('active');

			var id = $(this).attr('id').replace('-tab','');
			$('#' + id).addClass('active');
			$(this).addClass('nav-tab-active');
		});

		// init
		var active_tab = window.location.hash.replace('#top#','');

		// default to first tab
		if ( active_tab == '' || active_tab == '#_=_') {
			active_tab = $('.issuemtab').attr('id');
		}

		$('#' + active_tab).addClass('active');
		$('#' + active_tab + '-tab').addClass('nav-tab-active');
		
		$('.color-field').wpColorPicker();
			    
	});

})( jQuery );