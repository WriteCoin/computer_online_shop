<?php
  $title = "Форма регистрации";
  require __DIR__ . "/header.php";
  require "connect.php";

  $data = $_POST;

  // регистрация
  if (isset($data['do_signup'])) {
    $errors = array();

    $user_name = $secure_data($data['user_name']);
    $phone = $secure_data($data['phone']);
    $email = $secure_data($data['email']);
    $first_name = $secure_data($data['first_name']);
    $last_name = $secure_data($data['last_name']);
    $user_password = $secure_data($data['user_password']);
    $user_password2 = $secure_data($data['user_password2']);

    if (trim($user_name) == '') {
      $errors[] = "Введите логин!";
    }

    if (trim($phone) == '') {
      $errors[] = "Введите телефон";
    }

    if (trim($email) == '') {
      $errors[] = "Введите Email";
    }

    if (trim($first_name) == '') {
      $errors[] = "Введите имя";
    }

    if (trim($last_name) == '') {
      $errors[] = "Введите фамилию";
    }

    if ($user_password == '') {
      $errors[] = "Введите пароль";
    }

    if ($user_password2 != $user_password) {
      $errors[] = "Повторный пароль введен не верно!";
    }

    if (mb_strlen($user_name) < 5 || mb_strlen($user_name) > 90) {
      $errors[] = "Недопустимая длина логина";
    }

    if (mb_strlen($first_name) < 3 || mb_strlen($first_name) > 50) {
      $errors[] = "Недопустимая длина имени";
    }

    if (mb_strlen($last_name) < 5 || mb_strlen($last_name) > 50) {
      $errors[] = "Недопустимая длина фамилии";
    }

    if (mb_strlen($user_password) < 2 || mb_strlen($user_password) > 8) {
      $errors[] = "Недопустимая длина пароля (от 2 до 8 символов)";
    }

    if (!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $email)) {
      $errors[] = "Неверно введен Email";
    }

    // проверка на уникальность логина
    $login_query = pg_query_params($conn, 'SELECT * FROM person WHERE user_name = $1', Array($user_name));
    $user = pg_fetch_object($login_query);
    if ($user) {
      $errors[] = "Пользователь с таким логином существует!";
    }

    // проверка на уникальность email
    $email_query = pg_query_params($conn, 'SELECT * FROM person WHERE email = $1', Array($email));
    $user = pg_fetch_object($email_query);
    if ($user) {
      $errors[] = "Пользователь с таким Email существует!";
    }

    // проверка номера телефона
    if (!preg_match("/^[0-9]{10,11}+$/", $phone)) {
      $errors[] = "Телефон задан в неверном формате";
    }

    if (empty($errors)) {
      // Все проверено, регистрируем
      // добавляем в таблицу записи

      // хешируем пароль
      $user_password = password_hash($user_password, PASSWORD_DEFAULT);

      $new_user_query = pg_query_params($conn, 'INSERT INTO person(first_name, last_name, user_name, user_password, phone, email) VALUES ($1, $2, $3, $4, $5, $6);', Array($first_name, $last_name, $user_name, $user_password, $phone, $email));

      $user_query = pg_query_params($conn, 'SELECT * FROM person WHERE user_name = $1', Array($user_name));
      $new_user = pg_fetch_object($user_query);

      $new_client_query = pg_query_params($conn, 'INSERT INTO clients(person_id) VALUES ($1);', Array($new_user->id));
      $client_query = pg_query_params($conn, 'SELECT * FROM clients WHERE person_id = $1;', Array($new_user->id));
      $new_client = pg_fetch_object($client_query);

      echo '<div style="color: green; ">Вы успешно зарегистрированы! Можно <a href="login.php">авторизоваться</a>.</div><hr>';
    } else {
      echo '<div style="color: red;">' . array_shift($errors) . '</div><hr>';
    }
  }
  
  // $user_name = 'user';
  // $login_query = pg_query($conn, "SELECT * FROM person WHERE user_name = '{$user_name}'");
  // $num = pg_num_fields($login_query);

  // echo "Возвращено полей: " . $num . ".\n";

  // $rows = pg_num_rows($login_query);

  // echo "Возвращено строк: " . $rows . ".\n";

  // авторизация
?>

<div class="container">
  <h1><?= $title; ?></h1>
  <div class="layer">
    <form action="signup.php" method="post">
      <div class="form-group">
        <label for="first_name">Ваше имя:</label>
        <input type="text" name="first_name" placeholder="Введите имя">
      </div>

      <div class="form-group">
        <label for="last_name">Ваша фамилия:</label>
        <input type="text" name="last_name" placeholder="Введите фамилию">
      </div>

      <div class="form-group">
        <label for="user_name">Ваш логин:</label>
        <input type="text" name="user_name" placeholder="Введите логин">
      </div>
      
      <div class="form-group">
        <label for="user_password">Ваш пароль:</label>
        <input type="password" name="user_password" placeholder="Введите пароль">
      </div>

      <div class="form-group">
        <input type="password" name="user_password2" placeholder="Повторите пароль">
      </div>

      <div class="form-group">
        <label for="phone">Ваш телефон:</label>
        <input type="tel" name="phone" placeholder="Введите телефон">
      </div>

      <div class="form-group">
        <label for="email">Ваш Email:</label>
        <input type="email" name="email" placeholder="Введите Email">
      </div>

      <button class="btn" name="do_signup" type="submit">Зарегистрировать</button>
    </form>
    <br>
    <a href="login.php">Авторизация</a>
    <p>Вернуться на <a href="index.php">главную</a>.</p>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>