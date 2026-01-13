<?php
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
class Ninetynine_Hog {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ninetynine_Hog_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'NINETYNINE_HOG_VERSION' ) ) {
			$this->version = NINETYNINE_HOG_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = '99hog';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ninetynine_Hog_Loader. Orchestrates the hooks of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once NINETYNINE_HOG_PLUGIN_DIR . 'admin/class-admin-menu.php';
		require_once NINETYNINE_HOG_PLUGIN_DIR . 'includes/class-enqueue-scripts.php';
		require_once NINETYNINE_HOG_PLUGIN_DIR . 'includes/data-formatting.php';
		require_once NINETYNINE_HOG_PLUGIN_DIR . 'includes/class-event-handler.php';
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Ninetynine_Hog_Admin_Menu( $this->get_plugin_name(), $this->get_version() );
		add_action( 'admin_menu', array( $plugin_admin, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $plugin_admin, 'register_settings' ) );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$enqueue_scripts = new Ninetynine_Hog_Enqueue_Scripts( $this->get_version() );
		add_action( 'wp_enqueue_scripts', array( $enqueue_scripts, 'enqueue_scripts' ) );
		
		$event_handler = new Ninetynine_Hog_Event_Handler();
		add_action( 'wp_head', array( $event_handler, 'track_view_item' ) );
		add_action( 'woocommerce_before_shop_loop', array( $event_handler, 'track_view_item_list' ) );
		add_action( 'woocommerce_add_to_cart', array( $event_handler, 'track_add_to_cart' ), 10, 3 );
		add_action( 'woocommerce_cart_item_removed', array( $event_handler, 'track_remove_from_cart' ), 10, 2 );
		add_action( 'woocommerce_thankyou', array( $event_handler, 'track_purchase' ) );
		add_action( 'woocommerce_order_refunded', array( $event_handler, 'track_refund' ), 10, 2 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// The loader is not used in this simplified version.
		// The hooks are added directly in the define_admin_hooks and define_public_hooks methods.
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
