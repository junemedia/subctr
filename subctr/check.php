<?php

include_once('session_handlers.php');
include 'maropostMap.php';


if (!(isset($_POST['PHPSESSID'])) && !(isset($_GET['PHPSESSID']))) {
  session_start();
  error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
}
else {
  if ($_POST['PHPSESSID']) {
    $PHPSESSID = $_POST['PHPSESSID'];
  }
  else {
    $PHPSESSID = $_GET['PHPSESSID'];
  }

  if (session_id() == '') {
    session_start();
  }
}

include_once("config.php");

// send user back to main page if they didn't pass captcha.
// this is security feature and will block attacks via script or automated tool.
if ($_SESSION['passed_captcha'] == 'no') {
  $url = "http://".trim($_SERVER['SERVER_NAME'])."/subctr/index.php?PHPSESSID=".session_id();
  header("Location:$url");
  exit;
}

/***************************** debugging ***************************/
/* if ($debug === true) { */
/*   echo '<pre style="font-size: 80%;">'; */
/*   print_r($_SESSION); */
/*   echo '</pre>'; */
/* } */
/**************************** /debugging ***************************/

$confirmation = "";
$show_conf_message = false;







/* ***************************************************************** */
/*  get Maropost data                                                */
/* ***************************************************************** */
// get the Maropost data for the contact
list( $contact, $mp_sorted_subs ) = getContact($_SESSION['email']);

/***************************** debugging ***************************/
/* echo '<pre style="font-size: 80%;">'; */
/* print_r($mp_sorted_subs); */
/* print_r($list_subscriptions); */
/* echo "{$contact['email']}: {$contact['id']}"; */
/* echo '</pre>'; */
/**************************** /debugging ***************************/




