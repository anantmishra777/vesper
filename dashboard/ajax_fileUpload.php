<?php
	session_start();
	
	//redirect to login page if admin isn't logged in
	if( !isset($_SESSION['email']) )
		echo json_encode(array('response' => -1));

	//connect database
	include '../class/dbconnector1.php';
	include '../class/dbconnector2.php';

	//including mysql functions file
    include '../class/class.base.php';
    $base = new base();

    //set timezone
    date_default_timezone_set('Europe/Stockholm');

    //log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");



	//insert attachment details to table 'orderattachments'
	if( isset($_FILES) && $_SESSION['csrf_token']==$_POST['csrf_token'])
	{
		if( move_uploaded_file($_FILES['files']['tmp_name'][0], 'attachments/' . $_FILES['files']['name'][0]) )
		{
			$_SESSION['uploaded_file_names']  = $_FILES['files']['name'][0].',';		

			echo	"{\"files\":	
					 [{
						\"url\":          \"attachments/{$_FILES['files']['name'][0]}\",
						\"name\":        \"{$_FILES['files']['name'][0]}\",
						\"type\":		 \"image/jpeg\",
						\"size\":		 \"{$_FILES['files']['size'][0]}\"
					 }]}";
		}
		else
			echo 'Error: Try Later';
	}
?>