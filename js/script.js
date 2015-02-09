var $issuem_admin = jQuery.noConflict();

$issuem_admin(document).ready(function($) {

	// process notice links clicks, eg. dismiss, reminder
	$('.notice-link').click(function (e) {
		console.log('hi');
		e.preventDefault();
		$(this).closest('.notice').hide();
		// $.ajax({
		// 	url     : ajaxurl,
		// 	type    : 'POST',
		// 	dataType: 'text',
		// 	cache   : false,
		// 	data    : {
		// 		action  : 'wpmdb_process_notice_link',
		// 		nonce   : wpmdb_nonces.process_notice_link,
		// 		notice  : $(this).data('notice'),
		// 		type    : $(this).data('type'),
		// 		reminder: $(this).data('reminder')
		// 	}
		// });
	});

});