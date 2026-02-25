<?php
// action/download_sample.php
$filename = "waypoint_template_sample.csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
// Header Row
fputcsv($output, array('Template Name', 'Waypoint Template ID')); 
// Example Rows
fputcsv($output, array('Welcome Email', 'wptemplate_XABW7FVKHTS2EIKH'));
fputcsv($output, array('Test Campaign', 'wptemplate_44RULEDA5BMQLOV1'));

fclose($output);
exit();
?>