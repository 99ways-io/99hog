<?php
/**
 * The data formatting class.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    Ninetynine_Hog
 * @subpackage Ninetynine_Hog/includes
 */

class Ninetynine_Hog_Data_Formatting {

	/**
	 * Get formatted item data for PostHog events.
	 *
	 * @since    1.0.0
	 * @param    WC_Product    $product    The WooCommerce product object.
	 * @param    int           $quantity   The quantity of the item.
	 * @return   array         The formatted item data.
	 */
	public static function get_item_data( $product, $quantity = 1 ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return array();
		}

		$categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
		
		$item_data = array(
			'item_id'        => $product->get_id(),
			'productnumber'  => $product->get_sku(),
			'item_name'      => $product->get_name(),
			'image_url'      => wp_get_attachment_image_url( $product->get_image_id(), 'full' ),
			'price'          => (float) $product->get_price(),
			'quantity'       => (int) $quantity,
			'item_category'  => isset( $categories[0] ) ? $categories[0] : '',
			'item_category2' => isset( $categories[1] ) ? $categories[1] : '',
			'item_category3' => isset( $categories[2] ) ? $categories[2] : '',
		);

		// Omit brand if not present
		// Note: WooCommerce does not have a native brand taxonomy.
		// This would typically be added by a third-party plugin.
		// A filter `ninetynine_hog_item_brand` could be added here for developers to integrate.

		return $item_data;
	}
}
