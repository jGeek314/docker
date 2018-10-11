<?php require_once('includes/Site_Application.php'); ?>
<html>
<head>
    <?php require( 'includes/Site_Header.php'); ?>
    <style>
        [data-trn] > td,
        [data-trn] > th{
            background-color:white;
            transition: all 0.5s ease;
        }
        
        [data-trn].highlight > td,
        [data-trn].highlight > th{
            background-color:#c3e6cb;
        }
        
        [data-trn].primary > td,
        [data-trn].primary > th{
            background-color:rgb(104, 176, 255);
            color:black !important;
        }
        [data-trn].primary .text-danger,
        [data-trn].primary .btn-link{
            color:black !important;
        }
        
        .summary{
            padding:5px;
        }
        
        tr.default .btn-danger,
        tr.highlight .btn-primary{
            display:none;
        }
    </style>
   

</head>
<body>

<?php 
	require( 'includes/Site_Layout_Start.php');
	$AccountList = $db->query("
		SELECT 	*
		FROM	accounts
		WHERE 	AccountDeleted <> 'Y'
	");
	
	$defaultaccount = $AccountDefault = $db->query("
		SELECT 	AccountID
		FROM	defaultaccount
	")->fetchColumn();
	
	$categories = $db->query("
		SELECT 	*
		FROM	categories
		ORDER BY CategoryDescription
	");
	
	$accounttransactions = $db->query("
		SELECT 	*, UNIX_TIMESTAMP(EntryDate) as phpEntryDate, EntryDate,
                If( VerifiedDate is null, 1, 2) as vSort
		FROM	accounttransactionsextended
		WHERE	AccountID = $defaultaccount
		ORDER BY vSort, EntryDate desc
        limit 50
	");
	
	$RecentlyVerified = $db->query("
		SELECT 	*, UNIX_TIMESTAMP(accounttransactionsextended.EntryDate) as phpEntryDate, accounttransactions.EntryDate, accounttransactions.VerifiedDate
		FROM	accounttransactionsextended
		LEFT JOIN accounttransactions on accounttransactionsextended.TransactionID = accounttransactions.TransactionID
		WHERE	accounttransactionsextended.AccountID = $defaultaccount
		AND		accounttransactionsextended.VerifiedDate is not null
		ORDER BY accounttransactions.EntryDate desc
		limit 40
	");
    
    $LastVerified = $db->query("
    
       SELECT  TransactionID
       FROM	   accounttransactions
       Where   VerifiedDate = (Select Max(VerifiedDate) 
                              From accounttransactions
                              Where AccountID = $defaultaccount)
       AND     AccountID = $defaultaccount
       Union All
       Select 0 as TransactionID
       order by transactionid desc


	")->fetchAll()[0];
    
    $LastVerifiedID = $LastVerified['TransactionID'];
    
	?>


<table class="table table-condensed">

    <thead>
        <tr>  
            <th>Date</th>
            <th>Description</th>
            <th class="d-none d-md-table-cell">Category</th>
            <th class="d-none d-md-table-cell">Note</th>
            <th class="right">Amount</th>
            <th></th>
            <th class="d-none d-md-table-cell"></th>
        </tr>
    </thead>
        <?php
            while($row = $accounttransactions->fetch(PDO::FETCH_ASSOC)){
                $foundTransactions = true;
                $TranDate = date("m/d/Y",$row["phpEntryDate"]);
                $TranID = $row["TransactionID"];
                $TranTitle = $row["EntryTitle"];
                $TranCat = $row["CategoryDescription"];
                $TranAmount = Number_format($row["Amount"],2);
                $TranNote = $row["Note"];
                
                $statusClass = 'default';
                if (strlen($row["VerifiedDate"]) > 0){
                    $statusClass = 'highlight';
                }
                if ($TranID == $LastVerifiedID){
                    $statusClass = 'highlight primary';
                }
                
            ?>
                <tr data-trn="<?=$TranID?>"  class="<?=$statusClass?>">
                    <td><?= $TranDate ?></td>
                    <td><?= $TranTitle ?></td>
                    <td class="d-none d-md-table-cell"><?= $TranCat ?></td>
                    <td class="d-none d-md-table-cell"><?= $TranNote ?></td>
                    <td class="right"><?= formatPrice($TranAmount) ?></td>
                    <td class="right">
                        <button class="btn btn-link" data-edit-trans="<?= $TranID ?>">
                            <i class="fa fa-pencil"></i>
                        </button>
                    </td>
                    
                    <td class="right d-none d-md-table-cell">
                        <button type="button" class="btn btn-sm btn-primary" data-trn="<?= $TranID ?>">clear</button>
                        <button type="button" class="btn btn-sm btn-danger" data-trn="<?= $TranID ?>">undo</button>
                    </td>     
                </tr>
            <?php } 
            if(!$foundTransactions){ ?>
                <tr>
                    <td colspan="4">No Unverified Transactions Found</td>
                    <td colspan="3" class=" d-none d-md-table-cell"></td>
                </tr>
            <?php } ?>
  
</table>
<div id="TransactionForm"></div>

    <?php require( 'includes/Site_Layout_End.php');?>

     <script>

        $("#TransactionForm").dialog({
            height:400,
            width:400,
            title:"Edit Transaction",
            autoOpen:false
        });
  
        
        openTransaction = function(frmID){
            
            $("#TransactionForm").dialog('open');
            $("#TransactionForm").dialog('widget').position(
                    {my:"left+50 top+50", at:"left top", of:window});
            editTransaction(frmID);
        }
        
        clear = function(trnid){
            var $tr = $("tr[data-trn='" + trnid  +"']");
            $tr.addClass('highlight');  
            $tr.removeClass('default');
            updateSummary('clear',trnid);
        }
        
        undo = function(trnid){
            var $tr = $("tr[data-trn='" + trnid  +"']");
            $tr.addClass('default');  
            $tr.removeClass('highlight');
            updateSummary('undo',trnid);
        }
        
        $("tr[data-trn]").swipe(function(e,touch){
            var trnID = $(this).data('trn');

            switch(touch.direction){
                case 'right':
                    clear(trnID); break;
                case 'left':
                    undo(trnID);  break;
            }

        });
        
        $(".btn-primary[data-trn]").click(function(){
           clear($(this).data('trn'));
        });
         
        $(".btn-danger[data-trn]").click(function(){
           undo($(this).data('trn'));
        });
         
        var updateSummary = function(action,trnID){
            postJSON('TRN_205.php',{
                action: action,
                trnID: trnID
            }, function(data){
                console.log(data);
                $("#defaultBalance").html("$" + data.DefaultBalance);
                $("#defaultVerified").html("$" + data.LinkVerified);
                
                $("[data-trn]").removeClass('primary');
                $("[data-trn='" + data.LastVerified + "']").addClass('primary');
            });
        }
            
    </script>
</body>

</html>
