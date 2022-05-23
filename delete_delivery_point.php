<?php
  require 'connect.php';

  if (!isset($operator)) {
    die('Неверный запрос');
  }

  if (!isset($_POST['id'])) {
    die('POST-запросы не определены');
  }

  $id = $_POST['id'];

  $query_new_point_of_issue = pg_query_params($conn, 'DELETE FROM points_of_issue WHERE id = $1', Array($id));

  $_SESSION['op_message'] = 'Пункт выдачи удален';

  header('Location: delivery_points.php');
?>