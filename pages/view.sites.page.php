<?php error_log(__FILE__);?>
<?php
$errormsg2 = '';

if (array_key_exists('adddomainlist', $_POST) and $_POST['adddomainlist'] == 'Add domain to site') {
    goto processing;
} elseif (array_key_exists('addredirect', $_POST) and $_POST['addredirect'] == 'Add redirect') {
    goto processing;
} elseif (array_key_exists('profile', $_POST) and $_POST['changeprofile'] == 'Change Profile') {
    goto processing;
} elseif ($uri[3] == 'removedomain' and $uri[4] != '') {
    goto processing;
} elseif ($uri[3] == 'makeprimary' and $uri[4] != '') {
    goto processing;
} elseif ($uri[3] == 'removeredirect' and $uri[4] != '') {
    goto processing;
} elseif ($uri[3] == 'phpmicrocache' and $uri[4] != '') {
    goto processing;
} else {
    goto postprocessing;
}

processing:
if (array_key_exists('domain', $_POST) and trim($_POST['domain']) != '') {
    $result = $site->domainlist_add($auth->acc_id, $sitename, strtolower(trim($_POST['domain'])));
    if ($result[0]) {
        unset($result);
        goto postprocessing;
    } else {
        $errormsg = $result[1];
        unset($result);
        goto postprocessing;
    }
}

if (array_key_exists('addredirect', $_POST) and array_key_exists('type', $_POST) and trim($_POST['type']) != '') {
    $result = $site->redirect_add($auth->acc_id, $sitename, $_POST['regex'], $_POST['destination'], $_POST['type']);
    if ($result[0]) {
        unset($result);
        goto postprocessing;
    } else {
        $errormsg2 = $result[1];
        unset($result);
        goto postprocessing;
    }
}

if (array_key_exists('changeprofile', $_POST) and array_key_exists('profile', $_POST) and trim($_POST['profile']) != '') {
    $result = $site->change_profile($auth->acc_id, $sitename, $_POST['profile']);
    if ($result[0]) {
        unset($result);
        header('Location: /sites/view/'.$sitename);
        goto postprocessing;
    } else {
        $errormsg2 = $result[1];
        unset($result);
        goto postprocessing;
    }
}

if ($uri[3] == 'removedomain') {
    $result = $site->domainlist_remove($auth->acc_id, $sitename, $uri[4]);
    if ($result[0]) {
        unset($result);
        goto postprocessing;
    } else {
        $errormsg = $result[1];
        unset($result);
        goto postprocessing;
    }
}

if ($uri[3] == 'makeprimary') {
    $result = $site->domain_makeprimary($auth->acc_id, $sitename, $uri[4]);
    if ($result[0]) {
        unset($result);
        header('Location: /sites/view/'.$sitename);
        goto postprocessing;
    } else {
        $errormsg = $result[1];
        unset($result);
        goto postprocessing;
    }
}

if ($uri[3] == 'removeredirect') {
    $result = $site->redirect_remove($auth->acc_id, $sitename, $uri[4]);
    if ($result[0]) {
        unset($result);
        goto postprocessing;
    } else {
        $errormsg2 = $result[1];
        unset($result);
        goto postprocessing;
    }
}

if ($uri[3] == 'phpmicrocache') {
    if (strtolower($uri[4]) == 'disable') {
        $result = $site->php_microcache_remove($auth->acc_id, $sitename);
        if ($result[0]) {
            $site_data = $site->status($auth->acc_id);
        }
        goto postprocessing;
    }
    if (strtolower($uri[4]) == 'enable') {
        $result = $site->php_microcache_add($auth->acc_id, $sitename);
        if ($result[0]) {
            $site_data = $site->status($auth->acc_id);
        }
        goto postprocessing;
    }
}

