var $issuem_admin = jQuery.noConflict();

$issuem_admin(document).ready(function($) {


	$('.notice-link').click(function (e) {
		e.preventDefault();
		$(this).closest('.notice').hide();
		$.ajax({
			url     : issuem_ajax.ajaxurl,
			type    : 'POST',
			dataType: 'text',
			cache   : false,
			data    : {
				action  : 'issuem_process_notice_link',
				nonce   : issuem_ajax.noticeNonce,
				notice  : $(this).data('notice'),
				type    : $(this).data('type')
			}
		});
	});

});