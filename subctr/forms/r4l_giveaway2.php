<?php

include_once("../config.php");

$message = '';
$error = '';
$pixel = '';
$email_check_passed = false;
$signup_success = false;

if ($_POST['submit'] == 'Enter to Win!') {
	if (ctype_alnum(trim($_POST['src']))) {
		$src = trim($_POST['src']);
	} else {
		$src = '';
	}
	
	$src = strtoupper($src);
	
	$email = trim($_POST['email']);
	$guid = strtoupper(trim($_POST['guid']));
	
	$aJoinListId = array('393','396');
	
	switch ($guid) {
		case '2C9BB3EB97D34BF5B223EBBCBB7651DF':
			$subcampid = '3495';    //R4L Espresso Machine Giveaway 
			break;
		case '13212704FB654FCFBC9B02D282327DD5':
			$subcampid = '4311';	//Center Point R4L 0615
			break;
		case 'C74A2AF66E384FB68487DB27AC74A1B4':
			$subcampid = '3474';
			break;
		case 'DBD36470CCCE4730BF397E9991E5C66E':
			$subcampid = '3513';
			break;
		case 'AD3CD4094B2D49049DBFA089A9524E3F':
			$subcampid = '3689';	// Sweeptakes Advantage
			break;
		case 'DA5E75A7F09B4D28A6EBCB6F6D31D693':
			$subcampid = '4327';	// Shop At Home 0615
			break;
		case 'AC4B2C6F156E4F2CBEA6F6EC2E9CA148':
			$subcampid = '3560';
			break;
		case '497DD9B8427C4C5C99881944767A4505':
			$subcampid = '3626';	// Quick Rewards
			break;
		case 'A921027365F24F7EB81E2864F107FEEA':
			$subcampid = '3568';
			break;
		case '99E4F124AFDB42588BDD53799984EF62':
			$subcampid = '3572';
			break;
		case '690135A6F3F94D24A970A26B5FCE10EF':
			$subcampid = '3573';
			break;
		case '0CF4A30C712D46738F8338AD926E3861':
			$subcampid = '3574';
			break;
		case '8E36A65647EF42E08FDC7FFD31A4C919':
			$subcampid = '3575';
			break;
		case '87598D3D3C144A89AFB79D9CF1AB5489':
			$subcampid = '3576';
			break;
		case '4663AF6BE7E644FEA3DB7C26F0AAAC4D':
			$subcampid = '3590';
			break;
		case '47FF88193A874923B08C618E7781AB4D':
			$subcampid = '3644';	// R4L Facebook Paid Search
			break;
		case '1191E1DAF7244A3EB9659391C6A6BC00':
			$subcampid = '3684';
			break;
		default:
			$subcampid = '4383';	// R4L Default Giveaway 0615
	}
	
	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
		$error = "The email address you provided is not valid. Please try again.";
		$email_check_passed = false;
	} else {
		// Check DNS records corresponding to a given domain
		// Get MX records corresponding to a given domain.
		list($prefix, $domain) = split("@",$email);
		if (!getmxrr($domain, $mxhosts)) {
			$error = "The email address you provided is not valid. Please try again.";
			$email_check_passed = false;
		} else {
			$brite_verify = '';

			if ($error == '') {
				$check_banned_domain = "SELECT * FROM bannedDomains WHERE domain=\"$domain\" LIMIT 1";
				$check_banned_domain_result = mysql_query($check_banned_domain);
				if (mysql_num_rows($check_banned_domain_result) == 1) {
					$error = 'The email address you provided is not valid. Please try again.';
					$email_check_passed = false;
				}
			}

			if ($error == '') {
				$check_banned_email = "SELECT * FROM bannedEmails WHERE email=\"$email\" LIMIT 1";
				$check_banned_email_result = mysql_query($check_banned_email);
				if (mysql_num_rows($check_banned_email_result) == 1) {
					$error = 'The email address you provided is not valid. Please try again.';
					$email_check_passed = false;
				}
			}
			
			
			if ($error == '') {
				if (LookupImpressionWise($email) == false) {
					$error = "Your e-mail address is invalid.";
					$email_check_passed = false;
				}
			}
				
				
			if ($error == '') {
				$check_current_subscriber = "SELECT * FROM joinEmailActive WHERE email=\"$email\" LIMIT 1";
				$check_current_subscriber_result = mysql_query($check_current_subscriber);
				if (mysql_num_rows($check_current_subscriber_result) == 1) {
					// don't do BV check since the user is already subscribed to at least one newsletter/solo
					$email_check_passed = true;
				} else {
					// do BV check
					if (BullseyeBriteVerifyCheck($email) == true) {
						// BV passed
						$email_check_passed = true;
					} else {
						// BV failed
						$error = 'The email address you provided is not valid. Please try again.';
						$email_check_passed = false;
					}
				}
			}
		}
	}
	
	if ($error != '') {
		$message = "<tr><td colspan='2' style='color:red;' align='center' valign='top'>$error</td></tr>";
		$signup_success = false;
		$attempt = true;
	} else {
		// process sign up request...
		
		// CHECK FOR DUPES AND FIRE CAKE PIXEL ONLY IF UNIQUE SIGNUP (email+listid)
		$check = "SELECT * FROM joinEmailActive WHERE email=\"$email\" AND listid IN ('393','396') LIMIT 2";
		$check_result = mysql_query($check);
		if (mysql_num_rows($check_result) == 2) {
			$cake_pixel = "N";
		} else {
			$cake_pixel = "Y";
		}
		
		$signup_success = true;
		$user_ip = trim($_SERVER['REMOTE_ADDR']);
		$build_list_id = '';
		foreach ($aJoinListId as $listid) {
			// insert into joinEmailSub
			$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"R4LGiveaway2\",\"$src\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
				
			// insert into joinEmailActive
			$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"R4LGiveaway2\",\"$src\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
			
			// get new listid from old listid
			$new_listid = LookupNewListIdByOldListId($listid);
			
			// insert into campaigner
			$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
							VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"$subcampid\",\"R4LGiveaway2\",\"$src\",'sub','N')";
			$campaigner_result = mysql_query($campaigner);
			echo mysql_error();
			
			$build_list_id .= $listid.',';
		}
		
		if ($build_list_id != '') {
			$build_list_id = substr($build_list_id,0,strlen($build_list_id)-1);
			//echo "<!-- $build_list_id -->\n\n\n";
			// call to function to send new subscriber to Arcamax.
			$send_to_arcamax = Arcamax($email,$build_list_id,$subcampid,$user_ip,'sub'); // sub or unsub
			//echo "<!-- $send_to_arcamax -->\n\n\n";
			
			// record arcamax server response log
			$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
						VALUES (NOW(),\"$email\",\"$build_list_id\",\"$subcampid\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();
		}
		
		$gtm_pixel = "<img src='http://www.recipe4living.com/giveaway2/giveawayrecipe4living.html' width='0' height='0' border='0' />";
		
		$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=s&f=R4LGiveaway2$src' width='0' height='0' border='0' />"."<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=s&f=R4LGiveaway2$guid' width='0' height='0' border='0' />".$gtm_pixel;
		
		$thankyou_page = "http://www.recipe4living.com/giveaway/thankyou.htm?e=$email&cp=$cake_pixel";
		
		$message = "<tr><td colspan='2' style='color:red;padding:20px;' align='center' valign='top'>&nbsp;</td></tr><script type='text/javascript'>window.top.location = '$thankyou_page';</script>";
	}
} else {
	$attempt = false;
	$src = '';
	if (trim($_GET['src']) !='') {
		if (ctype_alnum(trim($_GET['src']))) {
			$src = trim($_GET['src']);
		}
	}
	$src = strtoupper($src);
	
	$guid = '';
	if (trim($_GET['guid']) !='') {
		$guid = trim($_GET['guid']);
	}
	$guid = strtoupper($guid);
	
	$pixel = "<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=d&f=R4LGiveaway2$src' width='0' height='0' border='0' />"."<img src='http://".trim($_SERVER['SERVER_NAME'])."/subctr/forms/stats.php?a=d&f=R4LGiveaway2$guid' width='0' height='0' border='0' />";
}
?>

