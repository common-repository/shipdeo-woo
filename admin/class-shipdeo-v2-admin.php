<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Shipdeo_V2
 * @subpackage Shipdeo_V2/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Shipdeo_V2
 * @subpackage Shipdeo_V2/admin
 * @author     Your Name <email@example.com>
 */
class Shipdeo_V2_Admin
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
	 * Data source
	 *
	 * @var Shipdeo_V2_Data_Store
	 */
	private $data_store;

	/**
	 * @var Shipdeo_V2_Rest_Api
	 */
	private $rest_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $shipdeo_v2 The name of this plugin.
	 * @param string $version The version of this plugin.
	 * @param Shipdeo_V2_Data_Store $data_store
	 * @param Shipdeo_V2_Rest_Api $rest_api
	 */
	public function __construct(
		$shipdeo_v2,
		$version,
		$data_store,
		$rest_api
	) {

		$this->shipdeo_v2 = $shipdeo_v2;
		$this->version = $version;
		$this->data_store = $data_store;
		$this->rest_api = $rest_api;
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style(
			$this->shipdeo_v2,
			plugin_dir_url(__FILE__) . 'css/app.min.css',
			array('woocommerce_admin_styles'),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script(
			$this->shipdeo_v2,
			plugin_dir_url(__FILE__) . 'js/app.min.js',
			array(
				'jquery',
				'select2',
			),
			$this->version,
			true
		);
	}

	public function menu_page()
	{
		$icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE4LjI4MyAwLjA3NzgwMTNDMTMuMDY3OCAwLjY3MDI0MiA4Ljc3ODg1IDQuODg0MSA4LjEwMjk2IDEwLjA5MUM3LjQxMDM5IDE1LjM4MTIgMTAuMjgwOCAyMC4wOTU3IDE0LjYzNjUgMjIuMTczNUMxNy4wNDggMjMuMzI1IDE4LjY5MTggMjUuNjI3OSAxOC42OTE4IDI4LjI5ODJWMjguOTk5QzE4LjY5MTggMzAuMTc1NiAxOC4wNzQzIDMxLjIyNyAxNy4xMDY1IDMxLjg5NDVDMTUuNjcxMiAzMi44OTU4IDE0Ljg1MzUgMzQuNzE0OSAxNS4zMzc0IDM2LjY3NTdDMTUuNzEyOSAzOC4xOTQ1IDE2Ljk0NzkgMzkuNDQ2MSAxOC40NTgyIDM5Ljg0NjZDMjEuNDI4OCA0MC42MTQzIDI0LjA5ODkgMzguMzk0NyAyNC4wOTg5IDM1LjU0OTNDMjQuMDk4OSAzNC4wMTM5IDIzLjMyMjkgMzIuNjcwNSAyMi4xNDY0IDMxLjg2OTVDMjEuMTk1MSAzMS4yMTg2IDIwLjYxMTEgMzAuMTU4OSAyMC42MTExIDI5LjAwNzRWMjguNTMxOEMyMC42MTExIDI1Ljc1MzEgMjIuMzEzMyAyMy4zNSAyNC44MDgyIDIyLjExNUMyOC42NTQ5IDIwLjIxMjYgMzEuMzA4MyAxNi4yNDkgMzEuMzA4MyAxMS42NjhDMzEuMzA4MyA0Ljc2NzI4IDI1LjMzMzkgLTAuNzIzMjQ5IDE4LjI4MyAwLjA3NzgwMTNaTTE5LjY1MTQgMTYuMTQ4OUMxNy4yNTY2IDE2LjE0ODkgMTUuMzEyNCAxNC4yMDQ2IDE1LjMxMjQgMTEuODA5OEMxNS4zMTI0IDkuNDE1MDEgMTcuMjU2NiA3LjQ3MDgzIDE5LjY1MTQgNy40NzA4M0MyMi4wNDYyIDcuNDcwODMgMjMuOTkwNSA5LjQxNTAxIDIzLjk5MDUgMTEuODA5OEMyMy45OTA1IDE0LjIwNDYgMjIuMDQ2MiAxNi4xNDg5IDE5LjY1MTQgMTYuMTQ4OVoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo=';

		add_menu_page(
			__('Shipdeo V2', 'shipdeo-v2'),
			__('Shipdeo V2', 'shipdeo-v2'),
			'manage_options',
			'shipdeo-v2',
			array($this, 'html_menu_view'),
			$icon,
			'59.1'
		);

		global $menu;

		$menu['59.2'] = array('', 'read', 'separator-shipdeo-v2', '', 'wp-menu-separator shipdeo-v2'); // WPCS: override ok.
	}

	public function html_menu_view()
	{
		$credential = $this->data_store->get_credential();
		$access_token = $this->data_store->get_access_token();
		$store_information = $this->data_store->get_store_information();

		require_once plugin_dir_path(__FILE__) . 'views/html-menu.php';
	}

	public function handle_post_oauth()
	{
		if (!$this->verify_nonce_for('shipdeo_v2_oauth')) {
			wp_die(__('Invalid nonce specified', 'shipdeo-v2'), __('Error', 'shipdeo-v2'), array(
				'response' 	=> 403,
				'back_link' => 'admin.php?page=shipdeo-v2',
			));
		}

		$client_id = sanitize_text_field($_POST['client_id']);
		$client_secret = sanitize_text_field($_POST['client_secret']);

		$this->data_store->set_credential(
			$client_id,
			$client_secret
		);

		list('status_code' => $status_code) = $this->data_store->login($client_id, $client_secret);

		if ($status_code == 500) {
			wp_redirect(
				esc_url_raw(
					add_query_arg(
						array(
							'error_message' => __('Failed connect to shipdeo', 'shipdeo-v2'),
						),
						admin_url('admin.php?page=shipdeo-v2#shipdeo_v2_oauth'),
					)
				)
			);
			exit;
		}

		$this->data_store->setup_webhook(
			$this->rest_api->get_full_url_webhooks_orders()
		);

		wp_redirect(
			esc_url_raw(
				add_query_arg(
					array(
						'success_message' => __('Success connected to shipdeo', 'shipdeo-v2'),
					),
					admin_url('admin.php?page=shipdeo-v2#shipdeo_v2_oauth'),
				)
			)
		);
		exit;
	}

	public function handle_post_store_information()
	{
		if (!$this->verify_nonce_for('shipdeo_v2_store_information')) {
			wp_die(__('Invalid nonce specified', 'shipdeo-v2'), __('Error', 'shipdeo-v2'), array(
				'response' 	=> 403,
				'back_link' => 'admin.php?page=shipdeo-v2',
			));
		}

		$store_name = sanitize_text_field($_POST['store_name']);
		$subdistrict_code = sanitize_text_field($_POST['subdistrict_code']);
		$subdistrict_name = sanitize_text_field($_POST['subdistrict_name']);
		$phone = sanitize_text_field($_POST['phone']);
		$email = sanitize_text_field($_POST['email']);
		$is_insuranced = sanitize_text_field($_POST['is_insuranced']);
		$is_must_insuranced = $is_insuranced === "true" ? sanitize_text_field($_POST['is_must_insuranced']) : "false";

		$this->data_store->set_store_information(
			$store_name,
			$phone,
			$email,
			$subdistrict_code,
			$subdistrict_name,
			$is_insuranced,
			$is_must_insuranced
		);

		wp_redirect(
			admin_url('admin.php?page=shipdeo-v2#store_information')
		);
		exit;
	}

	private function verify_nonce_for($name)
	{
		return isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], $name);
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

	public function add_shipping_meta_box()
	{
		add_meta_box(
			'shipdeo-v2',
			__('Shipdeo Information', 'shipdeo-v2'),
			array($this, 'shipping_fields'),
			'shop_order',
			'side',
			'core'
		);
	}

	public function shipping_fields()
	{
		global $theorder;
		/** @var \WC_Order */
		$order = $theorder;
		$wc_status = $order->get_status();
		$shipdeo_status = $order->get_meta('_shipdeo_v2_status');
		$order_id = $order->get_meta('_shipdeo_v2_order_id');
		$airwaybill = $order->get_meta('_shipdeo_v2_airwaybill');
		$shipping_methods = $order->get_shipping_methods();
		$link = null;
		$is_insuranced = false;

		if (count($shipping_methods) > 0) {
			$shipping_method = reset($shipping_methods);
			$is_insuranced = $shipping_method->get_meta('is_insuranced');
			$is_insuranced = $is_insuranced == null ? false : $is_insuranced;

			if ($airwaybill) {
				$courier_code = $shipping_method->get_meta('code');
				$link = $this->data_store->get_track_airwaybill_url(
					$courier_code,
					$airwaybill
				);
			}
		}

		require_once plugin_dir_path(__FILE__) . 'views/html-shipdeo-information.php';
	}

	public function handle_update_shipdeo_information($post_id)
	{
		$update = array();
		$is_insuranced = false;
		$shipping_total = 0;
		$fee_insurance = 0;
		$current_shipping_total = 0;

		if (isset($_POST['_shipdeo_v2_delivery_type']) && in_array($_POST['_shipdeo_v2_delivery_type'], array('pickup', 'dropoff'))) {
			$update['_shipdeo_v2_delivery_type'] = sanitize_text_field($_POST['_shipdeo_v2_delivery_type']);
		}

		if (count($update) < 1) {
			return;
		}

		$order = wc_get_order($post_id);
		$old_order = wc_get_order($post_id);
		$order_id = $order->get_meta('_shipdeo_v2_order_id');
		$subtotal_items = $order->get_subtotal();
		$total_discount = $order->get_discount_total();
		$total_tax = $order->get_total_tax();

		if (isset($_POST['_shipdeo_v2_is_insuranced']) && in_array($_POST['_shipdeo_v2_is_insuranced'], array(true, false))) {
			$is_insuranced = sanitize_text_field($_POST['_shipdeo_v2_is_insuranced']);
			$shipping_methods = $order->get_shipping_methods();
			$old_shipping_methods = $old_order->get_shipping_methods();

			if (count($shipping_methods) > 0 && count($old_shipping_methods) > 0) {
				$shipping_method = reset($shipping_methods);
				$old_shipping_method = reset($old_shipping_methods);

				$fee_insurance = floatval($shipping_method->get_meta('fee_insurance'));
				$current_shipping_total = floatval($shipping_method['total']);
				$current_insurance = $shipping_method->get_meta('is_insuranced');
				$method_title = $shipping_method['method_title'];
				$insurance_text = ' with Insurance';

				// update total charge
				if ($is_insuranced) {
					$shipping_method['method_title'] = $current_insurance == true ? $method_title : $method_title . $insurance_text;
					$shipping_total = $current_insurance == true ? $current_shipping_total : $current_shipping_total + $fee_insurance;
				} else {
					$shipping_method['method_title'] = $current_insurance == true ? str_replace($insurance_text, '', $method_title) : $method_title;
					$shipping_total = $current_insurance == true ? $current_shipping_total - $fee_insurance : $current_shipping_total;
				}

				$shipping_method['total'] = $shipping_total;
				$shipping_method->update_meta_data('is_insuranced', $is_insuranced);

				$shipping_method->save();
			}
		}

		foreach ($update as $key => $value) {
			$order->update_meta_data($key, $value);
		}

		$total = ($subtotal_items - $total_discount) + $total_tax + $shipping_total;
		$order->set_shipping_total($shipping_total);
		$order->set_total($total);

		$order->save();

		//update data to shipdeo
		$factory = new Shipdeo_V2_Order_Factory($order, $this->data_store);
		$factory->update($order_id, $old_order, $old_shipping_method);
	}

	/**
	 * @param array $columns
	 * @return array
	 */
	public function add_shipdeo_information_columns($columns)
	{
		$new_columns = array();

		foreach ($columns as $key => $column) {
			$new_columns[$key] = $column;

			if ($key == 'order_status') {
				$new_columns['payment_method'] = __('Payment Method', 'shipdeo-v2');
				$new_columns['shipdeo_v2_status'] = __('Shipdeo Status', 'shipdeo-v2');
				$new_columns['shipdeo_v2_airwaybill'] = __('Shipdeo Airwaybill', 'shipdeo-v2');
				$new_columns['shipdeo_v2_shipping_method'] = __('Shipping Method', 'shipdeo-v2');
			}
		}

		return $new_columns;
	}

	/**
	 * @param string $column
	 * @param int $post_id
	 * @return void
	 */
	public function add_shipdeo_information_column_values($column, $post_id)
	{
		$order = wc_get_order($post_id);

		switch ($column) {
			case 'payment_method':
				echo $order->get_payment_method_title();
				break;
			case 'shipdeo_v2_status':
				echo $order->get_meta('_shipdeo_v2_status');
				break;
			case 'shipdeo_v2_airwaybill':
				$airwaybill = $order->get_meta('_shipdeo_v2_airwaybill');
				$shipping_methods = $order->get_shipping_methods();

				if (!$airwaybill || count($shipping_methods) < 1) {
					break;
				}

				/** @var \WC_Order_Item_Shipping */
				$shipping_method = reset($shipping_methods);
				$courier_code = $shipping_method->get_meta('code');
				$link = $this->data_store->get_track_airwaybill_url($courier_code, $airwaybill);

				require plugin_dir_path(__FILE__) . 'views/html-airwaybill-link.php';
				break;
			case 'shipdeo_v2_shipping_method':
				echo $order->get_shipping_method();
				break;
		}
	}

	/**
	 * @param int $order_id
	 * @param \WC_Order $order
	 * @return void
	 */
	public function handle_update_order_status_to_processing($order_id, $order)
	{
		$this->confirm_order_to_shipdeo($order);
	}

	/**
	 * @param int $order_id
	 * @param \WC_Order $order
	 * @return void
	 */
	public function handle_update_order_status_to_cancelled($order_id, $order)
	{
		$order_id = $order->get_meta('_shipdeo_v2_order_id');

		if (!$order_id || !in_array($order->get_meta('_shipdeo_v2_status'), array('ENTRY', 'CONFIRMED', 'CONFIRM_PROBLEM'))) {
			return;
		}

		list(
			'status_code' => $status_code,
			'body' => $body
		) = $this->data_store->cancel_order(
			$order_id
		);

		if ($status_code != 200) {
			list('errorId' => $error_id, 'message' => $message) = $body;
			list($message_id, $message_content) = explode(" \n", $message);
			$note = sprintf(
				__(
					'Error cancel order to shipdeo with message: #%s %s',
					'shipdeo-v2'
				),
				$error_id,
				$message_content
			);
			$order->add_order_note($note);
			return;
		}

		$order->update_meta_data('_shipdeo_v2_status', 'CANCELLED');
		$order->save_meta_data();
	}

	public function handle_post_create_order_to_shipdeo()
	{
		if (!defined('DOING_AJAX') || !DOING_AJAX) {
			wp_redirect($_SERVER['HTTP_REFERER']);
			exit;
		}

		$order_id = sanitize_text_field($_POST['order_id']);
		$order = wc_get_order($order_id);
		$factory = new Shipdeo_V2_Order_Factory($order, $this->data_store);
		$factory->create();

		echo json_encode(true);
		wp_die();
		exit;
	}

	public function handle_post_confirm_order_to_shipdeo()
	{
		if (!defined('DOING_AJAX') || !DOING_AJAX) {
			wp_redirect($_SERVER['HTTP_REFERER']);
			exit;
		}

		$order_id = sanitize_text_field($_POST['order_id']);
		$order = wc_get_order($order_id);
		$this->confirm_order_to_shipdeo($order);

		echo json_encode(true);
		wp_die();
		exit;
	}

	/**
	 * @param \WC_Order $order
	 * @return void
	 */
	private function confirm_order_to_shipdeo($order)
	{
		$order_id = $order->get_meta('_shipdeo_v2_order_id');

		if (!$order_id || !in_array($order->get_meta('_shipdeo_v2_status'), array('ENTRY', 'CONFIRM_PROBLEM'))) {
			return;
		}

		list(
			'status_code' => $status_code,
			'body' => $body
		) = $this->data_store->confirm_order(
			$order_id,
			$order->get_meta('_shipdeo_v2_delivery_type')
		);

		if ($status_code != 200) {
			list('errorId' => $error_id, 'message' => $message) = $body;
			list($message_id, $message_content) = explode(" \n", $message);
			$note = sprintf(
				__(
					'Error confirm order to shipdeo with message: #%s %s',
					'shipdeo-v2'
				),
				$error_id,
				$message_content
			);
			$order->add_order_note($note);
			return;
		}

		list('data' => $data) = $body;

		$order->update_meta_data('_shipdeo_v2_status', $data['status']);
		$order->update_meta_data('_shipdeo_v2_airwaybill', $data['tracking_info']['airwaybill'] ?? null);
		$order->update_meta_data('_shipdeo_v2_booking_code', $data['tracking_info']['booking_code'] ?? null);
		$order->save_meta_data();
	}

	/**
	 * @param array $actions
	 * @return array
	 */
	public function add_shipdeo_bulk_actions($actions)
	{
		$actions['shipdeo_v2_print_shipping_label'] = __('Print shipping label', 'shipdeo-v2');

		return $actions;
	}

	/**
	 * @param string $redirect_to URL to redirect to.
	 * @param string $action Action name.
	 * @param array $ids List of ids.
	 * @return string
	 */
	public function handle_shipdeo_bulk_actions_print_shipping_label(
		$redirect_to,
		$action,
		$ids
	) {
		return add_query_arg(
			array(
				'selected_ids' => implode(',', $ids),
				'bulk_action' => $action,
			),
			$redirect_to
		);
	}

	public function add_admin_footer_to_handle_shipdeo_bulk_actions()
	{
		global $post_type, $pagenow;

		// Bail out if not on shop order list page.
		if ('edit.php' !== $pagenow || 'shop_order' !== $post_type || !isset($_REQUEST['bulk_action'])) { // WPCS: input var ok, CSRF ok.
			return;
		}

		$bulk_action = wc_clean(wp_unslash($_REQUEST['bulk_action']));

		switch ($bulk_action) {
			case 'shipdeo_v2_print_shipping_label':
				if (!isset($_REQUEST['selected_ids'])) {
					break;
				}

				$selected_ids = $_REQUEST['selected_ids'];

				require_once plugin_dir_path(__FILE__) . 'views/html-shipping-label.php';
				break;
		}
	}

	public function handle_post_print_shipping_label()
	{
		if (!defined('DOING_AJAX') || !DOING_AJAX) {
			wp_redirect($_SERVER['HTTP_REFERER']);
			exit;
		}

		$ids = explode(',', sanitize_text_field($_POST['ids']));
		$mode = sanitize_text_field($_POST['mode']);

		$order_ids = array_reduce(
			$ids,
			function ($occ, $id) {
				$order = wc_get_order($id);
				$order_id = $order->get_meta('_shipdeo_v2_order_id');

				if ($order_id) {
					array_push($occ, $order_id);
				}

				return $occ;
			},
			array()
		);

		list(
			'status_code' => $status_code,
			'body' => $body,
		) = $this->data_store->get_shipping_label_url_v2($order_ids);

		if ($status_code != 200) {
			echo json_encode(
				array(
					'status_code' => $status_code,
					'success' => false,
					'message' => __('Oops! Something went wrong', 'shipdeo-v2'),
				)
			);
			exit;
		}

		echo json_encode(
			array(
				'status_code' => $status_code,
				'success' => true,
				'data' => $body,
			)
		);
		wp_die();
		exit;
	}

	/**
	 * @param array $items
	 * @return array
	 */
	public function add_hidden_order_itemmeta($items)
	{
		if (!defined('WP_DEBUG') || WP_DEBUG == false) {
			$items = array_merge($items, array(
				'code',
				'service',
				'duration',
				'origin_subdistrict_code',
				'destination_subdistrict_code',
			));
		}

		return $items;
	}

	/**
	 * @return string
	 */
	public function get_status_when_payment_is_cod()
	{
		return 'on-hold';
	}
}
