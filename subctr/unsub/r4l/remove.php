<?php

include_once("../../config.php");

$user_ip = trim($_SERVER['REMOTE_ADDR']);
$pixel = '';
$message = '';

$email  = isset ($_REQUEST['email'])  ? trim($_REQUEST['email'])  : '';
$listid = isset ($_REQUEST['lid']) ? trim($_REQUEST['lid']) : '';
$jobid  = isset ($_REQUEST['jid'])  ? trim($_REQUEST['jid'])  : '0';
if (!ctype_digit($jobid)) { $jobid = 0; }

/*
 * initial page load, display the form
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (isset($maropostMap[$listid])) {
    $pixel = "<img src='http://www.recipe4living.com/unsub/".$listid."_Form.html' width='0' height='0' border='0'>";
    $newsletter_name = $maropostMap[$listid]['name'];
    include 'template/form.html';
  }
  else {
    echo "Invalid unsubscribe link: <a href='http://r4l.popularliving.com/subctr/index.php'>Click here to go to Subscription Center to unsubscribe</a>";
  }
}


/*
 * handle form submission
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // if they decide not to unsubscribe
  if (isset ($_POST['action']) && $_POST['action'] === 'stay') {
    $pixel = "<img src='http://www.recipe4living.com/unsub/".$listid."_Stay.html' width='0' height='0' border='0'>";
    include  'template/stay.html';
  }

  // otherwise unsubscribe them
  else {
    $pixel = "<img src='http://www.recipe4living.com/unsub/".$listid."_UnsubConf.html' width='0' height='0' border='0'>";

    /*************************** Maropost ****************************/
    // get the Maropost data for the contact
    list( $contact, $mp_sorted_subs ) = getContact($email);
    if (isset($maropostMap[$listid])) {
      $mapped_id = $maropostMap[$listid]['id'];

      if (isset($mp_sorted_subs['subscribed'][$mapped_id])) {
        $response = contactUnsubscribe($contact, $mapped_id);
      }
    }
    /************************* end Maropost **************************/

    include 'template/confirm.html';
  }
}
