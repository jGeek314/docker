
<html>
<head>
    <?php require( 'includes/Site_Header.php'); ?>

	<script language="JavaScript1.2" type="text/JavaScript">

		   function SetFocus(){
			document.getElementById("Description").focus();
		   }

	  </script>

</head>
<body onLoad="SetFocus()">

<?php 
	require( 'includes/Site_Layout_Start.php');
	
	$xTRN = $_GET['TRN'];
	$CategoryDetails = $db->query("
		SELECT	*
		FROM	categories
		WHERE	CategoryID = $xTRN
	");
	while($row = $CategoryDetails->fetch(PDO::FETCH_ASSOC)){
            $DefaultTitle = $row["CategoryDescription"];
            $DefaultType = $row["CategoryType"];
            $DefaultSummaryHide = $row["SummaryHide"];
            $xTransID = $row["CategoryID"]; 
        }
	
	
	$DefaultCatMult = $db->query("
		SELECT 	CatMult
		FROM	CategoryTypes
		WHERE	CategoryCode = '$DefaultType'
	")->fetchColumn();
		
	$CategoryTypeList = $db->query("
		SELECT 	*
		FROM	CategoryTypes
		WHERE	CatMult = $DefaultCatMult
	");
	
	
	$Cur_Title=$DefaultTitle;
	?>

<form name="frmChangeCategory" method="post" action="TRN_511.php">
	<input type="hidden" name="xTransID" value="<?php echo $xTransID?>">
	<table>
	<tr>
		<td colspan="2"><h3><?php echo "Edit Category: $Cur_Title";?></h3></td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td><b>Category Name:</b></td>
		<td>
			<input type="text" id="Description" name="Description" value="<?php echo $DefaultTitle?>" style="width:200px">
		</td>
	</tr>
		<tr>
		<td><b>Category Type:</b></td>
		<td>
                    <select name="CategoryType" style="width:200px">
                        <?php while ($row =  $CategoryTypeList->fetch(PDO::FETCH_ASSOC)) {
                                        $Cur_Value = $row["CategoryCode"];
                                        $Cur_Display = $row["CategoryTypeName"];
                                        if($Cur_Value == $DefaultType){
                                                echo "<option value=\"$Cur_Value\" selected=\"true\">$Cur_Display</option>";
                                        }
                                        else{
                                                echo "<option value=\"$Cur_Value\">$Cur_Display</option>";
                                        }	
                                }
                                ?>
                    </select>
		</td>
	</tr>
	<tr>
		<td><b>Transaction Summary:</b></td>
		<td>
			<input type="checkbox" name="xSummaryHide" value="true" <?php if($DefaultSummaryHide=='Y'){echo "checked='checked'";}?>> Hide From List
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Save Changes" style="background-color:white">
	</tr>
</table>
</form>

<?php require( 'includes/Site_Layout_End.php');?>
</body>
</html>