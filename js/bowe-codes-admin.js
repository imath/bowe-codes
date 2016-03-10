jQuery(document).ready(function($) {
	$('#available-bowe-codes').on('change', function(){
		requestBoweCodes( $(this).val() );
		return false;
	});

	$("#bowe-codes-compose").on( 'click', 'input:radio', function(){
		var isitAvatar = $(this).attr('name').replace( $('#available-bowe-codes').val()+'-', '');

		if( isitAvatar != 'avatar' )
			return;

		var sizeField = $(this).attr('name').replace( isitAvatar, 'size' );

		if( $(this).attr('checked') && $(this).val() == 0 ) {
			$('[name="'+sizeField+'"]').attr('disabled', 'disabled' );
		} else {
			$('[name="'+sizeField+'"]').attr('disabled', false );
		}
			
	})

	$('.insertShortcode').click( function() {
		var bowecodeID = $('#available-bowe-codes').val();
		var errors = Array();

		if( bowecodeID == 0 ) {
			alert( bowe_codes_vars.error_select );
			return false;
		}

		if( !$('#bowe-codes-compose table.form-table').length )
			return false;

		var bowecode = '[' + bowecodeID;

		$('#bowe-codes-compose table.form-table label').each( function(){
			var field, defaultval, currentval;
			field = defaultval = currentval = false;

			field = $(this).attr('for');
			defaultval = $(this).attr('data-defaultvalue');

			// we need the checked radio box 
			if( $('[name="'+field+'"]').attr('type') == 'radio' )
				currentval = $('[name="'+field+'"]:checked').val() ;
			else
				currentval = $('[name="'+field+'"]').val();

			// first check for required field !
			if( $('[name="'+field+'"]').attr('class') == 'required' && !currentval ) {
				errors.push( $(this).html() +' '+ bowe_codes_vars.error_required );
				return false;
			}

			// now let's build the shortcode by adding attributes if we need to.
			if( currentval != defaultval && $('[name="'+field+'"]').attr('disabled') != 'disabled' )
				bowecode += ' ' + field.replace( bowecodeID + '-', '') + '="'+ currentval +'"';
		});

		bowecode += ']';
		
		if( bowecodeID == 'bc_restrict_gm' )
			bowecode += bowe_codes_vars.restricted + '[/'+ bowecodeID +']';

		if( errors.length > 0 ) {
			message = bowecode = '';

			for( i in errors )
				message += errors[i];

			alert( message );
			return false;
		} else {
			// let's send that back to the editor !
			var win = window.dialogArguments || opener || parent || top;
			win.send_to_editor( bowecode );
		}

	});

	$('.cancelShortcode').click( function() {
			var win = window.dialogArguments || opener || parent || top;
			win.tb_remove();
	});

	function requestBoweCodes( code ) {
		$("#bowe-codes-compose").html( '<p><img src="'+ bowe_codes_vars.loader +'" alt="loading"> '+ bowe_codes_vars.loadertxt +'</p>');

		var data = {
            action: 'bowecodes_get_shortcode_form',
            requested_code: code
        };
        
	    $.post(ajaxurl, data, function(response) {
	      $("#bowe-codes-compose").html(response);
	    });
	}
});