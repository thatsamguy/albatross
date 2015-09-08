<?php error_log(__FILE__);?>
<?php
/* 
* Albatross Manager
* 
* Site-Remove Jobpack
* 
* Description:
*  Creates the Directory for the new site and assigns the correct initial permissions
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
$error->add('jobpack-Site-Remove', 'Gathered job data for '.$jobid.'. Data: '.json_encode($jobdata));

// Check correct jobdata has been provided: acc_id sitename
if (!is_array($jobdata)) {
    $errmsg = 'jobdata not an array';
    $error->add('jobpack-Site-Remove', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

if (!$jobdata[0] or !array_key_exists('sitename', $jobdata[1]) or !array_key_exists('acc_id', $jobdata[1]) or $jobdata[1]['sitename'] == '') {
    $errmsg = 'incorrect or invalid jobdata provided';
    $error->add('jobpack-Site-Remove', $errmsg);
    unset($errmsg);
    $update = $amm->update_jobstatus($jobid, 'failed');
    if (!$update[0]) {
        $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
    }
    unset($update);
    goto end;
}

$jobdata = $jobdata[1];

// Check backup directory exists
if (!is_dir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive')) {
    if (!mkdir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive')) {
        $errmsg = 'unable to create directory';
        $error->add('jobpack-Site-Remove', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id']."/archive'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'tryagain');
        if (!$update[0]) {
            $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

  // Set Permissions
  if (!chown($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive', 'nginx')) {
      $errmsg = 'unable to set ownership permissions';
      $error->add('jobpack-Site-Remove', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id']."/archive'");
      unset($errmsg);
      $update = $amm->update_jobstatus($jobid, 'failed');
      if (!$update[0]) {
          $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
      }
      unset($update);
      goto end;
  }

    if (!chgrp($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive', 'z'.$jobdata['acc_id'])) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Site-Remove', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id']."/archive'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

    if (!chmod($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive', 0771)) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Site-Remove', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id']."/archive'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }
}

// Backup site direCheck if directory still exists, if so rctory
if (is_dir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$jobdata['sitename'])) {
    exec('zip -r '.$conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_finalbackup.zip '.$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$jobdata['sitename']);

  // Set Permissions
  if (!chown($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_finalbackup.zip', 'z'.$jobdata['acc_id'])) {
      $errmsg = 'unable to set ownership permissions';
      $error->add('jobpack-Site-Remove', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename']."_finalbackup.zip'");
      unset($errmsg);
      $update = $amm->update_jobstatus($jobid, 'failed');
      if (!$update[0]) {
          $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
      }
      unset($update);
      goto end;
  }

    if (!chgrp($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_finalbackup.zip', 'z'.$jobdata['acc_id'])) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Site-Remove', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename']."_finalbackup.zip'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }

    if (!chmod($conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename'].'_finalbackup.zip', 0640)) {
        $errmsg = 'unable to set ownership permissions';
        $error->add('jobpack-Site-Remove', $errmsg." '".$conf->base_home_dir.'/'.$jobdata['acc_id'].'/archive/'.$jobdata['sitename']."_finalbackup.zip'");
        unset($errmsg);
        $update = $amm->update_jobstatus($jobid, 'failed');
        if (!$update[0]) {
            $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
        }
        unset($update);
        goto end;
    }
}

// Remove directory
if (is_dir($conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$jobdata['sitename'])) {
    exec('rm -fR '.$conf->base_home_dir.'/'.$jobdata['acc_id'].'/'.$jobdata['sitename']);
}

// Remove Awstats conf
unlink('/etc/awstats/awstats.'.$jobdata['acc_id'].'.'.$jobdata['sitename'].'.conf');

// Remove Nginx conf
unlink('/etc/nginx/conf.d/albatross/'.$jobdata['acc_id'].'.'.$jobdata['sitename'].'.conf');

// Mark job as closed
$update = $amm->update_jobstatus($jobid, 'closed');
if (!$update[0]) {
    $error->add('jobpack-Site-Remove', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
} unset($update);

// Add job to reload nginx
// TODO - check current workflow and add to new job
$result = $amm->add_job('Nginx-Reload', $jobdata['acc_id'], array('acc_id' => $jobdata['acc_id'], 'sitename' => $jobdata['sitename']), '');
if (!$result[0]) {
    $errmsg = 'Unable to add Nginx-Reload job to queue';
    $error->add('jobpack-Site-Remove', $errmsg);
    $error->add('jobpack-Site-Remove', $result[1]);
}
unset($result);

goto end;

// TODO - rollback on failure
end:
?>
