<div class="block">
  <table>
    <?php
      $soa = $dns->get_domain_soa($domain_id);
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t<th colspan=\"2\">SOA</th>\n";
      echo "\t</tr>\n";
      echo "</thead>\n";
      echo "<tfoot>\n";
      echo "\t<tr>\n";
      echo "\t<th colspan=\"2\"><a href=\"/dns/soa/".$domain_id."\">Edit SOA</a></th>\n";
      echo "\t</tr>\n";
      echo "</tfoot>\n";
      echo "<tbody>\n";
      if($soa[0]){
	foreach($soa[1] as $key=>$value){
	  echo "\t<tr>\n";
	  echo "\t\t<th>".$key."</th>\n";
	  echo "\t\t<td>".$value."</td>\n";
	  echo "\t</tr>\n";
	}
	unset($key,$value);
      }else{
	echo "\t<tr>\n";
	echo "\t\t<th colspan=\"2\" class=\"error=\">".$soa[1]."</th>\n";
	echo "\t</tr>\n";
      }
      unset($soa);
    ?>
    </tbody>
  </table>
</div>
<div class="block doublewidth">
  <table>
    <?php
      $records = $dns->get_records_from_domain($domain_id);
      echo "<thead>\n";
      echo "\t<tr>\n";
      echo "\t<th colspan=\"6\">Records</th>\n";
      echo "\t</tr>\n";
      echo "</thead>\n";
      echo "<tbody>\n";
      echo "\t<tr>\n";
      echo "\t\t<th>Name</th>\n";
      echo "\t\t<th>Type</th>\n";
      echo "\t\t<th>Content</th>\n";
      echo "\t\t<th>Priority</th>\n";
      echo "\t\t<th></th>\n";
      echo "\t<tr>\n";
      if($records[0]){
	foreach($records[1] as $key=>$record){
	  if($record['type']!="SOA"){
	    echo "\t<tr>\n";
	    echo "\t\t<td>".$record['name']."</td>\n";
	    echo "\t\t<td>".$record['type']."</td>\n";
	    echo "\t\t<td>".$record['content']."</td>\n";
	    if($record['type'] != "MX"){ $record['priority'] = ""; }
	    echo "\t\t<td>".$record['priority']."</td>\n";
	    if($record['type'] == "NS"){
	      echo "\t\t<td class=\"center\"><a href=\"/dns/server/".$domain_id."\">Edit</a></td>\n";
	    }elseif($record['type'] == "MX"){
	      echo "\t\t<td class=\"center\"><a href=\"/dns/email/".$domain_id."\">Edit</a></td>\n";
	    }else{
	      echo "\t\t<td class=\"center\"><a href=\"/dns/editrecord/".$domain_id."/record/".$key."\">Edit</a></td>\n";
	    }
	    if($record['type'] != "MX" AND $record['type'] != "NS"){
	      echo "\t\t<td class=\"center\"><a href=\"/dns/record/".$domain_id."/remove/".$key."\">Remove</a></td>\n";
	    }
	    echo "\t</tr>\n";
	  }
	}
	unset($record,$key);
      }else{
	echo "\t<tr>\n";
	echo "\t\t<th colspan=\"6\" class=\"error=\">".$records[1]."</th>\n";
	echo "\t</tr>\n";
      }
      unset($records);
    ?>
    </tbody>
  </table>
</div>