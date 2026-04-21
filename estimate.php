<?php
function get_xmr_from_btc($btc_amount) {
    // API endpoint configuration
    $api_url = 'https://www.wizardswap.io/api/estimate';
    
    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 
          http_build_query(array('currency_from' => 'BTC','currency_to' => 'XMR',"amount_from" => $btc_amount)));

// Receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($curl_error) {
        throw new Exception("cURL error: " . $curl_error);
    }
    
    // Check HTTP status code
    if ($http_code !== 200) {
        throw new Exception("API request failed with HTTP code: " . $http_code);
    }
    
    // Parse JSON response
    $data = json_decode($response, true);
    //$data=$response;
    if (!$data) {
        throw new Exception("Failed to decode JSON response");
    }
    
    // Verify response structure
    //if (!isset($data['rates']['XMR'])) {
    //    throw new Exception("Invalid API response format");
    //}
    
    // Calculate and return XMR amount
    //return $data['XMR_BTC']['XMR'] * $btc_amount;
	return $data['estimated_amount']; // wizardswap returns a float
}

function get_btc_from_xmr($xmr_amount) {
    // API endpoint configuration
    $api_url = 'https://exch.cx/api/rates';

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
        //curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS,
        //  http_build_query(array('currency_from' => 'BTC','currency_to' => 'XMR',"amount_from" => $btc_amount)));

// Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Check for cURL errors
    if ($curl_error) {
        throw new Exception("cURL error: " . $curl_error);
    }

    // Check HTTP status code
    if ($http_code !== 200) {
        throw new Exception("API request failed with HTTP code: " . $http_code);
    }

    // Parse JSON response
    $data = json_decode($response, true);
    //$data=$response;
    if (!$data) {
        throw new Exception("Failed to decode JSON response");
    }

    // Verify response structure
    //if (!isset($data['rates']['XMR'])) {
    //    throw new Exception("Invalid API response format");
    //}

    // Calculate and return XMR amount
    //return $data['XMR_BTC']['XMR'] * $btc_amount;
        //print_r($data['XMR_BTC']);
	$rateinfo = $data['XMR_BTC'];
	//return $data['XMR_BTC']; // wizardswap returns a float
	$estimate = $rateinfo['rate'] * $xmr_amount;
	if ($estimate > $rateinfo['reserve'])
		return "Insufficient liquidity.";
	else
		return $estimate;
}

function get_xmr_needed_for_btc($btc_amount) // this is going in reverse. given a BTC amount to recv, check how many XMR needed from exch
{
	// API endpoint configuration
    $api_url = 'https://exch.cx/api/rates';

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
        //curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS,
        //  http_build_query(array('currency_from' => 'BTC','currency_to' => 'XMR',"amount_from" => $btc_amount)));

// Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Check for cURL errors
    if ($curl_error) {
        throw new Exception("cURL error: " . $curl_error);
    }

    // Check HTTP status code
    if ($http_code !== 200) {
        throw new Exception("API request failed with HTTP code: " . $http_code);
    }

    // Parse JSON response
    $data = json_decode($response, true);
    //$data=$response;
    if (!$data) {
        throw new Exception("Failed to decode JSON response");
    }

    // Verify response structure
    //if (!isset($data['rates']['XMR'])) {
    //    throw new Exception("Invalid API response format");
    //}
	
    // Calculate and return XMR amount
    //return $data['XMR_BTC']['XMR'] * $btc_amount;
        //print_r($data['XMR_BTC']);
        $rateinfo = $data['XMR_BTC'];
        if ($btc_amount > $rateinfo['reserve'])
		return "Insufficient liquidity.";
	else
	{
		$rate = $rateinfo['rate'];
		return $btc_amount / $rate;
	}
	//return $data['XMR_BTC']; // wizardswap returns a float
        
	//$estimate = $rateinfo['rate'] * $xmr_amount;
        //if ($estimate > $rateinfo['reserve'])
        //        return "Insufficient liquidity.";
        //else
        //        return $estimate;

}

function get_btc_needed_for_xmr($xmr_amount) // last step in reverse, given an XMR amount we want, figure out how much BTC we need to send wizardswap
{
	// This is different since wizardswap's API does not return a rate, only an estimate, however we know from their faq that the rate is typically 2.2%.
	// Still, this makes it difficult to go in reverse (assuming you have the BTC amount that you want received, but want to know how much to send).
	// Because of the limitations of wizardswap's api, this function and the one above are not used.

}

// Example usage:
/*
try {
     //$xmr_amount = get_btc_from_xmr(1);
     //echo "Equivalent XMR: " . $xmr_amount;
     $btc_send_amount = 10;

	$xmr_amount = get_xmr_from_btc($btc_send_amount);
	if ($xmr_amount == 'Insufficient liquidity.')
		$btc_recv= $xmr_amount;
	else
		$btc_recv= get_btc_from_xmr($xmr_amount);
 } catch (Exception $e) {
     $btc_recv= "Error: " . $e->getMessage();
 }

echo "Estimated receive amount (BTC): $btc_recv";
*/
/*
try {
	$btc_recv_amount = 0.1;
	echo get_xmr_needed_for_btc($btc_recv_amount);
 
} catch (Exception $e) {
     echo "Error: " . $e->getMessage();
 }
*/

?>
