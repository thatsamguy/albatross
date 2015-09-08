<?php
/* 
* Albatross Manager
* 
* Sched-SiteSize Jobpack
* 
* Description:
*  Checks which sites have not had their size updated in 6+ hrs and adds check to job queue
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
// Include Dependencies
include_once 'sites.class.php';
$site = new site();
?>
<?php
$error->add('jobpack-Sched-SiteSize', 'Gathered job data for '.$jobid.'. Data: '.json_encode($jobdata));

$result = $site->sched_site_size();
if (!$result[0]) {
    $errmsg = 'unable to check site sizes';
    $error->add('jobpack-Sched-SiteSize', $errmsg.' '.$result[1]);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'tryagain');
    if (!$update[0]) {
        $error->add('jobpack-Sched-SiteSize', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    unset($result);
    goto end;
}

unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid, 'closed');
if (!$update[0]) {
    $error->add('jobpack-Sched-SiteSize', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
} unset($update);

goto end;

end:
?>
