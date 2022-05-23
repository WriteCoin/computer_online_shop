<?php
  require 'connect.php';

  if (!isset($operator)) {
    die('Неверный запрос');
  }

  if (!isset($_POST['id'])) {
    die('POST-запросы не определены');
  }

  $id = $_POST['id'];
  $name = $_POST['name'];
  $address = $_POST['address'];
  $work_time_start = $_POST['work_time_start'];
  $work_time_start_arr = preg_split('/:/', $work_time_start);
  $work_time_end = $_POST['work_time_end'];
  $work_time_end_arr = preg_split('/:/', $work_time_end);

  echo gettype($name) . ' ' . $name . '<br>';
  echo gettype($address) . ' ' . $address . '<br>';
  echo gettype($work_time_start) . ' ' . $work_time_start . '<br>';
  echo gettype($work_time_end) . ' ' . $work_time_end . '<br>';

  $query_existing = pg_query_params($conn, 'SELECT * FROM points_of_issue WHERE name = $1 AND address = $2 AND id <> $3', Array($name, $address, $id));
  $existing = pg_fetch_object($query_existing);

  // echo $existing->id . ' ' . $existing->name . '<br>';
  // echo pg_num_rows($query_existing) . '<br>';

  if (pg_num_rows($query_existing) > 0) {
    $_SESSION['op_message_error'] = 'Пункт выдачи заказа с такими данными уже существует';
  } elseif ($work_time_end_arr[0] - $work_time_start_arr[0] < 1) {
    $_SESSION['op_message_error'] = 'Часы работы не могут быть менее 1 часа';
  } elseif (!($work_time_start < $work_time_end)) {
    $_SESSION['op_message_error'] = 'Часы работы введены неверно';
  } else {
    $query_new_point_of_issue = pg_query_params($conn, 'UPDATE points_of_issue SET name = $1, address = $2, work_time_start = $3, work_time_end = $4 WHERE id = $5', Array($name, $address, $work_time_start, $work_time_end, $id));

    $_SESSION['op_message'] = 'Пункт выдачи изменен';
  }

  header('Location: delivery_points.php');
?>