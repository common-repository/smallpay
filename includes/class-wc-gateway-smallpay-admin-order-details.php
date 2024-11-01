<?php

class WC_Gateway_SmallPay_Admin_Order_Details
{
    /**
     * da definire
     */
    public function init_form_fields()
    {
        return array();
    }

    /**
     * hook metabox visibility
     */
    public function set_meta_box_smallpay()
    {
        add_action('add_meta_boxes', array($this, 'add_meta_box_details_payment_smallpay'));
        add_action('add_meta_boxes', array($this, 'remove_meta_box_custom_fields'));
    }

    /**
     * add metabox with payment info where payment method is XPay
     *
     * @return type
     */
    public function add_meta_box_details_payment_smallpay()
    {
        $order = wc_get_order(get_post_field("ID"));
        if (!$order) {
            return;
        }

        if (wc_sp_get_order_prop($order, 'payment_method') === WC_Gateway_SmallPay::GATEWAY_ID || substr(wc_sp_get_order_prop($order, 'payment_method'), 0, 5) == 'xpay_') {
            add_meta_box('xpay-subscription-box', __('Payment details', 'smallpay'), array($this, 'details_payment_xpay'), 'shop_order', 'normal', 'high');
        }
    }

    /**
     * Get info XPay
     *
     * @return type
     */
    public function details_payment_xpay()
    {
        $oInfoOrderXPay = new WC_Gateway_SmallPay_Order_Payment_Info(get_post_field("ID"));
        $aDetailsOrder = $oInfoOrderXPay->GetInfoXPay();

        $params = array('_cliente', 'mail', 'nazionalita', 'pan', '_scadenza_pan', 'messaggio', 'num_contratto');
        foreach ($params as $param) {
            if (!isset($aDetailsOrder[$param])) {
                $aDetailsOrder[$param] = null;
            }
        }
        $aOrderInstallments = json_decode(get_post_meta(get_post_field("ID"), 'smallpay_installments', true));

        $path = plugin_dir_path(__DIR__);
        include_once $path . 'templates/' . __FUNCTION__ . ".php";
    }

    /**
     * Remove default WC box postcustom fields
     */
    public function remove_meta_box_custom_fields()
    {
        remove_meta_box('postcustom', 'shop_order', 'normal');
    }
}

new WC_Gateway_SmallPay_Admin_Order_Details();
