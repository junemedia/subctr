<?php

/*
 * Grabs all the unprocessed sub/unsubs from the database and
 * pushes the data to Maropost via their API
 *
 * No longer necessary -- eventually, hopefully
 *
 */

$now = time();

date_default_timezone_set('UTC');
$output = date('D M j G:i:s T Y') . "\n";
$output .= "Start...\n\n";

require_once '/var/www/html/subctr.popularliving.com/subctr/config.local.php';
include_once '/var/www/html/subctr.popularliving.com/subctr/functions.php';
include_once '/var/www/html/subctr.popularliving.com/subctr/maropostFunctions.php';

mysql_connect (DB_HOST, DB_USERNAME, DB_PASSWORD);
mysql_select_db (DB_NAME);

// get all the unprocessed entries in `campaigner`
$sql = 'SELECT *
        FROM `campaigner`
        WHERE `isProcessed` = "N"
        ORDER BY `id` DESC LIMIT 20';
$queue = mysql_query($sql);
echo mysql_error();

$contacts = array();

while ($row = mysql_fetch_object($queue)) {
  $email = $row->email;
  $type = $row->type;
  $oldListId = $row->oldListId;
  $listid = $maropostMap[$row->oldListId]['id'];
  $rowid = $row->id;

  if (!isset($contacts[$email])) {
    $contacts[$email] = array();
  }
  $current = &$contacts[$email];


  // if there's already a more recent action in this batch, skip this
  if (!isset($current[$listid])) {

    /* check to see if there's an already processed action for this
     * contact/list that is more recent than this request, don't want
     * to overwrite more recent actions
     */
    $sql = "SELECT `id`
            FROM `campaigner`
            WHERE `email` = '$email'
              AND `oldListId` = $oldListId
              AND `isProcessed` = 'Y'
              AND `id` > $rowid
            ORDER BY `dateTime` DESC
            LIMIT 1";
    $result = mysql_query($sql);
    echo mysql_error();


    while ($foo = mysql_fetch_array($result)) { $fooid = $foo['id']; }

    $newerAction = (mysql_num_rows($result) !== 0);

    // if nothing newer, add it to the list for syncing
    if (!$newerAction) {
      $current[$listid] = $type;
    }
    // else put it in list but mark as skip, this prevents us from
    // making multiple db calls for the same contact/list
    else {
      $output .= " ** skip $rowid ($fooid)\n";
      $current[$listid] = 'skip';
    }
  }

  $output .= "mark $rowid as processed\n";
  $update = "UPDATE `campaigner`
             SET `isProcessed` = 'Y'
             WHERE `id` = '$rowid'";
  $update_result = mysql_query($update);
  echo mysql_error();
}

$output .= print_r($contacts, true);


foreach ($contacts as $email => $actions) {
  list($contact, $sortedSubs) = getContact($email);
  $output .= "$email\n";

  if ($contact['id'] == 0) {
    $output .= "  **create new contact\n";
    list($contact, $sortedSubs) = addContact($email);
  }

  foreach ($actions as $id => $action) {
    switch ($action) {
      case 'sub':
        $output .= "    subscribe to $id\n";
        contactSubscribe($contact, $id);
        break;
      case 'unsub':
        $output .= "    unsub from $id\n";
        contactUnsubscribe($contact, $id);
        break;
      default:
        $output .= "    skip $id\n";
    }
  }
}

$output .= "\n\n...done!";
$output .= "\n\n************************************************************************\n\n";


$logfile = __DIR__ . '/logs/sync_maropost-' . date('Ymd') . '.log';
file_put_contents($logfile, $output, FILE_APPEND);
