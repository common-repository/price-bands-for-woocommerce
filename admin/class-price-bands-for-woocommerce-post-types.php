<?php

// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) {
	exit;
}

// Post Types

if( !class_exists( 'PBFWC_Price_Bands_for_WooCommerce_Post_Types' ) ) {

	class PBFWC_Price_Bands_for_WooCommerce_Post_Types {

		public function __construct() {

			// Setup post type

			add_action( 'init', array( $this, 'pbfwc_price_band_post_type' ) );

			// Save Price Band

			add_action( 'save_post_pbfwc_price_band', array( $this, 'pbfwc_save_price_band' ) );

			// Delte Price Band

			add_action( 'deleted_post', array( $this, 'pbfwc_delete_price_band' ) );

		}

		public function pbfwc_price_band_post_type() {

			// Set labels
	
			$labels = array(
				'name'                  => _x( 'Price Bands', 'Post Type General Name', 'nplugins-price-bands-for-woocommerce' ),
				'singular_name'         => _x( 'Price Band', 'Post Type Singular Name', 'nplugins-price-bands-for-woocommerce' ),
				'menu_name'             => __( 'Price Bands', 'nplugins-price-bands-for-woocommerce' ),
				'name_admin_bar'        => __( 'Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'archives'              => __( 'Price Band Archives', 'nplugins-price-bands-for-woocommerce' ),
				'attributes'            => __( 'Price Band Attributes', 'nplugins-price-bands-for-woocommerce' ),
				'parent_item_colon'     => __( 'Parent Price Band:', 'nplugins-price-bands-for-woocommerce' ),
				'all_items'             => __( 'All Price Bands', 'nplugins-price-bands-for-woocommerce' ),
				'add_new_item'          => __( 'Add New Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'add_new'               => __( 'Add New', 'nplugins-price-bands-for-woocommerce' ),
				'new_item'              => __( 'New Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'edit_item'             => __( 'Edit Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'update_item'           => __( 'Update Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'view_item'             => __( 'View Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'view_items'            => __( 'View Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'search_items'          => __( 'Search Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'not_found'             => __( 'Not found', 'nplugins-price-bands-for-woocommerce' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'nplugins-price-bands-for-woocommerce' ),
				'featured_image'        => __( 'Featured Image', 'nplugins-price-bands-for-woocommerce' ),
				'set_featured_image'    => __( 'Set featured image', 'nplugins-price-bands-for-woocommerce' ),
				'remove_featured_image' => __( 'Remove featured image', 'nplugins-price-bands-for-woocommerce' ),
				'use_featured_image'    => __( 'Use as featured image', 'nplugins-price-bands-for-woocommerce' ),
				'insert_into_item'      => __( 'Insert into price band', 'nplugins-price-bands-for-woocommerce' ),
				'uploaded_to_this_item' => __( 'Uploaded to this price band', 'nplugins-price-bands-for-woocommerce' ),
				'items_list'            => __( 'Price band list', 'nplugins-price-bands-for-woocommerce' ),
				'items_list_navigation' => __( 'Price band list navigation', 'nplugins-price-bands-for-woocommerce' ),
				'filter_items_list'     => __( 'Filter price band list', 'nplugins-price-bands-for-woocommerce' ),
			);

			// Set args for registering post type

			$args = array(
				'label'                 => __( 'Price Band', 'nplugins-price-bands-for-woocommerce' ),
				'description'           => __( 'Price bands for products', 'nplugins-price-bands-for-woocommerce' ),
				'labels'                => $labels,
				'supports'              => array( 'title' ),
				'taxonomies'            => array(),
				'hierarchical'          => false,
				'public'                => false,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 56,
				'menu_icon'				=> 'dashicons-screenoptions',
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => true,
				'can_export'            => true,
				'has_archive'           => false,
				'exclude_from_search'   => false,
				'publicly_queryable'    => false,
				'capability_type'       => 'page',
			);

			// Register post type

			register_post_type( 'pbfwc_price_band', $args );

		}

		public function pbfwc_save_price_band( $post_id ) {

			// If the post is not trash/being trashed (we don't want to update product prices with empty values if price band being deleted)

			if( get_post_status( $post_id ) !== 'trash' ) {

				// Set variables

				$price_band_id = $post_id;
				$price_regular = $_POST['pbfwc_price_regular'];
				$price_sale = $_POST['pbfwc_price_sale'];

				// Update the price bands prices

				update_post_meta( $price_band_id, '_pbfwc_price_regular', sanitize_text_field( $price_regular ) );
				update_post_meta( $price_band_id, '_pbfwc_price_sale', sanitize_text_field( $price_sale ) );

				// Run product prices update

				$this->pbfwc_bulk_product_prices_update( $price_band_id, $price_regular, $price_sale );

			}

		}

		public function pbfwc_delete_price_band( $post_id ) {

			// Global $wpdb

			global $wpdb;

			// Delete product price band meta matching this deleted price band

			$wpdb->query( 
				$wpdb->prepare( 
					"DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key = %s AND meta_value = %s",
					'_pbfwc_price_band',
					$post_id
				)
			);

		}

		public function pbfwc_bulk_product_prices_update( $price_band_id, $price_regular, $price_sale ) {

			// Global $wpdb

			global $wpdb;

			// Update regular price

			$wpdb->query( 
				$wpdb->prepare( 
					"UPDATE {$wpdb->prefix}postmeta a
					INNER JOIN (
						SELECT post_id
						FROM {$wpdb->prefix}postmeta
						WHERE meta_value = %s
					) t on a.post_id = t.post_id and a.meta_key = %s
					SET meta_value = %s",
					$price_band_id,
					'_regular_price',
					$price_regular
				)
			);

			// Update price

			$wpdb->query( 
				$wpdb->prepare( 
					"UPDATE {$wpdb->prefix}postmeta a
					INNER JOIN (
						SELECT post_id
						FROM {$wpdb->prefix}postmeta
						WHERE meta_value = %s
					) t on a.post_id = t.post_id and a.meta_key = %s
					SET meta_value = %s",
					$price_band_id,
					'_price',
					$price_regular
				)
			);

			// If sale price

			if( $price_sale !== '' ) {

				// Update sale price

				$wpdb->query( 
					$wpdb->prepare( 
						"UPDATE {$wpdb->prefix}postmeta a
						INNER JOIN (
							SELECT post_id
							FROM {$wpdb->prefix}postmeta
							WHERE meta_value = %s
						) t on a.post_id = t.post_id and a.meta_key = %s
						SET meta_value = %s",
						$price_band_id,
						'_sale_price',
						$price_sale
					)
				);

				// Update price

				$wpdb->query( 
					$wpdb->prepare( 
						"UPDATE {$wpdb->prefix}postmeta a
						INNER JOIN (
							SELECT post_id
							FROM {$wpdb->prefix}postmeta
							WHERE meta_value = %s
						) t on a.post_id = t.post_id and a.meta_key = %s
						SET meta_value = %s",
						$price_band_id,
						'_price',
						$price_sale
					)
				);

			} else { // No sale price

				// Update sale price to empty

				$wpdb->query( 
					$wpdb->prepare( 
						"UPDATE {$wpdb->prefix}postmeta a
						INNER JOIN (
							SELECT post_id
							FROM {$wpdb->prefix}postmeta
							WHERE meta_value = %s
						) t on a.post_id = t.post_id and a.meta_key = %s
						SET meta_value = %s",
						$price_band_id,
						'_sale_price',
						''
					)
				);

			}

			// Delete Product Transients (so price range of variation products is updated)

			wc_delete_product_transients();

		}

	}

}