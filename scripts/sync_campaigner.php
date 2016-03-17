<?php

/*
 * Originally run from /home/spatel/scripts/
 *
 * Pre-Maropost, this script ran every five minutes
 * It grabs all the unprocessed sub/unsubs from the database and
 * pushes the data to Campaigner via their API
 *
 * No longer necessary
 *
 */


mysql_pconnect ('host', 'username', 'password');
mysql_select_db ('arcamax');

include_once("/var/www/html/subctr.popularliving.com/subctr/functions.php");

$query = "SELECT * FROM campaigner WHERE isProcessed = 'N' ORDER BY dateTime ASC";
$result = mysql_query($query);
echo mysql_error();

while ($row = mysql_fetch_object($result)) {
  $id = $row->id;
  $email = $row->email;
  $subcampid = $row->subcampid;
  $signup_date = $row->dateTime;
  $ipaddr = $row->ipaddr;
  $new_listid = $row->newListId;
  $source = getSubcampIdDescriptiveName($subcampid);
  $subsource = $row->source;
  $type = $row->type;

  $sub_array = array();
  $unsub_array = array();

  if ($type == 'sub') {
    $sub_array = array($new_listid);
  }
  else {
    $unsub_array = array($new_listid);
  }

  $data_array = array(
    'email' => $email,
    'first' => '',
    'last' => '',
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
    'city' => '',
    'state' => '',
    'zipcode' => '',
    'country' => 'US',
    'gender' => '',
    'birth_date' => '',
    'contactId' => 0,
    'sub_array' => $sub_array,
    'unsub_array' => $unsub_array
  );
  $send_result = sendToCampaigner($data_array);

  $result_code = trim(getXmlValueByTag($send_result, 'ResultCode'));
  $contactId = trim(getXmlValueByTag($send_result, 'ContactId'));
  $email = trim(getXmlValueByTag($send_result, 'ContactUniqueIdentifier'));

  // Record ID and email only if it's success
  if ($email != '' && ctype_digit($contactId) && $contactId != '') {
    $campaignerContacts = "INSERT IGNORE INTO campaignerContacts (id, email)
                           VALUES (\"$contactId\",\"$email\")";
    $campaignerContacts_result = mysql_query($campaignerContacts);
    echo mysql_error();
  }

  $send_result = addslashes($send_result);

  $update = "UPDATE campaigner
             SET isProcessed='Y',
                 response = \"$send_result\"
             WHERE id='$id'";
  $update_result = mysql_query($update);
  echo mysql_error();

  // Process the OpenX ads unit
  // Add the openx ad sequence
  $openx_ads_sequence = (int)($contactId);   //Open X Unique Sequence
  $openx_ads_tag_1 = (int)($openx_ads_sequence . "1");  // Open X Ad Tag 1
  $openx_ads_tag_2 = (int)($openx_ads_sequence . "2");  // Open X Ad Tag 2
  $openx_ads_tag_3 = (int)($openx_ads_sequence . "3");  // Open X Ad Tag 3
  $openx_ads_tag_4 = (int)($openx_ads_sequence . "4");  // Open X Ad Tag 4
  $openx_ads_tag_5 = (int)($openx_ads_sequence . "5");  // Open X Ad Tag 5


  $data_array = array(
    'ContactId'=> $contactId,
    'email' =>$email,
    'first' => "",
    'last' => "",
    'phone' =>'',
    'fax'=>'',
    'status' => 'Subscribed',
    'format' => 'Both',
    'openx_ads_sequence' => $openx_ads_sequence,
    'openx_ads_tag_1' => $openx_ads_tag_1,
    'openx_ads_tag_2' => $openx_ads_tag_2,
    'openx_ads_tag_3' => $openx_ads_tag_3,
    'openx_ads_tag_4' => $openx_ads_tag_4,
    'openx_ads_tag_5' => $openx_ads_tag_5
  );
  $send_result = updateCampaignerOpenX($data_array);
}

?>
