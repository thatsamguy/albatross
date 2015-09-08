<?php
if (array_key_exists('add', $_POST) and $_POST['add'] == 'Add email') {
    if ($_POST['user'] == '') {
        $errormsg = 'the email address must have a value';
    } elseif (strlen($_POST['passwd']) < 3) {
        $errormsg = 'email passwords must be at least three (3) characters long';
    } elseif (in_array(strtolower($_POST['domain']), $domains[1])) {
        $email_address = $_POST['user'].'@'.strtolower($_POST['domain']);
        $result = $email->add_email($email_address, $_POST['passwd'], $_POST['name']);
        if (!$result[0]) {
            $errormsg = $result[1];
        } else {
            header('Location: /email');
        }
    } else {
        $errormsg = 'Email addresses can only be added for domains that you control';
    }
}
if (!$domains[0]) {
    $errormsg = 'no domains are connected to this account number';
}
?>
<div class="block midwidth">
  <h3>Add email</h3>
  <form action="/email/addemail" method="post">
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
	<th>Email Address</th>
	<td><input class="text" size="15" type="textbox" name="user">&nbsp;@
	  <select name="domain">
	    <?php foreach ($domains[1] as $item) {
    echo "\t\t<option value=\"".$item.'">'.$item."</option>\n";
} ?>
	  </select>
	</td>
      </tr>
      <tr>
	<th>Name</th>
	<td><input class="text" size="25" type="textbox" name="name"></td>
      </tr>
      <tr>
	<th>Password</th>
	<td><input class="text" size="25" type="textbox" name="passwd"></td>
      </tr>
      <tr>
	<td>Example</td>
	<td>firstname.surname@cyprix.com.au<br/>Real Name</td>
      </tr>
      <tr>
	<th colspan="2" class="center"><input type="submit" name="add" value="Add email"></th>
      </tr>
    </tbody>
  </table>
  </form>
</div>