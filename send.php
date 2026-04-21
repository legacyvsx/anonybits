<!DOCTYPE html>
<html>
<?php

require_once("exchange.php");

if (isset($_REQUEST['btcsend']) && isset($_REQUEST['btcdest']))
{
	try {
     		//$xmr_amount = get_btc_from_xmr(1);
     		//echo "Equivalent XMR: " . $xmr_amount;
     		$btc_send_amount = floatval($_REQUEST['btcsend']);
		if (!ctype_alnum($_REQUEST['btcdest']))
			$btc_recv= "Invalid BTC address."; //probably should do some real BTC address validation
		else
		{
			//$btcAddress = "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa";
    			$btcAddress=$_REQUEST['btcdest'];
			$exchange = createXmrToBtcExchange($btcAddress);
    			//echo "EXCH Exchange ID: " . $exchange['exchange_id'] . "\n";
    			$id1 = $exchange['exchange_id'];
			//echo "XMR Deposit Address: " . $exchange['xmr_deposit_address'] . "\n";
    			//echo "Now creating the BTC to XMR exchange...\n";
    			$exchange2 = createBtcToXmrExchange(0.01,$exchange['xmr_deposit_address']);
    			//echo "WizardSwap Exchange ID: " . $exchange2[0] . "\n";
    			$id2 = $exchange2[0];
			//echo "WizardSwap BTC deposit addr: " . $exchange2[1] . "\n";
			$btc_recv = $exchange2[1];
		}
 	} catch (Exception $e) {
     		$btc_recv= "Error: " . $e->getMessage();
 	}

	//echo "TEST: $btc_recv<br/>";

}


?>
<head>
<title>AnonyBits</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>

<!-- Navbar (sit on top) -->
<div class="w3-top">
  <div class="w3-bar w3-white w3-wide w3-padding w3-card">
    <a href="index.php#home" class="w3-bar-item w3-button"><b>Anony</b>Bits</a>
    <!-- Float links to the right. Hide them on small screens -->
    <div class="w3-right w3-hide-small">
      <a href="index.php#basics" class="w3-bar-item w3-button">The Basics</a>
      <a href="index.php#tech" class="w3-bar-item w3-button">Tech Details</a>
      <a href="index.php#source" class="w3-bar-item w3-button">Source Code</a>
	  <a href="index.php#contact" class="w3-bar-item w3-button">Contact</a>
    </div>
  </div>
</div>

<!-- Header -->
<header class="w3-display-container w3-content w3-wide" style="max-width:1500px;" id="home">
  <img class="w3-image" src="upscalemedia-transformed.jpeg" alt="AnonyBits" />
  
</header>

<!-- Page content -->
<div class="w3-content w3-padding" style="max-width:1564px">

<div class="w3-container w3-padding-8">
	<h3 class="w3-border-bottom w3-border-light-grey w3-padding-16">Send Bitcoin Anonymously</h3>
    <p>
	We keep no logs whatsoever, so make sure to note down the information on this page. You will not be able to bookmark or refresh it. In particular, you will want to keep the tracking links 
	for each of your exchanges.<br/><br/>
	Send <?php if (isset($_REQUEST['btcsend'])) echo floatval($_REQUEST['btcsend']); ?> BTC to <?php echo $btc_recv; ?> (<a href="https://wizardswap.io">WizardSwap</a>)<br/>
	WizardSwap Exchange ID: <?php if (isset($id1)) echo "<a href=\"https://wizardswap.io/id={$id1}\">$id1</a>"; ?><br/>
	exch Exchange ID: <?php if (isset($id2)) echo "<a href=\"https://exch.cx/order/{$id2}\">$id2</a>"; ?><br/>
	<br/>
	After the first exchange completes, the second will automatically start, and your BTC will then arrive at the destination. All you need to do is send your BTC to the address above. You can track your funds through the process using the links above.
    </p>
  </div>
  


<!-- End page content -->
</div>

</body>
</html>
