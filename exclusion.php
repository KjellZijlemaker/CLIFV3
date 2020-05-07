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
			if (isset($_POST['indicatorSelect'])) {
				$_SESSION['indicatorid'] = $_POST['indicatorSelect'];
			}
			if (!isset($_SESSION['indicatorid'])) {
				$_SESSION['indicatorid'] = 1;
			}
			$indicatorid = $_SESSION['indicatorid'];
			$step = "exclusion";
			$userid = $userinfo['id'];
			$commentresult = mysqli_query($db_link, 
					"SELECT * FROM comment WHERE userid = '$userid' AND indicatorid = '$indicatorid' AND step = '$step'");
			if (!$commentresult)
				error(mysqli_error());
			$comment_num_rows = mysqli_num_rows($commentresult);
			while ($commentinfo = mysqli_fetch_array($commentresult)) {
				$comment = $commentinfo['comment'];
				$commentold = $commentinfo['comment'];
			}
			mysqli_free_result($commentresult);
			if ($_POST['submitComment'] == "submit") {
				$commentnew = $_POST['comment'];
				$commentnewprep = PrepSQL($commentnew);
				if ($commentnewprep != $commentcomment) {
					if ($comment_num_rows == 0) {
						$sql = "INSERT INTO comment (userid, indicatorid, step, comment, inserted) VALUES (
						$userid, $indicatorid, '$step', '$commentnewprep', NOW())";
						mysqli_query($db_link, $sql);
						$comment = $commentnewprep;
					} else if ($comment_num_rows > 0) {
						if (strcmp($commentnew, $commentold) != 0) {
							$sqlupdate = "UPDATE comment SET `comment` = '$commentnewprep', `updated` = NOW() WHERE `userid` = '$userid' AND `indicatorid` = '$indicatorid' AND `step` = '$step'";
							mysqli_query($db_link, $sqlupdate);
							$comment = $commentnewprep;
						}
					}
				}
				$commentresult = mysqli_query($db_link, 
						"SELECT * FROM comment WHERE userid = '$userid' AND indicatorid = '$indicatorid' AND step = '$step'");
				if (!$commentresult)
					error(mysqli_error());
				while ($commentinfo = mysqli_fetch_array($commentresult)) {
					$comment = $commentinfo['comment'];
				}
				mysqli_free_result($commentresult);
			}
			if ($_POST['form'] == "exclusionconstraints") {

				// update constraints
				$allconstraints = mysqli_query($db_link, 
						"SELECT * FROM formalised_constraint WHERE indicatorid = '$indicatorid' AND userid = '$userid'");
				if (!$allconstraints)
					error(mysqli_error());
				while ($constraintinfo = mysqli_fetch_array($allconstraints)) {
					$id = $constraintinfo['id'];
					$sqlupdate = "UPDATE formalised_constraint SET isexclusion = '0' WHERE id = '$id'";
					mysqli_query($db_link, $sqlupdate);
				}
				mysqli_free_result($allconstraints);
				$selconstraints = $_POST['constraints'];
				if (!empty($selconstraints)) {
					$N = count($selconstraints);
					for ($i = 0; $i < $N; $i++) {
						$sqlupdate = "UPDATE formalised_constraint SET `isexclusion` = '1', `updated` = NOW() WHERE id = '$selconstraints[$i]'";
						mysqli_query($db_link, $sqlupdate);
					}
				}
			}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>CLIF: Exclusion Criteria / Negation</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"></link>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
