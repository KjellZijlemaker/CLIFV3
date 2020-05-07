<?php
mb_internal_encoding( 'UTF-8' );
error_reporting(0);
$commentcomment = "Please document all questions that you had, ambiguities and how you resolved them, i.e. why you modelled the indicator the way you did. Also, please give us as much feedback as possible of what you think of the method, what you found easy and what difficult and the reasons for it. Your feedback is valuable for improving both this application and the indicators.";

$db = "localhost";
$db_user = "root";
$db_pass = "";
$dbname = "AI&Health";
$patientsdbname = "AI&Health_patients";
$snomeddbname = "snomedct_full";

$mysqli = new mysqli($db, $db_user, $db_pass, $dbname);
if($mysqli->connect_error) {
  exit('Error connecting to database'); //Should be a message a typical user could understand in production
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli->set_charset("utf8mb4");

$patienttables = array("allergies", "careplans", "conditions", "encounters", "imaging_studies", "immunizations", "medications", "observations", "orginizations", "patient", "payers",
"payer_transitions", "procedures", "providers2");
$codecolumns = array("CODE");


function endsWith($haystack, $needle)
{
	$length = strlen($needle);
	$start  = $length * -1; //negative
	return (substr($haystack, $start) === $needle);
}

function error($error, $query, $step, $userid, $indicatorid)
{
	$message =  "User ID: " . $userid . "\r\n
	Indicator ID: " . $indicatorid . "\r\n
	Error: " . $error . "\r\n
	Query: " . $query . "\r\n
	Step: " . $step . "\r\n
	";

	$to      = 'k.dentler@vu.nl';
	$subject = 'InFo Error';

	$headers = 'From: k.dentler@vu.nl' . "\r\n" .
    'Reply-To: k.dentler@vu.nl' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

	if (mail($to, $subject, $message, $headers)) {
		// echo "message mailed!!! <br /><br />";
	}
}

//function does not work anymore, rework in the future
//function PrepSQL($value, $db_link)
//{
	//not available anymore in php7
//	if(get_magic_quotes_gpc())
//	{
// 		$value = stripslashes($value);
//	}

	//$value = mysqli_real_escape_string($db_link, $result);
//	return($value);
//}

function PrepSQL($value) {
    $unwanted = array("'"); // add any unwanted char to this array
    return str_ireplace($unwanted, '', $value);
}

function getRelation($relation) {

	if($relation=="less-than" || $relation == 'value="less-than"><') {
		return("<");
	}

	if($relation=="less-than-or-equal-to" || $relation == 'value="less-than-or-equal-to">?') {
		return("<=");
	}

	if($relation=="equal-to" || $relation=='value="equal-to">=') {
		return("=");
	}

	if($relation=="not-equal-to" || $relation == 'value="not-equal-to">!=') {
		return("!=");
	}

	if($relation=="greater-than-or-equal-to" || $relation == 'value="greater-than-or-equal-to">?') {
		return(">=");
	}

	if($relation=="greater-than" || $relation == 'value="greater-than">>') {
		return(">");
	}

	return("error");
}

function jsonToCSV($arr, $cfilename)
{
	$bytes = random_bytes(16);
	$cfilename = "temp/" . $cfilename . $random_bytes;
    $data = json_decode($arr, true);
    $fp = fopen($cfilename, 'w');
    $header = false;
    foreach ($data as $row)
    {
        if (empty($header))
        {
            $header = array_keys($row);
            fputcsv($fp, $header);
            $header = array_flip($header);
        }
        fputcsv($fp, array_merge($header, $row));
    }
    fclose($fp);
    return $cfilename;
}
?>
