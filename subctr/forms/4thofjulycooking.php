<?php

include_once("../config.php");

$message = '';
$error = '';
$pixel = '';
$email_check_passed = false;
$signup_success = false;

if ($_POST['process'] == 'Y') {
	$email = trim($_POST['email']);
	$aJoinListId = array('393','396');

	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
		$error = "Your email address is invalid.";
		$email_check_passed = false;
	} else {
		// Check DNS records corresponding to a given domain
		// Get MX records corresponding to a given domain.
		list($prefix, $domain) = split("@",$email);
		if (!getmxrr($domain, $mxhosts)) {
			$error = "Your email address is invalid.";
			$email_check_passed = false;
		} else {
			$brite_verify = '';

			if ($error == '') {
				$check_banned_domain = "SELECT * FROM bannedDomains WHERE domain=\"$domain\" LIMIT 1";
				$check_banned_domain_result = mysql_query($check_banned_domain);
				if (mysql_num_rows($check_banned_domain_result) == 1) {
					$error = "Your email address is invalid.";
					$email_check_passed = false;
				}
			}

			if ($error == '') {
				$check_banned_email = "SELECT * FROM bannedEmails WHERE email=\"$email\" LIMIT 1";
				$check_banned_email_result = mysql_query($check_banned_email);
				if (mysql_num_rows($check_banned_email_result) == 1) {
					$error = "Your email address is invalid.";
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
						$error = "Your email address is invalid.";
						$email_check_passed = false;
					}
				}
			}
		}
	}
	
	if ($error != '') {
		$message = "alert('$error');";
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
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"3154\",\"4thofjulycooking\",\"\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
				
			// insert into joinEmailActive
			$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"3154\",\"4thofjulycooking\",\"\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
			
			
			// get new listid from old listid
			$new_listid = LookupNewListIdByOldListId($listid);
						
			// insert into campaigner
			$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"3154\",\"4thofjulycooking\",\"\",'sub','N')";
			$campaigner_result = mysql_query($campaigner);
			echo mysql_error();

			
			$build_list_id .= $listid.',';
		}
		
		if ($build_list_id != '') {
			$build_list_id = substr($build_list_id,0,strlen($build_list_id)-1);
			// call to function to send new subscriber to Arcamax.
			$send_to_arcamax = Arcamax($email,$build_list_id,'3154',$user_ip,'sub'); // sub or unsub
			//echo "<!-- $send_to_arcamax -->\n\n\n";
			
			// record arcamax server response log
			$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
						VALUES (NOW(),\"$email\",\"$build_list_id\",\"3154\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();
		}
		
		$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=s&f=4thofjulycooking' width='0' height='0' border='0' />";
	}
} else {
	$attempt = false;
	$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=d&f=4thofjulycooking' width='0' height='0' border='0' />";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8 />
<title>Sign Up Form</title>
<style>
	body { margin: 0; padding: 0; border:0;font-size:90%;font-family: verdana,arial;font-size: .80em; }

   #main {
  position: relative;  
  <?php if ($signup_success == false) { ?>
  background: #fff url('http://www.4thofjulycooking.com/images/page_bg_1.gif') no-repeat; 
  <?php } else { ?>
  background: #fff url('http://www.4thofjulycooking.com/images/page_bg_2.gif') no-repeat; 
  <?php } ?>
  width: 299px;  height: 177px;
    }
	#email {
	position: absolute;	border: 1px solid #fff;	top: 113px;	left: 15px;	width: 140px;	height: 11px;
	color: #666;font-weight: bold;	font-family: verdana,arial;	font-style:italic;	font-size: .70em;
	}
	#submit3 {
	position: absolute;	top: 110px;	left: 170px;	width: 87px;	height: 20px;	width: 70px;cursor: pointer;
	}
</style>
<script language="JavaScript">
function check_fields() {
	document.form1.email.style.backgroundColor="";
	var str = '';
	var response = '';

	if (document.form1.email.value == '') {
		str += "Please enter your email address.";
		document.form1.email.style.backgroundColor="yellow";
	}
	
	if (str == '') {
		return true;
	} else {
		alert (str);
		return false;
	}
}
function submitform() {
  document.form1.submit();
}
<?php echo $message; ?>
</script>
</head>
<body>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return check_fields();">
<input name="process" value="Y" type="hidden">
	<div id="main">
		<?php if ($signup_success == false) { ?>
			<div id="emailBox"><input type="text" name="email" id="email" value="enter your email" onFocus="this.value=''" /></div>
			<a href="javascript: submitform()" id="submit3"></a>
		<?php } else { ?>
			<div id="message" style="padding:10px;width: 230px;"><br>
				<b>Thank you for signing up for the Recipe4Living newsletter. 
				Please take a moment to add <br>R4L@recipe4living-recipes.com to your address book so you don't miss an issue!</b>
			</div>
		<?php } ?>
	</div>
</form>
<?php echo $pixel; ?>
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
