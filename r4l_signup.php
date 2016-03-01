<?php

/*
Method: HTTPS GET or POST (HTML Form)
Posting URL: http://r4l.popularliving.com/r4l_signup.php
Required Fields: email=&ipaddr=&keycode=if3lkj6i8hjnax&sublists=&subcampid=
Example: http://r4l.popularliving.com/r4l_signup.php?email=samirp@silvercarrot.com&ipaddr=216.180.167.121&keycode=if3lkj6i8hjnax&sublists=&subcampid=
*/

include_once("subctr/config.php");

$post_string = "";
while (list($key,$val) = each($_POST)) { $$key = addslashes(strtolower($val)); $post_string .= $key."=".addslashes(strtolower($val))."&"; }
while (list($key,$val) = each($_GET)) { $$key = addslashes(strtolower($val)); $post_string .= $key."=".addslashes(strtolower($val))."&"; }
$post_string = substr($post_string,0,strlen($post_string)-1);

if ($keycode != 'if3lkj6i8hjnax') { echo 'Error: keycode';exit; }
if ($email == '') { echo 'Error: email';exit; }
if ($sublists == '') { echo 'Error: sublists';exit; }
if ($subcampid == '') { echo 'Error: subcampid';exit; }
if ($ipaddr == '') { echo 'Error: ipaddr';exit; }
if (!ctype_digit($subcampid)) { echo 'Error: subcampid';exit; }
if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) { echo "Error: email";exit; }

list($prefix, $domain) = split("@",$email);
$parts = explode(",",$sublists);

if (!getmxrr($domain, $mxhosts)) { echo "Error: No MX Record";exit; }
if (count($parts) > 0) { foreach($parts as $list_parts) { if (!ctype_digit($list_parts)) { echo 'Error: sublists';exit; } } }

$check_banned_domain_result = mysql_query("SELECT * FROM bannedDomains WHERE domain=\"$domain\" LIMIT 1");
if (mysql_num_rows($check_banned_domain_result) == 1) { echo "Error: banned domain";exit; }

$check_banned_email_result = mysql_query("SELECT * FROM bannedEmails WHERE email=\"$email\" LIMIT 1");
if (mysql_num_rows($check_banned_email_result) == 1) { echo "Error: banned email";exit; }

if (BullseyeBriteVerifyCheck($email) == false) { echo "Error: email - BV";exit; }
if (LookupImpressionWise($email) == false) { echo 'Error: email - IW';exit; }

//We do not need to send to Arcamax any more. shut it down!
//$server_response = Arcamax($email,$sublists,$subcampid,$ipaddr,'sub');
//if (strstr($server_response, 'MASTERUNSUB')) { echo $server_response;exit; }

foreach($parts as $list_parts) {
	$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"R4LRightColumn\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"R4LRightColumn\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// get new listid from old listid
	$new_listid = LookupNewListIdByOldListId($list_parts);
				
	// insert into campaigner
	$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$new_listid\",\"$subcampid\",\"R4LRightColumn\",\"\",'sub','N')";
	$campaigner_result = mysql_query($campaigner);
	echo mysql_error();
}

$insert_api_lead = "INSERT IGNORE INTO api (dateTimeAdded,email,listid,subcampid,ipaddr,subsource) VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",'R4LRightColumn')";
$insert_api_lead_result = mysql_query($insert_api_lead);
echo mysql_error();

//$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response) VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",\"sub\",\"$post_string | $server_response\")";
//$insert_log_result = mysql_query($insert_log);
//echo mysql_error();

//echo $server_response;exit;

?>