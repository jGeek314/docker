
<?php 
	include('includes/Site_Application.php');
	$xTRN = $_GET['TRN'];
		

	$db->query("
		Update categories
		Set Deleted = 'Y'
		Where CategoryID = $xTRN
		");
	header( 'Location:TRN_500.php' ) ;
?>