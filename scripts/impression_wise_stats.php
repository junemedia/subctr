<?php

include_once("/home/spatel/config.php");


$yesterday = date("Y-m-d", strtotime("-1 day"));

$query = "SELECT * FROM impression_wise WHERE dateTime BETWEEN '$yesterday 00:00:00' AND '$yesterday 23:59:59'";
$result = mysql_query($query);
echo mysql_error();
$num_of_records = 0;
$accepted_count = 0;
$rejected_count = 0;
$invalid = 0;
$seed = 0;
$trap = 0;
$mole = 0;
while ($row = mysql_fetch_object($result)) {
	if ($row->result == 'invalid') { $invalid++; }
	if ($row->result == 'seed') { $seed++; }
	if ($row->result == 'trap') { $trap++; }
	if ($row->result == 'mole') { $mole++; }
	
	if ($row->isValid == 'Y') {
		$accepted_count++;
	} else {
		$rejected_count++;
	}
		
	$num_of_records++;
}


$report = "<table align='center' border='1' width='30%'>

<tr><td colspan='2'><b>Impression Wise Stats</b></td></tr>
<tr><td colspan='2'>&nbsp;</td></tr>
<tr><td><b>Invalid</b></td><td>$invalid</td></tr>
<tr><td><b>Seed</b></td><td>$seed</td></tr>
<tr><td><b>Mole</b></td><td>$mole</td></tr>
<tr><td><b>Trap</b></td><td>$trap</td></tr>
<tr><td colspan='2'>&nbsp;</td></tr>
<tr><td><b>Rejected</b></td><td>$rejected_count</td></tr>
<tr><td colspan='2'>&nbsp;</td></tr>
<tr><td><b>Accepted</b></td><td>$accepted_count</td></tr>
<tr><td colspan='2'>&nbsp;</td></tr>
<tr><td><b>Total</b></td><td>$num_of_records</td></tr>


</table>";

$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:admin@myfree.com\r\n";

mail('williamg@junemedia.com',"Real-Time Impression Wise Lookup Stats - $yesterday",$report,$sHeaders);

?>
