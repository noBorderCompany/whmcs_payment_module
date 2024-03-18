<?php

/*
* Plugin Name: noBorder crypto payment gateway for WHMCS
* Description: <a href="https://noBorder.company">noBorder</a> crypto payment gateway for WHMCS.
* Version: 1.1
* Author: noBorder.company
* Author URI: https://noBorder.company
* Author Email: info@noBorder.company
* Text Domain: noBorder_WHMCS_payment_module
* Tested version up to: 8.8
* copyright (C) 2020 noBorder
* license http://www.gnu.org/licenses/gpl-3.0.html GPLv3 or later
*/

use WHMCS\Database\Capsule;

$invoice_id = intval($_GET['invoiceid']);

if ($invoice_id > 0) {

    require_once __DIR__ . '/../../init.php';
    require_once __DIR__ . '/../../includes/gatewayfunctions.php';
    require_once __DIR__ . '/../../includes/invoicefunctions.php';

    $gatewayParams = getGatewayVariables('noborder');
    if (!$gatewayParams['type']) die('The module is not activated.');
	
	$user_id = $_SESSION['uid'];
	$api_key = $gatewayParams['api_key'];
	$pay_currency = $gatewayParams['pay_currency'];
	
	$action = $_GET['action'];
	$currency = $_GET['currency'];

	if ($action == 'pay' and $invoice_id > 0 and $user_id > 0) {

        $invoice = Capsule::table('tblinvoices')->where('id', $invoice_id)->where('status', 'Unpaid')->where('userid', $user_id)->first();
        
		if (!$invoice)
			die("Unfortunately, this invoice does not exist or does not belong to you. If you think there is a problem, contact support.");

        $user = Capsule::table('tblclients')->where('id', $user_id)->first();
        $desc = $user->firstname . ' ' . $user->lastname . ' ' . $user->phonenumber;
		
        $amount = $invoice->total;
        $systemurl = rtrim($gatewayParams['systemurl'], '/');
		
        $params = array(
			'api_key' => $api_key,
			'amount_value' => $amount,
			'amount_currency' => strtolower($currency),
			'pay_currency' => $pay_currency,
            'order_id' => $invoice_id,
			'desc' => $desc,
            'respond_type' => 'link',
            'callback' => $systemurl . 'modules/gateways/noborder.php?action=confirm&invoiceid=' . $invoice_id
		);
						
		$url = 'https://noborder.company/action/ws/request_create';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
				
		$result = json_decode($response);
		
		if ($result->status != 'success')
			echo '<p>The payment gateway encountered an error. <br> Gateway respond :' . $result->respond .'</p>';
		else {
			$is_Updated = Capsule::table('tblinvoices')->where('id', $invoice_id)->update(['notes' => $result->request_id]);
            if ($is_Updated == 1) header('Location: ' . $result->respond);
            if ($is_Updated == 0) die('The database encountered an error. Please try again and if the problem is not resolved, contact support.');
        }
    }
	
    if ($action == 'confirm' and $invoice_id > 0 and $user_id > 0){
		
		$invoice = Capsule::table('tblinvoices')->where('id', $invoice_id)->where('status', 'Unpaid')->first();
        
		if (!$invoice)
			die("Unfortunately, this invoice does not exist or does not belong to you. If you think there is a problem, contact support.");
			
        $checkGateway = checkCbInvoiceID($invoice_id, $gatewayParams['name']);
        if (!$checkGateway) die("Another payment method has been chosen to pay this invoice.");
		
		$params = array(
			'api_key' => $api_key,
			'order_id' => $invoice_id,
			'request_id' => $invoice->notes
		);
				
		$url = 'https://noborder.company/action/ws/request_status';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		
		$result = json_decode($response);
		
		if ($result->status != 'success') {
			
			logTransaction($gatewayParams['name'], ["GET" => $_GET, "POST" => $_POST, "result" => $result], 'Failure');
			
			$message = noborder_get_filled_message($gatewayParams['failed_massage'], $invoice->notes, $invoice_id);
            Capsule::table('tblinvoices')->where('id', $invoice_id)->update(['notes' => $message . '<br> Gateway respond : ' . $result->respond]);
			
            header('Location: ' . $gatewayParams['systemurl'] . '/viewinvoice.php?id=' . $invoice_id);
		
		} else {
		
			$verify_status = empty($result->status) ? NULL : $result->status;
			$verify_request_id = empty($result->request_id) ? NULL : $result->request_id;
			$verify_amount = empty($result->amount_value) ? NULL : $result->amount_value;
			
			$amount = $invoice->total;
			
			if (number_format($verify_amount, 5) != number_format($amount, 5)) {
				$message = noborder_get_filled_message($gatewayParams['failed_massage'], $verify_request_id, $invoice_id);
				$message .= '<br> Unfortunately, an error occurred in the confirmation process. Please try again or contact support if needed.';
				
				logTransaction($gatewayParams['name'], ["GET" => $_GET, "POST" => $_POST, "result" => $result], 'Failure');
				Capsule::table('tblinvoices')->where('id', $invoice_id)->update(['notes' => $message]);
				header('Location: ' . $gatewayParams['systemurl'] . '/viewinvoice.php?id=' . $invoice_id);
			
			} else {
				addInvoicePayment($invoice_id, $verify_request_id, $amount, 0, $gatewayParams['paymentmethod']);
				$message = noborder_get_filled_message($gatewayParams['success_massage'], $verify_request_id, $invoice_id);
				logTransaction($gatewayParams['name'], ["GET" => $_GET, "POST" => $_POST, "result" => $result], 'Success');
				Capsule::table('tblinvoices')->where('id', $invoice_id)->update(['notes' => $message]);
				header('Location: ' . $gatewayParams['systemurl'] . '/viewinvoice.php?id=' . $invoice_id);
			}
		
		}
	}
}

