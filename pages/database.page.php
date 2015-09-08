<?php $pt[]['database.page.php :: start'] = microtime(true); ?>
<div class="block doublewidth">
  <h3>MySQL Databases</h3>
  <?php
    $err = '';
    $db_info = $database->status($auth->acc_id);
    $pt[]['database.page.php :: database->status'] = microtime(true);
    if (!$db_info[0]) {
        $err = $db_info[1];
    } else {
        $totals = array('tables' => 0,'data' => 0,'indicies' => 0,'total' => 0);
        foreach ($db_info[1] as $schema => $value) {
            $totals['tables'] = $totals['tables'] + $value['numtables'];
            $totals['data'] = $totals['data'] + $value['data_size'];
            $totals['indicies'] = $totals['indicies'] + $value['index_size'];
            $totals['total'] = $totals['total'] + $value['total_size'];

            if ($value['data_size'] > (1024 * 1024 * 1024)) {
                $db_info[1][$schema]['data_size'] = round(($value['data_size'] / (1024 * 1024 * 1024)), 1).'G';
            } elseif ($value['data_size'] > (1024 * 1024)) {
                $db_info[1][$schema]['data_size'] = round(($value['data_size'] / (1024 * 1024)), 1).'M';
            } elseif ($value['data_size'] > 1024) {
                $db_info[1][$schema]['data_size'] = round(($value['data_size'] / (1024)), 1).'K';
            } elseif ($value['data_size'] > 0) {
                $db_info[1][$schema]['data_size'] = round(($value['data_size'] / (1024)), 2).'K';
            } else {
                $db_info[1][$schema]['data_size'] = 'zero';
            }

            if ($value['index_size'] > (1024 * 1024 * 1024)) {
                $db_info[1][$schema]['index_size'] = round(($value['index_size'] / (1024 * 1024 * 1024)), 1).'G';
            } elseif ($value['index_size'] > (1024 * 1024)) {
                $db_info[1][$schema]['index_size'] = round(($value['index_size'] / (1024 * 1024)), 1).'M';
            } elseif ($value['index_size'] > 1024) {
                $db_info[1][$schema]['index_size'] = round(($value['index_size'] / (1024)), 1).'K';
            } elseif ($value['index_size'] > 0) {
                $db_info[1][$schema]['index_size'] = round(($value['index_size'] / (1024)), 2).'K';
            } else {
                $db_info[1][$schema]['index_size'] = 'zero';
            }

            if ($value['total_size'] > (1024 * 1024 * 1024)) {
                $db_info[1][$schema]['total_size'] = round(($value['total_size'] / (1024 * 1024 * 1024)), 1).'G';
            } elseif ($value['total_size'] > (1024 * 1024)) {
                $db_info[1][$schema]['total_size'] = round(($value['total_size'] / (1024 * 1024)), 1).'M';
            } elseif ($value['total_size'] > 1024) {
                $db_info[1][$schema]['total_size'] = round(($value['total_size'] / (1024)), 1).'K';
            } elseif ($value['total_size'] > 0) {
                $db_info[1][$schema]['total_size'] = round(($value['total_size'] / (1024)), 2).'K';
            } else {
                $db_info[1][$schema]['total_size'] = 'zero';
            }
        }
        unset($schema, $value);

        if ($totals['data'] > (1024 * 1024 * 1024)) {
            $totals['data'] = round(($totals['data'] / (1024 * 1024 * 1024)), 1).'G';
        } elseif ($totals['data'] > (1024 * 1024)) {
            $totals['data'] = round(($totals['data'] / (1024 * 1024)), 1).'M';
        } elseif ($totals['data'] > 1024) {
            $totals['data'] = round(($totals['data'] / (1024)), 1).'K';
        } elseif ($value['total_size'] > 0) {
            $totals['data'] = round(($totals['data'] / (1024)), 2).'K';
        } else {
            $totals['data'] = 'zero';
        }

        if ($totals['indicies'] > (1024 * 1024 * 1024)) {
            $totals['indicies'] = round(($totals['indicies'] / (1024 * 1024 * 1024)), 1).'G';
        } elseif ($totals['indicies'] > (1024 * 1024)) {
            $totals['indicies'] = round(($totals['indicies'] / (1024 * 1024)), 1).'M';
        } elseif ($totals['indicies'] > 1024) {
            $totals['indicies'] = round(($totals['indicies'] / (1024)), 1).'K';
        } elseif ($value['total_size'] > 0) {
            $totals['indicies'] = round(($totals['indicies'] / (1024)), 2).'K';
        } else {
            $totals['indicies'] = 'zero';
        }

        if ($totals['total'] > (1024 * 1024 * 1024)) {
            $totals['total'] = round(($totals['total'] / (1024 * 1024 * 1024)), 1).'G';
        } elseif ($totals['total'] > (1024 * 1024)) {
            $totals['total'] = round(($totals['total'] / (1024 * 1024)), 1).'M';
        } elseif ($totals['total'] > 1024) {
            $totals['total'] = round(($totals['total'] / (1024)), 1).'K';
        } elseif ($value['total_size'] > 0) {
            $totals['total'] = round(($totals['total'] / (1024)), 2).'K';
        } else {
            $totals['total'] = 'zero';
        }
    }
  ?>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Database (Schema)</th>";
      echo "\t\t<th>Tables</th>";
      echo "\t\t<th>Data</th>";
      echo "\t\t<th>Indicies</th>";
      echo "\t\t<th>Total Size</th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if (strlen($err) > 0) {
          echo "<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td colspan=\"5\" class=\"error\">".$err."</td>\n";
          echo "\t</tr>\n";
          echo "</tfoot>\n";
      } else {
          echo "<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td style=\"bold\">Summary</td>\n";
          echo "\t\t<th>".$totals['tables']."</th>\n";
          echo "\t\t<th>".$totals['data']."</th>\n";
          echo "\t\t<th>".$totals['indicies']."</th>\n";
          echo "\t\t<th>".$totals['total']."</th>\n";
          echo "\t</tr>\n";
          echo "</tfoot>\n";
          echo "<tbody>\n";
          foreach ($db_info[1] as $schema => $value) {
              echo "\t<tr>\n";
              echo "\t\t<th><a href=\"/database/view/".$schema.'">'.$schema."</a></th>\n";
              echo "\t\t<td class=\"center\">".$value['numtables']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['data_size']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['index_size']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['total_size']."</td>\n";
              echo "\t</tr>\n";
          }
          unset($schema, $value);
          echo "</tbody>\n";
      }
    ?>
  </table>
</div>
<?php unset($db_info, $err);?>
<?php $pt[]['database.page.php :: end'] = microtime(true); ?>