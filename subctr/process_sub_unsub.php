<?php

echo 'exit';
exit;

include_once('session_handlers.php');
if (!(isset($_POST['PHPSESSID'])) && !(isset($_GET['PHPSESSID']))) {
	session_start();
	error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
} else {
	if ($_POST['PHPSESSID']) {
		$PHPSESSID = $_POST['PHPSESSID'];
	} else {
		$PHPSESSID = $_GET['PHPSESSID'];
	}
	
	if (session_id() == '') {
		session_start();
	}
}

include_once("config.php");






$email = $_SESSION['email'];
$source = $_SESSION['source'];
$subsource = $_SESSION['subsource'];
$subcampid = $_SESSION['subcampid'];

$listid = trim($_GET['listid']);
$request_type = trim($_GET['request_type']);

if (!ctype_digit($listid)) {
	$listid = '';
}
if (!ctype_alpha($request_type)) {
	$request_type = '';
}

/*
echo "\nemail: ".$email;
echo "\nsubcampid: ".$subcampid;
echo "\nsource: ".$source;
echo "\nsubsource: ".$subsource;
echo "\nlistid: ".$listid;
echo "\nrequest_type: ".$request_type;
echo "\n\n\n";*/

// process sub
if ($request_type == 'sub') {
	// insert into joinEmailSub
	$insert_query = "INSERT INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"$source\",\"$subsource\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// insert into joinEmailActive
	$insert_query = "INSERT INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"$source\",\"$subsource\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// call to function to send new subscriber to Arcamax.
	$send_to_arcamax = Arcamax($email,$listid,$subcampid,$user_ip,'sub'); // sub or unsub

	// record arcamax server response log
	$insert_log = "INSERT INTO arcamaxLog (dateTime,email,listid,subcampid,ipaddr,type,response)
				VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();
	//echo $send_to_arcamax;
	echo 'success';exit;
}


// process unsub
if ($request_type == 'unsub') {
	// insert into joinEmailUnsub
	$insert_query = "INSERT INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source,subsource,errorCode)
				VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"$source\",\"$subsource\",\"per request\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// delete from joinEmailActive
	$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\"";
	$delete_query_result = mysql_query($delete_query);
	echo mysql_error();

	// call to function to send unsub to Arcamax
	$send_to_arcamax = Arcamax($email,$listid,$subcampid,$user_ip,'unsub'); // sub or unsub

	// record arcamax server response log
	$insert_log = "INSERT INTO arcamaxLog (dateTime,email,listid,subcampid,ipaddr,type,response)
				VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();
	//echo $send_to_arcamax;
	echo 'success';exit;
}


?>
