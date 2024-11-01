<script type="text/template" id="tmpl-wc-modal-shipdeo-v2-print-shipping-label">
    <div class="wc-backbone-modal wc-modal-shipdeo-v2-print-shipping-label">
        <div class="wc-backbone-modal-content" style="width: 50vw; height: 80%;">
            <section class="wc-backbone-modal-main" role="main" style="height: 100%;">
                <header class="wc-backbone-modal-header">
                    <h1><?php echo __('Shipping Label', 'shipdeo-v2'); ?></h1>
                    <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                        <span class="screen-reader-text"><?php esc_html_e('Close modal panel', 'woocommerce'); ?></span>
                    </button>
                </header>
                <article style="height: 100%; width:auto; overflow: hidden; padding: 20px;" id="shipdeo_v2_shipping_label">
                    <!-- <p style="padding: 1rem; margin-bottom: 0rem">
                        <label style="margin-right: 1rem;">
                            <input type="radio" name="mode" value="default" checked />
                            <span>Default</span>
                        </label>
                        <label>
                            <input type="radio" name="mode" value="thermal" />
                            <span>Thermal</span>
                        </label>
                    </p> -->
                    <div id="shipdeo_v2_shipping_label_content" style="width: 100%; height: 100%;">
                        <iframe frameborder="0" width="100%" height="100%" style="width: 100%; height: 100%;"></iframe>
                    </div>
                </article>
            </section>
        </div>
    </div>
    <div class="wc-backbone-modal-backdrop modal-close"></div>
</script>
<script>
    (function($) {
        var ids = '<?php echo $selected_ids; ?>';

        $(document.body).on('wc_backbone_modal_loaded', function() {
            var mode = this.value;
            var $container = $('#shipdeo_v2_shipping_label');
            var $content = $container.find('#shipdeo_v2_shipping_label_content');

            $content.empty();
            $container.block({
                message: null,
                overlayCSS: {
                    background: "#fff",
                    opacity: 0.6,
                },
            });

            $.ajax({
                url: ajaxurl,
                method: 'post',
                dataType: 'json',
                data: {
                    action: 'post_print_shipping_label',
                    ids: ids,
                    mode: mode,
                },
                success: function(response) {
                    $container.unblock();

                    if (!response.success) {
                        $('<h3 />', {
                            html: response.message,
                            style: "padding: 1rem; text-align: center; color: red;"
                        }).appendTo($content);

                        return;
                    }

                    var url = response.data.data.url;

                    $('<iframe />', {
                        frameborder: 0,
                        width: '100%',
                        height: '100%',
                        style: 'width: 100%; height: 100%;',
                        src: url,
                    }).appendTo($content);
                }
            });
        });

        $(window).WCBackboneModal({
            template: 'wc-modal-shipdeo-v2-print-shipping-label',
        });
    })(jQuery);
</script>