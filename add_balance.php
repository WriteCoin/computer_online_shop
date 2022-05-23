<?php
  require 'connect.php';

  if (!isset($client)) {
    die('Неверный запрос');
  }

  if (!isset($_POST['new_balance'])) {
    die('Пополнять баланс так просто еще никто не мог');
  }

  if (!isset($client)) {
    die('Неверный запрос');
  }

  $new_balance = money_to_num($client->balance) + $_POST['new_balance'];

  $query_update_balance = pg_query_params($conn, 'UPDATE clients SET balance = $1 WHERE id = $2', Array($new_balance, $client->id));

  $loc = "Location: " . ($_SERVER['HTTP_REFERER'] != null ? $_SERVER["HTTP_REFERER"] : "index.php");
  $_SESSION['op_message'] = "Баланс пополен";
  header($loc);
?>