postprocessing:
?>
<?php
$flag = $site->flag_check($auth->acc_id, $sitename, 'nginx');
if ($flag[0]) {
    ?>
  <div class="error fullwidth">
    This site's configuration has been changed but not yet applied. <a href="/sites/updateconf/<?php echo $sitename;
    ?>">Apply this configuration now</a>.
  </div>
<?php

}
?>
<div class="block">
  <h3><?php echo $sitename;?></h3>
  <?php
    $thissite = $site_data[1][$sitename];
    $thissitestats = $site->stats($auth->acc_id);
  ?>
  <form action="/sites/view/<?php echo $sitename;?>" method="post">
  <table>
  <?php
    if ($thissite['phpmicrocache'] == 'enabled') {
        $phpmicrocache = 'Disable';
    } else {
        $phpmicrocache = 'Enable';
    }
    echo "<tfoot>\n";
    echo "\t<tr>\n";
    echo "\t\t<td class=\"center\" colspan=\"2\"><a href=\"/sites/view/".$sitename.'/phpmicrocache/'.$phpmicrocache.'">'.$phpmicrocache." PHP Microcache</a></td>\n";
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th class=\"center\" colspan=\"2\"><input type=\"submit\" name=\"changeprofile\" value=\"Change Profile\">&nbsp;&nbsp;&nbsp;\n";
    echo "\t\t\t<select name=\"profile\">\n";
    $nginxprofile['default'] = '';
    $nginxprofile['wordpress'] = '';
    $nginxprofile['mediawiki'] = '';
    $nginxprofile[$thissite['profile']] = ' selected';
    echo "\t\t\t<option value=\"default\"".$nginxprofile['default'].">default</option>\n";
    echo "\t\t\t<option value=\"wordpress\"".$nginxprofile['wordpress'].">wordpress</option>\n";
    echo "\t\t\t<option value=\"mediawiki\"".$nginxprofile['mediawiki'].">mediawiki</option>\n";
    echo "\t\t\t</select>\n";
    echo "\t\t</th>\n";
    echo "\t</tr>\n";
    echo "</tfoot>\n";
    echo "<tbody>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>Primary Domain</th>\n";
    echo "\t\t<td>".$thissite['defaultdomain']."</td>\n";
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>Configuration Profile</th>\n";
    echo "\t\t<td>".$thissite['profile']."</td>\n";
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>PHP Enabled</th>\n";
    if ($thissite['phpenabled'] == '1') {
        $thissite['phpenabled'] = 'Enabled - Version: '.phpversion();
    } else {
        $thissite['phpenabled'] = 'Disabled';
    }
    echo "\t\t<td>".$thissite['phpenabled']."</td>\n";
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>PHP Microcache</th>\n";
    if ($thissite['phpmicrocache'] == 'enabled') {
        $thissite['phpmicrocache'] = 'Enabled';
    } else {
        $thissite['phpmicrocache'] = 'Disabled';
    }
    echo "\t\t<td>".$thissite['phpmicrocache']."</td>\n";
    echo "\t</tr>\n";
    echo "\t\t<th>Custom Configuration</th>\n";
    if ($thissite['customcode'] == 'enabled') {
        $thissite['customcode'] = 'Enabled';
    } else {
        $thissite['customcode'] = 'Disabled';
    }
    echo "\t\t<td>".$thissite['customcode']."</td>\n";
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>Size on disk</th>\n";
    echo "\t\t<td>".$thissite['size']."</td>\n";
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>Statistics</th>\n";
    if ($thissitestats[1][$sitename] != '') {
        echo "\t\t<td><a href=\"".$thissitestats[1][$sitename]."\" target=\"_blank\">Site Statistics</a></td>\n";
    } else {
        echo "\t\t<td>No statistics avaliable</td>\n";
    }
    echo "\t</tr>\n";
    echo "</tbody>\n";
  ?>
  </table>
  </form>
