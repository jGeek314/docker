<?php require_once('includes/Site_Application.php'); ?>
<html>
<head>
    <title>bills - Auto Generate transactions</title>
    <?php require( 'includes/Site_Header.php'); ?>

</head>
<body >

<?php 
	require( 'includes/Site_Layout_Start.php');
	
	$DefaultName = '';
	$Date = $_POST["BillDate"];
	$DateTime = date('Y-m-d G:i:s',strtotime($Date.' 00:00:00'));
	
	if(isset($_POST['Generatebills'])){
	
		//Submit all the bills that are entry type bills and Autoverify
		$InsertEntrybillsAuto = $db->query("
			Insert Into accounttransactions(EntryTitle,EntryDate,Category,TransactionAmount,Note,VerifiedDate,AccountID)
			Select BillName,'$DateTime',CategoryID,Amount,Note,now(),AccountID
			From bills
			Where billstatus = 'A' and 
				  EntryType = 'Entry' and
				  AutoVerify = 'Y'
		");
		
		//Submit all the bills that are entry type bills and NOT Autoverify
		$InsertEntrybillsNoAuto = $db->query("
			Insert Into accounttransactions(EntryTitle,EntryDate,Category,TransactionAmount,Note,AccountID)
			Select BillName,'$DateTime',CategoryID,Amount,Note,AccountID
			From bills
			Where billstatus = 'A' and 
				  EntryType = 'Entry' and
				  AutoVerify = 'N'
		");
		
		//Submit all 'transfer from (43)' for transfer transactions that are AUTO
		$InsertTransferFromAuto = $db->query("
			Insert Into accounttransactions(EntryTitle,EntryDate,Category,TransactionAmount,Note,VerifiedDate,AccountID)
			Select BillName,'$DateTime',43,Amount,Note,now(),AccountID
			From bills
			Where billstatus = 'A' and 
				  EntryType = 'Transfer' and 
				  AutoVerify = 'Y'
		");
		
		//Submit all 'transfer from (43)' for transfer transactions that are NOT auto
		$InsertTransferFromNoAuto = $db->query("
			Insert Into accounttransactions(EntryTitle,EntryDate,Category,TransactionAmount,Note,AccountID)
			Select BillName,'$DateTime',43,Amount,Note,AccountID
			From bills
			Where billstatus = 'A' and 
				  EntryType = 'Transfer' and 
				  AutoVerify = 'N'
		");	

		//Submit all 'transfer to (44)' for transfer transactions that are AUTO
		$InsertTransferToAuto = $db->query("
			Insert Into accounttransactions(EntryTitle,EntryDate,Category,TransactionAmount,Note,VerifiedDate,AccountID)
			Select BillName,'$DateTime',44,Amount,Note,now(),TransferAccountID
			From bills
			Where billstatus = 'A' and 
				  EntryType = 'Transfer' and 
				  AutoVerify = 'Y'
		");
		
		//Submit all 'transfer to (44)' for transfer transactions that are not AUTO
		$InsertTransferToNoAuto = $db->query("
			Insert Into accounttransactions(EntryTitle,EntryDate,Category,TransactionAmount,Note,AccountID)
			Select BillName,'$DateTime',44,Amount,Note,TransferAccountID
			From bills
			Where billstatus = 'A' and 
				  EntryType = 'Transfer' and 
				  AutoVerify = 'N'
		");
		
	}
		
	
	
	?>
...proccess complete. Do not refresh this page.
<?php require( 'Includes/Site_Layout_End.php');?>


</body>
</html>