<?php
  require 'connect.php';
  $title = 'Заказы клиентов';
  require 'header.php';

  if (!isset($operator)) {
    die('Неверный запрос');
  }

  if (isset($_SESSION['query_clients'])) {
    $query_clients = $_SESSION['query_clients'];
    unset($_SESSION['query_clients']);
  } else {
    $query_clients = pg_query($conn, 'SELECT * FROM clients INNER JOIN person ON clients.person_id = person.id');
  }

  $current_date_obj = get_date('');
  $current_date = $current_date_obj->format('Y-m-d');
?>

<h1>Заказы клиентов</h1>

<div class="container-index">
  <div class="layer">
    <p>Вернуться на <a href="index.php">главную</a>.</p>
  </div>

  <div class="layer-index">
    <h3>Заказы клиентов</h3>

    <form action="order_change_form.php" method="post">
      <div class="form-group">
        <label for="reg-date-input">По дате регистрации заказа</label>
        <input type="date" id="reg-date-input" name="reg_date" max="<?= $current_date ?>" value="<?= $current_date ?>" onchange="document.getElementById('form-reg-date-input').value = this.value">
      </div>
  
      <div class="form-group">
        <label for="receipt-date-input">По дате получения заказа</label>
        <input type="date" id="receipt-date-input" name="receipt_date" onchange="document.getElementById('form-receipt-date-input').value = this.value">
      </div>
  
      <div class="form-group">
        <label for="order-number-input">По номеру заказа</label>
        <input type="number" id="order-number-input" name="order_number" onchange="document.getElementById('form-order-number-input').value = this.value">
      </div>

      <button class="btn" type="submit">Найти заказы</button>
    </form>

    <p><i>Или отобрать их по клиенту</i></p>

    <?php while ($client = pg_fetch_object($query_clients)) : 
      // $link = $client->user_name . ' (' $client->first_name . ' ' . $client->last_name . ') ';
      $link = $client->user_name;
    ?>
      <p>
        <form id="form-client-for-order" action="order_change_form.php" method="post">
          <input type="hidden" name="client_id" value="<? $client->id ?>">
          <input type="hidden" id="form-reg-date-input" name="reg_date">
          <input type="hidden" id="form-receipt-date-input" name="receipt_date">
          <input type="hidden" id="form-order-number-input" name="order_number">
          <p>
            Заказ клиента
            <a href="#" onclick="document.getElementById('form-client-for-order').submit(); return false"><?= $link ?></a>
          </p>
        </form>
      </p>
    <?php endwhile ?>
  </div>
</div>

<?php require 'footer.php'; ?>