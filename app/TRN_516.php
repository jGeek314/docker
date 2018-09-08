
<?php 
	include('includes/Site_Application.php');
	
	$Description = $_POST["Description"];
	$CategoryType = $_POST["CategoryType"];
	if(isset($_POST["xSummaryHide"])){
		$xSummaryHide = 'Y';
	}
	else{
		$xSummaryHide = '';
	}
		
	$db->query("
		Insert Into	categories (CategoryDescription, CategoryType, SummaryHide)
		Values ('$Description','$CategoryType','$xSummaryHide')
		");

	header( 'Location:TRN_500.php' ) ;
?>