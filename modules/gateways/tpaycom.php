<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    tpay.com
 * @copyright 2010-2016 tpay.com
 * @license   LICENSE.txt
 */

spl_autoload_register(function ($class) {

    $prefix = 'tpayLibs\\';
    $base_dir = __DIR__ . '/../../vendor/tpayLibs/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use tpayLibs\src\_class_tpay\Utilities\Util;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Meta_Data_Parameters
 *
 * @return array
 */
function tpaycom_MetaData()
{
    return array(
        'DisplayName' => 'tpaycom',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function tpaycom_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'tpay.com',
        ),
        // a text field type allows for single line text input
        'accountID' => array(
            'FriendlyName' => 'Account ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your account ID here',
        ),
        // a password field type allows for masked text input
        'secretKey' => array(
            'FriendlyName' => 'Secret Key',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter secret key here',
        ),
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see http://docs.whmcs.com/Payment_Gateway_Module_Parameters
 *
 * @return string
 */
function tpaycom_link($params)
{

    require_once 'tpaycom/TpayForm.php';

    $tpayFormProvider = new TpayForm($params['accountID'], $params['secretKey']);
    if ($params['clientdetails']['countrycode'] === 'PL') {
        (new Util())->setLanguage('pl');
    }

    $config = array();
    $config['crc'] = (string)$params['invoiceid'];
    $config['opis'] = $params["description"];
    $config['kwota'] = $params['amount'];
    $config['imie'] = $params['clientdetails']['firstname'];
    $config['nazwisko'] = $params['clientdetails']['lastname'];
    $config['email'] = $params['clientdetails']['email'];
    $config['adres'] = $params['clientdetails']['address1'] . ' ' . $params['clientdetails']['address2'];
    $config['miasto'] = $params['clientdetails']['city'];
    $config['kod'] = $params['clientdetails']['postcode'];
    $config['kraj'] = $params['clientdetails']['country'];
    $config['telefon'] = $params['clientdetails']['phonenumber'];
    $config['wyn_url'] = $params['systemurl'] . 'modules/gateways/callback/' . $params['paymentmethod'] . '.php';
    $config['pow_url'] = $params['returnurl'];
    $config['pow_url_blad'] = $params['returnurl'];

    return $params['currency'] !== 'PLN' ? '<p>Niepoprawna waluta<br/>Invalid currency</p>' :
        $tpayFormProvider->getTransactionForm($config);

}
