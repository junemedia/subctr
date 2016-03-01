<?php

// includes config.php in this script...
$form = trim($_GET['f']);	//	R4lSiteDhtml

if (ctype_alpha(trim($_GET['a'])) && ctype_alnum($form)) {
	include_once("../config.php");
	$today = date('Y-m-d');
	
	$check = "SELECT * FROM dhtml_stats WHERE dateAdded = '$today' AND form = '$form' LIMIT 1";
	$check_result = mysql_query($check);

	if (mysql_num_rows($check_result) == 0) {
		if (trim($_GET['a']) == 'd') {
			$record = "INSERT IGNORE INTO dhtml_stats (dateAdded, form, display) VALUES ('$today', '$form', '1')";
		}
		if (trim($_GET['a']) == 's') {
			$record = "INSERT IGNORE INTO dhtml_stats (dateAdded, form, signup) VALUES ('$today', '$form', '1')";
		}
	} else {
		if (trim($_GET['a']) == 'd') {
			$record = "UPDATE dhtml_stats SET display = display + 1 WHERE dateAdded = '$today' AND form = '$form' LIMIT 1";
		}
		if (trim($_GET['a']) == 's') {
			$record = "UPDATE dhtml_stats SET signup = signup + 1 WHERE dateAdded = '$today' AND form = '$form' LIMIT 1";
		}
	}
	$record_result = mysql_query($record);
}

?>
