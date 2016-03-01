<?php

include_once('session_handlers.php');
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

include_once("config.php");



//sleep(2);

// this script includes functions.php ONLY if email passes basic checks....search for "include_once".
// this script also includes config.php only if email passes basic checks....see below

$email = trim($_GET["email"]);

if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
	echo "The email address you provided is not valid. Please try again.";exit;
} else {
	// Check DNS records corresponding to a given domain
	// Get MX records corresponding to a given domain.
	list($prefix, $domain) = split("@",$email);
	if (!getmxrr($domain, $mxhosts)) {
		echo "The email address you provided is not valid. Please try again.";exit;
	} else {
		//include_once("functions.php");
		//include_once("config.php");
		
		$brite_verify = '';
		
		$check_banned_domain = "SELECT * FROM bannedDomains WHERE domain=\"$domain\" LIMIT 1";
		$check_banned_domain_result = mysql_query($check_banned_domain);
		if (mysql_num_rows($check_banned_domain_result) == 1) {
			echo 'The email address you provided is not valid. Please try again.';exit;
		}
		
		$check_banned_email = "SELECT * FROM bannedEmails WHERE email=\"$email\" LIMIT 1";
		$check_banned_email_result = mysql_query($check_banned_email);
		if (mysql_num_rows($check_banned_email_result) == 1) {
			echo 'The email address you provided is not valid. Please try again.';exit;
		}
		
		$check_bv_log = "SELECT * FROM briteVerifyLog WHERE email=\"$email\" LIMIT 1";
		$bv_log_result = mysql_query($check_bv_log);
		//echo mysql_error();
		$bv_row = mysql_fetch_object($bv_log_result);
		$is_valid = $bv_row->isValid;
		
		if ($is_valid == 'Y') {
			$_SESSION["email"] = $email;
			echo 'valid';exit;
		} else {
			$brite_verify = BriteVerify($email);
		}
		
		//echo 'BV check: '.$brite_verify;exit;
		if (strstr($brite_verify,'valid</status>') || strstr($brite_verify,'unknown</status>') || strstr($brite_verify,'></status>')) {
			$brite_verify = addslashes($brite_verify);
			$insert_bv_log = "INSERT INTO briteVerifyLog (email,dateTimeAdded,ip,isValid,result)
							VALUES (\"$email\", NOW(), \"$user_ip\",'Y',\"$brite_verify\")";
			$insert_bv_log_result = mysql_query($insert_bv_log);
			//echo mysql_error();
			$_SESSION["email"] = $email;
			echo 'valid';exit;
		} else {
			$p = xml_parser_create();
			xml_parse_into_struct($p, $brite_verify, $vals, $index);
			xml_parser_free($p);
			for ($x = 0; $x < count($vals); $x++) {
				if (strtolower($vals[$x]['tag']) == 'error') {
					$brite_verify = addslashes($brite_verify);
					$insert_bv_log = "INSERT INTO briteVerifyLog (email,dateTimeAdded,ip,isValid,result)
									VALUES (\"$email\", NOW(), \"$user_ip\",'N',\"$brite_verify\")";
					$insert_bv_log_result = mysql_query($insert_bv_log);
					//echo mysql_error();
					echo $vals[$x]['value'];exit;
					break;
				}
			}
		}
	}
}

?>

