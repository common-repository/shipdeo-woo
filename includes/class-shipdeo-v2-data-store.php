<?php

class Shipdeo_V2_Data_Store
{
    protected function get_main_base_url()
    {
        switch (SHIPDEO_V2_ENV) {
            case SHIPDEO_V2_ENV_PRODUCTION:
                return 'https://main-api-production.shipdeo.com';
            case SHIPDEO_V2_ENV_UAT:
                return 'https://main-api-uat.shipdeo.com';
            default:
                return 'https://main-api-development.shipdeo.com';
        }
    }

    protected function get_auth_base_url()
    {
        switch (SHIPDEO_V2_ENV) {
            case SHIPDEO_V2_ENV_PRODUCTION:
                return 'https://auth-api-production.shipdeo.com';
            case SHIPDEO_V2_ENV_UAT:
                return 'https://auth-api-uat.shipdeo.com';
            default:
                return 'https://auth-api-development.shipdeo.com';
        }
    }

    protected function get_response($response)
    {
        return [
            'status_code' => wp_remote_retrieve_response_code($response),
            'body' => json_decode(
                wp_remote_retrieve_body($response),
                true
            ),
        ];
    }

    protected function set_access_token($access_token, $expire_in)
    {
        set_transient('shipdeo_v2_access_token', $access_token, $expire_in);

        return $this->get_access_token();
    }

    public function get_access_token()
    {
        return get_transient('shipdeo_v2_access_token');
    }

    public function delete_access_token()
    {
        return delete_transient('shipdeo_v2_access_token');
    }

    public function set_credential($client_id, $client_secret)
    {
        update_option('shipdeo_v2_credential', array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ));

