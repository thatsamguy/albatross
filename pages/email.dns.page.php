<?php
  if ($domain_id > 0) {
      $nslist[] = $domain;
      ?>
<?php
if ((array_key_exists('pri_back', $_POST) and $_POST['pri_back']) or $uri[3] == 'remove') {
    goto processing;
} else {
    goto postprocessing;
}

      processing:
$domains = $dns->get_domains_for_acc_id($auth->acc_id);
      if (!$domains[0]) {
          $errormsg = $domains[1];
          goto postprocessing;
      }

      if (array_key_exists('pri_back', $_POST) and $_POST['pri_back'] == 'Set primary & backup servers to Albatross defaults') {
          if (!in_array($domain, $domains[1])) {
              $errmsg = "Unable to alter domain '".$domain."' due to lack of permission";
              $errormsg = $errmsg;
              unset($errmsg);
              goto postprocessing;
          }
  /*$result = $dns->add_record($domain_id,$_POST['domain'],"NS",$_POST['server'],"");
  if(!$result[0]){
    $errormsg = $result[1];
    goto postprocessing;
  }*/$errormsg = 'defaults not yet working';
      }

      if (array_key_exists('pri_back', $_POST) and $_POST['pri_back'] == 'Add Server') {
          if (!in_array($_POST['domain'], $domains[1])) {
              $errmsg = "Unable to alter domain '".$_POST['domain']."' due to lack of permission";
              $errormsg = $errmsg;
              unset($errmsg);
              goto postprocessing;
          } else {
              $result = $dns->add_record($domain_id, $_POST['domain'], 'MX', $_POST['server'], $_POST['priority']);
              if (!$result[0]) {
                  $errormsg = $result[1];
                  goto postprocessing;
              }
          }
      }

      if ($uri[3] == 'remove' and $uri[4] > 0) {
          // TODO: log remove action
  $result = $dns->remove_record($uri[4]);
          if (!$result[0]) {
              $errormsg = $result[1];
              goto postprocessing;
          }
      }

      postprocessing:
?>
<div class="block">
  <h3>Email servers<?php if ($domain_id > 0) {
    echo ' for '.$domain;
}
      ?></h3>
  <table>
    <?php
      $records = $dns->get_records_from_domain($domain_id);
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th colspan=\"2\">Current email servers</th>\n";
      echo "\t</tr>\n";
      echo "</thead>\n";
      echo "<tbody>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Server</th>\n";
      echo "\t\t<th>Priority</th>\n";
      echo "\t</tr>\n";
      if ($records[0]) {
          foreach ($records[1] as $key => $record) {
              if ($record['type'] == 'MX') {
                  if (!in_array($record['name'], $nslist)) {
                      $nslist[] = $record['name'];
                  }
                  echo "\t<tr>\n";
                  echo "\t\t<td>".$record['content']."</td>\n";
                  echo "\t\t<td>".$record['priority']."</td>\n";
                  echo "\t\t<td><a href=\"/dns/email/".$domain_id.'/remove/'.$key."\">Remove</a></td>\n";
                  echo "\t</tr>\n";
              }
          }
          unset($record, $key);
      } else {
          echo "\t<tr>\n";
          echo "\t\t<th colspan=\"2\" class=\"error=\">".$records[1]."</th>\n";
          echo "\t</tr>\n";
      }
      unset($records);
      ?>
    </tbody>
  </table>
</div>
<div class="block">
  <h3>Update Email servers<?php if ($domain_id > 0) {
    echo ' for '.$domain;
}
      ?></h3>
  <!--<form action="/dns/email/<?php echo $domain_id;
      ?>" method="post">
  <table>
    <thead>
      <tr>
	<td colspan="2"><input type="submit" name="pri_back" value="Set primary &amp; backup servers to Albatross defaults"></td>
      </tr>
    </thead>
  </table>
  </form>-->
  <form action="/dns/email/<?php echo $domain_id;
      ?>" method="post">
  <table>
    <?php if (strlen($errormsg) > 0) {
    ?>
    <tfoot>
      <tr>
	<td colspan="3" class="error"><?php echo $errormsg;
    ?></td>
      </tr>
    </tfoot>
    <?php 
}
      ?>
    <tbody>
      <tr>
	<th>Domain</th>
	<th>Server</th>
	<th>Priority</th>
      </tr>
      <tr>
	<td><input type="hidden" name="domain" value="<?php echo $domain;
      ?>"><?php echo $domain;
      ?></td>
	<td><input class="text" size="15" type="textbox" name="server"></td>
	<td><input class="text" size="2" type="textbox" name="priority"></td>
      </tr>
      <tr>
	<td>Example</td>
	<td>mail.cyprix.com.au</td>
	<td>10</td>
      </tr>
      <tr>
	<td colspan="3">Lower priority numbers means a higher priority server.</td>
      </tr>
      <tr>
	<th colspan="3" class="center"><input type="submit" name="pri_back" value="Add Server"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>
<?php

  } else { // Global Configuation
/*
      <select name="domain">
        <?php foreach($nslist as $item){
          echo "\t\t<option value=\"".$item."\">".$item."</option>\n";
        } ?>
      </select>
*/
if ($_POST['pri_back']) {
    goto processing2;
} else {
    goto postprocessing2;
}

      processing2:
$domains = $dns->get_domains_for_acc_id($auth->acc_id);
      if (!$domains[0]) {
          $errormsg = $domains[1];
          goto postprocessing2;
      }

      if ($_POST['pri_back'] == 'Set primary & backup servers to Albatross defaults') {
          /*if(!in_array($domain,$domains[1])){
    $errmsg = "Unable to alter domain '".$domain."' due to lack of permission";
    $errormsg = $errmsg;
    unset($errmsg);
    goto postprocessing2;
  }
  $result = $dns->add_record($domain_id,$_POST['domain'],"NS",$_POST['server'],"");
  if(!$result[0]){
    $errormsg = $result[1];
    goto postprocessing2;
  }*/$errormsg = 'defaults not yet working';
      }

      if ($_POST['pri_back'] == 'Add server to all domains') {
          /*if(!in_array($_POST['domain'],$domains[1])){
    $errmsg = "Unable to alter domain '".$_POST['domain']."' due to lack of permission";
    $errormsg = $errmsg;
    unset($errmsg);
    goto postprocessing2;
  }else{
    $result = $dns->add_record($domain_id,$_POST['domain'],"MX",$_POST['server'],$_POST['priority']);
    if(!$result[0]){
      $errormsg = $result[1];
      goto postprocessing2;
    }
  }*/
  $errormsg = 'add server to all domains not yet working';
      }

      postprocessing2:
?>
<div class="block doublewidth">
  <h3>Email servers</h3>
  <table>
    <?php
    $domains = $dns->get_domains_for_acc_id($auth->acc_id);
      if (!$domains[0]) {
          $errormsg = $domains[1];
      } else {
          echo "<thead>\n";
          echo "\t<tr>\n";
          echo "\t\t<th colspan=\"3\">Current email servers</th>\n";
          echo "\t</tr>\n";
          echo "</thead>\n";
          echo "<tbody>\n";
          echo "\t<tr>\n";
          echo "\t\t<th>Domain</th>\n";
          echo "\t\t<th>Server</th>\n";
          echo "\t\t<th>Priority</th>\n";
          echo "\t</tr>\n";
          foreach ($domains[1] as $domain_id => $domain) {
              $records = $dns->get_records_from_domain($domain_id);
              if ($records[0]) {
                  foreach ($records[1] as $key => $record) {
                      if ($record['type'] == 'MX') {
                          if (!in_array($record['name'], $nslist)) {
                              $nslist[] = $record['name'];
                          }
                          echo "\t<tr>\n";
                          echo "\t\t<td><a href=\"/dns/domain/".$domain_id.'" title="View this domain">'.$record['name']."</a></td>\n";
                          echo "\t\t<td>".$record['content']."</td>\n";
                          echo "\t\t<td>".$record['priority']."</td>\n";
                          echo "\t\t<td><a href=\"/dns/email/".$domain_id.'" title="Update this domains email servers">Servers</a></td>';
                          echo "\t</tr>\n";
                      }
                  }
                  unset($record, $key);
              } else {
                  echo "\t<tr>\n";
                  echo "\t\t<th colspan=\"3\" class=\"error=\">".$records[1]."</th>\n";
                  echo "\t</tr>\n";
              }
              unset($records);
          }
          unset($records, $domain, $domain_id);
      }
      ?>
    </tbody>
  </table>
</div>
<div class="block">
  <h3>Update All Email servers</h3>
  <!--<form action="/dns/email" method="post">
  <table>
    <thead>
      <tr>
	<td colspan="2"><input type="submit" name="pri_back" value="Set primary &amp; backup servers to Albatross defaults"></td>
      </tr>
    </thead>
  </table>
  </form>-->
  <form action="/dns/email" method="post">
  <table>
    <?php if (strlen($errormsg) > 0) {
    ?>
    <tfoot>
      <tr>
	<td colspan="3" class="error"><?php echo $errormsg;
    ?></td>
      </tr>
    </tfoot>
    <?php 
}
      ?>
    <tbody>
      <tr>
	<th>Domain</th>
	<th>Server</th>
	<th>Priority</th>
      </tr>
      <tr>
	<td>All domains</td>
	<td><input class="text" size="15" type="textbox" name="server"></td>
	<td><input class="text" size="2" type="textbox" name="priority"></td>
      </tr>
      <tr>
	<td>Example</td>
	<td>mail.cyprix.com.au</td>
	<td>10</td>
      </tr>
      <tr>
	<td colspan="3">Lower priority numbers means a higher priority server.</td>
      </tr>
      <tr>
	<th colspan="3" class="center"><input type="submit" name="pri_back" value="Add server to all domains"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>
<?php

  }
?>