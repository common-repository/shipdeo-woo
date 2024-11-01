<?php

class Shipdeo_V2_Order_Factory
{
    /**
     * @var \WC_Order
     */
    private $order;

    /**
     * @var Shipdeo_V2_Data_Store
     */
    private $data_store;

    private $shipping_methods;

    public function __construct($order, $data_store)
    {
        $this->order = $order;
        $this->data_store = $data_store;
        $this->shipping_methods = $order->get_shipping_methods();
    }

    public function create()
    {
        $order_number = $this->get_order_number();
        $is_cod = $this->is_cod();
        $courier = $this->get_courier();
        $origin = $this->get_origin();
        $destination = $this->get_destination();

        $items = $this->get_items_with_discount();
        $transaction = $this->get_transaction_with_discount_insurance(
            $items,
            $is_cod,
            $courier
        );

        //override data transaction
        $is_insuranced = false;
        $fee_insurance = $transaction['fee_insurance'];
        if(count($this->shipping_methods) > 0){
            $shipping_method = reset($this->shipping_methods);
            $is_insuranced = boolval($shipping_method->get_meta('is_insuranced'));
        }
        $transaction['fee_insurance'] = $is_insuranced ? $fee_insurance : 0;
        $tenant_id = $this->get_tenant_id();

        list('status_code' => $status_code, 'body' => $body) = $this->data_store->create_order(
            $order_number,
            $courier,
            $origin,
            $destination,
            $items,
            $transaction,
            $this->order->get_date_created()->date('Y-m-d H:i:s'),
            $tenant_id,
            $is_cod,
            $this->order->get_customer_note()
        );

        if ($status_code != 200) {
            list('errorId' => $error_id, 'message' => $message) = $body;
            list($message_id, $message_content) = explode(" \n", $message);
            $note = sprintf(
                __(
                    'Error create order to shipdeo with message: #%s %s',
                    'shipdeo-v2'
                ),
                $error_id,
                $message_content
            );
            $this->order->add_order_note($note);
            return;
        }

        list('data' => $data) = $body;

        $this->order->add_meta_data('_shipdeo_v2_order_id', $data['_id']);
        $this->order->add_meta_data('_shipdeo_v2_delivery_type', $data['delivery_type'] ?? null);
        $this->order->add_meta_data('_shipdeo_v2_status', $data['status'] ?? null);
        $this->order->add_meta_data('_shipdeo_v2_airwaybill', $data['tracking_info']['airwaybill'] ?? null);
        $this->order->add_meta_data('_shipdeo_v2_booking_code', $data['tracking_info']['booking_code'] ?? null);
        $this->order->save_meta_data();
    }

    public function update($order_id, $old_order, $old_shipping_method)
    {
        $tenant_id = $this->get_tenant_id();
        $is_cod = $this->is_cod();
        $courier = $this->get_courier();
        $origin = $this->get_origin();
        $destination = $this->get_destination();

        $shipping_methods = $this->shipping_methods;
        if (count($shipping_methods) > 0) {
            $shipping_method = reset($shipping_methods);
            $is_insuranced = boolval($shipping_method->get_meta('is_insuranced'));
            $items = $this->get_items_with_discount();
        }

        //TODO
        //refactor this code, when enable for update courier
        $transaction = $this->get_transaction_with_discount_insurance(
            $items,
            $is_cod,
            $courier
        );
        //override insurance flag
        $transaction['is_insuranced'] = $is_insuranced;
        $transaction['fee_insurance'] = $is_insuranced == true ? floatval($courier['fee_insurance']) : 0;


        list('status_code' => $status_code, 'body' => $body) = $this->data_store->update_order(
            $tenant_id,
            $order_id,
            $this->order,
            $courier,
            $origin,
            $destination,
            $items,
            $transaction,
            $is_cod,
            $this->order->get_customer_note()
        );

        if ($status_code != 202) {
            list('errorId' => $error_id, 'message' => $message) = $body;
            list($message_id, $message_content) = explode(" \n", $message);
            $note = sprintf(
                __(
                    'Error create order to shipdeo with message: #%s %s',
                    'shipdeo-v2'
                ),
                $status_code,
                $message
            );
            $this->order->add_order_note($note);

            // rollback woocommerce order
            $this->rollback_order_woocommerce($order_id, $old_order);
            return;
        }

        list('data' => $data) = $body;

        $this->order->add_meta_data('_shipdeo_v2_delivery_type', $data['delivery_type'] ?? null);
        $this->order->add_meta_data('_shipdeo_v2_status', $data['status'] ?? null);
        $this->order->add_meta_data('_shipdeo_v2_airwaybill', $data['tracking_info']['airwaybill'] ?? null);
        $this->order->add_meta_data('_shipdeo_v2_booking_code', $data['tracking_info']['booking_code'] ?? null);
        $this->order->save_meta_data();
    }

