<?php
$typearray = array('A','AAAA','CNAME','TXT');

if ($origpage == 'addalias') {
    $typearray = array('CNAME');
}
if ($origpage == 'addaddress') {
    $typearray = array('A');
}

if (array_key_exists('add', $_POST) or array_key_exists('edit', $_POST) or $uri[3] == 'remove') {
    goto processing;
}

if ($origpage == 'editrecord') {
    goto postprocessing2;
} else {
    goto postprocessing;
}

processing:
$domains = $dns->get_domains_for_acc_id($auth->acc_id);
if (!$domains[0]) {
    $error = $domains[1];
    goto postprocessing;
}

if ($_POST['add'] == 'Add record') {
    if (!in_array($_POST['type'], $typearray)) {
        $errmsg = "Unable to add record with type '".$record['type']."'";
        goto postprocessing2;
    } elseif (!in_array($domain, $domains[1])) {
        $errmsg = "Unable to alter domain '".$_POST['domain']."' due to lack of permission";
        goto postprocessing;
    } else {
        $result = $dns->add_record($domain_id, $_POST['name'], $_POST['type'], $_POST['content'], '');
        if (!$result[0]) {
            $errormsg = $result[1];
            goto postprocessing;
        } else {
            $errormsg = 'new record added successfully. <a href="/dns/domain/'.$domain_id.'">View Records</a>';
        }
    }
}

if ($_POST['edit'] == 'Edit record') {
    $record_id = $uri[4];

    $record_domain_id = $dns->get_record_domain_id($record_id);
    if (!$record_domain_id[0]) {
        $errormsg = $record_domain_id[1];
        goto postprocessing2;
    } else {
        $record_domain_id = $record_domain_id[1];
    }

    $records = $dns->get_records_from_domain($record_domain_id);
    if (!$records[0]) {
        $errormsg = $records[1];
        goto postpostprocessing2;
    }

    $record = $records[1][$record_id];

    if (!in_array($record['type'], $typearray)) {
        $errmsg = "Unable to alter record with type '".$record['type']."'";
        unset($record, $records, $record_domain_id);
        goto postprocessing2;
    }

    unset($record, $records);
    if (!in_array($record_domain_id, array_keys($domains[1]))) {
        $errmsg = 'Unable to alter record due to lack of permission';
        goto postprocessing2;
    } else {
        unset($result);
        $result = $dns->update_record($record_id, $_POST['content']);
        if (!$result[0]) {
            $errormsg = $result[1];
            goto postprocessing2;
        } else {
            $errormsg = 'record edited successfully. <a href="/dns/domain/'.$domain_id.'">View Records</a>';
            goto postprocessing2;
        }
    }
}

if ($uri[3] == 'remove') {
    $record_id = $uri[4];

    $record_domain_id = $dns->get_record_domain_id($record_id);
    if (!$record_domain_id[0]) {
        $errormsg = $record_domain_id[1];
        goto postprocessing3;
    } else {
        $record_domain_id = $record_domain_id[1];
    }

    $records = $dns->get_records_from_domain($record_domain_id);
    if (!$records[0]) {
        $errormsg = $records[1];
        goto postprocessing3;
    }

    $record = $records[1][$record_id];

    if (!in_array($record['type'], $typearray)) {
        $errmsg = "Unable to alter record with type '".$record['type']."'";
        unset($record, $records, $record_domain_id);
        goto postprocessing3;
    }

    unset($record, $records);
    if (!in_array($record_domain_id, array_keys($domains[1]))) {
        $errmsg = 'Unable to alter record due to lack of permission';
        goto postprocessing3;
    } else {
        unset($result);
        $result = $dns->remove_record($record_id, $_POST['content']);
        if (!$result[0]) {
            $errormsg = $result[1];
            goto postprocessing3;
        } else {
            header('Location: /dns/domain/'.$domain_id);
        }
    }
}

