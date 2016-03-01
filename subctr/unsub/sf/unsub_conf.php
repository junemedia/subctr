<?php

include_once("../../config.php");

$pixel = '';

if (ctype_digit(trim($_REQUEST['listid']))) {
	$pixel = "<img src='http://www.savvyfork.com/unsub/".trim($_REQUEST['listid'])."_UnsubConf.html' width='0' height='0' border='0'>";
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
<script>
function redirectToHomePage () {
	window.top.location = "http://www.savvyfork.com/";
}
setTimeout("redirectToHomePage();",5000);
</script>
</head>
<body>

<table width="500" align="center" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
	<tr>
		<td>
			<font color="#238E38" size="4" style="font-family: arial;">
				Thank you for your feedback! We're sorry to see you go and hope you'll come visit us again in the future.
			</font>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>
			<font color="#238E38" size="4" style="font-family: arial;">
				Have a good day!
			</font>
		</td>
	</tr>
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
