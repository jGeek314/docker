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

    /*
        possible actions:
            new - do nothing, blank form,
            insert - submitting a new entry
            modify - editing a existing entry, no changes made yet
            update - submitting an update
    */
    
    //if action is modify, we need to get the current values from the database
    if($_POST['action'] == 'modify'){
        $editTRN = $_POST['editTRN'];
         $TransactionDetails = $db->query("
            SELECT	*, UNIX_TIMESTAMP(EntryDate) as phpEntryDate
            FROM	accounttransactions
            WHERE	TransactionID = $editTRN
        ");
        while($row = $TransactionDetails->fetch(PDO::FETCH_ASSOC)) {
            $_POST['Account'] = $row['AccountID'];
            $_POST['Description'] = $row['EntryTitle'];
            $_POST['Category'] = $row['Category'];
            $_POST['Amount'] = Number_format($row["TransactionAmount"],2, '.', '');
            $_POST['Date'] = date('m/d/Y',$row["phpEntryDate"]);
            $_POST['Note'] = $row["Note"];
        } 
    }

    //clean up form variables and validate (insert and update only)
    if( in_array($_POST['action'], ['insert','update']) ) {
    
        foreach($formFields as $field => $properties){
           
            $_POST[$field] = str_replace(",", "", $_POST[$field]);
            $_POST[$field] = str_replace("'", "", $_POST[$field]);
            
            if($dot->get($field.'.required') && strlen($_POST[$field]) == 0){
                $errors[] = $dot[$field.'.label']." is required.";
            }
            
            if($dot->has($field.'.validate')){
                if ($dot->get($field.'.validate') == 'number' && strlen($_POST[$field]) > 0 && !is_numeric($_POST[$field] )){
                    $errors[] = $dot[$field.'.label'].' is not a valid number.';
                }
                if ($dot->get($field.'.validate') == 'date' && strlen($_POST[$field]) > 0 && !validateDate($_POST[$field]) ){
                    $errors[] = $dot[$field.'.label']." is not valid.  Use format 'mm/dd/yyyy'.";
                }
            }
        }
            
    }



    //if validation is successful then run the db updates
    if( sizeof($errors) == 0 && matchList('insert,update,delete',$_POST['action'])){

        switch($_POST['action']){
            case "insert":

                $Account = $_POST["Account"];
                $Description = $_POST["Description"];
                $Category = $_POST["Category"];
                $Amount = $_POST["Amount"];
                $Date = $_POST["Date"];
                $Note = $_POST["Note"];
                $DateTime = date('Y-m-d G:i:s',strtotime($Date.' 00:00:00'));

                $query2 = $db->query("
                    INSERT INTO accounttransactions (
                            AccountID,
                            EntryTitle, 
                            Category,
                            TransactionAmount,
                            EntryDate,
                            Note)
                    VALUES(	
                            $Account,
                            '$Description',
                            $Category,
                            $Amount,
                            '$DateTime',
                            '$Note');
                    ");

                $xTransID = $db->lastInsertId();


                break;

            case "update":

                $xTransID = $_POST["editTRN"];
                $Account = $_POST["Account"];
                $Description = $_POST["Description"];
                $Category = $_POST["Category"];
                $Amount = $_POST["Amount"];
                $Date = $_POST["Date"];
                $Note = $_POST["Note"];
                $DateTime = date('Y-m-d G:i:s',strtotime($Date.' 00:00:00'));

                $db->query("
                    INSERT INTO accounttransactionsbackup (TransactionID, EntryTitle, EntryDate, Category, TransactionAmount, Note, AccountID)
                    Select TransactionID, EntryTitle, EntryDate, Category, TransactionAmount, Note, AccountID
                    from accounttransactions
                    where TransactionID = $xTransID
                    ");

                $db->query("
                    Update 	accounttransactions
                    SET 	AccountID = $Account,
                            EntryTitle = '$Description',
                            Category = $Category,
                            TransactionAmount = $Amount,
                            EntryDate = '$DateTime',
                            Note = '$Note'
                    WHERE	TransactionID = $xTransID
                    ");

                break;

            case "delete":
                $xTransID = $_POST['deleteTRN'];	
                $query1 = $db->exec("
                    INSERT INTO accounttransactionsbackup (TransactionID, EntryTitle, EntryDate, Category, TransactionAmount, Note, AccountID)
                    Select TransactionID, EntryTitle, EntryDate, Category, TransactionAmount, Note, AccountID
                    from accounttransactions
                    where TransactionID = $xTransID
                    ");

                $query2 = $db->exec("
                    delete
                    from accounttransactions
                    where TransactionID = $xTransID
                    ");

                break;
                
            default:
                new dBug ( $_POST );        
                exit("No action was specified.  Script aborted.");
                
        }


        // Before navigating away, check the account summary... if a slush account is set up, then transfer funds.
        
        $accountsummary = query("
            SELECT      Sum(Amount) as accountTotal
            FROM	    accounts
            left join   accounttransactionsExtended on accounts.AccountID = accounttransactionsExtended.AccountID
            Where       accounts.Summary = 'Y' and AccountDeleted <> 'Y'
        ");
        
        $clearDiff = round($accountsummary[0]['accountTotal'] - floor($accountsummary[0]['accountTotal']),2);
        
        if ($clearDiff > 0){
            $getSlushAccount = query("
                Select AccountID, SlushAccount, SlushTransaction, AccountDescription
                from   accounts
                Where  Summary = 'Y'
                and    SlushAccount > 0 and SlushTransaction > 0
            ");
            
            if (sizeof($getSlushAccount) > 0){
                
                $slushTransaction =  $getSlushAccount[0]['SlushTransaction'];
                $slushAccount =      $getSlushAccount[0]['SlushAccount'];
                $slushAccountDescr = $getSlushAccount[0]['AccountDescription'];

                updateQuery("
                    INSERT INTO accounttransactions (AccountID, EntryTitle, Category,TransactionAmount,EntryDate, verifiedDate)
                    VALUES(	$slushAccount,'Transfer from $slushAccountDescr', 44, $clearDiff, now(),now());

                    update  accounttransactions
                    Set     TransactionAmount = TransactionAmount + $clearDiff
                    Where   TransactionId = $slushTransaction;
                ");
            }
        }
        
        $_SESSION['HighlightTrans'] = $xTransID;
        header( 'Location:'.$_POST['returnURL'] ) ;


    }
    
?>