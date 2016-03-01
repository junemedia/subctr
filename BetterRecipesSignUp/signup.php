<?php
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
// Turn off all error reporting
error_reporting(0);

$error = '';
$success = '';
if ($_POST['quick_submit'] == 'Submit') {
	$listid = '';
	//$aJoinListId = $_POST['aJoinListId'];
	$email_addr = $_POST['email_addr'];
	
	/*if (count($aJoinListId) == 0) {
		$error = 'Please select at least one newsletter!';
	} else {
		foreach ($aJoinListId as $id) {
			$listid .= $id.',';
		}
	}*/
	
	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email_addr)) {
		$error = 'Please enter valid email address!';
	} else {
		list($prefix, $domain) = split("@",$email_addr);
		if (!getmxrr($domain, $mxhosts)) {
			$error = 'Please enter valid email address!';
		}
	}

	if ($error == '') {
		$listid = '504,505';
		$user_ip = trim($_SERVER['REMOTE_ADDR']);
		$subcampid = '4183';

		$posting_url = "http://r4l.popularliving.com/br_api.php?email=$email_addr&ipaddr=".$user_ip."&keycode=if3lkj6i8hjnax&sublists=$listid&subcampid=$subcampid";
		$response = file_get_contents($posting_url);
		//echo $posting_url.'<br>'.$response;
		
		setcookie("EMAIL_ID", $email_addr, time()+642816000, "/", ".recipe4living.com");
		$plant_cookie = "<img src='http://jmtkg.com/plant.php?email=$email_addr' width='0' height='0'></img>";
		
		$pixel = "<!-- Google Tag Manager -->
				<noscript><iframe src=\"//www.googletagmanager.com/ns.html?id=GTM-PPMDBL\"
				height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
				<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
				new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
				j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
				'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
				})(window,document,'script','dataLayer','GTM-PPMDBL');
				dataLayer.push({'event': 'formsubscriberecipe4living'});</script>
				<!-- End Google Tag Manager -->";
		
		$success = 'Thank you for signing up!'.$plant_cookie.$pixel;
		header('Location:'.'./thanks.php?email_addr='.$email_addr);		
		$email_addr = '';
		//$aJoinListId = array();
	}
	
} else {
	if (!isset($email_addr)) { $email_addr = ''; }
	//if (!isset($aJoinListId)) { $aJoinListId = array(); }
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml" xml:lang="en" lang="en">
    <head>
	<title>Registration | Better Recipes</title>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<meta content="Online Recipes | Online Cookbook | Recipe Contests | Daily Sweepstakes | Better Recipes" name="title">
	<meta content="Better Recipes is the premier online cookbook and recipe community. Sign up to share online recipes, enter recipe contests, daily sweepstakes and more." name="description">
	<meta content="online recipes, online cookbook, recipe community, recipes, recipe contests, daily sweepstakes, share recipes" name="keywords">
	<meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
	<meta content="Better Recipes" property="og:site_name">
	<meta content="175775159784" property="fb:app_id">
	<meta content="1485626684,612918441,1257621649,24408765" property="fb:admins">
	<title>Online Recipes | Online Cookbook | Recipe Contests | Daily Sweepstakes | Better Recipes</title>
	<link href="http://www.betterrecipes.com/favicon.ico" rel="shortcut icon">
	<link href="css/newsletter.css" rel="stylesheet"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
</head>

<body class="shell">
<div id="brbgthin">
        <div id="wrapper"><noscript>
	<div class="noJS ACThead1">You Need To Have Javascript and Flash To Make This Site Work Well.</div>
</noscript>
<div id="main-header">
          <div class="wrapper">
            <div class="logo-space">
              <h1><a href="http://www.betterrecipes.com" title="Better Recipes"><img src="images/logo-betterrecipes.png" alt="BetterRecipes : better recipes - better meals"/></a></h1>
              <div class="hd-ad ad728x90">
                <!-- *** start of top banner ad *** -->
                <!-- *** end of top banner ad *** -->
              </div>
            </div><!-- /.logo-space -->           
            <div class="banner ad1000x45">
            </div><!-- /.banner -->
            <div class="clearfix"></div>
          </div><!-- /.wrapper -->
        </div><!-- /#main-header -->
    <div id="main-content">
                  <div id="theme-wrap">
                    <div class="wrapper">
                      <div class="section" style="min-height:550px;">
                        <div class="article">
                            <div id="pagebody">
                            <div id="singlecolumn"><div id="singlecolumnwell">
<br>
<table width="650" cellpadding="0" cellspacing="0" border="0" class="quickSignupContainer">
    <tr>
        <td width="50" class="quickNewsHide">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td><img src="images/br_regtopper_weeklyNL.jpg"></td>
    </tr>
</table>
<br>
<script>
function check_fields() {
	var email = document.getElementById('email_addr').value;
	var pattern = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
	var chkFlag = pattern.test(email);
	if(!pattern.test(email)) {
		alert("If you'd like to sign up for our newsletters, please enter a valid e-mail address!");
		document.getElementById('email_addr').focus();
		return false;
	} /*else {
		if (!(document.getElementById('1').checked || document.getElementById('2').checked || document.getElementById('4').checked)) {
			alert("Please select at least one newsletter!");
			return false;
		} else {
			return true;
		}
	}*/
}
window.scroll(0,0); // horizontal and vertical scroll targets
</script>
<table width="650" cellpadding="0" cellspacing="0" border="0" class="quickSignupContainer">
    <form id="quicksignForm" action="" method="post" onsubmit="return check_fields(this)">
        <tr>
            <td width="50" class="quickNewsHide">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td>
                <br><br>

                <table width="600" cellpadding="0" cellspacing="0" border="0" class="quickNewsUserInfo">

                    <tr>
                                <td colspan="2" height="5"><img height="5" src="images/shim.gif"></img></td>
                            </tr>
                            <tr>
                                <td class="REGText5" nowrap>E-mail&nbsp;Address:&nbsp;&nbsp;</td>
                                <td class="REGText7"><input id="email_addr" maxlength="50" style="width:110px; font-size:12px;" name="email_addr" value="<?php echo $email_addr; ?>" tabindex="1" class="required" type="email"/></td>
                            </tr>

                                <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>

					<tr>
						<td colspan="2">
                           <!-- <span class="REGText11"><br><b>Choose your FREE newsletter(s) below:</b></span>
							<li class="newsletter_item">
                            <label>
                                <input id="1" name="aJoinListId[]" value="9700002" class="newsCheck reg_newsletter_option" type="checkbox" checked="checked"/>
								<span class="newsLink_promo newsLink_tip" title="Rachael's every day favorites, seasonal recipes, holiday & party ideas, how-to tips, and much more!">
                                                    Every Day with Rachael Ray Weekly Bites</span>
                                            </label>
                        </li>
                    <li class="newsletter_item">
                            <label>
                                <input id="2" name="aJoinListId[]" value="28" class="newsCheck reg_newsletter_option" type="checkbox" checked="checked"/>
								<span class="newsLink_promo newsLink_tip" title="Daily newsletter featuring mouthwatering recipes from brands across the web. Also, enter to win the prize of the day!">
                                                   Recipe.com Daily Recipe</span>
                                            </label>
                        </li>
                    <li class="newsletter_item">
                            <label>
                                <input id="3" name="aJoinListId[]" value="3900003" class="newsCheck reg_newsletter_option" type="checkbox"/>
								<span class="newsLink_promo newsLink_tip" title="Get access to the best recipes from today's most widely respected and trusted food brands on the web. ">
                                                   Recipe.com Your Weekly Dish</span>
                                            </label>
                        </li>
                    <li class="newsletter_item">
                            <label>
                                <input id="4" name="aJoinListId[]" value="134" class="newsCheck reg_newsletter_option" type="checkbox"/>
								<span class="newsLink_promo newsLink_tip" title="Scrumptious kitchen-tested recipes for appetizers, dinner, dessert & more!">
                                                   BHG.com Weekly Recipe</span>
                                            </label>
                        </li>
                    <script type="text/javascript">
                    $(document).ready(function() {
                        if($('html').hasClass('no-touch')){
                            $('.newsLink_promo').tips({fixed:false});
                        }
                    });
                </script>-->
            <table class="quickNewsHide">
								<tr>
									<td colspan="2" valign="middle" align="left">&nbsp;</td>
								</tr>
							</table>
							<br/>
									<input id="reg_optin" name="reg_optin" value="true" tabindex="checked" type="checkbox" checked="checked"/>
									<span class="reg_priv_blurb"><strong>Yes!</strong> I'd like to receive news and offers from June Media Inc. via e-mail.</span>
									<input id="quick_submit" name="quick_submit" value="Submit" type="submit"/></td>
                    </tr>
                </table>
                <!-- End info table -->
            </td>
        </tr></form></table><br><br><br>

</div>
            <div class="clearall"></div>
		</div>
    </div>
                        </div><!-- /.article -->
                        <div class="sidebar">
                        </div><!-- /.sidebar -->
                        <div class="clearfix"></div>
                      </div><!-- /.section -->
                    </div><!-- /.wrapper -->
                  </div><!-- /#theme-wrap -->
                </div><!-- /#main-content -->
</div><!-- End Wrapper(s) Div DO NOT REMOVE -->
</div><!-- End brbg -->
</body>
   </html>
