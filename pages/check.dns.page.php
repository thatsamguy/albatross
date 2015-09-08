<div class="block doublewidth">
  <h3>Check worldwide propagation of records<?php if ($domain_id > 0) {
    echo ' from '.$domain;
}?></h3>
  <p>Each check will open in a new window.</p>
  <table>
    <?php
      $records = $dns->get_records_from_domain($domain_id);
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t<th colspan=\"4\">Records</th>\n";
      echo "\t</tr>\n";
      echo "</thead>\n";
      echo "<tbody>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Name</th>\n";
      echo "\t\t<th>Type</th>\n";
      echo "\t\t<th>Content (Priority)</th>\n";
      echo "\t<td class=\"center\">Powered by <a target=\"_blank\" href=\"http://www.whatsmydns.net\">What's My DNS?</a></td>\n";
      echo "\t<tr>\n";
      if ($records[0]) {
          foreach ($records[1] as $key => $record) {
              if ($record['type'] != 'SOA') {
                  echo "\t<tr>\n";
                  echo "\t\t<td>".$record['name']."</td>\n";
                  echo "\t\t<td>".$record['type']."</td>\n";
                  if ($record['type'] != 'MX') {
                      $record['priority'] = '';
                  } else {
                      $record['priority'] = ' ('.$record['priority'].')';
                  }
                  echo "\t\t<td>".$record['content'].$record['priority']."</td>\n";
                  if ($record['type'] == 'NS') {
                      echo "\t\t<td class=\"center\"><a target=\"_blank\" href=\"http://www.whatsmydns.net/#NS/".$record['name']."\">Check Name servers</a></td>\n";
                  } elseif ($record['type'] == 'MX') {
                      echo "\t\t<td class=\"center\"><a target=\"_blank\" href=\"http://www.whatsmydns.net/#MX/".$domain."\">Check Email servers</a></td>\n";
                  } else {
                      echo "\t\t<td class=\"center\"><a target=\"_blank\" href=\"http://www.whatsmydns.net/#".$record['type'].'/'.$record['name']."\">Check record</a></td>\n";
                  }
                  echo "\t</tr>\n";
              }
          }
          unset($record, $key);
      } else {
          echo "\t<tr>\n";
          echo "\t\t<th colspan=\"4\" class=\"error=\">".$records[1]."</th>\n";
          echo "\t</tr>\n";
      }
      unset($records);
    ?>
    </tbody>
  </table>
</div>