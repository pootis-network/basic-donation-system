<?php
	include "openid.php";
	include "config.php";
	session_start();
	if(isset($_GET["pid"]) && $_GET["pid"]>0){
		$pid = $_GET["pid"];
		$_SESSION['PackageID'] = $_GET["pid"];
	}elseif(isset($_SESSION['PackageID'])){
		$pid = $_SESSION['PackageID'];
	}else{
		$pid = 0;
	}
	if(isset($_GET["game"]) && $_GET["game"] != ""){
		$pagegame = $_GET["game"];
		$_SESSION['PageGame'] = $_GET["game"];
	}elseif(isset($_SESSION['PageGame'])){
		$pagegame = $_SESSION['PageGame'];
	}else{
		$pagegame = "";
	}
	if(isset($_GET["server"]) && $_GET["server"] != ""){
		$pageserver = $_GET["server"];
		$_SESSION['PageServer'] = $_GET["server"];
	}elseif(isset($_SESSION['PageServer'])){
		$pageserver = $_SESSION['PageServer'];
	}else{
		$pageserver = "";
	}
	
	$_SESSION['PackageID'] = $pid;
	$_SESSION['PageGame'] = $pagegame;
	$_SESSION['PageServer'] = $pageserver;
	
	
    $OpenID = new LightOpenID( DONATE_URL );
    
 
    if(!$OpenID->mode){
 
        if(isset($_GET['login'])){
            $OpenID->identity = "http://steamcommunity.com/openid";
            header("Location: {$OpenID->authUrl()}");
        }
 
        
 
    } elseif($OpenID->mode == "cancel"){
 
        echo "User has canceled Authenticiation.";
 
    } elseif(!isset($_SESSION['T2SteamAuth'])){
    	$_SESSION['T2SteamAuth'] = null;
    	if($OpenID->validate()){
    		$_SESSION['T2SteamAuth'] = $OpenID->identity;
    	}
		$_SESSION['T2SteamID64'] = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);
		header("Location: order.php");
    }
 
    if(isset($_GET['logout'])){
        unset($_SESSION['T2SteamAuth']);
        unset($_SESSION['T2SteamID64']);
        header("Location: order.php");
    }
 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Donate</title>
 
        
 
        <link href='style.css' rel='stylesheet' type='text/css' />
        
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  
		<script>
			var $fragment
			function enableNext(enable){
				if(enable){
					$(".button-next").removeClass("button-disabled").addClass("button-enabled");
				}else{
					$(".button-next").removeClass("button-enabled").addClass("button-disabled");
				}
			}
			function enableBack(enable){
				if(enable){
					$(".button-back").removeClass("button-disabled").addClass("button-enabled");
				}else{
					$(".button-back").removeClass("button-enabled").addClass("button-disabled");
				}
			}
			function back(){
				window.location.href=<?php		
					echo "\"".DONATE_URL."index.php?page=packages&game={$pagegame}&server={$pageserver}\"";
				?>;	
			}
			function next(){
			}
			function newPopup(url) {
				popupWindow = window.open(
				url,'popUpWindow','height=200,width=400,left=10,top=10,resizable=no,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes')
			}
			
			$(document).ready(function() {
				enableNext(false);
				enableBack(true);
				var code = "";
			});
			
		</script>
    </head>
    <body>
		<div id="tabs">
			<ul>
				<li><a href="#fragment-1"><div  class="numberCircle">1</div ><span>Games</span></a></li>
				<li><a href="#fragment-2"><div  class="numberCircle">2</div ><span>Servers</span></a></li>
				<li><a href="#fragment-3"><div  class="numberCircle">3</div ><span>Packages</span></a></li>
				<li><a href="#fragment-4"><div  class="numberCircle active">4</div ><span>Checkout</span></a></li>
			</ul>
			<div id="fragment">
				<center>
				<?php if(!isset($_SESSION['T2SteamAuth'])){?>
					<font size="5">Please <a href="?login"><img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png" /></a> so we could process your order.</font>
				<?php }else{

					$communityid = $_SESSION['T2SteamID64'];
					$authserver = bcsub($communityid, '76561197960265728') & 1;
					$authid = (bcsub($communityid, '76561197960265728')-$authserver)/2;
					$steamid = "STEAM_0:$authserver:$authid";

                    $Steam64 = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);
					$Profile = json_decode(file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".STEAM_API."&steamids={$Steam64}"))->response->players[0];

					?>
					
					<table border="1">
					<tr>
					<td><b>Account Name</b></td>
					<td><?php echo $Profile->personaname; ?> (<a href="?logout">Logout</a>)</td>
					</tr>
					<tr>
					<td><b>SteamID</b></td>
					<td><?php echo $steamid; ?></td>
					</tr>
					<tr>
					<td><b>SteamID64</b></td>
					<td><a href="http://steamcommunity.com/profiles/<?php echo $Steam64; ?>"><?php echo $Steam64; ?></a></td>
					</tr>
					<td colspan=2>&nbsp</td>
					<tr>
					</tr>
					<tr>
					<td><b>Package</b></td>
					<td><?php echo $PACKAGES[$pid]["buytitle"]; ?></td>
					</tr>
					</tr>
					<tr>
					<td><b>Price</b></td>
					<td><?php echo $PACKAGES[$pid]["price"]." ".PAYPAL_CURRENCY; ?></td>
					
					</table>
					<br/>
					By submitting this order you agree to our <a href="JavaScript:newPopup('tos.html');">Terms of Service</a>.
					<br/>
					<br/>
                    <?php if(ENABLE_PAYPAL){?>
					<form action='<?php echo PAYPAL_URL; ?>' method='post' name='frmPayPal1'>
						<input type='hidden' name='business' value='<?php
							if(PAYPAL_SANDBOX){
								echo PAYPAL_ID_SANDBOX;
							}else{
								echo PAYPAL_ID;
							}
						?>'>
						<input type='hidden' name='cmd' value='_xclick'>

						<input type='hidden' name='item_name' value='<?php echo $PACKAGES[$pid]["buytitle"];?>'>
						<input type='hidden' name='item_number' value='<?php echo $pid;?>'>
						<input type='hidden' name='amount' value='<?php echo $PACKAGES[$pid]["price"];?>'>
						<input type='hidden' name='no_shipping' value='1'>
						<input type='hidden' name='currency_code' value='<?php echo PAYPAL_CURRENCY;?>'>
						<input type='hidden' name='handling' value='0'>
						<input type='hidden' name='custom' value='<?php echo $Steam64;?>'>
						<input type='hidden' name='cancel_return' value='<?php echo DONATE_URL; ?>'>
						<input type='hidden' name='return' value='<?php echo DONATE_URL; ?>success.php'>
						<input type='hidden' name='notify_url' value='<?php echo DONATE_URL; ?>ipn.php'>

						<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
                    <?php }?>
				<?php }?>
				<br/>
				</center>
			</div>
			<div id="foot">
				<div class="selection-text"></div>
				<span class="button button-back"> 
					<a href="javascript:void(0)" onclick="back()">
						<span class="arrows">
							&#171;&nbsp;
						</span>
						Back
					</a>
				</span>    
				<span class="button button-next"> 
					<a href="javascript:void(0)" onclick="next()">
						Next
						<span class="arrows">
							&nbsp;&#187;
						</span>
					</a>
				</span>
			</div>
		</div>
    </body>
</html>