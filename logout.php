<?php
  require __DIR__ . '/header.php';
  require 'connect.php';

  unset($_SESSION['logged_user']);

  header('Location: index.php');

  require __DIR__ . '/footer.php';
?>