<?php

function createBtcToXmrExchange($btcAmount, $xmrAddress) {
    // Validate inputs
    if (!is_numeric($btcAmount) || $btcAmount <= 0) {
        throw new Exception('Invalid BTC amount');
    }
    
    if (empty($xmrAddress)) {
        throw new Exception('XMR address is required');
    }
    
    
    // Prepare request data
    $requestData = [
        'currency_from' => 'btc',
        'currency_to' => 'xmr',
        'address_to' => $xmrAddress,
        'amount_from' => (string)$btcAmount,
    ];
    
    
    // Initialize cURL session
    $ch = curl_init('https://www.wizardswap.io/api/exchange');
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for cURL errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('API request failed: ' . $error);
    }
    
    curl_close($ch);
    
    // Decode response
    $responseData = json_decode($response, true);
    
    // Check for API errors
    if ($httpCode !== 200 || !isset($responseData['id'])) {
        $errorMessage = isset($responseData['error']) ? $responseData['error'] : 'Unknown API error';
        throw new Exception('WizardSwap API error: ' . $errorMessage);
    }
    //print_r($responseData);
    return array($responseData['id'],$responseData['address_from']);
}

function createXmrToBtcExchange(string $destinationBtcAddress): array {
    // API endpoint
    $endpoint = "https://exch.cx/api/create";
    
    // Request parameters
    $params = [
        'from_currency' => 'XMR',
        'to_currency' => 'BTC',
        'to_address' => $destinationBtcAddress,
        'rate_mode' => 'dynamic',
        'fee_option' => 'f',
        'aggregation' => 'no'
    ];
    
    // Initialize cURL for create request
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest'  // Added XHR header
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception('cURL error: ' . curl_error($ch));
    }
    
    // Get HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Decode JSON response
    $result = json_decode($response, true);
    
    // Check if request was successful
    if ($httpCode !== 200 || !isset($result['orderid'])) {
        throw new Exception('API error: ' . ($result['error'] ?? 'Unknown error'));
    }
    
    // Make a second request to get the deposit address
    $orderEndpoint = "https://exch.cx/api/order";
    $orderParams = ['orderid' => $result['orderid']];
    
    sleep(5); // give it 5 seconds to generate the XMR deposit address, it comes back as "_GENERATING_" if we do this immediately.

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $orderEndpoint . '?' . http_build_query($orderParams));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Requested-With: XMLHttpRequest'  // Added XHR header for second request
    ]);
    
    $orderResponse = curl_exec($ch);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception('cURL error: ' . curl_error($ch));
    }
    
    $orderHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $orderResult = json_decode($orderResponse, true);
    
    if ($orderHttpCode !== 200 || !isset($orderResult['from_addr'])) {
        throw new Exception('API error: ' . ($orderResult['error'] ?? 'Unknown error'));
    }
    
    // Return the exchange ID and XMR deposit address
    return [
        'exchange_id' => $result['orderid'],
        'xmr_deposit_address' => $orderResult['from_addr']
    ];
}

// Example usage:
try {
    $btcAddress = "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa";
    $exchange = createXmrToBtcExchange($btcAddress);
    echo "EXCH Exchange ID: " . $exchange['exchange_id'] . "\n";
    echo "XMR Deposit Address: " . $exchange['xmr_deposit_address'] . "\n";
    echo "Now creating the BTC to XMR exchange...\n";
    $exchange2 = createBtcToXmrExchange(0.01,$exchange['xmr_deposit_address']);
    echo "WizardSwap Exchange ID: " . $exchange2[0] . "\n";
    echo "WizardSwap BTC deposit addr: " . $exchange2[1] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
// Example usage:
/*
try {
     $exchangeId = createBtcToXmrExchange(
         0.01,                                   // BTC amount
         '46ZYRffxHHZZpt8WQ5pHwJHnaAnJJuaeXWKZmCxdT1VQR6oP9QHMpvPVqwY4abQh8YBX7Urbc8MTgBxU44d5vCdCP49PUFh' // XMR address (example)
     );
     //echo "Exchange created with ID: " . $exchangeId;
	print_r($exchangeId);
 } catch (Exception $e) {
     echo "Error: " . $e->getMessage();
 }
*/
?>
