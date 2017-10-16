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

// Require libraries needed for gateway module functions.

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

spl_autoload_register(function ($class) {

    $prefix = 'tpayLibs\\';
    $base_dir = __DIR__ . '/../../../vendor/tpayLibs/';
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

if (empty($_POST)) {
    exit();
}
require_once __DIR__ . '/../tpaycom/TpayNotification.php';

$tpayNotificationHandler = new TpayNotification((int) $gatewayParams['accountID'], $gatewayParams['secretKey']);

/**
 * Validate callback authenticity.
 *
 */
$resources = $tpayNotificationHandler->checkPayment();

// Retrieve data returned in payment gateway callback
// Varies per payment gateway
$success = $resources["tr_status"] === 'TRUE' ? true : false;
$invoiceId = $resources["tr_crc"];
$transactionId = $resources["tr_id"];
$transactionStatus = $success ? 'Success' : 'Failure';

/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 */
$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

/**
 * Check Callback Transaction ID.
 *
 * Performs a check for any existing transactions with the same given
 * transaction number.
 *
 * Performs a die upon encountering a duplicate.
 */
checkCbTransID($transactionId);

/**
 * Log Transaction.
 *
 * Add an entry to the Gateway Log for debugging purposes.
 *
 * The debug data can be a string or an array. In the case of an
 * array it will be
 *
 * @param string $gatewayName Display label
 * @param string|array $debugData Data to log
 * @param string $transactionStatus Status
 */
logTransaction($gatewayParams['name'], $resources, $transactionStatus);

if ($success) {

    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId Invoice ID
     * @param string $transactionId Transaction ID
     * @param float $paymentAmount Amount paid (defaults to full balance)
     * @param string $gatewayModule Gateway module name
     */
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $resources["tr_paid"],
        $gatewayModuleName

    );

}
