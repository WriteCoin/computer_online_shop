<?php
  require 'connect.php';
  require 'api_remove_basket.php';

  if (isset($_POST['id'])) {
    $remove_from_basket($_POST['id']);
    $_SESSION['op_message'] = 'Товар удален из корзины.';
  } else {
    $_SESSION['op_message_error'] = 'Нелепый сбой: id товара не найден.';
  }

  header('Location: basket_view.php');
?>