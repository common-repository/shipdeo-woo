<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Shipdeo_V2
 *
 * @wordpress-plugin
 * Plugin Name:       Shipdeo V2
 * Plugin URI:        https://shipdeo.com
 * Description:       Plugin for support shipping in Indonesia.
 * Version:           1.2.8
 * Requires at least: 5.2
 * Requires PHP:      7.1
 * Author:            Clodeo
 * Author URI:        https://clodeo.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       shipdeo-v2
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SHIPDEO_V2_VERSION', '1.2.8');
define('SHIPDEO_V2_ENV_DEVELOPMENT', 'development');
define('SHIPDEO_V2_ENV_PRODUCTION', 'production');
define('SHIPDEO_V2_ENV_UAT', 'uat');
define('SHIPDEO_V2_ENV', SHIPDEO_V2_ENV_PRODUCTION);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-shipdeo-v2-activator.php
 */
function activate_shipdeo_v2()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-shipdeo-v2-activator.php';
	Shipdeo_V2_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-shipdeo-v2-deactivator.php
 */
function deactivate_shipdeo_v2()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-shipdeo-v2-deactivator.php';
	Shipdeo_V2_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_shipdeo_v2');
register_deactivation_hook(__FILE__, 'deactivate_shipdeo_v2');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-shipdeo-v2.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_shipdeo_v2()
{
	$plugin = new Shipdeo_V2();
	$plugin->run();
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	run_shipdeo_v2();
}
