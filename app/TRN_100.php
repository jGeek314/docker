<?php
    require_once('includes/Site_Application.php');
    
    
    
    require('TRN_105.php'); //Process form... must do this before continuing.




	$AccountList = $db->query("
		SELECT 	*
		FROM	accounts
		WHERE	AccountDeleted <> 'Y'
	");
	
	$categories = $db->query("
		SELECT 	*
		FROM	categories
		WHERE	Deleted <> 'Y'
		ORDER BY CategoryDescription
	");
	
	$accounttransactions = $db->query("
		SELECT 	*, UNIX_TIMESTAMP(EntryDate) as phpEntryDate
		FROM	accounttransactionsExtended
		WHERE	AccountID = $defaultaccount
		ORDER BY EntryDate Desc
		LIMIT 50
	");
	$AccountTOTAL = $db->query("
		SELECT 	Sum(Amount) as accountTotal
		FROM	accounttransactionsExtended
		WHERE	AccountID = $defaultaccount
	");

    
?>

<html>
<head>
    <title>Checkbook Transactions (Edit Transaction)</title>
    <?php require( 'includes/Site_Header.php'); ?>
</head>
<body>

<?php require( 'includes/Site_Layout_Start.php');?>

    <?php if( sizeof($errors) > 0 ){ ?>
        
        <div class="alert alert-danger">
            <b> The following error(s) exist on your form.  Please correct the issue(s) and try again.</b>
            <ul>
                <?php foreach($errors as $error){ ?>
                    <li> <?= $error; ?> </li>
                <?php } ?>
            </ul>
        </div>
    
    <?php } ?>
     
    <form name="frmNewEntry" id="frmNewEntry" method="post" style="max-width:600px">
        <input type="hidden" name="returnURL" value="TRN_100.php">
        <input type="hidden" name="action" value="insert">
        
        <div class="row">
            <label class="col-3 col-form-label">Account:</label>
            <div class="col-9">
                <input type="hidden" name="Account" value="<?=$_POST['Account']?>">
                <input type="text"   name="AccountDescr" readonly class="form-control-plaintext" value="<?= $defaultaccountDescr ?>">
            </div>
        </div>
        <hr>

        <div class="row">
            <label class="col-3 col-form-label">Description:</label>
            <div class="col-9">
                <input type="text" id="Description" name="Description" value="<?=$_POST['Description']?>" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row">
            <label class="col-3 col-form-label">Type:</label>
            <div class="col-9">
               <select name="Category" class="form-control form-control-sm">
                    <?php
                        while($row = $categories->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?= $row['CategoryID'] ?>" <?= matchSelect($row['CategoryID'], $_POST['Category']) ?>>
                                <?= $row['CategoryDescription'] ?>
                            </option>
                    <?php } ?>
                </select>
            </div>
            
        </div>
        
        <div class="row">
            <label class="col-3 col-form-label">Amount:</label>
            <div class="col-9">
                <input type="text" name="Amount" value="<?=$_POST['Amount']?>" class="form-control form-control-sm">
            </div>
        </div>
        
        <div class="row">
            <label class="col-3 col-form-label">Date:</label>
            <div class="col-9">
                <div class="input-group date" >
                    <input type="text" name="Date" value="<?=$_POST['Date']?>" class="form-control form-control-sm" data-datepicker readonly>
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <label class="col-3 col-form-label">Note:</label>
            <div class="col-9">
                <input type="text" name="Note" value="<?=$_POST['Note']?>" class="form-control form-control-sm">
            </div>
        </div>
        
        <div class="row">
            <div class="col-9 offset-3">
                <button type="submit" class="btn btn-primary btn-sm">Submit Entry</button>
            </div>
        </div>
    </form>

   
<br>


<h6 class="small text-muted">Recent Transactions</h6>
    
<table class="table">
    
 
    <thead>
        <tr>
            <th style="cursor: pointer;">Date</th>
            <th>Description</th>
            <th class="d-none d-md-table-cell">Category</th>
            <th class="d-none d-md-table-cell">Note</th>
            <th style="text-align:right">Amount</th>
            <th>&nbsp;</th>
        </tr>

    </thead>


        <?php

        while($row = $accounttransactions->fetch(PDO::FETCH_ASSOC)) {
            $TranDate=$row["phpEntryDate"];
            $TranID=$row["TransactionID"];
            $TranTitle=$row["EntryTitle"];
            $TranCat=$row["CategoryDescription"];
            $TranAmount=Number_format($row["Amount"],2, '.', '');
            $TranNote=$row["Note"];
            
            $TranStyle = "";
            if($TranAmount < 0){
                $TranAmount = number_format($TranAmount * -1.00,2, '.', '');
                $TranStyle = "color:red";
            }
            
            $rowStyle = '';
            if(isset($editTRN) && $editTRN == $TranID){
                $rowStyle = "bg-warning";
            } else if(isset($_SESSION['HighlightTrans']) && $_SESSION['HighlightTrans'] == $TranID){
                $rowStyle = "bg-success transition-ease";
            }
            ?>
            <tr class="<?= $rowStyle ?>">
                <td><?= $fn->date('m/d/Y',$TranDate) ?></td>
                <td><?= $TranTitle?></td>
                <td class="d-none d-md-table-cell"><?= $TranCat ?></td>
                <td class="d-none d-md-table-cell"><?= $TranNote ?></td>
                <td style="<?= $TranStyle ?>" align="right">$<?= $TranAmount ?>&nbsp;&nbsp;</td>
                <td>
                    <?php if( in_array($_POST['action'], ['insert','new']) ) { ?>
                    <button type="submit" class="btn btn-link" data-edit-trans="<?= $TranID ?>">
                        <i class="fa fa-pencil"></i>
                    </button>
                    <?php } ?>
                </td>
            </tr>
        <?php } //end while loop ?>
	</table>

    <br>

</div>
    
<?php require( 'includes/Site_Layout_End.php');?>
    <script>
        $("#Description").focus();
        $("[data-datepicker]").datepicker();
        
        
        $("[data-btn-cancel]").click(function(){
            post('TRN_100.php');
        })
        
        $("[data-delete-trans]").click(function(){
            var $deleteTRN = $(this).attr('data-delete-trans');
            jConfirm('Are you sure you want to delete this entry?', function(){
                post('TRN_105.php',{action: 'delete', deleteTRN: $deleteTRN});
            });
        })
        Delete = function(frmID){
                if(confirm("Are you sure you want to delete this transaction?")){
                    $("#" + frmID).submit();
                }
        };
  
        $("tr.bg-success").removeClass("bg-success");
        
    </script>
    
</body>
</html>