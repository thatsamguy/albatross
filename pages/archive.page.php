<div class="block">
  <?php
    $err = "";
    $site_data = $site->status($auth->acc_id);
    if(!$site_data[0]){
      $err = $site_data[1];
    }
  ?>
  <h3>Create a site backup</h3>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Site</th>";
      echo "\t\t<th>Backup</th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if(strlen($err)>0){
	echo "<tfoot>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan=\"2\" class=\"error\">".$err."</td>\n";
	echo "\t</tr>\n";
	echo "</tfoot>\n";
      }else{
	echo "<tfoot>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan=\"2\">Backups will occur immediately and be accessible in the archive directory via FTP.</td>\n";
	echo "\t</tr>\n";
	echo "</tfoot>\n";
	echo "<tbody>\n";
	foreach($site_data[1] as $site_title=>$info){
	  if($site_title!="__totalsize__" AND $site_title!='__totalbandwidth__'){
	    echo "\t<tr>\n";
	    echo "\t\t<th>".$site_title."</th>\n";
	    echo "\t\t<td class=\"center\"><a href=\"/archive/createbackup/".$site_title."\">Backup now</a></td>\n";
	    echo "\t</tr>\n";
	  }
	}
	unset($site_title,$info);
	echo "</tbody>\n";
      }
    ?>
  </table>
</div>
<?php unset($site_data,$err);?>