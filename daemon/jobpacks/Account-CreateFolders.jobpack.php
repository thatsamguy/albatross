<?php
/* 
* Albatross Manager
* 
* Account-CreateFolders Jobpack
* 
* Description:
*  Creates the initial subfolders for the new account and assigns the correct permissions
*  
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add('jobpack-Account-CreateFolders', 'Gathered job data for '.$jobid.'. Data: '.json_encode($jobdata));

// Check correct jobdata has been provided: acc_id
if (!is_array($jobdata)) {
    $errmsg = 'jobdata not an array';
    $error->add('jobpack-Account-CreateFolders', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-Account-CreateFolders', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

if (!$jobdata[0] or !array_key_exists('acc_id', $jobdata[1])) {
    $errmsg = 'incorrect or invalid jobdata provided';
    $error->add('jobpack-Account-CreateFolders', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-Account-CreateFolders', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

$jobdata = $jobdata[1];

// Grab default folder configuration
$conf->refresh();
$accountdata = $conf->account;
$subfolders = $accountdata['folder'];
unset($accountdata);

$replacearray['_accid_'] = $jobdata['acc_id'];

foreach ($subfolders as $key => $array) {
    foreach ($array as $key2 => $value) {
        $subfolders[$key][$key2] = str_replace(array_keys($replacearray), $replacearray, $value);
    }
    unset($key2, $value);
}
unset($key, $array);

foreach ($subfolders as $key => $array) {
    // Check if directory already exists, if so modify permissions
  if (is_dir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0])) {
      $errmsg = 'directory already exists';
      $error->add('jobpack-Account-CreateFolders', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0]."'");
  } else {
      // Create Directory
    if (!mkdir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0])) {
        $errmsg = 'unable to create directory';
        $error->add('jobpack-Account-CreateFolders', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0]."'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'tryagain');
        if (!$update[0]) {
            $error->add('jobpack-Account-CreateFolders', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }
  }

  // Set Permissions
  if (!chown($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0], $array[1])) {
      $errmsg = 'unable to set ownership';
      $error->add('jobpack-Account-CreateFolders', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0]."'");
      unset($errmsg);
      $update = $amm->update_jobstatus($jobid, 'failed');
      if (!$update[0]) {
          $error->add('jobpack-Account-CreateFolders', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
      }
      unset($update);
      goto end;
  }

    if (!chgrp($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0], $array[2])) {
        $errmsg = 'unable to set group permissions';
        $error->add('jobpack-Account-CreateFolders', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0]."'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Account-CreateFolders', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

    if (!chmod($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0], $array[3])) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Account-CreateFolders', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$array[0]."'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Account-CreateFolders', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }
}

// Mark job as closed
$update = $amm->update_jobstatus($jobid, 'closed');
if (!$update[0]) {
    $error->add('jobpack-Account-CreateFolders', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
} unset($update);
goto end;

// TODO - rollback on failure
?>
<?php
end:
?>
