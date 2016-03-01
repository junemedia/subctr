<?php

echo "Invalid unsubscribe link: <a href='http://sf.popularliving.com/subctr/index.php'>Click here to go to Subscription Center to unsubscribe</a>";
exit;

include_once("../config.php");

$message = '';

$title_583 = 'The Feed by Better Recipes';
$listing_conf = '';

if ($_POST['submit'] == 'Unsubscribe') {
	$email = trim($_POST['email']);
	
	if (ctype_alnum(trim($_POST['subsource']))) {
		$subsource = trim($_POST['subsource']);
	}

	$aListId = $_POST['aListId'];
	if (strlen($_POST['aListId']) == 0) {
		$aListId = array();
		$message .= "* Please select the newsletters.<br>";
	}
	
	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
		$message .= "* Please enter your valid email address.<br>";
	}
	
	// Check DNS records corresponding to a given domain
	// Get MX records corresponding to a given domain.
	list($prefix, $domain) = split("@",$email);
	if (!getmxrr($domain, $mxhosts)) {
		$message .= "* The email address you provided is not valid. Please try again.<br>";
	}
	
	if ($message == '') {
		foreach ($aListId as $listid) {
			$echo = "title_".$listid;
			$title_val = $$echo;
			$listing_conf .= "$title_val<br>";
			
			$check_query = "SELECT * FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\" LIMIT 1";
			$check_query_result = mysql_query($check_query);
			echo mysql_error();
			if (mysql_num_rows($check_query_result) > 0) {
				// since this user is in our system, remove them and send unsub to arcamax
				// insert into joinEmailUnsub
				$insert_query = "INSERT INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source,subsource,errorCode)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"3507\",\"SFUnsubLink\",\"$subsource\",\"per request\")";
				$insert_query_result = mysql_query($insert_query);
				echo mysql_error();
				
				// delete from joinEmailActive
				$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\" LIMIT 1";
				$delete_query_result = mysql_query($delete_query);
				echo mysql_error();
				
				// call to function to send unsub to Arcamax
				$send_to_arcamax = Arcamax($email,$listid,'3507',$user_ip,'unsub'); // sub or unsub
				
				if ($user_ip == '216.180.167.121') { echo '<!--'.$send_to_arcamax.'<br><br> -->'; }
				
				// record arcamax server response log
				$insert_log = "INSERT INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
							VALUES (NOW(),\"$email\",\"$listid\",\"3507\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
				$insert_log_result = mysql_query($insert_log);
				echo mysql_error();
			} else {
				// call to function to send unsub to Arcamax
				// send unsub request to arcamax even if this user is not in our system.  this is just to make sure user gets unsubscribed no matter what
				$send_to_arcamax = Arcamax($email,$listid,'3507',$user_ip,'unsub'); // sub or unsub
			}
		}

		echo "<table width='500px' align='center'><tr><td><font style='font-family:arial;font-style:normal;font-size:12px;font-weight:normal;text-decoration:none;'>
				<br><br><br><br><br>
				Your e-mail address (<b>$email</b>) has now been unsubscribed from the following lists:<br><br>
				<b>$listing_conf</b>
				<br>
				We're sad to see you go! If you change your mind, you can manage your subscriptions and 
				re-subscribe <a href='http://brstage.resolute.com/'>here</a>.
				</font></td></tr></table>";
		setcookie("email", $email, time()+2592000);
		exit;
	}
} else {
	$email = trim($_GET['email']);
	if (ctype_alnum(trim($_GET['subsource']))) {
		$subsource = trim($_GET['subsource']);
	}
	if (ctype_digit(trim($_GET['id']))) {
		$id = trim($_GET['id']);
	}
}

?>
<html>
<head>
<title>Unsubscribe From BetterRecipes Newsletters</title>
<style>
body {
	font-family:arial;font-style:normal;font-size:12px;font-weight:normal;text-decoration:none;
}
</style>
<script language="JavaScript">
function check_fields() {
	document.form1.email.style.backgroundColor="";
	
	var str = '';
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	if(!document.form1.email.value.match(reg)) {
		str += "* Please enter your valid email address.\n";
		document.form1.email.style.backgroundColor="yellow";
	}
	
	if (!(document.getElementById('583').checked)) {
		str += "* Please select the newsletters.\n";
	}

	if (str == '') {
		return true;
	} else {
		alert (str);
		return false;
	}
}


function CheckAll () {
	document.getElementById('504').checked = true;
	document.getElementById('505').checked = true;
}
function UnCheckAll () {
	document.getElementById('504').checked = false;
	document.getElementById('505').checked = false;
}
</script>
</head>
<body>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']?>" onSubmit="return check_fields();">
	<input type="hidden" value="<?php echo $subsource; ?>" name="subsource">
	<table width="500px" align="center" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
		<tr>
			<td colspan="2" align="center">
			<font color="#F99D1C" size="4" style="font-family: arial;">Unsubscribe From Better Recipes Newsletters</font>
			</td>
		</tr>
	</table>
	<center><h2><?php echo $message; ?></h2></center>
	
	<table width="500px" align="center" id="unsub_form" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
		<tr>
			<td colspan="2"><b>Please enter your email address here:</b><br><br>
			<input name='email' type="text" size="40" maxlength="100" value="<?php echo $email; ?>">
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">Select the newsletters from which you wish to unsubscribe. Take a moment to make sure you are choosing the correct newsletters then <b>click the Unsubscribe button</b> below.</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td><input type="checkbox" id='504' name="aListId[]" value="504" <?php if ($id == '504') { echo ' checked '; } ?>> <b><?php echo $title_504; ?></b></td>
		</tr>
		<tr>
			<td><input type="checkbox" id='505' name="aListId[]" value="505" <?php if ($id == '505') { echo ' checked '; } ?>> <b><?php echo $title_505; ?></b></td>
		</tr>
		
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2"><a href="#" onClick="CheckAll();"><span style="color:#F99D1C;">Check All</span></a>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="#" onClick="UnCheckAll();"><span style="color:#F99D1C;">Uncheck All</span></a>
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td align="center">
			<input type="submit" name="submit" value="Unsubscribe">
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
