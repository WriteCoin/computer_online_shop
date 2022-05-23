<?php
  if (isset($_POST['back-url'])) {
    $loc = "Location: " . $_POST['back-url'];
  } else {
    $loc = "Location: " . ($_SERVER['HTTP_REFERER'] != null ? $_SERVER["HTTP_REFERER"] : "index.php");
  }
  header($loc);
?>