#!/usr/bin/php -q
<?php
/* 
* Albatross Manager Monitor
* 
* Description:
*  Monitors the Albatross Manager and does live updating of the configuration
*
* Copyright 2011 Samuel Bailey
*/
?>
<?php
// TODO - remove hardcoded paths
set_include_path(realpath('/var/wwwdata/albatross/100001/albatross/lib').':'.realpath('/var/wwwdata/albatross/100001/albatross/conf').':'.realpath('/var/wwwdata/albatross/100001/albatross/daemon/jobpacks'));
?>
<?php
require_once 'amm.class.php';
$error->log = 'amm';
$amm = new amm();

// TODO - assume only 1 amm is running.. ever.. and remark all "active" jobs as "new" on restart in case of crash

// Run forever...
ini_set('max_execution_time', '0');
ini_set('max_input_time', '0');
set_time_limit(0);

// Basic Configuration
declare (ticks = 1);
$wnull = null;
$enull = null;
$max = 5;
$child = 0;
$children = array();
$active_jobs = array();
$prog = array();
$maxseen = 0;
$totseen = 0;
$sigterm = false;
$sighup = false;
$started = time();
$waitcount = 0;
$waitcountmax = 30; // seconds between logging stats

// Signal Handler
function sig_handler($signo)
{
    global $sigterm;
    global $sighup;
    if ($signo == SIGTERM) {
        $sigterm = true;
    } elseif ($signo == SIGHUP) {
        $sighup = true;
    } else {
        echo("Funny signal!\n");
    }
}

pcntl_signal(SIGTERM, 'sig_handler');
pcntl_signal(SIGHUP, 'sig_handler');

// Fork and exit (daemonize)
$pid = pcntl_fork();
if ($pid == -1) {
    // Unable to fork.
  $errmsg = 'amm unable to fork processes. startup failed';
    $error->add('amm', $errmsg);
    die($errmsg);
    unset($errmsg);
} elseif ($pid) {
    // Started successfully. Kill initial process.
  $errmsg = 'amm started with pid: '.$pid;
    echo $errmsg."\n";
    $error->add('amm', $errmsg);
    unset($errmsg);
    exit();
}
$parentpid = posix_getpid();

