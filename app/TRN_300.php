<?php require_once('includes/Site_Application.php'); ?>
<html>
<head>
    <?php require( 'includes/Site_Header.php'); ?>

</head>
<body onLoad="SetFocus()">

<?php 
	require( 'includes/Site_Layout_Start.php');
$Now = getdate();
$ThisMonthNum = $Now["year"]*12+$Now["mon"];
$Month1 = str_pad(($ThisMonthNum-1)%12+1, 2, "0", STR_PAD_LEFT);
$Year1 = ($ThisMonthNum - $Month1)/12;

$ThisMonthNum = $ThisMonthNum - 1;
$Month2 = str_pad(($ThisMonthNum-1)%12+1, 2, "0", STR_PAD_LEFT);
$Year2 = ($ThisMonthNum - $Month2)/12;

$ThisMonthNum = $ThisMonthNum - 1;
$Month3 = str_pad(($ThisMonthNum-1)%12+1, 2, "0", STR_PAD_LEFT);
$Year3 = ($ThisMonthNum - $Month3)/12;

$ThisMonthNum = $ThisMonthNum - 1;
$Month4 = str_pad(($ThisMonthNum-1)%12+1, 2, "0", STR_PAD_LEFT);
$Year4 = ($ThisMonthNum - $Month4)/12;

$ThisMonthNum = $ThisMonthNum - 1;
$Month5 = str_pad(($ThisMonthNum-1)%12+1, 2, "0", STR_PAD_LEFT);
$Year5 = ($ThisMonthNum - $Month5)/12;

$ThisMonthNum = $ThisMonthNum - 1;
$Month6 = str_pad(($ThisMonthNum-1)%12+1, 2, "0", STR_PAD_LEFT);
$Year6 = ($ThisMonthNum - $Month6)/12;

$ThisMonthNum = $ThisMonthNum - 1;
$Month7 = str_pad(($ThisMonthNum-1)%12+1, 2, "0", STR_PAD_LEFT);
$Year7 = ($ThisMonthNum - $Month7)/12;

$ThisMonthNum = $ThisMonthNum - 1;
$Month8 = str_pad(($ThisMonthNum-1)%12+1, 2, "0", STR_PAD_LEFT);
$Year8 = ($ThisMonthNum - $Month8)/12;

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
	
	$MonthlySummary = $db->query("
            SELECT 	cat.CategoryDescription, cat.CategoryID, a.Sum1, b.Sum2, c.Sum3, d.Sum4, e.Sum5, f.Sum6, g.Sum7, h.Sum8
            FROM	categories as cat
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum1, Category
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year1-$Month1-01 00:00:00'
                        AND	AccountID IN $ActiveAccountList
                        GROUP BY Category) as a
                        ON cat.CategoryID = a.Category
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum2, Category
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year2-$Month2-01 00:00:00'
                        AND EntryDate < '$Year1-$Month1-01 00:00:00'
                        AND	AccountID IN $ActiveAccountList
                        GROUP BY Category) as b
                        ON cat.CategoryID = b.Category

            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum3, Category
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year3-$Month3-01 00:00:00'
                        AND EntryDate < '$Year2-$Month2-01 00:00:00'
                        AND	AccountID IN $ActiveAccountList
                        GROUP BY Category) as c
                        ON cat.CategoryID = c.Category
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum4, Category
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year4-$Month4-01 00:00:00'
                        AND EntryDate < '$Year3-$Month3-01 00:00:00'
                        GROUP BY Category) as d
                        ON cat.CategoryID = d.Category
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum5, Category
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year5-$Month5-01 00:00:00'
                        AND EntryDate < '$Year4-$Month4-01 00:00:00'
                        AND	AccountID IN $ActiveAccountList
                        GROUP BY Category) as e
                        ON cat.CategoryID = e.Category
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum6, Category
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year6-$Month6-01 00:00:00'
                        AND EntryDate < '$Year5-$Month5-01 00:00:00'
                        AND	AccountID IN $ActiveAccountList
                        GROUP BY Category) as f
                        ON cat.CategoryID = f.Category
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum7, Category
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year7-$Month7-01 00:00:00'
                        AND EntryDate < '$Year6-$Month6-01 00:00:00'
                        AND	AccountID IN $ActiveAccountList
                        GROUP BY Category) as g
                        ON cat.CategoryID = g.Category
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum8, Category
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year8-$Month8-01 00:00:00'
                        AND EntryDate < '$Year7-$Month7-01 00:00:00'
                        AND	AccountID IN $ActiveAccountList
                        GROUP BY Category) as h
                        ON cat.CategoryID = h.Category
            WHERE	COALESCE(a.Sum1,0.0) + COALESCE(b.Sum2,0.0)+ COALESCE(c.Sum3,0.0) + COALESCE(d.Sum4,0.0)+ COALESCE(e.Sum5,0.0) + COALESCE(f.Sum6,0.0) + COALESCE(g.Sum7,0.0)+ COALESCE(h.Sum8,0.0)<> 0
            AND cat.SummaryHide <> 'Y'
            ");

	$MonthlyTotSummary = $db->query("
            SELECT      a.Sum1, b.Sum2, c.Sum3, d.Sum4, e.Sum5, f.Sum6, g.Sum7, h.Sum8
            FROM	(SELECT Sum(Amount) as Sum1, 'bind' as bind
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year1-$Month1-01 00:00:00'
                        AND SummaryHide <> 'Y'
                        AND	AccountID IN $ActiveAccountList
                        ) as a
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum2, 'bind' as bind
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year2-$Month2-01 00:00:00'
                        AND EntryDate < '$Year1-$Month1-01 00:00:00'
                        AND SummaryHide <> 'Y'
                        AND	AccountID IN $ActiveAccountList) as b
                        ON a.bind = b.bind
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum3, 'bind' as bind
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year3-$Month3-01 00:00:00'
                        AND EntryDate < '$Year2-$Month2-01 00:00:00'
                        AND SummaryHide <> 'Y'
                        AND	AccountID IN $ActiveAccountList) as c
                        ON a.bind = c.bind
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum4, 'bind' as bind
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year4-$Month4-01 00:00:00'
                        AND EntryDate < '$Year3-$Month3-01 00:00:00'
                        AND SummaryHide <> 'Y'
                        AND	AccountID IN $ActiveAccountList) as d
                        ON a.bind = d.bind
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum5, 'bind' as bind
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year5-$Month5-01 00:00:00'
                        AND EntryDate < '$Year4-$Month4-01 00:00:00'
                        AND SummaryHide <> 'Y'
                        AND	AccountID IN $ActiveAccountList) as e
                        ON a.bind = e.bind
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum6, 'bind' as bind
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year6-$Month6-01 00:00:00'
                        AND EntryDate < '$Year5-$Month5-01 00:00:00'
                        AND SummaryHide <> 'Y'
                        AND	AccountID IN $ActiveAccountList) as f
                        ON a.bind = f.bind
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum7, 'bind' as bind
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year7-$Month7-01 00:00:00'
                        AND EntryDate < '$Year6-$Month6-01 00:00:00'
                        AND SummaryHide <> 'Y'
                        AND	AccountID IN $ActiveAccountList) as g
                        ON a.bind = g.bind
            LEFT JOIN	
                        (SELECT Sum(Amount) as Sum8, 'bind' as bind
                        FROM accounttransactionsextended
                        WHERE EntryDate >= '$Year8-$Month8-01 00:00:00'
                        AND EntryDate < '$Year7-$Month7-01 00:00:00'
                        AND SummaryHide <> 'Y'
                        AND	AccountID IN $ActiveAccountList) as h
                        ON a.bind = h.bind
            ");		
