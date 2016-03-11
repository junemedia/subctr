<?php

include_once("JSON.php");

function CreateUpdateCampaign ($data_array) {
    $opts = array('ssl' => array('ciphers'=>'DHE-RSA-AES256-SHA:DHE-DSS-AES256-SHA:AES256-SHA:KRB5-DES-CBC3-MD5:KRB5-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:EDH-DSS-DES-CBC3-SHA:DES-CBC3-SHA:DES-CBC3-MD5:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA:AES128-SHA:RC2-CBC-MD5:KRB5-RC4-MD5:KRB5-RC4-SHA:RC4-SHA:RC4-MD5:RC4-MD5:KRB5-DES-CBC-MD5:KRB5-DES-CBC-SHA:EDH-RSA-DES-CBC-SHA:EDH-DSS-DES-CBC-SHA:DES-CBC-SHA:DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5:EXP-KRB5-DES-CBC-MD5:EXP-KRB5-RC2-CBC-SHA:EXP-KRB5-DES-CBC-SHA:EXP-EDH-RSA-DES-CBC-SHA:EXP-EDH-DSS-DES-CBC-SHA:EXP-DES-CBC-SHA:EXP-RC2-CBC-MD5:EXP-RC2-CBC-MD5:EXP-KRB5-RC4-MD5:EXP-KRB5-RC4-SHA:EXP-RC4-MD5:EXP-RC4-MD5'));
	$client = new SoapClient('https://ws.campaigner.com/2013/01/campaignmanagement.asmx?WSDL',  array("encoding"=>"ISO-8859-1",'soap_version'=> 'SOAP_1_1',
							   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
							   'stream_context' => stream_context_create($opts),
							   'trace' => 1,'exceptions' => 0,'connection_timeout' => 300)); 

	foreach ($data_array as $key => $value) { $$key = $value; }

	if ($campaign_id == 0) { $campaign_id = NULL; }
	
	$response = $client->CreateUpdateCampaign(Array(
	    'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
	    'campaignData' => Array(
	        'Id' => $campaign_id,
			'CampaignName' => $campaign_name,
			'CampaignSubject' => $subject_line,
			'CampaignFormat' => 'HTML',
			'CampaignStatus' => 'Complete',
			'CampaignType' => 'None',
			'HtmlContent' => $html_code,
			'FromName'=> $from_name,
			'FromEmailId' => $from_email_id,
			'ReplyEmailId' => $reply_email_id,
			'TrackReplies' => false,
			'AutoReplyMessageId'=> '0',
			'ProjectId'=>0,
			'IsWelcomeCampaign'=>false,
			'DateModified'=>date(DATE_ATOM),
			'Encoding'=>'UTF_8'
			)
	));
        $r = $client->__getLastResponse();
       
$tt = Array(
            'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
            'campaignData' => Array(
                'Id' => $campaign_id,
                        'CampaignName' => $campaign_name,
                        'CampaignSubject' => $subject_line,
                        'CampaignFormat' => 'HTML',
                        'Status' => 'Complete',
                        'CampaignType' => 'None',
                        'HtmlContent' => $html_code,
                        'FromName'=> $from_name,
                        'FromEmailId' => $from_email_id,
                        'ReplyEmailId' => $reply_email_id,
                        'TrackReplies' => false,
                        'AutoReplyMessageId'=> '0',
                        'ProjectId'=>0,
                        'IsWelcomeCampaign'=>false,
                        'DateModified'=>date(DATE_ATOM),
                        'Encoding'=>'UTF_8'
                        )
        );
//print_r($tt);
 
        $headers = 'From: leonz@junemedia.com' . "\r\n" . 'Reply-To: leonz@junemedia.com';            
        // Sent the error notification to
        $mailList = 'leonz@junemedia.com,williamg@junemedia.com';
        
        if (is_soap_fault($response)) {
            $erro = "SOAP Fault: (faultcode: {$response->faultcode}, faultstring: {$response->faultstring})";
            @mail($mailList, 'Push To Campaigner Error - Campaigner Server Issue', $erro, $headers);
        }
        
        
	
    
    // Catch the errors
    $errorFlag = "<ErrorFlag>true</ErrorFlag>";
    if(strpos(strtolower($r), strtolower($errorFlag)) !== false){
        // We found the error
        @mail($mailList, 'Push To Campaigner Error', $r, $headers);
    }
    
    return $r;
}




