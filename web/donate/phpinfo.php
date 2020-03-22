<?php
include('config.php');
if ($_SERVER['PHP_AUTH_PW'] == STEAM_API) {
	if($_SERVER['PHP_AUTH_PW'] == STEAM_API){ // phpinfo is only available if STEAM_API, defined in config.php, is known.
		phpinfo();
	}
} else {
	header('WWW-Authenticate: Basic');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}
?>