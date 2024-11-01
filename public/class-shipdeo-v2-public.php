<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Shipdeo_V2
 * @subpackage Shipdeo_V2/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Shipdeo_V2
 * @subpackage Shipdeo_V2/public
 * @author     Your Name <email@example.com>
 */
class Shipdeo_V2_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $shipdeo_v2    The ID of this plugin.
	 */
	private $shipdeo_v2;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * @var      Shipdeo_V2_Data_Store
	 */
	private $data_store;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $shipdeo_v2       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct(
		$shipdeo_v2,
		$version,
		$data_store
	) {

		$this->shipdeo_v2 = $shipdeo_v2;
		$this->version = $version;
		$this->data_store = $data_store;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shipdeo_V2_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shipdeo_V2_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if (is_checkout()) {
			wp_register_style(
				'jquery-ui',
				'https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css',
				array(),
				$this->version,
				'all'
			);
			wp_enqueue_style(
				$this->shipdeo_v2,
				plugin_dir_url(__FILE__) . 'css/app.css',
				array('jquery-ui'),
				$this->version,
				'all'
			);
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shipdeo_V2_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shipdeo_V2_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		if (is_checkout()) {
			wp_enqueue_script(
				$this->shipdeo_v2,
				plugin_dir_url(__FILE__) . 'js/checkout' . $suffix . '.js',
				array(
					'jquery',
					'jquery-ui-autocomplete',
				),
				$this->version,
				true
			);
		}
	}

	public function handle_post_get_subdistricts_by_name()
	{
		if (!defined('DOING_AJAX') || !DOING_AJAX) {
			wp_redirect($_SERVER['HTTP_REFERER']);
			exit;
		}

		$name = sanitize_text_field($_POST['name']);

		list('status_code' => $status_code, 'body' => $body) = $this->data_store->get_subdistricts_by_name($name);

		if ($status_code != 200) {
			wp_die(__('Not connected to shipdeo', 'shipdeo-v2'));
			exit;
		}

		list('data' => $data) = $body;

		echo json_encode($data);
		wp_die();
		exit;
	}

	public function handle_post_get_cities_by_name()
	{
		if (!defined('DOING_AJAX') || !DOING_AJAX) {
			wp_redirect($_SERVER['HTTP_REFERER']);
			exit;
		}

		$name = sanitize_text_field($_POST['name']);

		list('status_code' => $status_code, 'body' => $body) = $this->data_store->get_cities_by_name($name);

		if ($status_code != 200) {
			wp_die(__('Not connected to shipdeo', 'shipdeo-v2'));
			exit;
		}

		list('data' => $data) = $body;

		echo json_encode($data);
		wp_die();
		exit;
	}

	/**
	 * @param \WC_Order $order
	 * @return void
	 */
	public function handle_show_shipment_information($order)
	{
		$airwaybill = $order->get_meta('_shipdeo_v2_airwaybill');
		$shipping_methods = $order->get_shipping_methods();

		if (!$airwaybill || count($shipping_methods) < 1) {
			return;
		}

		$shipping_method = reset($shipping_methods);
		$courier_code = $shipping_method->get_meta('code');
		$link = $this->data_store->get_track_airwaybill_url(
			$courier_code,
			$airwaybill
		);

		require_once plugin_dir_path(__FILE__) . 'views/html-shipment-information.php';
	}

	public function handle_provide_ajaxurl()
	{
		echo '
			<script type="text/javascript">
				var ajaxurl = "' . admin_url('admin-ajax.php') . '";
			</script>
		';
	}
}
