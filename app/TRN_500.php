
<html>
<head>
    <?php require( 'includes/Site_Header.php'); ?>
	
	      <script
               type="text/javascript"
               language="javascript">
               function SetFocus(){
				document.getElementById("Account").focus();
			   }
			   function Delete(ID){
				if(confirm("Are you sure you want to delete this Category?")) location.href = "TRN_520.php?TRN=" + ID;
				}
				
				function Edit(ID){
					location.href = "TRN_510.php?TRN=" + ID;
				}
				
				function Add(ID){
					location.href = "TRN_521.php?TRN=" + ID;
				}
				function New(){
					location.href = "TRN_515.php";
				}
          </script>

</head>
<body onLoad="SetFocus()">

<?php 
	require( 'includes/Site_Layout_Start.php');
	$categories = $db->query("
		SELECT 	categories.*, b.TransCount, CategoryTypes.CategoryTypeName, CategoryTypes.CatMult, UNIX_TIMESTAMP(b.lastused) as LastUsed
		FROM	categories
		LEFT JOIN
				(Select Count(TransactionID) as TransCount, Category, max(EntryDate) as LastUsed
				From accounttransactions
				Group By Category) as b
				ON categories.CategoryID = b.Category
		LEFT JOIN
				CategoryTypes
				ON categories.CategoryType = CategoryTypes.CategoryCode
		where	Deleted <> 'Y'
		ORDER BY CategoryDescription
	");

	$Deletedcategories = $db->query("
		SELECT 	categories.*, b.TransCount, CategoryTypes.CategoryTypeName, CategoryTypes.CatMult, UNIX_TIMESTAMP(b.lastused) as LastUsed
		FROM	categories
		LEFT JOIN
				(Select Count(TransactionID) as TransCount, Category, coalesce(max(EntryDate),'') as LastUsed
				From accounttransactions
				Group By Category) as b
				ON categories.CategoryID = b.Category
		LEFT JOIN
				CategoryTypes
				ON categories.CategoryType = CategoryTypes.CategoryCode
		where	Deleted = 'Y'
		ORDER BY CategoryDescription
	");
	
	?>
<?php $colOutput = formatTableColumns([100,100,100,100,100,100,100]); ?>
<table class="zebra-table" >
    <?= $colOutput ?>
    <thead>
        <tr>
		<th>Category</th>
		<th align="right">Type</th>
		<th align="right">Last Used</th>
		<th align="right">Entries</th>
		<th align="right">Summary</th>
		<th></th>
		<th></th>
	</tr>
    </thead>
    <tbody class="wrap">
        
        <?php while($row = $categories->fetch(PDO::FETCH_ASSOC)){ 
            $LastUsed = date("m/d/Y",$row["LastUsed"]);
            ?>
        <tr>
            <td><?= $row['CategoryDescription'] ?> </td>
            <td class="right"><?= $row['CategoryTypeName'] ?> </td>
            <td class="right"><?= $LastUsed ?> </td>
            <td class="right"><?= $row['TransCount'] ?> </td>
            <td class="right"><?= $row['SummaryHide'] ?> </td>
            <td class="right">
                <img class="active" src="./images/edit.png" onclick="Edit('<?= $row['CategoryID'] ?>')"/>
            </td>
            <td class="right">
                <img class="active" src="./images/delete.png" onclick="Delete('<?= $row['CategoryID'] ?>')"/>
            </td>
        </tr>
        <?php } ?>
        
        <?php while($row = $Deletedcategories->fetch(PDO::FETCH_ASSOC)){ 
            $LastUsed = date("m/d/Y",$row["LastUsed"]);
            ?>
        <tr>
            <td><?= $row['CategoryDescription'] ?> </td>
            <td class="right"><?= $row['CategoryTypeName'] ?> </td>
            <td class="right"><?= $LastUsed ?> </td>
            <td class="right"><?= $row['TransCount'] ?> </td>
            <td class="right"><?= $row['SummaryHide'] ?> </td>
            <td class="right"></td>
            <td class="right">
                <img class="active" src="./images/add.png" onclick="Add('<?= $row['CategoryID'] ?>')"/>
            </td>
        </tr>
        <?php } ?>
    </tbody>
    <tbody>
        <tr>
            <td colspan="7">
                <button type="button" onclick="New()">New Category</button>
            </td>
        </tr>
    </tbody>
</table>

<?php require( 'includes/Site_Layout_End.php');?>
</body>
</html>