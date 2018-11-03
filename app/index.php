<?php require_once('includes/Site_Application.php'); ?>

<html>
<head>
    <title>Checkbook - Accounts Summary</title>
    <?php require( 'includes/Site_Header.php'); ?>

</head>
<body>

<?php 
	require( 'includes/Site_Layout_Start.php');
	
	$db = checkbookConnect();
	
	$AccountDefault = $db->query("
		SELECT 	AccountID
		FROM	defaultaccount
	");
	
	$categories = $db->query("
		SELECT 	*
		FROM	categories
		ORDER BY CategoryDescription
	");
	
	while($row = $AccountDefault->fetch(PDO::FETCH_ASSOC)) {
		$defaultaccount = $row['AccountID'];
	}


    $AccountTOTALs = query("
		SELECT		accounts.AccountID, accounts.AccountDescription, a.AccountTotal, b.VerifiedTotal,  accounttypes.AccountTypeDescription,                   accounts.Summary
        
		FROM		accounts
		LEFT JOIN	(SELECT 	Sum(Amount) as accountTotal, accountid
					FROM		accounttransactionsextended
					GROUP BY 	AccountID) as a ON accounts.AccountID = a.AccountID
                    
		LEFT JOIN	(SELECT		Sum(Amount) as VerifiedTotal, AccountID
					FROM		accounttransactionsextended
					WHERE		VerifiedDate is not null
					GROUP BY	AccountID) as b ON accounts.AccountID = b.AccountID
                LEFT JOIN       accounttypes on accounts.AccountType = accounttypes.AccountTypeCode
        left join   accounts as linked on accounts.linkedaccountid = linked.accountid
     
		WHERE 		accounts.AccountDeleted <> 'Y'
		ORDER BY 	accounts.summary desc, linked.AccountType, accounts.AccountDescription
	");
    
	$AccountGroups = query("
		SELECT		accounts.AccountID, accounts.AccountDescription, a.AccountTotal, b.VerifiedTotal,  accounttypes.AccountTypeDescription,                   linked.Summary
        
		FROM		accounts
		LEFT JOIN	(SELECT 	Sum(Amount) as accountTotal, accountid
					FROM		accounttransactionsextended
					GROUP BY 	AccountID) as a ON accounts.AccountID = a.AccountID
                    
		LEFT JOIN	(SELECT		Sum(Amount) as VerifiedTotal, linkedAccountID as AccountID
					FROM		accounttransactionsextended 
					WHERE		VerifiedDate is not null
                    and         AccountID in (Select AccountID From accounts where AccountDeleted <> 'Y')
					GROUP BY	linkedAccountID) as b ON accounts.AccountID = b.AccountID
                LEFT JOIN       accounttypes on accounts.AccountType = accounttypes.AccountTypeCode
        left join   accounts as linked on accounts.linkedaccountid = linked.accountid
     
		WHERE 		accounts.AccountDeleted <> 'Y'
        and         accounts.AccountID in (Select linkedaccountid from accounts where accountdeleted <> 'Y' and linkedaccountid <> accountid)
		ORDER BY 	linked.summary desc, linked.AccountType, accounts.AccountDescription
	");
    /*
	$AccountGroupTOTAL2 = $db->query("
		SELECT		accounttypes.AccountTypeCode, accounttypes.AccountTypeDescription, 
					a.AccountTotal, b.VerifiedTotal, c.TransCount
		FROM		accounttypes
		LEFT JOIN	(SELECT 	Sum(Amount) as accountTotal, AccountType
					FROM		accounttransactionsextended
					WHERE		AccountID IN (Select AccountID From accounts Where AccountDeleted <> 'Y')
					GROUP BY 	AccountType) as a ON accounttypes.AccountTypeCode = a.AccountType
		LEFT JOIN	(SELECT		Sum(Amount) as VerifiedTotal, AccountType
					FROM		accounttransactionsextended
					WHERE		VerifiedDate is not null
					AND		AccountID IN (Select AccountID From accounts Where AccountDeleted <> 'Y')
					GROUP BY	AccountType) as b ON accounttypes.AccountTypeCode = b.AccountType
		LEFT JOIN	(SELECT		count(AccountType) as TransCount, AccountType
					FROM		accounttransactionsextended
					WHERE		AccountID IN (Select AccountID From accounts Where AccountDeleted <> 'Y')
					GROUP BY	AccountType) as c ON accounttypes.AccountTypeCode = c.AccountType
		ORDER BY 	TransCount desc
	");
     */   
    /*
    $accountsummaryTOTALs = $db->query("
		SELECT		accounts.AccountID, accounts.AccountDescription, a.AccountTotal, b.VerifiedTotal,  accounttypes.AccountTypeDescription
		FROM		accounts
                LEFT JOIN       accounttypes on accounts.AccountType = accounttypes.AccountTypeCode
		LEFT JOIN	(SELECT 	Sum(Amount) as accountTotal, accountid
					FROM		accounttransactionsextended
					GROUP BY 	AccountID) as a ON accounts.AccountID = a.AccountID
		LEFT JOIN	(SELECT		Sum(Amount) as VerifiedTotal, AccountID
					FROM		accounttransactionsextended
					WHERE		VerifiedDate is not Null
					GROUP BY	AccountID) as b ON accounts.AccountID = b.AccountID
                
                Where           accounts.Summary = 'Y'
		ORDER BY 	AccountType, accounts.AccountDescription
	");
       */ 
        
	?>
    
    
    
    <div class="alert alert-success">
        <span class="badge badge-light summary">
            $<?=$accountsummary?>
        </span>
        <span>- Accounts Summary</span>
     
    </div>

<div class="container-fluid d-none d-md-block">
    <div class="row list-group-header">
        <div class="col">
            Account          
        </div>
        <div class="col text-right ">
            Total
        </div>
        <div class="col text-right">
            Verified
        </div>
        <div class="col"></div>
    </div>
</div>

<ul class="list-group">
     <?php 
        $GrandTotal = 0.00;
        foreach($AccountTOTALs as $row) {

            $AccountID = $row['AccountID'];
            $AccountDescription = $row['AccountDescription'];
            $AccountTotal = number_format($row['AccountTotal'],2);
            $AccountVerified = number_format($row['VerifiedTotal'],2);  
            $GrandTotal = $GrandTotal + $row['AccountTotal']; 
            $icon = getAccountIcon($row['AccountTypeDescription']);
            ?>
                
                <li class="list-group-item">
                    <div class="d-block d-md-none">
                        <span class="btn btn-link"  data-accountid="<?= $AccountID ?>">
                            <?= $icon." ".$AccountDescription ?>
                        </span>
                        <div class="pull-right text-right">
                            <?php if($row['Summary'] == 'Y'){ ?>
                                <span class="badge badge-success">
                                    $<?= $AccountTotal ?>
                                </span>
                            <?php } else {?>
                                <span class="badge badge-info">
                                    $<?= $AccountTotal ?>
                                </span>
                            <?php } ?>
                            <br>
                            <span class="badge badge-light text-muted">
                                $<?= $AccountVerified ?>
                            </span>
                        </div>
                    </div>
                    <div class="container-fluid d-none d-md-block">
                        <div class="row">
                            <div class="col">
                                <span class="btn btn-link"  data-accountid="<?= $AccountID ?>">
                                    <?= $icon." ".$AccountDescription ?>
                                </span>
                            </div>
                            <div class="col text-right ">
                                 <?php if($row['Summary'] == 'Y'){ ?>
                                    <span class="badge badge-success">
                                        $<?= $AccountTotal ?>
                                    </span>
                                <?php } else {?>
                                    <span class="badge badge-info">
                                        $<?= $AccountTotal ?>
                                    </span>
                                <?php } ?>
                            </div>
                            <div class="col text-right">
                                $<?= $AccountVerified ?>
                            </div>
                            <div class="col text-right">
                                <button class="btn btn-link btn-sm" data-edit-trn="<?= $AccountID ?>" >
                                    <i class="fa fa-pencil"></i> edit
                                </button>
                            </div>
                        </div>
                    </div>
                    

                </li>
        <?php } ?>  
</ul>
<br>
<button type="button" class="btn btn-primary btn-sm pull-right d-none d-md-inline" onclick="New()"><i class="fa fa-plus"></i> Create New Account</button>
<div class="clearfix"></div>


    <div class="container-fluid ">
    <div class="row list-group-header">
        <div class="col">
            Account Groups          
        </div>
        <div class="col text-right d-none d-md-block">
            Total
        </div>
        <div class="col text-right d-none d-md-block">
            Verified
        </div>
        <div class="col"></div>
    </div>
</div>

<ul class="list-group">
      <?php
            $GrandTotal = 0.00;
            foreach($AccountGroups as $row) {
                $AccountDescription = $row["AccountDescription"];
                $AccountTotal = number_format($row['AccountTotal'],2); 
                $AccountVerified = number_format($row['VerifiedTotal'],2); 
                $GrandTotal = $GrandTotal + $row['AccountTotal']; 
                $icon = getAccountIcon($row['AccountTypeDescription']); ?>
                <li class="list-group-item">
                    <div class="d-block d-md-none">
                        <?= $icon." ".$AccountDescription ?>
                        <div class="pull-right text-right">
                            <span class="badge badge-secondary">
                                $<?= $AccountTotal ?>
                            </span>
                            <br>
                            <span class="badge badge-light text-muted">
                                $<?= $AccountVerified ?>
                            </span>
                        </div>
                    </div>
                    <div class="container-fluid d-none d-md-block">
                        <div class="row">
                            <div class="col">
                                <?= $icon." ".$AccountDescription ?>
                            </div>
                            <div class="col text-right ">
                                $<?= $AccountTotal ?>
                            </div>
                            <div class="col text-right">
                                $<?= $AccountVerified ?>
                            </div>
                            <div class="col"></div>
                        </div>
                    </div>
                    

                </li>
        <?php } ?>  
</ul>
    
<!---    
<div class="container-fluid ">
    <div class="row list-group-header">
        <div class="col">
            Account Groups          
        </div>
        <div class="col text-right d-none d-md-block">
            Total
        </div>
        <div class="col text-right d-none d-md-block">
            Verified
        </div>
        <div class="col"></div>
    </div>
</div>

<ul class="list-group">
      ?php
            $GrandTotal = 0.00;
            while($row = $AccountGroupTOTALs->fetch(PDO::FETCH_ASSOC)) {
                $AccountDescription = $row["AccountTypeDescription"];
                $AccountTotal = number_format($row['AccountTotal'],2); 
                $AccountVerified = number_format($row['VerifiedTotal'],2); 
                $GrandTotal = $GrandTotal + $row['AccountTotal']; 
                $icon = getAccountIcon($row['AccountTypeDescription']); ?>
                <li class="list-group-item">
                    <div class="d-block d-md-none">
                        ?= $icon." ".$AccountDescription ?>
                        <div class="pull-right text-right">
                            <span class="badge badge-secondary">
                                $?= $AccountTotal ?>
                            </span>
                            <br>
                            <span class="badge badge-light text-muted">
                                $?= $AccountVerified ?>
                            </span>
                        </div>
                    </div>
                    <div class="container-fluid d-none d-md-block">
                        <div class="row">
                            <div class="col">
                                ?= $icon." ".$AccountDescription ?>
                            </div>
                            <div class="col text-right ">
                                $?= $AccountTotal ?>
                            </div>
                            <div class="col text-right">
                                $?= $AccountVerified ?>
                            </div>
                            <div class="col"></div>
                        </div>
                    </div>
                    

                </li>
        ?php } ?>  
</ul>
--->
    
<?php require( 'includes/Site_Layout_End.php');?>
    <script>
        $("[data-accountid]").click(function(){
            var curAccountID = $(this).attr('data-accountid');
            post('TRN_001.php',{
                ReturnPage: 'TRN_100.php',
                AccountID: curAccountID
            });
        });
        
        $("[data-edit-trn]").click(function(){
            post('TRN_015.php', {
                AccountID: $(this).data('edit-trn')
            });
        });
        
        function New(){
            location.href = "TRN_015.php";
        }

    </script>
</body>
</html>