<?php
include 'server.php';


//if the 'quiz_name' isset in the $_GET array,
if (isset($_GET['quiz_name'])){
	//grabs the quiz name for the get array, 
	//		1. displays the quiz name
	//		2. Creates a serever variable $ques_data_str which contains all the question data and later will be assigned to a JavaScript variable

	$quiz_id_name = $_GET['quiz_name'];

	include 'connect_db.php';

	$query = "SELECT * FROM quizes WHERE quiz_id_name = '$quiz_id_name'";

	$results = mysqli_query($db, $query) or die("dead");
	$question_data = mysqli_fetch_assoc($results);

	$quiz_name = $question_data['quiz_name'];
	$quiz_id_name = $question_data['quiz_id_name'];
	
	$ques_data_str = '';

	foreach ($question_data as $key => $value) {
		if (substr($key, 0, 3) === 'que' and $value != ''){
			$ques_data_str = $ques_data_str.'|'.$value;
		}
	}

	$ques_data_str = substr($ques_data_str, 1);

	$doc_name = $question_data['filename'];
	$total_time = intval($question_data['time_alloc'])*60;

	//checks whether the user has already started the quiz


	mysqli_close($db);

}


?>

<!--Generated the quiz relevant to the $question_data-->
<?php if (isset($_GET['quiz_name'])) : ?>
	<!DOCTYPE html>
	<html>


	<head>
		<title>Math_Quiz_Page</title>
		
		<script src="js/jquery-3.5.1.js"></script>
		<link rel="stylesheet" type="text/css" href="style.css">
		<link rel="stylesheet" type="text/css" href="fonts.css">

		<!--Creates the array ques_data to easily access the data-->
		<script type="text/javascript">
			
			//takes the string $ques_data_str which was proccessed in the server into a client side string variable ques_data.
			var ques_data_str = "<?php echo $ques_data_str;?>";
			var total_time = <?php echo $total_time ?>;
			var time_left = total_time;
			var quiz_id_name = '<?php echo $quiz_id_name; ?>';
			
			
			var ques_data = ques_data_str.split("|");
			//converts the client side string variable ques_data into an iterable array ques_data.
			
			for (var i = 0; i < ques_data.length; i++) {
				ques_data[i] = ques_data[i].split("#");
				for (var j = 0; j < ques_data[i].length; j++) {
					ques_data[i][j] = ques_data[i][j].split("~");
				}
			}
		
			//function to open the div to confirm submission
			function end_quiz_open_func(){
				$("#end_qiz_div").show(300);
			}
			//funtion to close the confirmation div
			function end_quiz_close_func(){
				$("#end_qiz_div").hide(300);
			}


			//function to get the selected radio button
			function getRadioVal(name){
				var val;

				var radios =document.getElementById('question_body').elements[name];

				for (var i=0; i<radios.length; i++){
					if (radios[i].checked) {
						val = radios[i].value;
						break;
					}
				}
				return val;
			}



			//show answers and submit the results without reloading the page.
			quiz_ended = false;
			ended_once = false;
			function end_quiz_conf_func(){
				if (!ended_once){

					ended_once = true;
					quiz_ended = true;
					$("#end_qiz_div").hide(300);

					var quiz_results_arr = "";
					var quiz_score = 0;
					var tot_score = 0;

					for (var ques_num = 0; ques_num < ques_data.length; ques_num++) {
						if (ques_num<9) {
							proccessed_ques_num = '0'+(ques_num+1).toString();
						}else{
							proccessed_ques_num = (ques_num+1).toString();
						}
						var slctd_answ = getRadioVal('Q'+proccessed_ques_num);
						
						for (var answ_num = 1; answ_num <= ques_data[ques_num].length-1; answ_num++) {
							var truth = ques_data[ques_num][answ_num][0];
							if (truth == '1'){
								correct_answ = ques_data[ques_num][answ_num][1];
								tot_score += 1;

								if (slctd_answ == correct_answ){
									document.getElementById('Q'+proccessed_ques_num).style.backgroundColor = "lightgreen";
									quiz_results_arr+='1';
									quiz_score += 1;
								}else{
									document.getElementById('Q'+proccessed_ques_num).style.backgroundColor = "pink";
									document.getElementById('Q'+proccessed_ques_num+'O'+answ_num.toString()+'TDD').style.backgroundColor = "lightgreen";
									document.getElementById('Q'+proccessed_ques_num+'O'+answ_num.toString()+'TDU').style.backgroundColor = "lightgreen";
									quiz_results_arr+='0';
								}
							}

						}

					}

					

					// const loggedIn = Discourse.User.current();
					// var quiz_taker = loggedIn.username;
					var quiz_taker = 'bypass';
					var time_taken = (total_time-time_left).toString();
					var score = Math.ceil((quiz_score/tot_score)*100);

					
					$.ajax({
						type:"post",
						url:"server.php",
						data:
						{
							'quiz_taker':quiz_taker,
							'quiz_id_name': quiz_id_name,
							'time_taken': time_taken,
							'score': score,
							'quiz_results_arr': quiz_results_arr					
						},
						cache:false,
						success: function(html){
							alert('Quiz ended');
							
						}
					});


					//finally disables all the elements in the form
					all_items=document.getElementById('question_body').elements;
					for (var i = 0; i < all_items.length; i++) {
						all_items[i].disabled=true;
					}			
				}
			}

			//Unhides the quiz. Starts the timer.
			function attempt_quiz_func(){
				document.getElementById("question_body").hidden = false;
				document.getElementById("attempt_quiz_btn").hidden = true;
				start_time = Math.round(Date.now()/1000);
				// const loggedIn = Discourse.User.current();
				// var quiz_taker = loggedIn.username;
				var quiz_taker = 'bypass';

				$.post("server.php",{
					'quiz_taker':quiz_taker,
					'start_time':start_time,
					'quiz_id_name': '<?php echo $quiz_id_name; ?>'
				}, function(data,status) {
					if (data[0]=='1'){
						now_time = Math.round(Date.now()/1000);
						time_left = total_time - (now_time - Number(data));
					}else if(data[0]=='Y'){
						alert(data);
					}
					timer_trigger();
				});
				
			}

			//function to reduce the seconds into the hh:mm:ss format
			function format_time(seconds){
				hours = (Math.floor(seconds/3600));
				if (hours<10){hours = '0'+hours;}
				minutes = (Math.floor((seconds%3600)/60));
				if (minutes<10){minutes = '0'+minutes;}
				seconds = ((seconds%3600)%60);
				if (seconds<10){seconds = '0'+seconds;}

				return hours+':'+minutes+':'+seconds;

			}
			
			//starts/resumes the timer
			function timer_trigger(){
				alerted_once = false
				var timer = setInterval(update_time,1000);
				function update_time(){
					if (time_left<=-1 && alerted_once == false){
						alert('Your time has been ended');
						alerted_once = true;
					}

					if (time_left<=-1 || quiz_ended) {
						clearInterval(timer);
						end_quiz_conf_func();
					}
					if (time_left < 0){
						document.getElementById('time_left').innerHTML = format_time(0);
					}else{
					document.getElementById('time_left').innerHTML = format_time(time_left);
					}
					time_left -= 1;
				}
			}

		</script>
		


	</head>


	<body>
		<div>

			
			<div style="text-align: center;"><?php echo "<h3 class='quiz_name'>".$quiz_name."</h3><br>"; ?></div>
			<div class="timer">Time left: <b><span id="time_left"></span></b></div>


			<br><br><br>
			<div style="text-align: center;"><button id="attempt_quiz_btn" onclick="attempt_quiz_func()" class="act_btn" style="width: 100px;">Attempt Quiz</button></div>
			<form id="question_body" action="quizes.php" hidden>
				<div>
					<span style="margin: 15px;"><label for="download_file">Download the question paper here: </label></span>
					<span><a download="<?php echo $doc_name;?>" href="<?php echo 'quiz_docs/'.$doc_name;?>"><button type="button" class="act_btn" style="width: 100px;">Download</button></a></span>
				</div>
				
			</form>
		</div>
		<script type="text/javascript">

			
			for (var ques_num = 0; ques_num<ques_data.length; ques_num++){
				

				if (ques_num<9) {
					proccessed_ques_num = '0'+(ques_num+1).toString();
				}else{
					proccessed_ques_num = (ques_num+1).toString();
				}

				var question = 
				'<div id="Q'+proccessed_ques_num+'" class="question">\
					<div>Question '+proccessed_ques_num+'</div>\
					<div>\
						<table>\
							<tr>\
								<td id="Q'+proccessed_ques_num+'O1TDU"><label id="Q'+proccessed_ques_num+'O1L">'+ques_data[ques_num][1][1].toString()+'</label></td>\
								<td id="Q'+proccessed_ques_num+'O2TDU"><label id="Q'+proccessed_ques_num+'O2L">'+ques_data[ques_num][2][1].toString()+'</label></td>\
								<td id="Q'+proccessed_ques_num+'O3TDU"><label id="Q'+proccessed_ques_num+'O3L">'+ques_data[ques_num][3][1].toString()+'</label></td>\
								<td id="Q'+proccessed_ques_num+'O4TDU"><label id="Q'+proccessed_ques_num+'O4L">'+ques_data[ques_num][4][1].toString()+'</label></td>\
								<td id="Q'+proccessed_ques_num+'O5TDU"><label id="Q'+proccessed_ques_num+'O5L">'+ques_data[ques_num][5][1].toString()+'</label></td>\
							</tr>\
							<tr id="Q'+proccessed_ques_num+'A">\
								<td id="Q'+proccessed_ques_num+'O1TDD"><input type="radio" name="Q'+proccessed_ques_num+'" value="'+ques_data[ques_num][1][1].toString()+'" id="Q'+proccessed_ques_num+'O1CB"></td>\
								<td id="Q'+proccessed_ques_num+'O2TDD"><input type="radio" name="Q'+proccessed_ques_num+'" value="'+ques_data[ques_num][2][1].toString()+'" id="Q'+proccessed_ques_num+'O2CB"></td>\
								<td id="Q'+proccessed_ques_num+'O3TDD"><input type="radio" name="Q'+proccessed_ques_num+'" value="'+ques_data[ques_num][3][1].toString()+'" id="Q'+proccessed_ques_num+'O3CB"></td>\
								<td id="Q'+proccessed_ques_num+'O4TDD"><input type="radio" name="Q'+proccessed_ques_num+'" value="'+ques_data[ques_num][4][1].toString()+'" id="Q'+proccessed_ques_num+'O4CB"></td>\
								<td id="Q'+proccessed_ques_num+'O5TDD"><input type="radio" name="Q'+proccessed_ques_num+'" value="'+ques_data[ques_num][5][1].toString()+'" id="Q'+proccessed_ques_num+'O5CB"></td>\
							</tr>\
						</table>\
					</div>\
				</div>';
				document.getElementById('question_body').innerHTML += question;
			}

			document.getElementById('question_body').innerHTML += 
			'<div id="end_qiz_div" style="text-align: center; display: none;">\
				<p>Are you sure you want to end the quiz</p>\
				<button type= "button" id="end_quiz_conf_btn" onclick="end_quiz_conf_func()" class="act_btn" style="width: 60px;">Yes</button>\
				<button type= "button" id="end_quiz_close_btn" onclick="end_quiz_close_func()" class="act_btn" style="width: 60px;">No</button>\
			</div>\
			<div style="text-align: center;"><button type="button" id="end_quiz_open_btn" onclick="end_quiz_open_func()" class="nav_btn">End Quiz</button></div>			';

			document.getElementById('time_left').innerHTML = format_time(time_left);

		</script>
		
	</body>
	</html>

<?php endif?>

