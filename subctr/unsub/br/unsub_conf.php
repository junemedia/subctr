<?php
include_once("../../config.php");
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
	window.top.location = "http://www.betterrecipes.com/";
}
setTimeout("redirectToHomePage();",5000);
</script>
</head>
<body>

<table width="450" align="center" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
	<tr>
		<td>
			<font color="#000" size="4" style="font-family: arial;">
				Thank you for your feedback! We're sorry to see you go and hope you'll come visit us again in the future.
			</font>
		</td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td>
			<font color="#000" size="4" style="font-family: arial;">
				Have a good day!
			</font>
		</td>
	</tr>
</table>
</body>
</html>
