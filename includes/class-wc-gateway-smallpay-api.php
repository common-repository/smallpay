<?php

class WC_SmallPay_Api
{

    public $response;
    private $url;
    private $uri;
    private $domain;
    private $payerFirstName;
    private $payerLastName;
    private $payerMail;
    private $payerPhone;
    private $orderReference;
    private $totalRecurrences;
    private $totalAmount;
    private $firstPaymentAmount;
    private $urlBack;

    public function __construct($domain, $urlBack, $url = null)
    {
        if ($url == null) {
            $url = SMALLPAY_URL;
        }
        $this->set_env($url);
        $this->set_domain($domain);
        $this->set_uri();
        $this->urlBack = $urlBack;
    }

    /**
     * Set API URL
     *
     * @param string $url - API url
     *
     */
    public function set_env($url)
    {
        $this->url = $url;
    }

    /**
     * Set domain
     *
     * @param string $domain - domain of shop
     *
     */
    public function set_domain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Set API URI
     *
     * @param string $domain - domain of shop
     *
     */
    public function set_uri()
    {
        $this->uri = $this->url . SMALLPAY_URI . $this->domain;
    }

    /**
     * Set order referance
     * 
     * @param string $orderReference
     */
    public function set_orderReference($orderReference)
    {
        $this->orderReference = $orderReference;
    }

    /**
     * Set Payer Info
     *
     * @param string $payerFirstName - First name of the payer.
     * @param string $payerLastName - Last name of the payer.
     * @param string $payerMail - Payer eMail address.
     *
     */
    public function set_payer_info($payerFirstName, $payerLastName, $payerMail, $payerPhone = null)
    {
        $this->payerFirstName = $payerFirstName;
        $this->payerLastName = $payerLastName;
        $this->payerMail = $payerMail;

        if (isset($payerPhone) && $payerPhone) {
            $this->payerPhone = $payerPhone;
        }
    }

    /**
     * Set Order Info
     *
     * @param string $orderReference - .
     * @param string $cardExp - card expiration (AAAAMM).
     * @param string $recurrencesTotal - Total number of recurrences.
     * @param string $amountTotal - Total amount of recurrences.
     * @param string $recurrencesLeft - Left number of recurrences.
     * @param string $amountLeft - Left amount of recurrences.
     *
     */
    public function set_order_info($orderReference, $totalRecurrences, $totalAmount, $firstPaymentAmount)
    {
        $this->orderReference = $orderReference;
        $this->totalRecurrences = $totalRecurrences;
        $this->totalAmount = $totalAmount;
        $this->firstPaymentAmount = $firstPaymentAmount;
    }

    /**
     * sets config setting
     * 
     * @param type $oConfig
     */
    public function set_settings($idMerchant, $service, $secret)
    {
        $this->idMerchant = (int) $idMerchant;
        $this->service = $service;
        $this->secret = $secret;
    }

    /**
     * check smallpay credentials
     * 
     */
    public function checkConfigs()
    {
        $pay_load = array(
            'merchantInfo' => array(
                'idMerchant' => $this->idMerchant,
                'hashPass' => sha1('paymentId=' . 'domain=' . $this->domain . 'serviceSmallpay=' . $this->service . 'uniqueId=' . $this->secret),
            ),
            'serviceSmallpay' => $this->service,
        );

        $this->uri .= '/checkSellConfigs';

        try {
            $this->exec_curl($this->uri, $pay_load, true);
        } catch (\Exception $exc) {
            $error = __('Please verify Smallpay credentials', 'smallpay');

            \WC_SmallPay_Logger::LogExceptionError(new \Exception($error));

            throw new \Exception($error);
        }
    }

    public function send_request_recurring_payment($statusUpdateCallbackUrl = null, $modifyInstallments = true)
    {
        $pay_load = array(
            'merchantInfo' => array(
                'idMerchant' => $this->idMerchant,
                'hashPass' => sha1('paymentId=' . $this->orderReference . 'domain=' . $this->domain . 'serviceSmallpay=' . $this->service . 'uniqueId=' . $this->secret),
            ),
            'payer' => array(
                'firstName' => (string) $this->payerFirstName,
                'lastName' => (string) $this->payerLastName,
                'eMailAddress' => (string) $this->payerMail
            ),
            'serviceSmallpay' => $this->service,
            'totalRecurrences' => (int) $this->totalRecurrences,
            'totalAmount' => (int) $this->totalAmount,
            'firstPaymentAmount' => (int) $this->firstPaymentAmount,
            'description' => (string) '#' . $this->orderReference . ' (' . $this->domain . ')',
            'redirectUrl' => (string) $this->urlBack,
            'modifyInstallments' => $modifyInstallments,
        );

        if ($this->payerPhone) {
            $pay_load['payer']['phoneNumber'] = $this->payerPhone;
        }

        if ($statusUpdateCallbackUrl != null) {
            $pay_load['statusUpdateCallbackUrl'] = (string) $statusUpdateCallbackUrl;
        }

        $this->uri .= '/recurrences/' . $this->orderReference;

        try {
            $res = $this->exec_curl($this->uri, $pay_load, true);

            WC_SmallPay_Logger::Log('Recurring payment request - ' . json_encode($pay_load));
            WC_SmallPay_Logger::Log('Recurring payment response - ' . json_encode($this->response));

            return $res;
        } catch (\Exception $exc) {
            WC_SmallPay_Logger::LogExceptionError(new \Exception('Recurring payment response - ' . $exc->getMessage()));

            throw new Exception(__("Thank you for your purchase. However, the transaction has been declined.", 'smallpay'), 0, $exc);
        }
    }

    public function retrieve_recurrences()
    {
        $pay_load = array(
            'idMerchant' => $this->idMerchant,
            'hashPass' => sha1('paymentId=' . $this->orderReference . 'domain=' . $this->domain . 'serviceSmallpay=' . 'uniqueId=' . $this->secret),
        );

        $this->uri .= '/retrieveRecurrences/' . $this->orderReference;

        try {
            WC_SmallPay_Logger::Log('Retrieve recurrences request - ' . json_encode($pay_load));

            $res = $this->exec_curl($this->uri, $pay_load, true);

            WC_SmallPay_Logger::Log('Retrieve recurrences response - ' . json_encode($this->response));

            return $res;
        } catch (\Exception $exc) {
            WC_SmallPay_Logger::LogExceptionError($exc);

            throw new Exception(__('Error while retrieving installments info from Smallpay', 'smallpay'), 0, $exc);
        }
    }

    private function exec_curl($request_uri, $pay_load, $url_complete = false)
    {
        if ($url_complete) {
            $url = $request_uri;
        } else {
            $url = $this->url . $request_uri;
        }

        $args = array(
            'body' => json_encode($pay_load),
            'timeout' => '30',
            'headers' => array('Content-Type' => 'application/json', 'accept' => 'application/json'),
        );

        $response = wp_remote_post($url, $args);

        if (is_array($response) && json_decode($response['response']['code'], true) == '204') {
            return true;
        }

        if (is_array($response) && json_decode($response['response']['code'], true) != '200') {
            \WC_SmallPay_Logger::Log(json_encode(array('url' => $url, 'pay_load' => $pay_load, 'response' => $response['response'])), 'error');
            throw new \Exception(json_encode($response['response']['code'], true) . ' - ' . json_decode($response['response']['message']));
        }

        if (is_array($response)) {
            $this->response = json_decode($response['body'], true);

            return true;
        } else {
            \WC_SmallPay_Logger::Log(json_encode(array('url' => $url, 'pay_load' => $pay_load, 'response' => $response['response'])), 'error');

            return false;
        }
    }

}
