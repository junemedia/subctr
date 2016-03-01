<?php

/*
Method: GET or POST
Posting URL: http://r4l.popularliving.com/check_record.php
Fields: email=&listid=&type=&ionumber=&partner=

email	=	user's email address	[required field]

type	=	emailonly	OR	emailpluslistid		[required field].  This value can be different by co-reg offer.
	if type = emailonly, then listid IS OPTIONAL because system will only check for email duplication
	if type = emailpluslistid, then listid IS REQUIRED because system will check for email+listid for duplication

listid	=	this is 3 digit number separate by comma.  For ex. 393,396.  This value can be different by co-reg offer.

ionumber = BE coreg IO# - this is OPTIONAL. If you pass this information, it will help me in future for reporting purpose.

partner = BE API PartnerID - this is OPTIONAL. If you pass this information, it will help me in future for reporting purpose.

*/

include_once("subctr/config.php");

while (list($key,$val) = each($_POST)) { $$key = strtolower(trim(addslashes($val))); }
while (list($key,$val) = each($_GET)) { $$key = strtolower(trim(addslashes($val))); }

$response = '';

if ($email == '') {
	$response = 'Error: bad email';
} else {
	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
		$response = 'Error: bad email';
	}
}

if (!in_array($type, array('emailonly','emailpluslistid'))) {
	$response = 'Error: type must be emailonly OR emailpluslistid';
}


if ($response == '' && $type == 'emailonly') {
	$listid = '';	// clear listid since we are only checking for unique emails, not unique emails+list
	
	$check = "SELECT * FROM joinEmailActive WHERE email=\"$email\" LIMIT 1";
	$check_result = mysql_query($check);
	if (mysql_num_rows($check_result) == 1) {
		$response = "FALSE";
	} else {
		$response = "TRUE";
	}
}


if ($response == '' && $type == 'emailpluslistid') {
	// since we are checking for email+listid, we need to make sure listid is valid
	$parts = explode(",",$listid);
	$listid_in = "";
	if (count($parts) > 0) {
		foreach($parts as $list_parts) {
			if (!ctype_digit($list_parts)) { $response = 'Error: bad listid'; }
			$listid_in .= "'$list_parts',";
		}
		$listid_in = substr($listid_in,0,strlen($listid_in)-1);
	} else {
		$response = "Error: bad listid";
	}
	
	if ($response == '') {
		$check = "SELECT * FROM joinEmailActive WHERE email=\"$email\" AND listid IN ($listid_in)";
		$check_result = mysql_query($check);
		if (mysql_num_rows($check_result) == count($parts)) {
			$response = "FALSE";
		} else {
			$response = "TRUE";
		}
	}
}


$insert_log = "INSERT IGNORE INTO duplication_check_log (email,listid,checkType,dateTime,response,ionumber,partner) 
		VALUES (\"$email\",\"$listid\",\"$type\",NOW(),\"$response\",\"$ionumber\",\"$partner\")";
$insert_log_resule = mysql_query($insert_log);
echo mysql_error();

echo $response;
exit;

?>