</head>
<body>
	<?php include_once("inc/head.inc") ?>
	<div style="height: 50px"></div>
	 <div class="container">
		<h1>Identify exclusion criteria / negation</h1>
		<p>For the constraints that you formalised, state which ones are
			exclusion criteria. Also select the constraints of the information
			model that only aim at those constraints. All constraints that you do
			not select are inclusion criteria. A constraint should not be both an
			exclusion criterion and only aim at the numerator.</p>
			<?php include_once("inc/indicator.inc") ?>
		<h2>Select constraints that are exclusion criteria / to be negated</h2>
		<div class="workwell">
			<div style="margin-left: 10px;">
				<a name="variables"></a>
				<form action="<?php echo $_SERVER['PHP_SELF'] . '#variables' ?>"
					method="post">

					<h3>
						<a href="informationmodel.php">Information model</a>
					</h3>
					<?php
			$informationmodelconstraint = mysqli_query($db_link, 
					"SELECT * FROM formalised_constraint WHERE indicatorid = '$indicatorid' AND userid = '$userid' AND constrainttype = 'informationmodel'");
			if (!$informationmodelconstraint)
				error(mysqli_error());
			while ($informationmodelconstraintrow = mysqli_fetch_array($informationmodelconstraint)) {
				$id = $informationmodelconstraintrow['id'];
				$table = $informationmodelconstraintrow['table'];
				$attribute = $informationmodelconstraintrow['attribute'];
				$relation = $informationmodelconstraintrow['relation'];
				$conceptid = $informationmodelconstraintrow['conceptid'];
				$isexclusion = $informationmodelconstraintrow['isexclusion'];
				$color = "black";
				if ((bool) $isexclusion)
					$color = "red";
// 				$fsn = mysqli_query($db_link, "SELECT FULLYSPECIFIEDNAME FROM `$snomeddbname`.concepts_core  WHERE CONCEPTID = '$conceptid' ");
// 				if (!$fsn)
// 					error(mysqli_error());
// 				$snorow = mysqli_fetch_row($fsn);
				echo "<input type='checkbox' name='constraints[]' value='$id' onchange='this.form.submit();'";
				if ((bool) $isexclusion) {
					echo " checked = 'checked' ";
				}
				echo " />&nbsp;<span style='color:$color' >";
				echo "$table.$attribute&nbsp;$relation&nbsp;$conceptid&nbsp;$snorow[0]";
				echo "</span><br />";
			}
			mysqli_free_result($informationmodelconstraint);
					?>
					<h3>
						<a href="temporal.php">Temporal constraints</a>
					</h3>
					<?php
			$dateconstraint = mysqli_query($db_link, 
					"SELECT * FROM formalised_constraint WHERE indicatorid = '$indicatorid' AND userid = '$userid' AND constrainttype = 'temporal_date'");
			if (!$dateconstraint)
				error(mysqli_error());
			while ($dateconstraintrow = mysqli_fetch_array($dateconstraint)) {
				$id = $dateconstraintrow['id'];
				$table = $dateconstraintrow['table'];
				$attribute = $dateconstraintrow['attribute'];
				$relation = $dateconstraintrow['relation'];
				$date = $dateconstraintrow['date'];
				$isexclusion = $dateconstraintrow['isexclusion'];
				$color = "black";
				if ((bool) $isexclusion)
					$color = "red";
				echo "<input type='checkbox' name='constraints[]' value='$id' onchange='this.form.submit();'";
				if ((bool) $isexclusion) {
					echo " checked = 'checked'  ";
				}
				echo " />&nbsp;<span style='color:$color' >";
				echo "$table.$attribute&nbsp;$relation&nbsp;$date";
				echo "</span><br />";
			}
			mysqli_free_result($dateconstraint);
			$daterelationconstraint = mysqli_query($db_link, 
					"SELECT * FROM formalised_constraint WHERE indicatorid = '$indicatorid' AND userid = '$userid' AND constrainttype = 'temporal_relation'");
			if (!$daterelationconstraint)
				error(mysqli_error());
			while ($daterelationconstraintrow = mysqli_fetch_array($daterelationconstraint)) {
				$id = $daterelationconstraintrow['id'];
				$table = $daterelationconstraintrow['table'];
				$attribute = $daterelationconstraintrow['attribute'];
				$relation = $daterelationconstraintrow['relation'];
				$table2 = $daterelationconstraintrow['table2'];
				$attribute2 = $daterelationconstraintrow['attribute2'];
				$isexclusion = $daterelationconstraintrow['isexclusion'];
				$color = "black";
				if ((bool) $isexclusion)
					$color = "red";
				echo "<input type='checkbox' name='constraints[]' value='$id' onchange='this.form.submit();' ";
				if ((bool) $isexclusion) {
					echo " checked = 'checked'  ";
				}
				echo " />&nbsp;<span style='color:$color' >";
				echo "$table.$attribute&nbsp;$relation&nbsp;$table2.$attribute2";
				echo "</span><br />";
			}
			mysqli_free_result($daterelationconstraint);
					?>
					<h3>
						<a href="numeric.php">Numeric constraints</a>
					</h3>
					<?php
			$numericconstraint = mysqli_query($db_link, 
					"SELECT * FROM formalised_constraint WHERE indicatorid = '$indicatorid' AND userid = '$userid' AND constrainttype = 'numeric'");
			if (!$numericconstraint)
				error(mysqli_error());
			while ($numericconstraintrow = mysqli_fetch_array($numericconstraint)) {
				$id = $numericconstraintrow['id'];
				$table = $numericconstraintrow['table'];
				$attribute = $numericconstraintrow['attribute'];
				$relation = $numericconstraintrow['relation'];
				$number = $numericconstraintrow['number'];
				$isexclusion = $numericconstraintrow['isexclusion'];
				$color = "black";
				if ((bool) $isexclusion)
					$color = "red";
				echo "<input type='checkbox' name='constraints[]' value='$id' onchange='this.form.submit();'";
				if ((bool) $isexclusion) {
					echo " checked = 'checked' ";
				}
				echo " />&nbsp;<span style='color:$color' >";
				echo "$table.$attribute&nbsp;$relation&nbsp;$number";
				echo "</span><br />";
			}
			mysqli_free_result($numericconstraint);
					?>
						<h3>
						<a href="numeric.php">Textual constraints</a>
					</h3>
					<?php
			$textualconstraint = mysqli_query($db_link, 
					"SELECT * FROM formalised_constraint WHERE indicatorid = '$indicatorid' AND userid = '$userid' AND constrainttype = 'textual'");
			if (!$textualconstraint)
				error(mysqli_error());
			while ($textualconstraintrow = mysqli_fetch_array($textualconstraint)) {
				$id = $textualconstraintrow['id'];
				$table = $textualconstraintrow['table'];
				$attribute = $textualconstraintrow['attribute'];
				$relation = $textualconstraintrow['relation'];
				$txt = $textualconstraintrow['txt'];
				$isexclusion = $textualconstraintrow['isexclusion'];
				$color = "black";
				if ((bool) $isexclusion)
					$color = "red";
				echo "<input type='checkbox' name='constraints[]' value='$id' onchange='this.form.submit();'";
				if ((bool) $isexclusion) {
					echo " checked = 'checked' ";
				}
				echo " />&nbsp;<span style='color:$color' >";
				echo "$table.$attribute&nbsp;$relation&nbsp;$txt";
				echo "</span><br />";
			}
			mysqli_free_result($textualconstraint);
					?>
					<h3>
						<a href="boolean.php">Boolean constraints</a>
					</h3>
					<?php
			$booleanconstraint = mysqli_query($db_link, 
					"SELECT * FROM formalised_constraint WHERE indicatorid = '$indicatorid' AND userid = '$userid' AND constrainttype = 'boolean'");
			if (!$booleanconstraint)
				error(mysqli_error());
			while ($booleanconstraintrow = mysqli_fetch_array($booleanconstraint)) {
				$id = $booleanconstraintrow['id'];
				$table = $booleanconstraintrow['table'];
				$attribute = $booleanconstraintrow['attribute'];
				$boolean = $booleanconstraintrow['boolean'];
				$isexclusion = $booleanconstraintrow['isexclusion'];
				$color = "black";
				if ((bool) $isexclusion)
					$color = "red";
				echo "<input type='checkbox' name='constraints[]' value='$id' onchange='this.form.submit();'";
				if ((bool) $isexclusion) {
					echo " checked = 'checked' ";
				}
				echo " />&nbsp;<span style='color:$color' >";
				echo "$table.$attribute&nbsp;=&nbsp;$boolean";
				echo "</span><br />";
			}
			mysqli_free_result($booleanconstraint);
					?>
					<input type="hidden" name="form" value="exclusionconstraints" />
				</form>
			</div>
		</div>
		<?php include_once("inc/comment.inc") ?>
	</div>
</body>
</html>
		<?php
		}
	}
	mysqli_free_result($user);
	mysqli_close($db_link);
} else {
	header("Location: login.php");
	return;
}
		?>
