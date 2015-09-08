<?php error_log(__FILE__);?>
<?php
/* 
* Albatross Manager
* 
* Email-VsftpdBandwidth Jobpack
* 
* Description:
*  Calculates the ftp bandwidth for each account from the rotated vsftpd log files
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
// Include Dependencies
include_once 'email.class.php';
$email = new email();
?>
<?php
$error->add('jobpack-Email-VsftpdBandwidth', 'Gathered job data for '.$jobid.'.');

$result = $email->vsftpd_bandwidth('/var/log/vsftpd.log.1');
if (!$result[0]) {
    $errmsg = 'unable to update ftp bandwidth';
    $error->add('jobpack-Email-VsftpdBandwidth', $errmsg.' '.$result[1]);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'tryagain');
    if (!$update[0]) {
        $error->add('jobpack-Email-VsftpdBandwidth', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    unset($result);
    goto end;
}

unset($result);

// Mark job as closed
$update = $amm->update_jobstatus($jobid, 'closed');
if (!$update[0]) {
    $error->add('jobpack-Email-VsftpdBandwidth', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
} unset($update);

goto end;

// TODO - rollback on failure
end:
?>