function noborder_MetaData(){
    return array(
        'DisplayName' => 'noBorder Payment Module',
        'APIVersion' => '1.1',
    );
}

function noborder_config(){
    return [
        "FriendlyName" => [
            "Type" => 'System',
            "Value" => 'noBorder',
        ],
        "api_key" => [
            "FriendlyName" => 'API key',
            "Type" => 'text',
			"Value" => '',
			"Description" => 'Enter your noBorder API key. You can obtain this key by visiting <a href="https://noborder.company/cryptosite" target="_blank">https://noborder.company/cryptosite</a>'
        ],
        "pay_currency" => [
            "FriendlyName" => 'Acceptable crypto currencies',
            "Type" => 'text',
			"Value" => '',
			"Description" => 'Specify the crypto currencies available for payment. You can list multiple currencies separated by a dash (e.g., bitcoin-dogecoin-ethereum). If you leave this field empty, the customers will be able to pay through all the active crypto currencies in the noborder.company.', 'woo-noborder-gateway'
        ],
        "success_massage" => [
            "FriendlyName" => 'Success message',
            "Type" => 'textarea',
            "Value" => 'Your payment has been successfully completed. <br><br> Invoice id : {invoice_id} <br> Track id: {request_id}',
            "Description" => 'Customize the message to display to customers after a successful payment. You can use placeholders `{invoice_id}` and `{request_id}` to display the invoice ID and request IDs.'
        ],
        "failed_massage" => [
            "FriendlyName" => 'Failure message',
            "Type" => 'textarea',
            "Value" => 'Your payment has failed. Please try again or contact the site administrator in case of a problem. <br><br> Invoice id : {invoice_id} <br> Track id: {request_id}',
            "Description" => 'Customize the message to display to customers after a failed payment. You can use placeholders `{invoice_id}` and `{request_id}` to display the invoice ID and request IDs.'
        ]
    ];
}

function noborder_link($params){
	if ($_SESSION['uid'] <= 0)
		$htmlOutput .= '<a href="/clientarea.php" class="btn btn-success btn-sm" id="btnPayNow" value="Submit"> Login / Register </a>';
	else {
		$htmlOutput = '<form method="get" action="modules/gateways/noborder.php">';
		$htmlOutput .= '<input type="hidden" name="action" value="pay">';
		$htmlOutput .= '<input type="hidden" name="invoiceid" value="' . $params['invoiceid'] . '">';
		$htmlOutput .= '<input type="hidden" name="currency" value="' . $params['currency'] . '">';
		$htmlOutput .= '<button type="submit" class="btn btn-success btn-sm" id="btnPayNow" value="Submit"> Pay now </button>';
		$htmlOutput .= '</form>';
	}
    return $htmlOutput;
}

function noborder_get_filled_message($massage, $request_id, $invoice_id){
    return str_replace(["{request_id}", "{invoice_id}"], [$request_id, $invoice_id], $massage);
}