/* ***************************************************************** */
/*  handle form submission                                           */
/* ***************************************************************** */
if (isset($_POST['submit']) && $_POST['submit'] == 'Update Information') {
  // getting an array of `joinLists`.`listid` (395, 394, etc...)
  // in other words a list of all the newsletters the user would like
  // to start/continue to receive
  $aJoinListId = $_POST['aJoinListId'];

  // create empty array if necessary
  if (count($_POST['aJoinListId']) == 0) {
    $aJoinListId = array();
  }

  // no idea at this point where the source and subsource session
  // vars would be getting set, but it apparently happens somewhere
  // sometimes
  $email = $_SESSION['email'];
  $user_ip = $_SESSION['user_ip'];
  $source = @$_SESSION['source'];
  $subsource = @$_SESSION['subsource'];
  if ($subsource == '') { $subsource = $host; }
  //if($source == ''){$source = "$host Sub Center";}


  /***************************** debugging ***************************/
  /* if ($debug === true) { */
  /*     echo '<pre style="font-size: 80%">'; */
  /*     print_r($_POST); */
  /*     echo '</pre>'; */
  /* } */
  /**************************** /debugging ***************************/


  // array of unselected ids
  // diff the array of all lists with the array we received, in other
  // words the list of all newsletters the user should not receive
  $not_checked_array = array_diff($_SESSION['all_listing_array'], $aJoinListId);

  // string of comma-separated ids of newly un/subscribed lists,
  // i.e., form items that have changed
  $new_sub = '';
  $new_unsub = '';

  $build_list_id_for_r4l = '';
  $build_list_id_for_ff = '';
  $build_list_id_for_wim = '';
  $build_list_id_for_sf = '';
  $build_list_id_for_br = '';

  /* *************************************************************** */
  /*  Do subscribes:
  /*  check if this is already in our system. if so, do nothing.
  /*  if not, then add as new signup
  /* *************************************************************** */
  foreach ($aJoinListId as $checked) {
    $check_query = "SELECT *
                    FROM joinEmailActive
                    WHERE email =\"$email\"
                    AND listid=\"$checked\"";
    $check_query_result = mysql_query($check_query);
    echo mysql_error();

    /************************ do Maropost subs ************************/
    // if not already subscribed, make call to Maropost
    if (isset($maropostMap[$checked])) {
      $mapped_id = $maropostMap[$checked]['id'];

      if (!isset($mp_sorted_subs['subscribed'][$mapped_id])) {

        // if contact doesn't already exist, add them to maropost
        if ($contact['id'] == 0) {
          // get the Maropost data for the contact
          list( $contact, $mp_sorted_subs ) = getContact($_SESSION['email']);
        }

        contactSubscribe($contact, $mapped_id);
      }
      else {
        //echo 'already subbed to '.$mapped_id.' '.$mp_sorted_subs['subscribed'][$mapped_id].'<br>';
      }
      // clear $mapped_id
      $mapped_id = 0;
    }

    // this is a new [re-]subscription
    if (mysql_num_rows($check_query_result) == 0) {

      // get the subcampid and add to appropriate $build_list
      $subcampid = '';
      if (in_array($checked, $_SESSION['r4l_all_listid'])) {
        $subcampid = $_SESSION['r4l_subcampid'];
        $build_list_id_for_r4l .= $checked.',';
      }
      if (in_array($checked, $_SESSION['fitfab_all_listid'])) {
        $subcampid = $_SESSION['fitfab_subcampid'];
        $build_list_id_for_ff .= $checked.',';
      }
      if (in_array($checked, $_SESSION['wim_all_listid'])) {
        $subcampid = $_SESSION['wim_subcampid'];
        $build_list_id_for_wim .= $checked.',';
      }
      if (in_array($checked, $_SESSION['sf_all_listid'])) {
        $subcampid = $_SESSION['sf_subcampid'];
        $build_list_id_for_sf .= $checked.',';
      }
      if (in_array($checked, $_SESSION['br_all_listid'])) {
        $subcampid = $_SESSION['br_subcampid'];
        $build_list_id_for_br .= $checked.',';
      }

      // add id to $new_subs string
      $new_sub .= "'$checked',";

      // insert into `joinEmailSub`
      // `joinEmailSub` logs subscribe activity
      $insert_query = "INSERT INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
              VALUES (NOW(),\"$email\",\"$user_ip\",\"$checked\",\"$subcampid\",\"$source\",\"$subsource\")";
      $insert_query_result = mysql_query($insert_query);
      echo mysql_error();

      // insert into `joinEmailActive`
      $insert_query = "INSERT INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
              VALUES (NOW(),\"$email\",\"$user_ip\",\"$checked\",\"$subcampid\",\"$source\",\"$subsource\")";
      $insert_query_result = mysql_query($insert_query);
      echo mysql_error();

      // get new listid from old listid
      $new_listid = LookupNewListIdByOldListId($checked);

      // insert into `campaigner`
      $campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
              VALUES (NOW(),\"$email\",\"$user_ip\",\"$checked\",\"$new_listid\",\"$subcampid\",\"$source\",\"$subsource\",'sub','N')";
      $campaigner_result = mysql_query($campaigner);
      echo mysql_error();

      $_SESSION['bouncecount'] = 0; // sub/unsub requests must reset bounce count to zero
    }
    else {
      // since this user already in our system, simply do nothing and continue
    }
  }

  /* *************************************************************** */
  /*  Do not sure what
  /* *************************************************************** */
  if ($build_list_id_for_r4l != '') {
    $temp_subcampid = $_SESSION['r4l_subcampid'];
    $build_list_id_for_r4l = substr($build_list_id_for_r4l,0,strlen($build_list_id_for_r4l)-1);
    $send_to_arcamax = true; // sub or unsub

    $insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
          VALUES (NOW(),\"$email\",\"$build_list_id_for_r4l\",\"$temp_subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
    $insert_log_result = mysql_query($insert_log);
    echo mysql_error();
    $temp_subcampid = ''; // clear temp value
  }

  if ($build_list_id_for_ff != '') {
    $temp_subcampid = $_SESSION['fitfab_subcampid'];
    $build_list_id_for_ff = substr($build_list_id_for_ff,0,strlen($build_list_id_for_ff)-1);
    $send_to_arcamax = true; // sub or unsub

    $insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
          VALUES (NOW(),\"$email\",\"$build_list_id_for_ff\",\"$temp_subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
    $insert_log_result = mysql_query($insert_log);
    echo mysql_error();
    $temp_subcampid = ''; // clear temp value
  }

  if ($build_list_id_for_wim != '') {
    $temp_subcampid = $_SESSION['wim_subcampid'];
    $build_list_id_for_wim = substr($build_list_id_for_wim,0,strlen($build_list_id_for_wim)-1);
    $send_to_arcamax = true; // sub or unsub

    $insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
          VALUES (NOW(),\"$email\",\"$build_list_id_for_wim\",\"$temp_subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
    $insert_log_result = mysql_query($insert_log);
    echo mysql_error();
    $temp_subcampid = ''; // clear temp value
  }

  if ($build_list_id_for_sf != '') {
    $temp_subcampid = $_SESSION['sf_subcampid'];
    $build_list_id_for_sf = substr($build_list_id_for_sf,0,strlen($build_list_id_for_sf)-1);
    $send_to_arcamax = true; // sub or unsub

    $insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
          VALUES (NOW(),\"$email\",\"$build_list_id_for_sf\",\"$temp_subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
    $insert_log_result = mysql_query($insert_log);
    echo mysql_error();
    $temp_subcampid = ''; // clear temp value
  }

  if ($build_list_id_for_br != '') {
    $temp_subcampid = $_SESSION['sf_subcampid'];
    $build_list_id_for_br = substr($build_list_id_for_br,0,strlen($build_list_id_for_br)-1);
    $send_to_arcamax = true; // sub or unsub

    $insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
          VALUES (NOW(),\"$email\",\"$build_list_id_for_br\",\"$temp_subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
    $insert_log_result = mysql_query($insert_log);
    echo mysql_error();
    $temp_subcampid = ''; // clear temp value
  }



  /* *************************************************************** */
  /*  Do unsubscribes
  /*  check if this is in our system. If so, unsub, else do nothing
  /* *************************************************************** */
  foreach ($not_checked_array as $not_checked) {
    $check_query = "SELECT * FROM joinEmailActive
                    WHERE email =\"$email\"
                    AND listid=\"$not_checked\"";
    $check_query_result = mysql_query($check_query);
    echo mysql_error();


    /************************ do Maropost un-subs *************************/
    if (isset($maropostMap[$not_checked])) {
      $mapped_id = $maropostMap[$not_checked]['id'];

      if (isset($mp_sorted_subs['subscribed'][$mapped_id])) {
        contactUnsubscribe($contact, $mapped_id);
      }
      else {
        //echo 'not subbed to '.$mapped_id.' '.$mp_sorted_subs['subscribed'][$mapped_id].'<br>';
      }

    }


    if (mysql_num_rows($check_query_result) == 0) {
      // since this user is not in our system, simply do nothing and continue
    }
    else {
      // this is a new un-subscribe

      // get the subcampid
      $subcampid = '';
      if (in_array($not_checked, $_SESSION['r4l_all_listid'])) {
        $subcampid = $_SESSION['r4l_subcampid'];
      }
      if (in_array($not_checked, $_SESSION['fitfab_all_listid'])) {
        $subcampid = $_SESSION['fitfab_subcampid'];
      }
      if (in_array($not_checked, $_SESSION['wim_all_listid'])) {
        $subcampid = $_SESSION['wim_subcampid'];
      }
      if (in_array($not_checked, $_SESSION['sf_all_listid'])) {
        $subcampid = $_SESSION['sf_subcampid'];
      }
      if (in_array($not_checked, $_SESSION['br_all_listid'])) {
        $subcampid = $_SESSION['br_subcampid'];
      }

      // add id to new unsubs string
      $new_unsub .= "'$not_checked',";

      // insert into `joinEmailUnsub`
      // `joinEmailUnsub` logs unsubscribe activity
      $insert_query = "INSERT INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source,subsource,errorCode)
            VALUES (NOW(),\"$email\",\"$user_ip\",\"$not_checked\",\"$subcampid\",\"$source\",\"$subsource\",\"per request\")";
      $insert_query_result = mysql_query($insert_query);
      echo mysql_error();

      // delete from `joinEmailActive`
      $delete_query = "DELETE FROM joinEmailActive
              WHERE email =\"$email\" AND listid=\"$not_checked\" LIMIT 1";
      $delete_query_result = mysql_query($delete_query);
      echo mysql_error();

      // get new listid from old listid
      $new_listid = LookupNewListIdByOldListId($not_checked);

      // insert into `campaigner`
      $campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
              VALUES (NOW(),\"$email\",\"$user_ip\",\"$not_checked\",\"$new_listid\",\"$subcampid\",\"$source\",\"$subsource\",'unsub','N')";
      $campaigner_result = mysql_query($campaigner);
      echo mysql_error();

      // call to function to send unsub to Arcamax
      $send_to_arcamax = true; // sub or unsub

      // record arcamax server response log
      $insert_log = "INSERT INTO arcamaxNewLog (dateTime, email,listid,subcampid,ipaddr,type,response)
            VALUES (NOW(),\"$email\",\"$not_checked\",\"$subcampid\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
      $insert_log_result = mysql_query($insert_log);
      echo mysql_error();

      $_SESSION['bouncecount'] = 0; // sub/unsub requests must reset bounce count to zero
    }
  }

  // remove trailing comma
  $new_sub = substr($new_sub, 0, strlen($new_sub)-1);
  $new_unsub = substr($new_unsub, 0, strlen($new_unsub)-1);

  // generate html to display new subsciptions
  if ($new_sub != '') {
    $get_title = "SELECT title,frequency FROM joinLists WHERE listid IN ($new_sub)";
    $get_title_result = mysql_query($get_title);
    echo mysql_error();

    $new_sub = "";
    while ($row = mysql_fetch_object($get_title_result)) {
      $new_sub .= "<font style='color: rgb(20, 80, 106);'><b>$row->title</b></font> <font style='font-size: 11px; color: rgb(70, 70, 70);'>($row->frequency)</font><br>";
    }
    $new_sub = "<br><b>You have just been subscribed to:</b><br>".$new_sub;
    $show_conf_message = true;
  }
  else {
    $new_sub = '';
  }

  // generate html to display new un-subscribes
  if ($new_unsub != '') {
    $get_title = "SELECT title,frequency FROM joinLists WHERE listid IN ($new_unsub)";
    $get_title_result = mysql_query($get_title);
    echo mysql_error();
    $new_unsub = "";
    while ($row = mysql_fetch_object($get_title_result)) {
      $new_unsub .= "<font style='color: rgb(20, 80, 106);'><b>$row->title</b></font> <font style='font-size: 11px; color: rgb(70, 70, 70);'>($row->frequency)</font><br>";
    }
    $new_unsub = "<br><b>You have just unsubscribed from:</b><br>".$new_unsub;
    $show_conf_message = true;
  }
  else {
    $new_unsub = '';
  }

  // generate html to display unchanged subscriptions
  $still_subscribe_to = "";
  $get_title = "SELECT title,frequency FROM joinLists WHERE listid IN (SELECT listid FROM joinEmailActive WHERE email = '$email')";
  $get_title_result = mysql_query($get_title);
  echo mysql_error();

  while ($row = mysql_fetch_object($get_title_result)) {
    $still_subscribe_to .= "<font style='color: rgb(20, 80, 106);'><b>$row->title</b></font> <font style='font-size: 11px; color: rgb(70, 70, 70);'>($row->frequency)</font><br>";
  }
  if ($still_subscribe_to != '') {
    $still_subscribe_to = "<br><b>Your current subscriptions include:</b><br>".$still_subscribe_to;
  }

  // only display if subscription changes have been made
  if ($show_conf_message == true) {
    $confirmation = "
        <tr><td colspan='2' style='line-height:30%'>&nbsp;</td></tr>
        <tr><td colspan='2'>Your subscriptions have been updated.
          Please allow 24-48 hours for these changes to take effect. Here is a summary of your changes: <br>
          $new_sub
          $new_unsub
          $still_subscribe_to
          </td></tr>
        <tr><td colspan='2' style='line-height:30%'>&nbsp;</td></tr>";
  }
  // reload page: refreshes the MP data
  header("Location: {$_SERVER['PHP_SELF']}");
}
/* end form submission handler */

// exit the page if email is not set.
if (trim($_SESSION['email']) == '') {
  echo "<div style='font-family: verdana;font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;'>
      Sorry, something went wrong.
      <a href='"."http://".trim($_SERVER['HTTP_HOST'])."/subctr/index.php?PHPSESSID=".session_id()."'>Click here and try again</a>.
        </div>";
  exit;
}

// pretty sure there's no need to enclose this in an else block due to
// the exit in the if block(?), but leaving for now
else {
  /********************** START = Get User Data **********************/
  $get_user_data = "SELECT * FROM userData WHERE email=\"".trim($_SESSION['email'])."\" LIMIT 1";
  $get_user_data_result = mysql_query($get_user_data);
  echo mysql_error();
  $userData_row = mysql_fetch_object($get_user_data_result);

  $_SESSION['fname'] = @$userData_row->fname;
  $_SESSION['lname'] = @$userData_row->lname;
  $_SESSION['zip'] = @$userData_row->zip;
  $_SESSION['gender'] = @$userData_row->gender;
  $_SESSION['day'] = @$userData_row->day;
  $_SESSION['month'] = @$userData_row->month;
  $_SESSION['year'] = @$userData_row->year;
  /* END = Get User Data */
}



/******************** START = Subscription History *******************/
//  [MM/DD/YYYY] at [TIME] from [IP ADDRESS HERE]
$first_signup_ipaddr = '';
$first_signup_date = '';
$temp_email = trim($_SESSION['email']);
$get_past_data = "SELECT dateTime,ipaddr FROM joinEmailSub WHERE email='$temp_email' ORDER BY dateTime ASC LIMIT 1";
$get_past_data_result = mysql_query($get_past_data);
echo mysql_error();

// $first_time indicates a new subscriber
$first_time = true;
// if the previous query returned a result, then they're not a
// first time subscriber
while ($get_past_data_row = mysql_fetch_object($get_past_data_result)) {
  $first_signup_ipaddr = $get_past_data_row->ipaddr;
  $first_signup_date = substr($get_past_data_row->dateTime,5,2).'/'.substr($get_past_data_row->dateTime,8,2).'/'.substr($get_past_data_row->dateTime,0,4);
  $first_time = false;
}
/* END = Subscription History */


/******************* START = Manage My Newsletters *******************/
// get all lists this user is subscribed to
$active_signup_array = array();
$signup_active = "SELECT *
                  FROM joinEmailActive
                  WHERE email=\"".trim($_SESSION['email'])."\"";
$signup_active_result = mysql_query($signup_active);
$signup_count = mysql_num_rows($signup_active_result);
echo mysql_error();

if ($signup_count == 0) {
  $active_subscriber = false;
}
else {
  $active_subscriber = true;
  while ($signup_active_row = mysql_fetch_object($signup_active_result)) {
    array_push($active_signup_array, $signup_active_row->listid);
  }
}
/* END = Manage My Newsletters */


/******************** START = GET LISTING TEMPLATE *******************/
// get template for main site listing
// the whole thing is so dumb there has to be a reason for it...
// queries db to get number of lists for the current site, then based
// on that number loads a template that has that number of slots and
// saves it as a string

// this is just to get the number of lists for the current site
$active_count = "SELECT *
                 FROM joinLists
                 WHERE isActive = 'Y'
                 AND site LIKE '%$host%'";
$active_count_result = mysql_query($active_count);
echo mysql_error();

// get contents of a file into a string
$filename = getcwd()."/templates/".mysql_num_rows($active_count_result)."_slots.html";
$handle = fopen($filename, "r");
$main_newsletter_listing = fread($handle, filesize($filename));
fclose($handle);

// same process as above for lists for sister sites...
// get template for more newsletter listing
// get lists of sister sites
$active_count = "SELECT *
                 FROM joinLists
                 WHERE isActive = 'Y'
                 AND site NOT LIKE '%$host%'";
$active_count_result = mysql_query($active_count);
echo mysql_error();

// get contents of a file into a string
// this is so dumb, there has to be a good reason for it...
$filename = getcwd()."/templates/".mysql_num_rows($active_count_result)."_slots.html";
$handle = fopen($filename, "r");
$more_newsletter_listing = fread($handle, filesize($filename));
fclose($handle);
/* END = GET LISTING TEMPLATE */

$_SESSION['all_listing_array'] = array();

/******************* START = Get Listing of NL/SOLO ******************/
// we just did this above, but whatevs...
// get lists for current site, and dump list info into the template
// chosen above
//
// also builds SESSION all_listing_array in the process

// get lists for current site
$active_nl_query = "SELECT *
                    FROM joinLists
                    WHERE isActive = 'Y'
                    AND site LIKE '%$host%'
                    ORDER BY sortOrder ASC";
$active_nl_result = mysql_query($active_nl_query);
echo mysql_error();

$xx = 0;
while ($active_nl_row = mysql_fetch_object($active_nl_result)) {
  array_push($_SESSION['all_listing_array'], $active_nl_row->listid);

  $xx++;
  $checked = isSubscribed($active_nl_row->listid, $active_signup_array, $mp_sorted_subs) ? 'checked' : '';

  // this is insane...
  $main_newsletter_listing = str_replace("[CHECKBOX_$xx]", "<input type='checkbox' value='$active_nl_row->listid' name='aJoinListId[]' $checked>", $main_newsletter_listing);
  $main_newsletter_listing = str_replace("[MESSAGE_$xx]",  "<div id='$active_nl_row->listid'></div>", $main_newsletter_listing);
  $main_newsletter_listing = str_replace("[IMAGE_URL_$xx]", $active_nl_row->logo, $main_newsletter_listing);
  $main_newsletter_listing = str_replace("[TITLE_$xx]", $active_nl_row->title, $main_newsletter_listing);
  $main_newsletter_listing = str_replace("[FREQUENCY_$xx]", $active_nl_row->frequency, $main_newsletter_listing);
  $main_newsletter_listing = str_replace("[DESCRIPTION_$xx]", $active_nl_row->description, $main_newsletter_listing);
}

// do the same for the list of the sister sites...
$active_nl_query = "SELECT * FROM joinLists
                    WHERE isActive = 'Y'
                    AND site NOT LIKE '%$host%'
                    ORDER BY site, sortOrder ASC";
$active_nl_result = mysql_query($active_nl_query);
echo mysql_error();

$xx = 0;
while ($active_nl_row = mysql_fetch_object($active_nl_result)) {
  array_push($_SESSION['all_listing_array'], $active_nl_row->listid);

  $xx++;
  $checked = isSubscribed($active_nl_row->listid, $active_signup_array, $mp_sorted_subs) ? 'checked' : '';

  // this is insane...
  $more_newsletter_listing = str_replace("[CHECKBOX_$xx]", "<input type='checkbox' value='$active_nl_row->listid' name='aJoinListId[]' $checked>", $more_newsletter_listing);
  $more_newsletter_listing = str_replace("[MESSAGE_$xx]", "<div id='$active_nl_row->listid'></div>", $more_newsletter_listing);
  $more_newsletter_listing = str_replace("[IMAGE_URL_$xx]", $active_nl_row->logo, $more_newsletter_listing);
  $more_newsletter_listing = str_replace("[TITLE_$xx]", $active_nl_row->title, $more_newsletter_listing);
  $more_newsletter_listing = str_replace("[FREQUENCY_$xx]", $active_nl_row->frequency, $more_newsletter_listing);
  $more_newsletter_listing = str_replace("[DESCRIPTION_$xx]", $active_nl_row->description, $more_newsletter_listing);
}
/* END = Get Listing of NL/SOLO */
?>

<html>
<head>
  <meta name="keywords" content="recipe newsletters, free recipe newsletter, recipe newsletter, daily recipe newsletter, crockpot recipe newsletter, slow cooker recipe newsletter, free slow cooker recipes, free crockpot recipes, free crockpot recipe newsletter budget cooking recipes, budget cooking newsletter,party recipes and tips, party recipe newsletter, quick and easy recipe newsletter, quick and easy recipes" />
  <meta name="description" content="Looking for recipes and cooking tips? You have come to the right place!" />
  <title>Manage My Newsletters</title>
  <script>window.scroll(0,0);</script>
  <style>
    body {
      font-family: verdana;
    }
  </style>
</head>
<body>
  <table width="575px" align="center" border="0" id="sub_form" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
    <tr>
      <td colspan="2">
        <a href="index.php?PHPSESSID=<?php echo session_id(); ?>"><b>Go Back To Sign In Page</b></a>
      </td>
    </tr>
    <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>
    <!---  START OF MANAGE MY NEWSLETTER SECTION -->
    <tr>
      <td colspan="2">
        <?php
          if ($host == 'r4l') { ?>
            <font color="#0F22B0" size="4" style="font-family: arial;">Manage My Newsletters</font>
          <?php }
          if ($host == 'fitfab') { ?>
            <font color="#EC519D" size="4" style="font-family: arial;">Manage My Newsletters</font>
          <?php }
          if ($host == 'wim') { ?>
            <font color="#2789BD" size="4" style="font-family: arial;">Manage My Newsletters</font>
          <?php }
          if ($host == 'sf') { ?>
            <font color="#F99D1C" size="4" style="font-family: arial;">Manage My Newsletters</font>
          <?php }
          if ($host == 'br') { ?>
            <font color="#0F22B0" size="4" style="font-family: arial;">Manage My Newsletters</font>
          <?php }
        ?>
      </td>
    </tr>
    <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>

    <?php echo $confirmation; ?>

    <tr>
      <td colspan="2">
        <?php //if ($host == 'r4l') { ?>
          <?php if ($active_subscriber == false) { ?>
            <b><?php echo trim($_SESSION['email']); ?></b> is currently <b>not</b> subscribed to any newsletters.<br><br>
          <?php } else { ?>
            <?php if ($show_conf_message == false) { ?><b><?php echo trim($_SESSION['email']); ?></b> is currently subscribed to the newsletters checked below.<br><br><?php } ?>
          <?php } ?>
        <?php //} ?>

        <?php
          // this will never be true anymore...see getBounceCountFromArcamax()
          if (ctype_digit($_SESSION['bouncecount']) && $_SESSION['bouncecount'] >= 20) {
            echo "<font color='red'>Note: You may not be receiving your newsletters because we currently show that your e-mail address has bounced out due to delivery problems with your domain. <b>If you would like to restore your subscription, <a href='reset.php'>click here</a></b>.</font><br><br>";
          }
          if (isset($_SESSION['BounceCountReset']) && $_SESSION['BounceCountReset'] == true) {
            $_SESSION['BounceCountReset'] = false;
            echo "<font color='red'>Thank you! Your subscription has been <b>restored</b>. You should start to receive e-mails within 24-48 hours. If you still do not receive your newsletters, <a href='http://www.recipe4living.com/contact/' target=_blank>contact us here</a>.</font><br><br>";
          }

        ?>

        Check the box next to the newsletter you want to receive. Uncheck a box to no longer receive that newsletter.
        <br><br>
        <b>When you are finished making your selections, scroll down and press the "Update Information" button.</b>
        <br><br>
        If you're simply looking to change your email address, <b><a href="http://<?php echo trim($_SERVER['HTTP_HOST']); ?>/subctr/email_change.php?PHPSESSID=<?php echo session_id(); ?>">click here</a></b>.
      </td>
    </tr>
    <!---  END OF MANAGE MY NEWSLETTER SECTION -->

    <!---  START OF Newsletters/Offers -->
    <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>
    <tr>
      <td colspan="2">
        <?php
          if ($host == 'r4l') { ?>
            <font color="#0F22B0" size="4" style="font-family: arial;">Recipe4Living Newsletters</font>
          <?php }
          if ($host == 'fitfab') { ?>
            <font color="#EC519D" size="4" style="font-family: arial;">Fit&FabLiving Newsletters</font>
          <?php }
          if ($host == 'wim') { ?>
            <font color="#2789BD" size="4" style="font-family: arial;">Work It, Mom! Newsletters</font>
          <?php }
          if ($host == 'sf') { ?>
            <font color="#F99D1C" size="4" style="font-family: arial;">SavvyFork Newsletters</font>
          <?php }
          if ($host == 'br') { ?>
            <font color="#0F22B0" size="4" style="font-family: arial;">Better Recipes Newsletters</font>
          <?php }
        ?>
      </td>
    </tr>

    <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" name='form1'>
      <input type="hidden" name="PHPSESSID" value="<?php echo session_id(); ?>">
      <tr>
        <td colspan="2">
          <?php echo $main_newsletter_listing; ?>
        </td>
      </tr>

      <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>
    <!---  END OF Newsletters/Offers -->

      <!--  START OF MORE Newsletters/Offers -->
      <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>
      <tr>
        <td colspan="2">
          <?php
            if ($host == 'r4l') { ?>
              <font color="#0F22B0" size="4" style="font-family: arial;">More Newsletters</font>
            <?php }
            if ($host == 'fitfab') { ?>
              <font color="#EC519D" size="4" style="font-family: arial;">More Newsletters</font>
            <?php }
            if ($host == 'wim') { ?>
              <font color="#2789BD" size="4" style="font-family: arial;">More Newsletters</font>
            <?php }
            if ($host == 'sf') { ?>
              <font color="#F99D1C" size="4" style="font-family: arial;">More Newsletters</font>
            <?php }
            if ($host == 'br') { ?>
              <font color="#0F22B0" size="4" style="font-family: arial;">More Newsletters</font>
            <?php }
          ?>
        </td>
      </tr>
      <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>
      <tr>
        <td colspan="2">
          Check out the following newsletters from our sister sites!
        </td>
      </tr>
      <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>
      <tr>
        <td colspan="2">
          <?php echo $more_newsletter_listing; ?>
        </td>
      </tr>
      <!---  END OF MORE Newsletters/Offers -->

      <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>

      <tr><td colspan="2" align="center"><input type="submit" name="submit" value="Update Information"></td></tr>
    </form>


    <!---  START OF UPDATE INFORMATION -->
    <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>
    <tr>
      <td colspan="2">
        <?php
          if ($host == 'r4l') { ?>
            <font color="#0F22B0" size="4" style="font-family: arial;">Personalize Your Subscription</font>
          <?php }
          if ($host == 'fitfab') { ?>
            <font color="#EC519D" size="4" style="font-family: arial;">Personalize Your Subscription</font>
          <?php }
          if ($host == 'wim') { ?>
            <font color="#2789BD" size="4" style="font-family: arial;">Personalize Your Subscription</font>
          <?php }
          if ($host == 'sf') { ?>
            <font color="#F99D1C" size="4" style="font-family: arial;">Personalize Your Subscription</font>
          <?php }
          if ($host == 'br') { ?>
            <font color="#0F22B0" size="4" style="font-family: arial;">Personalize Your Subscription</font>
          <?php }
        ?>
      </td>
    </tr>
    <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>

    <tr>
      <td colspan="2">
        <u>Optional</u>: Help us select special offers and other mailings for you by updating your information.
        Your information will <b>not</b> be shared with any third party.
        <br><br>
        <a href="http://<?php echo trim($_SERVER['HTTP_HOST']); ?>/subctr/update_info.php?PHPSESSID=<?php echo session_id(); ?>" onclick="window.parent.scrollTo(0,0);">Click here to update your information</a>.
      </td>
    </tr>
    <!---  END OF UPDATE INFORMATION -->

    <!---  START OF Subscription History -->
    <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>
    <tr>
      <td colspan="2">
        <?php
        if ($host == 'r4l') { ?>
          <font color="#0F22B0" size="4" style="font-family: arial;">Subscription History</font>
        <?php }
        if ($host == 'fitfab') { ?>
          <font color="#EC519D" size="4" style="font-family: arial;">Subscription History</font>
        <?php }
        if ($host == 'wim') { ?>
          <font color="#2789BD" size="4" style="font-family: arial;">Subscription History</font>
        <?php }
        if ($host == 'sf') { ?>
          <font color="#F99D1C" size="4" style="font-family: arial;">Subscription History</font>
        <?php }
        if ($host == 'br') { ?>
          <font color="#0F22B0" size="4" style="font-family: arial;">Subscription History</font>
        <?php } ?>
      </td>
    </tr>

    <tr><td colspan="2" style='line-height:30%'>&nbsp;</td></tr>

    <tr>
      <td colspan="2">
        <?php if ($first_time == false) { ?>
          <?php echo trim($_SESSION['email']); ?> signed up on <?php echo $first_signup_date; if ($first_signup_ipaddr !='') { echo ' from '.$first_signup_ipaddr; } ?>.<br><br>
        <?php } else { ?>
          You have not yet signed up for any newsletter.<br><br>
        <?php } ?>
        If you have any questions, please
        <?php
        if ($host == 'r4l') { ?>
          <a href="http://www.recipe4living.com/contact/" target="_blank">contact us here</a>.
        <?php }
        if ($host == 'fitfab') { ?>
          <a href="http://www.fitandfabliving.com/index.php/contact-us.html" target="_blank">contact us here</a>.
        <?php }
        if ($host == 'wim') { ?>
          <a href="http://www.workitmom.com/contact/" target="_blank">contact us here</a>.
        <?php }
        if ($host == 'sf') { ?>
          <a href="http://www.savvyfork.com/component/contactenhanced/contact/31.html" target="_blank">contact us here</a>.
        <?php }
        if ($host == 'br') { ?>
          <a href="http://www.recipe4living.com/contact/" target="_blank">contact us here</a>.
        <?php } ?>
      </td>
    </tr>
    <!---  END OF Subscription History -->

  </table>

  <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '<?php echo $google_analytics; ?>']);
    _gaq.push(['_trackPageview']);
    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
  </script>

</body>
</html>
