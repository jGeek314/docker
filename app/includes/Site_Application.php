<?php

    session_start();

    //include environment variables
    require_once('env.php');

    //include debugger (cfdump clone) for development.
    require_once('Util_dBug.php');
    require_once('Util_dot.php');
    
    //help function for dot notation
    function dot($items){
        return new \Adbar\Dot($items);
    }

    function errHandle($errNo, $errStr, $errFile, $errLine) {
        $msg = "$errStr in $errFile on line $errLine";
        if ($errNo == E_NOTICE || $errNo == E_WARNING) {
            throw new ErrorException($msg, $errNo);
        } else {
            echo $msg;
        }
    }

    set_error_handler('errHandle');

    $appVersion = "2.51";

    $curpageArr = explode('/',$_SERVER["REQUEST_URI"]);
    $curpage = $curpageArr[count($curpageArr) - 1];

    $db = checkbookConnect();
    $db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

    function query($sql,$return = true){
        global $db;
        try {
            $db ->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
            $resultPDO = $db->query($sql);
            if ($return){
                $result = $resultPDO -> fetchAll();
            } else{
                $result = true;
            }
            
         }catch(PDOException  $e ){
            echo "Error: <br><br>".$sql."<br><br>".$e;
            $result = false;
        }
        return $result;
    }
    
    function updateQuery($sql){
        query($sql,false);
    }

    function insertQuery($sql){
        query($sql,false);
    }
    
        
  /*
	method used for calling a function within a heredoc syntax
    to implement just assign the function to you want to the 
	function variable like so:
		{$fn->testfunction()}
  */
  class Fn {
    public function __call($name, $args) {
      if (function_exists($name)) {
        return call_user_func_array($name, $args);
      }
    }
  }
  $fn = new Fn();


function getDB(){
	return($_ENV["MYSQL_DATABASE"]);
}
function checkbookConnect(){
	global $dbname;
	return (new PDO('mysql:host=db;dbname='.$_ENV["MYSQL_DATABASE"].';charset=utf8', $_ENV["MYSQL_USER"], $_ENV["MYSQL_PASSWORD"]));
}




//since there is no "mysqli_result" function...
function my_result($qryObject, $column){
	$row = mysqli_fetch_assoc($qryObject);
    return($row[$column]);
}

require('Site_Functions.php');


// Get Default Account
$AccountDefaultSQL = "
    SELECT 	defaultaccount.accountid, AccountDescription, AccountBalance, VerifiedBalance, lastVerified.TransactionID, linkedVerified, accounts.Summary
    FROM	defaultaccount left join accounts
    on      accounts.accountid = defaultaccount.accountid

     left join
      (SELECT	Sum(Amount) as AccountBalance, accountid
       FROM		accounttransactionsextended
       Group by accountid)  total
     on defaultaccount.accountid = total.accountid
    left join
      (SELECT	Sum(Amount) as VerifiedBalance, accountid
       FROM		accounttransactionsextended
       WHERE	VerifiedDate is not null
       Group by accountid)  verified
     on defaultaccount.accountid = verified.accountid
    left join
        (Select TransactionID, accounttransactions.VerifiedDate, accounttransactions.accountid
         From   accounttransactions
         inner join
            (Select accountid, Max(VerifiedDate) as VerifiedDate
             From accounttransactions
             Group by accountid) lastMax
             on accounttransactions.accountid = lastMax.accountid
             and accounttransactions.VerifiedDate = lastMax.VerifiedDate
        ) lastVerified
      on defaultaccount.accountid = lastVerified.accountid

    left join
        (select linkedVerified, accountid 
         from 
            (Select Sum(Amount) as linkedVerified, linkedaccountid
             From   accounttransactionsextended
             where  verifieddate is not null
             Group by linkedaccountid) la
             inner join
            (Select accountid, linkedaccountid
             From   accounts) lb on la.linkedaccountid = lb.linkedaccountid
        ) linked on defaultaccount.accountid = linked.accountid


    ";
$AccountDefault = $db->query($AccountDefaultSQL);

while($row = $AccountDefault->fetch(PDO::FETCH_ASSOC)) {
    $defaultaccount = $row['accountid'];
    $defaultaccountDescr = $row['AccountDescription'];
    $DefaultBalance  = $row['AccountBalance'];
    $DefaultVerified  = $row['VerifiedBalance'];
    $DefaultSummary  = $row['Summary'];
    $LinkVerified = $row['linkedVerified'];
}


$accountsummaryResults = query("
    SELECT      Sum(Amount) as accountTotal
    FROM	    accounts
    left join   accounttransactionsextended on accounts.accountid = accounttransactionsextended.accountid
    Where       accounts.Summary = 'Y' and AccountDeleted <> 'Y'
");

$accountsummary = $accountsummaryResults[0]['accountTotal'];
        

