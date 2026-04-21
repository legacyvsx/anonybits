<!DOCTYPE html>
<html>
<?php
$host = $_SERVER['HTTP_HOST'];  // Gets the Host header value

// Remove port if present (e.g., if accessing https://1.2.3.4:443)
$host = preg_replace('/:\d+$/', '', $host);

// Check if it's an IP address (IPv4 or IPv6)
if (filter_var($host, FILTER_VALIDATE_IP)) {
	//header("Location: https://morallyrelative.com/ip_landing");
	echo "Nothing to see here, move along please.";
	exit();
}

require_once("estimate.php");

//echo "TEST1: " . $_REQUEST['btcsend']. "<br/>";
//var_dump(is_float($_REQUEST['btcsend']));

if (isset($_REQUEST['btcsend']))
{
	try {
     		//$xmr_amount = get_btc_from_xmr(1);
     		//echo "Equivalent XMR: " . $xmr_amount;
     		$btc_send_amount = floatval($_REQUEST['btcsend']);

        	$xmr_amount = get_xmr_from_btc($btc_send_amount);
        	if ($xmr_amount == 'Insufficient liquidity.')
                	$btc_recv= $xmr_amount;
        	else
                	$btc_recv= get_btc_from_xmr($xmr_amount);
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
    <a href="#home" class="w3-bar-item w3-button"><b>Anony</b>Bits</a>
    <!-- Float links to the right. Hide them on small screens -->
    <div class="w3-right w3-hide-small">
      <a href="#basics" class="w3-bar-item w3-button">The Basics</a>
      <a href="#tech" class="w3-bar-item w3-button">Tech Details</a>
      <a href="#source" class="w3-bar-item w3-button">Source Code</a>
	  <a href="#contact" class="w3-bar-item w3-button">Contact</a>
    </div>
  </div>
</div>

<!-- Header -->
<header class="w3-display-container w3-content w3-wide" style="max-width:1500px;" id="home">
  <img class="w3-image" src="upscalemedia-transformed.jpeg" alt="AnonyBits" />
  
</header>

<!-- Page content -->
<div class="w3-content w3-padding" style="max-width:1564px">

  <!-- Project Section -->
  <div class="w3-container w3-padding-8" id="basics">
    <h3 class="w3-border-bottom w3-border-light-grey w3-padding-16">The Basics</h3>
  </div>

  <div class="w3-row-padding">
    <div class="w3-col 26 m6 w3-margin-bottom">
      <div class="w3-display-container">
	  Fill in how much BTC you want to send to calculate an estimate of how much will be received.<br/><br/>
      <form action="index.php" method="POST">
      <input class="w3-input w3-border" type="text" placeholder="Amount of BTC to send" name="btcsend" value="<?php if (isset($btc_send_amount)) echo $btc_send_amount; ?>">
      <input class="w3-input w3-section w3-border" type="text" placeholder="Amount of BTC that will be received" name="btcrecv" disabled="disabled" value="<?php if (isset($btc_recv)) echo $btc_recv;?>">
      
      <button class="w3-button w3-black w3-section" type="submit">
        Estimate Rate
      </button>
    </form>
      </div>
    </div>
    <div class="w3-col 26 m6 w3-margin-bottom">
      <div class="w3-display-container">
       Once you've calculated how much BTC you want to send, fill in the values below to initiate your transaction and send BTC anonymously.<br/><br/>
	   <form action="send.php" method="POST">
      <input class="w3-input w3-border" type="text" placeholder="Amount of BTC to Send" name="btcsend">
      <input class="w3-input w3-section w3-border" type="text" placeholder="BTC Destination Address" name="btcdest">
      
      <button class="w3-button w3-black w3-section" type="submit">
        Initiate Transaction
      </button>
    </form>
      </div>
    </div>
    
  </div>
<div class="w3-container w3-padding-8">
    
    <p>
	AnonyBits is a free web app to make Bitcoin transactions (almost) anonymous. As most people know, Bitcoin has a completely public blockchain. Once you send a Bitcoin transaction, anyone with the sender or receiver address can view full details of the transaction. AnonyBits works by using <a href="https://getmonero.org">Monero/XMR</a>, an anonymous cryptocurrency which does not have a public blockchain like Bitcoin, as a privacy bridge. Note that this is very similar to what <a href="https://houdiniswap.com">HoudiniSwap</a> does, albeit with different exchange pairs (HoudiniSwap is also not free or open source). XMR journalist Trevor Baadi (@IWriteAboutXMR) has also written about this process here: <a href="https://monero.forex/how-to-make-bitcoin-and-other-crypto-anonymous/">https://monero.forex/how-to-make-bitcoin-and-other-crypto-anonymous/</a>.<br/><br/>

Try it out by filling out the values in the form above, or read further for technical details and source code. Once you click "Initiate Transaction" you will be taken to a page with further instructions. It will specify where to send your BTC and how to track the status of the transaction. The only fees that the user is charged for come from 2 CEX swap providers (see below). The maximum amount you can send is typically just over 1 BTC and is determined by the CEX swap providers. If you try to send more than allowed by the exchange providers, you may lose your coins, so make sure to try "Estimate Rate" before you initiate a transaction. If you receive an "Insufficient liquidity" error, try a smaller amount. 

    </p>
  </div>
  

  <!-- About Section -->
  <div class="w3-container w3-padding-8" id="tech">
    <h3 class="w3-border-bottom w3-border-light-grey w3-padding-16">Tech Details</h3>
    <p>The crux of the anonymization process used by AnonyBits depends on a conversion from and then back to Bitcoin (as depicted in the logo at the top). In particular, 2 different CEX swap providers are used - this ensures that your Bitcoin exits and re-enters the Bitcoin blockchain from entirely different points, making it very difficult to trace. Additionally, one of the CEX swap providers used is <a href="https://exch.cx">exch</a>, which uses a built-in mixer (side note: mixers will not actually make a transaction anonymous per se, but they do help obscure it), increasing privacy. Note that this does not mean the service provides 100% security and anonymity. There is an exploit in XMR which has been known about for ~6 months now called <a href="https://techleaks24.substack.com">Key Image Analysis</a>, which can unmask XMR sender privacy under the right conditions. XMR has a planned upgrade to introduce something called FCMP++ (Full Chain Membership Proofs) in the first half of 2025 (I believe), which most people believe will fix the issue. That said, until FCMP++ launches, it absolutely exists, despite a concerted effort to suppress discussion of it. There is even a leaked video of the company <a href="https://chainalysis.com">Chainalysis</a> giving a presentation to the IRS on exactly how and under what circumstances XMR can be traced (the video has been scrubbed from the internet, but there are screenshots of it floating around). Personally, I am very unhappy (disgusted, actually) with the way the XMR community has chosen to respond to the KIA exploit, especially since they acknowledge its validity here <a href="https://www.reddit.com/r/Monero/comments/1fh92ee/comment/lna087t/">https://www.reddit.com/r/Monero/comments/1fh92ee/comment/lna087t/</a>: "Presto! - you see the true spend, and sender privacy is pretty broken... That's why sooner or later, rings, the weakest of Monero's 3 privacy mechanisms, have to go." Despite this admission, they have chosen to pretend the issue doesn't exist until FCMP++ launches while simultaneously attacking anybody who brings it up on social media (note that is not a criticism of XMR itself, I obviously like XMR since I chose to use it for this project... but the way the XMR community has responded to what is unambiguously a huge security issue is absolutely not ok). XMR is an open source cryptocurrency with a community that is supposed to be focused on security/privacy, not trying to convince people on social media that "XMR is the #1 privacy coin and is 100% untraceable". If there wasn't an issue, Chainalysis would not be <a href="https://www.chainalysis.com/blog/2025-crypto-crime-report-introduction/">practically bragging about it</a> in their "2025 Crypto Crime Trends" blog by intentionally omitting any stats related to XMR, despite the entire crypto community being aware of the stats they collect through many methods such as malware nodes on the XMR blockchain to unmask decoys. The reason XMR is not included in this report/blog is because if it were, they'd have to reveal exactly how that info was obtained, yet the XMR community wants to spin it as "they didn't include XMR because it's truly untraceable". No. If that were the case we wouldn't have leaked screenshots of the same company giving a presentation to the IRS along with a proof of concept KIA exploit.<br/><br/>

This is the main reason I've decided to release the source - I'm interested in seeing if others can improve the security/privacy of AnonyBits, perhaps by using a different cryptocurrency as a privacy bridge or making other modifications. I know that some people feel other privacy coins like Zcash and/or Dash have better security than Monero, so feel free to fork the code and modify it, this would just require modifying the exchange pairs (for example from BTC/XMR,XMR/BTC to BTC/ZEC,ZEC/BTC) and would be simple to do since Zcash and Dash are supported by most CEX swap providers. The other possibility I have considered is using one of the "stronger" privacy coins like ARRR, DERO, or ZANO as a privacy bridge. While these currencies are generally thought to be VERY secure, this presents a different problem since they have very low liquidity and generally can only be obtained by using a DEX, meaning it might not be possible through a web app. Now that said, adapting this to use DEXs might be a worthwhile option to explore since the KIA exploit depends on CEX swap providers/exchanges furnishing the IRS (or some other over-reaching government organization) with a list of XMR transactions including their inputs and outputs.<br/><br/>

Lastly, I just want to point out that AnonyBits uses a method that has been known about and used for quite some time, as explained in the top section. Just because the process is well known doesn't mean I condone you using it for illegal purposes. Don't. If you understand the technical issues described in this section, you should also understand that using CEXs (even though the ones used by AnonyBits are non-KYC) as part of this process may get you caught if you do anything illegal.

    </p>
  </div>

  <div class="w3-container w3-padding-8" id="source">
    <h3 class="w3-border-bottom w3-border-light-grey w3-padding-16">Source Code</h3>
    <p>AnonyBits is written in pure PHP and is extremely lightweight since it keeps no logs or anything of the sort. It can be easily installed on any infrastructure that supports PHP with libcurl. The 2 swap providers used are <a href="https://exch.cx">exch</a> and <a href="https://wizardswap.io">WizardSwap</a>. Note that interfacing with their APIs does not require an API key, though one can be obtained if you wish to participate in their affiliate program which gives you a percentage of the exchange fees generated. I would prefer that you don't do this, and this site will never charge additional fees, but I can't really stop you from doing so if you want to modify the code and set up your own instance.
	<br/><br/>It's also important to note that <a href="https://exch.cx">exch</a> is used as the <i>second</i> CEX swap provider. This is because 1) it allows an exchange to be created without specifying a specific amount, and 2) it includes a mixer (see <a href="https://exch.cx/faq#how_exchanges_are_performed">https://exch.cx/faq#how_exchanges_are_performed</a> for more info). 1) 
ensures that even if the first exchange outputs a slightly different amount than expected (which can happen since rates fluctuate), the transaction will still complete. 2) adds privacy by obscuring the origin of the BTC ultimately sent to the recipient.

</p>
    
  </div>

  
  <!-- Contact Section -->
  <div class="w3-container w3-padding-8" id="contact">
    <h3 class="w3-border-bottom w3-border-light-grey w3-padding-16">Contact</h3>
    <p>Email me at hi[at]anonybits.io or find me on X (@h45hb4ng).</p>
    
  </div>


<!-- End page content -->
</div>

</body>
</html>