    private function rollback_order_woocommerce($order_id, $old_order){
		$order = $this->order;
		$shipping_methods = $order->get_shipping_methods();
		$old_shipping_methods = $old_order->get_shipping_methods();

		if (count($shipping_methods) > 0 && count($old_shipping_methods) > 0) {
			$shipping_method = reset($shipping_methods);
            $old_shipping_method = reset($old_shipping_methods);

            //TODO
            //refactor this code, when enable for update courier
            //add others property
            $is_insuranced = $old_shipping_method->get_meta('is_insuranced');
            $shipping_method['method_title'] = $old_shipping_method['method_title'];
            $shipping_method['total'] = $old_shipping_method['total'];
            $shipping_method->update_meta_data('is_insuranced', $is_insuranced);

			$shipping_method->save();
		}

        $delivery_type = $old_order->get_meta('_shipdeo_v2_delivery_type');
        $shipping_total = $old_order->get_shipping_total();
        $total = $old_order->get_total();

        $order->update_meta_data('_shipdeo_v2_delivery_type', $delivery_type);
        $order->set_shipping_total($shipping_total);
		$order->set_total($total);

        $order->save();
	}
    private function get_order_number()
    {
        return $this->order->get_order_number();
    }

    private function get_courier()
    {
        $shipping_methods = $this->order->get_shipping_methods();
        $shipping_method = reset($shipping_methods);

        return array(
            'code' => $shipping_method->get_meta('code'),
            'service' => $shipping_method->get_meta('service'),
            'base_price' => $shipping_method->get_meta('base_price'),
            'is_insuranced' => $shipping_method->get_meta('is_insuranced'),
            'fee_insurance' => $shipping_method->get_meta('fee_insurance'),
        );
    }

    private function get_origin()
    {
        return $this->data_store->get_origin();
    }

    private function get_destination()
    {
        $shipping_methods = $this->order->get_shipping_methods();
        $shipping_method = reset($shipping_methods);
        $subdistrict_code = $shipping_method->get_meta('destination_subdistrict_code');
        $subdistrict_name = $this->order->get_shipping_address_2();
        $city_name = $this->order->get_shipping_city();
        $province_name = $this->data_store->get_states_from(
            $this->order->get_shipping_state()
        );
        $postal_code = $this->order->get_shipping_postcode();

        return array_merge(
            $this->data_store->get_location_codes_from($subdistrict_code),
            array(
                'subdistrict_name' => $subdistrict_name,
                'city_name' => $city_name,
                'province_name' => $province_name,
                'postal_code' => $postal_code,
                'name' => $this->order->get_formatted_shipping_full_name(),
                'phone' => $this->order->get_billing_phone(),
                'address' => join(
                    ', ',
                    array(
                        $this->order->get_shipping_address_1(),
                        $subdistrict_name,
                        $city_name,
                        $province_name,
                        $postal_code
                    )
                ),
            )
        );
    }

    private function get_items()
    {
        $weight_uom = $this->data_store->get_weight_uom();
        $dimension_uom = $this->data_store->get_dimension_uom();

        return array_reduce($this->order->get_items(), function ($occ, $item) use ($weight_uom, $dimension_uom) {
            /** @var \WC_Order_Item_Product $item */

            /** @var \WC_Product */
            $product = $item->get_product();

            array_push($occ, array(
                'name' => $item->get_name(),
                'description' => $product->get_short_description() ?? $product->get_description(),
                'weight' => floatval($product->get_weight()) * $item->get_quantity(),
                'weight_uom' => $weight_uom,
                'qty' => $item->get_quantity(),
                'value' => floatval($product->get_price()),
                'width' => floatval($product->get_width()),
                'height' => floatval($product->get_height()),
                'length' => floatval($product->get_length()),
                'dimension_uom' => $dimension_uom,
                'total_value' => floatval($item->get_total()),
            ));

            return $occ;
        }, array());
    }

