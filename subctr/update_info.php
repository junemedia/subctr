<?php

include_once('session_handlers.php');
if (!(isset($_POST['PHPSESSID'])) && !(isset($_GET['PHPSESSID']))) {
	session_start();
	error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
} else {
	if ($_POST['PHPSESSID']) {
		$PHPSESSID = $_POST['PHPSESSID'];
	} else {
		$PHPSESSID = $_GET['PHPSESSID'];
	}
	
	if (session_id() == '') {
		session_start();
	}
}

include_once("config.php");


// exit the page if email is not set.
if (trim($_SESSION['email']) == '') {
	//echo " - Error at line ".__LINE__.". PHPSESSID: ".session_id();
	echo "<div style='font-family: verdana;font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;'>
			Sorry, something went wrong.
			<a href='"."http://".trim($_SERVER['HTTP_HOST'])."/subctr/index.php?PHPSESSID=".session_id()."'>Click here and try again</a>.
				</div>";
	exit;
}


$email = $_SESSION['email'];

$message = '';
if ($_POST['submit'] == 'Update Information') {
	$first = $_POST['first'];
	$last = $_POST['last'];
	$zip = $_POST['zip'];
	$day = $_POST['day'];
	$month = $_POST['month'];
	$year = $_POST['year'];
	$gender = $_POST['gender'];
	
	$first = (!ctype_alpha(trim($first)) ? '' : trim($first));
	$last = (!eregi("^[-A-Z[:space:]'\.]*$", trim($last)) ? '' : trim($last));
	$zip = (!ereg("^[0-9-]{5,}$", strtoupper(trim($zip))) ? '' : strtoupper(trim($zip)));
	if (!(strlen(trim($day)) == 2 && ctype_digit(trim($day)) && trim($day) <= 31)) { $day = ''; }
	if (!(strlen(trim($month)) == 2 && ctype_digit(trim($month)) && trim($month) <= 12)) { $month = ''; }
	if (!(strlen(trim($year)) == 4 && ctype_digit(trim($year)) && trim($year) < date('Y'))) { $year = ''; }
	if (checkdate($month, $day, $year) == false) { $month = '';$day = '';$year = ''; }
	$gender = ((strtoupper(trim($gender)) == 'M' || strtoupper(trim($gender)) == 'F') ? strtoupper(trim($gender)) : '');
	
	$_SESSION['fname'] = $first;
	$_SESSION['lname'] = $last;
	$_SESSION['zip'] = $zip;
	$_SESSION['gender'] = $gender;
	$_SESSION['day'] = $day;
	$_SESSION['month'] = $month;
	$_SESSION['year'] = $year;
	
	// call to function to send subscriber personal data to Arcamax. - this is NOT Signup or Unsub
	$send_to_arcamax = Arcamax($email,'',$_SESSION['subcampid'],$_SESSION['user_ip'],'sub');
	
	if ($_SESSION['user_ip'] == '216.180.167.121') { echo '<!--'.$send_to_arcamax.'<br><br> -->'; }

	if ($email != '') {
		// insert/update record only if email is there...
		$query = "SELECT * FROM userData WHERE email = \"$email\" LIMIT 1";
		$result = mysql_query($query);
		echo mysql_error();
		if (mysql_num_rows($result) == 0) {
			// do insert
			$insert = "INSERT INTO userData (email,fname,lname,zip,gender,day,month,year)
					VALUES (\"$email\",\"$first\",\"$last\",\"$zip\",\"$gender\",\"$day\",\"$month\",\"$year\")";
			$insert_result = mysql_query($insert);
			echo mysql_error();
		} else {
			// do update
			$update = "UPDATE userData SET 
						fname= \"$first\",
						lname= \"$last\",
						zip= \"$zip\",
						gender= \"$gender\",
						day= \"$day\",
						month= \"$month\",
						year= \"$year\"
					WHERE email = \"$email\"";
			$update_result = mysql_query($update);
			echo mysql_error();
		}
		$message = "<div style='font-family: verdana;font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;'>
					<b>Thank you for submitting your information.</b> <a href='check.php?PHPSESSID=".session_id()."'><b>Go Back to Manage My Newsletters</b></a>
					</div>";
		echo $message;exit;
	}
} else {
	/* START = Get User Data */
	$get_user_data = "SELECT * FROM userData WHERE email=\"$email\" LIMIT 1";
	$get_user_data_result = mysql_query($get_user_data);
	echo mysql_error();
	$userData_row = mysql_fetch_object($get_user_data_result);
	$first = $userData_row->fname;
	$last = $userData_row->lname;
	$zip = $userData_row->zip;
	$gender = $userData_row->gender;
	$day = $userData_row->day;
	$month = $userData_row->month;
	$year = $userData_row->year;
	/* END = Get User Data */
}

?>
<html>
<head>
<meta name="keywords" content="recipe newsletters, free recipe newsletter, recipe newsletter, daily recipe newsletter, crockpot recipe newsletter, slow cooker recipe newsletter, free slow cooker recipes, free crockpot recipes, free crockpot recipe newsletter budget cooking recipes, budget cooking newsletter,party recipes and tips, party recipe newsletter, quick and easy recipe newsletter, quick and easy recipes" />
<meta name="description" content="Looking for recipes and cooking tips? You have come to the right place!" />
<title>Update Information</title>
<style>
body {
	font-family: verdana;
}
</style>
<script>window.scroll(0,0);</script>
</head>
<body>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return check_fields();">
<input type="hidden" name="PHPSESSID" id="PHPSESSID" value="<?php echo session_id(); ?>">
<table border="0" align="center" width="575px" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
	<tr>
		<td colspan="2">
			<a href="check.php?PHPSESSID=<?php echo session_id(); ?>"><b>Go Back to Manage My Newsletters</b></a>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2">
			<?php
				if ($host == 'r4l') { ?>
					<font color="#0F22B0" size="4" style="font-family: arial;">Personalize Your Subscription</font>
				<?php }
				if ($host == 'fitfab') { ?>
					<font color="#EC519D" size="4" style="font-family: arial;">Personalize Your Subscription</font>
				<?php }
				if ($host == 'wim') { ?>
					<font color="#2789BD" size="4" style="font-family: arial;">Personalize Your Subscription</font>
				<?php }
				if ($host == 'sf') { ?>
					<font color="#F99D1C" size="4" style="font-family: arial;">Personalize Your Subscription</font>
				<?php }
			?>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2">
			Update your information to help us select special offers and other mailings for you. 
			<br><br>
			Note: The fields below are <u>optional</u>. After entering your information, press the submit button.
			<br><br>
			Your information will <b>not</b> be shared with any third party.
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td>First Name:</td>
		<td><input type="text" name="first" value="<?php echo $first; ?>"></td>
	</tr>
	<tr>
		<td>Last Name:</td>
		<td><input type="text" name="last" value="<?php echo $last; ?>"></td>
	</tr>
	<tr>
		<td>Zip Code:</td>
		<td><input type="text" name="zip" value="<?php echo $zip; ?>" maxlength="5" size="5"></td>
	</tr>
	<tr>
		<td>Date of Birth:</td>
		<td>
			<select name="month">
				<option value="" <?php if ($month == '') { echo 'selected'; } ?>></option>
				<option value="01" <?php if ($month == '01') { echo 'selected'; } ?>>01</option>
				<option value="02" <?php if ($month == '02') { echo 'selected'; } ?>>02</option>
				<option value="03" <?php if ($month == '03') { echo 'selected'; } ?>>03</option>
				<option value="04" <?php if ($month == '04') { echo 'selected'; } ?>>04</option>
				<option value="05" <?php if ($month == '05') { echo 'selected'; } ?>>05</option>
				<option value="06" <?php if ($month == '06') { echo 'selected'; } ?>>06</option>
				<option value="07" <?php if ($month == '07') { echo 'selected'; } ?>>07</option>
				<option value="08" <?php if ($month == '08') { echo 'selected'; } ?>>08</option>
				<option value="09" <?php if ($month == '09') { echo 'selected'; } ?>>09</option>
				<option value="10" <?php if ($month == '10') { echo 'selected'; } ?>>10</option>
				<option value="11" <?php if ($month == '11') { echo 'selected'; } ?>>11</option>
				<option value="12" <?php if ($month == '12') { echo 'selected'; } ?>>12</option>
			</select> / 
			<select name="day">
				<option value="" <?php if ($day == '') { echo 'selected'; } ?>></option>
				<option value="01" <?php if ($day == '01') { echo 'selected'; } ?>>01</option>
				<option value="02" <?php if ($day == '02') { echo 'selected'; } ?>>02</option>
				<option value="03" <?php if ($day == '03') { echo 'selected'; } ?>>03</option>
				<option value="04" <?php if ($day == '04') { echo 'selected'; } ?>>04</option>
				<option value="05" <?php if ($day == '05') { echo 'selected'; } ?>>05</option>
				<option value="06" <?php if ($day == '06') { echo 'selected'; } ?>>06</option>
				<option value="07" <?php if ($day == '07') { echo 'selected'; } ?>>07</option>
				<option value="08" <?php if ($day == '08') { echo 'selected'; } ?>>08</option>
				<option value="09" <?php if ($day == '09') { echo 'selected'; } ?>>09</option>
				<option value="10" <?php if ($day == '10') { echo 'selected'; } ?>>10</option>
				<option value="11" <?php if ($day == '11') { echo 'selected'; } ?>>11</option>
				<option value="12" <?php if ($day == '12') { echo 'selected'; } ?>>12</option>
				<option value="13" <?php if ($day == '13') { echo 'selected'; } ?>>13</option>
				<option value="14" <?php if ($day == '14') { echo 'selected'; } ?>>14</option>
				<option value="15" <?php if ($day == '15') { echo 'selected'; } ?>>15</option>
				<option value="16" <?php if ($day == '16') { echo 'selected'; } ?>>16</option>
				<option value="17" <?php if ($day == '17') { echo 'selected'; } ?>>17</option>
				<option value="18" <?php if ($day == '18') { echo 'selected'; } ?>>18</option>
				<option value="19" <?php if ($day == '19') { echo 'selected'; } ?>>19</option>
				<option value="20" <?php if ($day == '20') { echo 'selected'; } ?>>20</option>
				<option value="21" <?php if ($day == '21') { echo 'selected'; } ?>>21</option>
				<option value="22" <?php if ($day == '22') { echo 'selected'; } ?>>22</option>
				<option value="23" <?php if ($day == '23') { echo 'selected'; } ?>>23</option>
				<option value="24" <?php if ($day == '24') { echo 'selected'; } ?>>24</option>
				<option value="25" <?php if ($day == '25') { echo 'selected'; } ?>>25</option>
				<option value="26" <?php if ($day == '26') { echo 'selected'; } ?>>26</option>
				<option value="27" <?php if ($day == '27') { echo 'selected'; } ?>>27</option>
				<option value="28" <?php if ($day == '28') { echo 'selected'; } ?>>28</option>
				<option value="29" <?php if ($day == '29') { echo 'selected'; } ?>>29</option>
				<option value="30" <?php if ($day == '30') { echo 'selected'; } ?>>30</option>
				<option value="31" <?php if ($day == '31') { echo 'selected'; } ?>>31</option>
			</select> / 
			<select name="year">
				<option value="" <?php if ($year == '') { echo 'selected'; } ?>></option>
				<option value="1992" <?php if ($year == '1992') { echo 'selected'; } ?>>1992</option>
				<option value="1991" <?php if ($year == '1991') { echo 'selected'; } ?>>1991</option>
				<option value="1990" <?php if ($year == '1990') { echo 'selected'; } ?>>1990</option>
				<option value="1989" <?php if ($year == '1989') { echo 'selected'; } ?>>1989</option>
				<option value="1988" <?php if ($year == '1988') { echo 'selected'; } ?>>1988</option>
				<option value="1987" <?php if ($year == '1987') { echo 'selected'; } ?>>1987</option>
				<option value="1986" <?php if ($year == '1986') { echo 'selected'; } ?>>1986</option>
				<option value="1985" <?php if ($year == '1985') { echo 'selected'; } ?>>1985</option>
				<option value="1984" <?php if ($year == '1984') { echo 'selected'; } ?>>1984</option>
				<option value="1983" <?php if ($year == '1983') { echo 'selected'; } ?>>1983</option>
				<option value="1982" <?php if ($year == '1982') { echo 'selected'; } ?>>1982</option>
				<option value="1981" <?php if ($year == '1981') { echo 'selected'; } ?>>1981</option>
				<option value="1980" <?php if ($year == '1980') { echo 'selected'; } ?>>1980</option>
				<option value="1979" <?php if ($year == '1979') { echo 'selected'; } ?>>1979</option>
				<option value="1978" <?php if ($year == '1978') { echo 'selected'; } ?>>1978</option>
				<option value="1977" <?php if ($year == '1977') { echo 'selected'; } ?>>1977</option>
				<option value="1976" <?php if ($year == '1976') { echo 'selected'; } ?>>1976</option>
				<option value="1975" <?php if ($year == '1975') { echo 'selected'; } ?>>1975</option>
				<option value="1974" <?php if ($year == '1974') { echo 'selected'; } ?>>1974</option>
				<option value="1973" <?php if ($year == '1973') { echo 'selected'; } ?>>1973</option>
				<option value="1972" <?php if ($year == '1972') { echo 'selected'; } ?>>1972</option>
				<option value="1971" <?php if ($year == '1971') { echo 'selected'; } ?>>1971</option>
				<option value="1970" <?php if ($year == '1970') { echo 'selected'; } ?>>1970</option>
				<option value="1969" <?php if ($year == '1969') { echo 'selected'; } ?>>1969</option>
				<option value="1968" <?php if ($year == '1968') { echo 'selected'; } ?>>1968</option>
				<option value="1967" <?php if ($year == '1967') { echo 'selected'; } ?>>1967</option>
				<option value="1966" <?php if ($year == '1966') { echo 'selected'; } ?>>1966</option>
				<option value="1965" <?php if ($year == '1965') { echo 'selected'; } ?>>1965</option>
				<option value="1964" <?php if ($year == '1964') { echo 'selected'; } ?>>1964</option>
				<option value="1963" <?php if ($year == '1963') { echo 'selected'; } ?>>1963</option>
				<option value="1962" <?php if ($year == '1962') { echo 'selected'; } ?>>1962</option>
				<option value="1961" <?php if ($year == '1961') { echo 'selected'; } ?>>1961</option>
				<option value="1960" <?php if ($year == '1960') { echo 'selected'; } ?>>1960</option>
				<option value="1959" <?php if ($year == '1959') { echo 'selected'; } ?>>1959</option>
				<option value="1958" <?php if ($year == '1958') { echo 'selected'; } ?>>1958</option>
				<option value="1957" <?php if ($year == '1957') { echo 'selected'; } ?>>1957</option>
				<option value="1956" <?php if ($year == '1956') { echo 'selected'; } ?>>1956</option>
				<option value="1955" <?php if ($year == '1955') { echo 'selected'; } ?>>1955</option>
				<option value="1954" <?php if ($year == '1954') { echo 'selected'; } ?>>1954</option>
				<option value="1953" <?php if ($year == '1953') { echo 'selected'; } ?>>1953</option>
				<option value="1952" <?php if ($year == '1952') { echo 'selected'; } ?>>1952</option>
				<option value="1951" <?php if ($year == '1951') { echo 'selected'; } ?>>1951</option>
				<option value="1950" <?php if ($year == '1950') { echo 'selected'; } ?>>1950</option>
				<option value="1949" <?php if ($year == '1949') { echo 'selected'; } ?>>1949</option>
				<option value="1948" <?php if ($year == '1948') { echo 'selected'; } ?>>1948</option>
				<option value="1947" <?php if ($year == '1947') { echo 'selected'; } ?>>1947</option>
				<option value="1946" <?php if ($year == '1946') { echo 'selected'; } ?>>1946</option>
				<option value="1945" <?php if ($year == '1945') { echo 'selected'; } ?>>1945</option>
				<option value="1944" <?php if ($year == '1944') { echo 'selected'; } ?>>1944</option>
				<option value="1943" <?php if ($year == '1943') { echo 'selected'; } ?>>1943</option>
				<option value="1942" <?php if ($year == '1942') { echo 'selected'; } ?>>1942</option>
				<option value="1941" <?php if ($year == '1941') { echo 'selected'; } ?>>1941</option>
				<option value="1940" <?php if ($year == '1940') { echo 'selected'; } ?>>1940</option>
				<option value="1939" <?php if ($year == '1939') { echo 'selected'; } ?>>1939</option>
				<option value="1938" <?php if ($year == '1938') { echo 'selected'; } ?>>1938</option>
				<option value="1937" <?php if ($year == '1937') { echo 'selected'; } ?>>1937</option>
				<option value="1936" <?php if ($year == '1936') { echo 'selected'; } ?>>1936</option>
				<option value="1935" <?php if ($year == '1935') { echo 'selected'; } ?>>1935</option>
				<option value="1934" <?php if ($year == '1934') { echo 'selected'; } ?>>1934</option>
				<option value="1933" <?php if ($year == '1933') { echo 'selected'; } ?>>1933</option>
				<option value="1932" <?php if ($year == '1932') { echo 'selected'; } ?>>1932</option>
				<option value="1931" <?php if ($year == '1931') { echo 'selected'; } ?>>1931</option>
				<option value="1930" <?php if ($year == '1930') { echo 'selected'; } ?>>1930</option>
				<option value="1929" <?php if ($year == '1929') { echo 'selected'; } ?>>1929</option>
				<option value="1928" <?php if ($year == '1928') { echo 'selected'; } ?>>1928</option>
				<option value="1927" <?php if ($year == '1927') { echo 'selected'; } ?>>1927</option>
				<option value="1926" <?php if ($year == '1926') { echo 'selected'; } ?>>1926</option>
				<option value="1925" <?php if ($year == '1925') { echo 'selected'; } ?>>1925</option>
				<option value="1924" <?php if ($year == '1924') { echo 'selected'; } ?>>1924</option>
				<option value="1923" <?php if ($year == '1923') { echo 'selected'; } ?>>1923</option>
				<option value="1922" <?php if ($year == '1922') { echo 'selected'; } ?>>1922</option>
				<option value="1921" <?php if ($year == '1921') { echo 'selected'; } ?>>1921</option>
				<option value="1920" <?php if ($year == '1920') { echo 'selected'; } ?>>1920</option>
				<option value="1919" <?php if ($year == '1919') { echo 'selected'; } ?>>1919</option>
				<option value="1918" <?php if ($year == '1918') { echo 'selected'; } ?>>1918</option>
				<option value="1917" <?php if ($year == '1917') { echo 'selected'; } ?>>1917</option>
				<option value="1916" <?php if ($year == '1916') { echo 'selected'; } ?>>1916</option>
				<option value="1915" <?php if ($year == '1915') { echo 'selected'; } ?>>1915</option>
				<option value="1914" <?php if ($year == '1914') { echo 'selected'; } ?>>1914</option>
				<option value="1913" <?php if ($year == '1913') { echo 'selected'; } ?>>1913</option>
				<option value="1912" <?php if ($year == '1912') { echo 'selected'; } ?>>1912</option>
				<option value="1911" <?php if ($year == '1911') { echo 'selected'; } ?>>1911</option>
				<option value="1910" <?php if ($year == '1910') { echo 'selected'; } ?>>1910</option>
				<option value="1909" <?php if ($year == '1909') { echo 'selected'; } ?>>1909</option>
				<option value="1908" <?php if ($year == '1908') { echo 'selected'; } ?>>1908</option>
				<option value="1907" <?php if ($year == '1907') { echo 'selected'; } ?>>1907</option>
				<option value="1906" <?php if ($year == '1906') { echo 'selected'; } ?>>1906</option>
				<option value="1905" <?php if ($year == '1905') { echo 'selected'; } ?>>1905</option>
				<option value="1904" <?php if ($year == '1904') { echo 'selected'; } ?>>1904</option>
				<option value="1903" <?php if ($year == '1903') { echo 'selected'; } ?>>1903</option>
				<option value="1902" <?php if ($year == '1902') { echo 'selected'; } ?>>1902</option>
				<option value="1901" <?php if ($year == '1901') { echo 'selected'; } ?>>1901</option>
				<option value="1900" <?php if ($year == '1900') { echo 'selected'; } ?>>1900</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>Gender:</td>
		<td><select name="gender">
			<option value="" <?php if ($gender == '') { echo 'selected'; } ?>></option>
			<option value="M" <?php if ($gender == 'M') { echo 'selected'; } ?>>Male</option>
			<option value="F" <?php if ($gender == 'F') { echo 'selected'; } ?>>Female</option>
		</select></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="submit" name="submit" value="Update Information">
		</td>
	</tr>
</table>
</form>

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

</body>
</html>
