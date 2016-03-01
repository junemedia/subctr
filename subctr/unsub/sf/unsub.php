<?php

include_once("../../config.php");

$user_ip = trim($_SERVER['REMOTE_ADDR']);

$pixel = "";
$message = "";
if ($_POST['submit'] == 'Submit') {
	$email = addslashes(trim($_REQUEST['email']));
	$jobid = addslashes(trim($_REQUEST['jobid']));
	if (!ctype_digit($jobid)) { $jobid = 0; }
	$listid = addslashes(trim($_REQUEST['listid']));
	
	if ($_REQUEST['action_stay'] == 'Y') {
		header("Location:http://sf.popularliving.com/subctr/unsub/sf/stay.php?listid=$listid&jobid=$jobid&email=$email");
		exit;
	}
	
	if ($_REQUEST['action_unsub'] == 'Y') {
		// PROCESS UNSUB NOW...
		
		$comments = addslashes(trim($_REQUEST['comments']));
		$q1 = addslashes(trim($_REQUEST['q1']));
		$a1 = addslashes(trim($_REQUEST['a1']));
		$other = addslashes(trim($_REQUEST['other']));
		
		if ($a1 == 'OTHER' && $other == '') {
			$message .= "* Please enter the reason why you are unsubscribing.<br>";
		}
		
		if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
			$message .= "* Please enter your valid email address.<br>";
		}
		
		list($prefix, $domain) = split("@",$email);
		if (!getmxrr($domain, $mxhosts)) {
			$message .= "* The email address you provided is not valid. Please try again.<br>";
		}
		
		if ($message == '') {
			$check_query = "SELECT * FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\" LIMIT 1";
			$check_query_result = mysql_query($check_query);
			echo mysql_error();
			if (mysql_num_rows($check_query_result) > 0) {
				$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\" LIMIT 1";
				$delete_query_result = mysql_query($delete_query);
				echo mysql_error();
				
				// get new listid from old listid
				$new_listid = LookupNewListIdByOldListId($listid);
							
				// insert into campaigner
				$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
								VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"3507\",\"SFUnsubLink\",\"\",'unsub','N')";
				$campaigner_result = mysql_query($campaigner);
				echo mysql_error();
				
				$insert_query = "INSERT IGNORE INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source,subsource,errorCode)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"3507\",\"SFUnsubLink\",\"$jobid\",\"per request\")";
				$insert_query_result = mysql_query($insert_query);
				echo mysql_error();
				
				$unsub_id = mysql_insert_id();
				
				if ($a1 == 'OTHER') { $a1 = "OTHER: ".$other; }

				// RECORD SURVEY QUESTIONS
				$insert = "INSERT IGNORE INTO joinEmailUnsubDetails (unsubId,dateTime,email,listid,jobid,comments,q1,a1) VALUES 
						(\"$unsub_id\",NOW(),\"$email\",\"$listid\",\"$jobid\",\"$comments\",\"$q1\",\"$a1\")";
				$insert_query_result = mysql_query($insert);
				echo mysql_error();
			}

			// call to function to send unsub to Arcamax
			$send_to_arcamax = Arcamax($email,$listid,'3507',$user_ip,'unsub'); // sub or unsub
				
			// record arcamax server response log
			$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
						VALUES (NOW(),\"$email\",\"$listid\",\"3507\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();

			header("Location:http://sf.popularliving.com/subctr/unsub/sf/unsub_conf.php?listid=$listid&jobid=$jobid&email=$email");
			exit;
		}
	}
} else {
	//$email = trim($_REQUEST['email']);
	$jobid = trim($_REQUEST['jobid']);
	if (!ctype_digit($jobid)) { $jobid = 0; }
	$listid = trim($_REQUEST['listid']);
	$pixel = "<img src='http://www.savvyfork.com/unsub/".$listid."_Unsub.html' width='0' height='0' border='0'>";
}

switch ($listid) {
	case '583':
		$newsletter_name = 'The Feed by SavvyFork';
		break;
	case '508':
		$newsletter_name = 'IsSavvyforkSOLO';
		break;
	default:
		echo "Invalid unsubscribe link: <a href='http://sf.popularliving.com/subctr/index.php'>Click here to go to Subscription Center to unsubscribe</a>";
		exit;
}

?>
<html>
<head>
<title>Newsletter Unsubscribed</title>
<style>
body {
	font-family: arial;
	font-style: normal;
	font-size: 12px;
	font-weight: normal;
	text-decoration: none;
}
</style>
<script language="JavaScript">
function check_fields() {
	var str = '';
	if (!(document.getElementById('action_stay').checked || document.getElementById('action_unsub').checked)) {
		str += "* Please check the box below to confirm your action.\n";
	}
	
	if (document.getElementById('action_unsub').checked) {
		document.form1.email.style.backgroundColor="";
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		if(!document.form1.email.value.match(reg)) {
			str += "* Please enter your valid email address.\n";
			document.form1.email.style.backgroundColor="yellow";
		}
		
		document.form1.other.style.backgroundColor="";
		if (document.getElementById('a1_4').checked && document.getElementById('other').value == '') {
			str += "* Please enter the reason why you are unsubscribing.\n";
			document.form1.other.style.backgroundColor="yellow";
		}
	}

	if (str == '') {
		return true;
	} else {
		alert (str);
		return false;
	}
}

function UnCheckOther (id) {
	document.getElementById(id).checked = false;
}

function maxLength(el) {    
	if (!('maxLength' in el)) {
		var max = el.attributes.maxLength.value;
		el.onkeypress = function () {
			if (this.value.length >= max) return false;
		};
	}
}
</script>
</head>
<body>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']?>" onsubmit="return check_fields();">
	<input type="hidden" value="<?php echo $jobid; ?>" name="jobid">
	<input type="hidden" value="<?php echo $listid; ?>" name="listid">
	<table width="500" align="center" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
		<tr>
			<td>
			<font color="#238E38" size="4" style="font-family: arial;"><i><b>Wait - don't go!</b></i>  Are you sure you want to be unsubscribed from <i><b><?php echo $newsletter_name; ?></b></i>?</font>
			</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>Please check the box below to confirm your action.</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>
				<input type="checkbox" name="action_stay" id="action_stay" value="Y" onclick="UnCheckOther('action_unsub');">No, please keep me on the list!
				<br><br>
				<input type="checkbox" name="action_unsub" id="action_unsub" value="Y" onclick="UnCheckOther('action_stay');" checked>Yes, please unsubscribe me from <i><b><?php echo $newsletter_name; ?></b></i>.
			</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		
		<?php if ($message != '') { ?>
			<tr><td style="color:red;"><?php echo $message; ?></td></tr>
			<tr><td>&nbsp;</td></tr>
		<?php } ?>
		
		<tr><td>Please confirm your email address:</td></tr>
		<tr><td><input type="text" name="email" id="email" value="<?php echo $email; ?>" size="50" maxlength="200"></td></tr>
		<tr><td>&nbsp;</td></tr>
		
		
		<tr><td>What is the reason you are unsubscribing today?</td></tr>
		<input type="hidden" value="What is the reason you are unsubscribing today?" name="q1">
		<tr>
			<td>
				<input type="radio" name="a1" id="a1_1" value="I get too many emails from you." <?php if ($a1 == 'I get too many emails from you.') { echo ' checked '; } ?> onclick="javascript:document.getElementById('other').disabled=true;">I get too many emails from you.
				<br><br>
				<input type="radio" name="a1" id="a1_2" value="I am not interested in the content of these emails." <?php if ($a1 == 'I am not interested in the content of these emails.') { echo ' checked '; } ?> onclick="javascript:document.getElementById('other').disabled=true;">I am not interested in the content of these emails.
				<br><br>
				<input type="radio" name="a1" id="a1_3" value="I dont remember signing up for these emails." <?php if ($a1 == "I dont remember signing up for these emails.") { echo ' checked '; } ?> onclick="javascript:document.getElementById('other').disabled=true;">I don't remember signing up for these emails.
				<br><br>
				<input type="radio" name="a1" id="a1_4" value="OTHER" <?php if ($a1 == 'OTHER') { echo ' checked '; } ?> onclick="javascript:document.getElementById('other').disabled=false;">Other: 
				<input type="text" maxlength="100" size="40" name="other" id="other" value="<?php echo $other; ?>">
			</td>
		</tr>
		
		
		<tr><td>&nbsp;</td></tr>
		<tr><td>Comments:</td></tr>
		<tr><td><textarea name="comments" id="comments" cols="37" rows="5" maxlength="200"><?php echo $comments; ?></textarea></td></tr>
		
		
		
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td align="center">
			<input type="submit" name="submit" value="Submit">
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
<?php echo $pixel; ?>
</body>
</html>
