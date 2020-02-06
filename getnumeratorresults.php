<?php
require_once 'inc/util.inc';
require_once 'inc/queryutil.inc';
$DEBUG_MODE = false;
session_start();

header('Content-Type: application/text');
header('Content-Disposition: attachment; filename="numerator.json"');
$json_results = json_encode($_SESSION["numeratorresults"] );
echo $json_results;

?>