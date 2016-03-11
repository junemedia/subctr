<?php

define('MP_API_KEY', 'c300eeefb54ee6e746260585befa15a10a947a86');
define('MP_ACCOUNT_ID', 694);

function sortSubscriptions($list_subscriptions) {
  $sorted = array(
    'subscribed' => array(),
    'unsubscribed' => array()
  );

  foreach ($list_subscriptions as $list) {
    $status = strtolower($list['status']);
    $sorted[$status][$list['list_id']] = $list['name'];
  }
  return $sorted;
}

function isSubscribed($list_id, $signup_array, $sorted_subs) {
  global $maropostMap;

  // this is all the old system does
  // check if this id is in the array of user's current subs
  // if so, we can just return true
  if (in_array($list_id, $signup_array)) {
    return true;
  }

  // now, we're going to check if maropost has contact subscribed to
  // this list

  // check if there's a mapping for this id and if so check if
  // mapped id is in $sorted_subs array
  if (isset($maropostMap[$list_id])) {
    $mapped_id = $maropostMap[$list_id]['id'];
    return isset($sorted_subs['subscribed'][$mapped_id]);
  }

  // if we've gotten here, and we shouldn't, contact is not subscribed
  // in either our system or Maropost's
  return false;
}

function contactSubscribe($contact, $list_id, $subscribed = true) {
  $api_key = MP_API_KEY;
  $api_root = 'http://api.maropost.com/accounts/' . MP_ACCOUNT_ID;
  $api_headers = array(
    'Accept: application/json',
    'Content-Type: application/json'
  );

  $api_endpoint = "lists/$list_id/contacts/{$contact['id']}.json";

  $payload = array(
    'contact' => array(
      'subscribed' => $subscribed
    )
  );
  $json = json_encode($payload);

  $ch = curl_init("$api_root/$api_endpoint?auth_token=$api_key");
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $api_headers);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
  $response = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);

  // decode as an associative array
  $response = json_decode($response, true);

  // should add some better checking here
  if (json_last_error() === JSON_ERROR_NONE) {
    return $response;
  }
  else { }

}

function contactUnsubscribe($contact, $list_id) {
  return contactSubscribe($contact, $list_id, false);
}




function getAllLists() {
  $api_key = MP_API_KEY;
  $api_root = 'http://api.maropost.com/accounts/' . MP_ACCOUNT_ID;
  $api_headers = array(
    'Accept: application/json',
    'Content-Type: application/json'
  );

  $api_endpoint = "lists.json?no_counts=true";

  $ch = curl_init("$api_root/$api_endpoint&auth_token=$api_key");
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $api_headers);
  $response = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);

  // decode as an associative array
  return json_decode($response, true);
}

/*
 * return object if contact exists, else false
 */
function getContact($contactEmail) {
  $api_key = MP_API_KEY;
  $api_root = 'http://api.maropost.com/accounts/' . MP_ACCOUNT_ID;
  $api_headers = array(
    'Accept: application/json',
    'Content-Type: application/json'
  );

  $api_endpoint = "contacts/email.json?contact[email]=$contactEmail";

  $ch = curl_init("$api_root/$api_endpoint&auth_token=$api_key");
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $api_headers);
  $response = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);

  // decode as an associative array
  $contact = json_decode($response, true);

  // I think we could just check for a http status code of 200 here,
  // but for now check that it's valid JSON and has a non-empty id attribute
  if (json_last_error() === JSON_ERROR_NONE && isset($contact['id']) && $contact['id'] != '') {
    return $contact;
  }
  else {
    $contact = array(
      'id' => 0,
      'email' => $contactEmail,
      'list_subscriptions' => array()
    );
    return $contact;
  }
}
function getContactSubscriptions($contactEmail) {
  // getting an associative array here
  $contactData = getContactData($contactEmail);
  return $contactData['list_subscriptions'];
}


