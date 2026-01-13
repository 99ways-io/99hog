<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    Ninetynine_Hog
 * @subpackage Ninetynine_Hog/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ninetynine_Hog
 * @subpackage Ninetynine_Hog/includes
 * @author     Your Name <email@example.com>
 */
class Ninetynine_Hog_Enqueue_Scripts {
    /**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * Ninetynine_Hog_Enqueue_Scripts constructor.
     *
     * @param $version
     */
    public function __construct( $version ) {
        $this->version = $version;
    }

    /**
     * Enqueue scripts and styles.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $options = get_option( 'ninetynine_hog_options' );
        $api_key = isset( $options['posthog_api_key'] ) ? $options['posthog_api_key'] : '';
        $host    = isset( $options['posthog_host'] ) ? $options['posthog_host'] : '';

        if ( empty( $api_key ) || empty( $host ) ) {
            return;
        }

        $script = "
            !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split('.');2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}var l=t.createElement('script');l.type='text/javascript',l.async=!0,l.src=s.api_host+'/static/array.js',(p=t.getElementsByTagName('script')[0]).parentNode.insertBefore(l,p);var u=e;for(void 0!==a?u=e[a]=[]:a='posthog',u.people=u.people||[],u.toString=function(t){var e='posthog';return'posthog'!==a&&(e+='.'+a),t||(e+=' (stub)'),e},u.people.toString=function(){return u.toString(1)+'.people (stub)'},o='capture identify alias people.set people.set_once set_config register register_once unregister opt_out_capturing has_opted_out_capturing opt_in_capturing reset isFeatureEnabled onFeatureFlags'.split(' '),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
            posthog.init('{$api_key}', { api_host: '{$host}' });
        ";

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $user_email = $user->user_email;
            $script .= "posthog.identify('{$user_id}', { email: '{$user_email}' });";
        } else {
            $script .= "
                posthog.onReady(function() {
                    var distinct_id = posthog.get_distinct_id();
                    document.cookie = 'ph_distinct_id=' + distinct_id + ';path=/';
                });
            ";
        }

        // Identify user at checkout
        if ( function_exists('is_checkout') && is_checkout() && ! is_user_logged_in() ) {
            wp_enqueue_script( 'jquery' );
            $script .= "
                jQuery( document.body ).on( 'change', '#billing_email', function() {
                    var email = jQuery(this).val();
                    if ( email ) {
                        posthog.identify(email);
                    }
                });
            ";
        }

        wp_add_inline_script( 'jquery-core', $script );
		
		wp_enqueue_script(
			'ninetynine-hog-events',
			plugin_dir_url( __FILE__ ) . '../assets/js/99hog-events.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		if ( function_exists('is_checkout') && is_checkout() ) {
			$cart = WC()->cart;
			$items = array();
			foreach ( $cart->get_cart() as $cart_item ) {
				$product = $cart_item['data'];
				$items[] = Ninetynine_Hog_Data_Formatting::get_item_data( $product, $cart_item['quantity'] );
			}

			$data = array(
				'currency' => get_woocommerce_currency(),
				'value'    => $cart->get_cart_contents_total(),
				'items'    => $items,
			);
			wp_localize_script( 'ninetynine-hog-events', 'ninetynine_hog_checkout_data', $data );
		}
    }
}
