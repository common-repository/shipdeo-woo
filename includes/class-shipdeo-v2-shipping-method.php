<?php

class Shipdeo_V2_Shipping_Method extends WC_Shipping_Method
{
    public static $prefix = 'shipdeo';
    public static $minimum_total_cod = 25000;

    /**
     * @var Shipdeo_V2_Data_Store
     */
    private $data_store;

    /**
     * @var array
     */
    private $services;

    public function __construct($instance_id = 0)
    {
        $this->data_store = new Shipdeo_V2_Data_Store();
        $this->instance_id = absint($instance_id);
        $this->id = static::$prefix;
        $this->method_title = __('Shipdeo Shipping', 'shipdeo-v2');
        $this->method_description = __('Shipdeo Shipping', 'shipdeo-v2');
        $this->title = __('Shipdeo Shipping', 'shipdeo-v2');
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
        );
        $this->init();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init()
    {
        $this->instance_form_fields = array(
            'services' => array(
                'title' => __('Services', 'shipdeo-v2'),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'options' => $this->get_services_options(),
            ),
        );
        $this->services = $this->get_option('services', array());
    }

    public function get_services_options()
    {
        list('status_code' => $status_code, 'body' => $body) = $this->data_store->get_enabled_couriers();
        $options = array();

        if ($status_code != 200) {
            return $options;
        }

        list('data' => $couriers) = $body;

        foreach ($couriers as $courier) {
            foreach ($courier['services'] as $service) {
                $options[$courier['code'] . '_' . strtolower($service)] = strtoupper($courier['code']) . ' - ' . $service;
            }
        }

        return $options;
    }

