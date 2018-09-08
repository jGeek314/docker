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
        <?php 
            $nextAction = 'update'; 
            $buttonLabel = '<i class="fa fa-floppy-o"></i> Save Changes';
        ?>
        
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="editTRN" value="<?= $_POST['editTRN'] ?>" >
        <input type="hidden" name="returnURL" value="<?= $_POST['returnURL'] ?>" >
        
        <div class="row text-right">
            <label class="col-3 col-form-label">&nbsp;</label>
            <div class="col-9">
                <button type="button" class="btn btn btn-outline-danger btn-sm " data-delete-trans="<?=$_POST['editTRN']?>">
                    <i class="fa fa-trash"></i> delete entry
                </button>
            </div>
        </div>
            

        
        <div class="row">
            <label class="col-3 col-form-label">Account:</label>
            <div class="col-9">
                <select name="Account" class="form-control form-control-sm">
                    <?php
                        while($row = $AccountList->fetch(PDO::FETCH_ASSOC)) { ?>
                            <option value="<?= $row['AccountID'] ?>" <?= matchSelect($row["AccountID"],$_POST['Account']) ?> >
                                <?= $row['AccountDescription'] ?>
                            </option>
                    <?php } ?>
                </select>
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
              
                <button type="submit" class="btn btn-primary btn-sm"><?=$buttonLabel?></button>

                <button type="button" class="btn btn  btn-outline-secondary btn-sm pull-right" data-btn-cancel>
                    <i class="fa fa-times"></i> cancel changes
                </button>                
             
            </div>
        </div>
    </form>

   
<br>


</div>

<?php require( 'includes/Site_Layout_End.php');?>
    
    <script>
        var returnURL = '<?= $_POST['returnURL'] ?>';
        
        $("#Description").focus();
        $("[data-datepicker]").datepicker();
        
        $("[data-btn-cancel]").click(function(){
            post(returnURL);
        })
        
        $("[data-delete-trans]").click(function(){
            var $deleteTRN = $(this).attr('data-delete-trans');
            jConfirm('Are you sure you want to delete this entry?', function(){
                post('TRN_105.php',{action: 'delete', deleteTRN: $deleteTRN, returnURL: returnURL });
            });
        })
        
    </script>
    
</body>
</html>