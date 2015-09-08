<?php
if (array_key_exists('remove', $_POST) and $_POST['remove'] == 'Confirm remove domain') {
    $result = $dns->remove_domain($domain_id, $auth->acc_id);
    if (!$result[0]) {
        $errormsg = $result[1];
    } else {
        header('Location: /dns');
    }
}
?>
<div class="block">
  <h3>Add new domain</h3>
  <form action="/dns/removedomain/<?php echo $domain_id;?>" method="post">
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
	<td>Please confirm that you wish to completely remove the domain '<?php echo $domain;?>' from your dns account.</td>
      </tr>
      <tr>
	<th class="center"><input type="submit" name="remove" value="Confirm remove domain"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>
<?php
include 'dns.page.php';
?>