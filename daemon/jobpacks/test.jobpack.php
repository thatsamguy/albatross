<?php
/* 
* Albatross Manager
* 
* Test Jobpack
* 
* Description:
*  Test job for debugging and testing
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add("jobpack-test","Loaded test jobpack successfully");
$error->add("jobpack-test","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-test","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
?>
