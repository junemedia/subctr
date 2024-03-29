<?php

include_once("../../config.php");

$pixel = "";
if (isset($_POST['submit']) && $_POST['submit'] == 'Submit') {
	$email = trim($_REQUEST['email']);
        //$email = "";
	$jobid = trim($_REQUEST['jobid']);
	if (!ctype_digit($jobid)) { $jobid = 0; }
	$listid = trim($_REQUEST['listid']);
	
	if ($_REQUEST['action_stay'] == 'Y') {
		header("Location:/subctr/unsub/r4l/stay.php?listid=$listid&jobid=$jobid&email=$email");
		exit;
	}
	if ($_REQUEST['action_unsub'] == 'Y') {
		header("Location:/subctr/unsub/r4l/unsub.php?listid=$listid&jobid=$jobid&email=$email");
		exit;
	}
} else {
	$email = trim($_REQUEST['email']);
	$jobid = trim($_REQUEST['jobid']);
	if (!ctype_digit($jobid)) { $jobid = 0; }
	$listid = trim($_REQUEST['listid']);
	$pixel = "<img src='http://www.recipe4living.com/unsub/".$listid."_Form.html' width='0' height='0' border='0'>";
}

switch ($listid) {
	case '393':
		$newsletter_name = 'Daily Recipes';
		break;
	case '396':
		$newsletter_name = 'Recipe4Living Special Offers';
		break;
	case '395':
		$newsletter_name = 'Budget Cooking';
		break;
	case '394':
		$newsletter_name = 'Quick & Easy Recipes';
		break;
	case '511':
		$newsletter_name = 'Crockpot Creations';
		break;
	case '539':
		$newsletter_name = "Casserole Cookin'";
		break;
	case '554':
		$newsletter_name = 'Copycat Classics';
		break;
	case '574':
		$newsletter_name = 'Diabetic-Friendly Dishes';
		break;
	case '502':
		$newsletter_name = 'Recipe4Living Daily Recipes';
		break;
	case '507':
		$newsletter_name = 'Recipe4Living Giveaways';
		break;
	default:
		echo "Invalid unsubscribe link: <a href='http://r4l.popularliving.com/subctr/index.php'>Click here to go to Subscription Center to unsubscribe</a>";
		exit;
}

?>
<html>
<head>
<title>Unsubscribe From Recipe4Living Newsletters</title>
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
</script>

</head>
<body>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']?>" onsubmit="return check_fields();">
	<input type="hidden" value="<?php echo $jobid; ?>" name="jobid">
	<input type="hidden" value="<?php echo $listid; ?>" name="listid">
	<input type="hidden" value="<?php echo $email; ?>" name="email">
	<table width="500" align="center" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
		<tr>
			<td>
			<font color="#ED1D24" size="4" style="font-family: arial;"><i><b>Wait - don't go!</b></i>  Are you sure you want to be unsubscribed from <i><b><?php echo $newsletter_name; ?></b></i>?</font>
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
				<input type="checkbox" name="action_unsub" id="action_unsub" value="Y" onclick="UnCheckOther('action_stay');">Yes, please unsubscribe me from <i><b><?php echo $newsletter_name; ?></b></i>.
			</td>
		</tr>
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
