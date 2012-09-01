<?php
/* 
* Albatross Manager
* 
* Sched-SiteHttpBandwidth Jobpack
* 
* Description:
*  Runs http bandwidth updates on all sites with awstats files
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
$error->add("jobpack-Sched-SiteHttpBandwidth","Gathered job data for ".$jobid.". Data: ".json_encode($jobdata));

$result = $site->sched_site_httpbandwidth();
if(!$result[0]){
  $errmsg = "unable to check http bandwidth for sites";
  $error->add("jobpack-Sched-SiteHttpBandwidth",$errmsg." ".$result[1]);
  unset($errmsg);
  $update = $amm->update_jobstatus($jobid,"tryagain");
  if(!$update[0]){ $error->add("jobpack-Sched-SiteHttpBandwidth","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);
  unset($result);
  goto end;
}

unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid,"closed");
if(!$update[0]){ $error->add("jobpack-Sched-SiteHttpBandwidth","jobstatus update failed for ".$jobid." '".$update[1]."'"); } unset($update);

goto end;

end:
?>
