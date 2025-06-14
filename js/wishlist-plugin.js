// jQuery(document).ready(function($) {
//     $('.add-to-wishlist-button').click(function(e) {
//         e.preventDefault();
//         var product_id = $(this).parent().data('product-id');
//         var data = {
//             'action': 'add_to_wishlist',
//             'product_id': product_id
//         };
//         $.post(wishlist_plugin_ajax.ajaxurl, data, function(response) {
//             if (response === 'success') {
//                 alert('Product added to wishlist!');
//             } else if (response === 'exists') {
//                 alert('Product is already in wishlist!');
//             }
//         });
//     });
// });
