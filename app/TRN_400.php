<?php require_once('includes/Site_Application.php'); ?>
<html>
<head>
    <?php require( 'includes/Site_Header.php'); ?>

</head>
<body>

<?php 
	require( 'includes/Site_Layout_Start.php');
	
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
	
	$accounts = $AccountList = $db->query("
		SELECT 	AccountId, AccountDescription, sum(amount) as Balance
		FROM	accounttransactionsextended
		WHERE	AccountID IN $ActiveAccountList
		GROUP BY  AccountID,AccountDescription
	")->fetchAll();

	?>
<h3><i class="fa fa-exchange"></i> Transfer Between accounts</h3>
    
<br>

<form name="frmTransfer" method="post" action="TRN_405.php">
    <input type="hidden" name="ReturnPage" value="TRN_200.php">
    
    <div class="row">
        <div class="col-md-6">
            <p class="text-info">
                Transfer funds between accounts (note, this can be used for making credit card payments).  When transfering to a "virtual" account
                check the "auto verify" option.
            </p>
            
            <div class="row">
                <label class="col-4 col-form-label">From Account:</label>
                <div class="col-8">
                    <select name="xFromAccount" id="xFromAccount" class="form-control form-control-sm" >
                        <?php foreach ($accounts as $row){ ?>
                        <option value="<?= $row['AccountID'] ?>">
                            <?= $row["AccountDescription"]."... ".number_format($row['Balance'],2) ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <label class="col-4 col-form-label">To Account:</label>
                <div class="col-8">
                    <select name="xToAccount" id="xToAccount" class="form-control form-control-sm" >
                        <?php foreach ($accounts as $row){ ?>
                        <option value="<?= $row['AccountID'] ?>">
                            <?= $row["AccountDescription"]."... ".number_format($row['Balance'],2) ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <label class="col-4 col-form-label">Amount:</label>
                <div class="col-8">
                    <input type="text" value="0.00" name="Amount" class="form-control form-control-sm" >
                </div>
            </div>

            <div class="row">
                <label class="col-4 col-form-label">Description:</label>
                <div class="col-8">
                    <input type="text" value="" name="Description" class="form-control form-control-sm" >
                </div>
            </div>

            <div class="row">
                <label class="col-4 col-form-label">Date:</label>
                <div class="col-8">
                    <div class="input-group date" >
                        <input type="text" name="Date" value="<?=date('m/d/Y')?>" class="form-control form-control-sm" data-datepicker readonly>
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <label class="col-4 col-form-label">Auto Verfiy:</label>
                <div class="col-8">
                    <input type="checkbox" value="Yes" name="xAutoVerify"> <span class="text-info">automatically mark as verified?</span>
                </div>
            </div>

            <div class="row">
                <label class="col-4 col-form-label"></label>
                <div class="col-8">
                    <button type="submit" class="btn btn-primary">
                        Submit Transfer
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>



<?php require( 'includes/Site_Layout_End.php');?>
    <script>
        $("[data-datepicker]").datepicker();
    </script>
</body>
</html>