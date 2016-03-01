<?php

/*
Method: HTTPS GET or POST (HTML Form)
Posting URL: https://subctr.popularliving.com/google.php
Required Fields: email=&ipaddr=&keycode=ho7iskvnb5gjhqwp
Example: https://subctr.popularliving.com/google.php?email=samirp@silvercarrot.com&ipaddr=216.180.167.121&keycode=ho7iskvnb5gjhqwp
*/

$sublists = '393,396';
$subcampid = '3324';


include_once("subctr/config.php");

$post_string = "";
while (list($key,$val) = each($_POST)) { $$key = addslashes(strtolower($val)); $post_string .= $key."=".addslashes(strtolower($val))."&"; }
while (list($key,$val) = each($_GET)) { $$key = addslashes(strtolower($val)); $post_string .= $key."=".addslashes(strtolower($val))."&"; }
$post_string = substr($post_string,0,strlen($post_string)-1);

mail('samirp@silvercarrot.com','subctr google api',$post_string);

if ($keycode != 'ho7iskvnb5gjhqwp') { echo 'Error: keycode';exit; }
if ($email == '') { echo 'Error: email';exit; }
//if ($sublists == '') { echo 'Error: sublists';exit; }
//if ($subcampid == '') { echo 'Error: subcampid';exit; }
if ($ipaddr == '') { echo 'Error: ipaddr';exit; }
//if (!ctype_digit($subcampid)) { echo 'Error: subcampid';exit; }

if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) { echo "Error: email";exit; }

if (LookupImpressionWise($email) == false) { echo 'Error: email. Code: IW';exit; }

list($prefix, $domain) = split("@",$email);
if (!getmxrr($domain, $mxhosts)) { echo "Error: No MX Record";exit; }

$check_banned_domain = "SELECT * FROM bannedDomains WHERE domain=\"$domain\" LIMIT 1";
$check_banned_domain_result = mysql_query($check_banned_domain);
if (mysql_num_rows($check_banned_domain_result) == 1) { echo "Error: banned domain";exit; }

$check_banned_email = "SELECT * FROM bannedEmails WHERE email=\"$email\" LIMIT 1";
$check_banned_email_result = mysql_query($check_banned_email);
if (mysql_num_rows($check_banned_email_result) == 1) { echo "Error: banned email";exit; }

if (BullseyeBriteVerifyCheck($email) == false) { echo "Error: Brite Verify Failed";exit; }

$parts = explode(",",$sublists);
//if (count($parts) > 0) { foreach($parts as $list_parts) { if (!ctype_digit($list_parts)) { echo 'Error: sublists';exit; } } }

$server_response = Arcamax($email,$sublists,$subcampid,$ipaddr,'sub');

if (strstr($server_response, 'MASTERUNSUB')) { echo $server_response;exit; }


foreach($parts as $list_parts) {
	$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
				VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"GoogleSignupWithinAds\",\"$subsource\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"GoogleSignupWithinAds\",\"$subsource\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	
	// get new listid from old listid
	$new_listid = LookupNewListIdByOldListId($list_parts);
				
	// insert into campaigner
	$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$new_listid\",\"$subcampid\",\"GoogleSignupWithinAds\",\"$subsource\",'sub','N')";
	$campaigner_result = mysql_query($campaigner);
	echo mysql_error();

}

$insert_api_lead = "INSERT IGNORE INTO api (dateTimeAdded,email,listid,subcampid,ipaddr,subsource) 
		VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",'GoogleSignupWithinAds')";
$insert_api_lead_result = mysql_query($insert_api_lead);
echo mysql_error();

$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
			VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",\"sub\",\"$post_string&sublists=$sublists&subcampid=$subcampid | $server_response\")";
$insert_log_result = mysql_query($insert_log);
echo mysql_error();

echo $server_response;exit;

?>