<?php
include('config.php');
$paypal_id = PAYPAL_ID;
if(PAYPAL_SANDBOX){$paypal_id = PAYPAL_ID_SANDBOX;}

// Check if request valid
include('ipnlistener.php');
$listener = new IpnListener();
$listener->use_sandbox = PAYPAL_SANDBOX;
try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
	die("error while validating data. " . $e->getMessage());
}

if ($verified) {
    $pid = $_POST['item_number'];
	$tid = $_POST['txn_id'];
	$cid = $_POST['custom'];
	$price=$PACKAGES[$pid]["price"];
	
	if (	//Check if data match with package data
		$_POST['payment_status'] == "Completed"
		&& strtolower( $_POST['receiver_email'] ) == strtolower( $paypal_id )
		&& doubleval( $_POST['mc_gross'] ) == $price 
		&& $_POST['mc_currency'] == PAYPAL_CURRENCY)
		{
		// Connect to database
		$link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		if (!$link) {
			echo "<center>There was a problem with payment, can not connect to the database: " . mysqli_connect_error() . "<br>Please contact admin to resolve this problem.</center>";
			echo "<br>Transaction Report: <pre>".$listener->getTextReport()."</pre>";
			exit(0);
		}

		// Check if current order already in database
		$query = "SELECT * FROM payments WHERE transactionid=?";
		$stmt = mysqli_stmt_init($link);
		if (mysqli_stmt_prepare($stmt, $query)) {
			mysqli_stmt_bind_param($stmt, "s", $tid);
			mysqli_stmt_execute($stmt);
			if(mysqli_stmt_fetch($stmt)){
				echo "<center>Transaction was successful, you should receive your package soon.</center>";
				if(PAYPAL_SANDBOX){
					echo "<br>Transaction Report(shown only in sandbox mode): <pre>".$listener->getTextReport()."</pre>";
				}
			}else{
				echo "<center>There was a problem with payment, your order is not in database.<br>Please contact admin to resolve this problem.</center>";
				echo "<br>Transaction Report: <pre>".$listener->getTextReport()."</pre>";
			}
		}
		mysqli_stmt_close($stmt);	
		
	}else{
		if($_POST['payment_status'] == "Pending" ){
			echo "<center>Transaction is pending (reason: {$_POST['pending_reason']}), contact admin to resolve the problem.</center>";
		}else{
			echo "<center>There was a problem with payment, contact admin to resolve this problem.<br></center>";
		}
		echo "<br>Transaction Report: <pre>".$listener->getTextReport()."</pre>";
    }
}else{
	echo "invalid request";
}