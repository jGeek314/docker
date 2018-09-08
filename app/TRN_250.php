<?php
    require_once('includes/Site_Application.php');
    
    $formFields = [
        'action' =>        ['default' => 'new'],
        'Account' =>       ['label' => 'Account',      'required' => true,     'default' => $defaultaccount],
        'Description' =>   ['label' => 'Description',  'required' => true],
        'Category' =>      ['label' => 'Category',     'required' => false],
        'Amount' =>        ['label' => 'Amount',       'required' => true,     'validate' => 'number'],
        'Date' =>          ['label' => 'Date',         'required' => true,     'default' => date('m/d/Y'), 'validate' => 'date'],
        'Note' =>          ['label' => 'Note',         'required' => false]
    ];

    $dot = dot($formFields);
    
    //set default form variables
    foreach($formFields as $field => $properties){
        if( !isset($_POST[$field])){
            $_POST[$field] = $dot->get($field.'.default', '');
        }
    }

    $errors = [];

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
    
?>

<html>
<head>
    <title>Checkbook Transactions (Edit Transaction)</title>
    <?php require( 'includes/Site_Header.php'); ?>
</head>
<body>

<?php require( 'includes/Site_Layout_Start.php');?>

    <h3><i class="fa fa-search"></i> Search Account Transactions</h3>
    
    <p class="text-info">
        Search through the checkbook transactions for specific entries.
    </p>
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
                <select name="Account" class="form-control form-control-sm">
                    <option value=""></option>
                    <?php
                        while($row = $AccountList->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?= $row['AccountID'] ?>" <?= matchSelect($row["AccountID"],$_POST['Account']) ?> >
                                <?= $row['AccountDescription'] ?>
                            </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row">
            <label class="col-3 col-form-label">Keywords:</label>
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
                <div class="input-group input-group-sm" >
                     <div class="input-group-btn">
                        <input type="hidden" name="AmountType" value="">
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Greater than
                        </button>
                        <div class="dropdown-menu">
                          <a class="dropdown-item" href="#">Equal to</a>
                          <a class="dropdown-item" href="#">Greater than</a>
                          <a class="dropdown-item" href="#">Less than</a>
                        </div>
                      </div>
                    <input type="text" name="Amount" value="<?=$_POST['Amount']?>" class="form-control form-control-sm">
                    
                </div>
            </div>
 
        </div>
        
        <div class="row">
            <label class="col-3 col-form-label">Date:</label>
            <div class="col-4">
                <div class="input-group date" >
                    <input type="text" name="Date" value="<?=$_POST['Date']?>" class="form-control form-control-sm" data-datepicker readonly>
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="input-group date" >
                    <input type="text" name="Date" value="<?=$_POST['Date']?>" class="form-control form-control-sm" data-datepicker readonly>
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-9 offset-3">
                <button type="submit" class="btn btn-primary btn-sm">Submit Entry</button>
            </div>
        </div>
    </form>

   
<br>


<h6 class="small text-muted">Search Results</h6>
    
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
    

    
</body>
</html>