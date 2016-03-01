<?php

include_once("../config.php");

$message = '';
$error = '';
$pixel = '';
$email_check_passed = false;
$signup_success = false;

if ($_POST['submit'] == 'Submit for FREE Access') {
	if (ctype_alnum(trim($_POST['src']))) {
		$src = trim($_POST['src']);
	} else {
		$src = '';
	}
	
	$src = strtoupper($src);
	$email = trim($_POST['email']);
	
	$aJoinListId = array('411','410');
	$subcampid = '3328';
	
	$agree = trim($_POST['agree']);

	if ($agree != 'Y') {
		$error = "You must accept the Terms and Conditions and Privacy Policy.";
	} else {
		if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
			$error = "The email address you provided is not valid. Please try again.";
			$email_check_passed = false;
		} else {
			// Check DNS records corresponding to a given domain
			// Get MX records corresponding to a given domain.
			list($prefix, $domain) = split("@",$email);
			if (!getmxrr($domain, $mxhosts)) {
				$error = "The email address you provided is not valid. Please try again.";
				$email_check_passed = false;
			} else {
				$brite_verify = '';
	
				if ($error == '') {
					$check_banned_domain = "SELECT * FROM bannedDomains WHERE domain=\"$domain\" LIMIT 1";
					$check_banned_domain_result = mysql_query($check_banned_domain);
					if (mysql_num_rows($check_banned_domain_result) == 1) {
						$error = 'The email address you provided is not valid. Please try again.';
						$email_check_passed = false;
					}
				}
	
				if ($error == '') {
					$check_banned_email = "SELECT * FROM bannedEmails WHERE email=\"$email\" LIMIT 1";
					$check_banned_email_result = mysql_query($check_banned_email);
					if (mysql_num_rows($check_banned_email_result) == 1) {
						$error = 'The email address you provided is not valid. Please try again.';
						$email_check_passed = false;
					}
				}
				
				
				if ($error == '') {
					if (LookupImpressionWise($email) == false) {
						$error = "Your e-mail address is invalid. Please try again. If you continue to have an issue, please contact us <a href='http://www.recipe4living.com/contact/' target='_blank'>here</a>.";
						$email_check_passed = false;
					}
				}
					
					
				if ($error == '') {
					$check_current_subscriber = "SELECT * FROM joinEmailActive WHERE email=\"$email\" LIMIT 1";
					$check_current_subscriber_result = mysql_query($check_current_subscriber);
					if (mysql_num_rows($check_current_subscriber_result) == 1) {
						// don't do BV check since the user is already subscribed to at least one newsletter/solo
						$email_check_passed = true;
					} else {
						// do BV check
						if (BullseyeBriteVerifyCheck($email) == true) {
							// BV passed
							$email_check_passed = true;
						} else {
							// BV failed
							$error = 'The email address you provided is not valid. Please try again.';
							$email_check_passed = false;
						}
					}
				}
			}
		}
	}
	
	if ($error != '') {
		$message = "<tr><td colspan='2' style='color:red;' align='center' valign='top'>$error</td></tr>";
		$signup_success = false;
		$attempt = true;
	} else {
		// process sign up request...
		$signup_success = true;
		$user_ip = trim($_SERVER['REMOTE_ADDR']);
		$build_list_id = '';
		foreach ($aJoinListId as $listid) {
			// insert into joinEmailSub
			$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"FFDhtml\",\"$src\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
				
			// insert into joinEmailActive
			$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"FFDhtml\",\"$src\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
			
			
			// get new listid from old listid
			$new_listid = LookupNewListIdByOldListId($listid);
						
			// insert into campaigner
			$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"$subcampid\",\"FFDhtml\",\"$src\",'sub','N')";
			$campaigner_result = mysql_query($campaigner);
			echo mysql_error();

			
			$build_list_id .= $listid.',';
		}
		
		if ($build_list_id != '') {
			$build_list_id = substr($build_list_id,0,strlen($build_list_id)-1);
			//echo "<!-- $build_list_id -->\n\n\n";
			// call to function to send new subscriber to Arcamax.
			$send_to_arcamax = Arcamax($email,$build_list_id,$subcampid,$user_ip,'sub'); // sub or unsub
			//echo "<!-- $send_to_arcamax -->\n\n\n";
			
			// record arcamax server response log
			$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
						VALUES (NOW(),\"$email\",\"$build_list_id\",\"$subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();
		}
		
		$message = "<tr><td colspan='2' style='color:black;padding:20px;' align='center' valign='top'><h4>
					Thank you for signing up for the Fit&FabLiving newsletters!  
					You will receive a welcome e-mail confirming your subscription. 
					Please allow 24-48 hours to receive your first newsletter.
					</h4></td></tr>";
		$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=s&f=FFDhtml$src' width='0' height='0' border='0' />";
	}
} else {
	$attempt = false;
	$src = '';
	if (trim($_GET['src']) !='') {
		if (ctype_alnum(trim($_GET['src']))) {
			$src = trim($_GET['src']);
		}
	}
	$src = strtoupper($src);
	$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=d&f=FFDhtml$src' width='0' height='0' border='0' />";
}

?>
<html>
<head>
<title></title>
</head>
<body>
<?php echo $pixel; ?>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" value="<?php echo $src; ?>" name="src" id="src">
<table width="640px" height="300px" align="center" border="0" cellpadding="0" cellspacing="0" style="font-size:11px;font-family: verdana;background-image:url('http://pics.fitandfabliving.com/dhtml/3328_FF-General_LP.jpg');background-repeat:no-repeat;">
<tr valign="top">
	<td width="100%" height="100%" valign="top">&nbsp;</td>
</tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" style="font-size:11px;font-family: verdana;" width="610px">
<tr><td colspan="2">&nbsp;</td></tr>
<?php echo $message; if ($signup_success) { exit; } ?>
<tr>
	<td colspan="2" align="center">
		<table>
			<tr>
				<td valign="middle"><div STYLE="font-family: Arial, Helvetica, sans-serif;color: #666666;font-size: 20px;font-weight: bold;padding-left: 0px;">E-mail Address</div></td>
				<td valign="middle"><img src="http://pics.fitandfabliving.com/arrow_63x29.gif" border="0"></td>
				<td valign="middle"><input type="text" name="email" value="<?php echo $email; ?>" size="20" maxlength="100"></td>
			</tr>
		</table>
	</td>
</tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
	<td colspan="2" align="center">
		<font size="2">
			<input type="checkbox" id="agree" name="agree" value="Y" <?php if ($agree == 'Y') { echo ' checked '; } ?>> I accept the <a href="http://www.fitandfabliving.com/index.php/terms-of-use.html" target="_blank">Terms & Conditions</a> 
			and <a href="http://www.fitandfabliving.com/index.php/privacy-policy.html?gclid=1" target="_blank">Privacy Policy</a>.
		</font>
	</td>
</tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
	<td colspan="2" align="center">
		<input type="submit" name="submit" value="Submit for FREE Access" style="color:#050;font: bold 100% 'trebuchet ms',helvetica,sans-serif; background-color:#fed; border:1px solid; border-color: #696 #363 #363 #696; ">
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '<?php echo $google_analytics; ?>']);
	_gaq.push(['_trackPageview']);
	(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
</body>
</html>