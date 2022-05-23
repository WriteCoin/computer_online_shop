<?php
  $remove_from_basket = function($product_id) use ($client, $conn) {
    $query = pg_query_params($conn, 'DELETE FROM client_products WHERE client_id = $1 and product_id = $2', Array($client->id, $product_id));
    $_SESSION['op_message'] = 'Товар удален из корзины';
  }
?>