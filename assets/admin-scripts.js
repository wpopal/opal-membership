	/**
	 * Order Notes Panel
	 */
 ( function( $ ) {
	var opalmembership_meta_boxes_order_notes = {
		init: function() {
			$( '#opalmembership-payment-notes' )
				.on( 'click', 'a.add_note', this.add_order_note )
				.on( 'click', 'a.delete_note', this.delete_order_note );

		},

		add_order_note: function() {
			if ( ! $( 'textarea#add_order_note' ).val() ) {
				return;
			}



			var data = {
				action:    'membership_add_payment_note',
				post_id:   $(this).data('post-id') ,
				note:      $( 'textarea#add_order_note' ).val(),
				note_type: $( 'select#order_note_type' ).val(),
				security:  $( '#opalmembership_add_customer_note_nonce' ).val()
			};

			$.post( ajaxurl, data, function( response ) {
				$( 'ul.order_notes' ).prepend( response );

				$( '#add_order_note' ).val( '' );
			});

			return false;
		},

		delete_order_note: function() {
			var note = $( this ).closest( 'li.note' );


			var data = {
				action:   'opalmembership_delete_order_note',
				note_id:  $( note ).attr( 'rel' ),
				security: $( '#opalmembership_add_customer_note_nonce' ).val()
			};

			$.post( ajaxurl, data, function() {
				$( note ).remove();
			});

			return false;
		}
	};

	opalmembership_meta_boxes_order_notes.init();

} )( jQuery );