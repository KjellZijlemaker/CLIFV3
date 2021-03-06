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
			$step = "numeric";
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
						$userid,
						$indicatorid,
						'$step', '$commentnewprep', NOW())";
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
			if (($_POST['submitConstraint'] == "submit") || isset($_POST['tableattributeSelect'])
					|| isset($_POST['relationSelect'])) {
				$selectedtableattribute = $_POST['tableattributeSelect'];
				$relation = $_POST['relationSelect'];
				$number = $_POST['number'];
				$indicatorText = $_POST['indicatorText'];
				$indicatortextprep = PrepSQL($indicatorText);
				// all empty
				if (empty($selectedtableattribute) && empty($relation) && empty($number) && empty($indicatorText)) {
					$_SESSION['error'] = "Nothing saved: all fields empty";
				} else if (empty($selectedtableattribute) || empty($relation) || empty($number)
						|| empty($indicatorText)) {
					$_SESSION['error'] = "Nothing saved: one or more empty fields";
				} else if (!is_numeric($number)) {
					$_SESSION['error'] = "Nothing saved: number must be an integer ($number). ";
				} else {
					$pos = strpos($selectedtableattribute, ".");
					$table = substr($selectedtableattribute, 0, $pos);
					$attribute = substr($selectedtableattribute, ($pos + 1), strlen($selectedtableattribute));
					$numquery = "SELECT * FROM `formalised_constraint` WHERE `userid` = '$userid' AND `indicatorid` = '$indicatorid' AND `constrainttype` = 'numeric' AND `indicatortext` = '$indicatortextprep' AND `table` = '$table' AND `attribute` = '$attribute' AND `relation` = '$relation' AND `number` = '$number'";
					$result = mysqli_query($db_link, $numquery);
					if (!$result)
						error(mysqli_error());
					$num_rows = mysqli_num_rows($result);
					mysqli_free_result($result);
					if ($num_rows > 0) {
						$_SESSION['error'] = "Nothing saved: same row already in database. ";
					} else {
						$sql = "INSERT INTO formalised_constraint (`id`, `userid`, `indicatorid`, `constrainttype`, `indicatortext`, `table`, `attribute`, `conceptid`, `relation`, `date`, `table2`, `attribute2`, `number`, `isexclusion`, `numeratoronly`, `inserted`) VALUES (
						NULL,
						$userid,
						$indicatorid,
						'numeric',
						'$indicatortextprep',
						'$table', '$attribute', NULL, '$relation', NULL, NULL, NULL,
						$number, NULL, NULL, NOW())";
						mysqli_query($db_link, $sql);
						$selectedtableattribute = "";
						$relation = "";
						$number = "";
						$indicatorText = "";
						$indicatortextprep = "";
					}
				}
			}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>CLIF: Numeric</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"></link>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
