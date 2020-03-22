<?php

//Database
define("DB_HOST", "");
define("DB_USERNAME", "");
define("DB_PASSWORD", "");
define("DB_DATABASE", "");

// set your currency to recive in, USD is United States Dollar.
define("PAYMENT_CURRENCY", "USD");

//Paypal
define("PAYPAL_CURRENCY", PAYMENT_CURRENCY);
define("PAYPAL_SANDBOX", true);
define("PAYPAL_ID", ""); // Your paypal email address aka id.
define("PAYPAL_ID_SANDBOX", "" ); // https://developer.paypal.com/webapps/developer/applications/accounts

//Bitcoin/CryptoCurrency
define("COINPAYMENTS_CURRENCY", PAYMENT_CURRENCY);

define("COINPAYMENTS_API_PRIVATEKEY",  "" );
define("COINPAYMENTS_API_PUBLICKEY",  "" );
define("COINPAYMENTS_API_MERCHANTID",  "" );

//Steam API
define("STEAM_API", ""); // http://steamcommunity.com/dev/apikey

//Donation System URL
define("DONATE_URL", ""); // Please don't forget that the url must start with http:// or https:// and end with slash(/), DO NOT INCLUDE index.php !!!


//Games
$GAMES = array(
	"gmod" => array(
		'name' => "Garry's mod",
		'icon' => "icons/gmod.png",
		'display' => true,
		'servers' => array("buildrp", "gmr3")
	)
);

//Servers
$SERVERS = array(
	"buildrp" => array(
		'name' => "BuildRP",
		'icon' => "icons/gmod.png",
		'orderfile' => "order.php",
		'packages' => array(1,2,3)
	),
	"gmr3" => array(
		'name' => "gmodRacer3 Alpha",
		'icon' => "icons/gmod.png",
		'orderfile' => "order.php",
		'packages' => array(1,2,3)
	)
);

//Packages
$PACKAGES = array(
	1 => array(
		'title' => "VIP",
		'buytitle' => "BuildRP - VIP",
		'description' => "
			<b>Price: <b style=\"color:green;\">$1</b></b>
			</br>
			<b>Features:</b><br/>
			<b>1.</b> VIP rank<br/>
			<b>2.</b> 10,000 ingame Cash<br/>
			<br/>
			<b style=\"color:green;\">This rank is valid for 30 days.</b>",
		'price' => 1,
		'execute' => array( 
			"darkrp" => array(
				'online' => array(
					0 => array(
						array("darkrp_money",10000),
						array("broadcast", array(255,0,0) ,"%name% has donated for VIP!" )
					)
				),
				'offline' => array(
					0 => array(
						array("cancel", true, "darkrp"),
						array("sv_cmd","ulx", "adduserid", "%steamid%", "vip")
					),
					86400*30 => array(
						array("sv_cmd","ulx", "removeuserid", "%steamid%")
					)
				)
			)
		),
		'checkout' => "source.php"
	),
		2 => array(
		'title' => "Permanent VIP",
		'buytitle' => "BuildRP - PermaVIP",
		'description' => "
			<b>Price: <b style=\"color:green;\">$3</b></b>
			</br>
			<b>Features:</b><br/>
			<b>1.</b> VIP rank<br/>
			<b>2.</b> 35,000 ingame cash<br/>
			<br/>
			<b style=\"color:green;\">This rank is valid forever!</b>",
		'price' => 3,
		'execute' => array( 
			"buildrp" => array(
				'online' => array(
					0 => array(
						array("darkrp_money",65000),
						array("broadcast", array(255,0,0) ,"%name% has donated for VIP!" )
					)
				),
				'offline' => array(
					0 => array(
						array("cancel", true, "darkrp"),
						array("sv_cmd","ulx", "adduserid", "%steamid%", "vip")
					)
				)
			)
		),
		'checkout' => "source.php"
	),

	3 => array(
		'title' => "Mod",
		'buytitle' => "BuildRP - Mod",
		'description' => "
			<b>Price: <b style=\"color:green;\">$15</b></b>
			</br>
			<b>Features:</b><br/>
			<b>1.</b> Mod rank<br/>
			<b>2.</b> 65,000 ingame cash<br/>
			<br/>
			<b style=\"color:red;\">This rank is valid for 30 days and if abused it will be taken away.</b>",
		'price' => 15,
		'execute' => array( 
			"buildrp" => array(
				'online' => array(
					0 => array(
						array("darkrp_money",65000),
						array("broadcast", array(255,0,0) ,"%name% has donated for Mod!" )
					)
				),
				'offline' => array(
					0 => array(
						array("cancel", true, "darkrp"),
						array("sv_cmd","ulx", "adduserid", "%steamid%", "moderator")
					),
					86400*30 => array(
						array("sv_cmd","ulx", "removeuserid", "%steamid%")
					)
				)
			)
		),
		'checkout' => "source.php"
	),


);

// Advanced
function price($pid, $playerid){
	global $PACKAGES;
	return $PACKAGES[$pid]['price']; // What price should user pay
}

function priceNoID($pid){
	global $PACKAGES;
	return $PACKAGES[$pid]['price']; // What price should user pay
}

function ipnPriceValidation($pid, $playerid, $price){
	global $PACKAGES;
	return $PACKAGES[$pid]["price"] == $price; // Check if price valid after payment was done.
}

// !!!!! IGNORE !!!!!
if(PAYPAL_SANDBOX){
	define("PAYPAL_URL", "https://www.sandbox.paypal.com/cgi-bin/webscr" );
}else{
	define("PAYPAL_URL", "https://www.paypal.com/cgi-bin/webscr" );
}



