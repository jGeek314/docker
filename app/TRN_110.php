<?php 
    require( 'includes/Site_Application.php');
	
    $xTRN = $_POST['TRN'];
    $returnURL = !isset($_POST['returnURL']) ? "TRN_100.php" : $_POST['returnURL'];
    
    $TransactionDetails = $db->query("
            SELECT	*, UNIX_TIMESTAMP(EntryDate) as phpEntryDate
            FROM	accounttransactions
            WHERE	TransactionID = $xTRN
    ");
    while($row = $TransactionDetails->fetch(PDO::FETCH_ASSOC)) {
            $defaultaccount = $row["AccountID"];
            $DefaultTitle = $row["EntryTitle"];
            $DefaultCategory = $row["Category"];
            $DefaultAmount = Number_format($row["TransactionAmount"],2);
            $DefaultDate = date('m/d/Y',$row["phpEntryDate"]);
            $DefaultNote = $row["Note"];
            $xTransID = $row["TransactionID"];
    }
	
    $AccountList = $db->query("
            SELECT 	*
            FROM	accounts
            WHERE       AccountDeleted <> 'Y'
    ");

    $categories = $db->query("
            SELECT 	*
            FROM	categories
            WHERE 	Deleted <> 'Y'
            or		CategoryID = $DefaultCategory
            ORDER BY CategoryDescription
    ");
?>

<form name="frmChangeAccount" id="frmNewEntry" method="post" action="TRN_105.php">
     <input type="hidden" name="action" value="update">
     <input type="hidden" name="xTransID" value="<?= $xTRN ?>">
     <input type="hidden" name="returnURL" value="<?= $returnURL ?>">
     <table>
         <tr>
             <td><b>Account:</b></td>
             <td>
                 <select name="Account">
                     <?php
                         while($row = $AccountList->fetch(PDO::FETCH_ASSOC)) { ?>
                             <option value="<?= $row['AccountID'] ?>" <?= matchSelect($row["AccountID"],$defaultaccount) ?> >
                                 <?= $row['AccountDescription'] ?>
                             </option>
                         <?php } ?>
                 </select>
             </td>
         </tr>
         <tr><td colspan="2"><hr></td>
         <tr>
             <td><b>Description:</b></td>
             <td>
                 <input type="text" id="Description" name="Description" value="<?= $DefaultTitle ?>">
             </td>
         </tr>
         <tr>
             <td><b>Type:</b></td>
             <td>
                 <select name="Category">
                     <?php
                         while($row = $categories->fetch(PDO::FETCH_ASSOC)) { ?>
                             <option value="<?= $row['CategoryID'] ?>" <?= matchSelect($row['CategoryID'],$DefaultCategory) ?> >
                                 <?= $row['CategoryDescription'] ?>
                             </option>
                         <?php } ?>
                 </select>
             </td>
         </tr>
         <tr>
             <td><b>Amount:</b></td>
             <td><input type="text" name="Amount" value="<?= $DefaultAmount ?>"></td>
         </tr>
         <tr>
             <td><b>Date:</b></td>
             <td><input type="text" name="Date" value="<?= $DefaultDate ?>"></td>
         </tr>
         <tr>
             <td><b>Note:</b></td>
             <td><input type="text" name="Note" value="<?= $DefaultNote ?>"></td>
         </tr>
         <tr>
             <td>&nbsp;</td>
             <td><button type="submit">Save Changes</button></td>
         </tr>
     </table>
 </form>
