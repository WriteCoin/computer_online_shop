<?php
  require __DIR__ . '/header.php';
  require 'connect.php';

  $logged_user = $_SESSION['logged_user'];

  $query_to_client = pg_query_params($conn, 'SELECT * FROM clients WHERE person_id = $1', Array($logged_user->id));
  $client = pg_fetch_object($query_to_client);

  unset($_SESSION['logged_user']);

  if ($client) {
    $query_delete_client = pg_query_params($conn, 'DELETE FROM clients WHERE person_id = $1', Array($logged_user->id));
  }

  $_SESSION['op_message'] = "Пользователь удален.";

  header('Location: index.php');

  require __DIR__ . '/footer.php';
?>