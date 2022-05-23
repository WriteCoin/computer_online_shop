<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title; ?></title>
  <!-- <style type="text/css" media="all">
		@import url("styles.css");
	</style> -->
  <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

<?php
  function site_message() {
    if (isset($_SESSION['op_message'])) {
      echo '<div style="color: white; ">' . $_SESSION['op_message'] . '</div><hr>';
      unset($_SESSION['op_message']);
    } else if (isset($_SESSION['op_message_error'])) {
      echo '<div style="color: red; ">' . $_SESSION['op_message_error'] . '</div><hr>';
      unset($_SESSION['op_message_error']);
    }
  }
?>