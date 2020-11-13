<?php
include 'server.php';
include 'errors.php';

if (!isset($_SESSION['username'])){

	$_SESSION['msg'] = "You mus log in first to vie this page";
	header('location: login.php');
}

if (isset($_GET['logout'])){
	session_destroy();
	unset($_SESSION['username']);
	header('location: login.php');
}

 ?>

<?php $pub_time = time();?>

 <!DOCTYPE html>
 <html>
 <head>
 	<title>Admin Area</title>

	<link rel="stylesheet" type="text/css" href="style.css">
	<link rel="stylesheet" type="text/css" href="fonts.css">
 	<script src="js/jquery-3.5.1.js"></script>
	<script type="text/javascript">
		let active_fields = [];
		function manage_user_js(action, action_id){
			document.cookie = "action=" + action;
			document.cookie = "action_id=" + action_id;
		}
		function rem_conf_open(div_id){
			$("#rem_conf_div_".concat(div_id)).show(500);
		}
		function rem_conf_colapse(div_id){
			$("#rem_conf_div_".concat(div_id)).hide(500);
		}
		function publish_quiz_func(pub_time){

			quiz_name = document.getElementById("quiz_name_text").value

			if (quiz_name != ''){
				document.getElementById("quiz_name_text").hidden = true;
				document.getElementById("quiz_name_text_disp").value = document.getElementById("quiz_name_text").value;
				document.getElementById("quiz_name_text_disp").hidden = false;
				document.getElementById("quiz_name_text_disp").disabled = true;
				alert('Please copy the following embeding link and paste in the forumAC post.  <iframe src="quizes.php?quiz_name='+pub_time+'" width="500px" height="500px" frameborder="0" style="margin: auto; display: block; width: 500px; height: 700px; border-left: 1px solid lightsteelblue; border-right: 1px solid lightsteelblue;">Loading....</iframe>');
				$("#publish_div").show(1000);				
			}else{
				alert('Please enter a quiz name');
			}
		}

		

		//Creates a new question field when the final question field is clicked
		function ques_div_click(div_num){
			if (!(active_fields.includes(div_num)) && active_fields.length<49) { //the limit here must be exactly one less than the nunmber of question columns in the database
				active_fields.push(div_num);
				ques_num = active_fields.length+1;
				if (ques_num<10) {
					disp_ques_num = '0'+ ques_num.toString();
				}else{
					disp_ques_num = ques_num.toString();
				}
				ques_num = ques_num.toString();
				var new_ques_div = $(
				'<div name = "Q'+ques_num+'" onclick="ques_div_click('+ques_num+')" class="question_div">\
			 		<span>\
			 			<input type="text" name="Q'+ques_num+'Q" value="Question '+disp_ques_num+'" style="width: 90px;">\
			 		</span>\
			 		<span>\
			 			<input type="checkbox" name="Q'+ques_num+'CB1">\
			 		</span>\
			 		<span>\
			 			<input type="text" name="Q'+ques_num+'CT1" value="(1)" style="width: 20px;">\
			 		</span>\
			 		<span>\
			 			<input type="checkbox" name="Q'+ques_num+'CB2">\
			 		</span>\
			 		<span>\
			 			<input type="text" name="Q'+ques_num+'CT2" value="(2)" style="width: 20px;">\
			 		</span>\
			 		<span>\
			 			<input type="checkbox" name="Q'+ques_num+'CB3">\
			 		</span>\
			 		<span>\
			 			<input type="text" name="Q'+ques_num+'CT3" value="(3)" style="width: 20px;">\
			 		</span>\
			 		<span>\
			 			<input type="checkbox" name="Q'+ques_num+'CB4">\
			 		</span>\
			 		<span>\
			 			<input type="text" name="Q'+ques_num+'CT4" value="(4)" style="width: 20px;">\
			 		</span>\
			 		<span>\
			 			<input type="checkbox" name="Q'+ques_num+'CB5">\
			 		</span>\
			 		<span>\
			 			<input type="text" name="Q'+ques_num+'CT5" value="(5)" style="width: 20px;">\
			 		</span>\
			 	</div>'
			 	);
       			$('#quiz_temp').append(new_ques_div);
			}
		}

		
	</script>
 </head>
 <body style="text-align: center;">

 	<div style="text-align: right;">
	 	<?php if (isset($_SESSION['success'])) :?>
	 		<div>
	 			<p>
	 				<?php
	 				echo $_SESSION['success'];
	 				unset($_SESSION['success']);
	 				?>
	 			</p>
	 		</div>

	 	<?php endif ?>
		<p>Logged in as <STRONG> <?php echo $_SESSION['username']; ?></STRONG></p>
	 	<a href="index.php?logout='1'"><button class="nav_btn" style="width: 100px">Log out</button></a>
 	</div>
	
 	<!--Create a new quiz-->
 	<h2>Create a New Quiz</h2>

 	<form action="index.php" method="post" id="quiz_temp" enctype="multipart/form-data" class="quiz_creator_form">

 		<!-- Submit and clear buttons with double checking-->
 		<div id="publish_clear_div">
		 	<button type="button" id="publish_open_btn" onclick="publish_quiz_func('<?php echo $pub_time?>')" class="act_btn" style="width: 80px">Publish</button>
		 	<button type="button" id="refresh_n_clear_open_btn" class="act_btn" style="width: 80px">Clear</button>
	 	</div>
	 	<div id="publish_div" style="display: none; border: solid;">
	 		<p>Are you sure all the entered data are correct?</p>
	 		<button name="publish_quiz" class="nav_btn" style="width: 50px">Yes</button>
	 		<button type="button" id="publish_colapse_btn" class="nav_btn" style="width: 50px">No</button>
	 	</div>
	 	<div id="refresh_n_clear_div" style="display: none; border: solid;">
	 		<p>Are you sure you want to refresh and clear the quiz maker?</p>
	 		<button class="nav_btn" style="width: 50px">Yes</button>
	 		<button type="button" id="refresh_n_clear_colapse_btn" class="nav_btn" style="width: 50px">No</button>
	 	</div>

	 	<!--Select the subject and quiz name-->
	 	<div id="quiz_table_div">
	 		<table id="quiz_dip_table">
	 			<tr>
			 		<td class="left_column">
			 			<label for="subject">Subject: </label>
			 			<select name="subject" required>
			 				<option hidden disabled selected value>--select--</option>
			 				<option value="math">Mathematics</option>
			 				<option value="phys">Physics</option>
			 				<option value="chem">Chemistry</option>
			 			</select>
			 		</td>
			 		<td class="right_column">
			 			<label for="quiz_name">Quiz name: </label>
			 			<input type="text" name="quiz_name" id="quiz_name_text" required>
			 			<input type="text" name="pub_time" value="<?php echo $pub_time?>"  hidden>
			 			<input type="text" name="quiz_name_disp" id="quiz_name_text_disp" hidden>
			 		</td>
			 	</tr>
			 	<tr>
			 		<td class="left_column">
			 			<label for="uploaded_file">Question pdf: </label>
			 			<input type="file" name="uploaded_file" required style="width: 180px">
			 			<input type="hidden" name="uploaded_file_name">
			 		</td>
			 		<td class="right_column">
			 			<label for="time_alloc">Duration (min): </label>
			 			<input type="text" name="time_alloc" value="120" style="width: 30px">
			 		</td>
		 		</tr>
	 		</table>
	 	</div>

	 	

	 	<!-- Creates the initial question-->
	 	<div name = "Q1" onclick="ques_div_click(1)" class="question_div">
	 		<span>
	 			<input type="text" name="Q1Q" value="Question 01" style="width: 90px;">
	 		</span>
	 		<span>
	 			<input type="checkbox" name="Q1CB1">
	 		</span>
	 		<span>
	 			<input type="text" name="Q1CT1" value="(1)" style="width: 20px;">
	 		</span>
	 		<span>
	 			<input type="checkbox" name="Q1CB2">
	 		</span>
	 		<span>
	 			<input type="text" name="Q1CT2" value="(2)" style="width: 20px;">
	 		</span>
	 		<span>
	 			<input type="checkbox" name="Q1CB3">
	 		</span>
	 		<span>
	 			<input type="text" name="Q1CT3" value="(3)" style="width: 20px;">
	 		</span>
	 		<span>
	 			<input type="checkbox" name="Q1CB4">
	 		</span>
	 		<span>
	 			<input type="text" name="Q1CT4" value="(4)" style="width: 20px;">
	 		</span>
	 		<span>
	 			<input type="checkbox" name="Q1CB5">
	 		</span>
	 		<span>
	 			<input type="text" name="Q1CT5" value="(5)" style="width: 20px;">
	 		</span>
	 	</div>
 	</form>


 	

 	<!-- If the user is an Admin -->
 	<?php if ($_SESSION['status'] == 'Admin'): ?>

 		<h2>Manage Users</h2>

 		<div>
 			<span>

		 		<!--creates new users-->

				<div id="Reg_new_user_div">
					<div >
						<h3>Register new users</h3>
					</div>

					<form action="index.php" method="post">
						<table id="reg_user_table">
							<tr>
								<td class="left_column"><label for="email">Email: </label></td>
								<td class="right_column"><input type="email" name="email" required></td>
							</tr>
							<tr>
								<td class="left_column"><label for="username">Username: </label></td>
								<td class="right_column"><input type="text" name="username" required></td>
							</tr>
							<tr>
								<td class="left_column"><label for="password_1">Password: </label></td>
								<td class="right_column"><input type="password" name="password_1" required></td>
							</tr>
							<tr>
								<td class="left_column"><label for="password_2">Confirm Password: </label></td>
								<td class="right_column"><input type="password" name="password_2" required></td>
							</tr>
						</table>
						<button type="Submit" name="reg_user" class="act_btn" style="width: 100px;"> Create </button>
						
					</form>
				</div>
			</span>

			<span>

				<!--Manages existing user privileges-->
				<?php
					$db = mysqli_connect('localhost', 'root', '', 'forumac_quiz_integration');
					$retrieve_user_query = "SELECT * FROM users WHERE status = 'Poster'";
					$posters = mysqli_query($db, $retrieve_user_query) or die('Cannot run the query to retrieve users');
					$retrieve_user_query = "SELECT * FROM users WHERE status = 'Admin'";
					$Admins = mysqli_query($db, $retrieve_user_query) or die('Cannot run the query to retrieve users');
					mysqli_close($db);
				?>
				<form action="index.php" method="post">
				<div id="Manage_user_div" style="display: none;">
					<h3>Manage Users</h3>
					<br>
					<h3 class="admin_poster">Posters</h3>
					<hr>

					<table class="manage_user_table">
						<?php while ($poster = mysqli_fetch_array($posters)) :?>
							<tr>
								<td>
									<?php echo $poster['username'];
									$i = $poster['id'] ?>
								</td>

								<td>
									<button  onclick="manage_user_js('grant_admin', '<?php echo $i;?>')" class="act_btn">Grant Admin</button>
									<button type="button" onclick="rem_conf_open('<?php echo $i;?>')" class="act_btn">Remove user</button>
									<div id="rem_conf_div_<?php echo $i;?>" style="display: none;">
										<p> Are you sure you want to remove this user?</p><br>
										<button  onclick="manage_user_js('rem_user', '<?php echo $i;?>')" class="act_btn" style="width: 50px;">Yes</button>
										<button type="button" onclick="rem_conf_colapse('<?php echo $i;?>')" class="act_btn" style="width: 50px;">No</button>
									</div>
								</td>
							</tr>
							
						<?php endwhile ?>
					</table>
					<h3 class="admin_poster">Admins</h3>
					<hr>
					<table class="manage_user_table">
						<?php while ($Admin = mysqli_fetch_array($Admins)) :?>
							<tr>
								<td>
									<?php echo $Admin['username'];
									$i = $Admin['id'] ?>
								</td>
								<td>
									<button onclick="manage_user_js('rev_admin', '<?php echo $i;?>')" class="act_btn">Revoke Admin</button>
									<button type="button" onclick="rem_conf_open('<?php echo $i;?>')" class="act_btn">Remove user</button>
									<div id="rem_conf_div_<?php echo $i;?>" style="display: none;">
										<p> Are you sure you want to remove this user?</p><br>
										<button  onclick="manage_user_js('rem_user', '<?php echo $i;?>')" class="act_btn" style="width: 50px;">Yes</button>
										<button type="button" onclick="rem_conf_colapse('<?php echo $i;?>')" class="act_btn" style="width: 50px;">No</button>
									</div>
								</td>
							</tr>
						<?php endwhile ?>
					</table>
					
				</div>
				</form>


			</span>
		</div>
		<button id="Reg_new_user_btn" class="nav_btn" style="width: 100px;">New User</button>
		<button id="Manage_user_btn" class="nav_btn" style="width: 100px;">Existing Users</button>
 	<?php endif ?>

 	<script>
		$(document).ready(function(){
			$("#Reg_new_user_btn").click( function(){
				$("#Manage_user_div").hide(500);
				$("#Reg_new_user_div").toggle(500);
			});
			$("#Manage_user_btn").click( function(){
				$("#Reg_new_user_div").hide(500);
				$("#Manage_user_div").toggle(500);
			});
			$("#refresh_n_clear_open_btn").click( function(){
				$("#refresh_n_clear_div").show(500);
			});
			$("#refresh_n_clear_colapse_btn").click( function(){
				$("#refresh_n_clear_div").hide(500);
			});
			$("#publish_colapse_btn").click( function(){
				$("#publish_div").hide(500);
			});
		});
	</script>

 
 </body>
 </html>