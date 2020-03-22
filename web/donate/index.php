<?php 
	ini_set('log_errors', true);
	ini_set('error_log', dirname(__FILE__).'/index_errors.log');
	
	session_name("DonationSystem");
	session_start();
	include('config.php');
	$page = "games"; // games, servers, packages
	if(isset($_GET["page"])){
		$page = $_GET["page"];
	}
	$pagegame = "";
	if(isset($_GET["game"])){
		$pagegame = $_GET["game"];
	}
	$pageserver = "";
	if(isset($_GET["server"])){
		$pageserver = $_GET["server"];
	}
	$pagepackage = "";
	if(isset($_GET["pid"])){
		$pagepackage = $_GET["pid"];
	}
	$pid = "";
	if(isset($_GET["pid"])){
		$pid = $_GET["pid"];
	}
	
	$backpage = "";
	$nextpage = "";
	
	switch ($page) {
		case "games":
			$nextpage = "servers";
			break;
		case "servers":
			$backpage = "games";
			$nextpage = "packages";
			break;
		case "packages":
			$backpage = "servers";
			$nextpage = "checkout";
			break;
		case "checkout":
			$backpage = "packages";
			$nextpage = "";
			break;
	}
	
	include "openid.php";
	$OpenID = new LightOpenID( DONATE_URL );
		
	
	if(!isset($_SESSION['T2SteamID64'])){
		if(isset($_GET['loginbysteamid'])){
			$steamid = $_POST['steamid'];
			$gametype = 0;
			$authserver = 0;
			$clientid = '';
			$steamid = str_replace('STEAM_', '' ,$steamid);
			
			$parts = explode(':', $steamid);
			$gametype = $parts[0];
			$authserver = $parts[1];
			$clientid = $parts[2];
			$_SESSION['T2SteamID64'] = bcadd((bcadd('76561197960265728', $authserver)), (bcmul($clientid, '2')));
		}else{
			if(!$OpenID->mode){
				if(isset($_GET['login'])){
					$OpenID->identity = "http://steamcommunity.com/openid";
					header("Location: {$OpenID->authUrl()}");
				}
			} elseif($OpenID->mode == "cancel"){
				echo "User has canceled Authenticiation.";
			} else {			
				$_SESSION['T2SteamAuth'] = null;
				if($OpenID->validate()){
					$_SESSION['T2SteamAuth'] = $OpenID->identity;
					$_SESSION['T2SteamID64'] = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION['T2SteamAuth']);
				}
			}
		}
	}

	if(isset($_GET['logout'])){
		unset($_SESSION['T2SteamAuth']);
		unset($_SESSION['T2SteamID64']);
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Donate</title>

        <link href='style.php' rel='stylesheet' type='text/css' />
        
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  
		<script>
			var $fragment
			var $msg;
			
			var game;
			
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
			function next(){
				
				window.location.href=<?php		
				switch ($page) {
					case "games":
						echo "\"".DONATE_URL."index.php?page=servers&game=\"+$('input:radio[name=game]:checked').val()";
						break;
					case "servers":
						echo "\"".DONATE_URL."index.php?page=packages&game={$pagegame}&server=\"+$('input:radio[name=server]:checked').val()";
						break;
					case "packages":
						echo "\"".DONATE_URL."index.php?page=checkout&game={$pagegame}&server={$pageserver}&pid=\"+id+\"\"";
						break;
					case "checkout":
						echo "\"\"";
						break;
				}
				?>;	
			}
			function back(){
				
				window.location.href=<?php
				switch ($page) {
					case "games":
						echo "\"\"";
						break;
					case "servers":
						echo "\"".DONATE_URL."index.php?page=games\"";
						break;
					case "packages":
						echo "\"".DONATE_URL."index.php?page=servers&game={$pagegame}\"";
						break;
					case "checkout":
						echo "\"".DONATE_URL."index.php?page=packages&game={$pagegame}&server={$pageserver}\"";
						break;
				}
				?>;	
			}
			
			$(document).ready(function() {
				
				enableNext(false);
				enableBack(<?php
					if($page != "games"){
						echo "true";
					}else{
						echo "false";
					}
				?>);
				
				<?php
				if($page != "checkout"){ ?>
				$fragment = $("#fragment").buttonset();
				
				jQuery('#navigation').accordion({
					active: false,
					header: '.head',
					navigation: true,
					event: 'mouseover',
					fillSpace: true,
					animated: 'easeslide'
				});				
				
				
				$('.accordionButton').click(function() {
					if($(this).is('.on') != true) {
						id = $(this).attr( "id" );
						$('.accordionButton').removeClass('on');  
						$('.accordionContent').slideUp('normal');
			   
						if($(this).next().is(':hidden') == true) {
							$(this).addClass('on');		  
							$(this).next().slideDown('normal');
							enableNext(true);
						} 
					 }
				});
				$('.accordionContent').hide();
				<?php } ?>
			});
			
		</script>
    </head>
    <body>
		<div id="tabs">
			<ul>
				<li><a href="#fragment-1"><div  class="numberCircle <?php if($page == "games"){ echo "active"; } ?>">1</div ><span>Games</span></a></li>
				<li><a href="#fragment-2"><div  class="numberCircle <?php if($page == "servers"){ echo "active"; } ?>">2</div ><span>Servers</span></a></li>
				<li><a href="#fragment-3"><div  class="numberCircle <?php if($page == "packages"){ echo "active"; } ?>">3</div ><span>Packages</span></a></li>
				<li><a href="#fragment-4"><div  class="numberCircle <?php if($page == "checkout"){ echo "active"; } ?>">4</div ><span>Checkout</span></a></li>
			</ul>
			
			<div id="fragment">
				<?php
				if($page == "games"){
					$count = 0;
					foreach ($GAMES as $gameid => $game) {
						if($game["display"]){
							$count++;
							?>
							<input type="radio" id="gradio<?php echo $count; ?>" name="game" value="<?php echo $gameid; ?>" onclick="enableNext(true);"/>
							<label for="gradio<?php echo $count; ?>"><img src="<?php echo $game["icon"]; ?>"/><span><?php echo $game["name"]; ?></span></label>
							<?php
						}
					}
				}elseif($page == "servers"){
					$count = 0;
					foreach ($GAMES[$pagegame]['servers'] as $serverid) {
						$count++;
						?>
						<input type="radio" id="gradio<?php echo $count; ?>" name="server" value="<?php echo $serverid; ?>" onclick="enableNext(true);"/>
						<label for="gradio<?php echo $count; ?>"><img src="<?php echo $SERVERS[$serverid]["icon"]; ?>"/><span><?php echo $SERVERS[$serverid]["name"]; ?></span></label>
						<?php
					}
				}elseif($page == "packages"){
					$count = 0;
					?><div id="wrapper"><?php
					foreach ($SERVERS[$pageserver]["packages"] as $packageid) {
						$count++;
						?>
						<div id="<?php echo $packageid; ?>" class="accordionButton"><?php echo $PACKAGES[$packageid]["title"]; ?></div>
						<div id="c<?php echo $packageid; ?>" class="accordionContent">
							<?php echo $PACKAGES[$packageid]["description"]; ?>
						</div>
						<?php
					}
					
					?></div><?php
				}elseif($page == "checkout"){
					include("checkout/".$PACKAGES[$pagepackage]["checkout"]);
				}
				?>
			</div>
			
			<div id="foot">
				<div class="selection-text"></div>
				<span class="button button-back"> 
					<a href="javascript:void(0)" onclick="back()">
						<span class="arrows">&#171;&nbsp;</span>Back
					</a>
				</span>    
				<span class="button button-next"> 
					<a href="javascript:void(0)" onclick="next()">
						Next<span class="arrows">&nbsp;&#187;</span>
					</a>
				</span>
			</div>
		</div>
    </body>
</html>