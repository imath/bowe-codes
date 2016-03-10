jQuery(document).ready(function($) {
	$( '#widgets-right' ).on( 'change', '.shortcode-selector', function(){
		var shortcode_form = $(this).data( 'subform' );
		var shortcode = $(this).val();
		
		$( '#'+ shortcode_form ).html( '<p><img src="'+ bowe_codes_widgets.loader +'" alt="loading"> '+ bowe_codes_widgets.loadertxt +'</p>');

		var data = {
            action: 'widget_changed_shortcode',
            shortcode_selected: shortcode
        };
        
	    $.post(ajaxurl, data, function( response ) {
	      $( '#'+ shortcode_form ).html( response) ;
	    });
	});
});