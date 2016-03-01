<?php

ini_set('session.save_handler','user');
ini_set('session.gc_maxlifetime','3600');

function getMicroTime() {
	$mtime = explode(" ", microtime());
	return ($mtime[1] + $mtime[0]);
}

//the database connection
$hostName = "8ec3cdb8845732ea5bbc2a32fa2a87d52453102e.rackspaceclouddb.com";
$username = 'jingshi';
$password = 'kendeji12306!';
$dbname = 'arcamax';

$connection = NULL;
$session_table = NULL;

function sessionOpen($database_name, $table_name) {
  global $connection;
  global $session_table;
  global $dbname;
  global $hostName;
  global $username;
  global $password;
  //global $dbase;
  $database_name = $dbname;

  if (!($connection = @ mysql_connect($hostName, $username, $password)))
     echo dbError();

  if (!mysql_select_db($database_name, $connection))
     echo __file__.":".__line__.":".dbError();

  $session_table = $table_name;
  return true;
}

function sessionRead($sess_id) {
	global $connection;
	global $session_table;
	
	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $sess_id) == 0) {
		return "";
	}
	
	
	$search_query = "SELECT * FROM $session_table WHERE session_id = '$sess_id'";

	if(!($result = @mysql_query($search_query,
								$connection)))
	echo __file__.":".__line__.":".dbError();
	
	if(mysql_num_rows($result) == 0) {
		return "";
	} else {
		$row = mysql_fetch_array($result);
		return stripslashes($row["session_variable"]);
	}
}

function sessionWrite($sess_id, $val) {
	global $connection;
	global $session_table;
	$time_stamp = getMicroTime();
	
	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $sess_id) == 0) {
		return "";
	}
	
	$search_query = "SELECT session_id from $session_table WHERE session_id = '$sess_id'";
	
	if(!($result = @ mysql_query($search_query,
								 $connection)))
		echo __file__.":".__line__.":".dbError();
		
	if(mysql_num_rows($result) == 0){
		$insert_query = "INSERT INTO $session_table (session_id, session_variable, last_accessed) values
						('$sess_id', '".addslashes($val)."', '$time_stamp')";
		if(!mysql_query($insert_query, 
						$connection))
			echo __file__.":".__line__.":".dbError();
	} else {
		//existing session found
		$val = addslashes($val);
		$update_query = "UPDATE $session_table SET session_variable = \"$val\", last_accessed = '$time_stamp' 
						 WHERE session_id = '$sess_id'";
		if(!mysql_query($update_query,
						$connection))
			echo __file__.":".__line__.":".dbError();
	}
	return true;
}

function sessionClose() {
	return true;
}

function sessionDestroy($sess_id) {
	global $connection;
	global $session_table;
	
	if (preg_match('~^[A-Za-z0-9]{16,32}$~', $sess_id) == 0) {
		return "";
	}
	
	$delete_query = "DELETE FROM $session_table WHERE session_id = '$sess_id'";
	
	if(!($result = @ mysql_query($delete_query,
								 $connection)))
		echo __file__.":".__line__.":".dbError();
	return true;
}

function sessionGC($max_lifetime) {
	global $connection;
	global $session_table;
	
	$time_stamp = getMicroTime();
	
	$delete_query = "DELETE FROM $session_table WHERE last_accessed < ($time_stamp - $max_lifetime)";
	
	if(!($result = @mysql_query($delete_query,
								$connection)))
		echo __file__.":".__line__.":".dbError();
	return true;
}

session_set_save_handler("sessionOpen", "sessionClose","sessionRead", "sessionWrite", "sessionDestroy", "sessionGC");

if ($_COOKIE['PHPSESSID']) {
	$PHPSESSID = $_COOKIE['PHPSESSID'];
} else {
	if($_POST['PHPSESSID']){
		$PHPSESSID = $_POST['PHPSESSID'];
	} else if ($_GET['PHPSESSID']){
		$PHPSESSID = $_GET['PHPSESSID'];
	}
}

?>
