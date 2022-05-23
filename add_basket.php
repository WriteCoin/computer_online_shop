<?php
  require 'connect.php';
  require 'api_remove_basket.php';

  if (!isset($client)) {
    die('Неверный запрос');
  }

  $data = $_POST;

  if (isset($data['id'])) {
    $product_id = $data['id'];

    if (isset($data['to_remove_basket'])) {
      $remove_from_basket($product_id);
    } else {
      $quantity = $get_post('quantity', 1);

      $new_product_in_basket_query = pg_query_params($conn, 'INSERT INTO client_products(client_id, product_id, quantity) VALUES ($1, $2, $3)', Array($client->id, $product_id, $quantity));

      $_SESSION['op_message'] = 'Товар добавлен в корзину.';
    }
  } else {
    $_SESSION['op_message_error'] = 'Нелепый сбой: id товара не найден.';
  }

  header('Location: index.php');
?>