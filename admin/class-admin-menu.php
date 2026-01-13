<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    Ninetynine_Hog
 * @subpackage Ninetynine_Hog/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ninetynine_Hog
 * @subpackage Ninetynine_Hog/admin
 * @author     Your Name <email@example.com>
 */
class Ninetynine_Hog_Admin_Menu {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the menu page for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			'99hog Settings',
			'99hog',
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_settings_page' ),
			'dashicons-chart-bar',
			56
		);
	}

	/**
	 * Display the settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( '99hog_settings' );
				do_settings_sections( $this->plugin_name );
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting( '99hog_settings', 'ninetynine_hog_options' );

		add_settings_section(
			'ninetynine_hog_general_section',
			__( 'General Settings', '99hog' ),
			null,
			$this->plugin_name
		);

		add_settings_field(
			'posthog_api_key',
			__( 'PostHog Project API Key', '99hog' ),
			array( $this, 'render_text_field' ),
			$this->plugin_name,
			'ninetynine_hog_general_section',
			array(
				'name' => 'posthog_api_key',
				'desc' => 'Enter your PostHog Project API Key.',
			)
		);

		add_settings_field(
			'posthog_host',
			__( 'PostHog Host', '99hog' ),
			array( $this, 'render_text_field' ),
			$this->plugin_name,
			'ninetynine_hog_general_section',
			array(
				'name' => 'posthog_host',
				'desc' => 'Enter your PostHog instance host (e.g., https://app.posthog.com).',
			)
		);

		$events = array(
			'view_item'           => 'View Item',
			'view_item_list'      => 'View Item List',
			'add_to_cart'         => 'Add to Cart',
			'remove_from_cart'    => 'Remove from Cart',
			'begin_checkout'      => 'Begin Checkout',
			'add_shipping_info'   => 'Add Shipping Info',
			'add_payment_info'    => 'Add Payment Info',
			'purchase'            => 'Purchase',
			'refund'              => 'Refund',
		);

		foreach ( $events as $event_key => $event_name ) {
			add_settings_field(
				'enable_' . $event_key,
				sprintf( 'Enable %s', $event_name ),
				array( $this, 'render_checkbox_field' ),
				$this->plugin_name,
				'ninetynine_hog_general_section',
				array(
					'name' => 'enable_' . $event_key,
					'desc' => sprintf( 'Enable the %s event.', $event_name ),
				)
			);
		}
	}

	/**
	 * Render text field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_text_field( $args ) {
		$options = get_option( 'ninetynine_hog_options' );
		$value   = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : '';
		echo '<input type="text" id="' . esc_attr( $args['name'] ) . '" name="ninetynine_hog_options[' . esc_attr( $args['name'] ) . ']" value="' . esc_attr( $value ) . '" class="regular-text">';
		if ( ! empty( $args['desc'] ) ) {
			echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
		}
	}

	/**
	 * Render checkbox field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$options = get_option( 'ninetynine_hog_options' );
		$value   = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : 0;
		echo '<input type="checkbox" id="' . esc_attr( $args['name'] ) . '" name="ninetynine_hog_options[' . esc_attr( $args['name'] ) . ']" value="1" ' . checked( 1, $value, false ) . '>';
		if ( ! empty( $args['desc'] ) ) {
			echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
		}
	}

}
