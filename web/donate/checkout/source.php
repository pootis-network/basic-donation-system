<center>
<?php if(!isset($_SESSION['T2SteamID64'])){?>
	<center>
		<table border="1">
			<tr>
			<th><font size="3"> Continue by submiting SteamID </font></th>
			</tr>

			<tr>
			  <td><center>
				<a href="<?php echo "?login&page=checkout&game={$pagegame}&server={$pageserver}&pid={$pagepackage}"; ?>"><img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png" /></a>
				</center></td>
			</tr>
			<tr>
				<td><center>
				<form name="input" action="<?php echo "?loginbysteamid&page=checkout&game={$pagegame}&server={$pageserver}&pid={$pagepackage}"; ?>" method="post">
				<input type="text" name="steamid" value="" placeholder="STEAM_0:0:0">
				<input type="submit" value="Submit">
				</form>
				</center></td>
			</tr>
		</table>
	</center>
<?php }else{

	$communityid = $_SESSION['T2SteamID64'];
	
	// TBH, this code below is cringe, not gonna lie.
	//$authserver = bcsub($communityid, '76561198124525985') & 1;
	//$authid = (bcsub($communityid, '76561198124525985')-$authserver)/2;
	
	// much better!
	
	// import first the super mega ultra important steam() libary!!!!! special rip from somewhere private lol
		function IsSteamID32($input){
			return stristr(trim($input), 'STEAM_0:');
		}

		function IsSteamID64($input){
			return (strlen(trim($input)) == 17);
		}
			function SteamIDTo32($steamid64){
			$steamid64 = trim($steamid64);
	        $steamId1  = substr($steamid64, -1) % 2;
	        $steamId2a = intval(substr($steamid64, 0, 4)) - 7656;
	        $steamId2b = substr($steamid64, 4) - 1197960265728;
	        $steamId2b = $steamId2b - $steamId1;

	        if($steamId2a <= 0 && $steamId2b <= 0) {
	          //  die("SteamID $steamid64 is too small.");
	        }

	        return "STEAM_0:$steamId1:" . (($steamId2a + $steamId2b) / 2);
	    }

	    function SteamIDTo64($steamid32){
	    	$steamid32 = trim($steamid32);
	        if($steamid32 == 'STEAM_ID_LAN' || $steamid32 == 'BOT') {
	            die("Cannot convert SteamID \"$steamid32\" to a community ID.");
	        }
	        if(!IsSteamID32($steamid32)){
	            die("SteamID \"$steamid32\" doesn't have the correct format.");
	        }

	        $steamid32 = explode(':', substr($steamid32, 6));
	        $steamid32 = $steamid32[1] + $steamid32[2] * 2 + 1197960265728;

	        return '7656' . $steamid32;
	    }
	// the deed is done. now convert with better method!!!
	
	// old method
	//$steamid = "STEAM_0:$authserver:$authid";

    // new better method, actually no, thats GAY METHOD!!! 
	//$steamid = SteamIDTo32($communityid);
	
	//$Steam64 = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);
	
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_URL, "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".STEAM_API."&steamids={$communityid}"); 
	$data = curl_exec($ch); 
	curl_close($ch); 
	$Profile = json_decode($data)->response->players[0];
	
    // THIS METHOD BETTER WORK Now
	$steamid = SteamIDTo32($Profile->steamid);
    $steamid64 = $Profile->steamid;
?>
	
	<table border="1" style="border:1px solid black; border-collapse:collapse;">
	<tr>
	<td><b>Account Name</b></td>
	<td><?php echo $Profile->personaname; ?> (<a href="<?php echo "?logout&page=checkout&game={$pagegame}&server={$pageserver}&pid={$pagepackage}"; ?>">Wrong Steam Account?</a>)</td>
	</tr>
	<tr>
	<td><b>SteamID</b></td>
	<td><?php echo $steamid; ?></td>
	</tr>
	<tr>
	<td><b>SteamID64</b></td>
	<td><a href="http://steamcommunity.com/profiles/<?php echo $steamid64; ?>"><?php echo $steamid64; ?></a></td>
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
	<td><?php echo price($pid,$steamid)." ".PAYPAL_CURRENCY; ?></td>
	
	</table>
	<br/>
	By submitting this order you agree to our <a href="" onclick="javascript:window.open('tos.html','popUpWindow','height=200,width=400,left=10,top=10,resizable=no,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes');">Terms of Service</a>.
	<br/>
	<br/>
    <?php if(ENABLE_PAYPAL){?>
	<form action='<?php echo PAYPAL_URL; ?>' method='post' name='frmPayPal1'>
		<input type='hidden' name='business' value='<?php 
			if(PAYPAL_SANDBOX){
				echo PAYPAL_ID_SANDBOX;
			}else{
				if(array_key_exists('paypal_id',$PACKAGES[$pid])){
					echo $PACKAGES[$pid]['paypal_id'];
				}elseif(array_key_exists($pageserver,$SERVERS) && array_key_exists('paypal_id',$SERVERS[$pageserver])){
					echo $SERVERS[$pageserver]['paypal_id'];
				}else{
					echo PAYPAL_ID;
				}
			}
		?>'>
		<input type='hidden' name='cmd' value='_xclick'>

		<input type='hidden' name='item_name' value='<?php echo $PACKAGES[$pid]["buytitle"];?>'>
		<input type='hidden' name='item_number' value='<?php echo $pid;?>'>
		<input type='hidden' name='amount' value='<?php echo price($pid,$steamid);?>'>
		<input type='hidden' name='no_shipping' value='1'>
		<input type='hidden' name='currency_code' value='<?php echo PAYPAL_CURRENCY;?>'>
		<input type='hidden' name='handling' value='0'>
		<input type='hidden' name='custom' value='<?php echo $steamid;?>'>
		<input type='hidden' name='cancel_return' value='<?php echo DONATE_URL; ?>'>
		<input type='hidden' name='return' value='<?php echo DONATE_URL; ?>success.php'>
		<input type='hidden' name='notify_url' value='<?php echo DONATE_URL; ?>ipnPP.php'>

		<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_xpressCheckout.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	</form>
    <?php }?>
	<form action="https://www.coinpayments.net/index.php" method="post">
	<input type="hidden" name="cmd" value="_pay">
	<input type="hidden" name="reset" value="1">
	<input type="hidden" name="want_shipping" value="0">
	<input type="hidden" name="merchant" value="<?php echo COINPAYMENTS_API_MERCHANTID; ?>">
	<input type="hidden" name="currency" value="USD">
    <input type="hidden" name="amountf" value='<?php echo price($pid,$steamid);?>' >
    <input type="hidden" name="ipn_mode" value='button' >
    <input type='hidden' name='item_number' value='<?php echo $pid;?>'>
	<input type="hidden" name="item_name" value='<?php echo $PACKAGES[$pid]["buytitle"];?>'>		
	<input type="hidden" name="allow_extra" value="0">
    <input type='hidden' name='custom' value='<?php echo $steamid;?>'>
	<input type="hidden" name="success_url" value='<?php echo DONATE_URL; ?>success.php'>	
	<input type="hidden" name="cancel_url" value="<?php echo DONATE_URL; ?>">
	<input type='hidden' name='ipn_url' value='<?php echo DONATE_URL; ?>ipn.php'>
	<input type="image" src="https://www.coinpayments.net/images/pub/buynow-med.png" alt="Buy Now with CoinPayments.net">
</form>

<?php }?>
<br/>
</center>