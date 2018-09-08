<?php require_once('includes/Site_Application.php'); ?>
<html>
<head>
    <title>bills</title>
       
    <?php require( 'includes/Site_Header.php'); ?>
   
</head>
<body >

<?php 
	require( 'includes/Site_Layout_Start.php');
	
	$DefaultName = '';
	$DefaultType = '';
	$DefaultCategory = '';
	$DefaultAmount = '';
	$DefaultNote = '';
	$defaultaccountID = '';
	$DefaultTransferAccount = '';
	$DefaultAutoVerify = '';
	$DefaultAutoVerifyChecked = '';
	
	$DefaultAction = "New";
	
	if(isset($_POST['submitAction'])){
	
		if($_POST['submitAction'] != 'DeleteBill'){
		
			//if type is transfer account then use it, else set it to 0
			$TransAccount = 0;
			$BillCategory = 0;
			if($_POST['BillType'] == "Transfer"){
				$TransAccount = $_POST['BillTransferAccount'];
			}
			else{
				$BillCategory = $_POST['BillCategory'];
			}
			
			//if the auto verify is set, then set the flag
			$AutoVerify = 'N';
			if(isset($_POST['BillAutoVerify'])){
				if($_POST['BillAutoVerify'] == 'Y'){
					$AutoVerify = 'Y';
					$DefaultAutoVerifyChecked = 'checked="checked"';
				}
			}
		
		}	
		
		if($_POST['submitAction'] == 'AddBill'){
		
			//make sure a bill with the same name is currently being used
			$CheckBillKnt = $db->query("
				Select Count(*)
				From bills
				Where BillName = '".$_POST['BillName']."'
				and billstatus = 'A'
				and Note = '".$_POST['BillNote']."'
				and	BillID = (select max(BillID) from bills)
			")->fetchColumn();
			
			if($CheckBillKnt == 0){
				$CreateBill = $db->query("
					Insert Into bills(BillName,EntryType,CategoryID,Amount,Note,AccountID,TransferAccountID,AutoVerify)
					Values(
							 '".$_POST['BillName']."'
							,'".$_POST['BillType']."'
							, ".$BillCategory."
							, ".$_POST['BillAmount']."
							,'".$_POST['BillNote']."'
							, ".$_POST['BillAccountID']."
							, ".$TransAccount."
							,'".$AutoVerify."')
				");
			}
			else{
				$ActionErrorMessage = "You already have a bill called '".$_POST['BillName']."'... did ya hit the refresh button dummy?";
			}
		
		}
		
		if($_POST['submitAction'] == 'EditBill'){
			
			$DefaultAction = "Edit";
			
			$DefaultName = $_POST['BillName'];
			$DefaultType = $_POST['BillType'];
			$DefaultCategory = $_POST['BillCategory'];
			$DefaultAmount = $_POST['BillAmount'];
			$DefaultNote = $_POST['BillNote'];
			$defaultaccountID = $_POST['BillAccountID'];
			$DefaultTransferAccount = $_POST['BillTransferAccount'];
			$DefaultAutoVerify = $_POST['BillAutoVerify'];
	
		}
		
		if($_POST['submitAction'] == 'DeleteBill'){
			
			$DeleteBill = $db->query("
				Update  bills
				Set		billstatus = 'I'
				Where	BillID = ".$_POST['BillID']
			);
		}
		
		if($_POST['submitAction'] == 'SaveEdit'){
			
			$UpdateBill = $db->query("						
				Update   bills
                                Set      BillName = '".$_POST['BillName']."'
                                        ,EntryType = '".$_POST['BillType']."'
                                        ,CategoryID = ".$BillCategory."
                                        ,Amount = ".$_POST['BillAmount']."
                                        ,Note = '".$_POST['BillNote']."'
                                        ,AccountID = ".$_POST['BillAccountID']."
                                        ,TransferAccountID = ".$TransAccount."
                                        ,AutoVerify = '".$AutoVerify."'
				Where	 BillID = ".$_POST['BillID']
			);
		}
	}
	$bills = $db->query("
		SELECT 	bills.*, categories.CategoryDescription, accounts.AccountDescription, t.AccountDescription as transferDescription
		FROM	bills
		LEFT JOIN categories ON bills.CategoryID = categories.CategoryID
		left join accounts on bills.accountid = accounts.accountid
		left join accounts t on bills.transferAccountID = t.Accountid
		where	billstatus = 'A'
		ORDER BY BillName
	");
	
	$Deletedbills = $db->query("
		SELECT 	bills.*, categories.CategoryDescription, accounts.AccountDescription
		FROM	bills
		LEFT JOIN categories ON bills.CategoryID = categories.CategoryID
		left join accounts on bills.accountid = accounts.accountid
		where	billstatus <> 'A'
		ORDER BY BillName
	");
	
	$categories = $db->query("
		SELECT 	CategoryDescription, CategoryID
		FROM	categories
		Where	Deleted <> 'Y'
		ORDER BY CategoryDescription
	");

	$accounts = $db->query("
		SELECT 	AccountDescription, accounts.AccountID, coalesce(AccountDebits,0) as AccountDebits, coalesce(AccountCredits,0) as AccountCredits
		FROM	accounts
                left join   (Select AccountID, sum(Amount) as AccountDebits
                             From bills
                             Where billstatus = 'A'
                             Group by AccountID) Debits
                             on accounts.AccountID = Debits.AccountID
                left join   (Select TransferAccountID as AccountID, -1 * sum(Amount) as AccountCredits
                             From bills
                             Where EntryType = 'Transfer' and billstatus = 'A'
                             Group by TransferAccountID) Credits
                             on accounts.AccountID = Credits.AccountID
		Where	AccountDeleted <> 'Y'
		ORDER BY AccountType, AccountDescription 
	");
        $AccountRows = $accounts->fetchAll(PDO::FETCH_ASSOC);
	
	
	?>

<div style="width:900px;text-align:left">
	<b>Manage bills</b>
	<hr/>
	<form name="frmBillDetails" id="frmBillDetails" method="POST">
		
		<table class="table-form">
			<tr>
				<td>Bill Name:</td>
				<td>
					<input type="text" name="BillName" id="inputBillName" value="<?=$DefaultName;?>"/>
				</td>
			</tr>
			<tr>
				<td>Type:</td>
				<td>
					<select name="BillType" id="BillTypeToggle" onChange="toggleControls()">
						<option value="Entry" <?= matchSelect("Entry", $DefaultType)?> >Entry</option>
						<option value="Transfer" <?= matchSelect("Transfer", $DefaultType)?> >Transfer</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Category:</td>
				<td>
					<select name="BillCategory" id="inputBillCategory">
                                            
						<?php while($row = $categories->fetch(PDO::FETCH_ASSOC)) { ?>
							<option value="<?=$row["CategoryID"]?>" <?= matchSelect($row["CategoryID"], $DefaultCategory)?> >
								<?= $row["CategoryDescription"]?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Transfer Account:</td>
				<td>
					<select name="BillTransferAccount" id="inputTransferAccount">
                                                <?php foreach($AccountRows as $row) { ?>
							<option value="<?= $row["AccountID"]?>" <?= matchSelect($row["AccountID"], $DefaultTransferAccount)?>>
								<?= $row["AccountDescription"] ?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<?php if($DefaultAction == "New"){?>
							
						<input type="hidden" name="submitAction" value="AddBill">
						<button type="submit">Add New Bill</button>
						
					<?php }else{ ?>
						<input type="hidden" name="BillID" value="<?=$_POST['BillID']?>">
						<input type="hidden" name="submitAction" value="SaveEdit">
						<button type="submit">Save Changes</button>
						
					<?php } ?>
					
					<?php if(isset($ActionErrorMessage)){ ?>
					
						<div class="errorwrap">
							<label class="error"><?=$ActionErrorMessage?></label>
						</div>
						
					<?php } ?>
				</td>
			</tr>
		</table>
		<table class="table-form">
                    <tr>
                        <td>Amount:</td>
                        <td><input type="text" name="BillAmount" value="<?=$DefaultAmount?>" /></td>
                    </tr>
                    <tr>
                        <td>Note:</td>
                        <td><input type="text" name="BillNote" value="<?=$DefaultNote;?>" /></td>
                    </tr>
                    <tr>
                        <td>Paying Account:</td>
                        <td>
                            <select name="BillAccountID">
                                <?php foreach($AccountRows as $row) { ?>
                                    <option value="<?= $row["AccountID"]?>" <?= matchSelect($row["AccountID"], $defaultaccountID)?>>
                                        <?= $row["AccountDescription"] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="checkbox" value="Y" name="BillAutoVerify" <?=$DefaultAutoVerifyChecked?> />
                            Auto Verify transaction?
                        </td>
                    </tr>
		</table>
	</form>
<hr/>
<table style="width:100%" cellpadding="5" cellspacing="0">
	<tr class="table-header">
		<td>Bill</td>
		<td>Amount &nbsp;&nbsp;&nbsp;</td>
		<td>Category &nbsp;&nbsp;&nbsp;</td>
		<td>From Account &nbsp;&nbsp;&nbsp;</td>
		<td>Auto Verify? &nbsp;&nbsp;&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	
	<?php 
		$i=1; 
		$BillTotal = 0.00;
                while($row = $bills->fetch(PDO::FETCH_ASSOC)) {
                    $BillTotal = $BillTotal + $row["Amount"];
                    if($i%2==0){
                        $rowClass = 'even';
                    }
                    else{
                        $rowClass = 'odd';
                    }
		?>
			<tr class="<?= $rowClass ?> table-row">
				<td> 
                                    <?= $row["BillName"] ?> 
                                    <form name="frmEditBill<?= $row["BillID"] ?>" id="frmEditBill<?= $row["BillID"] ?>" method="POST">
                                            <input type="hidden" name="submitAction" value="EditBill">
                                            <input type="hidden" name="BillID" value="<?= $row["BillID"] ?>">
                                            <input type="hidden" name="BillName" value="<?= $row["BillName"] ?>">
                                            <input type="hidden" name="BillType" value="<?= $row["EntryType"] ?>">
                                            <input type="hidden" name="BillCategory" value="<?= $row["CategoryID"] ?>">
                                            <input type="hidden" name="BillAmount" value="<?= $row["Amount"] ?>">
                                            <input type="hidden" name="BillNote" value="<?= $row["Note"] ?>">
                                            <input type="hidden" name="BillAccountID" value="<?= $row["AccountID"] ?>">
                                            <input type="hidden" name="BillTransferAccount" value="<?= $row["TransferAccountID"] ?>">
                                            <input type="hidden" name="BillAutoVerify" value="<?= $row["AutoVerify"] ?>">
                                    </form>
                                    <form name="frmDeleteBill<?= $row["BillID"] ?>" id="frmDeleteBill<?= $row["BillID"] ?>" method="POST">
                                            <input type="hidden" name="submitAction" value="DeleteBill">
                                            <input type="hidden" name="BillID" value="<?= $row["BillID"] ?>">
                                    </form>
				</td>
				<td align="right">
					<?= $row["Amount"] ?> 
				</td>
				<td>
					<?php 
                                            if($row["EntryType"] == "Entry"){
                                                echo $row["CategoryDescription"];
                                            }
                                            else{
                                                echo "Transfer into <i>".$row["transferDescription"]."</i>";
                                            }
					?> 
				</td>
				
				<td>
					<?= $row["AccountDescription"]?> 
				</td>
				<td>
					<?= $row["AutoVerify"]?> 
				</td>
				<td align="right">
					<img border="0"src="./images/edit.png" onclick="editBill('frmEditBill<?= $row["BillID"] ?>')" style="cursor: pointer;"/>
				</td>
				<td align="right">
					<img border="0"src="./images/delete.png" onclick="deleteBill('frmDeleteBill<?= $row["BillID"] ?>')" style="cursor: pointer;"/>
				</td>
			</tr>
                <?php }?>
	<tr class="table-footer">
		<td><button type="button" style="background-color:white" onClick="$('#frmGenTran').submit()">Generate Transactions</button></td>
		<td align="right"><?php //echo number_format($BillTotal,2); ?></td>
		<td align="right">Bill Date:</td>
		<td>
			<form name="frmGenerateTransactions" id="frmGenTran" action="TRN_701.php" method="POST">
				<input type="hidden" name="Generatebills" value="true">
				<input type="text" name="BillDate" value="<?= date("m/01/y")?>" style="width:75px">
			</form>
		</td>
		<td></td>
		<td></td>
		<td></td>
	</tr>	
	
</table>
<table style="float:right;width:500px;">

    <tr>
        <th></th>
        <th class="right">Debits</th>
        <th class="right">Credits</th>
        <th class="right">Total</th>
    </tr> 
    <?php foreach($AccountRows as $row){ 
        $Debits = $row['AccountDebits']*-1;
        $Credits = $row['AccountCredits']*-1;
        $TotalBilled = $Debits + $Credits;
        if($TotalBilled != 0){
        ?>
    <tr>
        <td><?= $row['AccountDescription'] ?></td>
        <td class="right">
            <?php
                if($Debits != 0){
                    echo formatPrice($Debits);
                }else{
                    echo "---";
                }
            ?>
        </td>
        <td class="right">
            <?php
                if($Credits != 0){
                    echo formatPrice($Credits);
                }else{
                    echo "---";
                }
            ?>
        </td>
        <td class="right">
            <?= formatPrice($TotalBilled)  ?>
        </td>
    </tr>
    <?php }} ?>

    
</table>
</div>
<div id="AutoSubmit"></div>
<?php require( 'Includes/Site_Layout_End.php');?>
    
     <script>

        $(function(){

            toggleControls();

            $("#frmBillDetails").validate({

                invalidHandler: function(form){

                },
                errorPlacement: function(label, element) {
                    label.addClass('errorwrap');
                    label.insertAfter(element);
                },
                wrapper: "div",

                rules: {
                    BillName: {
                        required: true,
                        minlength: 2
                    },
                    BillCategory: {
                        required: function(element) {
                                return $("#BillTypeToggle").val() == "Entry";
                        },
                        range: [2,1000]
                    },
                    BillAmount: {
                        required: true,
                        number:true
                    }
                },

                messages: {
                    BillName: "DOH!",
                    BillCategory: "OOPS",
                    BillAmount: "...umm???"
                }
            });
        });

        editBill = function(frmID){
            $("#" + frmID).submit();
        }

        deleteBill = function(frmID){
            if(confirm('Warning!!! Hitting delete does not make your bills magically disappear... are you sure you want to continue and you\'re not being dumb???')){
                $("#" + frmID).submit();
            }
        }

        toggleControls = function(){
            var toggle = $("#BillTypeToggle").val();
            if(toggle == "Entry"){
                $("#inputBillCategory").prop( "disabled", false);
                $("#inputTransferAccount").prop( "disabled", true);
            }
            else{
                $("#inputBillCategory").prop( "disabled", true);
                $("#inputTransferAccount").prop( "disabled", false);
            }
        }

    </script>
    
</body>
</html>