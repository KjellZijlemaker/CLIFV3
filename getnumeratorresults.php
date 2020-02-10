<?php
require_once 'inc/util.inc';
require_once 'inc/queryutil.inc';
$DEBUG_MODE = false;
session_start();

$json_results = json_encode($_SESSION["numeratorresults"] );
$tempfilename = jsonToCSV($json_results, "numerator.csv");

header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename=numerator.csv');
header('Pragma: no-cache');
readfile($tempfilename);

unlink($tempfilename);
?>