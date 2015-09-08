<?php error_log(__FILE__);?>
<div class="block doublewidth">
  <h3><?php echo $db_name;?></h3>
  <?php
    $err = '';
    $table_info = $database->db_status($db_name);
    if ($table_info[0]) {
        foreach ($table_info[1] as $key => $value) {
            if ($value['data_length'] > (1024 * 1024 * 1024)) {
                $table_info[1][$key]['data_length'] = round(($value['data_length'] / (1024 * 1024 * 1024)), 1).'G';
            } elseif ($value['data_length'] > (1024 * 1024)) {
                $table_info[1][$key]['data_length'] = round(($value['data_length'] / (1024 * 1024)), 1).'M';
            } elseif ($value['data_length'] > 1024) {
                $table_info[1][$key]['data_length'] = round(($value['data_length'] / (1024)), 1).'K';
            } elseif ($value['data_length'] > 0) {
                $table_info[1][$key]['data_length'] = round(($value['data_length'] / (1024)), 2).'K';
            } else {
                $table_info[1][$key]['data_length'] = 'zero';
            }

            if ($value['index_length'] > (1024 * 1024 * 1024)) {
                $table_info[1][$key]['index_length'] = round(($value['index_length'] / (1024 * 1024 * 1024)), 1).'G';
            } elseif ($value['index_length'] > (1024 * 1024)) {
                $table_info[1][$key]['index_length'] = round(($value['index_length'] / (1024 * 1024)), 1).'M';
            } elseif ($value['index_length'] > 1024) {
                $table_info[1][$key]['index_length'] = round(($value['index_length'] / (1024)), 1).'K';
            } elseif ($value['index_length'] > 0) {
                $table_info[1][$key]['index_length'] = round(($value['index_length'] / (1024)), 2).'K';
            } else {
                $table_info[1][$key]['index_length'] = 'zero';
            }
        }
        unset($key, $value);
    } else {
        $err = $table_info[1];
    }
  ?>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Table</th>";
      echo "\t\t<th>Engine</th>";
      echo "\t\t<th>Row Format</th>";
      echo "\t\t<th>Rows</th>";
      echo "\t\t<th>Data</th>";
      echo "\t\t<th>Indicies</th>";
      echo "\t\t<th>Collation</th>";
      echo "\t\t<th>Comment</th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if (strlen($err) > 0) {
          echo "<tfoot>\n";
          echo "\t<tr>\n";
          echo "\t\t<td colspan=\"8\" class=\"error\">".$err."</td>\n";
          echo "\t</tr>\n";
          echo "</tfoot>\n";
      } else {
          echo "<tbody>\n";
          foreach ($table_info[1] as $key => $value) {
              echo "\t<tr>\n";
              echo "\t\t<td>".$value['table_name']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['engine']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['row_format']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['table_rows']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['data_length']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['index_length']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['table_collation']."</td>\n";
              echo "\t\t<td class=\"center\">".$value['table_comment']."</td>\n";
              echo "\t</tr>\n";
          }
          unset($key, $value);
          echo "</tbody>\n";
      }
    ?>
  </table>
</div>
<?php unset($db_info, $err);?>