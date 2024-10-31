<?php

// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) {
	exit;
}

// Meta Boxes

if( !class_exists( 'PBFWC_Price_Bands_for_WooCommerce_Meta_Boxes' ) ) {

	class PBFWC_Price_Bands_for_WooCommerce_Meta_Boxes {

		public function __construct() {

			// Add meta boxes

			add_action( 'add_meta_boxes', array( $this, 'pbfwc_add_meta_boxes' ) );

		}

		public function pbfwc_add_meta_boxes() {

			// Add pricing meta box

			add_meta_box(
				'pricing',
				__( 'Pricing', 'nplugins-price-bands-for-woocommerce' ),
				array( $this, 'pbfwc_pricing_meta_box' ),
				'pbfwc_price_band',
				'normal',
				'default'
			);

		}

		public function pbfwc_pricing_meta_box() {

			// Get the global $post

			global $post;

			// Set the $post_id variable

			$post_id = $post->ID;

			// Output the pricing fields ?>

			<p>
				<label for="pbfwc_price_regular"><?php echo __( 'Regular Price', 'nplugins-price-bands-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label><input type="text" id="pbfwc_price_regular" name="pbfwc_price_regular" value="<?php echo get_post_meta( $post_id, '_pbfwc_price_regular', true ); ?>" required>
			</p>
			<p>
				<label for="pbfwc_price_sale"><?php echo __( 'Sale Price', 'nplugins-price-bands-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label><input type="text" id="pbfwc_price_sale" name="pbfwc_price_sale" value="<?php echo get_post_meta( $post_id, '_pbfwc_price_sale', true ); ?>">
			</p>

		<?php }

	}

}