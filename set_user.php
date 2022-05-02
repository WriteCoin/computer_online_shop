<?php
  if (isset($_SESSION['logged_user'])) {
    $logged_user = $_SESSION['logged_user'];
    if (isset($_SESSION['user_role'])) {
      $logged_user_role = $_SESSION['user_role'];
      if ($logged_user_role == 'client') {
        $user_role_name = 'Клиент';
        $client_query = pg_query_params($conn, 'SELECT * FROM clients WHERE person_id = $1', Array($logged_user->id));
        $client = pg_fetch_object($client_query);
        // if (isset($data['do_add_product']) || isset($data['do_edit_products'])) {
        //   die('У Вас нет прав для просмотора этой страницы');
        // }
      } elseif ($logged_user_role == 'moderator') {
        $user_role_name = 'Модератор';
        $moderator_query = pg_query_params($conn, 'SELECT * FROM moderators WHERE person_id = $1', Array($logged_user->id));
        $moderator = pg_fetch_object($moderator_query);
      } elseif ($logged_user_role == 'operator') {
        $user_role_name = 'Оператор';
        $operator_qeury = pg_query_params($conn, 'SELECT * FROM operators WHERE person_id = $1', Array($logged_user->id));
        $operator = pg_fetch_object($operator_qeury);
      }
    }
  }
?>