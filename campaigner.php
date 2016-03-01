<?php

include_once("subctr/config.php");




$email_array = array('wgrant@gmail.com','williamg@junemedia.com','grant@footballguys.com','dkamys@live.com',
				'dankamys@gmail.com','dkamys@comcast.net','andis@junemedia.com','andisummers89@gmail.com','samir@dipusamir.com','samirp@junemedia.com,leonz@junemedia.com');


foreach ($email_array as $email) {
	$data_array = array('email' => $email, 'first' => '', 'last' => '',
						'phone' => '3', 'fax' => '', 'status' => 'Subscribed', 'format' => 'Both',
						'ipaddr' => $_SERVER['REMOTE_ADDR'], 'signup_date' => date('Y-m-d'), 'age_group' => '30-40',
						'oldlistid' => '583', 'subcampid' => '9999', 'source' => 'TestSavvyFork',
						'subsource' => '', 'address1' => '', 'address2' => '',
						'city' => '', 'state' => 'IL', 'zipcode' => '00000',
						'country' => 'US', 'gender' => '', 'birth_date' => '', 'contactId' => 0, 
						'sub_array' => array(3844768), 'unsub_array' => array());
	
	
	$result = sendToCampaigner($data_array);
	var_dump($result);
	
	$result_code = trim(getXmlValueByTag($result,'ResultCode'));
	$contactId = trim(getXmlValueByTag($result,'ContactId'));
	$email = trim(getXmlValueByTag($result,'ContactUniqueIdentifier'));
	
	
	// Record ID and email only if it's success
	if ($email !='' && ctype_digit($contactId) && $contactId !='') {
		$campaignerContacts = "INSERT IGNORE INTO campaignerContacts (id, email) VALUES (\"$contactId\",\"$email\")";
		$campaignerContacts_result = mysql_query($campaignerContacts);
		echo mysql_error();
	}
}



/*

[Contact.oldlistid]
[Contact.Email]
[Contact.oldlistid]


*/



/*
$data_array = array('email' => 'samirp@junemedia.com,leonz@junemedia.com', 'first' => 'Samir', 'last' => 'Patel',
					'phone' => '312-724-9440', 'fax' => '847-205-9340', 'status' => 'Subscribed', 'format' => 'Both',
					'ipaddr' => '10.1.1.1', 'signup_date' => '2014-03-28', 'age_group' => '30-40',
					'oldlistid' => '393', 'subcampid' => '9999', 'source' => 'TestXXX',
					'subsource' => 'TestYYY', 'address1' => '209 Any St', 'address2' => 'Any Floor',
					'city' => 'Any City', 'state' => 'IL', 'zipcode' => '00000',
					'country' => 'US', 'gender' => 'M', 'birth_date' => '1970-01-01', 'contactId' => '', 
					'sub_array' => array(), 'unsub_array' => array(3844883,3844813,3844803));


$result = sendToCampaigner($data_array);
var_dump($result);

$result_code = trim(getXmlValueByTag($result,'ResultCode'));
$contactId = trim(getXmlValueByTag($result,'ContactId'));
$email = trim(getXmlValueByTag($result,'ContactUniqueIdentifier'));


// Record ID and email only if it's success
if ($email !='' && ctype_digit($contactId) && $contactId !='') {
	$campaignerContacts = "INSERT IGNORE INTO campaignerContacts (id, email) VALUES (\"$contactId\",\"$email\")";
	$campaignerContacts_result = mysql_query($campaignerContacts);
	echo mysql_error();
}
*/
/*
IsDailyInsider	3844903
IsFitFabLivingSOLO	3844893
IsDailyRecipes	3844883
IsRecipe4LivingSOLO	3844873
IsBudgetCooking	3844863
IsQuickEasyRecipes	3844853
IsDietInsider	3844843
IsCrockpotCreations	3844833
IsCasseroleCooking	3844823
IsCopycatClassics	3844813
IsMakingItWork	3844803
IsWorkItMomSOLO	3844793
IsDiabeticFriendlyDishes	3844783
IsTheFeedBySavvyFork	3844768

*/

?>
