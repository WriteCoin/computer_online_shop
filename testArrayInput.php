<?php
  if (isset($_POST['abc'])) {
    echo '<br>' . $_POST['abc'] . '<br>';
  }
  if (isset($_POST['abd'])) {
    echo '<br>' . $_POST['abd'] . '<br>';
  }
  if (isset($_POST['users'])) {
    print_r($_POST['users']);
  }
  $users['abc'] = 1;
  $users['abd'] = '001';
?>
<form method="post">
  <input type="hidden" name="users" value="<?= array_shift($users) ?>">
  <button type="submit">Отправить</button>
</form>


