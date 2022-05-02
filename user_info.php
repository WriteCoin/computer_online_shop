<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Информация о пользователе</title>
  <link href="styles.css" rel="stylesheet" />
  <style>
    .phpOutput {
      margin-bottom: 10px;
      line-height: 1.5;
    }
  </style>
</head>

<?php
  $value_default = "undefined";
  function post_get($arg) {
    global $value_default;
    $value = $_POST[$arg];
    if (isset($value) && $value != "" && gettype($value) == "string") {
      return $value;
    } else {
      return $value_default;
    }
  }

  $name = post_get("name");
  $surname = post_get("surname");
  $fathername = post_get("fathername");
  $login = post_get("login");
  $password = post_get("password");
?>

<body>
  <div class="container">
    <div class="layer">
      <div class="phpOutput">
        <?php
          if ($login == $value_default || $password == $value_default) {
            echo "Вы не вошли в систему";
          } else {
        ?>
          <table class="table">
            <tr>
              <td><b>Имя:</b></td>
              <td><?=htmlspecialchars($name)?></td>
            </tr>
            <tr>
              <td><b>Фамилия:</b></td>
              <td><?=htmlspecialchars($surname)?></td>
            </tr>
            <tr>
              <td><b>Отчество:</b></td>
              <td><?=htmlspecialchars($fathername)?></td>
            </tr>
          </table>
        <?php
          }
        ?>
      </div>
      <br>
      <button class="btn" onclick="history.back();">Назад</button>
    </div>
  </div>
</body>
</html>