// Run Daemon until receive sigterm
while (!$sigterm) {
    // This is the master process.
  while (!$sighup && !$sigterm) {
      // The master process monitors and waits for jobs. Jobs are forked off as separate processes from here.

    // make sure there are not too many children processes. wait until some complete their tasks and exit.
    while (pcntl_wait($status, WNOHANG or WUNTRACED) > 0) {
        usleep(5000);
    }
      while (list($key, $val) = each($children)) {
          if (!posix_kill($val, 0)) {
              unset($active_jobs[$children[$key]]);
              unset($children[$key]);
              $child = $child - 1;
          }
      }
      $children = array_values($children);
      if ($child >= $max) {
          usleep(5000);
          continue;
      }

    // Grab queues from database
    unset($queue, $active_dblist);
      $amm->check_schedules();
      $queue = $amm->get_new_jobs();
      $active_dblist = $amm->get_active_jobs();

    // Compare active_dblist with active_jobs and queue. If does not exist, update job as failed.
    if ($active_dblist[0]) {
        foreach ($active_dblist[1]['jobs'] as $jobid) {
            if (!in_array($jobid, $active_jobs)) {
                // Not in active list. Check if in queue.
      if (!$queue[0]) {
          // Queue Empty. Not in queue either. Update job as failed.
        $amm->update_jobstatus($jobid, 'tryagain');
      } else {
          $inarray = 0;
          foreach ($queue[1] as $jobtype => $jobid_array) {
              if ($jobtype != 'count') {
                  if (!is_array($jobid_array)) {
                      $errmsg = 'jobid_array not an array. jobtype:';
                      $error->add('amm', $errmsg.' '.$jobtype.', '.json_encode($queue[1]));
                      unset($errmsg);
                  } else {
                      if (in_array($jobid, $jobid_array) and $jobtype != 'count') {
                          $inarray = 1;
                      }
                  }
              }
          }
          unset($jobid_array, $jobtype);
          if ($inarray == 0) {
              // Not in queue either. Update job as failed.
          $amm->update_jobstatus($jobid, 'tryagain');
          }
          unset($inarray);
      }
            }
        }
    }
      unset($jobid);

    // Work on job queue
    if ($queue[0]) {
        // For each job, fork a child.
      foreach ($queue[1] as $jobtype => $jobid_array) {
          if ($jobtype != 'count') {
              foreach ($jobid_array as $jobid) {
                  if (count($children) >= $max) {
                      usleep(5000);
                      continue;
                  }

        // Split off each jobtype and job
        if (!in_array($jobid, $active_jobs)) {
            ++$child;
            ++$totseen;
            $errmsg = 'total jobs processed: '.$totseen.'. queue: '.$queue[1]['count'].'. active: '.count($active_jobs);
            $error->add('amm', $errmsg);
            unset($errmsg);
            $pid = pcntl_fork();
            if ($pid == -1) {
                // Unable to fork.
        $errmsg = 'amm unable to fork processes. child fork failed';
                $error->add('amm', $errmsg);
                die($errmsg);
                unset($errmsg);
            } elseif ($pid) {
                // Parent Process
        $children[] = $pid;
                $active_jobs[$pid] = $jobid;
                usleep(5000);
            } else {
                // Start of Child Process
        $update = $amm->update_jobstatus($jobid, 'active');
                if (!$update[0]) {
                    $error->add('amm-jobthread', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
                }
                unset($update);
                $error->add('amm-jobthread', 'job start: '.$jobid.' jobtype: '.$jobtype);

        // get jobdata
        $jobdata = $amm->get_job_data($jobid);
                if (!$jobdata[0]) {
                    // unable to access job data, return job to queue
          $error->add('amm-jobthread', 'unable to access jobdata');
                    $update = $amm->update_jobstatus($jobid, 'tryagain');
                    if (!$update[0]) {
                        $error->add('amm-jobthread', 'jobstatus update failed for '.$jobid." '".$update[1]."'");
                    }
                    unset($update);
                    goto jobend;
                }

        // Include jobtype work file
        $include = include $jobtype.'.jobpack.php';
                if ($include === false) {
                    $errmsg = 'failed to load jobpack for type';
                    $error->add('amm-jobthread', $errmsg.' '.$jobtype);
                    unset($errmsg);
                }

                jobend:
        exit(); // End this child when complete
            }
        }
              }
              unset($jobid);
          }
      }
        unset($jobtype, $jobid_array);
    }
      usleep(1000000); // sleep for 1 sec
    //Calc total mem usage
    $mem = memory_get_peak_usage(true);
      if ($mem < 1024) {
          $memtype = 'bytes';
      }
      if ($mem < 1048576) {
          $mem = round(($mem / 1024), 2);
          $memtype = 'Kb';
      } else {
          $mem = round(($mem / 1048576), 2);
          $memtype = 'Mb';
      }
      ++$waitcount;
      if ($waitcount >= $waitcountmax) {
          // Only added progress log once per hour
      if ((date('i') == '0' or date('i') == '00') and date('s') > 0 and date('s') < 31) {
          $errmsg = 'total jobs processed: '.$totseen.'. memory usage: '.$mem.' '.$memtype;
          $error->add('amm', $errmsg);
      }
          unset($errmsg);
          $waitcount = 0;
      }
  }
  // wait until all children processes are gone. no need to leave a mess.
  while (pcntl_wait($status, WNOHANG or WUNTRACED) > 0) {
      usleep(5000);
  }
    $sighup = false;
    $started = time();
}
// All complete. Now ...
$error->add('amm', 'Daemon Stopped. pid: '.$pid);
exit();
?>