?>
<?php $colOutput = formatTableColumns([150,100,100,100,100,100,100,100,100]); ?>
<table class="zebra-table" style="width:100px">
    <?= $colOutput ?>
    <thead>
        <tr>
            <th></th>
            <th class="right"><?= GetMonthString($Month8)." ".$Year8 ?></th>
            <th class="right"><?= GetMonthString($Month7)." ".$Year7 ?></th>
            <th class="right"><?= GetMonthString($Month6)." ".$Year6 ?></th>
            <th class="right"><?= GetMonthString($Month5)." ".$Year5 ?></th>
            <th class="right"><?= GetMonthString($Month4)." ".$Year4 ?></th>
            <th class="right"><?= GetMonthString($Month3)." ".$Year3 ?></th>
            <th class="right"><?= GetMonthString($Month2)." ".$Year2 ?></th>
            <th class="right"><?= GetMonthString($Month1)." ".$Year1 ?></th>
        </tr>
    </thead>
    <tbody class="wrap">
        <?php   while($row = $MonthlySummary->fetch(PDO::FETCH_ASSOC)){ ?>
        <tr>
            <td>
                <a href="TRN_310.php?CategoryID=<?= $row["CategoryID"] ?>">
                    <?= $row["CategoryDescription"] ?>
                </a>
            </td>
            <td class="right">$<?= number_format(abs($row["Sum8"]),2)?> </td>
            <td class="right">$<?= number_format(abs($row["Sum7"]),2)?> </td>
            <td class="right">$<?= number_format(abs($row["Sum6"]),2)?> </td>
            <td class="right">$<?= number_format(abs($row["Sum5"]),2)?> </td>
            <td class="right">$<?= number_format(abs($row["Sum4"]),2)?> </td>
            <td class="right">$<?= number_format(abs($row["Sum3"]),2)?> </td>
            <td class="right">$<?= number_format(abs($row["Sum2"]),2)?> </td>
            <td class="right">$<?= number_format(abs($row["Sum1"]),2)?> </td>
        </tr>
        <?php } ?>
    </tbody>
    <tbody>
        <?php   while($row = $MonthlyTotSummary->fetch(PDO::FETCH_ASSOC)){ ?>
        <tr>
            <td></td>
            <td class="right">$<?= number_format(abs($row["Sum8"]),2) ?> </td>
            <td class="right">$<?= number_format(abs($row["Sum7"]),2) ?> </td>
            <td class="right">$<?= number_format(abs($row["Sum6"]),2) ?> </td>
            <td class="right">$<?= number_format(abs($row["Sum5"]),2) ?> </td>
            <td class="right">$<?= number_format(abs($row["Sum4"]),2) ?> </td>
            <td class="right">$<?= number_format(abs($row["Sum3"]),2) ?> </td>
            <td class="right">$<?= number_format(abs($row["Sum2"]),2) ?> </td>
            <td class="right">$<?= number_format(abs($row["Sum1"]),2) ?> </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
    
<?php require( 'includes/Site_Layout_End.php');?>
    
</body>
</html>