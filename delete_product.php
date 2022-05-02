<?php
  require 'connect.php';

  $data = $_POST;

  if (isset($data['id'])) {
    $product_id = $data['id'];

    $query_delete_properties = pg_query_params($conn, 'DELETE FROM properties WHERE product_id = $1', Array($product_id));
    $query_delete_refund = pg_query_params($conn, 'DELETE FROM refunds WHERE product_id = $1',  Array($product_id));
    $query_delete_products_in_orders = pg_query_params($conn, 'DELETE FROM products_in_baskets WHERE product_id = $1', Array($product_id));
    $query_delete_products_in_baskets = pg_query_params($conn, 'DELETE FROM products_in_orders WHERE product_id = $1', Array($product_id));

    $query_delete_product = pg_query_params($conn, 'DELETE FROM products WHERE id = $1', Array($product_id));

    $_SESSION['op_message'] = 'Товар удален.';
  } else {
    $_SESSION['op_message_error'] = 'Нелепый сбой: id товара не найден.';
  }

  header('Location: index.php');
?>