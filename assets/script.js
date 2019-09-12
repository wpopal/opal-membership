/**
* AJAX FUNCTIONS
*/
// Better focus for hidden submenu items for accessibility.
( function( $ ) {
	$(document).ready( function(){
		/**
		 * ajax actions
		 */
		 // Show the login form in the checkout when the user clicks the "Login" link
		$( 'body' ).on( 'click', '.membership-add-to-purchase', function ( e ) {
			var $this = $( this );
			var data = {
				action: $this.data( 'action' ),
				packageID:$this.attr('data-id')?$this.attr('data-id'):$this.data('id')
			};

			$.post( opalmembership_scripts.ajaxurl, data, function ( checkout_response ) {

			} ).done( function ( data ) {
				data = jQuery.parseJSON( data );
				if( data.result ){
					window.location.href = opalmembership_scripts.checkout;
				}
			} );
			return false;
		} );

		/**
		 * Checkout button
		 */
		$('#btn-opalmembership-checkout-button').click( function(){

	        var params = $("#opalmembership-checkout-form").serialize()+"&action=membership_preprocess_purchase&ajax=1";
	        $.ajax( {type:'POST',url:opalmembership_scripts.ajaxurl, data:params, 'dataType':'json'} ).done( function( output ){
	        	if( output.result == true ){
	        		$("#opalmembership-checkout-form").submit();
	        	}
	        	if( output.fields.length > 0 ){
	        		$("#opalmembership-checkout-form .has-error").removeClass('has-error');
	        		$("#opalmembership-checkout-form .input-error").remove();
	        		$(output.fields).each( function( key, input ){
	        			$('[name^="billing['+input.field+']"]').parent().addClass('has-error').removeClass('validate-success').append( '<div class="input-error">'+input.message+'</div>' );
	        			$('[name^="payment-info['+input.field+']"]').parent().addClass('has-error').removeClass('validate-success').append( '<div class="input-error">'+input.message+'</div>' );
	        		} );
	        	}
	        } );

			return false;
		} );

		/**
		 *
		 */
		$( document ).on( 'submit', '#opalmembership-coupon-form', function(e) {

	 		var $this = $(this);
	 		$(".message-alert", $this).remove();

		    $.ajax({
		           type: "POST",
		           url: opalmembership_scripts.ajaxurl,
		           data: $("#opalmembership-coupon-form").serialize()+'&action=membership_apply_coupon&ajax=1', // serializes the form's elements.
		           success: function(data) {
		              	data = jQuery.parseJSON( data );
		              	var message = $('<div class="message-alert alert alert-danger"></div>');

						$this.append(message);
						message.html( data.message );
		                if( data.result ){
		              		window.location.reload();
		                }
		           }

	        });

		    e.preventDefault(); // avoid to execute the actual submit of the form.
		});

		$( '.opalmembership-remove-coupon' ).on( 'click', function( e ){
			e.preventDefault();
		  	$.ajax({
				type: "POST",
				url: opalmembership_scripts.ajaxurl,
				data:'coupon_code='+$(this).data('code')+'&action=membership_remove_coupon&ajax=1', // serializes the form's elements.
				success: function(data) {
					data = jQuery.parseJSON( data );
					if( data.result ){
						window.location.reload();
					}
				}

         	});
			return false;
		} );

		/* base validate checkout input fields */
		$( '.validate-required .input-text, .validate-required select' ).on( 'focusout', function( e ){
			e.preventDefault();
			var _this = $(this),
				_parent_tag = _this.parents( '.form-row' ),
				_error_message = _parent_tag.find( '.input-error' ),
				_val = _this.val().trim();

			if ( ! _parent_tag.hasClass( 'validate-required' ) ) return false;

			if ( _val === '' || ( _this.attr( 'type' ) === 'email' && new RegExp( '^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$' ).test( _val ) === false ) ) {
				_parent_tag.removeClass( 'validate-success' );
				_parent_tag.addClass( 'has-error' );
				if ( _error_message.length === 1 ) {
					_error_message.show();
				}
			} else {
				_parent_tag.removeClass( 'has-error' );
				_parent_tag.addClass( 'validate-success' );
				if ( _error_message.length === 1 ) {
					_error_message.hide();
				}
			}
			return false;
		} );

		/* toggle effect payment method */
		$( document ).on( 'change', '.opalmembership-gateways input[name="payment_method"]', function( e ) {
			e.preventDefault();
			var _this = $( this ),
				_panel = _this.parents( '.opal-gateway-panel:first' ),
				_other = $( '.gateway-description, .gateway-form' ),
				_toggle_ele = _panel.find( '.gateway-description, .gateway-form' );
			_other.slideUp();
			_toggle_ele.slideDown();
		} );
		$( '.opalmembership-gateways input[name="payment_method"]:checked').change();

		/* toggle show register - login form on checkout page */
		$( document ).on( 'click', '.opalmembership-toggle-login', function( e ) {
			e.preventDefault();
			var section = $( '.opalmembership-login-form-toggle' );

			section.toggleClass( 'in' );

			if ( section.hasClass( 'in' ) ) {
				section.slideUp( 400 );
			} else {
				section.slideDown( 400 );
			}

			return false;
		} );

		///
		$('.membership-quick-purchase').each( function(){
			var $parent = $( this );
			$("li a", $parent ).click(function(){
				$( '.text-label', $parent ).html( $(this).html() ) ;
				$("li a", $parent ).removeClass( 'selected' );
				$(this).addClass( 'selected' );
				$('.membership-add-to-purchase', $parent).attr( 'data-id', $(this).data('package-id') );
			 	return true;
			});
		} );

		// stripe validate js
		$( '.stripe-cc-number' ).payment( 'formatCardNumber' );
        $( '.stripe-cc-exp' ).payment( 'formatCardExpiry' );
        $( '.stripe-cc-cvc' ).payment( 'formatCardCVC' );

	});
} )( jQuery );
