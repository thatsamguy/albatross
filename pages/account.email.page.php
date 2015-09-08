<?php error_log(__FILE__);?>
<?php
$alias_error = '';

if (array_key_exists('update', $_POST) and $_POST['update'] == 'Update') {
    if (strlen($_POST['passwd1']) > 0 or strlen($_POST['passwd2']) > 0) {
        if ($_POST['passwd1'] == $_POST['passwd2']) {
            $result = $email->update_password($email_acc['email'], $_POST['passwd1']);
            if (!$result[0]) {
                $errormsg = $result[1].'&nbsp;&nbsp;&nbsp;';
            } else {
                $errormsg = 'password updated.&nbsp;&nbsp;&nbsp;';
            }
            unset($result);
        } else {
            $errormsg = 'passwords must be the same';
        }
    }
    if (strlen($_POST['name']) > 0) {
        $result = $email->update_email_name($email_acc['email'], $_POST['name']);
        if (!$result[0]) {
            $errormsg .= $result[1];
        } else {
            header('Location: /email/account/'.$email_acc['email']);
        }
        unset($result);
    }
}

if ($uri[3] == 'activate') {
    $result = $email->activate_email($email_acc['email']);
    if ($result[0]) {
        header('Location: /email/account/'.$email_acc['email']);
    }
    unset($result);
} elseif ($uri[3] == 'deactivate') {
    $result = $email->deactivate_email($email_acc['email']);
    if ($result[0]) {
        header('Location: /email/account/'.$email_acc['email']);
    }
    unset($result);
}
?>
<div class="block">
  <h3><?php echo $email_acc['email'];?></h3>
  <?php
    $aliases = $email->get_alias_by_destination($email_acc['email']);
    if (!$aliases[0]) {
        $alias_error = $aliases[1];
    }
  ?>
  <table>
  <?php
    echo "<tbody>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>Email Address</th>";
    echo "\t\t<td>".$email_acc['email'].'</td>';
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>Account Name</th>";
    echo "\t\t<td>".$email_acc['name'].'</td>';
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>Domain</th>";
    echo "\t\t<td>".$email_acc['domain'].'</td>';
    echo "\t</tr>\n";
    $humansize = $email->email_size($auth->acc_id, $email_acc['email'], false);
    if (!$humansize[0]) {
        $humansize = 0;
    } else {
        $humansize = $humansize[1];
    }
    echo "\t<tr>\n";
    echo "\t\t<th>Mailbox Size</th>";
    echo "\t\t<td>".$humansize.'</td>';
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<th>Status</th>";
    echo "\t\t<td>".$email_acc['active'].'</td>';
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    if ($email_acc['active'] == 'active') {
        echo "\t\t<td class=\"center\" colspan='2'><a href=\"/email/account/".$email_acc['email'].'/deactivate">Deactivate email account</a></td>';
    } else {
        echo "\t\t<td class=\"center\" colspan='2'><a href=\"/email/account/".$email_acc['email'].'/activate">Activate email account</a></td>';
    }
    echo "\t</tr>\n";
    echo "\t<tr>\n";
    echo "\t\t<td class=\"center\" colspan='2'><a href=\"/email/removeaccount/".$email_acc['email'].'">Remove email account</a></td>';
    echo "\t</tr>\n";
    echo "</tbody>\n";
  ?>
  </table>
</div>
<div class="block">
<h3>Update details</h3>
<form action="/email/account/<?php echo $email_acc['email'];?>" method="post">
  <table>
    <?php if (strlen($errormsg) > 0) {
    ?>
    <tfoot>
      <tr>
	<td colspan="2" class="error"><?php echo $errormsg;
    ?></td>
      </tr>
    </tfoot>
    <?php 
} ?>
    <tbody>
      <tr>
	<th>Account Name</th>
	<td><input class="text" size="25" type="textbox" name="name"></td>
      </tr>
      <tr>
	<th>New Password</th>
	<td><input class="text" size="25" type="password" name="passwd1"></td>
      </tr>
      <tr>
	<th>Confirm Password</th>
	<td><input class="text" size="25" type="password" name="passwd2"></td>
      </tr>
      <tr>
	<th colspan="2" class="center"><input type="submit" name="update" value="Update"></th>
      </tr>
    </tbody>
  </table>
</form>
</div>
<div class="block">
<h3>Aliases for <?php echo $email_acc['email'];?></h3>
<table>
<?php
  echo "<thead>\n";
  echo "\t<tr>\n";
  echo "\t\t<th colspan=\"2\">Aliases</th>";
  echo "\t</tr>\n";
  echo "</thead>\n";
  if (strlen($alias_error) > 0) {
      echo "<tfoot>\n";
      echo "\t<tr>\n";
      echo "\t\t<td colspan=\"2\" class=\"error\">".$alias_error."</td>\n";
      echo "\t</tr>\n";
      echo "</tfoot>\n";
  } else {
      echo "<tbody>\n";
      foreach ($aliases[1] as $alias) {
          echo "\t<tr>\n";
          echo "\t\t<td>".$alias['alias']."</td>\n";
          if ($alias['active'] == 'active') {
              echo "\t\t<td class=\"center\"><a href=\"/email/alias/deactivate/".$alias['alias']."\">Deactivate</a></td>\n";
          } elseif ($alias['active'] == 'inactive') {
              echo "\t\t<td class=\"center\"><a href=\"/email/alias/activate/".$alias['alias']."\">Activate</a></td>\n";
          }
          echo "\t\t<td><a href=\"/email/removealias/".$alias['alias']."\">Remove</a></td>\n";
          echo "\t</tr>\n";
      }
      unset($alias);
      echo "</tbody>\n";
  }
?>
</table>
</div>
