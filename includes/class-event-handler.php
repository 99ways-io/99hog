<?php
/**
 * The event handler class.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    Ninetynine_Hog
 * @subpackage Ninetynine_Hog/includes
 */

class Ninetynine_Hog_Event_Handler {

	/**
	 * The plugin options.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    The plugin options.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'ninetynine_hog_options' );
		$this->init_posthog();
	}

	/**
	 * Initialize the PostHog client.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_posthog() {
		$api_key = isset( $this->options['posthog_api_key'] ) ? $this->options['posthog_api_key'] : '';
		$host    = isset( $this->options['posthog_host'] ) ? $this->options['posthog_host'] : '';

		if ( ! empty( $api_key ) && ! empty( $host ) ) {
			PostHog::init( $api_key, array( 'host' => $host ) );
		}
	}

	/**
	 * Track view_item event.
	 *
	 * @since    1.0.0
	 */
	public function track_view_item() {
		if ( ! empty( $this->options['enable_view_item'] ) && is_product() ) {
			global $product;
			if ( ! is_object( $product ) ) {
				$product = wc_get_product( get_the_ID() );
			}

			$this->send_event( 'view_item', array(
				'currency' => get_woocommerce_currency(),
				'value'    => $product->get_price(),
				'items'    => array( Ninetynine_Hog_Data_Formatting::get_item_data( $product ) ),
			) );
		}
	}

	/**
	 * Track view_item_list event.
	 *
	 * @since    1.0.0
	 */
	public function track_view_item_list() {
		if ( ! empty( $this->options['enable_view_item_list'] ) && ( is_shop() || is_product_category() || is_product_tag() || is_search() ) ) {
			global $wp_query;
			$items = array();
			foreach ( $wp_query->posts as $post ) {
				$product = wc_get_product( $post->ID );
				$items[] = Ninetynine_Hog_Data_Formatting::get_item_data( $product );
			}

			$this->send_event( 'view_item_list', array(
				'item_list_id'   => get_queried_object_id(),
				'item_list_name' => get_the_archive_title(),
				'items'          => $items,
			) );
		}
	}

	/**
	 * Track add_to_cart event.
	 *
	 * @param int $cart_item_key The cart item key.
	 * @param int $product_id The product ID.
	 * @param int $quantity The quantity.
	 */
	public function track_add_to_cart( $cart_item_key, $product_id, $quantity ) {
		if ( ! empty( $this->options['enable_add_to_cart'] ) ) {
			$cart_item = WC()->cart->get_cart_item( $cart_item_key );
			$product = $cart_item['data'];
			$this->send_event( 'add_to_cart', array(
				'currency' => get_woocommerce_currency(),
				'value'    => $cart_item['line_total'],
				'items'    => array( Ninetynine_Hog_Data_Formatting::get_item_data( $product, $quantity ) ),
			) );
		}
	}

	/**
	 * Track remove_from_cart event.
	 *
	 * @param string $cart_item_key The cart item key.
	 * @param object $cart The cart object.
	 */
	public function track_remove_from_cart( $cart_item_key, $cart ) {
		if ( ! empty( $this->options['enable_remove_from_cart'] ) ) {
			$cart_item = $cart->get_removed_cart_contents()[ $cart_item_key ];
			$product   = wc_get_product( $cart_item['product_id'] );
			$quantity  = $cart_item['quantity'];

			$this->send_event( 'remove_from_cart', array(
				'currency' => get_woocommerce_currency(),
				'value'    => $product->get_price() * $quantity,
				'items'    => array( Ninetynine_Hog_Data_Formatting::get_item_data( $product, $quantity ) ),
			) );
		}
	}

	/**
	 * Track purchase event.
	 *
	 * @param int $order_id The order ID.
	 */
	public function track_purchase( $order_id ) {
		if ( ! empty( $this->options['enable_purchase'] ) ) {
			$order = wc_get_order( $order_id );
			$items = array();
			foreach ( $order->get_items() as $item ) {
				$product   = $item->get_product();
				$items[]   = Ninetynine_Hog_Data_Formatting::get_item_data( $product, $item->get_quantity() );
			}
			$this->send_event( 'purchase', array(
				'transaction_id' => $order->get_order_number(),
				'currency'       => $order->get_currency(),
				'value'          => $order->get_total(),
				'items'          => $items,
				'shipping'       => $order->get_shipping_total(),
				'tax'            => $order->get_total_tax(),
				'coupon'         => implode( ', ', $order->get_coupon_codes() ),
				'payment_type'   => $order->get_payment_method_title(),
			) );

			PostHog::identify(array(
				'distinctId' => $order->get_billing_email(),
				'properties' => array(
					'email' => $order->get_billing_email(),
					'first_name' => $order->get_billing_first_name(),
					'last_name' => $order->get_billing_last_name(),
					'customer_id' => $order->get_customer_id(),
					'country' => $order->get_billing_country()
				)
			));
		}
	}

	/**
	 * Track refund event.
	 *
	 * @param int $order_id The order ID.
	 * @param int $refund_id The refund ID.
	 */
	public function track_refund( $order_id, $refund_id ) {
		if ( ! empty( $this->options['enable_refund'] ) ) {
			$order  = wc_get_order( $order_id );
			$refund = new WC_Order_Refund( $refund_id );
			$items  = array();
			foreach ( $refund->get_items() as $item ) {
				$product   = $item->get_product();
				$items[]   = Ninetynine_Hog_Data_Formatting::get_item_data( $product, $item->get_quantity() );
			}

			$this->send_event( 'refund', array(
				'transaction_id' => $order->get_order_number(),
				'currency'       => $order->get_currency(),
				'value'          => $refund->get_amount(),
				'items'          => $items,
			) );
		}
	}

	/**
	 * Send event to PostHog.
	 *
	 * @param string $event_name The event name.
	 * @param array  $properties The event properties.
	 */
	private function send_event( $event_name, $properties ) {
		$distinct_id = is_user_logged_in() ? get_current_user_id() : ( isset( $_COOKIE['ph_distinct_id'] ) ? $_COOKIE['ph_distinct_id'] : null );
		if ( ! $distinct_id ) {
			return; // Do not send event if we don't have a distinct_id
		}
		$context = array(
			'page_type' => $this->get_page_type(),
			'url' => $_SERVER['REQUEST_URI'],
			'referrer' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'wp_user_id' => is_user_logged_in() ? get_current_user_id() : null,
		);

		$properties = apply_filters( 'ph_ecom_event_props', $properties, $event_name, $context );

		$payload = array(
			'event' => $event_name,
			'properties' => array(
				'event_source' => 'server',
				'platform' => 'wordpress',
				'event_id' => wp_generate_uuid4(),
				'ecommerce' => $properties,
			),
			'distinctId' => $distinct_id,
		);

		$result = PostHog::capture($payload);

		do_action( 'ph_ecom_event_sent', $event_name, $payload, $result );
	}

	/**
	 * Get page type.
	 *
	 * @return string
	 */
	private function get_page_type() {
		if ( is_product() ) {
			return 'product';
		} elseif ( is_shop() || is_product_category() || is_product_tag() ) {
			return 'category';
		} elseif ( is_cart() ) {
			return 'cart';
		} elseif ( is_checkout() ) {
			return 'checkout';
		} elseif ( is_account_page() ) {
			return 'account';
		} elseif ( is_search() ) {
			return 'search';
		} else {
			return 'other';
		}
	}
}
