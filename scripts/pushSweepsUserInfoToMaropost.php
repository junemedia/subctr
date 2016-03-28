<?php

require_once '/var/www/html/subctr.popularliving.com/subctr/config.local.php';
require_once '/var/www/html/subctr.popularliving.com/subctr/functions.php';
require_once '/var/www/html/subctr.popularliving.com/subctr/maropostFunctions.php';

mysql_connect (DB_HOST, DB_USERNAME, DB_PASSWORD);
mysql_select_db (DB_NAME);

/*
 * get all signups in last 24 hours
 */

$endTime = time();
$startTime = $endTime - (24 * 60 * 60);
$link = "http://win.betterrecipes.com/api/syncUser/$startTime/$endTime";

date_default_timezone_set('UTC');
$output = date('D M j G:i:s T Y') . "\n";

$output .= "-->Start to download from $link ... ";

$receive = file_get_contents($link);
$items = json_decode($receive);
$output .= "Done\n";

$totalRows = count($items);
$output .= "-->total [$totalRows] \n\n";

$total = 0;
$successTotal = 0;
$failedTotal = 0;
$failedEmails = '';


if (!empty($items)) {
  foreach ($items as $item) {
    $email = $item->email;

    // get contact data from Maropost if it exists
    list( $contact, $mp_sorted_subs ) = getContact($email);

    $output .= "--------------------\n";
    $output .= "-->Processing $email ...\n";

    $subcampid = '';
    $save_subcampid = '';
    $signup_date = '';
    $ipaddr = long2ip($item->ip);
    $old_listid = '';
    $new_listid = '';
    $subsource = '';
    $type = 'sub';
    $fromSite = '';
    $firstName = $item->firstname;
    $lastName = $item->lastname;
    $city = $item->city;
    $zipcode = $item->zip;
    $state = $item->state;
    $signup_date = $item->date_registered;
    $alreadyExist = false;

    $sub_array = array();
    $unsub_array = array();

    // site_id 1: Better Recipes
    if ($item->site_id == 1) {
      $fromSite = 'BR';
      $subcampid = 4377; //Sweeps Registration BR 0615
      $source = 'SweepsRegistrationBR0715';
      $save_subcampid = $subcampid;
      $old_listid = 506; //506:old list id for br sweeps

      // For logs only
      $parts = array(504, 505, 506);
      // BetterRecipes Sweeps, BR Daily & BR Special Offers
      $sub_array = array(41154, 41136, 41140);
    }

    // site_id 2: Recipe4Living
    if ($item->site_id == 2) {
      $fromSite = 'R4L';
      $subcampid = 4378 ; //Sweeps Registration R4L 0615
      $source = 'SweepsRegistrationR4L0715';
      $save_subcampid = $subcampid;
      $old_listid= 507;   //507:old list id for r4l sweeps
      //$source = getSubcampIdDescriptiveName($subcampid);

      // For logs only
      $parts = array(393, 396, 507);
      // Recipe4Living Sweeps, R4L Daily, R4L Special Offers
      $sub_array = array(41141, 41157, 41158);
    }
    $output .= " $fromSite ...\n";

    // Log Start --------------------------------------
    // Alright, just like what we did before, save them all in the logs
    foreach ($parts as $list_parts) {
      $insert_query = "INSERT IGNORE INTO joinEmailSub (
                         dateTime,
                         email,
                         ipaddr,
                         listid,
                         subcampid,
                         source
                       )
                       VALUES (
                         NOW(),
                         '$email',
                         '$ipaddr',
                         '$list_parts',
                         '$subcampid',
                         '$source'
                       )";

      $insert_query_result = mysql_query($insert_query);
      echo mysql_error() . "\n";

      $insert_query = "INSERT IGNORE INTO joinEmailActive (
                         dateTime,
                         email,
                         ipaddr,
                         listid,
                         subcampid,
                         source
                       )
                       VALUES (
                         NOW(),
                         '$email',
                         '$ipaddr',
                         '$list_parts',
                         '$subcampid',
                         '$source'
                       )";
      $insert_query_result = mysql_query($insert_query);
      echo mysql_error() . "\n";


      /*
       * Send sub info to Maropost
       */

      // get Maropost list id
      $mapped_id = $maropostMap[$list_parts]['id'];

      if (!isset($mp_sorted_subs['subscribed'][$mapped_id])) {
        // if contact doesn't already exist, add them to maropost
        if ($contact['id'] == 0) {
          $output .= "    ** new contact **\n";
          // add the contact and update our contact data
          list( $contact, $mp_sorted_subs ) = addContact($email);
        }

        $output .= "    subscribe to $mapped_id\n";
        $subscribeCallResult = contactSubscribe($contact, $mapped_id);
      }
      else {
        $output .= "    already subscribed to $mapped_id\n";
      } 

      // clear $mapped_id
      $mapped_id = 0;
    }

    // Log End ---------------------------------






    //Check if the email is already in campaigner
    $query = "SELECT l.3818568 as email, l.3834288 as subcampid
              FROM LeonCampaignContactJoin as l
              WHERE l.3818568 = '".$email."'
              LIMIT 1";
    $result2 = mysql_query($query);
    echo mysql_error() . "\n";

    $row = mysql_fetch_object($result2);
    if (!empty($row)) {
      $alreadyExist = true;
      if (!empty($row->subcampid)) {
        $subcampid = ''; //don't override the exist subcampid
      }
    }

    $data_array = array(
      'email' => $email,
      'first' => $firstName,
      'last' => $lastName,
      'phone' => '',
      'fax' => '',
      'status' => 'Subscribed',
      'format' => 'Both',
      'ipaddr' => $ipaddr,
      'signup_date' => $signup_date,
      'age_group' => '',
      'oldlistid' => '',
      'subcampid' => $subcampid,
      'source' => $source,
      'subsource' => $subsource,
      'address1' => '',
      'address2' => '',
      'city' => $city,
      'state' => $state,
      'zipcode' => $zipcode,
      'country' => 'US',
      'gender' => '',
      'birth_date' => '',
      'contactId' => 0,
      'sub_array' => $sub_array,
      'unsub_array' => $unsub_array,
      'alreadyExist' => $alreadyExist
    );

    $send_result = json_encode(@$subscribeCallResult);
    //$result_code = trim(getXmlValueByTag($send_result,'ResultCode'));
    $result_code = 'success';
    $send_result = addslashes($send_result);
    if (strtolower($result_code) != 'success') {
      $failedEmails[] = $email."(".$result_code.")\r\n";
      $failedTotal++;
    }
    else {
      $successTotal++;
    }

    // insert into sweeps_log
    $sweeps_log = "INSERT IGNORE INTO sweeps_user_boolean_log (
                     dateTime,
                     email,
                     ipaddr,
                     oldListId,
                     newListId,
                     subcampid,
                     source,
                     status,
                     fromSite,
                     reponse,
                     link
                   )
                   VALUES (
                     NOW(),
                     '$email',
                     '$ipaddr',
                     '$old_listid',
                     '" . implode(',', $parts) . "',
                     '$save_subcampid',
                     '$source',
                     '$result_code',
                     '$fromSite',
                     '$send_result',
                     '$link'
                   )";
    $sweeps_log_result = mysql_query($sweeps_log);

    echo mysql_error() . "\n";
    echo "$result_code\n";
  }
}

$total = $successTotal + $failedTotal;

// Send out results mail
date_default_timezone_set('UTC');
$email = "johns@junemedia.com";

// Send the mail notification
$to      = $email . ',johns@junemedia.com';
$subject = 'Daily Report - Push Sweeps Register Users Into Campaigner';
$failedMsg = $failedTotal > 0 ? "Failed Emails:\r\n".implode(",", $failedEmails)."\r\n" : "";

$message = "Done! Total Upload [$total] emails.\r\n" .
           "Successed: $successTotal emails.\r\n" .
           "Failed: $failedTotal emails.\r\n".$failedMsg;

$headers = 'From: Pushing Sweeps <johns@junemedia.com>' . "\r\n" .
           'Reply-To: Pushing Sweeps <johns@junemedia.com>' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

tryMail($to, $subject, $message, $headers);

$output .= "\n\n************************************************************************\n\n";

$logfile = __DIR__ . '/logs/pushSweepsUserInfoToMaropost-' . date('Ym') . '.log';
file_put_contents($logfile, $output, FILE_APPEND);