        return $this->get_credential();
    }

    public function get_credential()
    {
        return get_option('shipdeo_v2_credential');
    }

    public function delete_credential()
    {
        return delete_option('shipdeo_v2_credential');
    }

    public function get_hashed_credential()
    {
        $credential = $this->get_credential();

        return $credential
            ? hash('sha256', json_encode($credential))
            : null;
    }

    public function set_tenant_id($tenant_id)
    {
        update_option('shipdeo_v2_tenant_id', $tenant_id);

        return $this->get_tenant_id();
    }

    public function get_tenant_id()
    {
        return get_option('shipdeo_v2_tenant_id');
    }

    public function set_store_information(
        $store_name,
        $phone,
        $email,
        $subdistrict_code,
        $subdistrict_name,
        $is_insuranced,
		$is_must_insuranced
    ) {
        update_option('shipdeo_v2_store_information', array(
            'store_name' => $store_name,
            'phone' => $phone,
            'email' => $email,
            'subdistrict_code' => $subdistrict_code,
            'subdistrict_name' => $subdistrict_name,
            'is_insuranced' => $is_insuranced,
			'is_must_insuranced' => $is_must_insuranced
        ));

        return $this->get_store_information();
    }

    public function get_store_information()
    {
        return get_option('shipdeo_v2_store_information');
    }

    public function delete_store_information()
    {
        return delete_option('shipdeo_v2_store_information');
    }

    public function login($client_id, $client_secret)
    {
        $response = wp_remote_request(
            $this->get_auth_base_url() . '/oauth2/connect/token',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode([
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type' => 'client_credentials',
                ]),
            )
        );

        $result = $this->get_response($response);

        list('status_code' => $status_code, 'body' => $body) = $result;

        if ($status_code == 200) {
            $this->set_tenant_id($body['client']['appId']);
            $this->set_access_token(
                $body['accessToken'],
                strtotime($body['accessTokenExpiresAt'])
            );
        } else {
            $this->delete_access_token();
        }

        return $result;
    }

    protected function authorized_request($url, $options)
    {
        if (!$this->get_access_token()) {
            $credential = $this->get_credential();

            if (!$credential) {
                return;
            }

            list('client_id' => $client_id, 'client_secret' => $client_secret) = $credential;
            list('status_code' => $status_code) = $this->login($client_id, $client_secret);

            if ($status_code != 200) {
                return;
            }
        }

        list('headers' => $headers) = $options;
        $headers['Authorization'] = 'Bearer ' . $this->get_access_token();

        $response = wp_remote_request(
            $url,
            array(
                'method' => $options['method'],
                'headers' => $headers,
                'body' => isset($options['body']) ? $options['body'] : null,
            )
        );

        $result = $this->get_response($response);
        list('status_code' => $status_code) = $result;

        if ($status_code == 401) {
            $this->delete_access_token();

            return $this->authorized_request($url, $options);
        }

        return $result;
    }

    public function get_subdistricts_by_name($name)
    {
        $result = $this->authorized_request(
            $this->get_main_base_url() . '/v1/master/locations',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode([
                    'type' => 'subdistrict',
                    'name' => $name,
                    'pagination' => [
                        'limit' => 100,
                    ],
                ])
            )
        );

        if ($result['status_code'] == 200) {
            $state_codes = array(
                strtoupper('Daerah Istimewa Aceh') => 'AC',
                strtoupper('Aceh') => 'AC',
                strtoupper('Sumatera Utara') => 'SU',
                strtoupper('Sumatera Barat') => 'SB',
                strtoupper('Riau') => 'RI',
                strtoupper('Kepulauan Riau') => 'KR',
                strtoupper('Jambi') => 'JA',
                strtoupper('Sumatera Selatan') => 'SS',
                strtoupper('Bangka Belitung') => 'BB',
                strtoupper('Bengkulu') => 'BE',
                strtoupper('Lampung') => 'LA',
                strtoupper('DKI Jakarta') => 'JK',
                strtoupper('Jawa Barat') => 'JB',
                strtoupper('Banten') => 'BT',
                strtoupper('Jawa Tengah') => 'JT',
                strtoupper('Jawa Timur') => 'JI',
                strtoupper('Daerah Istimewa Yogyakarta') => 'YO',
                strtoupper('Bali') => 'BA',
                strtoupper('Nusa Tenggara Barat') => 'NB',
                strtoupper('Nusa Tenggara Timur') => 'NT',
                strtoupper('Kalimantan Barat') => 'KB',
                strtoupper('Kalimantan Tengah') => 'KT',
                strtoupper('Kalimantan Timur') => 'KI',
                strtoupper('Kalimantan Selatan') => 'KS',
                strtoupper('Kalimantan Utara') => 'KU',
                strtoupper('Sulawesi Utara') => 'SA',
                strtoupper('Sulawesi Tengah') => 'ST',
                strtoupper('Sulawesi Tenggara') => 'SG',
                strtoupper('Sulawesi Barat') => 'SR',
                strtoupper('Sulawesi Selatan') => 'SN',
                strtoupper('Gorontalo') => 'GO',
                strtoupper('Maluku') => 'MA',
                strtoupper('Maluku Utara') => 'MU',
                strtoupper('Papua') => 'PA',
                strtoupper('Papua Barat') => 'PB',
            );

            list('body' => $body) = $result;
            list('data' => $data) = $body;

            $result['body']['data'] = array_map(function ($row) use ($state_codes) {
                return array(
                    'subdistrict_code' => $row['subdistrict_code'],
                    'subdistrict_name' => $row['subdistrict_name'],
                    'city_code' => $row['city']['city_code'],
                    'city_name' => $row['city']['city_name'],
                    'province_code' => $row['city']['province']['province_code'],
                    'province_name' => $row['city']['province']['province_name'],
                    'province_id' => strtoupper($state_codes[$row['city']['province']['province_name']] ?? $row['city']['province']['province_name']),
                );
            }, $data);
        }

        return $result;
    }

    public function get_cities_by_name($name)
    {
        $result = $this->authorized_request(
            $this->get_main_base_url() . '/v1/master/locations',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode([
                    'type' => 'city',
                    'name' => $name,
                    'pagination' => [
                        'limit' => 100,
                    ],
                ])
            )
        );

        if ($result['status_code'] == 200) {
            list('body' => $body) = $result;
            list('data' => $data) = $body;

            $result['body']['data'] = array_map(function ($row) {
                return array(
                    'city_code' => $row['city_code'],
                    'city_name' => $row['city_name'],
                    'province_code' => $row['province']['province_code'],
                    'province_name' => $row['province']['province_name'],
                );
            }, $data);
        }

        return $result;
    }

    public function get_states_from($state_code)
    {
        return array(
            'AC' => strtoupper('Aceh'),
            'SU' => strtoupper('Sumatera Utara'),
            'SB' => strtoupper('Sumatera Barat'),
            'RI' => strtoupper('Riau'),
            'KR' => strtoupper('Kepulauan Riau'),
            'JA' => strtoupper('Jambi'),
            'SS' => strtoupper('Sumatera Selatan'),
            'BB' => strtoupper('Bangka Belitung'),
            'BE' => strtoupper('Bengkulu'),
            'LA' => strtoupper('Lampung'),
            'JK' => strtoupper('DKI Jakarta'),
            'JB' => strtoupper('Jawa Barat'),
            'BT' => strtoupper('Banten'),
            'JT' => strtoupper('Jawa Tengah'),
            'JI' => strtoupper('Jawa Timur'),
            'YO' => strtoupper('Daerah Istimewa Yogyakarta'),
            'BA' => strtoupper('Bali'),
            'NB' => strtoupper('Nusa Tenggara Barat'),
            'NT' => strtoupper('Nusa Tenggara Timur'),
            'KB' => strtoupper('Kalimantan Barat'),
            'KT' => strtoupper('Kalimantan Tengah'),
            'KI' => strtoupper('Kalimantan Timur'),
            'KS' => strtoupper('Kalimantan Selatan'),
            'KU' => strtoupper('Kalimantan Utara'),
            'SA' => strtoupper('Sulawesi Utara'),
            'ST' => strtoupper('Sulawesi Tengah'),
            'SG' => strtoupper('Sulawesi Tenggara'),
            'SR' => strtoupper('Sulawesi Barat'),
            'SN' => strtoupper('Sulawesi Selatan'),
            'GO' => strtoupper('Gorontalo'),
            'MA' => strtoupper('Maluku'),
            'MU' => strtoupper('Maluku Utara'),
            'PA' => strtoupper('Papua'),
            'PB' => strtoupper('Papua Barat'),
        )[$state_code];
    }

    public function get_couriers()
    {
        return $this->authorized_request(
            $this->get_main_base_url() . '/v1/couriers',
            array(
                'method' => 'GET',
                'headers' => array(
                    'Content-Type' => 'application/json',
                )
            )
        );
    }

    public function get_enabled_couriers()
    {
        $transient_key = __METHOD__;
        $cache = get_transient($transient_key);

        if ($cache) {
            return $cache;
        }

        list('status_code' => $status_code, 'body' => $body) = $this->authorized_request(
            $this->get_main_base_url() . '/v1/couriers/list',
            array(
                'method' => 'GET',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
            )
        );

        if ($status_code != 200) {
            return;
        }

        $enabled_couriers = $body['data']['courierList'];
        $result = $this->get_couriers();
        list('status_code' => $status_code, 'body' => $body) = $result;

        if ($status_code == 200) {
            list('data' => $data) = $body;
            list('list' => $couriers) = $data;

            $result['body']['data'] = array_reduce($couriers, function ($occ, $courier) use ($enabled_couriers) {
                if (in_array($courier['code'], $enabled_couriers)) {
                    array_push($occ, $courier);
                }

                return $occ;
            }, array());

            set_transient($transient_key, $result, MINUTE_IN_SECONDS);
        }

        return $result;
    }

    public function get_origin()
    {
        $postal_code = get_option('woocommerce_store_postcode');
        $address = get_option('woocommerce_store_address');

        if (!$postal_code || !$address) {
            return null;
        }

        list(
            'store_name' => $store_name,
            'phone' => $phone,
            'subdistrict_code' => $store_subdistrict_code,
            'subdistrict_name' => $store_subdistrict_name,
        ) = $this->get_store_information();

        list(
            $origin_subdistrict_name,
            $origin_city_name,
            $origin_province_name,
        ) = explode(', ', $store_subdistrict_name);

        return array_merge(
            $this->get_location_codes_from($store_subdistrict_code),
            array(
                'subdistrict_name' => $origin_subdistrict_name,
                'city_name' => $origin_city_name,
                'province_name' => $origin_province_name,
                'postal_code' => $postal_code,
                'name' => $store_name,
                'phone' => $phone,
                'address' => join(
                    ', ',
                    array(
                        $address,
                        $origin_subdistrict_name,
                        $origin_city_name,
                        $origin_province_name,
                        $postal_code,
                    )
                ),
            )
        );
    }

    public function get_location_codes_from($subdistrict_code)
    {
        return array(
            'subdistrict_code' => $subdistrict_code,
            'city_code' => substr($subdistrict_code, 0, 5),
            'province_code' => substr($subdistrict_code, 0, 2),
        );
    }

    public function get_weight_uom()
    {
        return [
            'kg' => 'kg',
            'g' => 'gram',
            'lbs' => 'lbs',
            'oz' => 'oz',
        ][get_option('woocommerce_weight_unit')];
    }

    public function get_dimension_uom()
    {
        return get_option('woocommerce_dimension_unit');
    }

    public function get_shipping_prices(
        $couriers,
        $origin,
        $destination,
        $items
    ) {
        $required_origin_keys = array(
            'subdistrict_code',
            'subdistrict_name',
            'city_code',
            'city_name',
            'province_code',
            'province_name',
            'postal_code',
        );

        foreach ($required_origin_keys as $key) {
            if (!array_key_exists($key, $origin)) {
                throw new Exception('Origin ' . $key . ' is required', 403);
            }
        }

        $required_destination_keys = array(
            'subdistrict_code',
            'subdistrict_name',
            'city_code',
            'city_name',
            'province_code',
            'province_name',
            'postal_code',
        );

        foreach ($required_destination_keys as $key) {
            if (!array_key_exists($key, $destination)) {
                throw new Exception('Destination ' . $key . ' is required', 403);
            }
        }

        $required_item_keys = array(
            'name',
            'weight',
            'weight_uom',
            'qty',
            'value',
            'width',
            'height',
            'length',
            'dimension_uom',
        );

        $default_item = array(
            'is_insuranced' => false,
            'is_wood_package' => false,
        );

        $items = array_reduce($items, function ($occ, $item) use ($required_item_keys, $default_item) {
            foreach ($required_item_keys as $key) {
                if (!array_key_exists($key, $item)) {
                    throw new Exception('Item ' . $key . ' is required', 403);
                }
            }

            array_push($occ, array_merge(
                $item,
                $default_item,
            ));

            return $occ;
        }, array());

        $body = json_encode(array(
            'couriers' => $couriers,
            'origin_lat' => isset($origin['lat']) ? $origin['lat'] : null,
            'origin_long' => isset($origin['long']) ? $origin['long'] : null,
            'origin_province_name' => $origin['province_name'],
            'origin_province_code' => $origin['province_code'],
            'origin_city_name' => $origin['city_name'],
            'origin_city_code' => $origin['city_code'],
            'origin_subdistrict_name' => $origin['subdistrict_name'],
            'origin_subdistrict_code' => $origin['subdistrict_code'],
            'origin_postal_code' => $origin['postal_code'],
            'destination_lat' => isset($destination['lat']) ? $destination['lat'] : null,
            'destination_long' => isset($destination['long']) ? $destination['long'] : null,
            'destination_province_name' => $destination['province_name'],
            'destination_province_code' => $destination['province_code'],
            'destination_city_name' => $destination['city_name'],
            'destination_city_code' => $destination['city_code'],
            'destination_subdistrict_name' => $destination['subdistrict_name'],
            'destination_subdistrict_code' => $destination['subdistrict_code'],
            'destination_postal_code' => $destination['postal_code'],
            'is_cod' => false,
            'items' => $items,
        ));
        $body_hash = hash('sha256', $body);
        $transient_key = __METHOD__ . ':' . $body_hash;
        $cache = get_transient($transient_key);

        if ($cache) {
            return $cache;
        }

        $result = $this->authorized_request(
            $this->get_main_base_url() . '/v1/couriers/pricing',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => $body
            )
        );

        if ($result['status_code'] == 200) {
            set_transient($transient_key, $result, 10);
        }

        return $result;
    }

    public function validate_location(
        $province,
        $city,
        $subdistrict,
        $postal_code
    ) {
        $body = json_encode(array(
            'channel_id' => 'shopify',
            'province' => $province,
            'city' => $city,
            'subdistrict' => $subdistrict,
            'postal_code' => $postal_code,
            'isStrong' => true,
        ));
        $body_hash = hash('sha256', $body);
        $transient_key = __METHOD__ . ':' . $body_hash;
        $cache = get_transient($transient_key);

        if ($cache) {
            return $cache;
        }

        $result = $this->authorized_request(
            $this->get_main_base_url() . '/master/locations/validate/plugin',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => $body,
            )
        );

        if ($result['status_code'] == 200) {
            set_transient($transient_key, $result, 10);
        }

        return $result;
    }

    public function create_order(
        $order_number,
        $courier,
        $origin,
        $destination,
        $items,
        $transaction,
        $pickup_date,
        $tenant_id,
        $is_cod,
        $note = null
    ) {
        $required_courier_keys = array(
            'code',
            'service',
        );

        foreach ($required_courier_keys as $key) {
            if (!array_key_exists($key, $courier)) {
                throw new Exception('Courier ' . $key . ' is required', 403);
            }
        }

        $required_origin_keys = array(
            'subdistrict_code',
            'subdistrict_name',
            'city_code',
            'city_name',
            'province_code',
            'province_name',
            'postal_code',
            'name',
            'phone',
            'address',
        );

        foreach ($required_origin_keys as $key) {
            if (!array_key_exists($key, $origin)) {
                throw new Exception('Origin ' . $key . ' is required', 403);
            }
        }

        $required_destination_keys = array(
            'subdistrict_code',
            'subdistrict_name',
            'city_code',
            'city_name',
            'province_code',
            'province_name',
            'postal_code',
            'name',
            'phone',
            'address',
        );

        foreach ($required_destination_keys as $key) {
            if (!array_key_exists($key, $destination)) {
                throw new Exception('Destination ' . $key . ' is required', 403);
            }
        }

        $required_item_keys = array(
            'name',
            'weight',
            'weight_uom',
            'qty',
            'value',
            'width',
            'height',
            'length',
            'dimension_uom',
        );

        $items = array_reduce($items, function ($occ, $item) use ($required_item_keys) {
            foreach ($required_item_keys as $key) {
                if (!array_key_exists($key, $item)) {
                    throw new Exception('Item ' . $key . ' is required', 403);
                }
            }

            array_push($occ, $item);

            return $occ;
        }, array());

        $required_transaction_keys = array(
            'subtotal',
            'shipping_charge',
            'total_value',
            'weight',
            'width',
            'height',
            'length',
        );

        foreach ($required_transaction_keys as $key) {
            if (!array_key_exists($key, $transaction)) {
                throw new Exception('Transaction ' . $key . ' is required', 403);
            }
        }

        $body = json_encode(array(
            'origin_lat' => isset($origin['lat']) ? $origin['lat'] : null,
            'origin_long' => isset($origin['long']) ? $origin['long'] : null,
            'origin_province_name' => $origin['province_name'],
            'origin_province_code' => $origin['province_code'],
            'origin_city_name' => $origin['city_name'],
            'origin_city_code' => $origin['city_code'],
            'origin_subdistrict_name' => $origin['subdistrict_name'],
            'origin_subdistrict_code' => $origin['subdistrict_code'],
            'origin_postal_code' => $origin['postal_code'],
            'origin_contact_name' => $origin['name'],
            'origin_contact_phone' => $origin['phone'],
            'origin_contact_address' => $origin['address'],
            'destination_lat' => isset($destination['lat']) ? $destination['lat'] : null,
            'destination_long' => isset($destination['long']) ? $destination['long'] : null,
            'destination_province_name' => $destination['province_name'],
            'destination_province_code' => $destination['province_code'],
            'destination_city_name' => $destination['city_name'],
            'destination_city_code' => $destination['city_code'],
            'destination_subdistrict_name' => $destination['subdistrict_name'],
            'destination_subdistrict_code' => $destination['subdistrict_code'],
            'destination_postal_code' => $destination['postal_code'],
            'destination_contact_name' => $destination['name'],
            'destination_contact_phone' => $destination['phone'],
            'destination_contact_address' => $destination['address'],
            'is_cod' => $is_cod,
            'items' => $items,
            'order_number' => $order_number,
            'tenant_id' => $tenant_id,
            'courier' => $courier['code'],
            'courier_service' => $courier['service'],
            'transaction' => array_merge(
                array(
                    'coolie' => 1,
                    'package_category' => 'normal',
                    'package_content' => '-',
                ),
                $transaction,
            ),
            'delivery_type' => 'pickup',
            'delivery_time' => $pickup_date,
            'delivery_note' => !is_null($note) ? $note : null,
        ));

        return $this->authorized_request(
            $this->get_main_base_url() . '/v1/couriers/orders',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => $body,
            )
        );
    }

    public function update_order(
        $tenant_id,
        $order_id,
        $order,
        $courier,
        $origin,
        $destination,
        $items,
        $transaction,
        $is_cod,
        $note = null
    ) {
        $required_courier_keys = array(
            'code',
            'service',
        );

        foreach ($required_courier_keys as $key) {
            if (!array_key_exists($key, $courier)) {
                throw new Exception('Courier ' . $key . ' is required', 403);
            }
        }

        $required_origin_keys = array(
            'subdistrict_code',
            'subdistrict_name',
            'city_code',
            'city_name',
            'province_code',
            'province_name',
            'postal_code',
            'name',
            'phone',
            'address',
        );

        foreach ($required_origin_keys as $key) {
            if (!array_key_exists($key, $origin)) {
                throw new Exception('Origin ' . $key . ' is required', 403);
            }
        }

        $required_destination_keys = array(
            'subdistrict_code',
            'subdistrict_name',
            'city_code',
            'city_name',
            'province_code',
            'province_name',
            'postal_code',
            'name',
            'phone',
            'address',
        );

        foreach ($required_destination_keys as $key) {
            if (!array_key_exists($key, $destination)) {
                throw new Exception('Destination ' . $key . ' is required', 403);
            }
        }

        $required_item_keys = array(
            'name',
            'weight',
            'weight_uom',
            'qty',
            'value',
            'width',
            'height',
            'length',
            'dimension_uom',
        );

        $items = array_reduce($items, function ($occ, $item) use ($required_item_keys) {
            foreach ($required_item_keys as $key) {
                if (!array_key_exists($key, $item)) {
                    throw new Exception('Item ' . $key . ' is required', 403);
                }
            }

            array_push($occ, $item);

            return $occ;
        }, array());

        $required_transaction_keys = array(
            'subtotal',
            'shipping_charge',
            'total_value',
            'weight',
            'width',
            'height',
            'length',
        );

        foreach ($required_transaction_keys as $key) {
            if (!array_key_exists($key, $transaction)) {
                throw new Exception('Transaction ' . $key . ' is required', 403);
            }
        }

        $body = json_encode(array(
            'origin_lat' => isset($origin['lat']) ? $origin['lat'] : null,
            'origin_long' => isset($origin['long']) ? $origin['long'] : null,
            'origin_province_name' => $origin['province_name'],
            'origin_province_code' => $origin['province_code'],
            'origin_city_name' => $origin['city_name'],
            'origin_city_code' => $origin['city_code'],
            'origin_subdistrict_name' => $origin['subdistrict_name'],
            'origin_subdistrict_code' => $origin['subdistrict_code'],
            'origin_postal_code' => $origin['postal_code'],
            'origin_contact_name' => $origin['name'],
            'origin_contact_phone' => $origin['phone'],
            'origin_contact_address' => $origin['address'],
            'destination_lat' => isset($destination['lat']) ? $destination['lat'] : null,
            'destination_long' => isset($destination['long']) ? $destination['long'] : null,
            'destination_province_name' => $destination['province_name'],
            'destination_province_code' => $destination['province_code'],
            'destination_city_name' => $destination['city_name'],
            'destination_city_code' => $destination['city_code'],
            'destination_subdistrict_name' => $destination['subdistrict_name'],
            'destination_subdistrict_code' => $destination['subdistrict_code'],
            'destination_postal_code' => $destination['postal_code'],
            'destination_contact_name' => $destination['name'],
            'destination_contact_phone' => $destination['phone'],
            'destination_contact_address' => $destination['address'],
            'is_cod' => $is_cod,
            'items' => $items,
            'order_number' => $order->get_order_number(),
            'tenant_id' => $tenant_id,
            'courier' => $courier['code'],
            'courier_service' => $courier['service'],
            'transaction' => array_merge(
                array(
                    'coolie' => 1,
                    'package_category' => 'normal',
                    'package_content' => '-',
                ),
                $transaction,
            ),
            'delivery_type' => $order->get_meta('_shipdeo_v2_delivery_type'),
            'delivery_time' => $order->get_date_created()->date('Y-m-d H:i:s'),
            'delivery_note' => !is_null($note) ? $note : null,
        ));

        return $this->authorized_request(
            $this->get_main_base_url() . '/v1/couriers/orders/'.$order_id,
            array(
                'method' => 'PUT',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => $body,
            )
        );
    }

    public function confirm_order(
        $order_id,
        $delivery_type
    ) {
        return $this->authorized_request(
            $this->get_main_base_url() . '/v1/couriers/orders/' . $order_id,
            array(
                'method' => 'PATCH',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'delivery_type' => $delivery_type,
                )),
            )
        );
    }

    public function get_track_airwaybill_url(
        $courier_code,
        $airwaybill
    ) {
        return "https://shipdeo.com/tracking-airwaybill?{$courier_code}&{$airwaybill}";
    }

    public function cancel_order($order_id)
    {
        return $this->authorized_request(
            $this->get_main_base_url() . '/v1/couriers/orders/' . $order_id,
            array(
                'method' => 'DELETE',
            )
        );
    }

    /**
     * @param array $order_ids
     * @param string $mode default|thermal
     * @return array
     */
    public function get_shipping_label_url($order_ids, $mode = 'default')
    {
        $template = array(
            'format' => 'a4',
        );

        if ($mode == 'thermal') {
            $template = array(
                'width' => '80mm',
                'height' => '100mm',
                'marginTop' => '10px',
                'marginRight' => '10px',
                'marginBottom' => '10px',
                'marginLeft' => '10px',
            );
        }

        $body = json_encode(array(
            'display_type' => 'pdf',
            'additional_info' => array(
                'showCompanyLogo' => true,
                'printThermal' => $mode == 'thermal',
            ),
            'return_link_only' => true,
            'ordersId' => $order_ids,
            'showField' => array(
                'orderDetail' => true,
                'senderAddress' => true,
                'tenantLogo' => true,
                'showBarcodeNoOrder' => true,
                'showBarcodeTrackingNumber' => true,
                'showCreatedDateRelated' => true,
                'showShippingCharge' => true,
            ),
            'config' => array(
                'template' => array_merge(
                    array(
                        'marginsType' => 0,
                        'landscape' => false,
                        'printBackground' => false,
                        'fitToPage' => true,
                    ),
                    $template
                ),
            ),
        ));

        return $this->authorized_request(
            $this->get_main_base_url() . '/v1/shipping-label/plugins',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => $body,
            )
        );
    }

    /**
     * @param array $order_ids
     * @return array
     */
    public function get_shipping_label_url_v2($order_ids)
    {
        $body = json_encode(array(
            'ordersId' => $order_ids,
        ));

        return $this->authorized_request(
            $this->get_main_base_url() . '/v2/shipping-label',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => $body,
            )
        );
    }

    public function setup_webhook($url)
    {
        return $this->authorized_request(
            $this->get_main_base_url() . '/v1/webhook/setup',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'service' => 'order',
                    'url' => $url,
                    'webhookSecret' => $this->get_hashed_credential(),
                ))
            )
        );
    }

    public function get_cod_coverage(
        $courier_code,
        $origin_subdistrict_code,
        $destination_subdistrict_code,
        $postal_code = null
    ) {
        $body = json_encode(array(
            'courier' => $courier_code,
            'origin_subdistrict_code' => $origin_subdistrict_code,
            'destination_code' => $destination_subdistrict_code,
            'postal_code' => $postal_code,
        ));
        $body_hash = hash('sha256', $body);
        $transient_key = __METHOD__ . ':' . $body_hash;
        $cache = get_transient($transient_key);

        if ($cache) {
            return $cache;
        }

        $result = $this->authorized_request(
            $this->get_main_base_url() . '/v1/courier/cod/coverage',
            array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => $body
            )
        );

        if ($result['status_code'] == 200) {
            set_transient($transient_key, $result, 10);
        }

        return $result;
    }
}
