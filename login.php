<?php
  $title = "Форма авторизации";
  require __DIR__ . "/header.php";
  require "connect.php";

  $data = $_POST;
  if (isset($data['do_login'])) {
    $errors = array();

    $user_name = $data['user_name'];
    $user_password = $data['user_password'];
    $user_role = $data['user_role'];

    $user_query = pg_query_params($conn, 'SELECT * FROM person WHERE user_name = $1', Array($user_name));
    $user = pg_fetch_object($user_query);

    if (!$user) {
      $errors[] = "Пользователь с таким логином не найден!";
    } else {
      $client_query = pg_query_params($conn, 'SELECT * FROM clients WHERE person_id = $1;', Array($user->id));
      $client = pg_fetch_object($client_query);

      if (!$client) {
        $errors[] = "Клиент не найден";
      }

      $moderator_query = pg_query_params($conn, 'SELECT * FROM moderators WHERE person_id = $1', Array($user->id));
      $moderator = pg_fetch_object($moderator_query);

      if (!$moderator && $user_role == 'moderator') {
        $errors[] = "Вы не зарегистрированы в системе как модератор";
      }

      $operator_query = pg_query_params($conn, 'SELECT * FROM operators WHERE person_id = $1', Array($user->id));
      $operator = pg_fetch_object($operator_query);

      if (!$operator && $user_role == "operator") {
        $errors[] = "Вы не зарегистрированы в системе как оператор";
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
      <p>
        <input type="radio" value="operator" name="user_role">  Войти как оператор
      </p>

      <button class="btn" name="do_login" type="submit">Авторизоваться</button>
    </form>
    <br>
    <br>
    <a href="signup.php">Регистрация</a>
    <p>Вернуться на <a href="index.php">главную</a>.</p>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>