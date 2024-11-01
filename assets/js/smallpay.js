/**
 * Plugin Name: SmallPay
 * Plugin URI:
 * Description: Official SmallPay plugin.
 * Version: 2.0.3
 * Author: SmallPay Srl
 * Author URI: https://www.smallpay.it
 * Text Domain: smallpay
 * Domain Path: /lang
 *
 * Copyright: © 2017-2018, Nexi SpA
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

var xpayConfig;
var card;
var buttons;

var spContainer = "#smallpay-form-container";

(function ($) {
    function getSpCS(selectror) {
        return `${spContainer} ${selectror}`;
    }

    $(document).ready(function () {
        $("form.checkout").on("checkout_place_order_smallpay", function () {
            if ($(getSpCS("#smallpay-installments-number")).length > 0) {
                $(getSpCS("#installments")).val($(getSpCS("#smallpay-installments-number")).val());
            }

            if (
                $(getSpCS("#smallpay_xpay_maxInstallments")).val() != 1 &&
                !$(getSpCS("#smallpay_accept_check")).is(":checked") &&
                $(getSpCS("#smallpay-installments-number")).val() != 1
            ) {
                $(getSpCS("#smallpay_accept_text")).css({
                    color: "red",
                });

                return false;
            } else {
                $(getSpCS("#smallpay_accept_text")).css({
                    color: "inherit",
                });
            }
        });

        $(document).on("change", getSpCS("#smallpay-installments-number"), function () {
            spInstallmentsCalc();

            if ($(getSpCS("#installments"))) {
                $(getSpCS("#installments")).val($(getSpCS("#smallpay-installments-number")).val());
            }

            if (
                $(getSpCS("#smallpay_xpay_maxInstallments")).val() == 1 ||
                $(getSpCS("#smallpay-installments-number")).val() == 1
            ) {
                $(getSpCS("#custom-checkbox-container")).hide();
            } else {
                $(getSpCS("#custom-checkbox-container")).show();
            }
        });
    });

    function spInstallmentsCalc() {
        var admin_url = $(getSpCS("#smallpay_admin_url")).val();

        if ($(getSpCS("#smallpay_xpay_maxInstallments")).val() != 1) {
            $.ajax({
                type: "POST",
                data: {
                    action: "sp_calc_installments",
                    installments: $(getSpCS("#smallpay-installments-number")).val(),
                },
                url: admin_url + "admin-ajax.php",
                success: function (response) {
                    if ($(getSpCS("#smallpay-installments-number")).val() != 1) {
                        $(getSpCS("#smallpay-intallment-info")).html(response);
                    } else {
                        var oneInstallmentText = `${$(getSpCS("#oneInstallmentText")).val()} ${$(getSpCS("#total-formated")).val()}`;

                        $(getSpCS("#smallpay-intallment-info")).html(`${oneInstallmentText} <br><br>`);
                    }
                },
                complete: function () {},
            });
        } else {
            $(getSpCS("#installment-block")).hide();
            $(getSpCS(".br-remove")).hide();
        }
    }
})(jQuery);
