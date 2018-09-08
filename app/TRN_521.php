
<?php 
	include('functions.php');
	$xTRN = $_GET['TRN'];
		

	$db->query("
		Update categories
		Set Deleted = ''
		Where CategoryID = $xTRN
		");
	header( 'Location:TRN_500.php' ) ;
?>