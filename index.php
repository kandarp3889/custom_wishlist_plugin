<?php
/*
Plugin Name: Wishlist Plugin
Description: Adds wishlist functionality to WooCommerce.
Version: 1.0
Author: Your Name
*/

// Enqueue JavaScript for AJAX
function wishlist_plugin_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('wishlist-plugin-js', plugin_dir_url(__FILE__) . 'js/wishlist-plugin.js', array('jquery'), '1.0', true);
    wp_enqueue_style('wishlist-plugin-styles', plugin_dir_url(__FILE__) . 'css/wishlist-plugin-styles.css');
    wp_localize_script('wishlist-plugin-js', 'wishlist_plugin_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'wishlist_plugin_enqueue_scripts');
// Function to handle the shortcode
function wishlist_shortcode_function( $atts ) {
    // Initialize output variable
    $output = '';

    // Check if the user is logged in
    if ( is_user_logged_in() ) {
        // Get the current user's ID
        $user_id = get_current_user_id();

        // Get wishlist items for the current user (Replace this with your wishlist retrieval logic)
        $wishlist_items = get_user_meta( $user_id, 'wishlist_items', true );

        // Build the output
        if ( ! empty( $wishlist_items ) ) {
            $output .= '<div class="favItm"><ul>';
            foreach ($wishlist_items as $wishlist_item) {
				// Check if the item is a post
				if (get_post_type($wishlist_item) === 'product') {
					$post_thumbnail = get_the_post_thumbnail($wishlist_item, 'thumbnail');
					$permalink = get_permalink($wishlist_item);
					$output .= '<li>';
					if ($permalink) {
						$output .= '<a class="aTag" href="' . $permalink . '" data-product_id="'.$wishlist_item.'">'; 
					}
					if ($post_thumbnail) {
						$output .= $post_thumbnail;
					} else {
                        $output .= wc_placeholder_img('thumbnail');
                    }
					$output .= '<p>' . get_the_title($wishlist_item) . '</p>';
					if ($permalink) {
						$output .= '</a>';
					}
					$output .= '</li>';
				}
			}
            $output .= '</ul></div>';
        } else {
            $output .= '<p>Your wishlist is empty.</p>';
        }
    } else {
        $output .= '<p>Please log in to view your wishlist.</p>';
    }

    // Return the output
    return $output;
}
add_shortcode( 'wishlist', 'wishlist_shortcode_function' );
function wishlist_plugin_add_wishlist_button() {
        global $product;
        if ($product instanceof WC_Product) {
            $product_id = $product->get_id();
        } else {
            $product_id = null;
        }
    echo '<input type="hidden" id="current_product_id" value="' . $product_id . '">';
    ?>
    <style>
    .add-to-wishlist-button {
        display: inline-block;
        background-color: transparent;
        color: #007bff;
        padding: 5px 10px;
        border: none;
        cursor: pointer;
        margin-top: 10px; /* Adjust as needed */
    }
		.add-to-wishlist-button .fa {
			color: #222222;
		}
		.add-to-wishlist-button .far {
			color: #222222;
		}
		.add-to-wishlist-button {
			position: absolute;
            right: 0px;
            font-size: 30px;
		}
		.pro_ttl_prc .add-to-wishlist-button{
			right: 130px;
            top: -90px;
		}
    </style>
    <script>
    jQuery(document).ready(function($) {
        // Find the product grid container
        var productGrid = $('.jet-woo-products__item, .pro_ttl_prc, .favItm li .aTag, .auction-products .auction_product_single, .catalog-related-products .related-product, .elementor-element-518cd14 .related-product>a');
		var currentURL = window.location.href;
		var urlParts = currentURL.split('/');

			

        // Loop through each product grid item
        productGrid.each(function() {
			if(urlParts[3] === 'favorites' || urlParts[3] === 'my-account' || urlParts[3] === 'product' || urlParts[3] === 'catalog'){
			   var productIds = $(this).attr('data-product_id') ? $(this).attr('data-product_id') : $('#current_product_id').val();
		   }else{
			   var productIds = $('#product_id').val() ? $('#product_id').val() : $('#current_product_id').val();
		   }
            // Get the product ID for this item
            var productId = $(this).find('[data-product_id]').data('product_id') == undefined ? productIds : $(this).find('[data-product_id]').data('product_id');
            // Create the "Add to Wishlist" button with the initial icon class
            var wishlistButton = $('<a href="#" class="add-to-wishlist-button" data-product-id="' + productId + '"><i class="far fa-heart"></i></a>');

            // Append the button to the product grid item
            $(this).append(wishlistButton);

            // Check if product is already in wishlist on page load
            checkWishlistItem(productId, wishlistButton);

            // Toggle heart icon and add/remove product from wishlist on click
            wishlistButton.click(function(e) {
                e.preventDefault();
                var button = $(this);
                var isAdded = button.hasClass('active');
                toggleHeartIcon(button, isAdded);
                addToWishlist(productId, !isAdded);
            });
        });

        // Function to toggle heart icon
        function toggleHeartIcon(button, isAdded) {
			
            if (isAdded) {
                button.find('i').removeClass('fa').fadeOut();
                button.find('i').addClass('far').fadeIn();
            } else {
				
                button.find('i').removeClass('far').fadeOut();
                button.find('i').addClass('fa').fadeIn();
				
            }
			button.hasClass('active') != true ? button.find('i').removeClass('far').addClass('fa').fadeIn() :  button.find('i').removeClass('fa').addClass('far').fadeIn();
            button.toggleClass('active');
        }

        // Function to check if product is already in the wishlist
        function checkWishlistItem(productId, button) {
            // AJAX request to check if product is in the wishlist
            $.post(wishlist_plugin_ajax.ajaxurl, {
                action: 'check_wishlist_item',
                product_id: productId
            }, function(response) {
                if (response === 'true') {
                    toggleHeartIcon(button, true);
                }
            });
        }

        // Function to add/remove product from wishlist
        function addToWishlist(productId, isAdded) {
            // AJAX request to add/remove product from wishlist
            $.post(wishlist_plugin_ajax.ajaxurl, {
                action: 'add_to_wishlist',
                product_id: productId,
                is_added: isAdded ? 'true' : 'false'
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'wishlist_plugin_add_wishlist_button');


// Check if product is in wishlist
add_action('wp_ajax_check_wishlist_item', 'check_wishlist_item');
add_action('wp_ajax_nopriv_check_wishlist_item', 'check_wishlist_item');

function check_wishlist_item() {
    $product_id = $_POST['product_id'];
    $user_id = get_current_user_id();
    $wishlist_items = get_user_meta($user_id, 'wishlist_items', true);
    $is_in_wishlist = in_array($product_id, $wishlist_items);
    echo $is_in_wishlist ? 'true' : 'false';
    wp_die();
}

// Add or remove product from wishlist
add_action('wp_ajax_add_to_wishlist', 'add_to_wishlist');
add_action('wp_ajax_nopriv_add_to_wishlist', 'add_to_wishlist');

function add_to_wishlist() {
    $product_id = $_POST['product_id'];
    $is_added = $_POST['is_added'] === 'true';
    $user_id = get_current_user_id();
    $wishlist_items = get_user_meta($user_id, 'wishlist_items', true);
    $wishlist_items = is_array($wishlist_items) ? $wishlist_items : array();
    // $fsf = !in_array($product_id, $wishlist_items);
    // echo '<pre>';-
    
    
    if ($is_added) {
        if (!in_array($product_id, $wishlist_items, true)) {
            $wishlist_items[] = $product_id;
            update_user_meta($user_id, 'wishlist_items', $wishlist_items);
            wp_send_json_success('Product added to wishlist.');
        } else {
            wp_send_json_error('Product already exists in wishlist.');
        }
    } else {
        
        $key = array_search($product_id, $wishlist_items, true);
        if ($key !== false) {
            unset($wishlist_items[$key]);
            update_user_meta($user_id, 'wishlist_items', $wishlist_items);
            wp_send_json_success('Product removed from wishlist.');
        } else {
            wp_send_json_error('Product does not exist in wishlist.');
        }
    }
}



