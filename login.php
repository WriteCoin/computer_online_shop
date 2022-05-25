<?php
  $title = "Форма авторизации";
  require __DIR__ . "/header.php";
  require "connect.php";

  $data = $_POST;
  if (isset($data['do_login'])) {
    $errors = array();

    $user_name = $secure_data($data['user_name']);
    $user_password = $secure_data($data['user_password']);
    $user_role = $secure_data($data['user_role']);

    $user_query = pg_query_params($conn, 'SELECT * FROM person WHERE user_name = $1', Array($user_name));
    $user = pg_fetch_object($user_query);

    if (!$user) {
      $errors[] = "Пользователь с таким логином не найден!";
    } else {
      if ($user_role == 'client') {
        $client_query = pg_query_params($conn, 'SELECT * FROM clients WHERE person_id = $1;', Array($user->id));
        $client = pg_fetch_assoc($client_query);
        
        if (!isset($client['id'])) {
          $errors[] = "Клиент не найден";
        }
      }

      if ($user_role == 'moderator') {
        $moderator_query = pg_query_params($conn, 'SELECT * FROM moderators WHERE person_id = $1', Array($user->id));
        $moderator = pg_fetch_assoc($moderator_query);
        
        if (!isset($moderator['id'])) {
          $errors[] = "Вы не зарегистрированы в системе как модератор";
        }
      }

      if ($user_role == 'operator') {
        $operator_query = pg_query_params($conn, 'SELECT * FROM operators WHERE person_id = $1', Array($user->id));
        $operator = pg_fetch_assoc($operator_query);
        
        if (!isset($operator['id'])) {
          $errors[] = "Вы не зарегистрированы в системе как оператор";
        }
      }

      if (empty($errors) && password_verify($user_password, $user->user_password)) {
        // Все верно, пускаем пользователя
        $_SESSION['logged_user'] = $user;
        $_SESSION['user_role'] = $user_role;

        // Редирект на главную страницу
        header('Location: index.php');
      } else {
        $errors[] = "Пароль неверно введен!";
      }
    }
    if (!empty($errors)) {
      echo '<div style="color: red; ">' . array_shift($errors) . '</div><hr>';
    }
  }
?>

<div class="container">
  <h1><?= $title; ?></h1>
  <div class="layer">
    <form action="login.php" method="post">
      <div class="form-group">
        <label for="user_name">Ваш логин:</label>
        <input type="text" name="user_name" placeholder="Введите логин">
      </div>
      
      <div class="form-group">
        <label for="user_password">Ваш пароль:</label>
        <input type="password" name="user_password" placeholder="Введите пароль">
      </div>

      <p>
        <input type="radio" value="client" checked name="user_role">  Войти как клиент
      </p>
      <p>
        <input type="radio" value="moderator" name="user_role"> Войти как модератор
      </p>
      <!-- <p>
        <input type="radio" value="operator" name="user_role">  Войти как оператор
      </p> -->

      <button class="btn" name="do_login" type="submit">Авторизоваться</button>
    </form>
    <br>
    <br>
    <a href="signup.php">Регистрация</a>
    <p>Вернуться на <a href="index.php">главную</a>.</p>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>