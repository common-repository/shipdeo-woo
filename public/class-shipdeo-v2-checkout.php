<?php

class Shipdeo_V2_Checkout
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
     * @param array $fields
     * @return array
     */
    public function handle_override_checkout_fields($fields)
    {
        // Customize adddress 2
        $fields['address_2']['label'] = __('Subdistrict', 'shipdeo-v2');
        $fields['address_2']['label_class'] = array(
            'form-row-wide',
            'address-field',
        );
        $fields['address_2']['required'] = true;
        $fields['address_2']['placeholder'] = __('Subdistrict', 'shipdeo-v2');

        $fields['city']['priority'] = 61;
        $fields['state']['priority'] = 62;

        return $fields;
    }

    /**
     * @param int|\WP_ERROR $order_id
     * @param array $posted_data
     * @param true|\WC_Order|\WC_Order_Refund $order
     * @return void
     */
    public function handle_post_create_order_to_shipdeo($order_id, $posted_data, $order)
    {
        $factory = new Shipdeo_V2_Order_Factory($order, $this->data_store);
        $factory->create();
    }

    public function handle_show_available_payment_gateways($gateways)
    {
        if (!is_checkout()) {
            return $gateways;
        }

        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
        if ($chosen_shipping_methods == null) {
            return $gateways;
        }

        $chosen_shipping_methods = array_filter(
            $chosen_shipping_methods,
            function ($shipping_method) {
                return $shipping_method;
            }
        );

        if (count($chosen_shipping_methods) < 1) {
            return array();
        }

        $chosen_shipping_method = reset($chosen_shipping_methods);

        if (!$this->is_use_shipdeo_shipping_method($chosen_shipping_method)) {
            return $gateways;
        }

        list('total' => $cart_total) = WC()->session->get('cart_totals');
        $cart_total = floatval(($cart_total));

        if (!$this->can_use_cod_payment($chosen_shipping_method, $cart_total)) {
            unset($gateways['cod']);
        }

        return $gateways;
    }

    private function is_use_shipdeo_shipping_method($shipping_method_id)
    {
        $prefix = substr(
            $shipping_method_id,
            0,
            strlen(Shipdeo_V2_Shipping_Method::$prefix)
        );

        return $prefix == Shipdeo_V2_Shipping_Method::$prefix;
    }

    private function can_use_cod_payment($shipping_method_id, $cart_subtotal)
    {
        if ($cart_subtotal < Shipdeo_V2_Shipping_Method::$minimum_total_cod) {
            return false;
        }

        list($prefix, $encrypted_data) = explode('_', $shipping_method_id);

        list(
            'code' => $code,
            'support_cod' => $support_cod,
            'origin_subdistrict_code' => $origin_subdistrict_code,
            'destination_subdistrict_code' => $destination_subdistrict_code,
            'postal_code' => $postal_code
        ) = json_decode(
            Shipdeo_V2_Encryptor::decrypt($encrypted_data),
            true
        );

        if (!$support_cod) {
            return false;
        }

        list(
            'status_code' => $status_code,
            'body' => $body,
        ) = $this->data_store->get_cod_coverage(
            $code,
            $origin_subdistrict_code,
            $destination_subdistrict_code,
            $postal_code
        );

        if ($status_code != 200) {
            return false;
        }

        list('data' => $data) = $body;
        list('is_cod' => $is_cod) = $data;

        return boolval($is_cod);
    }
}
