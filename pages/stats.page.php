<?php error_log(__FILE__);?>
<div class="block">
  <?php
    $totals = array('sites' => 0,'databases' => 0,'emails' => 0);
    $maxsize = 10240 * 5; // megabytes (50G)
    $err = '';

    $site_stats = $site->stats($auth->acc_id);
    if (!$site_stats[0]) {
        $err = $site_stats[1];
    }

    $site_data = $site->status($auth->acc_id);
    if ($site_data[0]) {
        $totals['sites'] = round(($site_data[1]['__totalsize__']) / 1024, 2); // megabytes to 2dp
      $bandwidth = round((($site_data[1]['__totalbandwidth__']) / 1024) / 1024 / 1024, 2); // gigabytes to 2dp
    }

    $db_info = $database->status($auth->acc_id);
    if ($db_info[0]) {
        foreach ($db_info[1] as $schema => $value) {
            $totals['databases'] = $totals['databases'] + $value['total_size'];
        }
        $totals['databases'] = round($totals['databases'] / (1024 * 1024), 2); // megabytes to 2dp
    }

    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
    if ($domains[0]) {
        $email_list = array();
        foreach ($domains[1] as $domain) {
            $array = $email->get_emails_by_domain($domain);
            if ($array[0] and is_array($array[1])) {
                $email_list[$domain] = $array[1];
            } else {
                $email_list[$domain] = array();
            }
            unset($array);
        }

        foreach ($email_list as $domain => $value) {
            if ($value != '') {
                foreach ($value as $account) {
                    $rawsize = $email->email_size($auth->acc_id, $account['email'], true);
                    if (!$rawsize[0]) {
                        $rawsize = 0;
                    } else {
                        $rawsize = $rawsize[1];
                    }
                    $totals['emails'] = $totals['emails'] + $rawsize;
                }
            }
        }
        unset($email_list, $domain, $value, $account);
        $totals['emails'] = round($totals['emails'] / 1024, 2); // megabytes to 2dp
    }

    $used = array_sum($totals);
    $usedpercent = round(($used / $maxsize), 3) * 100;
    $free = 100 - $usedpercent;
  ?>
  <h3>Disk Space Usage</h3>
  <img class="center" src="http://chart.googleapis.com/chart?cht=p3&chd=t:<?php echo $usedpercent;?>,<?php echo $free;?>&chs=360x140&chco=0088FF,CCDDFF&chl=<?php echo $usedpercent;?>%+Used|<?php echo $free;?>%+Free" title="Total usage of disk quota" />
  <table>
    <?php
      if ($used == 0) {
          $used = 'zero';
      } elseif ($used > 1024) {
          $used = round($used / 1024, 2).'G'; // gigabytes to 2dp
      } else {
          $used = $used.'M';
      }
      if ($totals['sites'] == 0) {
          $totals['sites'] = 'zero';
      } elseif ($totals['sites'] > 1024) {
          $totals['sites'] = round($totals['sites'] / 1024, 1).'G'; // gigabytes to 1dp
      } else {
          $totals['sites'] = round($totals['sites'], 1).'M';
      }
      if ($totals['emails'] == 0) {
          $totals['emails'] = 'zero';
      } elseif ($totals['emails'] > 1024) {
          $totals['emails'] = round($totals['emails'] / 1024, 1).'G'; // gigabytes to 1dp
      } else {
          $totals['emails'] = round($totals['emails'], 1).'M';
      }
      if ($totals['databases'] == 0) {
          $totals['databases'] = 'zero';
      } elseif ($totals['databases'] > 1024) {
          $totals['databases'] = round($totals['databases'] / 1024, 1).'G'; // gigabytes to 1dp
      } else {
          $totals['databases'] = round($totals['databases'], 1).'M';
      }

      echo "<tfoot>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Total</th>\n";
      echo "\t\t<th>".$used."</th>\n";
      echo "\t</tr>\n";
      echo "</tfoot>\n";
      echo "<tbody>\n";
      echo "\t<tr>\n";
      echo "\t\t<th><a href=\"/sites\">Sites</a></th>\n";
      echo "\t\t<td class=\"center\">".$totals['sites']."</td>\n";
      echo "\t</tr>\n";
      echo "\t<tr>\n";
      echo "\t\t<th><a href=\"/email\">Emails</a></th>\n";
      echo "\t\t<td class=\"center\">".$totals['emails']."</td>\n";
      echo "\t</tr>\n";
      echo "\t<tr>\n";
      echo "\t\t<th><a href=\"/database\">Databases</a></th>\n";
      echo "\t\t<td class=\"center\">".$totals['databases']."</td>\n";
      echo "\t</tr>\n";
      echo "</tbody>\n";
    ?>
  </table>
  <?php unset($free, $used, $totals); ?>
</div>
<?php if ($auth->acc_id == '100001') {
    /*
http://chart.apis.google.com/chart?chf=c,ls,90,CCDDFF,0.2,FFFFFF,0.2,CCDDFF,0.2,FFFFFF,0.3,CCDDFF,0.1&chxl=0:|Base+Level|Level+2|Level+3|Level+4|1:|Jul|Aug|Sep|Oct|Nov|Dec&chxp=0,10,20,30,45&chxr=0,0,50&chxs=0,676767,11.5,0,lt,676767&chxt=y,x&chs=360x360&chco=0088FF&chds=0,50&chd=t:6.8,10.25,16.9,35,28.55,12&chtt=Monthly+Data+Usage&cht=bvs&chbh=a
*/
?>
<div class="block">
  <h3>Monthly Data Usage</h3>
  <img class="center" src="http://chart.apis.google.com/chart?chf=c,ls,90,CCDDFF,0.2,FFFFFF,0.2,CCDDFF,0.2,FFFFFF,0.3,CCDDFF,0.1&chxl=0:|Base+Level|Level+2|Level+3|Level+4|1:|Jun|Jul|Aug|Sep|Oct|Nov&chxp=0,10,20,30,45&chxr=0,0,50&chxs=0,676767,11.5,0,lt,676767&chxt=y,x&chs=360x280&chco=0088FF&chds=0,50&chd=t:6.8,10.25,16.9,35,28.55,<?php echo $bandwidth;
    ?>&cht=bvs&chbh=a" />
  <?php unset($free, $used, $totals);
    ?>
</div>
<?php 
} ?>
<div class="block">
  <h3>Site Statistics</h3>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Site</th>";
      echo "\t\t<th>AWStats</th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if (strlen($err) > 0) {
          echo "<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td colspan=\"2\" class=\"error\">".$err."</td>\n";
          echo "\t</tr>\n";
          echo "</tfoot>\n";
      } else {
          echo "<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td colspan=\"2\">Each link will open in a new window</td>\n";
          echo "\t</tr>\n";
          echo "</tfoot>\n";
          echo "<tbody>\n";
          foreach ($site_stats[1] as $site_name => $url) {
              if ($site_name != '__totalsize__' and $site_name != '__totalbandwidth__') {
                  echo "\t<tr>\n";
                  echo "\t\t<th>".$site_name."</th>\n";
                  echo "\t\t<td class=\"center\"><a href=\"".$url."\" target=\"_blank\">Site Statistics</a></td>\n";
                  echo "\t</tr>\n";
              }
          }
          unset($site_name, $url);
          echo "</tbody>\n";
      }
    ?>
  </table>
</div>
<?php unset($site_data, $err);?>