<html>
<head>
<title>Recipe4Living.com Giveaway 2</title>
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
<script language="JavaScript">
function check_fields() {
	var str = '';
	var response = '';

	if (document.form1.agree.checked == false) {
		str += "* You must agree to terms and conditions.\n";
	} else {
		if (document.form1.email.value == '') {
			str += "* Please enter your email address.\n";
		}
	}

	if (str == '') {
		return true;
	} else {
		alert (str);
		return false;
	}
}
</script>
<style>
* {
	font: 12px Arial, Helvetica, sans-serif;
	line-height: 1.25em; /* = 20px */
	color: #4e4e4e;
}
</style>
</head>
<body>
<?php echo $pixel; ?>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return check_fields();">
<input type="hidden" value="<?php echo $src; ?>" name="src" id="src">
<input type="hidden" value="<?php echo $guid; ?>" name="guid" id="guid">
<table border="0" cellpadding="0" cellspacing="5"  width="395px">
<tr><td colspan="2">&nbsp;</td></tr>
<?php echo $message; if ($signup_success) { exit; } ?>

			<tr>
				<td>Email Address</td>
				<td><font style="color:red;">*</font> <input type="text" name="email" id="email" size="30" maxlength="100" value="<?php echo $email; ?>"></td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2" align="center"><font style="color:red;">*</font> <input type="checkbox" name="agree" id="agree" value="true"> 
			I understand that by subscribing, I will also receive special offers from third party partners, and agree to Recipe4Living's 
			<a href="http://www.recipe4living.com/terms" target="_blank">Terms of Use</a>, and <a href="http://www.recipe4living.com/privacy" target="_blank">Privacy Policy</a>.
			</td></tr>
		
			<tr><td colspan="2">
					<div style="overflow:auto;height:100px;margin-top:20px;padding:0 10px;border:solid 1px #666;background-color:#e6e6e6">
					<h1>Recipe4Living Terms of Use</h1>
					<div class="text-content">
					<p>Welcome to the Recipe4Living.com website ("Website"). The Website is an Internet property of June Media, Inc. ("Recipe4Living&trade;," "we" or "us"). The products and/or services of Recipe4Living&trade;, June Media, Inc. and each of their respective affiliates are made available to visitors of the Website subject to the terms and conditions set forth herein. You agree to be bound by these Recipe4Living&trade; Terms of Use ("Terms and Conditions"), in their entirety, when you: 1) access or use the Website; and/or 2) register as a member on the Site ("Member"), which enables you to submit certain content to the Website. These Terms and Conditions are inclusive of the Recipe4Living&trade; Privacy Policy ("Privacy Policy") and any and all other applicable Recipe4Living&trade; operating rules, policies, price schedules and other supplemental terms and conditions or documents that may be published from time to time, which are expressly incorporated herein by reference (collectively, "Terms of Use"). Please review the Terms of Use carefully. If you do not agree to the terms and conditions contained within the Terms of Use in their entirety, you are not authorized to use the Website in any manner or form whatsoever.<br><br>Recipe4Living&trade; is not engaged in rendering health or dietary advice. Recipe4Living&trade; is not a medical or health services organization. Neither our Website, nor our staff, can provide diagnosis or medical advice, and nothing we do and no element of the Website should be construed as such. The Recipes (as that term is defined hereinbelow), information and reports that we may provide on the Website are not a substitute for physician consultation, evaluation or treatment. You should always check with your physician to be sure that any Recipes, foods, dietary regimes or other products or services offered by Recipe4Living&trade; are appropriate for you. Recipe4Living&trade;, therefore, disclaims any and all liability for any loss, damage, or injury based on information directly or indirectly obtained through the Website.<br><br></p>
					<h2>Acceptance of Terms of Use</h2>
					<p><br>You agree to the terms and conditions outlined in the Terms of Use with respect to your use of the Website. The Terms of Use constitutes the entire and only agreement between you and Recipe4Living&trade; with respect to your use of the Website and supersedes all prior or contemporaneous agreements, representations, warranties and/or understandings with respect to your use of the Recipes, Website, the content contained therein and/or the products and services provided by or through same. Recipe4Living&trade; may change the Terms of Use, in whole or in part, at any time without specific notice to you. The latest Terms of Use will be posted on the Website. Your continued use of the Website following posted notice constitutes your acceptance of all of the terms and conditions contained within the Term of Use in effect at that time. Therefore, you should regularly check the Website for updates and/or changes. Unless explicitly stated otherwise, any future offer(s) made available to you on the Website that augment(s) or otherwise enhance(s) the current features of the Website shall be subject to the Terms of Use. You understand and agree that Recipe4Living&trade; is not responsible or liable in any manner whatsoever for your inability to use the Website.<br><br></p>
					<h2>Requirements</h2>
					<p><br>The Website is available only to residents of the United States and the District of Columbia that are at least eighteen (18) years of age and that can enter into legally binding contracts under applicable law.<br><br></p>
					<h2>Registration/Account</h2>
					<p><br>Subject to the terms and conditions of the Terms of Use, by registering on the Website, and receiving approval from Recipe4Living&trade;, you can obtain, or attempt to obtain, Member services. Member services will enable you to: (i) access, submit and/or view Recipes and other Content (as that term is defined hereinbelow); and (ii) take advantage of the Website's message boards and other interactive features.<br><br>In order to obtain Member services, and/or submit Recipes and other Content, you must first submit the applicable application form ("Application") to Recipe4Living&trade; for review and initial approval. Recipe4Living&trade; reserves the right, in its sole discretion, to deny access to the Website and/or the Member services to anyone at any time and for any reason, whatsoever. The registration data that you must supply on the Website in order to obtain Member services may include, without limitation: your full name, gender, e-mail address(es), mailing address, daytime, evening and/or cellular telephone numbers, date of birth and/or any other information requested on the applicable form (collectively, "Registration Data"). You agree to provide true, accurate, current and complete Registration Data and to update your Registration Data, as necessary, in order to maintain it in an up to date and accurate fashion. Recipe4Living&trade; will verify and approve all registrants in accordance with its standard verification procedures.<br><br>During registration, you will be asked to provide the name or number that you wish to use as your account login identification, where applicable. If the login identification that you request is not available, you will be asked to supply another login identification. If Recipe4Living&trade; approves your Application, and you agree to any required separate agreement, as applicable, Recipe4Living&trade; will set up your specific Member account and send a confirmation e-mail to the e-mail address that you used to register for the applicable Member services. The confirmation e-mail will contain the following information: (i) your User Name; and (ii) your Password. You can access your specific Member account at the Website using your User Name and Password. You are responsible for maintaining the confidentiality of your Member account, User Name and Password and for restricting access to your computer. You agree to accept responsibility for all activities that occur through use of your Member account, User Name and Password. It is recommended that you make any necessary adjustments in connection with your e-mail account in order to ensure that e-mails from Recipe4Living&trade; are not blocked or otherwise sent to your junk mail folder.<br><br>Recipe4Living&trade; may reject your Application and/or terminate your participation in the Member services at any time and for any reason, in Recipe4Living's&trade; sole discretion. Such reasons may include, without limitation: (i) where Recipe4Living&trade; believes that you are in any way in breach of the Terms of Use; (ii) where Recipe4Living&trade; believes that you are, at any time, conducting any unauthorized commercial activity by and through the Member services; and/or (iii) where Recipe4Living&trade; believes that you are committing fraud or other improper conduct in connection with the Member services.<br><br></p>
					<h2>Copyright and Trademarks</h2>
					<p><br>All content and other material featured on the Website including, without limitation, any Recipe, Content, review, article, comment or other culinary instruction, the Recipe4Living&trade; logo, tagline, trademarks, service marks, design, text, images, graphics and other files, and the selection and arrangement thereof, is owned or licensed by Recipe4Living&trade; or its affiliates and/or licensors, as applicable, and is protected under applicable copyrights, trademarks and other proprietary (including, but not limited to, intellectual property) rights. You are granted a non-exclusive, non-transferable, revocable and limited license to access and use the Website, Recipes, Content and all other material posted or made available by and through the Website in accordance with the Terms of Use. Recipe4Living&trade; may terminate this license at any time for any reason. You may electronically copy and print to hard copy the Recipes and/or other Content for the sole purpose of using such material for informational and non-commercial, personal use only. Except as expressly provided in the Terms of Use, no part of the Recipes, Content and/or Website may be reproduced, recorded, retransmitted, sold, rented, broadcast, distributed, published, uploaded, posted, publicly displayed, altered to make new works, performed, digitized, compiled, translated or transmitted in any way to any other computer, website or other medium or for any commercial purpose, without Recipe4Living's&trade; prior express written consent. Systematic retrieval of Content, Recipes or other material from the Website by automated means or any other form of scraping or data extraction in order to create or compile, directly or indirectly, a collection, compilation, database or directory without written permission from Recipe4Living&trade; is prohibited. You may not create any "derivative works" by altering any aspect of the Content, Recipes and/or Website. You may not use the Content or Recipes in conjunction with any other third-party content. Except as expressly provided herein, you are not granted any rights or license to patents, copyrights, trade secrets, rights of publicity or trademarks with respect to any of the material posted or made available through the Website. The posting of information or material at the Website by Recipe4Living&trade; does not constitute a waiver of any right in such information and materials. Recipe4Living&trade; reserves all rights not expressly granted hereunder. You may not use any device, software or routine to interfere or attempt to interfere with the proper working of the Website. You may not take any action that imposes an unreasonable or disproportionately large load on the Website infrastructure.<br><br>The "Recipe4Living.com" and "Recipe4Living" names and logos, and the other marks displayed on the Website are proprietary to Recipe4Living&trade; or those owners that have granted the right and license to Recipe4Living&trade; to use such marks. The use of any Recipe4Living&trade; or third party trademark without the express written consent of Recipe4Living&trade; or such third party is strictly prohibited.<br><br></p>
					<h2>Disclaimer</h2>
					<p><br>THE WEBSITE, MEMBER SERVICES, RECIPES, SUBMISSIONS, ANY INFORMATION OR OTHER CONTENT CONTAINED HEREIN, AND/OR ANY OTHER RECIPE4LIVING&trade; SERVICES, ARE PROVIDED TO YOU ON AN "AS IS" AND "AS AVAILABLE" BASIS AND ALL WARRANTIES, EXPRESS AND IMPLIED, ARE DISCLAIMED TO THE FULLEST EXTENT PERMISSIBLE PURSUANT TO APPLICABLE LAW (INCLUDING, BUT NOT LIMITED TO, THE DISCLAIMER OF ANY WARRANTIES OF MERCHANTABILITY, NON-INFRINGEMENT OF INTELLECTUAL PROPERTY AND/OR FITNESS FOR A PARTICULAR PURPOSE). IN PARTICULAR, BUT NOT AS A LIMITATION THEREOF, RECIPE4LIVING&trade; MAKES NO WARRANTY THAT THE WEBSITE, MEMBER SERVICES, RECIPES, SUBMISSIONS, ANY INFORMATION OR OTHER CONTENT CONTAINED HEREIN, AND/OR ANY OTHER RECIPE4LIVING&trade; SERVICES: (A) WILL MEET YOUR REQUIREMENTS; (B) WILL BE UNINTERRUPTED, TIMELY, SECURE OR ERROR-FREE OR THAT DEFECTS WILL BE CORRECTED; (C) WILL BE FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS; (D) WILL HAVE SECURITY METHODS EMPLOYED THAT WILL BE SUFFICIENT AGAINST INTERFERENCE WITH YOUR ENJOYMENT OF THE WEBSITE, OR AGAINST INFRINGEMENT; AND/OR (E) WILL BE ACCURATE OR RELIABLE. THE WEBSITE, MEMBER SERVICES, RECIPES, SUBMISSIONS, ANY INFORMATION OR OTHER CONTENT CONTAINED HEREIN, AND/OR ANY OTHER RECIPE4LIVING&trade; SERVICES MAY CONTAIN BUGS, ERRORS, PROBLEMS OR OTHER LIMITATIONS. RECIPE4LIVING&trade; WILL NOT BE LIABLE FOR THE AVAILABILITY OF THE UNDERLYING INTERNET CONNECTION ASSOCIATED WITH THE WEBSITE. NO ADVICE OR INFORMATION, WHETHER ORAL OR WRITTEN, OBTAINED BY YOU FROM RECIPE4LIVING&trade;, ANY USER OR OTHERWISE THROUGH OR FROM THE WEBSITE, SHALL CREATE ANY WARRANTY NOT EXPRESSLY STATED IN THE TERMS OF USE.<br><br></p>
					<h2>Limitation of Liability</h2>
					<p><br>YOU EXPRESSLY UNDERSTAND AND AGREE THAT RECIPE4LIVING&trade; SHALL NOT BE LIABLE TO YOU OR ANY THIRD PARTY FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL AND/OR EXEMPLARY DAMAGES INCLUDING, BUT NOT LIMITED TO, DAMAGES FOR LOSS OF PROFITS, GOODWILL, USE, DATA OR OTHER INTANGIBLE LOSSES (EVEN IF RECIPE4LIVING&trade; HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES), TO THE FULLEST EXTENT PERMISSIBLE BY LAW FOR: (A) THE USE OR THE INABILITY TO USE THE WEBSITE, MEMBER SERVICES, RECIPES, SUBMISSIONS, ANY INFORMATION OR OTHER CONTENT CONTAINED HEREIN, AND/OR ANY OTHER RECIPE4LIVING&trade; SERVICES; (B) THE COST OF PROCUREMENT OF SUBSTITUTE GOODS AND SERVICES RESULTING FROM ANY GOODS, DATA, INFORMATION, CONTENT AND/OR ANY OTHER RECIPE4LIVING&trade; SERVICES PURCHASED OR OBTAINED FROM OR THROUGH THE WEBSITE; (C) THE UNAUTHORIZED ACCESS TO, OR ALTERATION OF, YOUR REGISTRATION DATA; AND (D) ANY OTHER MATTER RELATING TO THE WEBSITE, MEMBER SERVICES, RECIPES, SUBMISSIONS, ANY INFORMATION OR OTHER CONTENT CONTAINED HEREIN, AND/OR ANY OTHER RECIPE4LIVING&trade; SERVICES. THIS LIMITATION APPLIES TO ALL CAUSES OF ACTION, IN THE AGGREGATE INCLUDING, BUT NOT LIMITED TO, BREACH OF CONTRACT, BREACH OF WARRANTY, NEGLIGENCE, STRICT LIABILITY, MISREPRESENTATION AND ANY AND ALL OTHER TORTS. YOU HEREBY RELEASE RECIPE4LIVING&trade; FROM ANY AND ALL OBLIGATIONS, LIABILITIES AND CLAIMS IN EXCESS OF THE LIMITATIONS STATED HEREIN. IF APPLICABLE LAW DOES NOT PERMIT SUCH LIMITATION, THE MAXIMUM LIABILITY OF RECIPE4LIVING&trade; TO YOU UNDER ANY AND ALL CIRCUMSTANCES WILL BE FIVE HUNDRED ($500) DOLLARS. NO ACTION, REGARDLESS OF FORM, ARISING OUT OF YOUR USE OF THE WEBSITE, MEMBER SERVICES, RECIPES, SUBMISSIONS, ANY INFORMATION OR OTHER CONTENT CONTAINED HEREIN, AND/OR ANY OTHER RECIPE4LIVING&trade;&nbsp; SERVICES, MAY BE BROUGHT BY YOU OR RECIPE4LIVING&trade; MORE THAN ONE (1) YEAR FOLLOWING THE EVENT WHICH GAVE RISE TO THE CAUSE OF ACTION. THE NEGATION OF DAMAGES SET FORTH ABOVE IS A FUNDAMENTAL ELEMENT OF THE BASIS OF THE BARGAIN BETWEEN YOU AND RECIPE4LIVING&trade;. ACCESS TO THE WEBSITE WOULD NOT BE PROVIDED TO YOU WITHOUT SUCH LIMITATIONS. Some jurisdictions do not allow certain limitations on liability and in such jurisdictions Recipe4Living's&trade; liability shall be limited to the maximum extent permitted by law.<br><br></p>
					<h2>Use of the Recipe4Living&trade; Website</h2>
					<p><br>By submitting any culinary advice, directions, ingredients or other recipe ("Recipe"), comment, article, review, photograph, image or other content or material to the Website and/or Recipe4Living&trade; ("Submission" and, together with the Recipes, "Content"), either online or offline and whether or not solicited by the Website and/or Recipe4Living&trade;, you hereby grant to Recipe4Living&trade; an irrevocable, perpetual, royalty-free right and license to use, display, modify, reproduce, publish, distribute, make derivative works of, sublicense and otherwise commercially and non-commercially exploit such Content and all associated copyright, trade secret, trademark or other intellectual property rights therein, in any manner or medium now existing or hereafter developed (including, but not limited to, print, film or electronic storage devices), without compensation of any kind to you or any third party.&nbsp; By submitting such Content and/or other materials to us, you represent and warrant that Recipe4Living's&trade; use of same does not and will not breach any agreement, violate any law or infringe any third-party's rights.<br><br>You agree to use the Website in a manner consistent with any and all applicable federal, state and local laws, statutes, rules, regulations and judicial decrees including, without limitation, state and federal intellectual property laws. You agree that you shall not submit, post or otherwise make available to the Website and/or Recipe4Living&trade; any Recipes, Submissions or other material that: (i) is protected by copyright, trademark or other proprietary right without the express permission of the owner of such copyright, trademark or other proprietary right; (ii) displays any telephone numbers, street addresses, last names, URLs, e-mail addresses or any confidential information of any third party; (iii) contains any text or images that may be deemed indecent or obscene in your community, as defined under applicable law; (iv) impersonates any person or entity; (v) expresses or implies that any statements that you make are sponsored or endorsed by the Website and/or Recipe4Living&trade; without our specific prior written consent; (vi) interferes with or disrupt any of the Website's functions, services and/or the servers or networks connected to the Website; and/or (vii) contains software viruses or any other computer code, files or programs designed to interrupt, destroy or limit the functionality of any computer software or hardware or telecommunications equipment. Engaging in any of the aforementioned prohibited practices shall be deemed a breach of the Terms of Use and may result in the immediate termination of your account and access to the Website without notice, in the sole discretion of Recipe4Living&trade;. Recipe4Living&trade; reserves the right to pursue any and all legal and equitable remedies against any user that engages in the aforementioned prohibited conduct. You agree that the burden of determining that any material is not protected by copyright, trademark or other proprietary right rests with you. You agree and acknowledge that you shall be solely liable for any damages resulting from any infringement of copyrights, trademarks or proprietary rights, or any other harm resulting from any of your uploading, posting or delivery of any Recipes, Submissions or other Content to Recipe4Living&trade;. You further agree to indemnify and hold harmless Recipe4Living&trade; for your failure to comply with these provisions.<br><br></p>
					<h2>Recipe4Living Does Not Endorse Member Content</h2>
					<p><br>Recipe4Living&trade; operates the Website as a neutral host, and Recipe4Living&trade; does not regularly monitor, regulate or police the use of the Website by any of its participants. Recipe4Living&trade; has the right, but not the obligation, to monitor and edit or remove any Content. Recipe4Living&trade; takes no responsibility and assumes no liability for any Content submitted by you or any third party. Recipe4Living&trade; does not necessarily endorse, support, sanction, encourage, verify or agree with the comments, opinions or statements posted by Members on the bulletin board or elsewhere on the Website. Any information or material placed online by Members including, but not limited to, Content, advice and opinions, is the view and responsibility of those Members and does not necessarily represent the view of Recipe4Living&trade;. Where provided by a Members or other third party, Recipe4Living&trade; exercises no control over the Content or other information made available on the Website and/or passing through its network or equipment. Disputes may arise between you and others or between you and Recipe4Living&trade; related to such Content or other material posted or made available by and through the Website. Such disputes could involve, among other things: (i) the Content; (ii) other material posted or made available by and through the Website; (iii) use or misuse of domain names; (iii) the infringement of copyrights, trademarks or other rights in intellectual property; (iv) defamation; (v) fraud; and (vi) the use or misuse of information. You agree that all claims, disputes or wrongdoing which result from, or which are related in any way to, the Content or other information that you transmit, re-transmit or receive through the Website, network or equipment are your sole and exclusive responsibility. Recipe4Living&trade; expressly disclaims all responsibility and liability for uses by you of any Content or other information obtained on or in connection with the Site.<br><br></p>
					<h2>Copyright Complaints</h2>
					<p><br>Recipe4Living&trade; respects the intellectual property rights of others, and we require our users to do the same. Recipe4Living&trade; may, in appropriate circumstances and at its discretion, terminate the access of registered Members, subscribers and account holders who infringe upon the copyright, or other intellectual property, rights of others.<br><br>Pursuant to Title 17, United States Code, Section 512(c)(2), as amended by Title II of the Digital Millennium Copyright Act, notifications of copyright infringement on the Website must be sent to Recipe4Living's&trade; Designated Agent.<br><br>NOTE: THE FOLLOWING INFORMATION IS PROVIDED SOLELY FOR NOTIFYING RECIPE4LIVING&trade; THAT YOUR COPYRIGHTED MATERIAL MAY HAVE BEEN INFRINGED.<br><br>WE CAUTION YOU THAT UNDER FEDERAL LAW, IF YOU KNOWINGLY MISREPRESENT THAT MATERIAL IS INFRINGING ON COPYRIGHTED MATERIAL, YOU MAY BE SUBJECT TO SIGNIFICANT CIVIL PENALTIES. THESE INCLUDE MONETARY DAMAGES, COURT COSTS AND ATTORNEY'S FEES INCURRED BY US, BY ANY COPYRIGHT OWNER OR BY ANY COPYRIGHT OWNER'S LICENSEE, THAT IS INJURED AS A RESULT OF OUR RELYING UPON YOUR MISREPRESENTATION THAT YOU HAVE THE RIGHTS IN, AND THE RIGHT TO POST, ANY CONTENT. YOU MAY ALSO BE SUBJECT TO CRIMINAL PROSECUTION FOR PERJURY.<br><br>DO NOT SEND ANY INQUIRIES UNRELATED TO COPYRIGHT INFRINGEMENT (E.G., REQUESTS FOR TECHNICAL ASSISTANCE OR CUSTOMER SERVICE, REPORTS OF E-MAIL ABUSE, ETC.) TO THE CONTACT LISTED BELOW. YOU WILL NOT RECEIVE A RESPONSE IF SENT TO THAT CONTACT.<br><br>To be effective, the notification must be a written communication that includes the following:<br><br>&nbsp;&nbsp;&nbsp; An electronic or physical signature of the person authorized to act on behalf of the owner of the copyright interest;<br>&nbsp;&nbsp;&nbsp; A description of the copyrighted work that you claim has been infringed;<br>&nbsp;&nbsp;&nbsp; A description of where the material that you claim is infringing is located on the Website;<br>&nbsp;&nbsp;&nbsp; Your physical mailing address, telephone number and e-mail address;<br>&nbsp;&nbsp;&nbsp; A statement by you that you have a good faith belief that the disputed use is not authorized by the copyright owner, its agent or the law;<br>&nbsp;&nbsp;&nbsp; A statement by you made under penalty of perjury, that the above information in your notice is accurate and that you are the copyright owner or authorized to act on the copyright owner's behalf.<br><br>Any claims of copyright infringement regarding the Website and/or Recipe4Living&trade; should be sent to Recipe4Living's&trade; Compliance Officer as follows:<br>Recipe4Living.com<br>June Media, Inc.<br>Attn: Compliance Officer<br>209 W Jackson Blvd, Suite 702<br>Chicago, IL 60606<br><br>Upon receipt of notification of a claimed infringement, Recipe4Living&trade; will respond expeditiously to remove or disable access to the material that is claimed to be copyright protected.<br><br>Recipe4Living&trade; may also take reasonable steps to promptly notify the alleged infringer in writing of the claim against him or her, and that it has removed or disabled access to the material or terminated Internet access (see Sections 512(c)(1)(C) and (g) of the DMCA).<br><br></p>
					<h2>Representations and Warranties</h2>
					<p><br>Users and/or Members, as applicable, hereby represent and warrant to Recipe4Living&trade; as follows: (i) the Terms of Use constitute such party's legal, valid and binding obligation which is fully enforceable against such party in accordance with its terms; (ii) such party's use of the Recipes, Content and/or other participation on the Website will not conflict with or violate: (a) any provision of law, rule or regulation to which such party is subject; (b) any order, judgment or decree applicable to such party; (c) any provision of such party's corporate by-laws or certificate of incorporation, if applicable; or (d) any agreement or other instrument applicable to such party; (iii) there is no pending or, to the best of such party's knowledge, threatened claim, action or proceeding against such party; and (vi) such party will be solely responsible for complying with the terms and conditions of the Terms of Use.<br><br></p>
					<h2>Bypassing or Disabling any Portion of the Website or Software</h2>
					<p><br>If you bypass or disable any portion of the Website or associated software including, without limitation, the blocking of cookies, Recipes, Submissions and other Content, you are in violation of the Terms of Use and Recipe4Living&trade; may suspend or terminate your use of the Website without notice. Termination of your applicable Member account will not excuse you from any criminal or other civil liabilities that may result from your actions.<br><br></p>
					<h2>Accessing the Website</h2>
					<p><br>You are responsible for obtaining and maintaining, at your own cost and expense, all input/output devices or equipment (such as modems, terminal equipment, computer equipment and software) and communications services (including, without limitation, long distance or local telephone services) necessary to access the Website and for ensuring that such equipment and services are compatible with Recipe4Living's&trade; requirements.<br><br></p>
					<h2>Indemnification</h2>
					<p><br>You agree to indemnify and hold Recipe4Living&trade;, its parents and subsidiaries, and each of their respective members, officers, directors, employees, agents, co-branders, content licensors and/or other partners, harmless from and against any and all claims, expenses (including reasonable attorneys' fees), damages, suits, costs, demands and/or judgments whatsoever, made by any third-party due to or arising out of: (a) your use of the Website in any manner whatsoever; (b) your breach of the Terms of Use; and/or (d) your violation of any rights of another individual and/or entity. The provisions of this paragraph are for the benefit of Recipe4Living&trade;, its parents, subsidiaries and/or affiliates, and each of their respective officers, directors, members, employees, agents, shareholders, licensors, suppliers and/or attorneys. Each of these individuals and entities shall have the right to assert and enforce these provisions directly against you on its own behalf.<br><br></p>
					<h2>Third-party Websites</h2>
					<p><br>The Website contains links to other websites on the Internet that are owned and operated by third-parties. In some instances, these websites are co-branded and the third-parties are entitled to use Recipe4Living's&trade; name and logo on their third-party websites. Recipe4Living&trade; does not control the information, products or services available on these third-party websites. The inclusion of any link does not imply endorsement by Recipe4Living&trade; of the applicable website or any association with the website's operators. Because Recipe4Living&trade; has no control over such websites and resources, you agree that Recipe4Living&trade; is not responsible or liable for the availability or the operation of such external websites, for any material located on or available from any such websites or for the protection of your data privacy by third-parties. Any dealings with, or participation in promotions offered by, advertisers on the Website and/or other third-parties, including the payment and delivery of related goods or services, and any other terms, conditions, warranties or representations associated with such dealings or promotions, are solely between you and the applicable advertiser or other third-party. You further agree that Recipe4Living&trade; shall not be responsible or liable, directly or indirectly, for any loss or damage caused by the use of or reliance on any such material available on or through any such third-party website or any such dealings or promotions.</p>
					<h2>User Information</h2>
					<p><br>Except where expressly provided otherwise by us in the Terms of Use, and subject to the Privacy Policy, all Registration Data and/or materials that you submit through or in association with this Website shall be considered non-confidential. For a copy of the Privacy Policy, please click here.<br><br></p>
					<h2>Miscellaneous</h2>
					<p>For Contests and giveaways, only one entry per person will be valid and accepted. Duplicate entries and email addresses will be ignored.</p>
					<p><br>The Terms of Use shall be treated as though they were executed and performed in New York, New York and shall be governed by and construed in accordance with the laws of the State of New York (without regard to conflict of law principles). Should a dispute arise concerning the terms and conditions of the Terms of Use or the breach of same by any party hereto, the parties agree to submit their dispute for resolution by arbitration before the American Arbitration Association in New York City, in accordance with the then current Commercial Arbitration Rules of the American Arbitration Association. Any award rendered shall be final and conclusive to the parties and a judgment thereon may be entered in any court of competent jurisdiction. Nothing herein shall be construed to preclude any party from seeking injunctive relief in order to protect its rights pending an outcome in arbitration. The Terms of Use shall not be governed by the United Nations Convention on Contracts for the Sale of Goods.<br><br>Should any part of the Terms of Use be held invalid or unenforceable, that portion shall be construed consistent with applicable law and the remaining portions shall remain in full force and effect. The Terms of Use are personal between you and Recipe4Living&trade; and govern your use of the Website. Recipe4Living's&trade; failure to enforce any provision of the Terms of Use shall not be deemed a waiver of such provision nor of the right to enforce such provision. The parties do not intend that any agency or partnership relationship be created through operation of the Terms of Use.<br><br>The Terms of Use are the entire agreement between the parties pertaining to its subject matter, and supersedes all prior written or oral agreements (including prior versions of the Terms of Use and any conflicting confidentiality agreements), representations, warranties or covenants between the parties with respect to such subject matter. There are no third-party beneficiaries of the Terms of Use. The headings of sections or other subdivisions of these Terms and Conditions will not affect in any way the meaning or interpretation of these Terms and Conditions.<br><br>The Terms of Use will be binding on, inure to the benefit of, and be enforceable against, the parties and Recipe4Living's&trade; successors and assigns. You are not permitted to transfer any rights and/or obligations pursuant to the Terms of Use without the express written consent of Recipe4Living&trade;. Any attempt to do so will result in the immediate termination of your Member account and you will be denied access to the Website.<br><br>Recipe4Living&trade; may provide notices to Members and users by posting notices or links to notices on the Website. Notices to Members may also be made via e-mail, regular mail, overnight courier or facsimile at the Member's contact addresses of record as set forth by that party on the applicable Application or registration form. If Members wish to provide notice to Recipe4Living&trade;, such notice shall be sent, postage prepaid by U.S. registered or certified mail or by international or domestic overnight courier, to: Recipe4Living.com, June Media, Inc., Attn: Member Services, 209 W Jackson Blvd, Suite 702, Chicago, IL 60606. Notices sent by email or telecopy, with or without electronic confirmation, will not be deemed to be valid unless actual receipt is confirmed in writing by authorized Recipe4Living&trade; personnel.<br><br></p>
					<h2>How to Contact Us</h2>
					<p><br>Our "<a href="../../../contact" target="_top">Contact Us</a>" page contains information that allows you to contact us directly with any questions or comments that you may have. We listen to or read, as applicable, every message sent in and endeavor to reply promptly to each one. This information is used to respond directly to your questions or comments. If you have any questions about the Terms of Use or the practices of Recipe4Living&trade;, please feel free to contact us at 1-847-205-9320 or at <a href="http://www.junemedia.com" target="_top">JuneMedia.com</a>.<br><br>Revised: June 2013<br><br></p>	
					</div>
					</div>
			</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>


<tr>
	<td colspan="2" align="center">
		<input type="submit" name="submit" value="Enter to Win!">
	</td>
</tr>
</table>
</form>
</body>
</html>
