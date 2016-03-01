<?php

/*
Method: GET or POST
Posting URL: http://fitfab.popularliving.com/fitfab_api_giveaway2.php
Required Fields: email=&sublists=&subcampid=&ipaddr=&keycode=kfdj49358gkj359gjk55
Example: http://fitfab.popularliving.com/fitfab_api_giveaway.php?email=samirp@silvercarrot.com&sublists=395,396&subcampid=2679&ipaddr=216.180.167.121&keycode=kfdj49358gkj359gjk55
*/

include_once("subctr/config.php");

$post_string = "";
while (list($key,$val) = each($_POST)) { $$key = strtolower($val); $post_string .= $key."=".strtolower($val)."&"; }
while (list($key,$val) = each($_GET)) { $$key = strtolower($val); $post_string .= $key."=".strtolower($val)."&"; }
$post_string = substr($post_string,0,strlen($post_string)-1);

if ($keycode != 'kfdj49358gkj359gjk55') { echo 'Error: keycode';exit; }
if ($email == '') { echo 'Error: email';exit; }
if ($sublists == '') { echo 'Error: sublists';exit; }
if ($subcampid == '') { echo 'Error: subcampid';exit; }
if ($ipaddr == '') { echo 'Error: ipaddr';exit; }
if (!ctype_digit($subcampid)) { echo 'Error: subcampid';exit; }

if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) { echo "Error: email";exit; }

//if (LookupImpressionWise($email) == false) { echo 'Error: email. Code: IW';exit; }

list($prefix, $domain) = split("@",$email);
if (!getmxrr($domain, $mxhosts)) { echo "Error: email";exit; }

$check_banned_domain = "SELECT * FROM bannedDomains WHERE domain=\"$domain\" LIMIT 1";
$check_banned_domain_result = mysql_query($check_banned_domain);
if (mysql_num_rows($check_banned_domain_result) == 1) { echo "Error: email";exit; }

$check_banned_email = "SELECT * FROM bannedEmails WHERE email=\"$email\" LIMIT 1";
$check_banned_email_result = mysql_query($check_banned_email);
if (mysql_num_rows($check_banned_email_result) == 1) { echo "Error: email";exit; }

//if (BullseyeBriteVerifyCheck($email) == false) { echo "Error: email";exit; }

$parts = explode(",",$sublists);
if (count($parts) > 0) { foreach($parts as $list_parts) { if (!ctype_digit($list_parts)) { echo 'Error: sublists';exit; } } }

$server_response = Arcamax($email,$sublists,$subcampid,$ipaddr,'sub');

if (strstr($server_response, 'MASTERUNSUB')) { echo $server_response;exit; }


foreach($parts as $list_parts) {
	$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
				VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"FitFabGiveaway2\",\"$subsource\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"FitFabGiveaway2\",\"$subsource\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// get new listid from old listid
	$new_listid = LookupNewListIdByOldListId($list_parts);
				
	// insert into campaigner
	$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$new_listid\",\"$subcampid\",\"FitFabGiveaway2\",\"$subsource\",'sub','N')";
	$campaigner_result = mysql_query($campaigner);
	echo mysql_error();
}

$insert_api_lead = "INSERT INTO api (dateTimeAdded,email,listid,subcampid,ipaddr,subsource) 
		VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",'FitFabGiveaway2')";
$insert_api_lead_result = mysql_query($insert_api_lead);
echo mysql_error();

$insert_log = "INSERT INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
			VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",\"sub\",\"$post_string | $server_response\")";
$insert_log_result = mysql_query($insert_log);
echo mysql_error();

echo $server_response;exit;

?>