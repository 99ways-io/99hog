<?php
/**
 * Plugin Name: 99hog
 * Plugin URI: https://github.com/user-attachments/99hog
 * Description: A lean, open-source WordPress plugin that integrates WooCommerce with PostHog for ecommerce event tracking.
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: 99hog
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin version.
define( 'NINETYNINE_HOG_VERSION', '1.0.0' );
define( 'NINETYNINE_HOG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require NINETYNINE_HOG_PLUGIN_DIR . 'includes/class-ninetynine-hog.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ninetynine_hog() {
	$plugin = new Ninetynine_Hog();
	$plugin->run();
}
run_ninetynine_hog();
