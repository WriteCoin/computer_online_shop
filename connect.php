<?php
  require 'connect_info.php';

  // echo extension_loaded('pgsql') ? 'yes':'no';

  function pdo_test($host, $port, $dbname, $user, $password) {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    try {
      $conn = new PDO($dsn);
      if ($conn) {
        echo "Connected to the <strong>$db</strong> database successfully!";
        
        $res = $conn->query("select id, category_name from categories");

        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
          echo($row['id'].'-'.$row['category_name']);
        }
      }
    } catch (PDOException $e) {
      echo "Ошибка подключения:\n";
      echo $e->getMessage();
    }
  }

  $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

  if (!$conn) {
    die('Could not connect');
  }

  require 'insert_db.php';

  session_start();

  require 'set_user.php';
?>