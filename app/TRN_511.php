
<?php 
	include('includes/Site_Application.php');
	
	$xTransID = $_POST["xTransID"];
	$Description = $_POST["Description"];
	$CategoryType = $_POST["CategoryType"];
	if(isset($_POST["xSummaryHide"])){
		$xSummaryHide = 'Y';
	}
	else{
		$xSummaryHide = '';
	}

		
	$db->query("
		Update 	categories
		SET 	CategoryDescription = '$Description',
				CategoryType = '$CategoryType',
				SummaryHide = '$xSummaryHide'
		WHERE	CategoryID = $xTransID
		");

	header( 'Location:TRN_500.php' ) ;
?>