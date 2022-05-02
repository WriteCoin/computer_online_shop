<?php
  $data = $_POST;

  $is_add = isset($data['do_add_product']);
  $is_edit = isset($data['do_edit_product']);

  $is_index = false;

  if ($is_add) {
    $title = "Добавить товар";
    $h1_title = "Добавление товара";
  } elseif ($is_edit) {
    $title = "Редактировать товар";
    $h1_title = "Редактирование товара";
  } else {
    $title = "Главная страница";
    $h1_title = "Добро пожаловать на сайт компьютерного интернет-магазина!";
    $is_index = true;
  }

  require __DIR__ . '/header.php';
  require __DIR__ . '/connect.php';

  if (isset($_SESSION['op_message'])) {
    echo '<div style="color: white; ">' . $_SESSION['op_message'] . '</div><hr>';
    unset($_SESSION['op_message']);
  } else if (isset($_SESSION['op_message_error'])) {
    echo '<div style="color: red; ">' . $_SESSION['op_message_error'] . '</div><hr>';
    unset($_SESSION['op_message_error']);
  }

  if ($is_index) {
    require __DIR__ . '/side_info.php';
  }
?>

<h1><?= $h1_title; ?></h1>
<?php if (!$is_add && !$is_edit) : ?>
  
  <div class="container">
    <div class="layer">
      <?php if (isset($logged_user)) : ?>
        Привет, <?php echo "$logged_user->first_name $logged_user->last_name"; ?></br>
        Вы вошли как <?= $user_role_name; ?>.</br>

        <a href="logout.php">Выйти</a>
      <?php else : ?>
        <p><i>Чтобы оформить заказ, Вам необходимо войти в систему</i></p>

        <a href="login.php">Авторизоваться</a><br>
        <a href="signup.php">Регистрация</a>
      <?php endif; ?>
    </div>
  </div>
  <?php if (isset($logged_user)) : ?>
    <div class="container">
      <div class="layer">
        <h3>Информация об аккаунте</h3>
        <table class="table">
          <tr>
            <td><b>Имя:</b></td>
            <td><?=htmlspecialchars($logged_user->first_name)?></td>
          </tr>
          <tr>
            <td><b>Фамилия:</b></td>
            <td><?=htmlspecialchars($logged_user->last_name)?></td>
          </tr>
          <tr>
            <td><b>Логин:</b></td>
            <td><?=htmlspecialchars($logged_user->user_name)?></td>
          </tr>
          <tr>
            <td><b>Телефон:</b></td>
            <td><?=htmlspecialchars($logged_user->phone)?></td>
          </tr>
          <tr>
            <td><b>E-mail:</b></td>
            <td><?=htmlspecialchars($logged_user->email)?></td>
          </tr>
          <?php if (isset($client)) : ?>
          <tr>
            <td><b>Баланс:</b></td>
            <td><?=htmlspecialchars($client->balance)?></td>
          </tr>
          <tr>
            <td><b>Бонусы:</b></td>
            <td><?=htmlspecialchars($client->bonus_count)?></td>
          </tr>
          <?php endif ?>
        </table>

        <?php if (isset($client)) : ?>
        <a href="delete.php" onclick="return window.confirm('Вы точно хотите удалить данный аккаунт?');">Удалить аккаунт</a>
        <?php endif ?>
      </div>
    </div>
  <?php endif; ?>
<?php else : ?>
  <div class="container">
    <div class="layer">
      <p>Вернуться на <a href="index.php" onclick="return window.confirm('Вы точно хотите вернуться на главную страницу? Введенные данные не будут сохранены.');">главную</a>.</p>
    </div>
  </div>
<?php endif; ?>

<div class="container-index">
  <?php 
    if (!isset($products_query) && !$is_add && !$is_edit) {
      $products_query = pg_query($conn, "SELECT * FROM products");
    }
    if (!isset($title_product)) {
      $title_product = 'Список товаров';
    }
    if ($is_add) {
      $title_product = "Новый товар";
    } elseif ($is_edit) {
      $title_product = "Изменить товар";
    }
    require __DIR__ . '/products.php'; 
    $products_query = null;
  ?>
</div>

<?php require __DIR__ . '/footer.php'; ?>