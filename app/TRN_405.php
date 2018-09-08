<?php 
	include('includes/Site_Application.php');
	
	$xToAccount = $_POST["xToAccount"];
	$xFromAccount = $_POST["xFromAccount"];
	$Description = $_POST["Description"];
	$Amount = $_POST["Amount"];
	$Date = $_POST["Date"];
        
        $Note = !isset($_POST['Note']) ? "" : $_POST['Note'];
        
	$DateTime = date('Y-m-d G:i:s',strtotime($Date.' 00:00:00'));

	$xAutoVerifyDate = "NULL";
	if (isset($_POST['xAutoVerify'])){
		$xAutoVerifyDate = "now()";
	}
		
	$db->query("
		INSERT INTO accounttransactions (
				AccountID,
				EntryTitle, 
				Category,
				TransactionAmount,
				EntryDate,
				Note,
				VerifiedDate
				)
		VALUES(	
				$xToAccount,
				'$Description',
				44,
				$Amount,
				'$DateTime',
				'',
				$xAutoVerifyDate
				)
		");
	
	$db->query("
		INSERT INTO accounttransactions (
				AccountID,
				EntryTitle, 
				Category,
				TransactionAmount,
				EntryDate,
				Note,
				VerifiedDate
				)
		VALUES(	
				$xFromAccount,
				'$Description',
				43,
				$Amount,
				'$DateTime',
				'',
				$xAutoVerifyDate
				)
		");

	header( 'Location:TRN_400.php' ) ;
?>