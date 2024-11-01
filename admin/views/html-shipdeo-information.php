<?php

/** @var \WC_Order $order */ ?>

<style>
    #order_shipping_line_items > tr > td.wc-order-edit-line-item {
        display: none !important;
    }
</style>

<div>
    <p>
        <label for="shipdeo_v2_delivery_type">
            <?php echo __('Delivery Type', 'shipdeo-v2'); ?>
        </label>
        <?php
        $delivery_type = $order->get_meta('_shipdeo_v2_delivery_type');
        $delivery_type_options = array(
            'pickup' => __('Pickup', 'shipdeo-v2'),
            'dropoff' => __('Drop Off', 'shipdeo-v2'),
        );
        $insurance_options = array(
            true => __('Yes', 'shipdeo-v2'),
            false => __('No', 'shipdeo-v2'),
        );
        ?>
        <?php if ($shipdeo_status == 'ENTRY' && $wc_status != 'completed') : ?>
            <select name="_shipdeo_v2_delivery_type" id="shipdeo_v2_delivery_type" style="width: 100%;">
                <option value=""><?php echo __('Choose an type', 'shipdeo-v2') ?></option>
                <?php foreach ($delivery_type_options as $value => $label) : ?>
                    <option value="<?php echo $value; ?>" <?php echo $delivery_type == $value ? 'selected' : null; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        <?php else : ?>
            <input readonly type="text" value="<?php echo $delivery_type_options[$delivery_type] ?? null; ?>" style="width: 100%;" />
        <?php endif; ?>
    </p>
    <p>
        <label for="shipdeo_v2_insurance">
            <?php echo __('Insurance', 'shipdeo-v2'); ?>
        </label>
        <?php if ($shipdeo_status == 'ENTRY' && $wc_status != 'completed') : ?>
            <select name="_shipdeo_v2_is_insuranced" id="shipdeo_v2_is_insuranced" style="width: 100%;">
                <?php foreach ($insurance_options as $value => $label) : ?>
                    <option value="<?php echo $value; ?>" <?php echo $is_insuranced == $value ? 'selected' : null; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        <?php else : ?>
            <input readonly type="text" value="<?php echo $insurance_options[$is_insuranced] ?? null; ?>" style="width: 100%;" />
        <?php endif; ?>
    </p>
    <p>
        <label for="shipdeo_v2_status">
            <?php echo __('Status', 'shipdeo-v2'); ?>
        </label>
        <input readonly type="text" value="<?php echo $shipdeo_status; ?>" style="width: 100%;" />
    </p>
    <p>
        <label for="shipdeo_v2_payment_method">
            <?php echo __('Payment Method', 'shipdeo-v2'); ?>
        </label>
        <input readonly type="text" value="<?php echo __($order->get_payment_method() == 'cod' ? 'COD' : 'Non COD', 'shipdeo-v2'); ?>" style="width: 100%;" />
    </p>
    <p>
        <label for="shipdeo_v2_airwaybill">
            <?php echo __('Airwaybill', 'shipdeo-v2'); ?>
        </label>
        <input readonly type="text" value="<?php echo $airwaybill; ?>" style="width: 100%;" />
        <?php if ($link) : ?>
            <a href="<?php echo $link; ?>" target="_blank"><?php echo __('Go to tracking page', 'shipdeo-v2'); ?></a>
        <?php endif; ?>
    </p>
    <p>
        <label for="shipdeo_v2_booking_code">
            <?php echo __('Booking Code', 'shipdeo-v2'); ?>
        </label>
        <input readonly type="text" value="<?php echo $order->get_meta('_shipdeo_v2_booking_code'); ?>" style="width: 100%;" />
    </p>
    <?php if (!$order_id) : ?>
        <button type="button" class="button button-primary" id="shipdeo_v2_create_order" data-order_id="<?php echo $order->get_id(); ?>"><?php echo __('Create Order to Shipdeo', 'shipdeo-v2'); ?></button>
    <?php endif; ?>
    <?php if ($order->has_status('processing') && in_array($shipdeo_status, array('ENTRY', 'CONFIRM_PROBLEM'))) : ?>
        <button type="button" class="button button-primary" id="shipdeo_v2_confirm_order" data-order_id="<?php echo $order->get_id(); ?>"><?php echo __('Confirm Order', 'shipdeo-v2'); ?></button>
    <?php endif; ?>
</div>