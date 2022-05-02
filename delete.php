<?php
  require __DIR__ . '/header.php';
  require 'connect.php';

  $logged_user = $_SESSION['logged_user'];

  $query_to_client = pg_query_params($conn, 'SELECT * FROM clients WHERE person_id = $1', Array($logged_user->id));
  $client = pg_fetch_object($query_to_client);

  // $query_to_moderator = pg_query_params($conn, 'SELECT * FROM moderators WHERE person_id = $1', Array($logged_user->id));
  // $moderator = pg_fetch_object($query_to_moderator);

  // $query_to_operator = pg_query_params($conn, 'SELECT * FROM operators WHERE person_id = $1', Array($logged_user->id));
  // $operator = pg_fetch_object($query_to_operator);

  unset($_SESSION['logged_user']);

  if ($client) {
    $query_delete_client = pg_query_params($conn, 'DELETE FROM clients WHERE person_id = $1', Array($logged_user->id));
  }
  // if ($moderator) {
  //   $query_delete_moderator = pg_query_params($conn, 'DELETE FROM moderators WHERE person_id = $1', Array($logged_user->id));
  // }
  // if ($operator) {
  //   $query_delete_operator = pg_query_params($conn, 'DELETE FROM operators WHERE person_id = $1', Array($logged_user->id));
  // }

  // $query_delete_user = pg_query_params($conn, 'DELETE FROM person WHERE id = $1', Array($logged_user->id));

  $_SESSION['op_message'] = "Пользователь удален.";

  header('Location: index.php');

  require __DIR__ . '/footer.php';
?>