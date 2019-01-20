<?php
	//connect databse
	include 'dbconnector1.php';
	include 'dbconnector2.php';

	//log errors
	ini_set("log_errors", 1);
	ini_set("error_log", "error.log");

	class base
	{
		//functions here now

		//encrypt info
		function enc($string)
		{
			return openssl_encrypt($string, 'AES-256-CBC', '48f5d1ba295d17e6ecc0cd508b6a242c501f1aff', true, '48f5d1ba295d17e6');
		}

		//decrypt info
		function dec($string)
		{
			return openssl_decrypt($string, 'AES-256-CBC', '48f5d1ba295d17e6ecc0cd508b6a242c501f1aff', true, '48f5d1ba295d17e6');
		}


		/**
		 * function to get data from a DB
		 * @param [type] $[name] [<description>]  [type]
		 * @param [type] $[name] [<description>]  [type]
		 * @param [type] $[name] [<description>]  [type]
		 * @return [type] [<description>] [type]
		 */
		function select($table_name, $fields, $where_array = null, $return_rowcount=0, $fetchAll=0)
		{
			global $conn1;
			global $conn2;

			//check if the select is a direct select or one with while flags included
			if($where_array && count($where_array) > 0)
			{
				//where array has some values, so we need to run with the where command

				//fix the where_array with ? 
				$where_flag = array();
				$where_flag_values = array();
				foreach($where_array as $key=>$value)
				{
					$where_flag[] = "$key = ?";
					$where_flag_values[] = $value;
				}
				$where_flag_string = implode(' AND ', $where_flag);
				
				try
				{
					$s = $conn2->prepare("SELECT $fields FROM $table_name WHERE $where_flag_string");
					//generate the bindParams
					$s->execute($where_flag_values);
				}
				catch(PDOException $e){
					echo $e->getMessage();
				}

				if($return_rowcount==1)
					return $s->rowCount();

				//Is the query having results?
				else if($s->rowCount() > 0)
				{
					//return details
					if($fetchAll==1)
						return $s->fetchAll(PDO::FETCH_OBJ);
					else
						return $s->fetch(PDO::FETCH_OBJ);
				}
				else
				{
					return false;
				}
				$s->closeCursor();
			}
			else
			{
				//where array doesn't have any value, run directly
				try
				{
					$s = $conn2->query("SELECT $fields FROM $table_name");
					$s->execute();
				}
				catch(PDOException $e)
				{
					echo $e->getMessage();
				}

				if($return_rowcount==1)
					return $s->rowCount();

				else if($s->rowCount() > 0)
				{
					//return details
					return $s->fetchAll(PDO::FETCH_OBJ);
				}
				else
				{
					return false;
				}
				$s->closeCursor();
			}
		}//function


		/**
		 * Function to insert into the database
		 * @param  [type] $table Table name
		 * @param  [type] $arr   Array to be inserted with column names
		 * @return [type]        [description]
		 */
		function insert($table, $arr)
		{
		  global $conn1;global $conn2;
		  $names  = join(',', array_keys($arr));
		  $values = substr(str_repeat(',?', count($arr)), 1);
		  $s = $conn1->prepare("INSERT INTO $table ($names) VALUES ($values)");
		  if ($s->execute(array_values($arr))) 
		  {
		      return true;
		  }
		  else
		  	return false;
		}//function


		/**
		 * Check if a particular entry in table exists with the particular field name and field value inside a table
		 * @param  [type] $field_name  [description]
		 * @param  [type] $field_value [description]
		 * @param  [type] $table       [description]
		 * @return [type]              [description]
		 */
		function field_exists($field_name, $field_value, $table)
		{
		  global $conn1;global $conn2;
		  try
		  {
		    $s = $conn2->prepare("SELECT * from $table where $field_name = :f_value");
		    $s->bindParam(':f_value', $field_value);
		    $s->execute();
		    if($s->rowCount() > 0)
		    {
		      return true;
		    }
		    else
		    {
		      return false;
		    }
		  }
		  catch(PDOException $e)
		  {
		    echo $e->getMessage();
		  }
		}//function

		/**
		 * [updateMQ description]
		 * @param  [type] $tableName     [The table name to be inserted here]
		 * @param  [type] $colsArray     [Array of the columns that have to be updated]
		 * @param  [type] $whereCol      [Primary identifier of the update. Like where id = 1]
		 * @param  [type] $whereCOlValue [Value of the id]
		 * @return [type]                [returns 1 if query successful, 0 if not]
		 */
		function updateMQ($tableName, $colsArray, $whereCol, $whereCOlValue)
		{
		  global $conn1;global $conn2;
		  //first convert the $colsArray, which could be supplied to the update query
		  $count = count($colsArray);
		  $c=0;
		  $and = "";
		  $UpdateString = "";
		  foreach($colsArray as $key=>$value)
		  {
		    $c++;
		    if($c!=$count)
		    {
		      $and = ",";
		    }
		    $s = "$key = ?$and ";
		    $UpdateString .= $s;
		    $and = "";
		  }
		  //$colsArray converted
		  //
		  //now generate the execute string
		  //Example :- execute([$email, $status]);
		  $c=0;
		  $ExecuteString = array();
		  foreach($colsArray as $key=>$value)
		  {
		    /*
		    $c++;
		    if($c!=$count)
		    {
		      $and = ",";
		    }
		    $s = "$value$and ";
		    $ExecuteString .= $s;
		    $and = "";
		    */
		    $ExecuteString[] = $value;
		  }
		  // $ExecuteString = rtrim($ExecuteString);
		  // $final_execute_string = "[$ExecuteString]";
		  //$UpdateString == //contains the string with the AND keyword
		  //
		  //now bind the entire query together
		  $query = "UPDATE $tableName SET $UpdateString WHERE $whereCol = ?";
		  $ExecuteString[] = $whereCOlValue;
		  //execute the PDO query now
		  //
		  
		  try
		  {
		    $s = $conn1->prepare($query);
		    if($s->execute($ExecuteString))
		    {
		      return 1;
		    }
		    else
		    {
		      return 0;
		    }
		  }
		  catch(PDOException $e)
		  {
		    echo $e->getMessage();
		  }
		}//function


		/**
		 * Function to recursively generate a unique value pertaining to a field in a table
		 * @param  [type] $tableName [description]
		 * @param  [type] $type      [description]
		 * @param  [type] $fieldName [description]
		 * @return [type]            [description]
		 */
		function recursive_generator($fieldName, $type, $tableName)
		{
			if($type == 'token' || $type == 'report' || $type == 'file')
				$id = bin2hex(random_bytes(20));
			else
				$id = mt_rand(10000000, 99999999);
			if(self::field_exists($fieldName, $id, $tableName))
				self::recursive_generator($tableName, $fieldName);
			else
			   return $id;
		}//function

 

		//check if file with same name already exists
		function setFilename()
		{
			$target = bin2hex(random_bytes(10));
			if( file_exists('attachments/'.$target) )
				self::setFilename();
			else
				return $target;
		}


		/**
		 * Function to recursively generate a unique value of auth api key
		 */
		function auth_api_key_generator()
		{						
			global $conn1;global $conn2;

		    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		    $digits = '0123456789';
		    $randomString = '';

		    do
		    {
		    	for($i=0; $i<10; $i++)
		    	{
		    		if( $i<3 || ($i>5 && $i<8) )
		    			$randomString .= $letters[rand(0, 25)];
				  	
				  	else if( ($i>2 && $i<6) || ($i>7) )
				   		$randomString .= $digits[rand(0, 9)];	   	   
		    	}    
		    }
		    while( $this->select('customer_api_keys', '*', array('auth_api_key'=>$randomString), 1) > 0 );

		    return $randomString;
		}
		



		/**
		 * Generate a random 32 character message id comprising of alphanumeric characters
		 * @return [type] [description]
		 */
		function random_message_id()
	    {
		    $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
		    $pass = array(); //remember to declare $pass as an array
		    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		    for ($i = 0; $i < 32; $i++) {
		        $n = rand(0, $alphaLength);
		        $pass[] = $alphabet[$n];
		    }
		    return implode($pass); //turn the array into a string
		}//function end


		/**
		 * Calcualte the total number of messages that were used for this transactions
		 * @param  [type] $mobiles [description]
		 * @param  [type] $message [description]
		 * @return [type]          [description]
		 */
		function calculate_total_messages_used($mobiles, $message){
			//first check if $message is less than or equal to 140, since 1 message means 140 characters
			//since the message would be urlencoded, decode it first
			$message = strlen(urldecode($message));
			$message_count = ceil($message/140);
			$numbers_count = count(explode(',', $mobiles));
			$total_messages = $message_count * $numbers_count;
			return $total_messages;
		}//function

		/**
		 * Update remaining balance count for the user
		 * @param  [type] $totalMessagesUsed [description]
		 * @param  [type] $column            [description]
		 * @param  [type] $customer_id       [description]
		 * @return [type]                    [description]
		 */
		function update_message_credits_available($totalMessagesUsed, $column, $customer_id)
		{
			global $conn1;global $conn2;
			try
			{
				$s = $conn1->prepare("UPDATE customer_balances SET $column = $column-$totalMessagesUsed WHERE $customer_id = :c");
				$s->bindParam(':c', $customer_id);
				$s->execute();
			}
			catch(PDOException $e)
			{
				echo $e->getMessage();
			}
		}//function


		/**
		 * Function to check whether credits are available for a specific route or not
		 * @param  [type] $auth_token        [description]
		 * @param  [type] $route             [description]
		 * @param  [type] $totalMessagesUsed [description]
		 * @return [type]                    [description]
		 */
		function credits_available($auth_api_key, $route, $totalMessagesUsed)
		{
			$customer_details = self::select("customer_api_keys", "*", array("auth_api_key" => $auth_api_key));
			$credits_available = self::select("customer_balances", '*', array("customer_id" => $customer_details->customer_id));

			if($route == "txn_balance")
			{
				return $credits_available->txn_balance;
			}
			if($route == "pr_balance")
			{
				return $credits_available->pr_balance;
			}
			if($route == "otp_balance")
			{
				return $credits_available->otp_balance;
			}
		}//function



		/**
		 * @param  [type]
		 * @return [type]
		 */
		function sendOutput ($data)
		{
			echo json_encode($data);
		}

		/**
		 * Function to ouput the error that was generated while using the API
		 * @param  [type]
		 * @return [type]
		 */
		function output_error($error_number, $custom_msg = null)
		{
			//fetch the error details from the error number
			$return = "";
			if(!is_null($custom_msg))
			{
				$return = array("request_successful" => 0, "error_code" => "ER".$error_number, "error_desc" => $custom_msg);
			}
			else
			{
				$error_details = self::select("api_error_codes", "*", array("error_number" => $error_number));
				$return = array("request_successful" => 0, "error_code" => $error_details->error_id, "error_desc" => $error_details->error_desc);
			}
			//pass to sendOutput now
			self::sendOutput($return);
		}


		/**
		 * Function to register a scheduled message
		 * @param  [type] $data [description]
		 * @return [type]       [description]
		 */
		function register_schedule_msg($data)
		{
			global $conn1;
			global $conn2;
			
			//get the data from post
			$mobiles = isset($data['mobiles']) ? $data['mobiles'] : '';
			$message = isset($data['message']) ? $data['message'] : '';
			$sender_id = isset($data['sender_id']) ? $data['sender_id'] :'';
			$send_on = isset($data['send_on']) ? $data['send_on'] : '';
			$customer_id = isset($data['customer_id']) ? $data['customer_id'] : '';
			$auth_token = isset($data['auth_token']) ? $data['auth_token'] : '';

			$totalMessagesUsed = self::calculate_total_messages_used($mobiles, $message);

			if(!self::credits_available($auth_token, "pr_balance", $totalMessagesUsed))
			{
				self::output_error(402);
				die();
			}

			//first level data verifications
			if(empty($mobiles))
			{
				self::output_error(301, "Parameter missing : mobiles");
				die();
			}
			if(empty($message))
			{
				self::output_error(301, "Parameter missing : message");
				die();
			}
			if(empty($sender_id))
			{
				self::output_error(301, "Parameter missing : sender_id");
				die();
			}
			if(empty($sender_id))
			{
				self::output_error(301, "Parameter missing : send_on");
				die();
			}
			//end first level data verifications
			
			//second level data verifications -namely verifying mobile number and sender ID
			//verify sender ID now
			if((strlen($sender_id) != 6) || (ctype_alpha($sender_id) == false))
			{
				self::output_error(302);
				die();
			}
			//End second level data verifications

			//Generate data to be sent to the function
			$data = array();
			$data['mobiles'] = $mobiles;
			$data['message'] = $message;
			$data['sender_id'] = $sender_id;
			$data['send_on'] = $send_on;
			// $response = sendsms_pr($data); //calling function from provider functions file
			// $ns_key = self::recursive_generator("ns_key", $type = "msg_id", "messages_sent");

			//return response to user
			// $return = array("request_successful" => 1, "error_code" => "", "error_desc" => "", "message_id" => $ns_key);
			// self::sendOutput($return);

			//DB Insert code begin
			// $date = @date("Y-m-d H:i:s");
				
			$customer_details = self::select("customer_api_keys", "*", array("auth_api_key" => $auth_token));
			$customer_id = $customer_details->customer_id;

			//insert into DB now
			$insert_array = array("customer_id" => $customer_id, "number" => $mobiles, "sender_id" => $sender_id, "message" => $message, "send_on" => $send_on);
			self::insert("scheduled_messages",$insert_array);
			//update the MSG count now
			// self::update_message_credits_available($totalMessagesUsed, "pr_balance", $customer_id);
			// 
			//return response to user
			$return = array("request_successful" => 1, "error_code" => "", "error_desc" => "");
			self::sendOutput($return);
		}

		function register_create_group($data)
		{
			global $conn1;global $conn2;
			
			//get the data from post
			$mobiles = isset($data['mobiles']) ? $data['mobiles'] : '';
			$group_name = isset($data['group_name']) ? $data['group_name'] : '';
			$customer_id = isset($data['customer_id']) ? $data['customer_id'] : '';
			
			//first level data verifications
			if(empty($mobiles))
			{
				self::output_error(301, "Parameter missing : mobiles");
				die();
			}
			if(empty($group_name))
			{
				self::output_error(301, "Parameter missing : group_name");
				die();
			}
			if(empty($customer_id))
			{
				self::output_error(301, "Parameter missing : customer_id");
				die();
			}
			//end first level data verifications
			
			//removing spaces from $mobiles
			$mobiles = str_replace(" ", "", $mobiles);	

			//generating unique 5 digit group ID
			while(true)
			{
				$group_id = mt_rand(10000, 99999);
				try
				{
					$query = $conn2->prepare("SELECT * FROM message_groups WHERE group_id = :group_id");
					$query->bindParam(':group_id', $group_id);
					$query->execute();
				}
				catch(PDOException $e)
				{
					echo $e->getMessage();
				}

				//checking if chosen group ID already exists
				if($query->rowCount()==0)
					break;
			}

			//insert into DB now
			$insert_array = array("customer_id" => $customer_id, "group_name" => $group_name, "group_id" => $group_id, "mobiles_list" => $mobiles );
			self::insert("message_groups", $insert_array);
		
			//return response to user
			$return = array("request_successful" => 1, "error_code" => "", "error_desc" => "");
			self::sendOutput($return);
		}

		function format_date($date)
		{
			$m = $this->get_month_name( substr($date, 5, 2) );
			if( date('Y-m-d')==substr($date, 0, 10) )
				return $this->convert_to_12hr( substr($date, 11, 5) );
			else if( date('Y')==substr($date, 0, 4) )
				return substr($date, 8, 2).' '.$m.' '.$this->convert_to_12hr( substr($date, 11, 5) );
			else
				return substr($date, 8, 2).' '.$m.' '.substr($date, 0, 4).' '.$this->convert_to_12hr( substr($date, 11, 5) );
		}

		function convert_to_12hr($time)
		{
			if( substr($time, 0, 2) >= 12 )
			{
				if( substr($time, 0, 2)!=12 )
					return ( substr($time, 0, 2)-12 ).substr($time, 2).'pm';
				else
					return ( substr($time, 0, 2) ).substr($time, 2).'pm';
			}
			else
				return $time.'am';
		}

		function get_month_name($month)
		{
			switch ($month) 
			{
				case '01':
					return 'Jan';
					break;
				case '02':
					return 'Feb';
					break;
				case '03':
					return 'Mar';
					break;
				case '04':
					return 'Apr';
					break;
				case '05':
					return 'May';
					break;
				case '06':
					return 'June';
					break;
				case '07':
					return 'July';
					break;
				case '08':
					return 'Aug';
					break;
				case '09':
					return 'Sep';
					break;
				case '10':
					return 'Oct';
					break;
				case '11':
					return 'Nov';
					break;
				case '12':
					return 'Dec';
					break;
			}
		}

		function getRowCount($conn2, $table_name, $where_array = null)
		{
			global $conn2;
			
			//check if the select is a direct select or one with while flags included
			if($where_array && count($where_array) > 0)
			{
				//where array has some values, so we need to run with the where command

				//fix the where_array with ? 
				$where_flag = array();
				$where_flag_values = array();
				foreach($where_array as $key=>$value)
				{
					$where_flag[] = "$key = ?";
					$where_flag_values[] = $value;
				}
				$where_flag_string = implode(' AND ', $where_flag);
				
				try
				{
					$s = $conn2->prepare("SELECT * FROM $table_name WHERE $where_flag_string");
					//generate the bindParams
					$s->execute($where_flag_values);
				}
				catch(PDOException $e)
				{
					echo $e->getMessage();
				}				
			}
			else
			{
				//where array doesn't have any value, run directly
				try
				{
					$s = $conn2->query("SELECT * FROM $table_name");
				}
				catch(PDOException $e)
				{
					echo $e->getMessage();
				}					
			}
			
			return $s->rowCount();				
		}//function


		//To fetch a specific column value of a row
		function getColumnValue($table, $column, $where_array)
		{
			global $conn2;

			//fix the where_array with ? 
			$where_flag = array();
			$where_flag_values = array();
			foreach($where_array as $key=>$value)
			{
				$where_flag[] = "$key = ?";
				$where_flag_values[] = $value;
			}
			$where_flag_string = implode(' AND ', $where_flag);

			try
			{
				$s = $conn2->prepare("SELECT $column FROM $table WHERE $where_flag_string");
				$s->execute($where_flag_values);
			}
			catch(PDOException $e)
			{
				echo $e->getMessage();
			}

			$row = $s->fetch(PDO::FETCH_OBJ);
			return $row->$column;
		}


		function sendOTP($email)
		{
			global $conn1;
			global $conn2;
			$code = self::genOTP();
			//get customer data
			$clientInfo = self::select("clients", "*", array("c_email" => $email));
			
			//send OTP now to customer using WF SMS API now
			$number = $clientInfo->c_contactNumber;
			// sendSMSVS($number, $code);
			var_dump(sendSMSVS($number, $code));
			//end OTP sentcode
			
			
			$genOn = @date("Y-m-d H:i:s");
			//delete all from the 2fa table for this email id
			self::delete_function("2fa", array("email" => $email));
			//insert into 2fa now
			$insert_array = array("email" => $email, "phone" => $clientInfo->c_contactNumber, "code" => $code, "generated_on" => $genOn);
			self::insert("2fa", $insert_array);
			// return $response;
		}

		function genOTP()
		{
			//generate a 4 digit code
			$code = mt_rand(1111,4444);
			if(self::field_exists("code", $code, "2fa"))
			{
				self::genOTP();
			}
			else
			{
				return $code;
			}
		}//function


		function startsWith($haystack, $needle)
		{
		     $length = strlen($needle);
		     return (substr($haystack, 0, $length) === $needle);
		}

		function endsWith($haystack, $needle)
		{
		    $length = strlen($needle);

		    return $length === 0 || 
		    (substr($haystack, -$length) === $needle);
		}

		/**
		 * Function to delete a row from a table given by where_array
		 * @param  [type] $table_name  [description]
		 * @param  [type] $where_array [description]
		 * @return [type]              [description]
		 */
		function delete_function($table_name, $where_array)
		{
		  global $conn1;
		  global $conn2;
		  if(count($where_array) > 0)
		  {
		    $where_flag = array();
		    $where_flag_values = array();
		    foreach($where_array as $key=>$value)
		    {
		      $where_flag[] = "$key = ?";
		      $where_flag_values[] = $value;
		    }
		    $where_flag_string = implode(' AND ', $where_flag);
		    //
		    try{
		      $s = $conn1->prepare("DELETE from $table_name where $where_flag_string");
		      //generate the bindParams
		      $s->execute($where_flag_values);
		    }
		    catch(PDOException $e){
		      echo $e->getMessage();
		    }
		  }
		  else
		  {
		    try
		    {
		      $s = $conn->query("DELETE FROM $table_name");
		    }
		    catch(PDOException $e)
		    {
		      echo $e->getMessage();
		    }
		  }//else
		}//function
		



		//switch language
		function _lang($key, $language)
		{
			global $base;
			$query = $base->select('lang', '*', array('key_'=>$key));
			
			if( $language=='en' )
				return $query->english;
			else
				return $query->swedish;
		}
	}//class
?>