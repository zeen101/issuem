var $enctype_hack = jQuery.noConflict();

$enctype_hack(document).ready(function($) {
	
	$( '#edittag, #addtag' ).attr( 'enctype','multipart/form-data' );
	$( '#edittag, #addtag' ).attr( 'encoding', 'multipart/form-data' );
	
	$( 'label[for=parent]' ).css( 'display', 'none' );
	$( 'select#parent' ).css( 'display', 'none' );
	
});