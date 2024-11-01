<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <h2 id="shipdeo_v2_oauth"><?php echo __('Credential', 'shipdeo-v2') ?></h2>

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="shipdeo_v2_oauth" />
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('shipdeo_v2_oauth'); ?>" />

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="shipdeo_v2_client_id"><?php echo __('Client ID', 'shipdeo-v2') ?></label>
                    </th>
                    <td>
                        <input required type="text" name="client_id" id="shipdeo_v2_client_id" class="regular-text" value="<?php echo isset($credential['client_id']) ? $credential['client_id'] : null ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shipdeo_v2_client_secret"><?php echo __('Client Secret', 'shipdeo-v2') ?></label>
                    </th>
                    <td>
                        <input required type="text" name="client_secret" id="shipdeo_v2_client_secret" class="regular-text" value="<?php echo isset($credential['client_secret']) ? $credential['client_secret'] : null ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php echo __('Status', 'shipdeo-v2'); ?></label>
                    </th>
                    <td>
                        <?php if (isset($_GET['error_message'])) : ?>
                            <span style="font-weight: bold; color: red;"><?php echo $_GET['error_message']; ?></span>
                        <?php endif; ?>
                        <?php if (isset($_GET['success_message'])) : ?>
                            <span style="font-weight: bold; color: green;"><?php echo $_GET['success_message']; ?></span>
                        <?php endif; ?>
                        <?php if (!isset($_GET['error_message']) && !isset($_GET['success_message'])) : ?>
                            <?php if ($access_token) : ?>
                                <span style="font-weight: bold; color: green;"><?php echo __('Connected to shipdeo', 'shipdeo-v2'); ?></span>
                            <?php else : ?>
                                <span style="font-weight: bold; color: red;"><?php echo __('Not connected to shipdeo', 'shipdeo-v2') ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $access_token ? __('Reconnect', 'shipdeo-v2') : __('Connect', 'shipdeo-v2'); ?>" />
        </p>
    </form>

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="shipdeo_v2_store_information" />
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('shipdeo_v2_store_information'); ?>" />

        <h2 id="shipdeo_v2_store_information"><?php echo __('Store Information', 'shipdeo-v2') ?></h2>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="shipdeo_v2_store_name"><?php echo __('Store Name', 'shipdeo-v2') ?></label>
                    </th>
                    <td>
                        <input required type="text" name="store_name" id="shipdeo_v2_store_name" class="regular-text" value="<?php echo isset($store_information['store_name']) ? $store_information['store_name'] : null ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shipdeo_v2_subdistrict"><?php echo __('Subdistrict', 'shipdeo-v2') ?></label>
                    </th>
                    <td id="shipdeo_v2_subdistrict">
                        <input type="hidden" name="subdistrict_name" id="shipdeo_v2_subdistrict_name" value="<?php echo isset($store_information['subdistrict_name']) ? $store_information['subdistrict_name'] : null ?>" />
                        <select required name="subdistrict_code" id="shipdeo_v2_subdistrict_code" class="regular-text">
                            <?php if (isset($store_information['subdistrict_code']) && isset($store_information['subdistrict_name'])) : ?>
                                <option value="<?php echo $store_information['subdistrict_code']; ?>" selected><?php echo $store_information['subdistrict_name']; ?></option>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shipdeo_v2_store_email"><?php echo __('Email', 'shipdeo-v2') ?></label>
                    </th>
                    <td>
                        <input required type="email" name="email" id="shipdeo_v2_store_email" class="regular-text" value="<?php echo isset($store_information['email']) ? $store_information['email'] : null ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="e"><?php echo __('Phone', 'shipdeo-v2') ?></label>
                    </th>
                    <td>
                        <input required type="text" name="phone" id="shipdeo_v2_store_phone" class="regular-text" value="<?php echo isset($store_information['phone']) ? $store_information['phone'] : null ?>" />
                    </td>
                </tr>
            </tbody>
        </table>

         <h2 id="shipdeo_v2_store_configuration"><?php echo __('Store Configuration', 'shipdeo-v2') ?></h2>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="shipdeo_v2_is_insuranced"><?php echo __('Insurance', 'shipdeo-v2') ?></label>
                        <div class="tooltip dashicons dashicons-editor-help">
                            <span class="tooltiptext">You can add insurance to the transaction</span>
                        </div>
                    </th>
                    <td>
                        <input type="hidden" name="is_insuranced" id="shipdeo_v2_is_insuranced" value="<?php echo isset($store_information['is_insuranced']) ? $store_information['is_insuranced'] : "false" ?>" />
                        <input required type="radio" name="is_insuranced" id="shipdeo_v2_is_insuranced_yes" value="true" checked/>
                        Yes
                        <span class="element-space"></span>
                        <input required type="radio" name="is_insuranced" id="shipdeo_v2_is_insuranced_no" value="false"/>
                        No
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                    <label for="shipdeo_v2_is_must_use_insuranced"><?php echo __('Must Use Insurance', 'shipdeo-v2') ?></label>
                        <div class="tooltip dashicons dashicons-editor-help">
                            <span class="tooltiptext">You can add must use insurance to the transaction</span>
                        </div>
                    </th>
                    <td>
                        <input type="hidden" name="is_must_insuranced" id="shipdeo_v2_is_must_insuranced" value="<?php echo isset($store_information['is_must_insuranced']) ? $store_information['is_must_insuranced'] : "false" ?>" />
                        <input required type="radio" name="is_must_insuranced" id="shipdeo_v2_is_must_insuranced_yes" value="true"/>
                        Yes
                        <span class="element-space"></span>
                        <input required type="radio" name="is_must_insuranced" id="shipdeo_v2_is_must_insuranced_no" value="false"/>
                        No
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save', 'shipdeo-v2'); ?>" />
        </p>
    </form>
</div>