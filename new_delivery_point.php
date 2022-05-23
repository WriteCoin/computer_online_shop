<?php
  $title = "Добавить пункт выдачи";
  require 'header.php';
  require 'connect.php';

  if (isset($client)) {
    die('Неверный запрос');
  }

  site_message();
?>

<h1>Новый пункт выдачи</h1>

<div class="container-index">
  <div class="layer">
    <a href="delivery_points.php">Вернуться к списку выдачи заказов</a>
    <br>
    <a href="#" onclick="history.replaceState({}, '', document.referrer); history.back(); return false;">Вернуться назад</a>
  </div>

  <form action="add_delivery_point.php" method="post">
    <div class="layer">
      <div class="form-group">
        <label for="point-of-issue-name">Наименование пункта доставки:</label>
        <input type="text" id="point-of-issue-name" name="name" required>
      </div>
      <div class="form-group">
        <label for="point-of-issue-address">Адрес пункта доставки:</label>
        <input type="text" id="point-of-issue-address" name="address" required>
      </div>
      <br>
      <p><b>Часы работы:</b></p>
      <p>
        <input type="time" id="point-of-issue-work-time-start" name="work_time_start" required>
        -
        <input type="time" id="point-of-issue-work-time-end" name="work_time_end" required>
      </p>
      <button class="btn" type="submit">Добавить</button>
    </div>
  </form>

</div>

<?php require 'footer.php'; ?>