<?php 

function CreateSelectList($sqlResults,$Value,$Display, $Name){
	echo "<Select name=\"$Name\">";
	$num = mysqli_numrows($sqlResults);
	$i=0;
	while ($i < $num) {
		echo "hello";
		$Cur_Value=mysqli_result($sqlResults,$i,$Value);
		$Cur_Display=mysqli_result($sqlResults,$i,$Display);
		echo "<option value=\"$Cur_Value\">$Cur_Display</option>";
	$i++;
	}
}

function YearList($Name, $SelectedYear){
	echo "<select name=\"$Name\">";
	$i=2010;
	while ($i <= 2020){
		if($i == $SelectedYear){
			echo "<option value=\"$i\" selected=\"true\">$i</option>";}
		else{
			echo "<option value=\"$i\">$i</option>";}
		$i++;
	}	
	echo "</select>";
}

function MonthList($Name){
	$month = strtotime('2000-01-01'); 
	$end = strtotime('2000-12-30'); 
	echo "<select name=\"$Name\">";
	while($month < $end) { 
		$ThisMonthDisplay = date('M', $month);
		$ThisMonthValue = date('m', $month);
		echo "<option value=\"$ThisMonthValue\" >$ThisMonthDisplay</option>";  
		$month = strtotime("+1 month", $month); 
	} 
	echo "</select>";
}

function padWithZeros($s, $n) {
  return sprintf("%0" . $n . "d", $s);
}

function dateToNormal($Date){
	return substr($Date,5,2)."/".substr($Date,8,2)."/".substr($Date,0,4);
}

function GetMonthString($n)
{
    $timestamp = mktime(0, 0, 0, $n, 1, 2005);
    return date("M", $timestamp);
}


function formatTableColumns($colArr){
    $colOutput =  "<colgroup>";
    foreach($colArr as $num){
        if ($num > 0){
            $colOutput .= '<col style="width:'.$num.'px">';  
        } else{
            $colOutput .= '<col>';
        }
        
    }
    $colOutput .= "</colgroup>";
    return $colOutput;
}

function matchSelect($val1, $val2){
    if($val1 == $val2){
        return 'selected="selected"';
    }else{
        return "";
    }
}

function optionCheck($val1, $val2){
    if($val1 == $val2){
        return 'checked="checked"';
    }else{
        return "";
    }
}

function matchDisplay($val1, $val2, $display, $altDisplay =''){
    if($val1 == $val2){
        return $display;
    } 
    return $altDisplay;

}

function matchnodisplay($val1,$val2,$display){
    if($val1 <> $val2){
        return $display;
    }
    return '';
}
function matchList($list, $val){
    $arr = explode(",", strtolower($list));
    if(in_array(strtolower($val),$arr)){
        return true;
    }
    return false;
}

function matchListDisplay($val1, $val2, $display){
    if(matchList($val1, $val2)){
        return $display;
    }
    return '';
}

function formParam($val,$default = ''){
    global $_POST;
    if(isset($_POST[$val])){
        return $_POST[$val];
    }
    return $default;
}
function formatPrice($val){
    $tmpval1 = str_replace( ',', '', $val );
    $tmpval = is_numeric($tmpval1) ? $tmpval1 : 0;
    if($val < 0){
        $tmpval = number_format($tmpval*-1,2);
        return '<span class="text-danger">-$'.$tmpval.'</span>';
    }
    else{
        return "$".number_format($tmpval,2);
    }
   
}


function validateDate($date){
    $d = DateTime::createFromFormat('m/d/Y', $date);
    return $d && $d->format('m/d/Y') === $date;
}

function getAccountIcon($val){           
    switch($val){
        case 'Checking accounts':
            return '<i class="fa fa-money"></i>';
        case 'Virtual':
            return '<i class="fa fa-snapchat-ghost"></i>';
        case 'Credit Cards':
            return '<i class="fa fa-credit-card"></i>';
        case 'Savings accounts':
            return '<i class="fa fa-university"></i>';
        default:
            return '<i class="fa fa-question-circle-o"></i>';
    }
}

?>