<?php
// Turn off all error reporting
error_reporting(0);

$post_string = "";
while (list($key,$val) = each($_POST)) { $$key = addslashes(strtolower($val)); $post_string .= $key."=".addslashes(strtolower($val))."&"; }
while (list($key,$val) = each($_GET)) { $$key = addslashes(strtolower($val)); $post_string .= $key."=".addslashes(strtolower($val))."&"; }
$post_string = substr($post_string,0,strlen($post_string)-1);

if ($email_addr == '') { echo 'No Email Entered.';exit; }

$error = '';
$success = '';
		
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
	} else {
		if (!(document.getElementById('1').checked || document.getElementById('2').checked || document.getElementById('4').checked)) {
			alert("Please select at least one newsletter!");
			return false;
		} else {
			return true;
		}
	}
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
				<?php if ($success != '') { ?>
						<tr><td style="color:red;font-size:18px;font-weight:bold;"><?php echo $success; ?></td></tr>
				<?php } ?>
				<tr><td style="height:10px;"></td></tr>
				<script type="text/javascript"> 
var t=5;//set time 
setInterval("refer()",1000); //start
function refer(){  
    if(t==0){ 
        location="http://www.betterrecipes.com"; //redirect url
    } 
    document.getElementById('show').innerHTML=""+t+""; //show time
    t--; 
} 
</script> 
<tr><td style="color:#000;">The page will redirect to <a href="http://www.betterrecipes.com">home page</a> after <span id="show"></span> seconds.</td></tr>

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
   
