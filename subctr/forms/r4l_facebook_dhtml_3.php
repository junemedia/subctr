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
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"2918\",\"R4lFacebookDhtml\",\"R4lFacebookDhtml\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
				
			// insert into joinEmailActive
			$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"2918\",\"R4lFacebookDhtml\",\"R4lFacebookDhtml\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
			
			// get new listid from old listid
			$new_listid = LookupNewListIdByOldListId($listid);
						
			// insert into campaigner
			$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"2918\",\"R4lFacebookDhtml\",\"R4lFacebookDhtml\",'sub','N')";
			$campaigner_result = mysql_query($campaigner);
			echo mysql_error();
			
			$build_list_id .= $listid.',';
		}
		
		if ($build_list_id != '') {
			$build_list_id = substr($build_list_id,0,strlen($build_list_id)-1);
			//echo "<!-- $build_list_id -->\n\n\n";
			// call to function to send new subscriber to Arcamax.
			$send_to_arcamax = Arcamax($email,$build_list_id,'2918',$user_ip,'sub'); // sub or unsub
			//echo "<!-- $send_to_arcamax -->\n\n\n";
			
			// record arcamax server response log
			$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
						VALUES (NOW(),\"$email\",\"$build_list_id\",\"2918\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();
		}
		
		$message = "<tr><td colspan='2' style='color:black;padding:20px;' align='center' valign='top'><b>
					Thank you for signing up for Recipe4Living newsletter(s)! <br>
					You will receive a welcome e-mail confirming your subscription. <br>
					Please allow 24-48 hours to receive your first newsletter.
					</b></td></tr>";
		$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=s&f=R4lFacebookDhtml' width='0' height='0' border='0' />";
	}
} else {
	$attempt = false;
	$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=d&f=R4lFacebookDhtml' width='0' height='0' border='0' />";
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
		if (!document.getElementById(1).checked && !document.getElementById(2).checked && !document.getElementById(3).checked && !document.getElementById(4).checked && !document.getElementById(5).checked && !document.getElementById(6).checked && !document.getElementById(7).checked) {
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
<!--
<script type="text/javascript" src="http://<?php echo trim($_SERVER['SERVER_NAME']); ?>/subctr/js/tooltip.js"></script>
<style>
.tip {
	font:10px/12px Arial,Helvetica,sans-serif;
	border:solid 1px #666666;width:270px;padding:1px;position:absolute;z-index:100;
	visibility:hidden;color:#333333;top:0px;left:90px;
	background-color:#ffffcc;layer-background-color:#ffffcc;
}
</style>
-->
<style type="text/css">

#divBG {
background-image:url('http://pics.recipe4living.com/R4L_sign-up_FB.jpg');
background-repeat: no-repeat;
border:0px;
font-size:11px;
font-family: verdana;
height: 615px;
width: 515px;
position: relative;
}

#divBGconf {
background-image:url('http://pics.recipe4living.com/R4L_sign-up_CF.jpg');
background-repeat: no-repeat;
border:0px;
font-size:11px;
font-family: verdana;
height: 615px;
width: 515px;
position: relative;
}

#emailRow {
position: absolute;
top: 215px;
left: 30px;
font-family: arial,helvetica;
font-size:18px;
color:#004AB2;
font-weight:bold;
}

#nlList {
position: absolute;
top: 250px;
left: 10px;

}

#nlTable {
font-family: arial,helvetica;
font-size:18px;
color:#004AB2;
}

#privacy {
position: absolute;
top: 590px;
left: 185px;
height: 15px;
width: 80px;
cursor: pointer;
}

#terms {
position: absolute;
top: 590px;
left: 295px;
height: 15px;
width: 75px;
cursor: pointer;

}

</style>
</head>
<body>
<?php echo $pixel; ?>

<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return check_fields();">
<input type="hidden" name="aJoinListId[]" value="396">
<input type="hidden" name="submit" value="Sign Me Up!">
<?php if ($signup_success == true) {?>
<div id="divBGconf">
	<div style="position: absolute; top: 215px; left: 30px;">
	<?php echo $message;exit; } ?>
	</div>
	</div>


<div id="divBG">
	<div id="emailRow">
	
	* E-mail Address: <input style="background-color: #fdee8f;" type="text" name="email" value="" size="30" maxlength="100">&nbsp;&nbsp;<INPUT style="vertical-align:bottom;" TYPE="image" SRC="http://pics.recipe4living.com/subctrSUbutton.jpg" BORDER="0" ALT="Submit Form" />
	</div>		
	<div id="nlList">
	<table id="nlTable" align="left" border="0" cellpadding="8" cellspacing="0">
						<tr>
							<td>
								<input type="checkbox" name="aJoinListId[]" value="393" id="1" checked> <b>Daily Recipes <span style="font-size: 11pt;">(Daily)</span></b>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="aJoinListId[]" value="395" id="2"> <b>Budget Cooking<span style="font-size: 11pt;"> (3 issues/week)</span></b>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="aJoinListId[]" value="392" id="4"> <b>Party Tips & Recipes<span style="font-size: 11pt;"> (weekly)</span></b>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="aJoinListId[]" value="394" id="3"> <b>Quick & Easy Recipes<span style="font-size: 11pt;"> (3 issues/week)</span></b>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="aJoinListId[]" value="511" id="5"> <b>Crockpot Creations<span style="font-size: 11pt;"> (3 issues/week)</span></b>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="aJoinListId[]" value="539" id="6"> <b>Casserole Cookin'<span style="font-size: 11pt;"> (2 issues/week)</span></b>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="aJoinListId[]" value="554" id="7"> <b>Copycat Classics<span style="font-size: 11pt;"> (2 issues/week)</span></b>
							</td>
						</tr>
					</table>
	</div>
	<div id="privacy" onClick="window.open('http://www.recipe4living.com/privacy','_blank','width=800,height=1000,scrollbars=yes')">
	</div>
	<div id="terms" onClick="window.open('http://www.recipe4living.com/terms','_blank','width=800,height=1000,scrollbars=yes')">
	</div>
</div>

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
