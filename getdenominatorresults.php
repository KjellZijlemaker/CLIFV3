<?php
require_once 'inc/util.inc';
require_once 'inc/queryutil.inc';
$DEBUG_MODE = false;
session_start();

//we now have the unique patient id's
$difference = array_values(array_diff(array_column($_SESSION["denominatorresults"], 'patientid'), array_column($_SESSION["numeratorresults"], 'patientid' )));

//only save these patients in a new array
$newarr = [];
foreach($_SESSION["denominatorresults"] as $value){
    foreach($difference as $diff){
        if($value['patientid'] == $diff){
            array_push($newarr, $value);
        }
    }
}


$newarr = json_encode($newarr);
$tempfilename = jsonToCSV($newarr, "non-passed.csv");

header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename=non-passed.csv');
header('Pragma: no-cache');
readfile($tempfilename);
unlink($tempfilename);
?>