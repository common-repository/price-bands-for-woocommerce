<?php

// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) {
	exit;
}

// Enqueues

if( !class_exists( 'PBFWC_Price_Bands_for_WooCommerce_Enqueues' ) ) {

	class PBFWC_Price_Bands_for_WooCommerce_Enqueues {

		public function __construct() {

			// Enqueue styles/scripts

			add_action( 'admin_enqueue_scripts', array( $this, 'pbfwc_enqueue_css' ) );

		}

		public function pbfwc_enqueue_css( $hook ) {

			// If this is the price band edit or new price band screen

			if( get_post_type() == 'pbfwc_price_band' && ( $hook == 'post.php' || $hook == 'post-new.php' ) ) {

				// Enqueue the meta boxes css

				wp_enqueue_style( 'pbfwc-meta-boxes', plugin_dir_url( __FILE__ ) . 'assets/css/meta-boxes.css' );

			}

		}

	}

}