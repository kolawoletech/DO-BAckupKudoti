jQuery(document).ready(function() {

    /**
     * Show / Hide a add button new fields
	 *
	 * @return void;
     */
    function um_profilec_add_button(){
        var add_btn = jQuery('.profilec-add').closest('p');
        var current_progress = jQuery('#role_um_allocated_progress').val();
        if ( current_progress*1 >= 100 ){
            jQuery(add_btn).css('display','none');
        }else{
            jQuery(add_btn).css('display','');
        }
    }

	/**
	 Add a profile field to completion
	 **/
	jQuery(document).on( 'click', '.profilec-add', function(e) {
		e.preventDefault();
		jQuery(this).parent().hide();
		jQuery('.profilec-field').show();
		jQuery('#progress_field, #progress_value').prop('disabled', false).prop('readonly', false);
	});

	/**
	 Cancel add
	 **/
	jQuery(document).on( 'click', '.profilec-cancel', function(e) {
		e.preventDefault();
		jQuery('.profilec-add').parent().show();
		jQuery('.profilec-field').hide();
		jQuery('#progress_field, #progress_value').prop('disabled', true).prop('readonly', true).val('');
	});


	/**
	 Save a profile field to completion
	 **/
	jQuery(document).on( 'click', '.profilec-save', function(e) {
		e.preventDefault();

		var current_progress = jQuery( '#role_um_allocated_progress' ).val();
		if ( current_progress*1 == 100 )
			return false;

		var progress_value = jQuery( '#progress_value' ).val();
		if(!(/^[0-9]+$/gm.test(progress_value)))
			return false;

		if(progress_value*1 <= 0)
			return false;

		var new_progress = current_progress*1 + progress_value*1;
		if ( new_progress > 100 )
			return false;

		var progress_field = jQuery( '#progress_field' ).val();

        if ( progress_field == '0' )
            return false;

		var profilec_data_keys = [];

        jQuery( '.profilec-data p > .profilec-key' ).each( function() {
            profilec_data_keys.push( jQuery( this ).text() );
		});

        if( jQuery.inArray( progress_field, profilec_data_keys ) !== -1 )
            return false;

        var hidden_data = '<input type="hidden" id="role_um_progress_' + progress_field + '" name="role[_um_progress_' + progress_field + ']" value="' + progress_value + '" />';
		jQuery( '#role_um_allocated_progress' ).after( hidden_data );

		var percent_wrapper = '<p><span class="profilec-key alignleft">' + progress_field + '</span><span class="profilec-progress alignright"><strong><ins>' + progress_value + '</ins>%</strong> <span class="profilec-edit"><i class="um-faicon-pencil"></i></span></span></p><div class="clear"></div>';
		jQuery('.profilec-data').append( percent_wrapper );


		jQuery('#role_um_allocated_progress').val( new_progress );
		jQuery('.profilec-ajax').html( 100 - new_progress );

		jQuery('.profilec-cancel').trigger('click');
        um_profilec_add_button();
		return false;
	});


	/**
		Make inline edit
	**/
	jQuery(document).on('click', '.profilec-edit',function(e){
		e.preventDefault();
		jQuery(this).parents('p').after( jQuery('.profilec-inline') );
		jQuery('.profilec-inline').show();
		jQuery('.profilec-inline').find('#progress_valuei').val( parseInt( jQuery(this).parents('p').find('ins').html() ) );
		jQuery('.profilec-inline').find('#progress_fieldi').val( jQuery(this).parents('p').find('.profilec-key').html() );
        um_profilec_add_button();
		return false;
	});


	/**
		Remove a profile field
	**/
	jQuery(document).on('click', '.profilec-remove',function(e){
		e.preventDefault();
		var cont = jQuery(this).parents('.profilec-inline');
		var progress_value = cont.find('#progress_valuei').val();
		var progress_field = cont.find('#progress_fieldi').val();

		var current_progress = jQuery('#role_um_allocated_progress').val();
		var new_progress = current_progress*1 - progress_value*1;

		jQuery( '#role_um_progress_' + progress_field ).remove();
		cont.prev().remove();
		cont.hide();

		jQuery('#role_um_allocated_progress').val( new_progress );
		jQuery('.profilec-ajax').html( 100 - new_progress );
        um_profilec_add_button();
		return false;
	});


	/**
		Update a profile field to completion
	**/
	jQuery(document).on('click', '.profilec-update',function(e){
		e.preventDefault();

		var cont = jQuery(this).parents('.profilec-inline');
		var progress_field = cont.find('#progress_fieldi').val();
		var current_progress = jQuery('#role_um_allocated_progress').val();

		var prev_progress_value = jQuery( '#role_um_progress_' + progress_field ).val();
		var progress_value = cont.find('#progress_valuei').val();

		var diff_progress = prev_progress_value - progress_value;

		var new_progress = current_progress*1 - diff_progress;
		if ( new_progress > 100 )
			return false;

		jQuery( '#role_um_progress_' + progress_field ).val( progress_value );
		cont.prev().find( '.profilec-progress ins' ).html( progress_value );
		jQuery('#role_um_allocated_progress').val( new_progress );

		jQuery('.profilec-ajax').html( 100 - new_progress );
		cont.hide();
        um_profilec_add_button();
		return false;
	});
});