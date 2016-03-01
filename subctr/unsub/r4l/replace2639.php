<?php

include_once("../../config.php");

if ($_POST['submit'] == 'Submit') {
	
	$fileLog = fopen('emails.txt','a+');
	
	$limit = " LIMIT 0,5";
	//Get the emails that need to be updated
	$check_query = "SELECT email,listid FROM joinEmailUnsub WHERE subcampid =2639".$limit;
	$check_query_result = mysql_query($check_query);
	
	while($row = mysql_fetch_array($check_query_result, MYSQL_ASSOC))
	{
		//Get subcampid from joinEmailSub table
		$getQuery = "SELECT email,listid,subcampid FROM joinEmailSub WHERE email='".$row['email']."' AND listid=".$row['listid'];
		$get_result = mysql_query($getQuery);
		$subRow = mysql_fetch_array($get_result, MYSQL_ASSOC);
		
		//Update subcampid in joinEmailUnsub table
		$unsubQuery = "UPDATE joinEmailUnsub SET subcampid = ".$subRow['subcampid']." WHERE email='".$row['email']."' AND listid=".$row['listid'];
		$unsub_result = mysql_query($unsubQuery);
		echo mysql_error();
		
		//Update subcampid in  joinEmailUnsubDetails table
		//Actually there is no subcampid with 2639 in joinEmailUnsubDetails
		//We don't need to update it
		/*$detailQuery = "UPDATE  joinEmailUnsubDetails SET first_subcampid = ".$subRow['subcampid']." WHERE email='".$row['email']."' AND listid=".$row['listid'];
		$detail_result = mysql_query($detailQuery);
		echo mysql_error();*/
		
		//Update subcampid in campaigner table
		$campaignerQuery = "UPDATE campaigner SET subcampid = ".$subRow['subcampid']." WHERE email='".$row['email']."' AND oldListId=".$row['listid'];
		$campaigner_result = mysql_query($campaignerQuery);
		echo mysql_error();		
		
		//Update subcampid in arcamaxNewLog table
		$unsubQuery = "UPDATE arcamaxNewLog SET subcampid = ".$subRow['subcampid']." WHERE email='".$row['email']."' AND listid=".$row['listid'];
		$unsub_result = mysql_query($unsubQuery);
		echo mysql_error();
		
		if($fileLog)
		{
			fwrite($fileLog,$subRow['email']."\r\n");
		}
	}	
	fclose($fileLog);
}
?>
<html>
<head>
<title>Replace 2639</title>

</head>
<body>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']?>" onsubmit="return check_fields();">	
	<table width="500" align="center" style="font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;">
		<tr>
			<td align="center">
			<input type="submit" name="submit" value="Submit">
			</td>
		</tr>
	</table>
</form>
</body>
</html>
