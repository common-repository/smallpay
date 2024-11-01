/**
 * Plugin Name: Nexi XPay
 * Plugin URI:
 * Description: New Nexi Payments gateway. Official Nexi XPay plugin.
 * Version: 3.2.0
 * Author: Nexi SpA
 * Author URI: https://www.nexi.it
 * Text Domain: woocommerce-gateway-nexi-xpay
 * Domain Path: /lang
 *
 * Copyright: © 2017-2018, Nexi SpA
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
jQuery(function ($) {
    $(document).ready(function () {
        hideShowRangeProperties(2, $('input[name$="sp_enable_range_2"]').is(":checked"));
        hideShowRangeProperties(3, $('input[name$="sp_enable_range_3"]').is(":checked"));

        $(".sp-categories-select2").select2({
            closeOnSelect: false,
            scrollAfterSelect: true,
            templateSelection: formatState,
        });

        function formatState(state) {
            var temp = state.text.split("->");

            return temp[temp.length - 1];
        }

        var tr = $('select[id$="sp_incomplete_status"]').closest("tr");

        tr.height(parseInt(tr.height()) + 40 + "px");

        tr.children("th, td").css("vertical-align", "bottom");
    });

    $('input[name$="sp_enable_range_2"]').on("change", function () {
        hideShowRangeProperties(2, $(this).is(":checked"));
    });

    $('input[name$="sp_enable_range_3"]').on("change", function () {
        hideShowRangeProperties(3, $(this).is(":checked"));
    });

    $('input[name$="sp_max_cart_range_1"]').on("change", function () {
        if (parseInt($(this).val()) > 0) {
            $('input[name$="sp_min_cart_range_2"]').val(parseInt($(this).val()) + 1);
        }
    });

    $('input[name$="sp_max_cart_range_2"]').on("change", function () {
        if (parseInt($(this).val()) > 0) {
            $('input[name$="sp_min_cart_range_3"]').val(parseInt($(this).val()) + 1);
        }
    });

    function hideShowRangeProperties(range, valore) {
        if (!valore) {
            $(`.sp-properties-range${range}`).parent().parent().parent().hide();
        } else {
            $(`.sp-properties-range${range}`).parent().parent().parent().show();
        }
    }
});
