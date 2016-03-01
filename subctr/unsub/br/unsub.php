<?php

include_once("../../config.php");

/*
if($_SERVER['REMOTE_ADDR'] == '66.54.186.254'){
    ini_set("display_errors","On");
    error_reporting(E_ALL);
}


if($_SERVER["SERVER_ADDR"] == '192.168.51.26'){
    echo 'a';
}

if($_SERVER["SERVER_ADDR"] == '192.168.51.30'){
    echo 'b';
}
*/

$user_ip = trim($_SERVER['REMOTE_ADDR']);
$email = addslashes(trim($_REQUEST['email']));
$jobid = addslashes(trim($_REQUEST['jobid']));
if (!ctype_digit($jobid)) { $jobid = 0; }
$listid = addslashes(trim($_REQUEST['listid']));
$pixel = "";
$message = "";
if ($_POST['submit'] == 'Submit') {

	
	if ($_REQUEST['action_stay'] == 'Y') {
		header("Location:http://br.popularliving.com/subctr/unsub/br/stay.php?listid=$listid&jobid=$jobid&email=$email");
		exit;
	}elseif($_REQUEST['action_unsub'] == 'Y') {
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
				$row = mysql_fetch_array($check_query_result, MYSQL_ASSOC);
				$subcampid = $row['subcampid'];
			
				$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\" LIMIT 1";
				$delete_query_result = mysql_query($delete_query);
				echo mysql_error();
				
				// get new listid from old listid
				$new_listid = LookupNewListIdByOldListId($listid);
							
			}
                        
                        // Process this way only if we didn't have their records locally
                        // Cheating by find the subcampId from joinEmailSub table first.
                        switch ($listid){
                            case 504:
                                $attrs = json_encode(array("IsBetterRecipes Daily"=>"False"));
                                break;
                            case 505:
                                $attrs = json_encode(array("IsBetterRecipes SOLO"=>"False"));
                                break;
                            case 506:
                                $attrs = json_encode(array("IsBetterRecipesSweeps"=>"False"));
                                break;
                            default:
                                $attrs = false;
                                break;

                        }

                        if($attrs){
                            $update507Sql = "INSERT INTO `LeonCampaignPush` (`id`, `email`, `attrs`, `IsProcessed`, `date_add`, `notes`) VALUES "
                                            . "(NULL, '$email', '$attrs', 'N', NOW(),'')";
                            mysql_query($update507Sql);
                        }
                        
                        if(!$subcampid) {$subcampid = "Not Found in JoinEmailActive";}
                        
                        $insert_query = "INSERT IGNORE INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source,subsource,errorCode)
                                                VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"BRUnsubLink\",\"$jobid\",\"per request\")";
                        $insert_query_result = mysql_query($insert_query);
                        echo mysql_error();

                        $unsub_id = mysql_insert_id();

                        if ($a1 == 'OTHER') { $a1 = "OTHER: ".$other; }

                        // RECORD SURVEY QUESTIONS
                        $insert = "INSERT IGNORE INTO joinEmailUnsubDetails (unsubId,dateTime,email,listid,jobid,comments,q1,a1) VALUES 
                                        (\"$unsub_id\",NOW(),\"$email\",\"$listid\",\"$jobid\",\"$comments\",\"$q1\",\"$a1\")";
                        $insert_query_result = mysql_query($insert);
                        echo mysql_error();
                        
			// call to function to send unsub to Arcamax
			$send_to_arcamax = Arcamax($email,$listid,'2744',$user_ip,'unsub'); // sub or unsub
				
			// record arcamax server response log
			$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
						VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();

			header("Location:http://br.popularliving.com/subctr/unsub/br/unsub_conf.php?listid=$listid&jobid=$jobid&email=$email");
			exit;
		}
        }else{
            echo "Oh,Invalid unsubscribe link: <a target='_blank' href='http://br.popularliving.com/subctr/index.php'>Click here to go to Subscription Center to unsubscribe</a>";
        }
}

switch ($listid) {
	case '504':
		$newsletter_name = 'Better Recipes Daily';
		break;
	case '505':
		$newsletter_name = 'Better Recipes SOLO';
		break;
	case '506':
		$newsletter_name = 'Better Recipes Giveaways';
		break;
        case '507':
		$newsletter_name = 'Recipe4Living Giveaways';
		break;
            
	default:
		echo "Invalid unsubscribe link: <a target='_blank' href='http://br.popularliving.com/subctr/index.php'>Click here to go to Subscription Center to unsubscribe</a>";
		exit;
}

?>
<html>
<head>
<title>Unsubscribe From Newsletters</title>
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

function showEmailConfirm()
{
	if (document.getElementById('action_unsub').checked)
	{	
		for (var i = 1; i < 4; i++)
		{
			var rowObj = document.getElementById("cf_"+i);
			showRow(rowObj);
		}
	}
	
	if(document.getElementById('action_stay').checked)
	{
		for (var i = 1; i < 4; i++)
		{
			var rowObj = document.getElementById("cf_"+i);
			hideRow(rowObj);
		}
	}
}

function showRow(rowObj)
{  
	rowObj.style.display = "block";
} 

function hideRow(rowObj)
{
	rowObj.style.display = "none";
}
</script>
</head>
<body>
    <form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']?>" onsubmit="return check_fields();" enctype="multipart/form-data" >
	<input type="hidden" value="<?php echo $jobid; ?>" name="jobid">
	<input type="hidden" value="<?php echo $listid; ?>" name="listid">
	<table width="450" align="center" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
		<tr>
			<td>
			<font size="4" style="font-family: arial;"><i><b>Wait - don't go!</b></i>  Are you sure you want to be unsubscribed from <i><b><?php echo $newsletter_name; ?></b></i>?</font>
			</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>Please check the box below to confirm your action.</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>
				<input type="checkbox" name="action_stay" id="action_stay" value="Y" onclick="UnCheckOther('action_unsub');showEmailConfirm();">No, please keep me on the list!
				<br><br>
				<input type="checkbox" name="action_unsub" id="action_unsub" value="Y" onclick="UnCheckOther('action_stay');showEmailConfirm();">Yes, please unsubscribe me from <i><b><?php echo $newsletter_name; ?></b></i>.
			</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		
		<?php if ($message != '') { ?>
			<tr><td style="color:red;"><?php echo $message; ?></td></tr>
			<tr><td>&nbsp;</td></tr>
		<?php } ?>
		
		<tr id="cf_1" style="display:none;"><td>Please confirm your email address:</td></tr>
		<tr id="cf_2" style="display:none;"><td><input type="text" name="email" id="email" value="<?php echo $email; ?>" size="50" maxlength="200"></td></tr>
		<tr id="cf_3" style="display:none;"><td>&nbsp;</td></tr>
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
