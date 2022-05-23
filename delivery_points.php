<?php
  $title = 'Изменить пункты доставки';
  require 'header.php';
  require 'connect.php';

  $query_delivery_points = pg_query($conn, "SELECT * FROM points_of_issue");

  site_message();
?>

<h1>Пункты доставки</h1>

<div class="container-index">
  <div class="layer">
    <p>На <a href="index.php">главную</a>.</p>
    <br><br>
    <a href="#" onclick="history.replaceState({}, '', document.referrer); history.back(); return false;">Вернуться назад</a>
    <br>
    <br>
    <button class="btn" onclick="document.location = 'new_delivery_point.php'">Добавить новый пункт доставки</button>
  </div>
  <?php if (pg_num_rows($query_delivery_points)) : ?>
    <div class="layer-index">
      <?php while ($point_of_issue = pg_fetch_object($query_delivery_points)) : 
        // $name = 'name' . $point_of_issue->id;
        // $address = 'address' . $point_of_issue->id;
        // $work_time_start = 'work_time_start' . $point_of_issue->id;
        // $work_time_end = 'work_time_end' . $point_of_issue->id;
      ?>
        <div class="layer">
          <form method="post">
            <input type="hidden" name="id" value="<?= $point_of_issue->id ?>">
            <div class="form-group">
              <label for="point-of-issue-name">Наименование пункта доставки:</label>
              <input type="text" id="point-of-issue-name" name="name" required value="<?= $point_of_issue->name ?>">
            </div>
            <div class="form-group">
              <label for="point-of-issue-address">Адрес пункта доставки:</label>
              <input type="text" id="point-of-issue-address" name="address" required value="<?= $point_of_issue->address ?>">
            </div>
            <br>
            <p><b>Часы работы:</b></p>
            <!-- <label for="point-of-issue-work-time">Часы работы:</label> -->
            <p>
              <input type="time" id="point-of-issue-work-time-start" name="work_time_start" required value="<?= $point_of_issue->work_time_start ?>">
              -
              <input type="time" id="point-of-issue-work-time-end" name="work_time_end" required value="<?= $point_of_issue->work_time_end ?>">
            </p>
            <p>
              <button class="btn" type="submit" formaction="update_delivery_point.php" onclick="return window.confirm('Отправить изменения?');">Отправить изменения</button>
            </p>
            <p>
              <button class="btn" type="submit" formaction="delete_delivery_point.php" onclick="return window.confirm('Удалить пункт доставки?');">Удалить</button>
            </p>
          </form>
        </div>
      <?php endwhile ?>
    </div>
  
  <?php else : ?>
    <div class="layer">
      <p><i>Нет пунктов доставки</i></p>
    </div>
  <?php endif ?>
</div>

<?php require 'footer.php'; ?>