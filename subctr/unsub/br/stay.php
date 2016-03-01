<?php

include_once("../../config.php");


$listid = '';
$jobid = '';
$email = '';
$pixel = '';
$ipaddr = trim($_SERVER['REMOTE_ADDR']);

if (ctype_digit(trim($_REQUEST['listid']))) {
	$listid = trim($_REQUEST['listid']);
	$pixel = "<img src='http://www.fitandfabliving.com/unsub/".$listid."_Stay.html' width='0' height='0' border='0'>";
}

if (ctype_digit(trim($_REQUEST['jobid']))) {
	$jobid = trim($_REQUEST['jobid']);
}


if (eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", trim($_REQUEST['email']))) {
	$email = trim($_REQUEST['email']);
}


$insert = "INSERT IGNORE INTO staying (dateTime,listid,jobid,email,ipaddr) VALUES (NOW(),\"$listid\",\"$jobid\",\"$email\",\"$ipaddr\")";
$result = mysql_query($insert);
echo mysql_error();

?>
<html>
<head>
<title>Newsletter Stay</title>
<style>
body {
	font-family: arial;
	font-style: normal;
	font-size: 12px;
	font-weight: normal;
	text-decoration: none;
}
</style>
<script>
function redirectToHomePage () {
	window.top.location = "http://www.betterrecipes.com/";
}
setTimeout("redirectToHomePage();",5000);
</script>
</head>
<body>

<table width="450" align="center" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
	<tr>
		<td>
			<font color="#F07AB0" size="4" style="font-family: arial;">
				Great! Thanks for staying with us.
			</font>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
</table>
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
