<?php 

session_start();
//initialize variables

$username = '';
$email = '';
$errors = array();


//Register users
if (isset($_POST['reg_user'])){

	include 'connect_db.php';
	$username = mysqli_real_escape_string($db, $_POST['username']);
	$email = mysqli_real_escape_string($db, $_POST['email']);
	$password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
	$password_2 = mysqli_real_escape_string($db, $_POST['password_2']);

	//form validation

	if (empty($username)) {array_push($errors, "Username is required");}
	if (empty($email)) {array_push($errors, "Email is required");}
	if (empty($password_1)) {array_push($errors, "Password is required");}
	if ($password_1 != $password_2) {array_push($errors, "Passwords dose not match");}

	//check db for users with the same username or same email addresses

	$user_check_query = "SELECT * FROM users WHERE username = '$username' or email = '$email' LIMIT 1";

	$results = mysqli_query($db, $user_check_query) or die('could not do the query in line 35');
	$user = mysqli_fetch_assoc($results);

	if ($user){
		if ($user['username'] === $username){array_push($errors, "Username is already taken");}
		if ($user['email'] === $email){array_push($errors, "Another username is already registered with that email id");}
	}

	// Regiter the user if there are no more errors

	if (count($errors) === 0){

		$password = md5($password_1);
		$query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

		mysqli_query($db, $query) or die('could not do the query in line 50');

		
	}
	mysqli_close($db);
}

//Log in users
if (isset($_POST['login_user'])){

	$username = $_POST['username'];
	$password = md5($_POST['password_1']);

	//check db for the user

	include 'connect_db.php';
	$user_verify_query = "SELECT * FROM users WHERE username = '$username' and password = '$password' LIMIT 1";
	$results = mysqli_query($db, $user_verify_query);
	$user = mysqli_fetch_assoc($results);

	if ($user){
		$_SESSION['username'] = $user['username'];
		$_SESSION['status'] = $user['status'];
		$_SESSION['success'] = "You have successfully logged in.";
		header('location: index.php');
	}else{
		array_push($errors, 'Incorrect username/password combination');
	}

	mysqli_close($db);

}

//User management
if (isset($_COOKIE['action']) and isset($_COOKIE['action_id'])){

	$action = $_COOKIE['action'];
	$id=$_COOKIE['action_id'];
	
	if ($action === 'rem_user') {
		$query = "DELETE FROM users WHERE `id` = $id";
	}elseif ($action === 'grant_admin'){
		$query="UPDATE `users` SET `status`='Admin' WHERE `id`=$id";
	}elseif ($action === 'rev_admin'){
		$query="UPDATE `users` SET `status`='Poster' WHERE `id`=$id";
	}

	include 'connect_db.php';
	mysqli_query($db, $query);
	mysqli_close($db);
	setcookie("action", "", time() - 3600);
	setcookie("action_id", "", time() - 3600);

}

//Save the published quiz's answers in the db
if (isset($_POST['publish_quiz'])){

	// foreach ($_POST as $key => $value) {
	// 	echo $key."==>".$value."<br>";
	// }

	// creates the query by iterating over the post array

	//initializes variables
	$ques_data = array();
	$first_ques = TRUE;
	$ques_num = 1;
	$correct_answer = FALSE;
	$atleast_one_correct = FALSE;
	$num_of_ques = 0;

	//iteration
	foreach ($_POST as $key => $value) {
		//echo $key,'=>',$value,"<br>";

		// builds basic data

		$query_beg = "INSERT INTO quizes (publisher, time_stamp, subject, quiz_name, quiz_id_name, filename, time_alloc";

		if ($key==='subject'){
			$query_trai = ") VALUES ('".$_SESSION['username']."', ".time().", '".$value."'";
			
		}elseif ($key === 'quiz_name'){
			$query_trai = $query_trai.", '".$value."', 'quiz";
			
		}elseif ($key === 'pub_time'){
			$query_trai = $query_trai.$value."'";
		}elseif ($key === 'uploaded_file_name'){

			$filename = $_FILES['uploaded_file']['name'];
			$filename_parts = pathinfo($filename);
			$date = date("[d,m,Y]");
			$edited_filename = $filename_parts['filename']."_".$date.".".$filename_parts['extension'];

			$query_trai = $query_trai.", '".$edited_filename."'";

			$target = "quiz_docs/".$edited_filename;
			if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $target)) {
				echo "Document uploaded successfully.<br>";
			}else{
				echo "Problem in uploading the document.<br>";
			}

		}elseif ($key === 'time_alloc'){
			$query_trai = $query_trai.", ".$value;
		//builds question data
		}elseif (substr($key, 0, 1) === 'Q'){
			if (substr($key, 3,1)=='Q'){
				if ($first_ques){
					array_push($ques_data, $value);
					$first_ques = FALSE;
				}else{
					if (!($atleast_one_correct)){
						for ($i=1; $i<6 ; $i+1) { 
							$ques_data[$i]='1'.substr($ques_data[$i], 1);
						}
					}else{
						$atleast_one_correct = FALSE;
					}
					$num_of_ques += 1;
					$query_trai = $query_trai.", '".implode('#',$ques_data)."'";
					unset($ques_data);
					$ques_data = array();
					array_push($ques_data, $value);
					$ques_num += 1;
				}
			}elseif (substr($key,3,2) == 'CB'){
				$correct_answer = TRUE;
				$atleast_one_correct = TRUE;
			}elseif (substr($key,3,2)=='CT'){
				if ($correct_answer){
					array_push($ques_data, "1~".$value);
					$correct_answer = FALSE;
				}else{
					array_push($ques_data, "0~".$value);
				}
			}
			
		}
	}

	//echo $query_beg, $atleast_one_correct, $query_trai, '<br>';
	//print_r($ques_data);
	
	if ($atleast_one_correct){
		$num_of_ques +=1;
		$query_trai = $query_trai.", '".implode('#',$ques_data)."'";
		unset($ques_data);
	}
	
	for ($ques_num=1; $ques_num <=$num_of_ques ; $ques_num++) { 
		if ($ques_num<10){
			$formatted_ques_num = '0'.$ques_num;
		}else{
			$formatted_ques_num = strval($ques_num);
		}
		$query_beg = $query_beg.", ques_".$formatted_ques_num;
	}

	$query =  $query_beg.$query_trai. ")";
	//echo $query;
	
	//Creates the connection with the db and runs the query
	include 'connect_db.php';
	mysqli_query($db, $query);
	mysqli_close($db);
	echo "Quiz added successfully.<br>";
	

}


