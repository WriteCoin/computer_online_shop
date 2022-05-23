<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>img</title>
</head>

<?php
  require 'connect.php';
?>

<body>
  <form action="image.php" method="post" enctype="multipart/form-data">
    <p>Загрузить картинку</p>
    <input type="file" name="img_upload"><input type="submit" name="upload" value="Загрузить">

    <?php

      if (isset($_POST['upload'])) {
        // echo $_POST['upload'];
        $file = $_FILES['img_upload'];
        if (!empty($file['tmp_name'])) {
          $img = pg_escape_bytea(file_get_contents($file['tmp_name']));
        }
        pg_query_params($conn, 'INSERT INTO images (img) VALUES ($1)', Array(1));
      }

    ?>
    
  </form>

  <?php
      $query = pg_query($conn, "SELECT encode(\"img\", 'base64') AS img FROM images ORDER BY id DESC");
      while ($row = pg_fetch_object($query)) {
        // $show_img = base64_encode($row->img); 
        // echo $show_img; 
        // $show_img = $row->img; 
        $show_img = hex2bin(pg_unescape_bytea(substr($row->img, 2)));
        $show_img = base64_encode($show_img);
        // $show_img = hex2bin($row->img);
        // $show_img = pg_unescape_bytea(base64_encode($row->img));
        ?>
        <img src="data:image/jpeg;base64, <?php echo $show_img; ?>" alt="">
        <!-- <img src="<?= $show_img ?>" alt=""> -->
    <?php } ?>
</body>
</html>