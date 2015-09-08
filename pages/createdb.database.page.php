<?php
if (array_key_exists('add', $_POST) and $_POST['add'] == 'Create database') {
    if ($_POST['db_name'] == '') {
        $errormsg = 'the database name must have a value';
    } elseif (strlen($_POST['passwd']) < 3) {
        $errormsg = 'database passwords must be at least three (3) characters long';
    } else {
        $result = $database->add($_POST['db_name'], $auth->acc_id, $_POST['passwd']);
        if (!$result[0]) {
            $errormsg = $result[1];
        } else {
            header('Location: /database');
        }
    }
}
?>
<div class="block midwidth">
  <h3>New database</h3>
  <form action="/database/createdb" method="post">
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
	<th>Database Name</th>
	<td><?php echo $auth->acc_id;?>_<input class="text" size="15" type="textbox" name="db_name"></td>
      </tr>
      <tr>
	<th>User Account</th>
	<td>z<?php echo $auth->acc_id;?></td>
      </tr>
      <tr>
	<th>Password</th>
	<td><input class="text" size="25" type="password" name="passwd"></td>
      </tr>
      <tr>
	<td>Example</td>
	<td>100001_testdb</td>
      </tr>
      <tr>
	<th colspan="2" class="center"><input type="submit" name="add" value="Create database"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>