    /**
     * calculate_shipping function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping($package = array())
    {
        if ($package['destination'] == null) {
            return;
        }

        if ($package['destination']['country'] != 'ID') {
            return;
        }

        $couriers = $this->get_couriers();

        if (count($couriers) < 1) {
            return;
        }

        $origin = $this->data_store->get_origin();

        if (is_null($origin)) {
            return;
        }

        $destination = $this->get_destination_from($package['destination']);

        if (is_null($destination)) {
            return;
        }

        // $items = $this->get_items_from($package['contents']);
        $items = $this->get_items_with_discount($package['contents'], $package['applied_coupons']);

        if (count($items) < 1) {
            return;
        }

        $options = $this->data_store->get_store_information();
        $is_insuranced = $options["is_insuranced"];
        $is_must_insuranced = $options["is_must_insuranced"];

        list('status_code' => $status_code, 'body' => $body) = $this->data_store->get_shipping_prices(
            $couriers,
            $origin,
            $destination,
            $items,
        );

        if ($status_code != 200) {
            return;
        }

        list('data' => $shipping_prices) = $body;

        foreach ($shipping_prices as $shipping_price) {
            list(
                'courier' => $courier,
                'courierCode' => $code,
                'service' => $service,
                'insuranceValue' => $insurance_value,
                'price' => $price,
                'duration' => $duration,
                'supportCod' => $support_cod
            ) = $shipping_price;

            $service_code = strtolower($code) . '_' . strtolower($service);

            if (!in_array($service_code, $this->get_services())) {
                continue;
            }

            $encrypted_data = Shipdeo_V2_Encryptor::encrypt(
                json_encode(
                    array(
                        'code' => $code,
                        'service' => $service,
                        'support_cod' => $support_cod,
                        'origin_subdistrict_code' => $origin['subdistrict_code'],
                        'destination_subdistrict_code' => $destination['subdistrict_code'],
                        'postal_code' => $destination['postal_code']
                    )
                )
            );


            //refactor code, create as a function
            //
            // $id = static::$prefix . '_' . $encrypted_data;
            // $this->add_rate(array(
            //     'id' => $id,
            //     'label' => $courier . ' - ' . $service,
            //     'cost' => $price,
            //     'taxes' => false,
            //     'meta_data' => array(
            //         'code' => $code,
            //         'service' => $service,
            //         'duration' => $duration,
            //         'origin_subdistrict_code' => $origin['subdistrict_code'],
            //         'destination_subdistrict_code' => $destination['subdistrict_code'],
            //     ),
            //     'package' => $package,
            // ));

            $id = static::$prefix . '_' . $encrypted_data;
            if($is_must_insuranced === 'false'){
                $shipping_rate = $this->generate_shipping_rate($id, $courier, $service, $price, false, $insurance_value,
                    $code, $duration, $origin['subdistrict_code'], $destination['subdistrict_code']);
                $this->add_rate($shipping_rate);
            }

            if($is_insuranced === 'true' && $insurance_value > 0){
                $shipping_rate = $this->generate_shipping_rate_with_insurance($id, $courier, $service, $price, false, $insurance_value,
                    $code, $duration, $origin['subdistrict_code'], $destination['subdistrict_code']);
                $this->add_rate($shipping_rate);
            }
        }
    }

    private function generate_shipping_rate($id, $courier, $service, $price, $tax, $insurance_value,
        $code, $duration, $origin_subdistrict_code, $destination_subdistrict_code){
        $duration_text = $this->mapping_duration($duration);
        return array(
            'id' => $id,
            'label' => $courier . ' - ' . $service . ' '.$duration_text,
            'cost' => $price,
            'taxes' => $tax,
            'meta_data' => array(
                'code' => $code,
                'service' => $service,
                'base_price' => $price,
                'is_insuranced' => false,
                'fee_insurance' => $insurance_value,
                'duration' => $duration,
                'origin_subdistrict_code' => $origin_subdistrict_code,
                'destination_subdistrict_code' => $destination_subdistrict_code,
            ),
        );
    }

    private function generate_shipping_rate_with_insurance($id, $courier, $service, $price, $tax, $insurance_value,
        $code, $duration, $origin_subdistrict_code, $destination_subdistrict_code){
        $duration_text = $this->mapping_duration($duration);
        return array(
            'id' => $id.'_insurance',
            'label' => $courier . ' - ' . $service . ' with Insurance'.' '.$duration_text,
            'cost' => $price + $insurance_value,
            'taxes' => $tax,
            'meta_data' => array(
                'code' => $code,
                'service' => $service,
                'base_price' => $price,
                'is_insuranced' => true,
                'fee_insurance' => $insurance_value,
                'duration' => $duration,
                'origin_subdistrict_code' => $origin_subdistrict_code,
                'destination_subdistrict_code' => $destination_subdistrict_code,
            ),
        );
    }

    private function get_couriers()
    {
        $couriers = [];

        foreach ($this->services as $service) {
            list($courier) = explode('_', $service);

            if (!in_array($courier, $couriers)) {
                array_push($couriers, $courier);
            }
        }

        return $couriers;
    }

    private function get_services()
    {
        return $this->services;
    }

    public function get_destination_from($package_destination)
    {
        list(
            'state' => $province,
            'city' => $city,
            'address_2' => $subdistrict,
            'postcode' => $postal_code,
        ) = $package_destination;

        list('status_code' => $status_code, 'body' => $body) = $this->data_store->validate_location(
            $province,
            $city,
            $subdistrict,
            $postal_code
        );

        if ($status_code != 200) {
            return null;
        }

        list('data' => $data) = $body;
        list(
            'code' => $code,
            'province' => $province,
            'city' => $city,
            'subdistrict' => $subdistrict,
            'isLocationCrossCheck' => $is_location_cross_check,
            'pointSubdistrict' => $point_subdistrict,
        ) = $data;

        if ($is_location_cross_check || $point_subdistrict > 0) {
            return null;
        }

        return array_merge(
            $this->data_store->get_location_codes_from($code),
            array(
                'subdistrict_name' => $subdistrict,
                'city_name' => $city,
                'province_name' => $province,
                'postal_code' => $postal_code,
            )
        );
    }

    public function get_items_from($package_contents)
    {
        $weight_uom = $this->data_store->get_weight_uom();
        $dimension_uom = $this->data_store->get_dimension_uom();

        return array_reduce($package_contents, function ($occ, $package_content) use ($weight_uom, $dimension_uom) {
            $product = $package_content['data'];
            $quantity = intval($package_content['quantity']);

            array_push($occ, array(
                'name' => $product->get_name(),
                'weight' => floatval($product->get_weight()) * $quantity,
                'weight_uom' => $weight_uom,
                'qty' => $quantity,
                'base_price' => floatval($product->get_price()),
                'value' => floatval($product->get_price()),
                'width' => floatval($product->get_width()),
                'height' => floatval($product->get_height()),
                'length' => floatval($product->get_length()),
                'dimension_uom' => $dimension_uom,
            ));

            return $occ;
        }, array());
    }

    public function get_items_with_discount($package_contents, $coupons)
    {
        $weight_uom = $this->data_store->get_weight_uom();
        $dimension_uom = $this->data_store->get_dimension_uom();

        $items = array_reduce($package_contents, function ($occ, $package_content) use ($weight_uom, $dimension_uom, $coupons) {
            $product = $package_content['data'];
            $quantity = intval($package_content['quantity']);
            $product_id = $product->get_id();
            $product_price = floatval($product->get_price());

            //summary product price after discount (Rp.)
            $product_price_total = floatval($package_content['line_total']);

            //calculate product price after discount
            //product price (per product) not summary
            $product_price_discount = $product_price_total / $quantity;

            $height = floatval($product->get_height());
            $width = floatval($product->get_width());
            $length = floatval($product->get_length());
            $weight = floatval($product->get_weight()) * $quantity;
            $weightDimension = $this->convertDimensionToWeight($height, $width, $length, $quantity);
            $maxWeight = $weight > $weightDimension ? $weight : $weightDimension;

            array_push($occ, array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'weight' => $maxWeight,
                'weight_uom' => $weight_uom,
                'qty' => $quantity,
                'base_price' => floatval($product->get_price()),
                'value' => $product_price,
                'width' => 0,
                'height' => 0,
                'length' => 0,
                'dimension_uom' => $dimension_uom,
                'total_value' => $product_price_discount,
            ));

            return $occ;
        }, array());

        error_log(json_encode($items));
        return $items;
    }

    private function convertDimensionToWeight($height, $width, $length, $qty) {
        $weight = (($height * $width * $length) / 6000) * $qty;
        $weight = round($weight, 3);
        return $weight;
    }

    private function mapping_duration($duration)
    {
        $duration_str = strtoupper($duration);
        $duration_str = str_replace('DAY', '', $duration_str);
        $duration_str = str_replace('HARI', '', $duration_str);
        $duration_str = str_replace('D', '', $duration_str);
        $duration_str = trim($duration_str, " ");
        $split_str = explode("-", $duration_str);
        $result = "";
        if(count($split_str) == 0 || $split_str[0] == '') {
            return $result;
        }

        if(count($split_str) == 1) {
        $result = $split_str[0] == '0' ? 'Hari ini' : $split_str[0]. ' hari';
            return '('.$result.')';
        }

        $result = $split_str[0] == '0' ? 'Hari ini' : $split_str[0];
        $result = count($split_str) > 1 ?
                    ($split_str[0] == $split_str[1] ? $split_str[0].' hari' : $result .' - '.$split_str[1].' hari') :
                    $result;
        return '('.$result.')';
    }
}
