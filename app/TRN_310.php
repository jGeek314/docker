<?php require_once('includes/Site_Application.php'); ?>
<html>
<head>
    <?php require( 'includes/Site_Header.php'); ?>
    <script>
        $(function(){
            $("#TransactionForm").dialog({
                height:400,
                width:400,
                title:"Edit Transaction",
                autoOpen:false
            });
        });
        
        openTransaction = function(frmID){
            
            $("#TransactionForm").dialog('open');
            $("#TransactionForm").dialog('widget').position(
                    {my:"left+50 top+50", at:"left top", of:window});
            editTransaction(frmID);
        }
        Delete = function(frmID){
                if(confirm("Are you sure you want to delete this transaction?")){
                    $("#" + frmID).submit();
                }
        };
    </script>
</head>
<body>
    <div id="TransactionForm"></div>
<?php 
	require( 'includes/Site_Layout_Start.php');

	$catID = $_GET['CategoryID']; 
	
	$Activeaccounts = $db->query("
		SELECT  AccountID
		FROM	accounts
		WHERE 	AccountDeleted <> 'Y'
		");
	
	$ActiveAccountList = "(";
	while($row = $Activeaccounts->fetch(PDO::FETCH_ASSOC)){
            $ActiveAccountList .= $row["AccountID"].",";
	}
	$ActiveAccountList .= '0)';
        
	$CatSummary = $db->query("
		SELECT *, UNIX_TIMESTAMP(EntryDate) as phpEntryDate
		FROM	accounttransactionsextended
		WHERE	Category = $catID
		AND	AccountID IN $ActiveAccountList
		ORDER BY 	EntryDate desc
		");
        
	//new dBug($catID);	
?>
    
<?php $colOutput = formatTableColumns([50,100,200,200,150,100,50,50]); ?>

<table class="zebra-table" style="width:100px">
    
    <?= $colOutput ?>
    <thead>
        <tr>
            <th>Trn #</th>
            <th>Date</th>
            <th>Description</th>
            <th>Category</th>
            <th>Note</th>
            <th style="text-align:right">Amount</th>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
        </tr>

    </thead>
    <tbody class="wrap">
        
        <?php
                while($row = $CatSummary->fetch(PDO::FETCH_ASSOC)) {
                    $TranDate=$row["phpEntryDate"];
                    $TranID=$row["TransactionID"];
                    $TranTitle=$row["EntryTitle"];
                    $TranCat=$row["CategoryDescription"];
                    $TranAmount=Number_format($row["Amount"],2);
                    $TranNote=$row["Note"];
                    if($TranAmount < 0){
                        $TranAmount = number_format($TranAmount * -1.00,2);
                        $TranStyle = "color:red";
                    }
                    else{
                        $TranStyle = "";   
                    }
                    ?>
                    <tr>
                        <td><?= $TranID ?></td>
                        <td><?= $fn->date('m/d/Y',$TranDate) ?></td>
                        <td><?= $TranTitle?></td>
                        <td><?= $TranCat ?></td>
                        <td><?= $TranNote ?></td>
                        <td style="<?= $TranStyle ?>" align="right"><?= $TranAmount ?>&nbsp;&nbsp;</td>
                        <td>
                            <img onClick="openTransaction('frmDetails<?= $TranID ?>')" class="active" src="./images/edit.png"/>                           
                            <form id="frmDetails<?= $TranID ?>">
                                <input type="hidden" name="action"      value="modify">
                                <input type="hidden" name="TRN"      value="<?= $TranID ?>">
                                <input type="hidden" name="returnURL" value="TRN_310.php?CategoryID=<?= $catID ?>">
                            </form>
                        </td>
                        <td>
                            <img src="./images/delete.png" onclick="Delete('delTran<?= $TranID ?>')" class="active"/>
                            <form id="delTran<?= $TranID ?>" method="POST" action="TRN_105.php">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="TransID" value="<?= $TranID ?>">
                                <input type="hidden" name="returnURL" value="TRN_310.php?CategoryID=<?= $catID ?>">
                            </form>
                        </td>
                    </tr>
                <?php } //end while loop ?>
    </tbody>

</table>
<?php require( 'includes/Site_Layout_End.php');?>
</body>
</html>