<?php
// Report all PHP errors (see changelog)
error_reporting(E_ALL);

// Report all PHP errors
error_reporting(-1);
ini_set('display_errors', -1);
require_once 'inc/util.inc';
session_start();
$_SESSION['error'] = "";
if (isset($_COOKIE['user'])) {
	$username = $_COOKIE['user'];
	$password = $_COOKIE['pass'];
	// $db_link = mysqli_connect($db, $db_user, $db_pass);
	// if (!$db_link)
	// 	error(mysqli_error());
	// mysqli_query($db_link,"SET SESSION interactive_timeout=30");
	// mysqli_select_db($db_link,$dbname);
	$stmt = $mysqli->prepare("SELECT * FROM user WHERE firstname = ?");
	$stmt->bind_param("s", $_POST['username']);
	$stmt->execute();
	$result = $stmt->get_result();
	while($row = $userinfo->fetch_assoc()) {
		if ($password == $userinfo['password']) {
			$_SESSION['unlocked'] = $userinfo['unlocked'];
			if ((bool) $_SESSION['unlocked']) {
				header("Location: index.php");
			} else {
				header("Location: unlock.php");
			}
			return;
		}
	  }
	$stmt->close();

	
}
//if the login form is submitted
if (isset($_POST['submit'])) {
	// makes sure they filled it in
	if (!$_POST['username'] | !$_POST['password']) {
		die('You did not fill in a required field.');
	}
	$stmt = $mysqli->prepare("SELECT * FROM user WHERE firstname = ?");
	$stmt->bind_param("s", $_POST['username']);
	$stmt->execute();
	$result = $stmt->get_result();
	print_r($result->num_rows);
	// If no rows, user does not exists
	if($result->num_rows === 0) exit('That user does not exist in our database.');
	

	$userinfo = $result->fetch_assoc();

	//gives error if the password is wrong
	if ($_POST['password'] != $userinfo['password']) {
		die('Incorrect password, please try again.');
	} else {
	
	// if login is ok then we add a cookie
	$_POST['username'] = stripslashes($_POST['username']);
	$twohours = time() + 7200;
	setcookie('user', $_POST['username'], $twohours);
	setcookie('pass', $_POST['password'], $twohours);
	//then redirect them
	$_SESSION['unlocked'] = $userinfo['unlocked'];
	if ((bool) $_SESSION['unlocked']) {
		header("Location: index.php");
	} else {
		header("Location: unlock.php");
	}
	return;
	}
	

	$stmt->close();
	
} else {
	// if they are not logged in
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>CLIF: Login</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"></link>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container">
		<div style="height: 10px"></div>
		<h1>CLIF: Login</h1>
		<p>
			Please sign in. If you would like to have a user account or encounter
			problems, please <a href="mailto:k.dentler@vu.nl">mail me</a>.
		</p>
		<div style="height: 15px"></div>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<div style="margin-left: 50px; text-align: left;">
				User: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input
					type="text" class="small" name="username"></input><br />Password:
				<input type="password" class="small" name="password"></input> <br />
				<button type="submit" class="btn small primary" name="submit"
					value="submit">login</button>
			</div>
		</form>
	</div>
</body>
</html>
	<?php } ?>
