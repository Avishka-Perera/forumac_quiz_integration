<?php include('server.php') ?>
<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="style.css">
	<link rel="stylesheet" type="text/css" href="fonts.css">
</head>
<body>
	<div id="login_div">
		<div class = "header">
			<h2>LOGIN</h2>
		</div>

		<form action="login.php" method="post" >
			<?php include('errors.php') ?>
			<table id="login_table">
				<tr>
					<td class="left_column" ><label for="username">Username: </label></td>
					<td class="right_column"><input type="text" name="username" required></td>
				</tr>
				<tr>
					<td class="left_column"><label for="password_1">Password: </label></td>
					<td class="right_column"><input type="password" name="password_1" required></td>
				</tr>
			</table>
			<button type="Submit" name="login_user" id="login_btn" class="act_btn" style="width: 100px;"> Login </button>
		</form>
	</div>
</body>
</html>