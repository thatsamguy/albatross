<?php error_log(__FILE__);?>
<?php $pt[]['email.page.php :: start'] = microtime(true); ?>
<div class="block doublewidth">
  <h3>Email Addresses</h3>
  <?php
    $err = '';
    $totalsize = array();
    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
    $pt[]['email.page.php :: dns->get_domains_for_acc_id'] = microtime(true);
    if (!$domains[0]) {
        $err = $domains[1];
    } else {
        asort($domains[1]);
        $email_list = array();
        $alias_list = array();
        foreach ($domains[1] as $domain) {
            $array = $email->get_emails_by_domain($domain);
            $pt[]['email.page.php :: email->get_emails_by_domain'] = microtime(true);
            if ($array[0] and is_array($array[1])) {
                $email_list[$domain] = $array[1];
            } else {
                $email_list[$domain] = array();
            }
            unset($array);
            $array = $email->get_aliases_by_domain($domain);
            $pt[]['email.page.php :: email->get_aliases_by_domain'] = microtime(true);
            if ($array[0] and is_array($array[1])) {
                $alias_list[$domain] = $array[1];
            } else {
                $alias_list[$domain] = array();
            }
            unset($array);

    // check some email addresses exist
    $errmsg = 'there are no email addresses for your account';
            $err = $errmsg;
            unset($errmsg);
            foreach ($email_list as $domain => $value) {
                if (count($value) > 0) {
                    $err = '';
                }
            }
        }
        unset($domain);
    }
  ?>
  <?php $pt[]['email.page.php :: email table start'] = microtime(true); ?>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Email Address</th>";
      echo "\t\t<th>Domain</th>";
      echo "\t\t<th>Account Name</th>";
      echo "\t\t<th>Mailbox Size</th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if (strlen($err) > 0) {
          echo "<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td colspan=\"4\" class=\"error\">".$err."</td>\n";
          echo "\t</tr>\n";
          echo "</tfoot>\n";
      } else {
          echo "<tbody>\n";
          $pt[]['email.page.php :: per domain size calc start'] = microtime(true);
          foreach ($email_list as $domain => $value) {
              if ($value != '') {
                  $totalsize[$domain] = 0;
                  foreach ($value as $account) {
                      echo "\t<tr>\n";
                      echo "\t\t<th><a href=\"/email/account/".$account['email'].'">'.$account['email']."</a></th>\n";
                      echo "\t\t<td>".$domain."</td>\n";
                      echo "\t\t<td>".$account['name']."</td>\n";
                      $rawsize = $email->email_size($auth->acc_id, $account['email'], true);
                      if (!$rawsize[0]) {
                          $rawsize = 0;
                      } else {
                          $rawsize = $rawsize[1];
                      }
                      $totalsize[$domain] += $rawsize;
                      $humansize = $email->email_size($auth->acc_id, $account['email'], false);
                      if (!$humansize[0]) {
                          $humansize = 0;
                      } else {
                          $humansize = $humansize[1];
                      }
                      echo "\t\t<td class=\"center\">".$humansize."</td>\n";
                      unset($humansize);
                      if ($account['active'] == 'active') {
                          echo "\t\t<td class=\"center\"><a href=\"/email/account/".$account['email']."/deactivate\">Deactivate Account</a></td>\n";
                      } elseif ($account['active'] == 'inactive') {
                          echo "\t\t<td class=\"center\"><a href=\"/email/account/".$account['email']."/activate\">Activate Account</a></td>\n";
                      }
                      echo "\t</tr>\n";
                  }
                  unset($account);
              }
          }
          $pt[]['email.page.php :: per domain size calc end'] = microtime(true);
          unset($domain, $value);
          echo "</tbody>\n";
      }
    ?>
  </table>
</div>
<?php $pt[]['email.page.php :: email summary start'] = microtime(true); ?>
<div class="block midwidth">
<h3>Email Summary</h3>
<table>
  <thead>
    <th>Domain</th>
    <th>Emails</th>
    <th>Aliases</th>
    <th>Status</th>
    <th>Size</th>
  </thead>
  <tbody>
  <?php
    $total_email = 0;
    $total_alias = 0;
    $total_size = 0;

    foreach ($domains[1] as $domain) {
        echo "\n<tr>\n";
        if (array_key_exists($domain, $totalsize)) {
            $total_size += $totalsize[$domain];
        } else {
            $total_size += 0;
        }
        if (array_key_exists($domain, $totalsize) and $totalsize[$domain] > (1024 * 1024)) {
            $totalsize[$domain] = round(($totalsize[$domain] / (1024 * 1024)), 1).'G';
        } elseif (array_key_exists($domain, $totalsize) and $totalsize[$domain] > 1024) {
            $totalsize[$domain] = round(($totalsize[$domain] / (1024)), 1).'M';
        } elseif (array_key_exists($domain, $totalsize) and $totalsize[$domain] > 0) {
            $totalsize[$domain] = round(($totalsize[$domain]), 1).'K';
        } else {
            $totalsize[$domain] = '';
        }
        echo "\t\t<td class=\"center\">".$domain."</td>\n";
        if (array_key_exists($domain, $email_list)) {
            $total_email += count($email_list[$domain]);
        } else {
            $total_email += 0;
        }
        echo "\t\t<td class=\"center\">".count($email_list[$domain])."</td>\n";
        if (array_key_exists($domain, $alias_list)) {
            $total_alias += count($alias_list[$domain]);
        } else {
            $total_alias += 0;
        }
        echo "\t\t<td class=\"center\">".count($alias_list[$domain])."</td>\n";
        $domain_status = $email->get_domain_status($domain);
        if ($domain_status[0]) {
            $status = $domain_status[1];
        } else {
            $status = 'unknown';
        }
        $status = strtoupper(substr($status, 0, 1)).substr($status, 1);
        echo "\t\t<td class=\"center\">".$status."</td>\n";
        echo "\t\t<td class=\"center\">".$totalsize[$domain]."</td>\n";
        echo "\n</tr>\n";
    }
    echo "\t<tr>\n";
    if ($total_size > (1024 * 1024)) {
        $total_size = round(($total_size / (1024 * 1024)), 1).'G';
    } elseif ($total_size > 1024) {
        $total_size = round(($total_size / (1024)), 1).'M';
    } elseif ($total_size > 0) {
        $total_size = round(($total_size), 1).'K';
    } else {
        $total_size = 'zero';
    }
    echo "\t\t<th class=\"center\">Total</th>\n";
    echo "\t\t<th class=\"center\">".$total_email."</th>\n";
    echo "\t\t<th class=\"center\">".$total_alias."</th>\n";
    echo "\t\t<th class=\"center\"></th>\n";
    echo "\t\t<th class=\"center\">".$total_size."</th>\n";
    echo "\t</tr>\n";
  ?>
  </tbody>
</table>
</div>
<?php unset($domains, $email_list, $alias_list);?>
<?php $pt[]['email.page.php :: end'] = microtime(true); ?>