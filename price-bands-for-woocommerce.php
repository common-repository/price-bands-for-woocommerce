<?php

/*
Plugin Name: Price Bands for WooCommerce
Plugin URI:  https://nplugins.com/
Description: Create a price band and assign it to a product or variation. The product or variation pricing will now inherit the price band pricing. Update the pricing on a price band and all assigned products/variations will update to the new price.
Version: 1.0.4
Author: N Plugins
Author URI: https://nplugins.com/
Text Domain: nplugins-price-bands-for-woocommerce
Domain Path: languages
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Auto Deactivate Free Version

if ( !function_exists( 'pbfwc_fs' ) ) {
    // START FREEMIUS
    // Create a helper function for easy SDK access.
    function pbfwc_fs()
    {
        global  $pbfwc_fs ;
        
        if ( !isset( $pbfwc_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $pbfwc_fs = fs_dynamic_init( array(
                'id'             => '1870',
                'slug'           => 'price-bands-for-woocommerce',
                'type'           => 'plugin',
                'public_key'     => 'pk_d07d25b825493bd4d394d9a3014fd',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug' => 'edit.php?post_type=pbfwc_price_band',
            ),
                'is_live'        => true,
            ) );
        }
        
        return $pbfwc_fs;
    }
    
    // Init Freemius.
    pbfwc_fs();
    // Signal that SDK was initiated.
    do_action( 'pbfwc_fs_loaded' );
    // END FREEMIUS
    // WooCommerce check
    
    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        // Price Bands for WooCommerce
        
        if ( !class_exists( 'PBFWC_Price_Bands_for_WooCommerce' ) ) {
            class PBFWC_Price_Bands_for_WooCommerce
            {
                public function __construct()
                {
                    // Requires
                    require_once plugin_dir_path( __FILE__ ) . 'admin/class-price-bands-for-woocommerce-enqueues.php';
                    require_once plugin_dir_path( __FILE__ ) . 'admin/class-price-bands-for-woocommerce-post-types.php';
                    require_once plugin_dir_path( __FILE__ ) . 'admin/class-price-bands-for-woocommerce-meta-boxes.php';
                    require_once plugin_dir_path( __FILE__ ) . 'admin/class-price-bands-for-woocommerce-product-fields.php';
                    // Instantiate Classes
                    $price_bands_for_woocommerce_enqueues = new PBFWC_Price_Bands_for_WooCommerce_Enqueues();
                    $price_bands_for_woocommerce_post_types = new PBFWC_Price_Bands_for_WooCommerce_Post_Types();
                    $price_bands_for_woocommerce_meta_boxes = new PBFWC_Price_Bands_for_WooCommerce_Meta_Boxes();
                    $price_bands_for_woocommerce_product_fields = new PBFWC_Price_Bands_for_WooCommerce_Product_Fields();
                }
            
            }
            // Initial Instantiate
            $price_bands_for_woocommerce = new PBFWC_Price_Bands_for_WooCommerce();
        }
    
    } else {
        function pbfwc_woocommerce_install_activate_notice()
        {
            ?>
		
			<div class="notice notice-error is-dismissible">
				<p><?php 
            _e( 'Price Bands for WooCommerce requires WooCommerce to be installed and activated.', 'nplugins-price-bands-for-woocommerce' );
            ?></p>
			</div>
		
		<?php 
        }
        
        add_action( 'admin_notices', 'pbfwc_woocommerce_install_activate_notice' );
    }

}
