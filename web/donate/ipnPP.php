<?php
include('ipnlistener.php');
include('config.php');

ini_set('log_errors', true);
ini_set('error_log', DIR_BASE.'/ipn_errors.log');
error_reporting(E_ALL);


$listener = new IpnListener();
$listener->use_sandbox = PAYPAL_SANDBOX;

// Check if request valid
try {
    $listener->requirePostMethod();
} catch (Exception $e) {
    echo "You shouldn't be here.";
	header('Refresh: 3; URL=./'); 
	exit(0);
}

$pid = $_POST['item_number'];
$paypal_id = PAYPAL_ID;
if(PAYPAL_SANDBOX){
	$paypal_id =  PAYPAL_ID_SANDBOX;
}elseif(array_key_exists('paypal_id',$PACKAGES[$pid])){
	$paypal_id = $PACKAGES[$pid]['paypal_id'];
}


$verified = $listener->processIpn();
if ($verified) {
	$tid = $_POST['txn_id'];
	$cid = $_POST['custom'];
	$price=$_POST['mc_gross'];
	
	// Convert comunity id to steamdid and gather data
	$authserver = bcsub($cid, '76561197960265728') & 1;
	$authid = (bcsub($cid, '76561197960265728')-$authserver)/2;
	$steamid = "STEAM_0:$authserver:$authid";
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_URL, "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".STEAM_API."&steamids={$cid}"); 
	$data = curl_exec($ch); 
	curl_close($ch); 
	$Profile = json_decode($data)->response->players[0];
	$nick = $Profile->personaname;
	
	//Check if payment valid
	if (	
		$_POST['payment_status'] == "Completed" // Is payment completed?
		&& strtolower( $_POST['receiver_email'] ) == strtolower( $paypal_id ) // Was payment sent to correct location?
		&& ipnPriceValidation($pid, $steamid, $price) // Does price match? (ipnPriceValidation function is defined in config.php)
		&& $_POST['mc_currency'] == PAYPAL_CURRENCY) // Does currency match?
		{

		// Connect to database
		$link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		if (!$link) {
			error_log("Failed to connect to the database: " . mysqli_connect_error());
			mail( PAYPAL_ID, 'IPN Error', "Failed to connect to the database: " . mysqli_connect_error() . "\n\nIPN Message:" . $listener->getTextReport());
			exit(0);
		}

		// Create payments table if doesnt exist
		$query = "CREATE TABLE IF NOT EXISTS payments (  
		transactionid varchar(125) NOT NULL, 
		playerid varchar(40) NOT NULL, 
		playername varchar(40) NOT NULL,
		packageid int(6) unsigned NOT NULL, 
		time int(10) unsigned NOT NULL, 
		info text NOT NULL, 
		UNIQUE KEY transactionid (transactionid))";
		mysqli_query($link, $query);
		
		// Check if current order already in database
		$query = "SELECT * FROM payments WHERE transactionid=?";
		$stmt = mysqli_stmt_init($link);
		if (mysqli_stmt_prepare($stmt, $query)) {
			mysqli_stmt_bind_param($stmt, "s", $tid);
			mysqli_stmt_execute($stmt);
			if(mysqli_stmt_fetch($stmt)){
				mysqli_stmt_close($stmt);	
				mysqli_close($link);
				die();
				error_log("Transaction id already exists in database {$tid}");
				mail( PAYPAL_ID, 'IPN Error', "Transaction id already exists in database.\n\nIPN Message:" . $listener->getTextReport());
				exit(0);
			}
		}
		mysqli_stmt_close($stmt);	
		
		

		// And data to payments
		$query = "INSERT INTO payments ( transactionid, playerid, playername, packageid, time, info) VALUES ( ?, ?, ?, ?, UNIX_TIMESTAMP(), ?)";
		$stmt = mysqli_stmt_init($link);
		if (mysqli_stmt_prepare($stmt, $query)) {
			mysqli_stmt_bind_param($stmt, "sssis", $tid, $steamid,$nick, $pid, $listener->getTextReport());
			mysqli_stmt_execute($stmt);
		}
		mysqli_stmt_close($stmt);
		
		// Check if orders table exist
		$query = "CREATE TABLE IF NOT EXISTS `commands` (
		id MEDIUMINT NOT NULL AUTO_INCREMENT,
		serverid varchar(40) NOT NULL,
		packageid int(6) unsigned NOT NULL,
		online TINYINT(1) UNSIGNED NOT NULL,
		commandid int(6) unsigned NOT NULL,
		delay INT UNSIGNED NOT NULL,
		activatetime INT UNSIGNED NOT NULL,
		command TEXT NOT NULL,
		activated TINYINT(1) UNSIGNED NOT NULL,
		transactionid varchar(125) NOT NULL,
		playerid varchar(40) NOT NULL,
		playername varchar(40) NOT NULL, 
		PRIMARY KEY (id))";
		mysqli_query($link, $query);

		// Add new commands
		foreach ( $PACKAGES[$pid]["execute"] as $serverid => $modes) {
			foreach ( $modes as $mode => $delays) {
				foreach ( $delays as $delay => $commands) {
					foreach ( $commands as $commandid => $command) {
						$online = 0;
						if($mode == "online"){
							$online = 1;
						}
						$cmdid = $commandid+1;
						$query = "INSERT INTO `commands` ( serverid, packageid, online, commandid, delay, command, transactionid, playerid, playername, activatetime, activated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP() + ? , 0)";
						$stmt = mysqli_stmt_init($link);
						if (mysqli_stmt_prepare($stmt, $query)) {
							mysqli_stmt_bind_param($stmt, "siiiissssi", 
								$serverid, 
								$pid,
								$online,
								$cmdid,
								$delay,
								json_encode($command),
								$tid,
								$steamid, 
								$nick,
								$delay
							);
							mysqli_stmt_execute($stmt);
						}
						mysqli_stmt_close($stmt);
					}
				}
			}
		}
		// Disconnect
		mysqli_close($link);
	}else{
		$error = "ProductID = $pid\n";
		if($_POST['payment_status'] != "Completed" ){
			$error .= "Payment status is not completed ( payment_status = {$_POST['payment_status']} , pending_reason = {$_POST['pending_reason']} )\n Multi currencies normaly takes about one day to complete the order.\n";
		}
		if(strtolower( $_POST['receiver_email'] ) != strtolower( $paypal_id )){
			$error .= "Seller email doesn't match ( ".$paypal_id." != {$_POST['receiver_email']} )\n";
		}
		if(!ipnPriceValidation($pid, $steamid, $price)){
			$error .= "Prices doesn't match ( ".price($pid,$steamid)." != {$_POST['mc_gross']} )\n";
		}
		if($_POST['mc_currency'] != PAYPAL_CURRENCY){
			$error .= "Currency doesn't match ( ".PAYPAL_CURRENCY." != {$_POST['mc_currency']} )\n";
		}
		$error .= $listener->getTextReport();
	    mail( PAYPAL_ID, 'Invalid Product Info', $error);
    }
} else {
    mail( PAYPAL_ID , 'Invalid IPN Validation', $listener->getTextReport());
}