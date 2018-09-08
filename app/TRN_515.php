
<html>
<head>
    <?php require( 'includes/Site_Header.php'); ?>

	<script>

		   function SetFocus(){
			document.getElementById("Description").focus();
		   }

	  </script>

</head>
<body onLoad="SetFocus()">

<?php 
	require( 'includes/Site_Layout_Start.php');
	
	
	$CategoryTypeList = $db->query("
		SELECT 	*
		FROM	CategoryTypes
	");

	?>

<form name="frmAddAccount" method="post" action="TRN_516.php">

	<table>
	<tr>
		<td colspan="2"><h3>Create a Category</h3></td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td><b>Description:</b></td>
		<td>
			<input type="text" id="Description" name="Description" value="" style="width:200px">
		</td>
	</tr>
		<tr>
		<td><b>Type:</b></td>
		<td>
			<select name="CategoryType" style="width:200px">
				<?php
					$i=0;
					while ($row = $CategoryTypeList->fetch(PDO::FETCH_ASSOC)) {
						$Cur_Value = $row["CategoryCode"];
						$Cur_Display = $row["CategoryTypeName"];
						$CatMult = $row["CatMult"];
						if($CatMult >0){$Cur_Display2 = "Debit";}
						else{$Cur_Display2 = "Credit";}
						echo "<option value=\"$Cur_Value\">$Cur_Display ($Cur_Display2)</option>";
					$i++;	
					}
					?>
			</select>
		</td>
	</tr>
	<tr>
		<td><b>Transaction Summary:</b></td>
		<td>
			<input type="checkbox" name="xSummaryHide" value="true" > Hide From List
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Add Catgeory" style="background-color:white">
	</tr>
</table>
</form>

<?php require( 'includes/Site_Layout_End.php');?>
</body>
</html>