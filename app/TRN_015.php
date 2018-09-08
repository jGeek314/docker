<?php

    include('includes/Site_Application.php');
    
    if ( isset($_POST["action"])){
    
        $Description = formParam('Description');
        $AccountType = formParam('AccountType');
        
        switch($_POST["action"]){

            case "update":

                $AccountID = $_POST["AccountID"];
                if($AccountType == 'VR'){
                    $linkedAccount = $_POST["linkedAccount"];
                } else {
                    $linkedAccount = $AccountID;
                }
                $xSummary = 'N';
                if(isset($_POST["Summary"])){
                    $xSummary = 'Y';
                }

                updateQuery("
                    Update 	accounts
                    SET 	AccountDescription = '$Description',
                            AccountType = '$AccountType',
                            Summary = '$xSummary',
                            linkedAccountID = $linkedAccount
                    WHERE	AccountID = $AccountID
                    ");
                
                break;

            case "insert":

                $db->query("
                    Insert Into	accounts (AccountDescription, AccountType)
                    Values ('$Description','$AccountType')
                    ");

                $AccountID = $db->lastInsertId();
                
                if($AccountType == 'VR'){
                    $linkedAccount = $_POST["linkedAccount"];
                } else {
                    $linkedAccount = $AccountID;
                }
                
                $db->query("
                    update accounts Set linkedAccountID = $linkedAccount 
                    Where AccountID = $AccountID;
                    
                    INSERT INTO accounttransactions (
                        AccountID,
                        EntryTitle, 
                        Category,
                        TransactionAmount,
                        EntryDate,
                        Note)
                    VALUES(	
                        $AccountID,
                        'Initialize Account',
                        1,
                        0,
                        curdate(),
                        '')
                    ");
                
                break;

            case "delete":

                $AccountID = $_POST['AccountID'];
                
                updateQuery("
                    Update accounts
                    Set AccountDeleted = 'Y'
                    Where AccountID = $AccountID;
                    
                    Update accounts
                    Set SlushAccount = 0, 
                        SlushTransaction = 0
                    Where   SlushAccount = $AccountID;
                    ");

                break;

            default:

                exit("no action submitted... aborted");
        }
    
        // for insert and update operations, update the slush account information
        if (matchList("insert,update",$_POST['action'])){
            if(isset($_POST["Slushy"]) && $AccountType == 'VR'){
                updateQuery("
                    Update accounts
                    Set SlushAccount = $AccountID
                    where AccountID = $linkedAccount;");
                
                insertQuery("
                    INSERT INTO accounttransactions (
                            AccountID,
                            EntryTitle, 
                            Category,
                            TransactionAmount,
                            EntryDate,
                            VerifiedDate,
                            Note)
                    VALUES(	
                            $linkedAccount,
                            'Slush transactions for $Description Account',
                            43,
                            0,
                            '2000-01-01 00:00:00',
                            '2000-01-01 00:00:00',
                            'Auto transfer from rounding');
                    ");
                
                $slushTransactionID = $db->lastInsertId();
                
                updateQuery("
                    Update accounts
                    Set SlushTransaction = $slushTransactionID
                    where AccountId = $linkedAccount");
                
            } else {
                updateQuery("
                    Update accounts
                    Set SlushAccount = 0
                    where AccountID = $linkedAccount;
                    ");
            }
        }
        
	   header( 'Location:index.php' );
    }

    $DefaultTitle = '';
    $DefaultType = '';
    $Defaultlinked = '';
    $DefaultSlush = '';
    $SubmitAction = 'insert';
    $AccountID = '';
    
    if (isset($_POST['AccountID'])){
        $AccountID = $_POST['AccountID'];
        
        $AccountDetails = query("
            SELECT	accounts.*, b.Balance, coalesce(accountslinked.AccountID,0) as IsSlushy
            FROM	accounts
            left join (Select AccountID, Sum(amount) as Balance 
                       from   accounttransactionsExtended 
                       where  AccountID = $AccountID
                       group by AccountID) b on accounts.AccountID = b.AccountID
            left join accounts as accountslinked on accounts.linkedAccountID = accountslinked.AccountID
            and accountslinked.SlushAccount = accounts.AccountID
            WHERE	accounts.AccountID = $AccountID
        ");

        foreach($AccountDetails as $row) {
            $DefaultTitle = $row['AccountDescription'];
            $DefaultType = $row['AccountType'];
            $DefaultSummary = $row['Summary'];
            $DefaultBalance = $row['Balance'];
            $Defaultlinked = $row['LinkedAccountID'];
            $DefaultSlush = 'N';
            if ($row['IsSlushy'] > 0){
                $DefaultSlush = 'Y';
            }
        }
        
        $SubmitAction = 'update';
    }

    //Get Account Types selection list
	$accounttypes = query("
		SELECT 	*
		FROM	accounttypes
	");

    //Get non-virtual accounts for virtual account creation
    $Standardaccounts = query("
        Select *
        From accounts
        Where AccountType <> 'VR'
    ");


?>   

<html>
<head>
    <title>accounts - Add New accounts</title>
    <?php require( 'includes/Site_Header.php'); ?>

	

</head>
<body>

<?php require( 'includes/Site_Layout_Start.php'); ?>

    
    
    
    <?php if(strlen($AccountID) > 0) { ?>
        <h4>Edit Account: <?php echo "$DefaultTitle ... \$$DefaultBalance";?></h4>
    <?php } else { ?>
        <h4>Create a New Account</h4>
    <?php } ?>
    
<form name="frmAddAccount" method="post" class="form-horizontal">
    
    <input type="hidden" name="action" value="<?= $SubmitAction ?>">
    <input type="hidden" name="AccountID" value="<?= $AccountID ?>">
    
    <div class="row">
        <label class="col-2 col-form-label">Description:</label>
        <div class="col-5">
            <input type="text" name="Description" class="form-control form-control-sm" value="<?= $DefaultTitle ?>">
        </div>
    </div>
    
    <div class="row">
        <label class="col-2 col-form-label">Type:</label>
        <div class="col-5">
            <select name="AccountType" class="form-control form-control-sm">
                <?php
                    foreach ($accounttypes as  $row) { ?>
                        <option value="<?= $row["AccountTypeCode"] ?>" <?= matchSelect($row["AccountTypeCode"], $DefaultType) ?> >
                            <?= $row["AccountTypeDescription"] ?>
                        </option>
                <?php } ?>
            </select>
        </div>
    </div>
    
    <div id="linkedRow" <?= matchnodisplay($DefaultType,'VR','hidden')?>>
        <div class="row">
            <label class="col-2 col-form-label">linked Account:</label>
            <div class="col-5">
                <select name="linkedAccount" class="form-control form-control-sm">
                    <?php
                        foreach ($Standardaccounts as  $row) { ?>
                            <option value="<?= $row["AccountID"] ?>" <?= matchSelect($row["AccountID"], $Defaultlinked) ?> >
                                <?= $row["AccountDescription"] ?>
                            </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="row">
            <label class="col-2"></label>
            <div class="col-5">
                <input type="checkbox" name="Slushy" value="Y" <?= optionCheck('Y',$DefaultSlush)?> > Is this a slush for the linked account?
            </div>
        </div>
    </div>
    
    <?php if(strlen($AccountID) > 0) { ?>
        <div class="row" style="padding-top:10px;padding-bottom:10px">
            <label class="col-2"></label>
            <div class="col-5">
                <input type="checkbox" name="Summary" value="Y" <?= optionCheck('Y',$DefaultSummary)?> > Include in checkbook summary?
            </div>
        </div>
        <div class="row">
            <label class="col-2"></label>
            <div class="col-5">
                <button type="submit" class="btn btn-sm btn-primary">
                    Update Account
                </button>
                <button type="button" class="btn btn-danger btn-sm pull-right" data-delete="<?=$AccountID?>">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </div>
        </div>
    <?php } else { ?>
        <div class="row">
            <label class="col-2"></label>
            <div class="col-5">
                <button type="submit" class="btn btn-sm btn-primary">
                    Add Account
                </button>
            </div>
        </div>
    <?php } ?>
    

</form>

<?php require( 'includes/Site_Layout_End.php');?>
    
<script>

   $("[name='Description']").focus();

    $("[data-delete]").click(function(){
       jConfirm('Hey stupid! Are you sure you want to delete this account!?', function(){
           post('TRN_015.php',{
               action: 'delete',
               AccountID: '<?= $AccountID ?>',
               
           })
       }) ;
    });

    $('[name="AccountType"]').change(function(){
        console.log($(this).val());
       if($(this).val() == 'VR'){
           console.log($("#linkedRow").length);
           $("#linkedRow").removeAttr('hidden');
       } else {
           $("#linkedRow").attr('hidden','');
       }
    });
</script>
    
</body>
</html>