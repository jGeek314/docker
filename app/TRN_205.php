<?php 

    $start = microtime(TRUE); 

    include('includes/Site_Application.php');

    $trnID = $_POST["trnID"];

    switch($_POST["action"]){

        case "clear":
            
            $db->query("
				INSERT INTO accounttransactionsbackup (TransactionID, EntryTitle, EntryDate, Category, TransactionAmount, Note, AccountID)
				Select TransactionID, EntryTitle, EntryDate, Category, TransactionAmount, Note, AccountID
				from accounttransactions
				where TransactionID = $trnID
				");
            
            $db->query("
                Update 	accounttransactions
                SET 	VerifiedDate = now()
                WHERE	TransactionID = $trnID
                and     VerifiedDate is null
                ");

            break;

        case "undo":

            $db->query("
				INSERT INTO accounttransactionsbackup (TransactionID, EntryTitle, EntryDate, Category, TransactionAmount, Note, AccountID)
				Select TransactionID, EntryTitle, EntryDate, Category, TransactionAmount, Note, AccountID
				from accounttransactions
				where TransactionID = $trnID
				");
            
            $query2 = $db->query("
                Update 	accounttransactions
                SET 	VerifiedDate = null
                WHERE	TransactionID = $trnID
                ");

            break;
            
       }     
  

        // re-run the accounts stats
        $LastVerified = $db->query($AccountDefaultSQL)->fetchAll()[0];

    $end = microtime(TRUE) - $start; 
?>




{
"DefaultBalance" :   "<?= $LastVerified['AccountBalance'] ?>",
"DefaultVerified" :  "<?= $LastVerified['VerifiedBalance'] ?>",
"LastVerified" :     "<?= $LastVerified['TransactionID'] ?>",
"LinkVerified" :     "<?= $LastVerified['linkedVerified'] ?>",
"ExecutionTime":     "<?= $end ?>"
}

   