</head>
<body>
	<?php include_once("inc/head.inc") ?>
	<div style="height: 50px"></div>
	 <div class="container">
		<h1>Formalise numeric constraints</h1>
		<p>
			Please scan through the text and search for all numeric constraints
			that occur in the <i>numerator</i> and in the <i>in- and exclusion
				criteria</i> of the indicator. A numeric constraint compares a
			concept to a number.
		</p>
		<?php include_once("inc/indicator.inc") ?>
		<a name="numeric"></a>
		<div class="workwell">
			<form action="<?php echo $_SERVER['PHP_SELF'] . '#numeric' ?>"
				method="post">
				<table class="table table-condensed">
					<tr>
						<th style="width: 25%">Copy relevant piece of indicator text</th>
						<th style="width: 25%">Table.Attribute</th>
						<th style="width: 25%">Relation</th>
						<th style="width: 15%">Number</th>
						<th style="width: 10%">Delete</th>
					</tr>
					<?php
			$content = mysqli_query($db_link, 
					"SELECT * FROM formalised_constraint WHERE userid = '$userid' AND indicatorid = '$indicatorid' AND constrainttype = 'numeric'");
			if (!$content)
				error(mysqli_error());
			while ($row = mysqli_fetch_array($content)) {
				$id = $row['id'];
				$table = $row['table'];
				$attribute = $row['attribute'];
				echo "<tr>";
				echo "<td>" . $row['indicatortext'] . "</td>";
				echo "<td>" . $table . "." . $attribute . "</td>";
				echo "<td>" . $row['relation'] . "</td>";
				echo "<td>" . $row['number'] . "</td>";
				echo "<td><a href='delete.php?id=$id&step=$step&anchor=numeric'>x</a></td>";
				echo "</tr>\n";
			}
			mysqli_free_result($content);
					?>
					<tr valign="top">
						<td><input type="text" name="indicatorText" maxlength="100"
							value="<?php echo $indicatorText; ?>" />
						</td>
						<td><select name="tableattributeSelect" class="input-large shadow_slect">
								<option value="">please choose</option>
								<?php
			$variablesql = "SELECT * FROM `query_variable` WHERE `userid` = '$userid'  AND `indicatorid` = '$indicatorid' ORDER BY `variable`";
			$variables = mysqli_query($db_link, $variablesql);
			if (!$variables)
				error(mysqli_error());
			mysqli_select_db($db_link, $patientsdbname);
			while ($variablerow = mysqli_fetch_array($variables)) {
				$variableattributes = mysqli_query($db_link, "DESCRIBE `$variablerow[table]`");
				if (!$variableattributes)
					error(mysqli_error());
				while ($variableattributerow = mysqli_fetch_array($variableattributes)) {
					$datatype = mysqli_query($db_link, 
							"SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$variablerow[table]' AND COLUMN_NAME = '$variableattributerow[0]'");
					if (!$datatype)
						error(mysqli_error());
					$datatyperow = mysqli_fetch_row($datatype);
					if ((strcmp($datatyperow[0], "int") == 0)
							&& !preg_match('/\Q' . "id" . '\E$/', $variableattributerow[0])) {
						$option = "$variablerow[variable].$variableattributerow[0]";
						echo "<option value='$option'";
						if (strcmp($selectedtableattribute, $option) == 0) {
							echo "selected='selected'";
						}
						;
						echo ">$option</option>";
					}
				}
				mysqli_free_result($variableattributes);
			}
			mysqli_free_result($variables);
			foreach ($patienttables as &$patienttable) {
				$attributes = mysqli_query($db_link, "DESCRIBE `$patienttable`");
				if (!$attributes)
					error(mysqli_error());
				while ($attributerow = mysqli_fetch_array($attributes)) {
					$datatype = mysqli_query($db_link, 
							"SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$patienttable' AND COLUMN_NAME = '$attributerow[0]'");
					if (!$datatype)
						error(mysqli_error());
					$datatyperow = mysqli_fetch_row($datatype);
					if ((strcmp($datatyperow[0], "int") == 0) && !preg_match('/\Q' . "id" . '\E$/', $attributerow[0])) {
						$option = "$patienttable.$attributerow[0]";
						echo "<option value='$option'";
						if (strcmp($selectedtableattribute, $option) == 0) {
							echo "selected='selected'";
						}
						;
						echo ">$option</option>";
					}
				}
				mysqli_free_result($attributes);
			}
			mysqli_select_db($db_link, $dbname);
								?>
						</select></td>
						<td><select name="relationSelect" class="input-large shadow_slect">
								<option value="">please choose</option>
								<option
								<? if ($relation == "less-than")
				echo (" selected=\"selected\"");
								?>
									value="less-than">&lt;</option>
								<option
								<? if ($relation == "less-than-or-equal-to")
				echo (" selected=\"selected\"");
								?>
									value="less-than-or-equal-to">&le;</option>
								<option
								<? if ($relation == "equal-to")
				echo (" selected=\"selected\"");
								?>
									value="equal-to">=</option>
								<option
								<? if ($relation == "not-equal-to")
				echo (" selected=\"selected\"");
								?>
									value="not-equal-to">!=</option>
								<option
								<? if ($relation == "greater-than-or-equal-to")
				echo (" selected=\"selected\"");
								?>
									value="greater-than-or-equal-to">&ge;</option>
								<option
								<? if ($relation == "greater-than")
				echo (" selected=\"selected\"");
								?>
									value="greater-than">&gt;</option>
						</select></td>
						<td><input class="input-medium" type="text" name="number" maxlength="50"
							value="<?php if (!empty($number))
				echo $number;
								   ?>" />
						</td>
						<td></td>
					</tr>
				</table>
				<button type="submit" class="btn small primary"
					name="submitConstraint" value="submit">save changes</button>
				<span style="color: red"> <?php echo $_SESSION['error']; ?> </span>
			</form>
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
