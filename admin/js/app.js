(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  var SubdistrictComponent = (function () {
    var $el = $("#shipdeo_v2_subdistrict");
    var $name = $el.find("#shipdeo_v2_subdistrict_name");
    var $code = $el.find("#shipdeo_v2_subdistrict_code");

    function init() {
      $code
        .select2({
          minimumInputLength: 1,
          ajax: {
            delay: 350,
            url: ajaxurl,
            dataType: "json",
            method: "post",
            data: function (params) {
              return {
                action: "get_subdistricts_by_name",
                name: params.term,
              };
            },
            processResults: function (data) {
              return {
                results: data.reduce(function (occ, row) {
                  occ.push({
                    id: row.subdistrict_code,
                    text: [
                      row.subdistrict_name,
                      row.city_name,
                      row.province_name,
                    ].join(", "),
                  });

                  return occ;
                }, []),
              };
            },
          },
        })
        .on("select2:select", function (event) {
          var data = event.params.data;
          $name.val(data.text);
        });
    }

    init();
  })();

  var CreateOrderToShipdeo = (function () {
    var $el = $("#shipdeo_v2_create_order");
    var $container = $('#shipdeo-v2');

    $el.on("click", handleClick);

    function handleClick() {
      var orderId = $el.data("order_id");

      $container.block({
        message: null,
        overlayCSS: {
          background: "#fff",
          opacity: 0.6,
        },
      });

      $.ajax({
        method: 'post',
        url: ajaxurl,
        data: {
          action: 'post_create_order_to_shipdeo',
          order_id: orderId,
        },
        success: function (response) {
          $container.unblock();
          window.location.reload();
        }
      })
    }
  })();

  var ConfirmOrderToShipdeo = (function () {
    var $el = $("#shipdeo_v2_confirm_order");
    var $container = $('#shipdeo-v2');

    $el.on("click", handleClick);

    function handleClick() {
      var orderId = $el.data("order_id");

      $container.block({
        message: null,
        overlayCSS: {
          background: "#fff",
          opacity: 0.6,
        },
      });

      $.ajax({
        method: 'post',
        url: ajaxurl,
        data: {
          action: 'post_confirm_order_to_shipdeo',
          order_id: orderId,
        },
        success: function (response) {
          $container.unblock();
          window.location.reload();
        }
      })
    }
  })();

  var DisableMustInsurance = function (disabled) {
    $("input[name='is_must_insuranced']").prop("disabled", disabled);
    if(disabled){
      $("#shipdeo_v2_is_must_insuranced_no").prop("checked", true);
    }
  };

  var CheckedInsurance = function (checked) {
    if(checked === "true"){
      $("#shipdeo_v2_is_insuranced_yes").prop("checked", true);
      $("#shipdeo_v2_is_insuranced_no").prop("checked", false);
    } else {
      $("#shipdeo_v2_is_insuranced_yes").prop("checked", false);
      $("#shipdeo_v2_is_insuranced_no").prop("checked", true);
    }
  };

  var CheckedMustInsurance = function (checked) {
    if(checked === "true"){
      $("#shipdeo_v2_is_must_insuranced_yes").prop("checked", true);
      $("#shipdeo_v2_is_must_insuranced_no").prop("checked", false);
    } else {
      $("#shipdeo_v2_is_must_insuranced_yes").prop("checked", false);
      $("#shipdeo_v2_is_must_insuranced_no").prop("checked", true);
    }
  };

  var OnClickInsurance = (function () {
    var $insurance = $("input[name='is_insuranced']");
    $insurance.on("click", insuranceClick);

    function insuranceClick() {
      var insuranceValue = $("input[name='is_insuranced']:checked").val();
      $("#shipdeo_v2_is_insuranced").val(insuranceValue)
      if(insuranceValue === "true"){
        DisableMustInsurance(false);
      } else {
        DisableMustInsurance(true);
      }
    }
  })();

  var OnClickMustInsurance = (function () {
    var $mustInsurance = $("input[name='is_must_insuranced']");
    $mustInsurance.on("click", mustInsuranceClick);

    function mustInsuranceClick() {
      var mustInsuranceValue = $("input[name='is_must_insuranced']:checked").val();
      $("#shipdeo_v2_is_must_insuranced").val(mustInsuranceValue);
      if(mustInsuranceValue === "true"){
        CheckedInsurance("true");
      }
    }
  })();

  var InitInsurance = (function () {
    var insuranceValue = $("#shipdeo_v2_is_insuranced").val();
    var mustInsuranceValue = $("#shipdeo_v2_is_must_insuranced").val();
    CheckedInsurance(insuranceValue);
    CheckedMustInsurance(mustInsuranceValue);
    if(insuranceValue === "false"){
      DisableMustInsurance(true);
    }
  })();
})(jQuery);
