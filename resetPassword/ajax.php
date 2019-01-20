<?php
	session_start();    

	//connect database
	include '../class/dbconnector1.php';
	include '../class/dbconnector2.php';
    include '../class/class.base.php';
    $base = new base();

    //set timezone
    date_default_timezone_set('Europe/Stockholm');


    //verifying form submission and CSRF token
	if( isset( $_POST['new_password'] ) && $_POST['csrf_token']==$_SESSION['csrf_token'] )
	{	
		//encrypt password
		$password = hash('sha256', $_POST['new_password']);

		//fetching user email from table 'tokens'
		$query = $base->select('tokens', 'user', array('token'=>$base->enc($_POST['token'])));
		$email = $query->user;

		//delete row from table 'tokens'
		$token  = $base->enc($_POST['token']);
		try 
		{
			$query2 = $conn1->prepare('DELETE FROM tokens WHERE token=:token');
			$query2->bindParam(':token', $token);
			$query2->execute();
		}
		catch (PDOException $e) 
		{
		 	$e->getMessage();	
		} 

		//bad request
		if( $query==false )
			echo json_encode(array('response'=>0));

		//verifying if user is a 'client'
		else if( $base->select('clients', '*', array('c_email'=>$email)) != false )
		{
			//change password
			$password = $base->enc($password);
			try
			{
				$query2 = $conn1->prepare('UPDATE clients SET c_password=:c_password WHERE c_email=:c_email');
				$query2->bindParam(':c_password', $password);
				$query2->bindParam(':c_email', $email);
				$query2->execute();
			}
			catch (PDOException $e) 
			{
			 	$e->getMessage();	
			} 

			echo json_encode(array('response'=>1));
		}

		//verifying if user is admin
		else if( $base->select('adminaccounts', '*', array('ac_email'=>$base->enc($email))) != false )
		{
			//change password
			$password = $base->enc($password);
			$email = $base->enc($email);
			try
			{
				$query2 = $conn1->prepare('UPDATE adminaccounts SET ac_password=:ac_password WHERE ac_email=:ac_email');
				$query2->bindParam(':ac_password', $password);
				$query2->bindParam(':ac_email', $email);
				$query2->execute();
			}
			catch (PDOException $e) 
			{
			 	$e->getMessage();	
			} 

			echo json_encode(array('response'=>1));
		}
		else
		{
			//remove dangling else
		}
	}
?>