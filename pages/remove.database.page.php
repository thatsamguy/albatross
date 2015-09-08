<?php
if (array_key_exists('remove', $_POST) and $_POST['remove'] == 'Confirm remove database') {
    $result = $database->remove($db_name);
    if (!$result[0]) {
        $errormsg = $result[1];
    } else {
        header('Location: /database');
    }
    unset($result);
}
?>
<div class="block">
  <h3>Remove database</h3>
  <form action="/database/remove/<?php echo $db_name;?>" method="post">
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
	<td>Please confirm that you wish to completely remove the database '<?php echo $db_name;?>' from your account.<br/><p class="error">Note: all data from this database will be deleted!</p></td>
      </tr>
      <tr>
	<th class="center"><input type="submit" name="remove" value="Confirm remove database"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>