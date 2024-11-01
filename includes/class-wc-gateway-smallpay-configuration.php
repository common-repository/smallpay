<?php

class WC_Gateway_SmallPay_Configuration
{

    public $settings;
    public $sp_merchant_id;
    public $sp_secret;
    public $sp_service;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->init_settings();

        add_action('admin_enqueue_scripts', array($this, 'add_admin_config_script'));
    }

    public function __destruct()
    {
        remove_action('admin_enqueue_scripts', array($this, 'add_admin_config_script'));
    }

    public function init_settings()
    {
        $this->sp_merchant_id = isset($this->settings['sp_merchant_id']) ? trim($this->settings['sp_merchant_id']) : false;
        $this->sp_secret = isset($this->settings['sp_secret']) ? trim($this->settings['sp_secret']) : false;
        $this->sp_service = isset($this->settings['sp_service']) ? trim($this->settings['sp_service']) : false;
    }

    //SETTINGS FORM MANAGEMENT

    public function get_form_fields()
    {
        $form_fields = array(
            'module_description' => array(
                'title' => '',
                'type' => 'title',
                'description' => __('From this page you can enter the general configurations of the module. We remind you to enable the individual products you wish to offer with installment payment directly from the product tab.', 'smallpay'),
                'class' => 'style_title'
            ),
            'title_section_1' => array(
                'title' => __('SmallPay Gateway configurations', 'smallpay'),
                'type' => 'title',
            ),
            'enabled' => array(
                'title' => __('Enable/Disable', 'smallpay'),
                'type' => 'checkbox',
                'label' => __('Enable SmallPay payment module.', 'smallpay'),
                'default' => 'no'
            ),
            'sp_merchant_id' => array(
                'title' => __('Merchant ID', 'smallpay') . ' *',
                'type' => 'text',
                'desc_tip' => __('Provided by SmallPay', 'smallpay')
            ),
            'sp_service' => array(
                'title' => __('Service ID', 'smallpay') . ' *',
                'type' => 'text',
                'desc_tip' => __('Provided by SmallPay', 'smallpay')
            ),
            'sp_secret' => array(
                'title' => __('Unique ID', 'smallpay') . ' *',
                'type' => 'text',
                'desc_tip' => __('Provided by SmallPay', 'smallpay')
            ),
            'options_title' => array(
                'title' => __('Smallpay options', 'smallpay'),
                'type' => 'title',
                'description' => __('Using this configurator you can set up categories of installment products with their price ranges and number of installments', 'smallpay'),
            ),
        );

        $form_fields = array_merge(
                $form_fields,
                array(
                    'range_title' . __SMALLPAY_RANGE_KEY_MAP__[__SMALLPAY_RANGE_1__] => array(
                        'title' => ucfirst($this->get_range_desc(__SMALLPAY_RANGE_1__)),
                        'type' => 'title',
                        'class' => __SMALLPAY_RANGE_1__ . '-title'
                    )
                ),
                $this->get_range_inputs(__SMALLPAY_RANGE_1__),
                array(
                    'range_title' . __SMALLPAY_RANGE_KEY_MAP__[__SMALLPAY_RANGE_2__] => array(
                        'title' => ucfirst($this->get_range_desc(__SMALLPAY_RANGE_2__)),
                        'type' => 'title',
                        'class' => __SMALLPAY_RANGE_2__ . '-title'
                    )
                ),
                array(
                    'sp_enable' . __SMALLPAY_RANGE_KEY_MAP__[__SMALLPAY_RANGE_2__] => array(
                        'type' => 'checkbox',
                        'label' => __('Enable second price range', 'smallpay'),
                        'default' => 'no'
                    ),
                ),
                $this->get_range_inputs(__SMALLPAY_RANGE_2__),
                array(
                    'range_title' . __SMALLPAY_RANGE_KEY_MAP__[__SMALLPAY_RANGE_3__] => array(
                        'title' => ucfirst($this->get_range_desc(__SMALLPAY_RANGE_3__)),
                        'type' => 'title',
                        'class' => __SMALLPAY_RANGE_3__ . '-title'
                    )
                ),
                array(
                    'sp_enable' . __SMALLPAY_RANGE_KEY_MAP__[__SMALLPAY_RANGE_3__] => array(
                        'type' => 'checkbox',
                        'label' => __('Enable third price range', 'smallpay'),
                        'default' => 'no'
                    ),
                ),
                $this->get_range_inputs(__SMALLPAY_RANGE_3__)
        );

        $form_fields = array_merge($form_fields, array(
            'sp_incomplete_status' => array(
                'title' => __('Order creation status', 'smallpay'),
                'type' => 'select',
                'description' => __('Order status at creation', 'smallpay'),
                'default' => 'wc-incomplete-inst',
                'desc_tip' => true,
                'options' => $this->get_options_order_status(),
                'class' => 'build_style font-style'
            ),
            'sp_complete_status' => array(
                'title' => __('Order status completed', 'smallpay'),
                'type' => 'select',
                'description' => __('Order status upon completion of installment payments', 'smallpay'),
                'default' => 'wc-completed-inst',
                'desc_tip' => true,
                'options' => $this->get_options_order_status(),
                'class' => 'build_style font-style'
            ),
        ));

        return $form_fields;
    }

    public function get_options_order_status()
    {
        return WC_Gateway_SmallPay::get_order_status(wc_get_order_statuses());
    }

    public function get_options_config_installments()
    {
        $installments = array(
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12
        );

        return $installments;
    }

    public function get_options_config_catefories_tree()
    {
//        //to get checkbox tree template
//        include ABSPATH . 'wp-admin/includes/template.php';
//
//        $args = array(
//            'taxonomy' => 'product_cat',
//            'echo' => 0,
//        );
//
//        $html = wp_terms_checklist(0, $args);

        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);

        $parentCategories = array();
        $childCategories = array();

        foreach ($categories as $category) {
            if ($category->parent == 0) {
                $parentCategories[] = $category;
            } else {
                if (!array_key_exists($category->parent, $childCategories)) {
                    $childCategories[$category->parent] = array();
                }

                $childCategories[$category->parent][] = $category;
            }
        }

        $options = array();

        foreach ($parentCategories as $parentCategory) {
            $options[$parentCategory->term_id] = $parentCategory->name;

            $childOptions = $this->get_child_options($childCategories, $parentCategory->term_id);

            foreach ($childOptions as $key => $childOption) {
                $options[$key] = $parentCategory->name . ' -> ' . $childOption;
            }
        }

        return $options;
    }

    private function get_child_options($childCategories, $id)
    {
        $options = array();

        if (array_key_exists($id, $childCategories)) {
            foreach ($childCategories[$id] as $childCategory) {
                $options[$childCategory->term_id] = $childCategory->name;

                $childOptions = $this->get_child_options($childCategories, $childCategory->term_id);

                foreach ($childOptions as $childKey => $childOption) {
                    $options[$childKey] = $childCategory->name . ' -> ' . $childOption;
                }
            }
        }

        return $options;
    }

    private function get_range_inputs($range)
    {
        return array(
            'sp_categories' . __SMALLPAY_RANGE_KEY_MAP__[$range] => array(
                'title' => __('Installment categories', 'smallpay'),
                'type' => 'multiselect',
                'options' => $this->get_options_config_catefories_tree(),
                'desc_tip' => __('Select all categories on which you want to enable installment payments', 'smallpay'),
                'class' => 'sp-categories-select2 sp-properties-' . $range
            ),
            'sp_min_cart' . __SMALLPAY_RANGE_KEY_MAP__[$range] => array(
                'title' => __('Price range from - €', 'smallpay'),
                'type' => 'text',
                'desc_tip' => __('The minimum value of products for which installment payment can be made.', 'smallpay'),
                'class' => 'sp-properties-' . $range
            ),
            'sp_max_cart' . __SMALLPAY_RANGE_KEY_MAP__[$range] => array(
                'title' => __('Price range to - €', 'smallpay'),
                'type' => 'text',
                'desc_tip' => __('The maximum value of products for which installment payment can be made.', 'smallpay'),
                'class' => 'sp-properties-' . $range
            ),
            'sp_min_installments' . __SMALLPAY_RANGE_KEY_MAP__[$range] => array(
                'title' => __('Minimum number of installments', 'smallpay'),
                'type' => 'select',
                'default' => 1,
                'options' => $this->get_options_config_installments(),
                'desc_tip' => __('Select the minimum number of installments for products in the cart.', 'smallpay'),
                'class' => 'sp-properties-' . $range
            ),
            'sp_max_installments' . __SMALLPAY_RANGE_KEY_MAP__[$range] => array(
                'title' => __('Maximum number of installments', 'smallpay'),
                'type' => 'select',
                'default' => 12,
                'options' => $this->get_options_config_installments(),
                'desc_tip' => __('Select the maximum number of installments for products in the cart.', 'smallpay'),
                'class' => 'sp-properties-' . $range
            ),
        );
    }

    public function add_admin_config_script()
    {
        wp_register_style('select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', false, '1.0', 'all');
        wp_register_script('select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '1.0', true);
        wp_enqueue_style('select2css');
        wp_enqueue_script('select2');
    }

    //CONFIGURATION MANAGEMENT

    public function is_enabled()
    {
        if ($this->settings['enabled'] == 'yes') {
            return true;
        }

        return false;
    }

    /**
     * categories saved as payable in installments
     * 
     * @return array
     */
    private function get_enabled_categories($range)
    {
        $enabledCategories = $this->settings['sp_categories' . __SMALLPAY_RANGE_KEY_MAP__[$range]];

        if ($enabledCategories) {
            if (!is_array($enabledCategories)) {
                $enabledCategories = json_decode($enabledCategories);
            }
        } else {
            $enabledCategories = array();
        }

        return $enabledCategories;
    }

    /**
     * checks groups selected min/max installments and amount
     * 
     * @param string $range
     * @return array
     */
    public function check_range_configs($range)
    {
        $ret = array('res' => true, 'msg' => '');

        if ($range == __SMALLPAY_RANGE_1__ || $this->check_if_range_enabled($range)) {
            $resInsNumber = $this->check_config_installments($range);

            $ret['msg'] = ucfirst($this->get_range_desc($range)) . ': ';

            $errorMsg = array();

            if (!$resInsNumber['res']) {
                $ret['res'] = false;
                $errorMsg[] = $resInsNumber['msg'];
            }

            $resAmount = $this->check_config_amounts($range);

            if (!$resAmount['res']) {
                $ret['res'] = false;
                $errorMsg[] = $resAmount['msg'];
            }

            $ret['msg'] .= implode(' | ', $errorMsg);
        }

        return $ret;
    }

    /**
     * 
     * @param type $range
     * @return boolean
     */
    private function check_if_range_enabled($range)
    {
        if ($this->settings['sp_enable' . __SMALLPAY_RANGE_KEY_MAP__[$range]] == 'yes') {
            return true;
        }

        return false;
    }

    /**
     * checks if selected min and max installments numbers, in config section, are valid
     * 
     * @param string $range
     * @return array
     */
    public function check_config_installments($range)
    {
        $MIN_NUMBER = 1;
        $MAX_NUMBER = 12;

        $res = false;
        $msg = array();

        $min = (int) $this->settings['sp_min_installments' . __SMALLPAY_RANGE_KEY_MAP__[$range]];
        $max = (int) $this->settings['sp_max_installments' . __SMALLPAY_RANGE_KEY_MAP__[$range]];

        $minMsg = __('Minimum number of installments selected invalid.', 'smallpay');
        $maxMsg = __('Maximum number of installments selected invalid.', 'smallpay');

        if (!($min >= $MIN_NUMBER && $min <= $MAX_NUMBER)) {
            $msg[] = $minMsg;
        }

        if (!($max >= $MIN_NUMBER && $max <= $MAX_NUMBER)) {
            $msg[] = $maxMsg;
        }

        if (($min >= $MIN_NUMBER && $min <= $MAX_NUMBER) && ($max >= $MIN_NUMBER && $max <= $MAX_NUMBER)) {
            if ($max >= $min) {
                $res = true;
            } else {
                $msg = array($minMsg);
            }
        }

        return array(
            'res' => $res,
            'msg' => count($msg) > 0 ? implode(' | ', $msg) : ''
        );
    }

    /**
     * checks price ranges min/max amounts
     * 
     * @param string $range
     * @return array
     */
    private function check_config_amounts($range)
    {
        $ret = array('res' => true, 'msg' => '');

        $rangeCheckMap = array(
            __SMALLPAY_RANGE_2__ => __SMALLPAY_RANGE_1__,
            __SMALLPAY_RANGE_3__ => __SMALLPAY_RANGE_2__
        );

        $minGroup = (float) $this->settings['sp_min_cart' . __SMALLPAY_RANGE_KEY_MAP__[$range]];
        $maxGroup = (float) $this->settings['sp_max_cart' . __SMALLPAY_RANGE_KEY_MAP__[$range]];

        if ($range == __SMALLPAY_RANGE_1__ || $this->check_if_range_enabled($range)) {
            $errorMsg = array();

            if ($minGroup < 0 || ($minGroup == 0 && !$this->empty_single_price($range, 'min'))) {
                $ret['res'] = false;
                $errorMsg[] = __('Invalid min amount.', 'smallpay');
            }

            //if second or third group, compares this group's min amount with previous group's max amount 
            if (in_array($range, array(__SMALLPAY_RANGE_2__, __SMALLPAY_RANGE_3__)) && $ret['res']) {
                $maxGroupToCheckWith = (float) $this->settings['sp_max_cart' . __SMALLPAY_RANGE_KEY_MAP__[$rangeCheckMap[$range]]];

                if ($minGroup <= $maxGroupToCheckWith) {
                    $ret['res'] = false;
                    $errorMsg[] = __('Invalid min amount. Must be greater than', 'smallpay') . ' ' . $maxGroupToCheckWith;
                }
            }

            if (($maxGroup < $minGroup && $maxGroup != 0) || ($maxGroup == 0 && !$this->empty_single_price($range))) {
                $ret['res'] = false;
                $errorMsg[] = __('Invalid max amount.', 'smallpay');
            }

            $ret['msg'] .= implode(' | ', $errorMsg);
        }

        return $ret;
    }

    private function get_range_desc($range)
    {
        $map = array(
            __SMALLPAY_RANGE_1__ => __('first price range', 'smallpay'),
            __SMALLPAY_RANGE_2__ => __('second price range', 'smallpay'),
            __SMALLPAY_RANGE_3__ => __('third price range', 'smallpay'),
        );

        return $map[$range];
    }

    /**
     * finds the payment configs based on the price
     * 
     * @param float $amount
     * @return array
     */
    private function get_price_range_configs($amount)
    {
        $ret = array('res' => true);

        if ($this->check_amount_in_range(__SMALLPAY_RANGE_1__, $amount)) {
            $ret['group'] = __SMALLPAY_RANGE_1__;
        } else if ($this->check_if_range_enabled(__SMALLPAY_RANGE_2__) &&
                $this->check_amount_in_range(__SMALLPAY_RANGE_2__, $amount)) {
            $ret['group'] = __SMALLPAY_RANGE_2__;
        } else if ($this->check_if_range_enabled(__SMALLPAY_RANGE_3__) &&
                $this->check_amount_in_range(__SMALLPAY_RANGE_3__, $amount)) {
            $ret['group'] = __SMALLPAY_RANGE_3__;
        } else {
            $minGroup = (float) $this->settings['sp_min_cart' . __SMALLPAY_RANGE_KEY_MAP__[__SMALLPAY_RANGE_1__]];
            $maxGroup = (float) $this->settings['sp_max_cart' . __SMALLPAY_RANGE_KEY_MAP__[__SMALLPAY_RANGE_1__]];

            //if second and third price ranges aren't enabled, prices for the first range aren't required so you can pay with its other configs
            if ($minGroup == 0 &&
                    $maxGroup == 0 &&
                    !$this->check_if_range_enabled(__SMALLPAY_RANGE_2__) &&
                    !$this->check_if_range_enabled(__SMALLPAY_RANGE_3__)) {
                $ret['res'] = true;
                $ret['group'] = __SMALLPAY_RANGE_1__;
            } else {
                $ret['res'] = false;
            }
        }

        if ($ret['res']) {
            if ($this->check_config_installments($ret['group'])['res']) {
                $ret['min_ins'] = (int) $this->settings['sp_min_installments' . __SMALLPAY_RANGE_KEY_MAP__[$ret['group']]];
                $ret['max_ins'] = (int) $this->settings['sp_max_installments' . __SMALLPAY_RANGE_KEY_MAP__[$ret['group']]];
            } else {
                $ret['min_ins'] = 1;
                $ret['max_ins'] = 1;
            }

            $ret['min_a'] = (float) $this->settings['sp_min_cart' . __SMALLPAY_RANGE_KEY_MAP__[$ret['group']]];
            $ret['max_a'] = (float) $this->settings['sp_max_cart' . __SMALLPAY_RANGE_KEY_MAP__[$ret['group']]];
            $ret['categories'] = $this->get_enabled_categories($ret['group']);
        }

        return $ret;
    }

    /**
     * 
     * @param string $range
     * @param float $amount
     * @return boolean
     */
    private function check_amount_in_range($range, $amount)
    {
        $minGroup = (float) $this->settings['sp_min_cart' . __SMALLPAY_RANGE_KEY_MAP__[$range]];
        $maxGroup = (float) $this->settings['sp_max_cart' . __SMALLPAY_RANGE_KEY_MAP__[$range]];

        if ($minGroup >= 0 && $maxGroup >= 0) {
            if ($amount >= $minGroup && $amount <= $maxGroup) {
                return true;
            } else if ($minGroup == 0 && $amount <= $maxGroup) {
                return true;
            } else if ($maxGroup == 0 && $amount >= $minGroup) {
                return true;
            }
        }

        return false;
    }

    /**
     * checks if single amount can be empty/0
     * if it isn't __SMALLPAY_RANGE_1__ than it can't
     * if it's in __SMALLPAY_RANGE_1__ and one of the other ranges is enabled than it can't
     * 
     * @param type $range
     * @param type $flag
     * @return boolean
     */
    private function empty_single_price($range, $flag = 'max')
    {
        if ($range !== __SMALLPAY_RANGE_1__) {
            return false;
        }

        if ($flag == 'min') {
            return true;
        }

        if ($this->check_if_range_enabled(__SMALLPAY_RANGE_2__) || $this->check_if_range_enabled(__SMALLPAY_RANGE_3__)) {
            return false;
        }

        return true;
    }

    /**
     * finds the range of installments for the given cart of products
     * 
     * @param Cart $cart
     * @return array
     */
    public function get_installments_number($cart)
    {
        $min = null;
        $max = null;
        $payInOneInstallment = true;

        if ($cart !== null) {
            $payInOneInstallment = false;

            $products = $cart->get_cart();

            /**
             * for each product gets the price range settings from configuration and checks if there are product categories that can be paid in installments
             * than calculates MIN and MAX number of installments that can be used overall for all the products
             * 
             * if there are products that aren't in any of the seted ranges of price or if their categories cannot be paid in installments, returns 1 as MAX and MIN number of installments
             */
            foreach ($products as $product) {
                $p = wc_get_product($product['product_id']);

                // if it is a variable product, the price of the selected variant is retrived
                if ($p->is_type('variable')) {
                    $pPrice = (float) $p->get_price();

                    $pv = new \WC_Product_Variation($product['variation_id']);
                    $pPrice = $pv->get_price();
                } else {
                    $pPrice = (float) $p->get_price();
                }

                $rangeProps = $this->get_price_range_configs($pPrice);

                if (!$rangeProps['res']) {
                    $payInOneInstallment = true;
                    break;
                }

                $categories = wc_get_product_term_ids($product['product_id'], 'product_cat');

                if (count(array_intersect($categories, $rangeProps['categories'])) == 0) {
                    $payInOneInstallment = true;
                    break;
                }

                if ($min == null || $rangeProps['min_ins'] > $min) {
                    $min = $rangeProps['min_ins'];
                }

                if ($max == null || $rangeProps['max_ins'] < $max) {
                    $max = $rangeProps['max_ins'];
                }
            }

            //if $max < $min there aren't numbers of installments in common so you can't pay in installments
            if ($min > $max) {
                $payInOneInstallment = true;
            }
        }

        if ($payInOneInstallment) {
            return array(
                'min' => 1,
                'max' => 1,
                'pay_ins' => false
            );
        } else {
            return array(
                'min' => $min,
                'max' => $max,
                'pay_ins' => true
            );
        }
    }

    /**
     * checks if selected installments number, in payment form, is between config min and max OR if it is a payment in one solution
     * 
     * @param Cart $cart
     * @param int $installments
     * @return boolean
     */
    public function check_installments($cart, $installments)
    {
        $insNumbers = $this->get_installments_number($cart);

        if (($installments >= $insNumbers['min'] && $installments <= $insNumbers['max']) || $installments == 1) {
            return true;
        }

        return false;
    }

    /**
     * checks if a product is payable in more than 1 installment 
     * 
     * @param type $productId
     * @return type
     */
    public function payable_in_installments($productId)
    {
        $ret = array(
            'res' => false,
            'max_ins' => null
        );

        $product = wc_get_product($productId);

        if ($product !== false) {
            $rangeProps = $this->get_price_range_configs((float) $product->get_price());

            if ($rangeProps['res']) {
                $categories = wc_get_product_term_ids($productId, 'product_cat');

                if ($rangeProps['max_ins'] > 1 && count(array_intersect($categories, $rangeProps['categories'])) > 0) {
                    $ret['res'] = true;
                    $ret['max_ins'] = $rangeProps['max_ins'];
                }
            }
        }

        return $ret;
    }

}
