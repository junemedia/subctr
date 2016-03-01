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

$_SESSION['passed_captcha'] = 'no';

if ($_POST['submit'] == 'Sign In' && $_POST['email'] != '' && strlen($_POST['security_code']) == 3) {
	$email = addslashes($_POST['email']);
	
	if( $_SESSION['security_code'] == $_POST['security_code'] && !empty($_SESSION['security_code'] ) ) {
		// Insert you code for processing the form here, e.g emailing the submission, entering it into a database.
		unset($_SESSION['security_code']);
		
		$error = '';
		
		$email_check_passed = false;
		
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
				//include_once("functions.php");
				//include_once("config.php");
				
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
						$_SESSION["email"] = $email;
						$email_check_passed = true;
					} else {
						// do BV check
						if (BullseyeBriteVerifyCheck($email) == true) {
							// BV passed
							$_SESSION["email"] = $email;
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
		
		if ($email_check_passed == true) {
			$_SESSION['passed_captcha'] = 'yes';
			$bounce_count = getBounceCountFromArcamax($email);

			$sign_in = "INSERT IGNORE INTO signin (email,dateTimeAdded,site,ipaddr) 
						VALUES (\"$email\", NOW(), \"$host\",\"$user_ip\")";
			$sign_in_result = mysql_query($sign_in);
			
			$url = "http://".trim($_SERVER['SERVER_NAME'])."/subctr/check.php?PHPSESSID=".session_id();
			header("Location:$url");
			exit;
		} else {
			$message = "<tr><td colspan='2' style='color:red;'>$error</td></tr><tr><td colspan='2'>&nbsp;</td></tr>";
			$_SESSION["email"] = '';
			$_SESSION['bouncecount'] = '';
		}
	} else {
		unset($_SESSION['security_code']);
		
		$message = "<tr><td colspan='2' style='color:red;'>
				The code you entered was incorrect.
				</td></tr><tr><td colspan='2'>&nbsp;</td></tr>";
	}
}

if ($host == 'sf') {
	$loader_img = 'sf_loader.gif';
}

if ($host == 'r4l') {
	$loader_img = 'r4l_loader.gif';
}

if ($host == 'wim') {
	$loader_img = 'wim_loader.gif';
}

if ($host == 'fitfab') {
	$loader_img = 'fitfab_loader.gif';
}

if ($host == 'br') {
	$loader_img = 'br_loader.gif';
}

?>

<html>
<head>
<meta name="keywords" content="recipe newsletters, free recipe newsletter, recipe newsletter, daily recipe newsletter, crockpot recipe newsletter, slow cooker recipe newsletter, free slow cooker recipes, free crockpot recipes, free crockpot recipe newsletter budget cooking recipes, budget cooking newsletter,party recipes and tips, party recipe newsletter, quick and easy recipe newsletter, quick and easy recipes" />
<meta name="description" content="Looking for recipes and cooking tips? You have come to the right place!" />
<title>Manage My Newsletters</title>
<SCRIPT LANGUAGE=JavaScript SRC="js/ajax.js" TYPE=text/javascript></script>
<script language="JavaScript">
function check_fields() {
	document.form1.email.style.backgroundColor="";
	document.form1.security_code.style.backgroundColor="";
	var str = '';
	var response = '';
	
	if (document.form1.email.value == '') {
		str += "* Please enter your email address.";
		document.form1.email.style.backgroundColor="yellow";
	}
	
	if (document.form1.security_code.value == '') {
		str += "* Please enter Security Code.";
		document.form1.security_code.style.backgroundColor="yellow";
	}
	
	if (str == '') {
		document.getElementById('loader').style.visibility = 'visible';
		return true;
	} else {
		alert (str);
		return false;
	}
}
</script>
<script>window.scroll(0,0);</script>
<style>
body {
	font-family: verdana;
}
</style>

</head>
<body>
<table width="575px" align="center" border="0" id="sub_form" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
	<tr>
		<td colspan="2">
		<?php
			if ($host == 'r4l') { ?>
				<font color="#0F22B0" size="4" style="font-family: arial;">Manage My Newsletters</font>
			<?php }
			if ($host == 'fitfab') { ?>
				<font color="#EC519D" size="4" style="font-family: arial;">Manage My Newsletters</font>
			<?php }
			if ($host == 'wim') { ?>
				<font color="#2789BD" size="4" style="font-family: arial;">Manage My Newsletters</font>
			<?php }
			if ($host == 'sf') { ?>
				<font color="#F99D1C" size="4" style="font-family: arial;">Manage My Newsletters</font>
			<?php }
			if ($host == 'br') { ?>
				<font color="#0F22B0" size="4" style="font-family: arial;">Manage My Newsletters</font>
			<?php }
		?>
		</td>
	</tr>
		
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<?php echo $message; ?>
		
	<tr>
		<td colspan="2">
		<b>Welcome to the Subscription Management Center</b>
		</td>
	</tr>
		
	<tr><td colspan="2">&nbsp;</td></tr>
	
	
	<tr>
		<td colspan="2">
			To make changes to your subscription status, please complete the form below. 
			On the next page you can update your information, add or delete newsletters you 
			subscribe to, and change your e-mail address. Thank you!
		</td>
	</tr>
		
	<tr><td colspan="2">&nbsp;</td></tr>
		
		
		
	<!-- form starts here -->
	<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return check_fields();">
	<input type="hidden" name="PHPSESSID" id="PHPSESSID" value="<?php echo session_id(); ?>">
	<tr>
		<td>Email:</td>
		<td>
			<input type="text" name="email" value="<?php echo $_SESSION["email"]; ?>" maxlength="100" size="30">
		</td>
	</tr>
	
	
	<tr>
		<td>Verification:</td>
		<td>
			<img src="CaptchaSecurityImages.php?PHPSESSID=<?php echo session_id(); ?>">
		</td>
	</tr>
	
	
	<tr>
		<td>Text in Image:</td>
		<td>
			<input id="security_code" name="security_code" type="text" maxlength="3" size="30" onkeyup="this.value=this.value.toUpperCase();">
		</td>
	</tr>
	
	
	<tr>
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="Sign In">
			<div id="loader"><img src="/subctr/images/<?php echo $loader_img; ?>"></div>
		</td>
	</tr>
	</form>
	<!-- form ends here -->
</table>

<script type="text/javascript">
	document.getElementById('loader').style.visibility = 'hidden';
</script>


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
