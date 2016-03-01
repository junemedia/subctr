<?php

/*
Method: GET or POST
Posting URL: http://r4l.popularliving.com/api.php
Required Fields: email=&sublists=&subcampid=&ipaddr=
Optional Fields: fname=&lname=&gender=&addr1=&addr2=&city=&state=&zip=&phone_1=&phone_2=&phone_3=&day=&month=&year=&subsource=

Notes:
	* gender = Male, Female, M, or F
	* subsource = IO #

Example:
http://r4l.popularliving.com/api.php?
email=samirp@silvercarrot.com
&sublists=395,396
&subcampid=2679
&ipaddr=216.180.167.121
&fname=Samir
&lname=Patel
&gender=Male
&addr1=3400 Dundee Rd
&addr2=236
&city=Northbrook
&state=IL
&zip=60062
&phone_1=847
&phone_2=205
&phone_3=9340
&day=15
&month=02
&year=1980
&subsource=19824

*/

include_once("subctr/config.php");

$post_string = "";
$arcamax_post_string = "";

while (list($key,$val) = each($_POST)) {
	$$key = $val;
	$post_string .= $key."=".$val."&";
	
	if ($key == 'email' || $key == 'sublists' || $key == 'subcampid' || $key == 'ipaddr') {
		$arcamax_post_string .= $key."=".$val."&";
	}
}

while (list($key,$val) = each($_GET)) {
	$$key = $val;
	$post_string .= $key."=".$val."&";
	
	if ($key == 'email' || $key == 'sublists' || $key == 'subcampid' || $key == 'ipaddr') {
		$arcamax_post_string .= $key."=".$val."&";
	}
}

$arcamax_post_string = substr($arcamax_post_string,0,strlen($arcamax_post_string)-1);
$post_string = substr($post_string,0,strlen($post_string)-1);

$afid1 = strtolower(trim(addslashes($afid1)));
$afid2 = strtolower(trim(addslashes($afid2)));
$home = strtolower(trim(addslashes($home)));
$io = strtolower(trim(addslashes($io)));
$api = strtolower(trim(addslashes($api)));
$email = strtolower($email);

$temp_post_data = addslashes($post_string);
$insert_post_data = "INSERT IGNORE INTO querystring (dateTimeAdded,postdata) VALUES (NOW(), \"$temp_post_data\")";
$insert_post_data_result = mysql_query($insert_post_data);


// START = VALIDATE REQUIRED FIELDS 
if ($email == '') { echo 'Error: email is required';exit; }
if ($sublists == '') { echo 'Error: sublists is required';exit; }
if ($subcampid == '') { echo 'Error: subcampid is required';exit; }
if ($ipaddr == '') { echo 'Error: ipaddr is required';exit; }

$parts = explode(",",$sublists);
if (count($parts) > 0) {
	foreach($parts as $list_parts) {
		if (!ctype_digit($list_parts)) { echo 'Error: sublists is invalid';exit; }
	}
}

if (!ctype_digit($subcampid)) { echo 'Error: subcampid must be digit';exit; }

if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
	echo "Error: invalid email address.";exit;
}

if (LookupImpressionWise($email) == false) {
	echo 'Error: invalid email address. Code: IW';exit;
}
// END = VALIDATE REQUIRED FIELDS





// We will shutndown Arcamax call here:
/*
// SEND TO ARCAMAX - START 
$server_response = "";
$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
$aUrlArray = explode("//", $sPostingUrl);
$sUrlPart = $aUrlArray[1];
$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
$sHostPart = ereg_replace("\/","",$sHostPart);
$sScriptPath = substr($sUrlPart,strlen($sHostPart));
$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
if ($rSocketConnection) {
	fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
	fputs($rSocketConnection, "Host: $sHostPart\r\n");
	fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
	fputs($rSocketConnection, "Content-length: " . strlen($arcamax_post_string) . "\r\n");
	fputs($rSocketConnection, "User-Agent: MSIE\r\n");
	fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.datapass:jAyRwBU8")."\r\n");
	fputs($rSocketConnection, "Connection: close\r\n\r\n");
	fputs($rSocketConnection, $arcamax_post_string);
	while(!feof($rSocketConnection)) { $server_response .= fgets($rSocketConnection, 1024); }
	fclose($rSocketConnection);
} else {
	$server_response = "$errstr ($errno)<br />\r\n";
}
$server_response = addslashes($server_response);
if (strstr($server_response, 'MASTERUNSUB')) { echo $server_response;exit; }
// SEND TO ARCAMAX - END
*/










// START = NOW PROCESS SUBSCRIBERS
$parts = explode(",",$sublists);
foreach($parts as $list_parts) {
	// insert into joinEmailSub
	$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource,afid1,afid2,home,io,api)
				VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"BE System\",\"$subsource\",\"$afid1\",\"$afid2\",\"$home\",\"$io\",\"$api\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// insert into joinEmailActive
	$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource,afid1,afid2,home,io,api)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"BE System\",\"$subsource\",\"$afid1\",\"$afid2\",\"$home\",\"$io\",\"$api\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// get new listid from old listid
	$new_listid = LookupNewListIdByOldListId($list_parts);
	
	// insert into campaigner
	$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$new_listid\",\"$subcampid\",\"BESystem\",\"$subsource\",'sub','N')";
	$campaigner_result = mysql_query($campaigner);
	echo mysql_error();
}

// Log API lead int API table for reporting purpose
$insert_api_lead = "INSERT IGNORE INTO api (dateTimeAdded,email,listid,subcampid,
	ipaddr,fname,lname,gender,addr1,addr2,city,state,zip,phone_1,phone_2,phone_3,
	day,month,year,subsource) VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",
	\"$ipaddr\",\"$fname\",\"$lname\",\"$gender\",\"$addr1\",\"$addr2\",\"$city\",\"$state\",
	\"$zip\",\"$phone_1\",\"$phone_2\",\"$phone_3\",\"$day\",\"$month\",\"$year\",\"$subsource\")";
$insert_api_lead_result = mysql_query($insert_api_lead);
echo mysql_error();


// record arcamax server response log
$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
			VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",\"sub\",\"$arcamax_post_string | $server_response\")";
$insert_log_result = mysql_query($insert_log);
//echo mysql_error();
// END = NOW PROCESS SUBSCRIBERS


// LET BE SYSTEM KNOW SERVER RESPONSE FROM ARCAMAX
echo $server_response;
exit;

?>
