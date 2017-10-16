<?php
/*
 * Created by tpay.com.
 * Date: 16.10.2017
 * Time: 12:54
 */

use tpayLibs\src\_class_tpay\PaymentForms\PaymentBasicForms;

class TpayForm extends PaymentBasicForms
{
    public function __construct($id, $secret)
    {
        $this->merchantId = (int)$id;
        $this->merchantSecret = $secret;
        parent::__construct();
    }
}