    private function get_items_with_discount()
    {
        $weight_uom = $this->data_store->get_weight_uom();
        $dimension_uom = $this->data_store->get_dimension_uom();

        return array_reduce($this->order->get_items(), function ($occ, $item) use ($weight_uom, $dimension_uom) {
            /** @var \WC_Order_Item_Product $item */

            /** @var \WC_Product */
            $product = $item->get_product();
            $quantity = $item->get_quantity();
            $total = floatval($item->get_total());

            //calculate product price after discount (per product)
            $product_price = floatval($product->get_price());
            $height = floatval($product->get_height());
            $width = floatval($product->get_width());
            $length = floatval($product->get_length());
            $weight = floatval($product->get_weight()) * $quantity;
            $weightDimension = $this->convertDimensionToWeight($height, $width, $length, $quantity);
            $maxWeight = $weight > $weightDimension ? $weight : $weightDimension;

            array_push($occ, array(
                'name' => $item->get_name(),
                'description' => $product->get_short_description() ?? $product->get_description(),
                'weight' => $maxWeight,
                'weight_uom' => $weight_uom,
                'qty' => $quantity,
                'value' => $product_price,
                'width' => 0,
                'height' => 0,
                'length' => 0,
                'dimension_uom' => $dimension_uom,
                'total_value' => $total,
            ));

            return $occ;
        }, array());
    }

    private function convertDimensionToWeight($height, $width, $length, $qty) {
        $weight = (($height * $width * $length) / 6000) * $qty;
        $weight = round($weight, 3);
        return $weight;
    }

    private function get_transaction_from($items, $is_cod)
    {
        return array(
            'subtotal' => floatval($this->order->get_subtotal()),
            'shipping_charge' => floatval($this->order->get_shipping_total()),
            'total_value' => floatval($this->order->get_total()),
            'total_cod' => $is_cod ? floatval($this->order->get_total()) : null,
            'weight' => floatval(
                array_reduce($items, function ($occ, $item) {
                    return $occ + $item['weight'];
                }, 0)
            ),
            'width' => floatval(
                array_reduce($items, function ($occ, $item) {
                    return $occ + $item['width'];
                }, 0)
            ),
            'height' => floatval(
                array_reduce($items, function ($occ, $item) {
                    return $occ + $item['height'];
                }, 0)
            ),
            'length' => floatval(
                array_reduce($items, function ($occ, $item) {
                    return $occ + $item['length'];
                }, 0)
            ),
        );
    }

    private function get_transaction_with_discount_insurance($items, $is_cod, $courier)
    {
        return array(
            'subtotal' => floatval($this->order->get_subtotal()),
            'shipping_charge' => floatval($courier['base_price']),
            'total_value' => floatval($this->order->get_total()),
            'total_cod' => $is_cod ? floatval($this->order->get_total()) : null,
            'weight' => floatval(
                array_reduce($items, function ($occ, $item) {
                    return $occ + $item['weight'];
                }, 0)
            ),
            'width' => floatval(
                array_reduce($items, function ($occ, $item) {
                    return $occ + $item['width'];
                }, 0)
            ),
            'height' => floatval(
                array_reduce($items, function ($occ, $item) {
                    return $occ + $item['height'];
                }, 0)
            ),
            'length' => floatval(
                array_reduce($items, function ($occ, $item) {
                    return $occ + $item['length'];
                }, 0)
            ),
            'is_insuranced' => $courier['is_insuranced'],
            'fee_insurance' => floatval($courier['fee_insurance']),
            'discount' => floatval($this->order->get_discount_total()),
        );
    }

    private function get_tenant_id()
    {
        return $this->data_store->get_tenant_id();
    }

    private function get_payment_method()
    {
        return $this->order->get_payment_method();
    }

    private function is_cod()
    {
        return $this->get_payment_method() == 'cod';
    }
}
