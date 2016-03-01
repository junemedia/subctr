<?php

include_once("../config.php");

$message = '';
$error = '';
$pixel = '';
$email_check_passed = false;
$signup_success = false;

if ($_POST['submit'] == 'Sign Me Up!') {
	$email = trim($_POST['email']);
	$aJoinListId = $_POST['aJoinListId'];

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
	
	if ($error != '') {
		$message = "<tr><td colspan='2' style='color:red;' align='center' valign='top'><strong>$error</strong></td></tr>";
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
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"3098\",\"R4LSignUpDhtml\",\"R4LSiteRightColumn\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
				
			// insert into joinEmailActive
			$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"3098\",\"R4LSignUpDhtml\",\"R4LSiteRightColumn\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
			
			// get new listid from old listid
			$new_listid = LookupNewListIdByOldListId($listid);
						
			// insert into campaigner
			$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"3098\",\"R4LSignUpDhtml\",\"R4LSiteRightColumn\",'sub','N')";
			$campaigner_result = mysql_query($campaigner);
			echo mysql_error();
			
			$build_list_id .= $listid.',';
		}
		
		if ($build_list_id != '') {
			$build_list_id = substr($build_list_id,0,strlen($build_list_id)-1);
			// call to function to send new subscriber to Arcamax.
			$send_to_arcamax = Arcamax($email,$build_list_id,'3098',$user_ip,'sub'); // sub or unsub

			// record arcamax server response log
			$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
						VALUES (NOW(),\"$email\",\"$build_list_id\",\"3098\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();
		}
		
		$message = "<tr><td colspan='2' style='color:black;padding:20px;' align='center' valign='top'><b>
					Thank you for signing up for your Recipe4Living newsletter(s)! 
					You will receive a welcome e-mail confirming your subscription. 
					Please allow 24-48 hours to receive your first newsletter.
					</b></td></tr>";
		$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=s&f=R4LSignUpDhtml' width='0' height='0' border='0' />";
	}
} else {
	$attempt = false;
	$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=d&f=R4LSignUpDhtml' width='0' height='0' border='0' />";
}

?>
<html>
<head>
<title></title>
<script language="JavaScript">
function check_fields() {
	document.form1.email.style.backgroundColor="";
	var str = '';
	var response = '';

	if (document.form1.email.value == '') {
		str += "Please enter your email address.";
		document.form1.email.style.backgroundColor="yellow";
	} else {
		if (!document.getElementById(1).checked && !document.getElementById(2).checked && !document.getElementById(3).checked && !document.getElementById(4).checked && !document.getElementById(5).checked && !document.getElementById(6).checked) {
			str += "You have not checked any newsletters. Please select at least one newsletter to proceed.";
			document.form1.email.style.backgroundColor="yellow";
		}
	}
	if (str == '') {
		return true;
	} else {
		alert (str);
		return false;
	}
}
</script>
<style type="text/css">
b {font-family: arial,helvetica;font-size:16px;color: #004AB2}
b.small{font-family: arial,helvetica;font-size:13px;color: #004AB2}
b#bottomBold {font-family: arial,helvetica;font-size:14.5px;color: #004AB2}
#bottomSmall {font-family: arial,helvetica;font-size:14.5px;color: #004AB2}
.checkBox {
padding-left: 27px;
}
</style>
</head>
<body>
<?php echo $pixel; ?>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return check_fields();">
<input type="hidden" name="aJoinListId[]" value="396">
<input type="hidden" name="submit" value="Sign Me Up!">
<?php

if ($signup_success == true) { ?>
  <table border="0" height="507" width="675px" cellpadding="0" cellspacing="0" style="background-image:url('http://pics.recipe4living.com/subctrBGImgSignUp.jpg');">
	<tr><td height="170"></td></tr>
<?php } else { ?>
  <table border="0" height="507" width="675px" cellpadding="0" cellspacing="0" style="background-image:url('http://pics.recipe4living.com/subctrBGImg.jpg');">
	<tr><td height="170"></td></tr>
<?php } ?>

	<?php echo $message; if ($signup_success == true) { exit; } ?>
		<tr>
			<td valign="middle" width="60%" align="left" style="padding-left: 30px;font-family: arial,helvetica;font-size:18px;color:#004AB2;font-weight:bold;">
				E-mail Address: <input style="background-color: #fdee8f;" type="text" name="email" value="<?php echo $email; ?>" size="30" maxlength="100"></td><td><INPUT TYPE="image" SRC="http://pics.recipe4living.com/subctrSUbutton.jpg" BORDER="0" ALT="Submit Form" />
			</td>
		</tr>
		<tr>
			<td class="checkBox">
				<input style="cursor: pointer;" type="checkbox" name="aJoinListId[]" value="393" id="1" checked> <b title="Recipes and cooking tips perfect for both beginner and expert chefs!" style="cursor: help;">Daily Recipes</b> <b class="small">(Daily)	</b>
			</td>
		</tr>
		<tr>
			<td class="checkBox">
				<input style="cursor: pointer;" type="checkbox" name="aJoinListId[]" value="395" id="2" checked> <b title="Stretch your dollar further with budget-friendly recipes!" style="cursor: help;">Budget Cooking</b> <b class="small">(3 times/week)</b>	
			</td>
		</tr>
		<tr>
			<td class="checkBox">
				<input style="cursor: pointer;" type="checkbox" name="aJoinListId[]" value="392" id="3" checked> <b title="Find new impressive appetizers, party drinks and party-stopping tips!" style="cursor: help;">Party Tips & Recipes</b> <b class="small">(weekly)</b>
			</td>
		</tr>
		<tr>
			<td class="checkBox">
				<input style="cursor: pointer;" type="checkbox" name="aJoinListId[]" value="394" id="4" checked> <b title="Simple, easy to make recipes. Perfect for a chef on-the-go." style="cursor: help;">Quick & Easy Recipes</b> <b class="small">(3 times/week)</b>
			</td>
		</tr>
		<tr>
			<td class="checkBox">
				<input style="cursor: pointer;" type="checkbox" name="aJoinListId[]" value="511" id="5" checked> <b title="Set it and forget it! Easy recipes designed for your slow cooker." style="cursor: help;">Crockpot Creations</b> <b class="small">(3 times/week)</b>
			</td>
		</tr>
		<tr>
			<td class="checkBox">
				<input style="cursor: pointer;" type="checkbox" name="aJoinListId[]" value="539" id="6" checked> <b title="Find the perfect dish for your next home-cooked meal!" style="cursor: help;">Casserole Cookin'</b> <b class="small">(2 times/week)</b>
			</td>
		</tr>
		<tr>
			<td>
				<b id="bottomBold">Bonus: You will also receive special offers from <br />Recipe4Living partners.</b> 
				&nbsp;<span id="bottomSmall">By signing up for any of our<br />  newsletters, you agree to our <a href="http://www.recipe4living.com/privacy" target="_blank">privacy policy</a> and <a href="http://www.recipe4living.com/terms" target="_blank">terms of use</a>.</span>
			</td>
		</tr>
	</table>
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