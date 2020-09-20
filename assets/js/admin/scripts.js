jQuery( function ( $ ) {

	$( '#woocommerce-order-items' ).on( 'click', 'button.reduce-stock', function() {

		// wc_meta_boxes_order_items.block();
		$( '#woocommerce-order-items' ).block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		var data = {
			action   : 'manually_reduce_stock_for_woocommerce',
			order_id : woocommerce_admin_meta_boxes.post_id,
			security : woocommerce_admin_meta_boxes.order_item_nonce,
		};

		$.ajax( {
			url:     woocommerce_admin_meta_boxes.ajax_url,
			data:    data,
			type:    'POST',
			success: function( response ) {
				if ( response.success ) {
						//wc_meta_boxes_order_items.reloaded_items();
						// wc_meta_boxes_order_items.unblock();
						$( '#woocommerce-order-items' ).unblock();

						// Update notes.
						if ( response.data.notes_html ) {
							$( 'ul.order_notes' ).empty();
							$( 'ul.order_notes' ).append( $( response.data.notes_html ).find( 'li' ) );
						}
				} else {
					window.alert( response.data.error );
				}
				$( '#woocommerce-order-items' ).unblock();
			},
			complete: function() {

				$( '#woocommerce-order-items' ).unblock();
			}
		} );
	} );
} );
