<?php

// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) {
	exit;
}

// Product Fields

if( !class_exists( 'PBFWC_Price_Bands_for_WooCommerce_Product_Fields' ) ) {

	class PBFWC_Price_Bands_for_WooCommerce_Product_Fields {

		public function __construct() {

			// Add fields

			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'pbfwc_add_price_band_field_simple_product' ) );
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'pbfwc_add_price_band_field_variable_product' ), 10, 3 );

			// Save fields

			add_action( 'save_post_product', array( $this, 'pbfwc_save_price_band_simple_product' ) );
			add_action( 'woocommerce_save_product_variation', array( $this, 'pbfwc_save_price_band_variable_product' ) );
			add_action( 'updated_post_meta', array( $this, 'pbfwc_on_woocommerce_product_price_update'), 10, 4 );			

		}

		public function pbfwc_add_price_band_field_simple_product() {

			// Show the price band field for simple products

			$this->pbfwc_show_price_band_field( $variation = false );

		}

		public function pbfwc_add_price_band_field_variable_product( $loop, $variation_data, $variation ) {

			// Show the price band field for variable products

			$this->pbfwc_show_price_band_field( $variation );

		}

		public function pbfwc_show_price_band_field( $variation ) {

			// Get the global $post

			global $post;

			// Set args for get posts

			$args = array(
				'post_type'			=> 'pbfwc_price_band',
				'post_status'		=> 'publish',
				'posts_per_page'	=> -1,
				'order'				=> 'ASC',
				'orderby'			=> 'title',
			);

			// Get the price bands

			$price_bands = get_posts( $args );

			// Setup an options array with the first option as no price band

			$options = array(
				'' => __( 'No price band', 'nplugins-price-bands-for-woocommerce' ),
			);

			// Loop through each price band

			foreach( $price_bands as $price_band ) {

				// Store variables

				$price_band_id = $price_band->ID;
				$price_band_price_regular = get_post_meta( $price_band_id, '_pbfwc_price_regular', true );
				$price_band_price_sale = get_post_meta( $price_band_id, '_pbfwc_price_sale', true );

				// If price band price sale is empty

				if( empty( $price_band_price_sale ) ) {

					// Just show the regular price

					$options[$price_band_id] = get_the_title( $price_band->ID ) . ' (' . __( 'Regular: ', 'nplugins-price-bands-for-woocommerce' ) . get_woocommerce_currency_symbol() . $price_band_price_regular . ')';	

				} else {

					// Show both prices

					$options[$price_band_id] = get_the_title( $price_band->ID ) . ' (' . __( 'Regular: ', 'nplugins-price-bands-for-woocommerce' ) . get_woocommerce_currency_symbol() . $price_band_price_regular . ' / ' . __( 'Sale: ', 'nplugins-price-bands-for-woocommerce' ) . get_woocommerce_currency_symbol() . $price_band_price_sale . ')';

				}

			}

			// If a simple product

			if( $variation == false ) {

				// Set args for a simple product

				$args = array(
					'id'            => 'pbfwc_price_band',
					'class'         => 'pbfwc_price_band',
					'label'         => 'Price Band',
					'desc_tip'      => true,
					'description'   => 'Override this products price by selecting a price band.',
					'options'		=> $options,
					'value'			=> get_post_meta( $post->ID, '_pbfwc_price_band', true ),
				);

			} elseif( !empty( $variation ) ) { // Is a variation

				// Set args for a variable product

				$args = array(
					'id'            => 'pbfwc_price_band[' . $variation->ID . ']',
					'class'         => 'pbfwc_price_band',
					'label'         => 'Price Band',
					'desc_tip'      => true,
					'description'   => 'Override this products price by selecting a price band.',
					'options'		=> $options,
					'value'			=> get_post_meta( $variation->ID, '_pbfwc_price_band', true ),
				);

			}

			// Show select field
 
			woocommerce_wp_select( $args );

		}

		public function pbfwc_save_price_band_simple_product( $post_id ) {

			// Ensure this is not the change price band bulk action (without this not selecting a price band on bulk action would lose the price band as $_POST['pbfwc_price_band'] doesn't exist for it)

			if( !isset( $_REQUEST['change_price_band'] ) ) {

				// Store product id

				$product_id = $post_id;

				// If product post type

				//if( get_post_type( $product_id ) == 'product' ) {

					// If the price band removed

					if( $_POST['pbfwc_price_band'] == '' ) {

						// Delete the post meta

						delete_post_meta( $product_id, '_pbfwc_price_band' );

					} else { // Price band to update

						// Add/update the post meta

						update_post_meta( $product_id, '_pbfwc_price_band', sanitize_text_field( $_POST['pbfwc_price_band'] ) );

						// Set the product prices

						$this->pbfwc_set_woocommerce_product_price_regular( $product_id );
						$this->pbfwc_set_woocommerce_product_price_sale( $product_id );

					}

				//}

			}

		}

		public function pbfwc_save_price_band_variable_product( $post_id ) {

			// Ensure this is not the change price band bulk action (without this not selecting a price band on bulk action would lose the price band as $_POST['pbfwc_price_band'] doesn't exist for it)

			if( !isset( $_REQUEST['change_price_band'] ) ) {

				// Store product id

				$product_id = $post_id;

				// If the price band removed

				if( $_POST['pbfwc_price_band'][ $product_id ] == '' ) {

					// Delete the post meta

					delete_post_meta( $product_id, '_pbfwc_price_band' );

				} else { // Price band to update

					// Add/update the post meta

					update_post_meta( $product_id, '_pbfwc_price_band', sanitize_text_field( $_POST['pbfwc_price_band'][ $product_id ] ) );

					// Set the product prices

					$this->pbfwc_set_woocommerce_product_price_regular( $product_id );
					$this->pbfwc_set_woocommerce_product_price_sale( $product_id );

				}

			}

		}
		
		public function pbfwc_on_woocommerce_product_price_update( $meta_id, $object_id, $meta_key, $_meta_value ) {

			// If the meta meta key is _regular_price

			if( $meta_key == '_regular_price' ) {

				// Set the product id

				$product_id = $object_id;

				// Set the regular price

				$this->pbfwc_set_woocommerce_product_price_regular( $product_id );

			}

			// If the meta meta key is regular_price

			if( $meta_key == '_sale_price' ) {

				// Set the product id

				$product_id = $object_id;

				// Set the sale price

				$this->pbfwc_set_woocommerce_product_price_sale( $product_id );

			}

		}

		public function pbfwc_set_woocommerce_product_price_regular( $product_id ) {

			// Get the global $wpdb

			global $wpdb;

			// Get price band

			$price_band = get_post_meta( $product_id, '_pbfwc_price_band', true );

			// Get price band regular price

			$price_band_price_regular = get_post_meta( $price_band, '_pbfwc_price_regular', true );

			// If $price_band and $price_band_price_regular not empty

			if( !empty( $price_band ) && !empty( $price_band_price_regular ) ) {

				// Update the prices (Note: WPDB query used so we don't get tangled up in WooCommerce's _price field calculation)

				$wpdb->query( 
					$wpdb->prepare( 
						"UPDATE {$wpdb->prefix}postmeta SET meta_value = %s WHERE meta_key = %s AND post_id = %s",
						$price_band_price_regular,
						'_regular_price',
						$product_id
					)
				);

				$wpdb->query( 
					$wpdb->prepare( 
						"UPDATE {$wpdb->prefix}postmeta SET meta_value = %s WHERE meta_key = %s AND post_id = %s",
						$price_band_price_regular,
						'_price',
						$product_id
					)
				);

			}

		}

		public function pbfwc_set_woocommerce_product_price_sale( $product_id ) {

			// Get the global $wpdb

			global $wpdb;

			// Get price band

			$price_band = get_post_meta( $product_id, '_pbfwc_price_band', true );

			// Get price band sale price

			$price_band_price_sale = get_post_meta( $price_band, '_pbfwc_price_sale', true );

			// If $price_band and $price_band_price_sale not empty

			if( !empty( $price_band ) && !empty( $price_band_price_sale ) ) {

				// Update the prices (Note: WPDB query used so we don't get tangled up in WooCommerce's _price field calculation)

				$wpdb->query( 
					$wpdb->prepare( 
						"UPDATE {$wpdb->prefix}postmeta SET meta_value = %s WHERE meta_key = %s AND post_id = %s",
						$price_band_price_sale,
						'_sale_price',
						$product_id
					)
				);

				$wpdb->query( 
					$wpdb->prepare( 
						"UPDATE {$wpdb->prefix}postmeta SET meta_value = %s WHERE meta_key = %s AND post_id = %s",
						$price_band_price_sale,
						'_price',
						$product_id
					)
				);


			}

		}

	}

}