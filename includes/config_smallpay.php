<?php
const SMALLPAY_SVIL = 'svil';
const SMALLPAY_STAGING = 'staging';
const SMALLPAY_PROD = 'prod';

//MODIFY ONLY THIS CONSTANT FOR CHANGE ENVIRONMENT
const SMALLPAY_ENV = SMALLPAY_PROD;

if (SMALLPAY_ENV == SMALLPAY_SVIL) {
    include_once($this->path . '/includes/configs/smallpay_svil.php');
}

if (SMALLPAY_ENV == SMALLPAY_STAGING) {
    include_once($this->path . '/includes/configs/smallpay_staging.php');
}

if (SMALLPAY_ENV == SMALLPAY_PROD) {
    include_once($this->path . '/includes/configs/smallpay_prod.php');
}

