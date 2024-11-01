<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Shipdeo_V2
 * @subpackage Shipdeo_V2/includes
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
 * @package    Shipdeo_V2
 * @subpackage Shipdeo_V2/includes
 * @author     Your Name <email@example.com>
 */
class Shipdeo_V2
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Shipdeo_V2_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $shipdeo_v2    The string used to uniquely identify this plugin.
	 */
	protected $shipdeo_v2;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * @var Shipdeo_V2_Data_Store
	 */
	protected $data_store;

	/**
	 * @var Shipdeo_V2_Rest_Api
	 */
	protected $rest_api;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('SHIPDEO_V2_VERSION')) {
			$this->version = SHIPDEO_V2_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->shipdeo_v2 = 'shipdeo-v2';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Shipdeo_V2_Loader. Orchestrates the hooks of the plugin.
	 * - Shipdeo_V2_i18n. Defines internationalization functionality.
	 * - Shipdeo_V2_Admin. Defines all hooks for the admin area.
	 * - Shipdeo_V2_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shipdeo-v2-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shipdeo-v2-i18n.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shipdeo-v2-order-factory.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-shipdeo-v2-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-shipdeo-v2-public.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-shipdeo-v2-checkout.php';

		$this->loader = new Shipdeo_V2_Loader();

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shipdeo-v2-data-store.php';

		$this->data_store = new Shipdeo_V2_Data_Store();

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shipdeo-v2-rest-api.php';

		$this->rest_api = new Shipdeo_V2_Rest_Api(
			$this->get_shipdeo_v2(),
			$this->get_data_store()
		);

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shipdeo-v2-encryptor.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Shipdeo_V2_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Shipdeo_V2_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Shipdeo_V2_Admin(
			$this->get_shipdeo_v2(),
			$this->get_version(),
			$this->get_data_store(),
			$this->get_rest_api()
		);

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'menu_page');
		$this->loader->add_action('admin_post_shipdeo_v2_oauth', $plugin_admin, 'handle_post_oauth');
		$this->loader->add_action('admin_post_shipdeo_v2_store_information', $plugin_admin, 'handle_post_store_information');
		$this->loader->add_action('wp_ajax_get_subdistricts_by_name', $plugin_admin, 'handle_post_get_subdistricts_by_name');
		$this->loader->add_action('wp_ajax_get_cities_by_name', $plugin_admin, 'handle_post_get_cities_by_name');
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_shipping_meta_box');
		// Why priority is 60? So the callback will be triggered after default woocommerce callback has done
		// You can see default woocommerce callback in at wp-content/plugins/woocommerce/includes/admin/class-wc-admin-meta-boxes.php
		$this->loader->add_action('woocommerce_process_shop_order_meta', $plugin_admin, 'handle_update_shipdeo_information', 60);
		$this->loader->add_filter('manage_edit-shop_order_columns', $plugin_admin, 'add_shipdeo_information_columns');
		$this->loader->add_action('manage_shop_order_posts_custom_column', $plugin_admin, 'add_shipdeo_information_column_values', 10, 2);
		$this->loader->add_action('woocommerce_order_status_processing', $plugin_admin, 'handle_update_order_status_to_processing', 10, 2);
		$this->loader->add_action('woocommerce_order_status_cancelled', $plugin_admin, 'handle_update_order_status_to_cancelled', 10, 2);
		$this->loader->add_action('wp_ajax_post_create_order_to_shipdeo', $plugin_admin, 'handle_post_create_order_to_shipdeo');
		$this->loader->add_action('wp_ajax_post_confirm_order_to_shipdeo', $plugin_admin, 'handle_post_confirm_order_to_shipdeo');
		$this->loader->add_filter('bulk_actions-edit-shop_order', $plugin_admin, 'add_shipdeo_bulk_actions');
		$this->loader->add_filter(
			'handle_bulk_actions-edit-shop_order',
			$plugin_admin,
			'handle_shipdeo_bulk_actions_print_shipping_label',
			10,
			3
		);
		$this->loader->add_action('admin_footer', $plugin_admin, 'add_admin_footer_to_handle_shipdeo_bulk_actions');
		$this->loader->add_action('wp_ajax_post_print_shipping_label', $plugin_admin, 'handle_post_print_shipping_label');
		$this->loader->add_filter('woocommerce_hidden_order_itemmeta', $plugin_admin, 'add_hidden_order_itemmeta');
		$this->loader->add_filter('woocommerce_cod_process_payment_order_status', $plugin_admin, 'get_status_when_payment_is_cod');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		$plugin_public = new Shipdeo_V2_Public(
			$this->get_shipdeo_v2(),
			$this->get_version(),
			$this->get_data_store()
		);

		$plugin_checkout = new Shipdeo_V2_Checkout(
			$this->get_shipdeo_v2(),
			$this->get_version(),
			$this->get_data_store()
		);

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('wp_ajax_nopriv_get_subdistricts_by_name', $plugin_public, 'handle_post_get_subdistricts_by_name');
		$this->loader->add_action('wp_ajax_nopriv_get_cities_by_name', $plugin_public, 'handle_post_get_cities_by_name');
		$this->loader->add_action('woocommerce_after_order_details', $plugin_public, 'handle_show_shipment_information');
		$this->loader->add_action('wp_head', $plugin_public, 'handle_provide_ajaxurl');

		add_action('woocommerce_shipping_init', function () {
			require_once plugin_dir_path(__FILE__) . 'class-shipdeo-v2-shipping-method.php';
		});

		add_filter('woocommerce_shipping_methods', function ($methods) {
			$methods['shipdeo'] = 'Shipdeo_V2_Shipping_Method';
			return $methods;
		});

		$this->loader->add_filter('woocommerce_default_address_fields', $plugin_checkout, 'handle_override_checkout_fields');
		$this->loader->add_action(
			'woocommerce_checkout_order_processed',
			$plugin_checkout,
			'handle_post_create_order_to_shipdeo',
			10,
			3
		);
		$this->loader->add_filter('woocommerce_available_payment_gateways', $plugin_checkout, 'handle_show_available_payment_gateways');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_shipdeo_v2()
	{
		return $this->shipdeo_v2;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Shipdeo_V2_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

	/**
	 * @return Shipdeo_V2_Data_Store
	 */
	public function get_data_store()
	{
		return $this->data_store;
	}

	/**
	 * @return Shipdeo_V2_Rest_Api
	 */
	public function get_rest_api()
	{
		return $this->rest_api;
	}
}
