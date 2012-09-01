<?php
/* 
* Albatross Manager
* 
* Site-CalcHttpBandwidth Jobpack
* 
* Description:
*  Calculates the http bandwidth of a site for the current month
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
// Include Dependencies
include_once("sites.class.php");
$site = new site();
?>
<?php
$error->add("jobpack-Site-CalcHttpBandwidth","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

// Check correct jobdata has been provided: acc_id sitename
if(!is_array($jobdata)){
  $errmsg = "jobdata not an array";
  $error->add("jobpack-Site-CalcHttpBandwidth",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-CalcHttpBandwidth","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

if(!$jobdata[0] OR !array_key_exists("sitename",$jobdata[1]) OR !array_key_exists("acc_id",$jobdata[1]) OR $jobdata[1]['sitename']==""){
  $errmsg = "incorrect or invalid jobdata provided";
  $error->add("jobpack-Site-CalcHttpBandwidth",$errmsg);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"failed");
  if(!$update[0]){ $error->add("jobpack-Site-CalcHttpBandwidth","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  goto end;
}

$jobdata = $jobdata[1];

$result = $site->awstats_httpbandwidth($jobdata['acc_id'],$jobdata['sitename']);
if(!$result[0]){
  $errmsg = "unable to update site http bandwidth";
  $error->add("jobpack-Site-CalcHttpBandwidth",$errmsg." '".$jobdata['acc_id']."->".$jobdata['sitename']."' ".$result[1]);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Site-CalcHttpBandwidth","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  unset($result);
  goto end;
}

unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Site-CalcHttpBandwidth","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);

goto end;

end:
?>