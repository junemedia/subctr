<?php

/*
Method: HTTPS GET or POST (HTML Form)
Posting URL: http://r4l.popularliving.com/flow.php
Required Fields: email=&ipaddr=&keycode=kgjie95khls2968&sublists=&subcampid=&subsource=
Example: https://r4l.popularliving.com/flow.php?email=samirp@silvercarrot.com&ipaddr=216.180.167.121&keycode=kgjie95khls2968&sublists=393,396&subcampid=1234&subsource=LINKID
*/

include_once("subctr/config.php");

$post_string = "";
while (list($key,$val) = each($_POST)) { $$key = addslashes(strtolower($val)); $post_string .= $key."=".addslashes(strtolower($val))."&"; }
while (list($key,$val) = each($_GET)) { $$key = addslashes(strtolower($val)); $post_string .= $key."=".addslashes(strtolower($val))."&"; }
$post_string = substr($post_string,0,strlen($post_string)-1);

//Only for a brand new email address that we have never seen do we fire the call to google analytics
$select_query = "SELECT email FROM campaigner WHERE email=\"$email\"";
$select_query_result = mysql_query($select_query);
$check_response = ' is_newemail:false';

if(mysql_num_rows($select_query_result) == 0)
{
	$check_response=' is_newemail:true';
}

if ($keycode != 'kgjie95khls2968') { echo 'Error: keycode'.$check_response;exit; }
if ($email == '') { echo 'Error: email'.$check_response;exit; }
if ($sublists == '') { echo 'Error: sublists'.$check_response;exit; }
if ($subcampid == '') { echo 'Error: subcampid'.$check_response;exit; }
if ($ipaddr == '') { echo 'Error: ipaddr'.$check_response;exit; }
if (!ctype_digit($subcampid)) { echo 'Error: subcampid'.$check_response;exit; }

// SKIP EMAIL VALIDATION BECAUSE IMPRESSION WISE AND BRITE VERIFY IS ALREADY DONE BEFORE DATAPASS TO THIS SCRIPT
//if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) { echo "Error: email";exit; }
//if (LookupImpressionWise($email) == false) { echo 'Error: email. Code: IW';exit; }
//list($prefix, $domain) = split("@",$email);
//if (!getmxrr($domain, $mxhosts)) { echo "Error: No MX Record";exit; }
//if (BullseyeBriteVerifyCheck($email) == false) { echo "Error: Brite Verify Failed";exit; }

$parts = explode(",",$sublists);
if (count($parts) > 0) { foreach($parts as $list_parts) { if (!ctype_digit($list_parts)) { echo 'Error: sublists'.$check_response;exit; } } }

$server_response = Arcamax($email,$sublists,$subcampid,$ipaddr,'sub');

if (strstr($server_response, 'MASTERUNSUB')) { echo $server_response.$check_response;exit; }

$subsource = strtoupper($subsource);

foreach($parts as $list_parts) {
	$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
				VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"R4LFlowDhtml\",\"$subsource\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"R4LFlowDhtml\",\"$subsource\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// get new listid from old listid
	$new_listid = LookupNewListIdByOldListId($list_parts);
				
	// insert into campaigner
	$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
					VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$new_listid\",\"$subcampid\",\"R4LFlowDhtml\",\"$subsource\",'sub','N')";
	$campaigner_result = mysql_query($campaigner);
	echo mysql_error();
}

$insert_api_lead = "INSERT IGNORE INTO api (dateTimeAdded,email,listid,subcampid,ipaddr,subsource) 
		VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",'R4LFlowDhtml')";
$insert_api_lead_result = mysql_query($insert_api_lead);
echo mysql_error();

$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
			VALUES (NOW(),\"$email\",\"$sublists\",\"$subcampid\",\"$ipaddr\",\"sub\",\"$post_string&sublists=$sublists&subcampid=$subcampid | $server_response\")";
$insert_log_result = mysql_query($insert_log);
echo mysql_error();

echo $server_response.$check_response;exit;
//echo $json_result;exit;
//echo 'test';exit;

?>