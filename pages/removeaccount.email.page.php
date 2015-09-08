<?php
if (array_key_exists('remove', $_POST) and $_POST['remove'] == 'Confirm remove email account') {
    $result = $email->remove_email($email_acc['email']);
    if (!$result[0]) {
        $errormsg = $result[1];
    } else {
        header('Location: /email');
    }
}
?>
<div class="block">
  <h3>Remove email account</h3>
  <form action="/email/removeaccount/<?php echo $email_acc['email'];?>" method="post">
  <table>
    <?php if (strlen($errormsg) > 0) {
    ?>
    <tfoot>
      <tr>
	<td class="error"><?php echo $errormsg;
    ?></td>
      </tr>
    </tfoot>
    <?php 
} ?>
    <tbody>
      <tr>
	<td>Please confirm that you wish to completely remove the email address '<?php echo $email_acc['email'];?>' from your account.<br>
	<br>This includes all emails and settings that have been stored for this account.</td>
      </tr>
      <tr>
	<th class="center"><input type="submit" name="remove" value="Confirm remove email account"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>