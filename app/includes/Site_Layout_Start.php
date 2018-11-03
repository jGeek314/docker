<?php 
    require_once('Site_Application.php');
         
    $AccountListDefault = $db->query("
		SELECT 	*
		FROM	accounts
		WHERE	AccountDeleted <> 'Y'  
                        or AccountID = (Select AccountID From defaultaccount)
	");
	
?>




<header class="bg-dark">
    <div class="center-content">

        <nav class="navbar navbar-expand-md navbar-dark bg-dark">
            <div>
                <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbar">
                    <i class="fa fa-bars"></i>
                </button>
                <span class="navbar-brand">
                    My checkbook
                </span>
                
            </div>
            <div class="text-right flex-nowrap d-flex d-md-none text-secondary">
                Summary - <span class="badge badge-primary text-nowrap">$<?=$accountsummary?></span>
            </div>
            
            


              <div class="navbar-collapse collapse" id="navbar">
                <ul class="navbar-nav mr-auto">
                  <li class="nav-item">
                    <a class="nav-link <?= matchDisplay($curpage,'index.php','active') ?>" href="index.php">Accounts</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?= matchDisplay($curpage,'TRN_400.php','active') ?>" href="TRN_400.php">Transfer</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?= matchDisplay($curpage,'TRN_250.php','active') ?>" href="TRN_250.php">Search</a>
                  </li>
                  <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown">Other Features</a>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="TRN_300.php">Cost Breakdown</a>
                      <a class="dropdown-item" href="TRN_500.php">Manage categories</a>
                      <a class="dropdown-item" href="TRN_700.php">Manage bills</a>
                      <a class="dropdown-item" href="TRN_800.php">Search Transactions</a>
                    </div>
                  </li>
                </ul>

              </div>
            
            <div class="text-right d-none d-md-inline text-secondary">
                Checkbook Summary - <span class="badge badge-primary">$<?=$accountsummary?></span>
            </div>
        </nav>

        <div class="title-bar">
            <hr>


            <?php if(matchList('trn_100.php,trn_200.php,trn_210.php',$curpage)) { ?>
                <div class="row nav-2">
                    <div class="col text-left">
                      <div class="dropdown">
                        <button type="button" class="btn btn-link  dropdown-toggle" data-toggle="dropdown"><?=$defaultaccountDescr?></button>
                        <div class="dropdown-menu bg-dark">

                            <?php
                                while($row = $AccountListDefault->fetch(PDO::FETCH_ASSOC)) {  ?>

                                    <button type="button" class="dropdown-item text-light" data-change-account="<?= $row['AccountID'] ?>" <?= matchDisplay($row["AccountID"],$defaultaccount,'disabled') ?> >
                                        <?= $row['AccountDescription'] ?>
                                    </button>

                            <?php } ?>

                        </div>
                      </div>
                    </div>
                    <div class="col text-right">
                        <div>
                            Account Total - 
                            <span class="badge badge-success" id="defaultBalance">
                                $<?= $DefaultBalance ?>
                            </span>
                        </div>
                        <div>
                            Verified Total - 
                            <span class="badge badge-dark" id="defaultVerified">
                                $<?= $LinkVerified ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php } ?> 



            <h6 class="small text-muted">
                <?php

                switch ($curpage) {
                    case "";            echo "Accounts Summary";    break;
                    case "INDEX.php":   echo "Accounts Summary";    break;
                    case "TRN_015.php": echo "Add/Edit Account";    break;
                    case "TRN_250.php": echo "Search Transactions"; break;
                    case "TRN_300.php": echo "Cost Breakdown by categories: ".$defaultaccountDescr; break;
                    case "TRN_400.php": echo "Transfer Funds Between accounts"; break;
                    case "TRN_500.php": echo "Manage categories";   break; 
                    case "TRN_700.php": echo "Manage bills";    break;
                }
                ?>
            </h6> 
        </div>
    </div>

</header>

<main>
    <div class="center-content">

        <div class="container-fluid inner-content">

            <?php if(in_array($curpage, array('TRN_100.php','TRN_200.php','TRN_210.php'))) {  ?>
                <ul class="nav nav-tabs">
                  <li class="nav-item">
                    <a class="nav-link <?= matchDisplay($curpage,'TRN_100.php','active') ?>" href="TRN_100.php">Add Entry</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?= matchDisplay($curpage,'TRN_200.php','active') ?>" href="TRN_200.php">Verify Entries</a>
                  </li>
                  <li class="nav-item">
                    <span class="nav-link <?= matchDisplay($curpage,'TRN_210.php','active','disabled') ?>">Edit Entry</span>
                  </li>
                </ul> 
            <?php } ?>
       
          

