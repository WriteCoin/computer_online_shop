<?php
  require 'connect.php';

  $data = $_POST;

  echo $client->id;

  $basket_query = pg_query_params($conn, 'SELECT * FROM baskets WHERE client_id = $1', Array($client->id));
  $basket = pg_fetch_object($basket_query);

  if (isset($data['id'])) {
    $product_id = $data['id'];
    $new_product_in_basket_query = pg_query_params($conn, 'INSERT INTO products_in_baskets(basket_id, product_id) VALUES ($1, $2)', Array($basket->id, $product_id));

    $_SESSION['op_message'] = 'Товар добавлен в корзину.';
  } else {
    $_SESSION['op_message_error'] = 'Нелепый сбой: id товара не найден.';
  }

  // header('Location: index.php');
?>