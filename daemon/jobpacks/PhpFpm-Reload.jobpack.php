<?php
/* 
* Albatross Manager
* 
* PhpFpm-Reload Jobpack
* 
* Description:
*  Tells php-fpm to reload its configuration files
*
* Copyright 2012 Samuel Bailey
*/
?>
<?php
$error->add('jobpack-PhpFpm-Reload', 'Gathered job data for '.$jobid.'. Data: '.json_encode($jobdata));

include_once 'sites.class.php';
$site = new site();

// Check correct jobdata has been provided: acc_id
if (!is_array($jobdata)) {
    $errmsg = 'jobdata not an array';
    $error->add('jobpack-PhpFpm-Reload', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-PhpFpm-Reload', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

if (!$jobdata[0] or !array_key_exists('acc_id', $jobdata[1])) {
    $errmsg = 'incorrect or invalid jobdata provided';
    $error->add('jobpack-PhpFpm-Reload', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-PhpFpm-Reload', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

$jobdata = $jobdata[1];

// Reload config
exec('/sbin/service php-fpm reload', $result, $retval);
if ($retval != 0) {
    $errmsg = 'unable to reload php-fpm configuration';
    $error->add('jobpack-PhpFpm-Reload', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'tryagain');
    if (!$update[0]) {
        $error->add('jobpack-PhpFpm-Reload', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
} else {
    // TODO - fix checking of successfull reload
}

success:
// Mark job as closed
$update = $amm->update_jobstatus($jobid, 'closed');
if (!$update[0]) {
    $error->add('jobpack-PhpFpm-Reload', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
} unset($update);
goto end;
?>
<?php
end:
?>
