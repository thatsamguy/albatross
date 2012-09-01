<?php $pt[]['sites.page.php :: start'] = microtime(true); ?>
<div class="block doublewidth">
  <h3>Sites</h3>
  <?php
    $err = "";
    $site_data = $site->status($auth->acc_id);
    if(!$site_data[0]){
      $err = $site_data[1];
    }
  ?>
  <?php $pt[]['sites.page.php :: site->status'] = microtime(true); ?>
  <table>
    <?php
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Site</th>";
      echo "\t\t<th>Primary Domain</th>";
      echo "\t\t<th>Configuration Profile</th>";
      echo "\t\t<th>PHP</th>";
      echo "\t\t<th>Size</th>";
      echo "\t</tr>\n";
      echo "</thead>\n";
      if(strlen($err)>0){
	echo "<tfoot>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan=\"5\" class=\"error\">".$err."</td>\n";
	echo "\t</tr>\n";
	echo "</tfoot>\n";
      }else{
	echo "<tfoot>\n";
	echo "\t<tr>\n";
	echo "\t\t<th colspan=\"3\"></th>\n";
	echo "\t\t<th>Total</th>\n";
	echo "\t\t<th>".round(($site_data[1]['__totalsize__'])/1024,1)."M</th>\n";
	echo "\t</tr>\n";
	echo "</tfoot>\n";
	echo "<tbody>\n";
	$pt[]['sites.page.php :: table setup'] = microtime(true);
	foreach($site_data[1] as $site_name=>$value){
	  if($site_name!="__totalsize__" AND $site_name!="__totalbandwidth__" ){
	    if($value['phpenabled'] == "1"){ $value['phpenabled'] = "Enabled"; }else{ $value['phpenabled'] = ""; }
	    echo "\t<tr>\n";
	    echo "\t\t<th><a href=\"/sites/view/".$site_name."\">".$site_name."</a></th>\n";
	    echo "\t\t<td class=\"center\">".$value['defaultdomain']."</td>\n";	  
	    echo "\t\t<td class=\"center\">".$value['profile']."</td>\n";
	    echo "\t\t<td class=\"center\">".$value['phpenabled']."</td>\n";
	    echo "\t\t<td class=\"center\">".$value['size']."</td>\n";
	    echo "\t</tr>\n";
	  }
	}
	unset($site_name,$value);
	$pt[]['sites.page.php :: table data'] = microtime(true);
	echo "</tbody>\n";
      }
    ?>
  </table>
</div>
<?php unset($site_data,$err);?>
<?php $pt[]['sites.page.php :: end'] = microtime(true); ?>