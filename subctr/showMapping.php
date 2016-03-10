<?php

ini_set('display_errors', true);

include_once("config.php");
include_once('maropostMap.php');

$sql = "SELECT `title`, `listid`, `isActive`
        FROM `joinLists`
        ORDER BY `isActive`";
$dbLists = mysql_query($sql);
echo mysql_error();

$output = '';
while ($row = mysql_fetch_object($dbLists)) {
  $title = $row->title;
  $listid = $row->listid;
  $active = $row->isActive;

  $output .= "[$active] $title => ";

  if (isset($maropostMap[$listid])) {
    $output .= $maropostMap[$listid]['name'];
  }
  $output .= '<br>';
}

echo $output;