function ListMediaFilesCampaigner() {
	$client = new SoapClient('https://ws.campaigner.com/2013/01/contentmanagement.asmx?WSDL',  array('exceptions' => false,
						   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
	$response = $client->ListMediaFiles(Array(
	    'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
	    ));
	return $client->__getLastResponse();
}



function UploadMediaFileCampaigner($image) {
	if (strlen(basename($image)) >= 45) {
		$image_file_name = substr(md5(uniqid(rand(), true)),0,10).substr(basename($image),-40);
	} else {
		$image_file_name = substr(md5(uniqid(rand(), true)),0,5).basename($image);
	}
	
	
	$image_file_name = str_replace(' ', '_', $image_file_name);

	$client = new SoapClient('https://ws.campaigner.com/2013/01/contentmanagement.asmx?WSDL',  array('exceptions' => false,
						   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
	
	$response = $client->UploadMediaFile(Array(
	    'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
	    'fileName' => basename($image_file_name),
	    'fileContentBase64' => base64_encode(file_get_contents($image)),
	    ));
	return $client->__getLastResponse();
}

function getLocationByIp($ipaddr)
{
	$result = array();
	if(!empty($ipaddr))
	{
		$url = "http://freegeoip.net/json/$ipaddr";
		$content = file_get_contents($url);
		$ipInfo = json_decode($content, true);
		if(isset($ipInfo['region_name']))
		{
			$result['region'] = $ipInfo['region_name'];
		}
		if(isset($ipInfo['zipcode']))
		{
			$result['zipcode'] = $ipInfo['zipcode'];
		}
	}
	
	return $result;
}

function sendToCampaigner ($data_array) {
	$client = new SoapClient('https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL',  array('exceptions' => false,
						   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
	
	$email = $data_array['email'];			$first = $data_array['first'];			$last = $data_array['last'];
	$phone = $data_array['phone'];			$fax = $data_array['fax'];
	$status = $data_array['status'];	// Subscribed, Unsubscribed, HardBounce, SoftBounce, Pending
	$format = $data_array['format'];	// Text, HTML, Both
	if ($format == '') { $format = 'Both'; }
	
	$ipaddr = $data_array['ipaddr'];
	if ($ipaddr == '') { $ipaddr = trim($_SERVER['REMOTE_ADDR']); }
	$signup_date = $data_array['signup_date'];	// yyyy-mm-dd
	if ($signup_date == '') { $signup_date = date('Y-m-d'); }
	
	$age_group = $data_array['age_group'];		$oldlistid = $data_array['oldlistid'];		$subcampid = $data_array['subcampid'];
	$source = $data_array['source'];			$subsource = $data_array['subsource'];		$address1 = $data_array['address1'];
	$address2 = $data_array['address2'];		$city = $data_array['city'];				$state = $data_array['state'];
	$zipcode = $data_array['zipcode'];			$country = $data_array['country'];	// country code US
	$gender = $data_array['gender'];	// M or F
	$birth_date = $data_array['birth_date'];	// yyyy-mm-dd
      
    
	
	if(empty($state) || empty($zipcode))
	{	
                $ipDetail = getLocationByIp($ipaddr);
		if(!empty($ipDetail))
		{
			if(empty($state) && isset($ipDetail['region']))
			{
				$state = $ipDetail['region'];
			}
			
			if(empty($zipcode) && isset($ipDetail['zipcode']))
			{
				$zipcode = $ipDetail['zipcode'];
			}
		}			
	}
	
	$contactId = $data_array['contactId'];		if ($contactId == '') { $contactId = 0; }
	
	$sub_array = $data_array['sub_array'];		$unsub_array = $data_array['unsub_array'];
        
        
        
        $uploadArray = array(
                (($ipaddr !='') ? array("_" => $ipaddr, "Id" => 3834378) : array("_" => "", "Id" => 3834378)),	// ipaddr
                (($oldlistid !='') ? array("_" => $oldlistid, "Id" => 3834333) : array("_" => "", "Id" => 3834333)),	// oldlistid
                (($subcampid !='') ? array("_" => $subcampid, "Id" => 3834288) : array("_" => "", "Id" => 3834288)),	// subcampid
                (($signup_date !='') ? array("_" => $signup_date, "Id" => 3834363) : array("_" => "", "Id" => 3834363)),	//signup_datetime
                (($source !='') ? array("_" => $source, "Id" => 3834388) : array("_" => "", "Id" => 3834388)),	// source
                (($subsource !='') ? array("_" => $subsource, "Id" => 3834408) : array("_" => "", "Id" => 3834408)),	// subsource
                (($address1 !='') ? array("_" => $address1, "Id" => 3834418) : array("_" => "", "Id" => 3834418)),	// address1
                (($address2 !='') ? array("_" => $address2, "Id" => 3834428) : array("_" => "", "Id" => 3834428)),	// address2
                (($city !='') ? array("_" => $city, "Id" => 3834438) : array("_" => "", "Id" => 3834438)),	// city
                (($state !='') ? array("_" => $state, "Id" => 3834448) : array("_" => "", "Id" => 3834448)),	//state
                (($zipcode !='') ? array("_" => $zipcode, "Id" => 3833693) : array("_" => "", "Id" => 3833693)),	// zipcode
                (($country !='') ? array("_" => $country, "Id" => 3834458) : array("_" => "", "Id" => 3834458)),	// country
                (($gender !='') ? array("_" => $gender, "Id" => 3834468) : array("_" => "", "Id" => 3834468)),	// gender
                (($birth_date !='') ? array("_" => $birth_date, "Id" => 3834483) : array("_" => "", "Id" => 3834483)),	// birth_date
                (($age_group !='') ? array("_" => $age_group, "Id" => 3834493) : array("_" => "", "Id" => 3834493)),	// age_group


                // PROCESS SUBSCRIBERS
                (in_array(3844903, $sub_array) ? array("_" => "true", "Id" => 3844903) : array("_" => "", "Id" => 3844903)),	// IsDailyInsider
                (in_array(3844893, $sub_array) ? array("_" => "true", "Id" => 3844893) : array("_" => "", "Id" => 3844893)),	// IsFitFabLivingSOLO
                (in_array(3844883, $sub_array) ? array("_" => "true", "Id" => 3844883) : array("_" => "", "Id" => 3844883)),	// IsDailyRecipes
                (in_array(3844873, $sub_array) ? array("_" => "true", "Id" => 3844873) : array("_" => "", "Id" => 3844873)),	// IsRecipe4LivingSOLO
                (in_array(3844863, $sub_array) ? array("_" => "true", "Id" => 3844863) : array("_" => "", "Id" => 3844863)),	// IsBudgetCooking
                (in_array(3844853, $sub_array) ? array("_" => "true", "Id" => 3844853) : array("_" => "", "Id" => 3844853)),	// IsQuickEasyRecipes
                (in_array(3844843, $sub_array) ? array("_" => "true", "Id" => 3844843) : array("_" => "", "Id" => 3844843)),	// IsDietInsider
                (in_array(3844833, $sub_array) ? array("_" => "true", "Id" => 3844833) : array("_" => "", "Id" => 3844833)),	// IsCrockpotCreations
                (in_array(3844823, $sub_array) ? array("_" => "true", "Id" => 3844823) : array("_" => "", "Id" => 3844823)),	// IsCasseroleCooking
                (in_array(3844813, $sub_array) ? array("_" => "true", "Id" => 3844813) : array("_" => "", "Id" => 3844813)),	// IsCopycatClassics
                (in_array(3844803, $sub_array) ? array("_" => "true", "Id" => 3844803) : array("_" => "", "Id" => 3844803)),	// IsMakingItWork
                (in_array(3844793, $sub_array) ? array("_" => "true", "Id" => 3844793) : array("_" => "", "Id" => 3844793)),	// IsWorkItMomSOLO
                (in_array(3844783, $sub_array) ? array("_" => "true", "Id" => 3844783) : array("_" => "", "Id" => 3844783)),	// IsDiabeticFriendlyDishes
                (in_array(3844768, $sub_array) ? array("_" => "true", "Id" => 3844768) : array("_" => "", "Id" => 3844768)),	// IsTheFeedBySavvyFork
                (in_array(4195798, $sub_array) ? array("_" => "true", "Id" => 4195798) : array("_" => "", "Id" => 4195798)),    // IsEditorsChoice
                (in_array(4195818, $sub_array) ? array("_" => "true", "Id" => 4195818) : array("_" => "", "Id" => 4195818)),    // IsMoreWeLove
                (in_array(4195808, $sub_array) ? array("_" => "true", "Id" => 4195808) : array("_" => "", "Id" => 4195808)),    // IsR4LSeasonal
                (in_array(4195828, $sub_array) ? array("_" => "true", "Id" => 4195828) : array("_" => "", "Id" => 4195828)),    // IsSecondHelping
                (in_array(4240263, $sub_array) ? array("_" => "true", "Id" => 4240263) : array("_" => "", "Id" => 4240263)),    // IsBetterRecipesDaily
                (in_array(4240273, $sub_array) ? array("_" => "true", "Id" => 4240273) : array("_" => "", "Id" => 4240273)),    // IsBetterRecipesSOLO
                (in_array(4362328, $sub_array) ? array("_" => "true", "Id" => 4362328) : array("_" => "", "Id" => 4362328)),    // IsBetterRecipesSweeps
                (in_array(4362338, $sub_array) ? array("_" => "true", "Id" => 4362338) : array("_" => "", "Id" => 4362338)),    // IsRecipe4LivingSweeps
                (in_array(4369063, $sub_array) ? array("_" => "true", "Id" => 4369063) : array("_" => "", "Id" => 4369063)),    // IsSavvyforkSOLO

                // PROCESS UNSUBSCRIBERS
                (in_array(3844903, $unsub_array) ? array("_" => "false", "Id" => 3844903) : array("_" => "", "Id" => 3844903)),	// IsDailyInsider
                (in_array(3844893, $unsub_array) ? array("_" => "false", "Id" => 3844893) : array("_" => "", "Id" => 3844893)),	// IsFitFabLivingSOLO
                (in_array(3844883, $unsub_array) ? array("_" => "false", "Id" => 3844883) : array("_" => "", "Id" => 3844883)),	// IsDailyRecipes
                (in_array(3844873, $unsub_array) ? array("_" => "false", "Id" => 3844873) : array("_" => "", "Id" => 3844873)),	// IsRecipe4LivingSOLO
                (in_array(3844863, $unsub_array) ? array("_" => "false", "Id" => 3844863) : array("_" => "", "Id" => 3844863)),	// IsBudgetCooking
                (in_array(3844853, $unsub_array) ? array("_" => "false", "Id" => 3844853) : array("_" => "", "Id" => 3844853)),	// IsQuickEasyRecipes
                (in_array(3844843, $unsub_array) ? array("_" => "false", "Id" => 3844843) : array("_" => "", "Id" => 3844843)),	// IsDietInsider
                (in_array(3844833, $unsub_array) ? array("_" => "false", "Id" => 3844833) : array("_" => "", "Id" => 3844833)),	// IsCrockpotCreations
                (in_array(3844823, $unsub_array) ? array("_" => "false", "Id" => 3844823) : array("_" => "", "Id" => 3844823)),	// IsCasseroleCooking
                (in_array(3844813, $unsub_array) ? array("_" => "false", "Id" => 3844813) : array("_" => "", "Id" => 3844813)),	// IsCopycatClassics
                (in_array(3844803, $unsub_array) ? array("_" => "false", "Id" => 3844803) : array("_" => "", "Id" => 3844803)),	// IsMakingItWork
                (in_array(3844793, $unsub_array) ? array("_" => "false", "Id" => 3844793) : array("_" => "", "Id" => 3844793)),	// IsWorkItMomSOLO
                (in_array(3844783, $unsub_array) ? array("_" => "false", "Id" => 3844783) : array("_" => "", "Id" => 3844783)),	// IsDiabeticFriendlyDishes
                (in_array(3844768, $unsub_array) ? array("_" => "false", "Id" => 3844768) : array("_" => "", "Id" => 3844768)),	// IsTheFeedBySavvyFork
                (in_array(4195798, $unsub_array) ? array("_" => "false", "Id" => 4195798) : array("_" => "", "Id" => 4195798)), // IsEditorsChoice
                (in_array(4195818, $unsub_array) ? array("_" => "false", "Id" => 4195818) : array("_" => "", "Id" => 4195818)), // IsMoreWeLove
                (in_array(4195808, $unsub_array) ? array("_" => "false", "Id" => 4195808) : array("_" => "", "Id" => 4195808)), // IsR4LSeasonal
                (in_array(4195828, $unsub_array) ? array("_" => "false", "Id" => 4195828) : array("_" => "", "Id" => 4195828)), // IsSecondHelping
                (in_array(4240263, $unsub_array) ? array("_" => "false", "Id" => 4240263) : array("_" => "", "Id" => 4240263)), // IsBetterRecipesDaily
                (in_array(4240273, $unsub_array) ? array("_" => "false", "Id" => 4240273) : array("_" => "", "Id" => 4240273)), // IsBetterRecipesSOLO
                (in_array(4362328, $unsub_array) ? array("_" => "false", "Id" => 4362328) : array("_" => "", "Id" => 4362328)), // IsBetterRecipesSweeps
                (in_array(4362338, $unsub_array) ? array("_" => "false", "Id" => 4362338) : array("_" => "", "Id" => 4362338)), // IsRecipe4LivingSweeps
                (in_array(4369063, $unsub_array) ? array("_" => "false", "Id" => 4369063) : array("_" => "", "Id" => 4369063)), // IsSavvyforkSOLO
	    );
        
        
        if(isset($unsub_array) && ($unsub_array != false) && (count($unsub_array)>0)){
            // unset the subcampId and signup date if this is a unsub request!
            $uploadArray = unsetSubArrayAttr($uploadArray, array(3834288,3834363));
            
        }
	
	$response = $client->ImmediateUpload(Array(
	    'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
	    'UpdateExistingContacts' => true,
	    'TriggerWorkflow' => false,
	    'contacts' => Array(
	        'ContactData' => Array(
	            Array(   
	                'IsTestContact' => false,	// if set to 'true', then specified email will receive test email
	                'ContactKey' => Array(
                            'ContactId' => $contactId,	// provide contact id for existing subscriber
	                    'ContactUniqueIdentifier' => $email,
	                ),
	                'EmailAddress' => $email,'FirstName' => $first,'LastName' => $last,'PhoneNumber' => $phone,'Fax' => $fax,'Status' => $status,'MailFormat' => $format,
	                'CustomAttributes' => $uploadArray
	        )
	    )
	)));
	
	return $client->__getLastResponse();
	
	/*
	Custom Fields IDs
	oldlistid	3834333				IsDailyInsider	3844903
	subcampid	3834288				IsFitFabLivingSOLO	3844893
	signup_datetime	3834363			IsDailyRecipes	3844883
	ipaddress	3834378				IsRecipe4LivingSOLO	3844873
	source	3834388					IsBudgetCooking	3844863
	subsource	3834408				IsQuickEasyRecipes	3844853
	address1	3834418				IsDietInsider	3844843
	address2	3834428				IsCrockpotCreations	3844833
	city	3834438					IsCasseroleCooking	3844823
	state	3834448					IsCopycatClassics	3844813
	zipcode	3833693					IsMakingItWork	3844803
	country	3834458					IsWorkItMomSOLO	3844793
	gender	3834468					IsDiabeticFriendlyDishes	3844783
	birth_date	3834483				IsTheFeedBySavvyFork	3844768
	age_group	3834493				
	*/
}

function updateCampaignerOpenX ($data_array) {
    $client = new SoapClient('https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL',  array('exceptions' => false,
                           'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
    
    $email = $data_array['email'];
    $first = $data_array['first'];
    $last = $data_array['last'];
    $phone = $data_array['phone'];
    $fax = $data_array['fax'];
    $status = $data_array['status'];    // Subscribed, Unsubscribed, HardBounce, SoftBounce, Pending
    $format = $data_array['format'];    // Text, HTML, Both

    
    // Add the openx ad sequence
    $openx_ads_sequence = $data_array["openx_ads_sequence"];      // Open X Unique Sequence
    $openx_ads_tag_1 = $data_array["openx_ads_tag_1"];            // Open X Ad Tag 1
    $openx_ads_tag_2 = $data_array["openx_ads_tag_2"];            // Open X Ad Tag 2
    $openx_ads_tag_3 = $data_array["openx_ads_tag_3"];            // Open X Ad Tag 3
    $openx_ads_tag_4 = $data_array["openx_ads_tag_4"];            // Open X Ad Tag 4
    $openx_ads_tag_5 = $data_array["openx_ads_tag_5"];            // Open X Ad Tag 5    
    
    
    $contactId = $data_array['ContactId'];        if ($contactId == '') { $contactId = 0; }
    
    $response = $client->ImmediateUpload(Array(
        'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
        'UpdateExistingContacts' => true,
        'TriggerWorkflow' => false,
        'contacts' => Array(
            'ContactData' => Array(
                Array(   
                    'IsTestContact' => false,    // if set to 'true', then specified email will receive test email
                    'ContactKey' => Array(
                        'ContactId' => $contactId,    // provide contact id for existing subscriber
                        'ContactUniqueIdentifier' => $email,
                    ),
                    'EmailAddress' => $email,'FirstName' => $first,'LastName' => $last,'PhoneNumber' => $phone,'Fax' => $fax,'Status' => $status,'MailFormat' => $format,
                    'CustomAttributes' => array(0 =>
                            
                            //OpenX ads Unit
                            (($openx_ads_sequence !='') ? array("_" => $openx_ads_sequence, "Id" => 4173563) : array("_" => "", "Id" => 4173563)),      //Open X Unique Sequence
                            (($openx_ads_tag_1 !='') ? array("_" => $openx_ads_tag_1, "Id" => 4173573) : array("_" => "", "Id" => 4173573)),            // Open X Ad Tag 1
                            (($openx_ads_tag_2 !='') ? array("_" => $openx_ads_tag_2, "Id" => 4173658) : array("_" => "", "Id" => 4173658)),            // Open X Ad Tag 2
                            (($openx_ads_tag_3 !='') ? array("_" => $openx_ads_tag_3, "Id" => 4173668) : array("_" => "", "Id" => 4173668)),            // Open X Ad Tag 3
                            (($openx_ads_tag_4 !='') ? array("_" => $openx_ads_tag_4, "Id" => 4173678) : array("_" => "", "Id" => 4173678)),            // Open X Ad Tag 4
                            (($openx_ads_tag_5 !='') ? array("_" => $openx_ads_tag_5, "Id" => 4173688) : array("_" => "", "Id" => 4173688)),            // Open X Ad Tag 5
                            
                )
            )
        )
    )));
    
    return $client->__getLastResponse();
    
    /*
    Custom Fields IDs
    oldlistid    3834333                IsDailyInsider    3844903
    subcampid    3834288                IsFitFabLivingSOLO    3844893
    signup_datetime    3834363            IsDailyRecipes    3844883
    ipaddress    3834378                IsRecipe4LivingSOLO    3844873
    source    3834388                    IsBudgetCooking    3844863
    subsource    3834408                IsQuickEasyRecipes    3844853
    address1    3834418                IsDietInsider    3844843
    address2    3834428                IsCrockpotCreations    3844833
    city    3834438                    IsCasseroleCooking    3844823
    state    3834448                    IsCopycatClassics    3844813
    zipcode    3833693                    IsMakingItWork    3844803
    country    3834458                    IsWorkItMomSOLO    3844793
    gender    3834468                    IsDiabeticFriendlyDishes    3844783
    birth_date    3834483                IsTheFeedBySavvyFork    3844768
    age_group    3834493                
    */
}



function getSubcampIdDescriptiveName ($subcampid) {
	$get_id = "SELECT notes FROM subcampid WHERE subcampid='$subcampid' LIMIT 1";
	$result = mysql_query($get_id);
	while ($id_row = mysql_fetch_object($result)) {
		return trim($id_row->notes);
	}
}


function LookupNewListIdByOldListId ($old_list_id) {
	if (strlen($old_list_id) == 3 && ctype_digit($old_list_id)) {
		$get_id = "SELECT newListId FROM joinLists WHERE listid='$old_list_id' LIMIT 1";
		$result = mysql_query($get_id);
		while ($id_row = mysql_fetch_object($result)) {
			return trim($id_row->newListId);
		}
	} else {
		return false;
	}
}



function getXmlValueByTag($inXmlset,$needle) {
	$resource    =    xml_parser_create(); //Create an XML parser
	xml_parse_into_struct($resource, $inXmlset, $outArray); // Parse XML data into an array structure
	xml_parser_free($resource); //Free an XML parser
	for($i=0;$i<count($outArray);$i++) {
		if($outArray[$i]['tag']==strtoupper($needle)){
			$tagValue    =    $outArray[$i]['value'];
		}
	}
	return $tagValue;
}



function LookupImpressionWise($email_addr) {

  // added by john during testing, because IW doesn't like my domain
  // for some reason
  if (strpos($email_addr, 'ultranaut.com') !== false) {
    return true;
  }

	$isValid = true;
	$isValid_msg = 'Y';
	$sPostingUrl = "http://post.impressionwise.com/fastfeed.aspx?code=560020&pwd=SilCar&email=$email_addr";
	$response = strtolower(file_get_contents($sPostingUrl));
	
	//	code=560020&pwd=SilCar&email=testme@impressionwise.com&result=Key&NPD=NA&TTP=0.16
	$pieces = explode("&", $response);
	foreach ($pieces as $pair) {
		$data = explode("=", $pair);
		$$data[0] = $data[1];
	}	
	
	if (in_array($result, array("invalid", "seed", "trap", "mole"))) {
		$isValid = false;
		$isValid_msg = 'N';
	}
	
	if($npd=='041')
	{
		$isValid = true;
		$isValid_msg = 'Y';
	}
	
	if ($result == 'retry') {
		mail('samirp@silvercarrot.com','Impression Wise RETRY',$sPostingUrl."\n\n\n".$response);
	}
	
	
	$log_iw = "INSERT IGNORE INTO impression_wise (dateTime,email,isValid,npd,result,ttp,response) 
				VALUES (NOW(),\"$email_addr\",\"$isValid_msg\",\"$npd\",\"$result\",\"$ttp\",\"$response\")";
	$result = mysql_query($log_iw);
	
	return $isValid;
}




function getBounceCountFromArcamax($email) {
	// Shutdown Arcamax call
	return true;
	/*
	$post_string = "email=$email&encoding=JSON";
	$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
	$aUrlArray = explode("//", $sPostingUrl);
	$sUrlPart = $aUrlArray[1];
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
	$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	$server_response = '';
	if ($rSocketConnection) {
		fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
		fputs($rSocketConnection, "Host: $sHostPart\r\n");
		fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
		fputs($rSocketConnection, "Content-length: " . strlen($post_string) . "\r\n");
		fputs($rSocketConnection, "User-Agent: MSIE\r\n");
		fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.datapass:jAyRwBU8")."\r\n");
		fputs($rSocketConnection, "Connection: close\r\n\r\n");
		fputs($rSocketConnection, $post_string);
		while(!feof($rSocketConnection)) {
			$server_response .= fgets($rSocketConnection, 1024);
		}
		fclose($rSocketConnection);
	}
	$obj = json_decode(substr($server_response,strpos($server_response, '{'),strlen($server_response)));
	return $_SESSION['bouncecount'] = trim($obj->{'bouncecount'});
	*/
}




function Arcamax($email,$listid,$subcampid,$user_ip,$type) {
	// Shutdown Arcamax call
	return true;
	
	//$server_response = '';
	//
	//$extra = '';
	//if ($_SESSION['fname'] !='') { $extra .= "&csi_fname=".$_SESSION['fname']; }
	//if ($_SESSION['lname'] !='') { $extra .= "&csi_lname=".$_SESSION['lname']; }
	//if ($_SESSION['addr1'] !='') { $extra .= "&csi_addr1=".$_SESSION['addr1']; }
	//if ($_SESSION['addr2'] !='') { $extra .= "&csi_addr2=".$_SESSION['addr2']; }
	//if ($_SESSION['city'] !='') { $extra .= "&csi_city=".$_SESSION['city']; }
	//if ($_SESSION['state'] !='') { $extra .= "&csi_state=".$_SESSION['state']; }
	//if ($_SESSION['zip'] !='') { $extra .= "&csi_zip=".$_SESSION['zip']; }
	//if ($_SESSION['gender'] !='') { $extra .= "&csi_gender=".$_SESSION['gender']; }
	//if ($_SESSION['phone_1'] !='') { $extra .= "&csi_phone_1=".$_SESSION['phone_1']; }
	//if ($_SESSION['phone_2'] !='') { $extra .= "&csi_phone_2=".$_SESSION['phone_2']; }
	//if ($_SESSION['phone_3'] !='') { $extra .= "&csi_phone_3=".$_SESSION['phone_3']; }
	//if ($_SESSION['day'] !='') { $extra .= "&csi_day=".$_SESSION['day']; }
	//if ($_SESSION['month'] !='') { $extra .= "&csi_month=".$_SESSION['month']; }
	//if ($_SESSION['year'] !='') { $extra .= "&csi_year=".$_SESSION['year']; }
	//if ($_SESSION['country'] !='') { $extra .= "&csi_country=".$_SESSION['country']; }
	//
	//if ($type == 'sub') {
	//	$post_string = "email=$email&sublists=$listid&subcampid=$subcampid&ipaddr=$user_ip".$extra;
	//} else {
	//	$post_string = "email=$email&unsublists=$listid&subcampid=$subcampid&ipaddr=$user_ip".$extra;
	//}
	//
	//
	//$temp_post_data = addslashes($post_string);
	//$insert_post_data = "INSERT IGNORE INTO querystring (dateTimeAdded,postdata) VALUES (NOW(), \"$temp_post_data\")";
	//$insert_post_data_result = mysql_query($insert_post_data);
	//
    //
	//$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
	//$aUrlArray = explode("//", $sPostingUrl);
	//$sUrlPart = $aUrlArray[1];
    //
	//// separate host part and script path
	//$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	//$sHostPart = ereg_replace("\/","",$sHostPart);
	//$sScriptPath = substr($sUrlPart,strlen($sHostPart));
	//		
	//if (strstr($sPostingUrl, "https:")) {
	//	$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	//} else {
	//	$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
	//}
	//		
	//if ($rSocketConnection) {
	//	fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
	//	fputs($rSocketConnection, "Host: $sHostPart\r\n");
	//	fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
	//	fputs($rSocketConnection, "Content-length: " . strlen($post_string) . "\r\n");
	//	fputs($rSocketConnection, "User-Agent: MSIE\r\n");
	//	fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.datapass:jAyRwBU8")."\r\n");
	//	fputs($rSocketConnection, "Connection: close\r\n\r\n");
	//	fputs($rSocketConnection, $post_string);
	//			
	//	while(!feof($rSocketConnection)) {
	//		$server_response .= fgets($rSocketConnection, 1024);
	//	}
	//	fclose($rSocketConnection);
	//		
	//	/*if (strstr($server_response,"error")) {
	//		$message = "Error: $server_response";
	//	} else {
	//		$message = "Success: Unsub Successful!";
	//	}*/
	//} else {
	//	$server_response = "$errstr ($errno)<br />\r\n";
	//}
	//
	//return addslashes($server_response);
	
}









/*function BullseyeBriteVerifyCheck ($email) {
	$handle = fopen("http://www3.tendollars.com/BriteVerifyForSubscriptionCenter.aspx?email=$email&source=subcenter", "rb");
	$server_response = stream_get_contents($handle);
	fclose($handle);
	
	if (strstr($server_response,'valid') || strstr($server_response,'unknown')) {
		$return_value = true;
	} else {
		$return_value = false;
	}
	
	if (strstr($server_response,'not valid') || strstr($server_response,'invalid')) {
		$return_value = false;
	}
	
	$server_response = addslashes($server_response);
	$user_ip = trim($_SERVER['REMOTE_ADDR']);
	$insert_bv_log = "INSERT INTO BullseyeBriteVerifyCheck (email,dateTimeAdded,ip,response)
				VALUES (\"$email\", NOW(), \"$user_ip\",\"$server_response\")";
	$insert_bv_log_result = mysql_query($insert_bv_log);
	
	return $return_value;
}*/

function BullseyeBriteVerifyCheck ($email) {
	$emailInfo = array();
	if(!empty($email))
	{
		$result = mysql_query("SELECT * FROM email_validation WHERE date(dateAdded) >= date_sub(curdate(),interval 1 day) and email = \"$email\"");
		$emailInfo = mysql_fetch_array($result,MYSQL_ASSOC);
		if (empty($emailInfo)) {
			$url = "https://bpi.briteverify.com/emails.json?address=$email&apikey=ad6d5755-ff3e-4a0b-8d63-c61bcffd57b1";
			$content = file_get_contents($url);
			$emailInfo = json_decode($content, true);
			
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			
			if(!empty($emailInfo))
			{
				//Cache the new email address
				$sql = 'INSERT IGNORE INTO email_validation (email,status,error_code,error,dateAdded,ipaddress) VALUES ("'.$emailInfo["address"].'","'.$emailInfo["status"].'","'.$emailInfo["error_code"].'", "'.$emailInfo["error"].'", NOW(),"'.$ipaddress.'")';
				$result = mysql_query($sql);
			}
		} 
	}
	
	if(!empty($emailInfo) && ($emailInfo["status"]=="valid" || $emailInfo["status"]=="unknown" || $emailInfo["status"]=="accept all" || $emailInfo["status"]=="accept_all"))
	{
		return true;
	}
	else
	{
		return false;
	}
}


/**
 * @author Leon Zhao
 * @todo remove the ids that we do not want to overwrite, such as subcampId, signup date
 * @param array $dataArray to be refined
 * @param array $attr_id_array contains the ids of the target removed
 * @return array cleaned $dataArray
 */
function unsetSubArrayAttr($dataArray, $attr_id_array){
    foreach($dataArray as $k=>$v){
        if(in_array($v["Id"], $attr_id_array)){
            unset($dataArray[$k]);
        }
    }
    return $dataArray;
}




function BriteVerify ($email) {
	$data = "email[address]=$email&apikey=ad6d5755-ff3e-4a0b-8d63-c61bcffd57b1";
	$fp = fsockopen('ssl://api.briteverify.com', 443);

	fputs($fp, "POST /emails/verify.xml HTTP/1.1\r\n");
	fputs($fp, "Host: api.briteverify.com\r\n");
	fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
	fputs($fp, "Content-length: ". strlen($data) ."\r\n");
	fputs($fp, "Connection: close\r\n\r\n");
	fputs($fp, $data);

	$result = '';
	while(!feof($fp)) { $result .= fgets($fp, 128); }
	fclose($fp);

	$result = explode("\r\n\r\n", $result, 2);	// split the result header from the content
	$content = isset($result[1]) ? $result[1] : '';//$header = isset($result[0]) ? $result[0] : '';

	return $content;	//return array($header, $content);
}

function getAttrNameByListId($listId){
    //$sql = "SELECT lcca.Id,lcca.Name,jl.listid FROM `LeonCampaignContactAttribute` as lcca left join joinLists as jl on lcca.Id=jl.newListId where lcca.DataType='Boolean'";

    $listArray = array(
        "504" => "IsBetterRecipes Daily",
        "505" => "IsBetterRecipes SOLO",
        "506" => "IsBetterRecipesSweeps",
        "395" => "IsBudgetCooking",
        "539" => "IsCasseroleCooking",
        "554" => "IsCopycatClassics",
        "511" => "IsCrockpotCreations",
        "411" => "IsDailyInsider",
        "393" => "IsDailyRecipes",
        "574" => "IsDiabeticFriendlyDishes",
        "448" => "IsDietInsider",
        "501" => "IsEditorsChoice",
        "410" => "IsFitFabLivingSOLO",
        //"NULL" => "IsLegacySweeps"
        "553" => "IsMakingItWork",
        "503" => "IsMoreWeLove",
        "394" => "IsQuickEasyRecipes",
        "502" => "IsR4LSeasonal",
        "396" => "IsRecipe4LivingSOLO",
        "507" => "IsRecipe4LivingSweeps",
        "508" => "IsSavvyforkSOLO",
        "500" => "IsSecondHelping",
        "583" => "IsTheFeedBySavvyFork",
        "558" => "IsWorkItMomSOLO"
    );
    if(array_key_exists($listId,$listArray)){
        return $listArray[$listId];
    }else{
        return false;
    }
}


