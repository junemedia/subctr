<?php
include_once("config.php");
/*echo $_POST;
echo $_GET;

print_r("Post:\r\n");
print_r($_POST);

print_r("\r\nGet:\r\n");
print_r($_GET);
exit;*/

$endTime = time();
$startTime = $endTime - (24*60*60);
$link = "http://win.betterrecipes.com/api/syncUser/".$startTime.'/'.$endTime;

echo "-->Start to download from $link ... ";

$recieve = file_get_contents($link);
$items = json_decode($recieve);
echo "Done\n";

$totalRows = count($items);
echo "-->total [$totalRows] \n";

/*echo "<pre>";
print_r($items);
echo "</pre>";exit();*/

//$items = array(array('email'=>'howewangme@gmail.com','ip'=>'66.54.186.254','site_id'=>2,'firstname'=>'Howe','lastname'=>'Wang','city'=>'Chicago','zip'=>'60606','state'=>'IL','date_registered'=>'2014-12-12'));

$total = 0;
$successTotal = 0;
$failedTotal = 0;
$failedEmails = '';
if(!empty($items))
{
    foreach($items as $item) {
        
            $email = $item->email;
        echo "-->Processing $email ... ";
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

            if($item->site_id==1)
            {
                $fromSite = 'BR';
                $subcampid = 4377; //Sweeps Registration BR 0615
                $source = 'SweepsRegistrationBR0715';
                $save_subcampid = $subcampid;
                $old_listid = 506; //506:old list id for br sweeps

                // For logs only
                $parts = array(506,504,505);
            }

            if($item->site_id==2)
            {
                $fromSite = 'R4L';
                $subcampid = 4378 ; //Sweeps Registration R4L 0615
                $source = 'SweepsRegistrationR4L0715';
                $save_subcampid = $subcampid;
                $old_listid= 507;   //507:old list id for r4l sweeps
                //$source = getSubcampIdDescriptiveName($subcampid);

                // For logs only
                $parts = array(507,393,396,501,502,503);
            }
            echo " $fromSite ... ";

            // We don't have unsub here, ok?
            if($old_listid == 506){
                // IsBetterRecipesSweeps, then add IsBetterRecipesDaily IsBetterRecipesSOLO
                $sub_array = array(4362328,4240263,4240273);

            }

            if($old_listid == 507){
                // IsRecipe4LivingSweeps, then add IsDailyRecipes IsRecipe4LivingSOLO IsEditorsChoice IsR4LSeasonal IsMoreWeLove
                $sub_array = array(4362338,3844883,3844873,4195798,4195808,4195818);
            }        

            // Log Start --------------------------------------
            // Alright, just like what we did before, save them all in the logs
            foreach($parts as $list_parts) {
                $insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"$source\")";
                $insert_query_result = mysql_query($insert_query);
                echo mysql_error();

                $insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$ipaddr\",\"$list_parts\",\"$subcampid\",\"$source\")";
                $insert_query_result = mysql_query($insert_query);
                echo mysql_error();
            }

            // Log End ---------------------------------

            //Check if the email is already in campaigner
            $query = "SELECT l.3818568 as email,l.3834288 as subcampid  FROM LeonCampaignContactJoin as l WHERE l.3818568 = '".$email."' limit 1";
            $result2 = mysql_query($query);
            echo mysql_error();
            $row = mysql_fetch_object($result2);
            if(!empty($row))
            {
                    $alreadyExist = true;
                    if(!empty($row->subcampid))
                    {
                            $subcampid = ''; //don't override the exist subcampid
                    }
            }

            $data_array = array('email' => $email, 'first' => $firstName, 'last' => $lastName,
                                                    'phone' => '', 'fax' => '', 'status' => 'Subscribed', 'format' => 'Both',
                                                    'ipaddr' => $ipaddr, 'signup_date' => $signup_date, 'age_group' => '',
                                                    'oldlistid' => '', 'subcampid' => $subcampid, 'source' => $source,
                                                    'subsource' => $subsource, 'address1' => '', 'address2' => '',
                                                    'city' => $city, 'state' => $state, 'zipcode' => $zipcode,
                                                    'country' => 'US', 'gender' => '', 'birth_date' => '', 'contactId' => 0, 
                                                    'sub_array' => $sub_array, 'unsub_array' => $unsub_array, 'alreadyExist'=>$alreadyExist    

                                );

            $send_result = sendSweepsToCampaigner($data_array);
            //print_r($data_array);
            $result_code = trim(getXmlValueByTag($send_result,'ResultCode'));
            $send_result = addslashes($send_result);	
            if(strtolower($result_code) != 'success')
            {
                    $failedEmails[] = $email."(".$result_code.")\r\n";		
                    $failedTotal++;
            }else
            {
                    $successTotal++;
            }

            // insert into sweeps_log
            $sweeps_log = "INSERT IGNORE INTO sweeps_user_boolean_log (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,status,fromSite,reponse,link)
                                            VALUES (NOW(),\"$email\",\"$ipaddr\",\"$old_listid\",\"".implode(',', $parts)."\",\"$save_subcampid\",\"$source\",\"$result_code\",\"$fromSite\",\"$send_result\",\"$link\")";
            //echo $sweeps_log . "\n";
            $sweeps_log_result = mysql_query($sweeps_log);
            echo mysql_error();
            echo "$result_code\n";
    }
}

$total = $successTotal+$failedTotal;
	
// Send out results mail
date_default_timezone_set('America/Chicago');
$email = "williamg@junemedia.com";
//$email = "";
// Send the mail notification
$to      = $email . ',leonz@junemedia.com,howew@junemedia.com';
$subject = 'Daily Report - Push Sweeps Register Users Into Campaigner';
$failedMsg = $failedTotal>0?"Failed Emails:\r\n".implode(",", $failedEmails)."\r\n":"";

$message = "Done! Total Upload [$total] emails.\r\n".
		   "Successed: $successTotal emails.\r\n".
		   "Failed: $failedTotal emails.\r\n".$failedMsg;
$headers = 'From: Pushing Sweeps <leonz@junemedia.com>' . "\r\n" .
	'Reply-To: Pushing Sweeps <leonz@junemedia.com>' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

tryMail($to, $subject, $message, $headers);

?>