postprocessing:
?>
<div class="block doublewidth">
  <h3>Add new dns record<?php if ($domain_id > 0) {
    echo ' for '.$domain;
}?></h3>
  <form action="/dns/<?php echo $origpage;?>/<?php echo $domain_id;?>" method="post">
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
} ?>
    <tbody>
      <tr>
	<th>Name</th>
	<th>Type</th>
	<th>Content</th>
      </tr>
      <tr>
	<td><input class="text" size="20" type="textbox" name="name"></td>
	<td>
	  <select name="type">
	    <?php foreach ($typearray as $item) {
    echo "\t\t<option value=\"".$item.'">'.$item."</option>\n";
} ?>
	  </select>
	</td>
	<td><input class="text" size="20" type="textbox" name="content"></td>
      </tr>
      <tr>
	<td colspan="3">Examples</td>
      </tr>
      <?php if ($origpage == 'addrecord' or $origpage == 'addaddress') {
    ?>
      <tr>
	<td>cyprix.com.au</td>
	<td>A</td>
	<td colspacomn="2">111.223.225.162</td>
      </tr>
      <?php 
}
        if ($origpage == 'addrecord' or $origpage == 'addalias') {
            ?>
      <tr>
	<td>www.cyprix.com.au</td>
	<td>CNAME</td>
	<td>cyprix.com.au</td>
      </tr>
      <?php 
        } ?>
      <tr>
	<th colspan="3" class="center"><input type="submit" name="add" value="Add record"></th>
      </tr>
      <?php if ($origpage == 'addrecord') {
    ?>
      <tr>
	<td><a href="/dns/server/<?php echo $domain_id;
    ?>">Add NS record</a></td>
	<td colspan="2"><a href="/dns/email/<?php echo $domain_id;
    ?>">Add MX record</a></td>
      </tr>
      <?php 
} ?>
    </tbody>
  </table>
  </form>
</div>
<?php goto end;?>
<?php
postprocessing2:
$record_id = $uri[4];

$domains = $dns->get_domains_for_acc_id($auth->acc_id);
if (!$domains[0]) {
    $errormsg = $domains[1];
    goto postpostprocessing2;
}

$record_domain_id = $dns->get_record_domain_id($record_id);
if (!$record_domain_id[0]) {
    $errormsg = $record_domain_id[1];
    goto postpostprocessing2;
} else {
    $record_domain_id = $record_domain_id[1];
}

if (!in_array($record_domain_id, array_keys($domains[1]))) {
    $errmsg = 'Unable to view record due to lack of permission';
    goto postpostprocessing2;
}

$records = $dns->get_records_from_domain($record_domain_id);
if (!$records[0]) {
    $errormsg = $records[1];
    goto postpostprocessing2;
}

$record = $records[1][$record_id];
unset($domains, $record_domain_id, $records);

postpostprocessing2:
?>
<div class="block doublewidth">
  <h3>Edit record<?php if ($domain_id > 0) {
    echo ' for '.$domain;
}?></h3>
  <form action="/dns/<?php echo $origpage;?>/<?php echo $domain_id;?>/record/<?php echo $record_id;?>" method="post">
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
} ?>
    <tbody>
      <tr>
	<th>Name</th>
	<th>Type</th>
	<th>Content</th>
      </tr>
      <tr>
	<td><?php echo $record['name'];?></td>
	<td><?php echo $record['type'];?></td>
	<td><input class="text" size="20" type="textbox" name="content" value="<?php echo $record['content'];?>"></td>
      </tr>
      <tr>
	<th colspan="3" class="center"><input type="submit" name="edit" value="Edit record"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>
<?php goto end;?>
<?php
postprocessing3:
?>
<div class="block">
<p class="error">Unable to remove record - <?php echo $errormsg;?></p>
<a href="/dns/domain/<?php echo $domain_id;?>">View Records</a>
</div>
<?php end: ?>