</div>
<div class="block">
  <h3>Domain List</h3>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Domain</th>";
      echo "\t\t<th colspan=\"2\">Actions</th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      echo "<tfoot>\n";
      echo "\t<tr>\n";
      echo "\t\t<td colspan=\"3\">The site will work with any of the domains listed here, including wildcard (*) subdomains.</td>\n";
      echo "\t</tr>\n";
      echo "</tfoot>\n";
      echo "<tbody>\n";
      echo "\t<tr>\n";
      echo "\t\t<td>".$thissite['defaultdomain']."</td>\n";
      echo "\t\t<td colspan=\"2\" class=\"center\">Primary Domain</td>\n";
      echo "\t</tr>\n";
      $domainlist = $site->get_domainslist($auth->acc_id, $sitename);
      if ($domainlist[0]) {
          $domainlist[1] = explode(' ', trim($domainlist[1]));
          foreach ($domainlist[1] as $key => $value) {
              if (trim($value) == '') {
                  unset($domainlist[1][$key]);
              }
          }
          if (count($domainlist) > 0) {
              foreach ($domainlist[1] as $value) {
                  if (trim($value) != '') {
                      echo "\t<tr>\n";
                      echo "\t\t<td>".trim($value)."</td>\n";
                      echo "\t\t<td class=\"center\"><a href=\"/sites/view/".$sitename.'/removedomain/'.trim($value)."\">Remove</a></td>\n";
                      echo "\t\t<td class=\"center\"><a href=\"/sites/view/".$sitename.'/makeprimary/'.trim($value)."\">Make Primary</a></td>\n";
                      echo "\t</tr>\n";
                  }
              }
          } else {
              echo "\t<tr>\n";
              echo "\t\t<td colspan=\"2\" class=\"error\">There are no alternative domains for this site.</td>\n";
              echo "\t</tr>\n";
          }
      }
      echo "</tbody>\n";
      unset($domainlist);
    ?>
  </table>
  <form action="/sites/view/<?php echo $sitename;?>" method="post">
  <table>
    <?php
      if (strlen($errormsg) > 0) {
          echo "\t<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td colspan=\"2\" class=\"error\">".$errormsg."</td>\n";
          echo "\t</tr>\n";
          echo "\t</tfoot>\n";
      }
      echo "<tbody>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Domain</th>\n";
      echo "\t\t<td><input class=\"text\" size=\"24\" type=\"textbox\" name=\"domain\"></td>\n";
      echo "\t</tr>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Examples</th>\n";
      echo "\t\t<td>cyprix.com.au<br>blog.cyprix.net<br>*.cyprix.com.au</td>\n";
      echo "\t</tr>\n";
      echo "\t<tr>\n";
      echo "\t\t<th colspan=\"2\" class=\"center\"><input type=\"submit\" name=\"adddomainlist\" value=\"Add domain to site\"></th>\n";
      echo "\t</tr>\n";
      echo "</tbody>\n";
    ?>
  </table>
  </form>
</div>
<div class="block doublewidth">
  <h3>Redirect List</h3>
  <form action="/sites/view/<?php echo $sitename;?>" method="post">
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Redirect</th>";
      echo "\t\t<th>Destination</th>";
      echo "\t\t<th>Type</th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if (strlen($errormsg2) > 0) {
          echo "\t<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td colspan=\"3\" class=\"error\">".$errormsg2."</td>\n";
          echo "\t</tr>\n";
          echo "\t</tfoot>\n";
      } else {
          echo "\t<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td>/&nbsp;<input type=\"textbox\" size=\"16\" name=\"regex\"></td>\n";
          echo "\t\t<td><input type=\"textbox\" size=\"24\" name=\"destination\"></td>\n";
          echo "\t\t<td class=\"center\"><select name=\"type\"><option value=\"\">Select Redirect Type</option><option value=\"redirect\">Redirect (302)</option><option value=\"permanent\">Permanent (301)</option></select></td>\n";
          echo "\t\t<th class=\"center\"><input type=\"submit\" name=\"addredirect\" value=\"Add redirect\"></th>\n";
          echo "\t</tr>\n";
          echo "\t</tfoot>\n";
      }
      unset($errormsg2);
      $redirectlist = $site->get_redirect_array($auth->acc_id, $sitename);
      if ($redirectlist[0]) {
          echo "<tbody>\n";
          if (count($redirectlist) > 0) {
              foreach ($redirectlist[1] as $value) {
                  echo "\t<tr>\n";
                  echo "\t\t<td>/".trim($value['regex'])."</td>\n";
                  echo "\t\t<td>".trim($value['destination'])."</td>\n";
                  echo "\t\t<td class=\"center\">".trim(strtoupper(substr($value['type'], 0, 1)).substr($value['type'], 1))."</td>\n";
                  echo "\t\t<td class=\"center\"><a href=\"/sites/view/".$sitename.'/removeredirect/'.trim($value['redirect'])."\">Remove</a></td>\n";
                  echo "\t</tr>\n";
              }
          }
          echo "</tbody>\n";
      } else {
          echo "<tbody>\n";
          echo "\t<tr>\n";
          echo "\t\t<td colspan=\"3\" class=\"error\">This site has no custom redirects.</td>\n";
          echo "\t</tr>\n";
          echo "</tbody>\n";
      }
      unset($redirectlist);
    ?>
  </table>
  </form>
</div>
<?php unset($thissite); ?>