if (isset($_POST['start_time'])) {



	$start_time = $_POST['start_time'];
	$quiz_taker = $_POST['quiz_taker'];
	$quiz_id_name = $_POST['quiz_id_name'];

	//create the connection
	include 'connect_db.php';

	//add a new field if there are no existing quiz fields
	//checks the db for existing fields
	$query = "SELECT quiz_id_name FROM resps WHERE quiz_id_name = '".$quiz_id_name."'";
	
	$results = mysqli_query($db, $query);
	
	//if nothing was returned add the new quiz field
	if (mysqli_num_rows($results) ==0){
		$query = "INSERT INTO resps (`quiz_id_name`) VALUES ('".$quiz_id_name."')";
		$results = mysqli_query($db, $query);
	}



	//get the names of all the columns into a single array
	$query = "SELECT * FROM resps LIMIT 1";
	$results = mysqli_query($db, $query);

	$tab_cols = array();
	$arr = mysqli_fetch_array($results);

	foreach ($arr as $key => $value) {
		if (!(is_numeric($key))){
			array_push($tab_cols, $key);
		}		
	}

	//if there is no column, create a new column
	if (!(in_array($quiz_taker, $tab_cols))){
		$query = "ALTER TABLE  `resps` ADD  `".$quiz_taker."` VARCHAR(255)";
		mysqli_query($db, $query);
	}

	//checks whether there are no previous inputs. If yes add the start_time. 
	$query = "SELECT `".$quiz_taker."` FROM resps WHERE `quiz_id_name` = '".$quiz_id_name."'";
	$results = mysqli_query($db, $query);
	$data = mysqli_fetch_array($results);

	if ($data[0]==""){
		$query = "UPDATE `resps` SET `".$quiz_taker."`='".$start_time."' WHERE `quiz_id_name` = '".$quiz_id_name."'";
		mysqli_query($db, $query);
	}elseif (is_numeric($data[0])){
		echo $data[0];
	}else{
		echo "You have already taken this quiz. You may attempt again. But your score will not be updated in the leaderboard";
	}
	//close the connection
	mysqli_close($db);
}

//add the results to the database
if (isset($_POST['score'])){

	$quiz_taker = $_POST['quiz_taker'];
	$quiz_id_name = $_POST['quiz_id_name'];
	$time_taken = $_POST['time_taken'];
	$score = $_POST['score'];
	$quiz_results_arr = $_POST['quiz_results_arr'];


	include 'connect_db.php';
	$query = "SELECT `".$quiz_taker."` FROM resps WHERE `quiz_id_name` = '".$quiz_id_name."'";
	$results = mysqli_query($db,$query);
	$start_time = mysqli_fetch_array($results)[0];

	if (is_numeric($start_time)) {
		$update = $start_time.','.$time_taken.','.$score.','.$quiz_results_arr;

		$query = "UPDATE `resps` SET `".$quiz_taker."`='".$update."' WHERE `quiz_id_name` = '".$quiz_id_name."'";
		mysqli_query($db,$query);

		mysqli_close($db);
	}
		
}

 ?>

