var $issuem_migrate = jQuery.noConflict();

$issuem_migrate(document).ready(function($) {
	
	$( '.checkall' ).click(function () {
		
		$( this ).parents( 'table:eq(0)' ).find( ':checkbox' ).attr( 'checked', this.checked );
	
	});
	
});