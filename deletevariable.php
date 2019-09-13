<?php
require_once 'inc/util.inc';
session_start();
$_SESSION['error'] = "";
if (isset($_COOKIE['user'])) {
	$username = $_COOKIE['user'];
	$password = $_COOKIE['pass'];
	$db_link = mysqli_connect($db, $db_user, $db_pass);
	if (!$db_link)
		error(mysqli_error());
	mysqli_query($db_link, "SET NAMES utf8");
	mysqli_query($db_link, "SET CHARACTER SET utf8");
	mysqli_query($db_link, "SET SESSION interactive_timeout=30");
	mysqli_select_db($db_link, $dbname);
	$user = mysqli_query($db_link, "SELECT * FROM user WHERE firstname = '$username'");
	if (!$user)
		error(mysqli_error());
	while ($userinfo = mysqli_fetch_array($user)) {
		if ($password != $userinfo['password']) {
			header("Location: login.php");
			return;
		} else {
			$_SESSION['unlocked'] = $userinfo['unlocked'];
			if (!(bool) $_SESSION['unlocked']) {
				header("Location: unlock.php");
				return;
			}
			$id = $_GET["id"];
			$userid = $userinfo['id'];
			$sql = "DELETE FROM query_variable WHERE userid = '$userid' AND variableid='$id'";
			$result = mysqli_query($db_link, $sql);
			if (!$result)
				error(mysqli_error());
			header("location: informationmodel.php#queryvariables");
		}
	}
	mysqli_free_result($user);
	mysqli_close($db_link);
} else {
	header("Location: login.php");
	return;
}
?>