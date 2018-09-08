<?php 
	include('includes/Site_Application.php');
	
	$Account = $_POST["AccountID"];
	$xReturn = $_POST["ReturnPage"];

	$db->query("
		Update defaultaccount
		set AccountID = $Account
		");


    header( 'Location:'.$xReturn ) ;

