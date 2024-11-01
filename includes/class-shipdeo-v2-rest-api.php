<?php

class Shipdeo_V2_Rest_Api
{
    protected $namespace;

    /**
     * @var Shipdeo_V2_Data_Store
     */
    protected $data_store;

    public function __construct($shipdeo_v2, $data_store)
    {
        $this->namespace = $shipdeo_v2;
        $this->data_store = $data_store;
        $this->register_routes();
    }

    protected function register_routes()
    {
        add_action('rest_api_init', function () {
            register_rest_route(
                $this->namespace,
                $this->get_webhooks_orders_route(),
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'webhooks_orders'),
                    'permission_callback' => array($this, 'validate_authorization'),
                )
            );
        });
    }

    public function get_webhooks_orders_route()
    {
        return '/v1/webhooks/orders';
    }

    public function get_webhooks_orders_uri()
    {
        return $this->namespace . $this->get_webhooks_orders_route();
    }

    public function get_full_url_webhooks_orders()
    {
        return $_SERVER['HTTP_ORIGIN'] . '/wp-json/' . $this->get_webhooks_orders_uri();
    }

    /**
     * @param \WP_REST_Request $request
     * @return bool
     */
    public function validate_authorization($request)
    {
        return $request->get_header('Authorization') == $this->data_store->get_hashed_credential();
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function webhooks_orders($request)
    {
        list(
            'airwayBillNumber' => $airwaybill,
            'status' => $status,
            'shipdeoOrderId' => $shipdeoOrderId,
        ) = $request->get_json_params();

        $status = strtoupper($status);

        if ((!$airwaybill && !$shipdeoOrderId) || !$status) {
            return new WP_Error('invalid_request_body', 'Invalid request body', array(
                'status' => 400,
            ));
        }

        $meta_key = $shipdeoOrderId ? '_shipdeo_v2_order_id' : '_shipdeo_v2_airwaybill';
        $meta_value = $shipdeoOrderId ?? $airwaybill;
        $orders = wc_get_orders(array(
                        'limit' => 1,
                        'meta_key' => $meta_key,
                        'meta_value' => $meta_value,
                        'meta_compare' => '=',
                    ));

        if (count($orders) < 1) {
            return new WP_Error('not_found', 'Order not found', array(
                'status' => 404,
            ));
        }

        /** @var \WC_Order */
        $order = reset($orders);
        $order->update_meta_data('_shipdeo_v2_status', $status);
        $order->update_meta_data('_shipdeo_v2_airwaybill', $airwaybill);
        $order->save_meta_data();

        if ($status == 'DELIVERED') {
            $order->set_status('completed');
            $order->save();
        }

        if ($status == 'RETURNED' || $status == 'CANCELLED') {
            $order->set_status('cancelled');
            $order->save();
        }

        if ($status == 'CONFIRMED') {
            $order->set_status('processing');
            $order->save();
        }

        return new WP_REST_Response(array(
            'success' => true,
            'data' => array(
                'order_id' => $order->get_meta('_shipdeo_v2_order_id'),
                'order_number' => $order->get_order_number(),
            )
        ));
    }
}
