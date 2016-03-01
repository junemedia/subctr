<?php

include_once('subctr/session_handlers.php');
if (!(isset($_POST['PHPSESSID'])) && !(isset($_GET['PHPSESSID']))) {
	session_start();
	error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
} else {
	if ($_POST['PHPSESSID']) {
		$PHPSESSID = $_POST['PHPSESSID'];
	} else {
		$PHPSESSID = $_GET['PHPSESSID'];
	}
	
	if (session_id() == '') {
		session_start();
	}
}

include_once("subctr/config.php");


$_SESSION["email"] = '';
$_SESSION["source"] = '';
$_SESSION["subsource"] = '';

// if the user came to this page, send them to subctr/index.php page with all the querystring (if any)
while (list($key,$val) = each($_GET)) {
	$$key = $val;
	if ($key == 'e') {
		if (eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $e)) {
			// Check DNS records corresponding to a given domain
			// Get MX records corresponding to a given domain.
			list($prefix, $domain) = split("@",$e);
			if (getmxrr($domain, $mxhosts)) {
				$_SESSION["email"] = $e;
			}
		}
	}
	if ($key == 's') {
		if (ctype_alnum($s)) {
			$_SESSION["source"] = $s;
		}
	}
	if ($key == 'ss') {
		if (ctype_alnum($ss)) {
			$_SESSION["subsource"] = $ss;
		}
	}
}

//$url = "http://".trim($_SERVER['SERVER_NAME'])."/subctr/index.php?PHPSESSID=".session_id();
if (trim($_SERVER['SERVER_NAME']) == 'r4l.popularliving.com') {
	$url = "http://www.recipe4living.com/index/subctr";
}
if (trim($_SERVER['SERVER_NAME']) == 'fitfab.popularliving.com') {
	$url = "http://www.fitandfabliving.com/index.php/component/content/article/4753.html";
}
if (trim($_SERVER['SERVER_NAME']) == 'wim.popularliving.com') {
	$url = "http://www.workitmom.com/index.php";
}
if (trim($_SERVER['SERVER_NAME']) == 'sf.popularliving.com') {
	$url = "http://www.savvyfork.com/";
}

header("Location:$url");
exit;

?>

