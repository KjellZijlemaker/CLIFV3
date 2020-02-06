<?php
require_once 'inc/util.inc';
require_once 'inc/queryutil.inc';
$DEBUG_MODE = false;
session_start();


// $json_results_num = json_encode($_SESSION["numeratorresults"] );
// $json_results_den = json_encode($_SESSION["denominatorresults"] );

//we now have the unique patient id's
$difference = array_values(array_diff(array_column($_SESSION["denominatorresults"], 'patientid'), array_column($_SESSION["numeratorresults"], 'patientid' )));

$newarr = [];
foreach($_SESSION["denominatorresults"] as $value){
    foreach($difference as $diff){
        if($value['patientid'] == $diff){
            array_push($newarr, $value);
        }
    }
}

header('Content-Type: application/text');
header('Content-Disposition: attachment; filename="non-passed.json"');

$newarr = json_encode($newarr);

print_r($newarr);
?>