(function ($) {
  "use strict";

  $('[name$="_country"]').on("change", handle_change_country);
  $('[name$="_state"]').on("select2:opening", handle_select2_opening_state);
  $(document.body).on("country_changed", handle_country_changed);
  $(document.body).on("address_2_changed", handle_address_2_changed);

  function init() {
    $('[name$="_country"]')
      .toArray()
      .forEach(function (el) {
        var $el = $(el);
        var name = $el.attr("name");
        var module = String(name).replace("_country", "");
        var value = $el.val();

        init_fields(module, value);
      });
  }

  function init_fields(module, value) {
    if (value == "ID") {
      init_address_2_autocomplete(module);
      init_city_autocomplete(module);
    } else {
      destroy_address_2_autocomplete(module);
      destroy_city_autocomplete(module);
    }
  }

  function handle_change_country(event) {
    var name = $(this).attr("name");
    var prefix = String(name).replace("_country", "");

    $(document.body).trigger("country_changed", {
      module: prefix,
      value: event.target.value,
    });
  }

  function handle_country_changed(event, data) {
    var module = data.module;
    var value = data.value;

    set_address_2_value(module, null);
    set_city_value(module, null);
    set_state_value(module, null);
    init_fields(module, value);
  }

  function init_address_2_autocomplete(module) {
    var $address_2 = $('[name="' + module + '_address_2"]');

    $address_2.autocomplete({
      autoFocus: true,
      delay: 350,
      minLength: 1,
      source: function (request, response) {
        $.ajax({
          url: ajaxurl,
          dataType: "json",
          method: "post",
          data: {
            action: "get_subdistricts_by_name",
            name: request.term,
          },
          success: function (data) {
            response(
              data.map(function (row) {
                return {
                  label: [
                    row.subdistrict_name,
                    row.city_name,
                    row.province_name,
                  ].join(", "),
                  value: row.subdistrict_name,
                  data: row,
                };
              })
            );
          },
        });
      },
      select: function (event, ui) {
        $(document.body).trigger("address_2_changed", {
          module: module,
          value: ui.item.data,
        });
      },
    });
  }

  function destroy_address_2_autocomplete(module) {
    var $address_2 = $('[name="' + module + '_address_2"]');

    if ($address_2.autocomplete("instance")) {
      $address_2.autocomplete("destroy");
    }
  }

  function set_address_2_value(module, value) {
    $('[name="' + module + '_address_2"]').val(value);
  }

  function handle_address_2_changed(event, data) {
    var module = data.module;
    var value = data.value;
    set_city_value(module, value.city_name);
    set_state_value(module, value.province_id);
    $(document.body).trigger("update_checkout");
  }

  function init_city_autocomplete(module) {
    var $city = $('[name="' + module + '_city"]');

    $city.autocomplete({
      autoFocus: true,
      delay: 350,
      minLength: 1,
      source: function (request, response) {
        $.ajax({
          url: ajaxurl,
          dataType: "json",
          method: "post",
          data: {
            action: "get_cities_by_name",
            name: request.term,
          },
          success: function (data) {
            response(
              data.map(function (row) {
                return {
                  label: row.city_name,
                  value: row.city_name,
                };
              })
            );
          },
        });
      },
    });
  }

  function destroy_city_autocomplete(module) {
    var $city = $('[name="' + module + '_city"]');

    if ($city.autocomplete("instance")) {
      $city.autocomplete("destroy");
    }
  }

  function set_city_value(module, value) {
    $('[name="' + module + '_city"]').val(value);
  }

  function set_state_value(module, value) {
    $('[name="' + module + '_state"]')
      .val(value)
      .trigger("change");
  }

  function handle_select2_opening_state() {
    return !$(this).is("[readonly]");
  }

  init();
})(jQuery);
