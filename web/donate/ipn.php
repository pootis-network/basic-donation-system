<?php
include('config.php');

ini_set('log_errors', true);
ini_set('error_log', getcwd().'/ipn_errors.log');
error_reporting(E_ALL);


/*
// prevent hacker people from reading file
 function requirePostMethod() {
        // require POST requests
        if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Allow: POST', true, 405);
            throw new Exception("Invalid HTTP request method.");
        }
    }

// Check if request valid
try {
   requirePostMethod();
} catch (Exception $e) {
    echo "You shouldn't be here.";
	header('Refresh: 3; URL=./'); 
	exit(0);
}
*/

// autofill data from config!
$cp_merchant_id = '';
$cp_ipn_secret = '';
// not used as its useless
$cp_debug_email = ''; 

    //These would normally be loaded from your database, the most common way is to pass the Order ID through the 'custom' POST field.
    $order_currency = "USD";
    
	// dont preset, grab price via a function() via config.php!
	//$order_total = 1.00;

    function errorAndDie($error_msg) {
        global $cp_debug_email;
        if (!empty($cp_debug_email)) {
            $report = 'Error: '.$error_msg."\n\n";
            $report .= "POST Data\n\n";
            foreach ($_POST as $k => $v) {
                $report .= "|$k| = |$v|\n";
            }
            error_log('CoinPayments IPN Error:', $report);
        }
        die('IPN Error: '.$error_msg);
    }

    if (!isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') {
      //  errorAndDie('IPN Mode is not HMAC');
    }

    //if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
     //   errorAndDie('No HMAC signature sent.');
    //}

    $request = file_get_contents('php://input');
    if ($request === FALSE || empty($request)) {
		errorAndDie('Error reading POST data');	
    }

    if (!isset($_POST['merchant']) || $_POST['merchant'] != trim($cp_merchant_id)) {
		var_dump($request);
		var_dump(trim($cp_merchant_id));
        errorAndDie('No or incorrect Merchant ID passed');
    }

    $hmac = hash_hmac("sha512", $request, trim($cp_ipn_secret));
   // if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
    //if ($hmac != $_SERVER['HTTP_HMAC']) { <-- Use this if you are running a version of PHP below 5.6.0 without the hash_equals function
       // errorAndDie('HMAC signature does not match');
   // }
    
    // HMAC Signature verified at this point, load some variables.

    $txn_id = $_POST['txn_id'];
    $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number'];
	$pid = $item_number;
	$steamid = $_POST['custom'];
	$nick = "Customer";
	
	// work around??
	$order_total = priceNoID($item_number);
	
    $amount1 = floatval($_POST['amount1']);
    $amount2 = floatval($_POST['amount2']);
    $currency1 = $_POST['currency1'];
    $currency2 = $_POST['currency2'];
    $status = intval($_POST['status']);
    $status_text = $_POST['status_text'];

    //depending on the API of your system, you may want to check and see if the transaction ID $txn_id has already been handled before at this point

    // Check the original currency to make sure the buyer didn't change it.
    if ($currency1 != $order_currency) {
        errorAndDie('Original currency mismatch!');
    }    
    
    // Check amount against order total
    if ($amount1 < $order_total) {
        errorAndDie('Amount is less than order total!');
    }
  
    if ($status >= 100 || $status == 2) {
        // Connect to database
		$link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		if (!$link) {
			error_log("Failed to connect to the database: " . mysqli_connect_error());
			//mail( PAYPAL_ID, 'IPN Error', "Failed to connect to the database: " . mysqli_connect_error() . "\n\nIPN Message:" . 'TR');
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
			mysqli_stmt_bind_param($stmt, "s", $txn_id);
			mysqli_stmt_execute($stmt);
			if(mysqli_stmt_fetch($stmt)){
				mysqli_stmt_close($stmt);	
				mysqli_close($link);
				die();
				error_log("Transaction id already exists in database {$txn_id}");
				//mail( PAYPAL_ID, 'IPN Error', "Transaction id already exists in database.\n\nIPN Message:" . 'TR');
				exit(0);
			}
		}
		mysqli_stmt_close($stmt);	
		
		

		// And data to payments
		$query = "INSERT INTO payments ( transactionid, playerid, playername, packageid, time, info) VALUES ( ?, ?, ?, ?, UNIX_TIMESTAMP(), ?)";
		$stmt = mysqli_stmt_init($link);
		var_dump($txn_id);
			//var_dump($steamid);
			//var_dump($nick);
			//var_dump($pid);
		if (mysqli_stmt_prepare($stmt, $query)) {
			$int_pid = (int) $pid;
			//var_dump($int_pid);
			mysqli_stmt_bind_param($stmt, 'sssss', $txn_id, $steamid, $nick, $int_pid, 'TR');
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
							$help = json_encode($command);
							
							mysqli_stmt_bind_param($stmt, "siiiissssi", 
								$serverid, 
								$pid,
								$online,
								$cmdid,
								$delay,
								$help,
								$txn_id,
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
		echo 'survived';
		mysqli_close($link);
		
    } else if ($status < 0) {
        //payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent
    } else {
        //payment is pending, you can optionally add a note to the order page
    }
