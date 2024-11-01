<?php

final class WC_Gateway_SmallPay extends WC_Payment_Gateway_CC
{

    protected $APIXPay;
    protected $module_version;
    protected $oConfig;
    public static $lastId = 0;
    public static $lastColumn = null;
    public static $alreadyEnqueuedNotice = false;

    const GATEWAY_ID = 'smallpay';

    public function __construct()
    {
        require_once "class-wc-gateway-smallpay-api.php";
        require_once "constant_smallpay.php";
        $this->id = static::GATEWAY_ID;
        $this->method_title = __('SmallPay', 'smallpay');
        $this->method_description = __('Allow your customers to pay in installments', 'smallpay');

        $this->module_version = SPWC_PLUGIN_VERSION;

        $this->has_fields = true;
        $this->icon = plugins_url('assets/images/smallpay.png', plugin_dir_path(__FILE__));

        //what plugin supports
        $this->supports = array('products');

        //migration of old fields values into new first range of price fields
        $options = get_option('woocommerce_smallpay_settings');

        if (is_array($options)) {
            $migrateFields = array(
                'sp_categories',
                'sp_min_installments',
                'sp_max_installments',
                'sp_min_cart',
                'sp_max_cart'
            );

            $changed = false;

            foreach ($migrateFields as $field) {
                if (array_key_exists($field, $options)) {
                    $options[$field . __SMALLPAY_RANGE_KEY_MAP__[__SMALLPAY_RANGE_1__]] = $options[$field];
                    unset($options[$field]);

                    $changed = true;
                }
            }

            if ($changed) {
                update_option('woocommerce_smallpay_settings', $options);
            }
        }

        //Set Config Form
        parent::init_settings();
        $this->oConfig = new WC_Gateway_SmallPay_Configuration($this->settings);
        $this->form_fields = $this->oConfig->get_form_fields();

        $this->title = __('SmallPay', 'smallpay');

        //Set Description on payment page
        $this->description = __('You will be able to pay for your order in installments', 'smallpay');
        $this->instructions = $this->description;

        //Add JS script in Front and BO
        add_action('wp_enqueue_scripts', array($this, 'add_checkout_script'));
        add_action('admin_enqueue_scripts', array($this, 'add_admin_script'));

        add_action('wp_ajax_sp_calc_installments', array($this, 'sp_calc_installments'));
        add_action('wp_ajax_nopriv_sp_calc_installments', array($this, 'sp_calc_installments'));

        //Custom Field
        add_action('woocommerce_before_add_to_cart_button', array($this, 'smallpay_display_custom_field'));
        add_action('woocommerce_before_shop_loop_item_title', array($this, 'smallpay_display_custom_badge'), 10, 0);
        //End Custom Field
        add_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'wc_smallPay_payment_return'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'smallpay_checkConfigs'));
        //Box installments in my account order page and thank you page
        add_action('woocommerce_view_order', array($this, 'wc_smallpay_myorder'), 20);
        add_action('woocommerce_thankyou', array($this, 'wc_smallpay_show_order_and_empty_cart'), 20);
        //Widget
        add_action('wp_dashboard_setup', array($this, 'smallpay_dashboard_widgets'));
        //Load script only in checkout page
        add_action('woocommerce_after_checkout_form', array($this, 'checkout_script'));
        //Add extra Column in order page
        add_filter('manage_edit-shop_order_columns', array($this, 'smallpay_order_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'smallpay_order_column_content'));
        //Add Fiter in order page
        add_action('restrict_manage_posts', array($this, 'filter_orders_by_payment_method'), 20);
        add_filter('request', array($this, 'filter_orders_by_payment_method_query'));
        //Add custom order status
        add_action('init', array($this, 'register_instalments_payment_order_status'), 25);
        add_filter('wc_order_statuses', array($this, 'add_instalments_payment_to_order_statuses'));
        /*        add_filter('woocommerce_email_attachments', array( $this,'attach_contract_to_email'), 10, 3); */

        //Set payment title in checkout
        add_action('woocommerce_checkout_update_order_review', array($this, 'set_title'));
    }

    public function __destruct()
    {
        //Add JS script in Front and BO
        remove_action('wp_enqueue_scripts', array($this, 'add_checkout_script'));
        remove_action('admin_enqueue_scripts', array($this, 'add_admin_script'));

        remove_action('wp_ajax_sp_calc_installments', array($this, 'sp_calc_installments'));
        remove_action('wp_ajax_nopriv_sp_calc_installments', array($this, 'sp_calc_installments'));
        //Custom Field
        remove_action('woocommerce_before_add_to_cart_button', array($this, 'smallpay_display_custom_field'));
        remove_action('woocommerce_before_shop_loop_item_title', array($this, 'smallpay_display_custom_badge'), 10, 0);
        //End Custom Field
        remove_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'wc_smallPay_payment_return'));
        //remove_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'wc_smallPay_status_callback'));
        remove_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'smallpay_checkConfigs'));
        //Box installments in my account order page and thank you page
        remove_action('woocommerce_view_order', array($this, 'wc_smallpay_myorder'), 20);
        remove_action('woocommerce_thankyou', array($this, 'wc_smallpay_show_order_and_empty_cart'), 20);
        //Widget
        remove_action('wp_dashboard_setup', array($this, 'smallpay_dashboard_widgets'));
        //Load script only in checkout page
        remove_action('woocommerce_after_checkout_form', array($this, 'checkout_script'));
        //Add extra Column in order page
        remove_action('manage_edit-shop_order_columns', array($this, 'smallpay_order_column'));
        remove_action('manage_shop_order_posts_custom_column', array($this, 'smallpay_order_column_content'));
        //Add Fiter in order page
        remove_action('restrict_manage_posts', array($this, 'filter_orders_by_payment_method'), 20);
        remove_action('request', array($this, 'filter_orders_by_payment_method_query'));
        //Add custom order status
        remove_action('init', array($this, 'register_instalments_payment_order_status'), 25);
        remove_action('wc_order_statuses', array($this, 'add_instalments_payment_to_order_statuses'));

        //Set payment title in checkout
        remove_action('woocommerce_checkout_update_order_review', array($this, 'set_title'));
    }

    /* public function attach_contract_to_email($attachments, $id, $object)
      {
      $pdf_path = plugin_dir_path(__FILE__) . 'constant_smallpay.php';
      if ($id === 'customer_processing_order') {
      $attachments[] = $pdf_path;
      }
      return $attachments;
      } */

    public function register_instalments_payment_order_status()
    {
        register_post_status('wc-incomplete-inst', array(
            'label' => __('Installment payment in progress', 'smallpay'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Installment payment in progress (%s)', 'Installment payment in progress (%s)', 'smallpay')
        ));

        register_post_status('wc-completed-inst', array(
            'label' => __('Installment payment completed', 'smallpay'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Completed inst. (%s)', 'Completed inst. (%s)', 'smallpay')
        ));
    }

    public function add_instalments_payment_to_order_statuses($order_statuses)
    {
        return self::get_order_status($order_statuses);
    }

    public static function get_order_status($order_statuses)
    {
        $new_order_statuses = array();

        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
        }

        $new_order_statuses['wc-incomplete-inst'] = __('Installment payment in progress', 'smallpay');
        $new_order_statuses['wc-completed-inst'] = __('Installment payment completed', 'smallpay');

        return $new_order_statuses;
    }

    public function set_title()
    {
        if ($this->oConfig->get_installments_number(WC()->cart)['pay_ins']) {
            $this->title = __('Payment in installments', 'smallpay');
        } else {
            $this->title = __('Credit card', 'smallpay');
        }
    }

    /**
     * checks smallpay credentials and saves settings
     * check on min/max installments number, if not valid, the plugin is still available but the payment can be made only in a single solution
     * 
     */
    public function smallpay_checkConfigs()
    {
        //saves post data to db
        $this->process_admin_options();

        $postDati = $this->get_post_admin_options();

        $this->oConfig = new WC_Gateway_SmallPay_Configuration($this->settings);

        $api = new WC_SmallPay_Api(WC_SmallPay::get_local_domain(), get_site_url() . '?wc-api=WC_Gateway_SmallPay');

        $api->set_settings($postDati['sp_merchant_id'], $postDati['sp_service'], $postDati['sp_secret']);

        try {
            $api->checkConfigs();
        } catch (\Exception $exc) {
            WC_Admin_Settings::add_error($exc->getMessage());

            $option = get_option('woocommerce_smallpay_settings');
            $option['enabled'] = 'no';

            update_option('woocommerce_smallpay_settings', $option);
        }

        $resInsNumber = $this->oConfig->check_range_configs(__SMALLPAY_RANGE_1__);
        $resInsNumberRange2 = $this->oConfig->check_range_configs(__SMALLPAY_RANGE_2__);
        $resInsNumberRange3 = $this->oConfig->check_range_configs(__SMALLPAY_RANGE_3__);

        if (!($resInsNumber['res'] && $resInsNumberRange2['res'] && $resInsNumberRange3['res'])) {
            if (!$resInsNumber['res']) {
                WC_Admin_Settings::add_error($resInsNumber['msg']);
            }

            if (!$resInsNumberRange2['res']) {
                WC_Admin_Settings::add_error($resInsNumberRange2['msg']);
            }

            if (!$resInsNumberRange3['res']) {
                WC_Admin_Settings::add_error($resInsNumberRange3['msg']);
            }
        }
    }

    /**
     * returns submitted setting options
     * 
     * @return boolean
     */
    public function get_post_admin_options()
    {
        $post_data = $this->get_post_data();

        $fields = array();

        foreach ($this->get_form_fields() as $key => $value) {
            if ('title' !== $this->get_field_type($value)) {
                try {
                    $fields[$key] = $this->get_field_value($key, $value, $post_data);
                } catch (Exception $e) {
                    $fields[$key] = false;
                }
            }
        }

        return $fields;
    }

    //Add Fiter in order page
    public function filter_orders_by_payment_method()
    {
        global $typenow;

        if ('shop_order' === $typenow) {
            // get all payment methods, even inactive ones
            $gateways = WC()->payment_gateways->payment_gateways();
            ?>
            <select name="_shop_order_payment_method" id="dropdown_shop_order_payment_method">
                <option value="">
                    <?php esc_html_e('All Payment Methods', 'smallpay'); ?>
                </option>

                <?php foreach ($gateways as $id => $gateway) { ?>
                    <option value="<?php echo esc_attr($id); ?>" <?php echo esc_attr(isset($_GET['_shop_order_payment_method']) ? selected($id, $_GET['_shop_order_payment_method'], false) : ''); ?>>
                        <?php echo esc_html($gateway->get_method_title()); ?>
                    </option>
                <?php } ?>
            </select>
            <?php
        }
    }

    public function filter_orders_by_payment_method_query($vars)
    {
        global $typenow;

        if ('shop_order' === $typenow && isset($_GET['_shop_order_payment_method'])) {
            $vars['meta_key'] = '_payment_method';

            $vars['meta_value'] = wc_clean($_GET['_shop_order_payment_method']);
        }

        return $vars;
    }

    /**
     * Add extra Column in order page
     * 
     * @param type $columns
     * @return type
     */
    public function smallpay_order_column($columns)
    {
        $columns['payment_method'] = __('Payment Method', 'smallpay');
        $columns['installments_number'] = __('Installments', 'smallpay');
        $columns['installments_status'] = __('Last Installment', 'smallpay');

        return $columns;
    }

    /**
     * sets content for extra columns
     * 
     * @global type $post
     * @param type $column
     */
    public function smallpay_order_column_content($column)
    {
        global $post;

        self::$lastColumn = $column;

        if (is_array(get_post_meta($post->ID, 'smallpay_installments', true))) {
            $data = get_post_meta($post->ID, 'smallpay_installments', true);
        } else {
            $data = json_decode(get_post_meta($post->ID, 'smallpay_installments', true));
        }

        if ('payment_method' === $column) {
            if ($data == null) {
                return;
            }

            if ($data->installments != null && count($data->installments) > 1) {
                echo __('Payment in installments', 'smallpay') . ' ' . $this->title;
            } else {
                echo __('Credit card', 'smallpay') . ' ' . $this->title;
            }
        } elseif ('installments_number' === $column) {
            if ($data == null || $data->installments == null || count($data->installments) <= 1) {
                return;
            }

            $total_installments = count($data->installments);

            $payed = 0;

            foreach ($data->installments as $set) {
                if ($set->transactionDate != '') {
                    $payed += 1;
                }
            }

            echo $payed . '/' . $total_installments;
        } elseif ('installments_status' === $column) {
            if (is_array(get_post_meta($post->ID, 'smallpay_installments', true))) {
                $data = get_post_meta($post->ID, 'smallpay_installments', true);
            } else {
                $data = json_decode(get_post_meta($post->ID, 'smallpay_installments', true));
            }

            if ($data == null || $data->installments == null || count($data->installments) <= 1) {
                return;
            }

            $status = __SMALLPAY_ICON_KO__;

            foreach ($data->installments as $set) {
                if ($set->transactionDate && $set->transactionStatus != __SMALLPAY_TS_PAYED__) {
                    $status = __SMALLPAY_ICON_KO__;
                    break;
                } else {
                    $status = __SMALLPAY_ICON_OK__;
                }
            }

            echo $status;
        }
    }

    /**
     * displays installments counters on dashboard
     * 
     * @global type $wp_meta_boxes
     */
    public function smallpay_dashboard_widgets()
    {
        global $wp_meta_boxes;
        wp_add_dashboard_widget('smallpay_widget', __('Status of installment transactions', 'smallpay'), array($this, 'smallpay_widget'));
    }

    /**
     * calculates data for dashboard counters
     * 
     */
    public function smallpay_widget()
    {
        $query = new WC_Order_Query(array(
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'payment_method' => 'smallpay',
            'return' => 'ids',
        ));
        $orders = $query->get_orders();

        $ok = 0;
        $ko = 0;
        $completed = 0;

        foreach ($orders as $orders_id) {
            if (get_post_meta($orders_id, 'smallpay_installments', true) != null) {
                $recurrences = json_decode(get_post_meta($orders_id, 'smallpay_installments', true));
                $status = false;
                $count = 0;
                $recurrence = 0;

                if (isset($recurrences->recurrencesSet)) {  //for compatibility with old data structure
                    foreach ($recurrences->recurrencesSet as $set) {
                        $recurrence += 1;
                        if ($set->actualChargeDate == '' && $set->lastChargeAttemptDate != '') {
                            $status = false;
                            break;
                        } elseif ($set->actualChargeDate != '' && $set->lastChargeAttemptDate != '') {
                            $status = true;
                            $count += 1;
                        } else {
                            $status = true;
                        }
                    }
                } else if (isset($recurrences->installments)) {
                    foreach ($recurrences->installments as $set) {
                        $recurrence += 1;

                        if ($set->transactionStatus == __SMALLPAY_TS_UNSOLVED__) {
                            $status = false;
                            break;
                        } else {
                            $status = true;
                            if ($set->transactionDate != null) {
                                $count += 1;
                            }
                        }
                    }
                }

                if ($status) {
                    if ($count == $recurrence) {
                        $completed += 1;
                    } else {
                        $ok += 1;
                    }
                } else {
                    $ko += 1;
                }
            }
        }

        $path = plugin_dir_path(__DIR__);
        $logo = plugins_url('assets/images/smallpay.png', plugin_dir_path(__FILE__));
        $okText = __('Open transactions', 'smallpay');
        $koText = __('Transactions with issues', 'smallpay');
        $completedText = __('Completed transactions', 'smallpay');
        include_once $path . 'templates/' . __FUNCTION__ . ".php";
    }

    public function wc_smallpay_myorder($order_id)
    {
        $aOrderInstallments = json_decode(get_post_meta($order_id, 'smallpay_installments', true));
        $path = plugin_dir_path(__DIR__);
        $domain = get_site_url();
        include_once $path . 'templates/' . __FUNCTION__ . ".php";
    }

    /**
     * adds badge ('payable in installments') under product miniature
     * 
     * @global type $post
     */
    public function smallpay_display_custom_badge()
    {
        if ($this->oConfig->is_enabled()) {
            global $post;

            $ret = $this->oConfig->payable_in_installments($post->ID);

            if ($ret['res']) {
                echo '<div class="smallpay-custom-field-wrapper" >'
                . '<span class="smallpay-custom-badge" style="text-transform: uppercase;padding: 3px;width: 100%;max-width: 400px;margin-bottom: 1em;border: 1px solid;border-radius: 3px;/*color: black;border-color: black;*/display: inline-block;margin-top: 1em;"">'
                . __('product available in', 'smallpay')
                . ' ' . $ret['max_ins'] . ' ' . __('installments', 'smallpay') . '</span>'
                . '</div>';
            }
        }
    }

    /**
     * adds additional info ('payable in installments') in product detail page - FrontOffice - Shop
     * 
     * @global type $post
     */
    public function smallpay_display_custom_field()
    {
        if ($this->oConfig->is_enabled()) {
            global $post;

            $ret = $this->oConfig->payable_in_installments($post->ID);

            if ($ret['res']) {
                echo '<div class="smallpay-custom-field-wrapper" style="background-color: lightgrey; border-left-color: #f8b250; border-left-style: outset; padding: 15px; max-width: 600px; display: block; margin-bottom: 30px;"">'
                . '<table style="margin: 0;">'
                . '<tr>'
                . '<td style="border: 0; vertical-align: middle; padding: 0; background-color: lightgrey; padding-right: 8px;"><h5 style="padding-left: 10px; margin: 0; font-weight: bold;">' . __('INSTALLMENT PAYMENT AVAILABLE', 'smallpay') . '</h5></td>'
                . '<td rowspan="2" style="border: 0; vertical-align: middle; padding: 0; background-color: lightgrey; width: 115px;"><img src=' . plugins_url('assets/images/smallpay.png', plugin_dir_path(__FILE__)) . ' style="max-height: 45px; float: right;"></td>'
                . '</tr>'
                . '<tr>'
                . '<td style="border: 0; vertical-align: middle; padding: 0; background-color: lightgrey; padding-right: 8px;"><h6 style="padding-left:10px; margin: 0;">' . __('you can pay this product in', 'smallpay') . ' ' . $ret['max_ins'] . ' ' . __('installments', 'smallpay') . '</h6></td>'
                . '</tr>'
                . '</table></div>';
            }
        }
    }

    /**
     * payment option form
     * 
     */
    public function form()
    {
        $installmentsInfo = $this->oConfig->get_installments_number(WC()->cart);

        $dati = $this->sp_calc_installments($installmentsInfo['max'], true);

        $installments = $dati['installments'];
        $totalFormatted = $dati['totalFormatted'];
        $first_installment_amount = $dati['firstFormatted'];
        $installment_amount = $dati['othersFormatted'];

        $path = plugin_dir_path(__DIR__);

        include_once $path . 'templates/' . __FUNCTION__ . ".php";
    }

    /**
     * Add JS & CSS to checkout page
     */
    public function add_checkout_script()
    {
        
    }

    /**
     * Add JS & CSS to WC BackOffice
     */
    public function add_admin_script()
    {
        wp_enqueue_style('smallpay_style', plugins_url('assets/css/smallpay.css', plugin_dir_path(__FILE__)));
        wp_enqueue_script('smallpay_xpay_build_config', plugins_url('assets/js/smallpay_back.js', plugin_dir_path(__FILE__)), array(), $this->module_version, true);
    }

    /**
     * Return true if SmallPay is avaiable between payment methods
     */
    public function is_available()
    {
        if (is_add_payment_method_page()) { //Check if user is not in add payment method page in his account
            return false;
        }

        if (get_woocommerce_currency() !== "EUR") { //Check if currency is EURO
            return false;
        }

        if (class_exists("WC_Subscriptions_Cart") && WC_Subscriptions_Cart::cart_contains_subscription()) { //Check if cart contains subscription
            return false;
        }

        return parent::is_available();
    }

    /**
     * Funzione obbigatoria per WP, processa il pagamento e fa il redirect
     *
     * @param type $order_id
     * @return type
     */
    public function process_payment($order_id)
    {
        $order = new WC_Order($order_id);

        $installments = sanitize_text_field($_REQUEST['installments']);

        if (!$this->oConfig->check_installments(WC()->cart, $installments)) {
            wc_add_notice(__('Number of installments selected invalid', 'smallpay'), "error");
            return false;
        }

        $amount = preg_replace('#[^\d,.]#', '', strip_tags($order->get_total()));
        $amount = absint(round(wc_format_decimal(( (float) $amount * 100), wc_get_price_decimals())));

        $instData = $this->sp_calc_installments($installments, true);

        if ($amount != ($instData['total'])) {
            return false;
        }

        if ($instData['installments'] != sanitize_text_field($_REQUEST['installments'])) {
            return false;
        }

        $firstInstallments = number_format($instData['first'], 0, "", "");

        $paymentId = substr($order_id . '-' . time(), 0, 30);

        $redirectUrl = get_rest_url(null, 'smallpay/payment-return/' . $paymentId);

        $api = new WC_SmallPay_Api(WC_SmallPay::get_local_domain(), $redirectUrl);

        $api->set_settings($this->oConfig->sp_merchant_id, $this->oConfig->sp_service, $this->oConfig->sp_secret);
        $api->set_payer_info($order->get_billing_first_name(), $order->get_billing_last_name(), $order->get_billing_email(), $order->get_billing_phone());
        $api->set_order_info($paymentId, $installments - 1, $instData['total'], $firstInstallments);

        $statusUpdateCallbackUrl = get_rest_url(null, 'smallpay/status-update');

        $modifyInstallments = true;

        try {
            $res = $api->send_request_recurring_payment($statusUpdateCallbackUrl, $modifyInstallments);

            if ($res) {
                $ret = array('result' => 'success', 'redirect' => $api->response['paymentUrl']);

                return $ret;
            }
        } catch (\Exception $ex) {
            wc_add_notice($ex->getMessage(), "error");
            return false;
        }

        return false;
    }

    /**
     * Calculate the installments amount
     * 
     * @param int $installments
     * @param boolean $php
     * @return array | html
     */
    public function sp_calc_installments($installments = null, $php = false)
    {
        $ret = array();

        if ($installments == null && !empty($_REQUEST['installments'])) {
            $installments = sanitize_text_field($_REQUEST['installments']);
        }

        $totalNF = bcmul(WC()->cart->total, 100, 1);
        $shippingNF = bcmul(WC()->cart->shipping_total, 100, 1);
        $subTotal = bcsub($totalNF, $shippingNF, 1);

        if (isset($installments) && $installments != 0) {
            $installment_amountNF = floor(bcdiv(bcsub($totalNF, $shippingNF), $installments, 2));
            $first_installment_amountNF = bcadd(bcadd($installment_amountNF, bcsub($subTotal, bcmul($installment_amountNF, $installments), 1), 1), $shippingNF, 1);
        } else {
            $installment_amountNF = 0;
            $first_installment_amountNF = 0;
        }

        $installment_amount = bcdiv($installment_amountNF, 100, 3);
        $installment_amount = number_format($installment_amount, 2, ',', ' ');

        $first_installment_amount = bcdiv($first_installment_amountNF, 100, 3);
        $first_installment_amount = number_format($first_installment_amount, 2, ',', ' ');

        $totalFormatted = number_format(bcdiv($totalNF, 100, 3), 2, ',', ' ');

        if ($php == false) {
            ob_clean();
            include_once plugin_dir_path(__DIR__) . 'templates/installments_plan.php';
            wp_die();
        } else {
            return array(
                'installments' => $installments,
                'total' => round($totalNF),
                'first' => round($first_installment_amountNF),
                'others' => (int) $installment_amountNF,
                'totalFormatted' => $totalFormatted,
                'firstFormatted' => $first_installment_amount,
                'othersFormatted' => $installment_amount
            );
        }
    }

    /**
     * handles return from payment gateway
     * 
     * @param type $data
     * @return WP_REST_Response
     */
    public function wc_smallPay_payment_return($data)
    {
        //needed to add error notice
        WC()->frontend_includes();

        WC()->session = new WC_Session_Handler();
        WC()->session->init();

        $params = $data->get_params();

        $paymentId = $params["paymentId"];

        $api = new WC_SmallPay_Api(WC_SmallPay::get_local_domain(), null);

        $api->set_orderReference($paymentId);

        $api->set_settings($this->oConfig->sp_merchant_id, $this->oConfig->sp_service, $this->oConfig->sp_secret);

        try {
            $api->retrieve_recurrences();

            $response = $api->response;

            $orderIds = self::explode_paymentId($response['paymentId']);

            update_post_meta($orderIds['post_id'], 'smallpay_installments', json_encode($response));

            if ($response['status'] !== __SMALLPAY_IP_ACTIVE__) {
                $error = __('The first payment wasn\'t made or the transaction was unsuccessful', 'smallpay');

                WC_SmallPay_Logger::LogExceptionError(new \Exception('Smallpay return - ' . $error . ' - ' . json_encode($response)));

                wc_add_notice($error, "error");

                return new WP_REST_Response($error, 303, array("Location" => wc_get_checkout_url()));
            }

            $order = new WC_Order($orderIds['order_id']);
            $config = get_option('woocommerce_smallpay_settings');

            if ($order->get_status() != $config['sp_incomplete_status'] && $order->get_status() != $config['sp_complete_status'] && $order->get_status() != 'processing') {
                $order->payment_complete();

                $order->update_status($config['sp_incomplete_status']);
            }

            return new WP_REST_Response(null, "200", array("Refresh" => "1; URL=" . $this->get_return_url($order)));
        } catch (\Exception $exc) {
            wc_add_notice($exc->getMessage(), "error");

            return new WP_REST_Response($exc->getMessage(), 303, array("Location" => wc_get_checkout_url()));
        }
    }

    /**
     * empties the cart and shows order detail
     * 
     * @param type $order_id
     */
    function wc_smallpay_show_order_and_empty_cart($order_id)
    {
        //con't be done in wc_smallPay_payment_return because session is not available
        //therefore user's cart can't be cleared
        WC()->cart->empty_cart(true);

        $this->wc_smallpay_myorder($order_id);
    }

    /**
     * handles installments status update
     * 
     * @param type $data
     * @return WP_Error|WP_REST_Response
     */
    public function wc_smallPay_status_callback($data)
    {
        $request = json_decode(file_get_contents('php://input'), true);

        if ($request == false || !is_array($request)) {
            $error = __('Request format not valid', 'smallpay');

            WC_SmallPay_Logger::LogExceptionError(new \Exception('Status update callback - ' . $error . ' - ' . json_encode($request)));

            return new WP_Error('data_missing', array('data_missing' => array('status' => 500, 'message' => $error)));
        }

        $calculatedHashPass = sha1('paymentId=' . $request['paymentId'] . 'domain=' . $request['domain'] . 'timestamp=' . $request['timestamp'] . 'uniqueId=' . $this->settings['sp_secret']);

        if ($calculatedHashPass != $request['hashPass']) {
            $error = __('Invalid hashPass', 'smallpay');

            WC_SmallPay_Logger::LogExceptionError(new \Exception('Status update callback - ' . $error . ' - ' . json_encode(array(
                                'calculatedHashPass' => $calculatedHashPass,
                                'sp_secret' => $this->settings['sp_secret'],
                                'smallpayRequest' => $request
                            )))
            );

            return new WP_Error('invalid_hashPass', array('invalid_hashPass' => array('status' => 500, 'message' => $error)));
        }

        if (!isset($request['installments'])) {
            $error = __('Missing installments info', 'smallpay');

            WC_SmallPay_Logger::LogExceptionError(new \Exception('Status update callback - ' . $error . ' - ' . json_encode($request)));

            return new WP_Error('missing_installments', array('missing_installments' => array('status' => 500, 'message' => $error)));
        }

        $orderIds = self::explode_paymentId($request['paymentId']);

        update_post_meta($orderIds['post_id'], 'smallpay_installments', json_encode($request));

        if ($request['status'] !== __SMALLPAY_IP_ACTIVE__) {
            $error = __('The first payment wasn\'t made or the transaction was unsuccessful', 'smallpay');

            WC_SmallPay_Logger::LogExceptionError(new \Exception('Status update callback - ' . $error . ' - ' . json_encode($request)));

            return new WP_Error('first_payment_error', array('first_payment_error' => array('status' => 500, 'messagge' => $error)));
        }

        header('Content-Type: application/json');

        $order = new WC_Order($orderIds['order_id']);

        $config = get_option('woocommerce_smallpay_settings');

        if ("wc-" . $order->get_status() != $config['sp_incomplete_status']) {
            $order->payment_complete();

            if (count($request['installments']) > 1) {
                $config = get_option('woocommerce_smallpay_settings');

                $order->update_status($config['sp_incomplete_status']);
            }
        } else {
            $completed = false;

            foreach ($request['installments'] as $set) {
                if ($set['transactionStatus'] == __SMALLPAY_TS_UNSOLVED__) {
                    $completed = false;
                    break;
                } elseif (in_array($set['transactionStatus'], array(__SMALLPAY_TS_PAYED__, __SMALLPAY_TS_DELETED__))) {
                    $completed = true;
                } else {
                    $completed = false;
                }
            }

            if ($completed) {
                $config = get_option('woocommerce_smallpay_settings');
                $order->update_status($config['sp_complete_status']);
            }
        }

        return new WP_REST_Response(array(), 200);
    }

    public function checkout_script()
    {
        wp_enqueue_script('smallpay', plugins_url('assets/js/smallpay.js', plugin_dir_path(__FILE__)), array(), $this->module_version, true);
        wp_enqueue_style('style_smallpay', plugins_url('assets/css/smallpay.css', plugin_dir_path(__FILE__)));
    }

    public static function explode_paymentId($paymentId)
    {
        $temp = explode('-', $paymentId);

        return array(
            'order_id' => $temp[0],
            'timestamp' => $temp[1] ?? null,
            'post_id' => $temp[0]
        );
    }

}
