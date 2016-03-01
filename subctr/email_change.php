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

if ($_POST['submit'] == 'Update Information') {
	// add slashes for security purpose - to avoid MySQL injection attack
	$old_email = addslashes($_POST['old_email']);
	$new_email = addslashes($_POST['new_email']);
	
	$error = '';
	if ($new_email != '') {
		$email_check_passed = false;

		if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $new_email)) {
			$error = "The email address you provided is not valid. Please try again.";
			$email_check_passed = false;
		} else {
			// Check DNS records corresponding to a given domain
			// Get MX records corresponding to a given domain.
			list($prefix, $domain) = split("@",$new_email);
			if (!getmxrr($domain, $mxhosts)) {
				$error = "The email address you provided is not valid. Please try again.";
				$email_check_passed = false;
			} else {
				if ($error == '') {
					$check_banned_domain = "SELECT * FROM bannedDomains WHERE domain=\"$domain\" LIMIT 1";
					$check_banned_domain_result = mysql_query($check_banned_domain);
					if (mysql_num_rows($check_banned_domain_result) == 1) {
						$error = 'The email address you provided is not valid. Please try again.';
						$email_check_passed = false;
					}
				}
				
				if ($error == '') {
					$check_banned_email = "SELECT * FROM bannedEmails WHERE email=\"$new_email\" LIMIT 1";
					$check_banned_email_result = mysql_query($check_banned_email);
					if (mysql_num_rows($check_banned_email_result) == 1) {
						$error = 'The email address you provided is not valid. Please try again.';
						$email_check_passed = false;
					}
				}
				
				if ($error == '') {
					if (LookupImpressionWise($new_email) == false) {
						$error = "Your e-mail address is invalid. Please try again. If you continue to have an issue, please contact us <a href='http://www.recipe4living.com/contact/' target='_blank'>here</a>.";
						$email_check_passed = false;
					}
				}

				if ($error == '') {
					if (BullseyeBriteVerifyCheck($new_email) == true) {
						// BV passed
						$_SESSION["email"] = $new_email;
						$email_check_passed = true;
					} else {
						// BV failed
						$error = 'The email address you provided is not valid. Please try again.';
						$email_check_passed = false;
					}
				}
			}
		}
	} else {
		$error = "New email address is required for email change.";
		$email_check_passed = false;
	}
	
	if ($email_check_passed == true) {
		$source = trim($_SESSION["source"]);
		$subsource = trim($_SESSION["subsource"]);
		
		$insert = "INSERT INTO emailChange (dateTimeAdded,old_email,new_email,ip)
					VALUES (NOW(),\"$old_email\",\"$new_email\",\"$user_ip\")";
		$result = mysql_query($insert);
		echo mysql_error();
		
		// copy user data to new email address
		$query = "SELECT * FROM userData WHERE email = \"$old_email\" LIMIT 1";
		$result = mysql_query($query);
		echo mysql_error();
		if (mysql_num_rows($result) == 1) {
			$insert_user = "INSERT IGNORE INTO userData (email,fname,lname,zip,gender,day,month,year)
						SELECT '$new_email',fname,lname,zip,gender,day,month,year FROM userData
						WHERE email = \"$old_email\" LIMIT 1";
			//echo $insert_user;
			$result = mysql_query($insert_user);
			echo mysql_error();
		}
		
		$check_query = "SELECT * FROM joinEmailActive WHERE email = \"$old_email\"";
		$check_query_result = mysql_query($check_query);
		echo mysql_error();
		$build_list_id = '';
		while ($active_row = mysql_fetch_object($check_query_result)) {
			$listid = $active_row->listid;
			$subcampid = $active_row->subcampid;
			
			$build_list_id .= $listid.',';
	
			// ******* NOW PROCESS SUB NEW EMAIL *******
			// insert into joinEmailSub
			$insert_query = "INSERT INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
						VALUES (NOW(),\"$new_email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"$source\",\"$subsource\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
						
			// insert into joinEmailActive
			$insert_query = "INSERT INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
						VALUES (NOW(),\"$new_email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"$source\",\"$subsource\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
			
			
			// get new listid from old listid
			$new_listid = LookupNewListIdByOldListId($listid);
						
			// insert into campaigner
			$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
							VALUES (NOW(),\"$new_email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"$subcampid\",\"$source\",\"$subsource\",'sub','N')";
			$campaigner_result = mysql_query($campaigner);
			echo mysql_error();
						
			
			// ******* NOW PROCESS UNSUB OLD EMAIL *******
			// insert into joinEmailUnsub
			$insert_query = "INSERT INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source,subsource,errorCode)
					VALUES (NOW(),\"$old_email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"$source\",\"$subsource\",\"email change\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
						
			// delete from joinEmailActive
			$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$old_email\" AND listid=\"$listid\"";
			$delete_query_result = mysql_query($delete_query);
			echo mysql_error();
			
			
			// get new listid from old listid
			$new_listid = LookupNewListIdByOldListId($listid);
						
			// insert into campaigner
			$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
							VALUES (NOW(),\"$old_email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"$subcampid\",\"$source\",\"$subsource\",'unsub','N')";
			$campaigner_result = mysql_query($campaigner);
			echo mysql_error();
		}
		
		if ($build_list_id != '') {
			$build_list_id = substr($build_list_id,0,strlen($build_list_id)-1);
			echo "<!-- $build_list_id -->\n\n\n";
			
			// ******* NOW PROCESS SUB NEW EMAIL *******
			// call to function to send new subscriber to Arcamax.
			$send_to_arcamax = Arcamax($new_email,$build_list_id,$subcampid,$user_ip,'sub'); // sub or unsub
			echo "<!-- $send_to_arcamax -->\n\n\n";
			
			// record arcamax server response log
			$insert_log = "INSERT INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
					VALUES (NOW(),\"$new_email\",\"$build_list_id\",\"$subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();
			
			
			// ******* NOW PROCESS UNSUB OLD EMAIL *******
			// call to function to send unsub to Arcamax
			$send_to_arcamax = Arcamax($old_email,$build_list_id,$subcampid,$user_ip,'unsub'); // sub or unsub
			
			// record arcamax server response log
			$insert_log = "INSERT INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
					VALUES (NOW(),\"$old_email\",\"$build_list_id\",\"$subcampid\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();
		}
		
		// add new email to session.
		$_SESSION["email"] = $new_email;
		
		echo "<div style='font-family: verdana;font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;'>
				Your email change request has been processed.
				<a href='index.php?PHPSESSID=".session_id()."'> Click here to sign in with your new email address. </a></div>";
		exit;
	} else {
		echo "<script>alert('$error');</script>";
	}
} else {
	// exit the page if email is not set.
	if (trim($_SESSION["email"]) == '') {
		//echo " - Error at line ".__LINE__.". PHPSESSID: ".session_id();
		echo "<div style='font-family: verdana;font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;'>
				Sorry, something went wrong.
				<a href='"."http://".trim($_SERVER['HTTP_HOST'])."/subctr/index.php?PHPSESSID=".session_id()."'>Click here and try again</a>.
					</div>";
		exit;
	}
	
	$old_email = $_SESSION["email"];
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

?>
<html>
<head>
<meta name="keywords" content="recipe newsletters, free recipe newsletter, recipe newsletter, daily recipe newsletter, crockpot recipe newsletter, slow cooker recipe newsletter, free slow cooker recipes, free crockpot recipes, free crockpot recipe newsletter budget cooking recipes, budget cooking newsletter,party recipes and tips, party recipe newsletter, quick and easy recipe newsletter, quick and easy recipes" />
<meta name="description" content="Looking for recipes and cooking tips? You have come to the right place!" />
<title>Email Address Change</title>
<SCRIPT LANGUAGE=JavaScript SRC="js/ajax.js" TYPE=text/javascript></script>
<script language="JavaScript">
function check_fields() {
	document.form1.new_email.style.backgroundColor="";
	document.form1.conf_new_email.style.backgroundColor="";
	var str = '';
	var response = '';
	
	if (document.form1.new_email.value == '') {
		str += "Please enter a new email address.";
		document.form1.new_email.style.backgroundColor="yellow";
	} else {
		if (document.form1.new_email.value != document.form1.conf_new_email.value) {
			str += "The email addresses you provided do not match. Please go back and try again.";
			document.form1.new_email.style.backgroundColor="yellow";
			document.form1.conf_new_email.style.backgroundColor="yellow";
		}
	}
	
	if (document.form1.old_email.value == document.form1.new_email.value) {
		str += "Please make sure you have entered the correct new email address.";
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
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return check_fields();">
<input type="hidden" name="PHPSESSID" id="PHPSESSID" value="<?php echo session_id(); ?>">
<table border="0" align="center" width="575px" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
	<tr>
		<td colspan="2">
			<a href="check.php?PHPSESSID=<?php echo session_id(); ?>"><b>Go Back to Manage My Newsletters</b></a>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2">
			<?php
				if ($host == 'r4l') { ?>
					<font color="#0F22B0" size="4" style="font-family: arial;">Email Address Change</font>
				<?php }
				if ($host == 'fitfab') { ?>
					<font color="#EC519D" size="4" style="font-family: arial;">Email Address Change</font>
				<?php }
				if ($host == 'wim') { ?>
					<font color="#2789BD" size="4" style="font-family: arial;">Email Address Change</font>
				<?php }
				if ($host == 'sf') { ?>
					<font color="#F99D1C" size="4" style="font-family: arial;">Email Address Change</font>
				<?php }
			?>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2">
			To update your email address, please enter your new email address and press the submit button.
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td>Old Email:</td>
		<td><?php echo $old_email; ?><input type="hidden" name="old_email" value="<?php echo $old_email; ?>"></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td>New Email:</td>
		<td><input type="text" name="new_email" value="<?php echo $new_email; ?>" maxlength="100"></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td>Confirm New Email:</td>
		<td><input type="text" name="conf_new_email" value="<?php echo $conf_new_email; ?>" maxlength="100"></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="Update Information">
			<div id="loader"><img src="/subctr/images/<?php echo $loader_img; ?>"></div>
		</td>
	</tr>
</table>
</form>

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
