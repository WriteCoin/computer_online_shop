<?php
  $title = 'Оформление заказа';

  require 'header.php';
  require 'connect.php';

  if (!isset($client)) {
    die('Неверный запрос');
  }

  $contact_name = $get_post('contact_name', $logged_user->first_name . ' ' . $logged_user->last_name);
  $contact_email = $get_post('contact_email', $logged_user->email);
  $contact_phone = $get_post('contact_phone', $logged_user->phone);
  $way_to_receive = $get_post('way_to_receive', 1);
  $payment_method = $get_post('payment_method', 1);
  $delivery_address = $get_post('delivery_address', '');
  $point_of_issue_id = $get_post('point_of_issue_id', 1);
  $date_of_receipt = $get_post('date_of_receipt', '');

  $query_client_products = pg_query_params($conn, 'SELECT * FROM client_products WHERE client_id = $1', Array($client->id));
  // $client_products = pg_fetch_assoc($query_client_products);
  $products_ids = [];
  while ($client_product = pg_fetch_assoc($query_client_products)) {
    $products_ids[] = $client_product['product_id'];
  }
  $products_ids_str = count($products_ids) ? implode(', ', $products_ids) : '';
  $query_products = pg_query($conn, "SELECT * FROM products WHERE id IN ($products_ids_str)");
  $query_properties_products = pg_query($conn, "SELECT * FROM properties WHERE product_id IN ($products_ids_str)");

  $query_client_products = pg_query_params($conn, 'SELECT * FROM client_products WHERE client_id = $1', Array($client->id));

  // print_r($client_products);

  // while ($client_product = pg_fetch_object($query_client_products)) {
  //   echo "<p>id товара $client_product->product_id; количество - $client_product->quantity<br />";
  // }

  // $query_order_max_id = pg_query($conn, 'SELECT MAX(id) FROM orders');
  // $order_max_id = pg_fetch_object($query_order_max_id);

  // if (isset($order_max_id)) {
  //   $order_max_id = 1;
  // }

  $query_payment_methods = pg_query($conn, "SELECT * FROM payment_methods");
  
  $query_ways_to_receive = pg_query($conn, "SELECT * FROM ways_to_receive");
  
  // $query_order_statuses = pg_query($conn, "SELECT * FROM order_statuses");

  if (is_numeric($way_to_receive)) {
    $query_way_to_receive_name = pg_query_params($conn, 'SELECT way_to_receive_name FROM ways_to_receive WHERE id = $1', Array($way_to_receive));
    $way_to_receive_name = pg_fetch_object($query_way_to_receive_name)->way_to_receive_name;
  } else {
    $way_to_receive_name = $way_to_receive;
  }

  $min_date = get_date('');

  $max_date = get_date('');
  $max_date->add(new DateInterval('P' . MAX_MAKE_ORDER_DAYS . 'D'));

  if ($way_to_receive_name == 'Доставка') {
    $is_delivery = true;
    $date_format = 'Y-m-dTH:i';
  } elseif ($way_to_receive_name == 'Самовывоз') {
    $is_pickup = true;

    if ($delivery_address) {
      $delivery_address = substr($delivery_address, 0, strpos($delivery_address, ' ('));

      $query_point_of_issue_need = pg_query_params($conn, 'SELECT * FROM points_of_issue WHERE address = $1', Array($delivery_address));
    } else {
      $query_point_of_issue_need = pg_query($conn, 'SELECT * FROM points_of_issue WHERE id = 1');
    }
    $point_of_issue_need = pg_fetch_object($query_point_of_issue_need);

    $date_format = 'Y-m-d';

    $min_date->add(new DateInterval('P1D'));

    $query_all_points_of_issue = pg_query($conn, "SELECT * FROM points_of_issue");

    $statuses_cond = "'В процессе подтверждения оператором', 'Готовится'";

    $query_point_ids = [];

    $query_point_pickup_times = [];

    while ($point_of_issue = pg_fetch_object($query_all_points_of_issue)) {
      $pickup_times = get_pickup_times($point_of_issue->work_time_start, $point_of_issue->work_time_end);
      foreach ($pickup_times as $key => $time) {
        // echo $time . '<br>';
        $query_count_orders = pg_query_params($conn, 'SELECT COUNT(*) FROM orders INNER JOIN order_statuses ON orders.order_status_id = order_statuses.id WHERE make_time(date_part(\'hour\', orders.date_of_receipt)::int, date_part(\'hour\', orders.date_of_receipt)::int, 0::double precision) = $1 AND order_statuses.order_status_name IN ($2) AND orders.point_of_issue_id = $3', Array($time, $statuses_cond, $point_of_issue->id));
        if ($query_count_orders) {
          $count_obj = pg_fetch_object($query_count_orders);
          // echo $count_obj->count . '<br>';
          if ($count_obj->count >= MAX_ORDERS_FOR_TIME) {
            unset($pickup_times[$key]);
          }
        }   
      }
      if (count($pickup_times)) {
        $query_point_ids[] = $point_of_issue->id;
      }
      $query_point_pickup_times[$point_of_issue->id] = $pickup_times;
      // echo '<br>';
    }

    // print_r($query_point_ids);

    $query_point_ids_str = count($query_point_ids) ? implode(', ', $query_point_ids) : '';

    // echo '<br>' . $query_point_ids_str . '<br>';

    $query_points_of_issue = $query_point_ids_str ? pg_query($conn, "SELECT * FROM points_of_issue WHERE id IN ($query_point_ids_str)") : [];

    if (!$query_points_of_issue) {
      $_SESSION['op_message_error'] = 'Некуда везти, выберите доставку';
      $no_points = true;
    }
  }

  $min_date_str = $min_date->format($date_format);
  $max_date_str = $max_date->format($date_format);

  $order_price = money_to_num(get_client_products_price());

  $order_bonuses = money_to_num(get_client_products_bonuses());

  $discount = get_price_discount($order_price);

  $delivery_price = get_price_delivery($order_price);

  // echo '<br>' . $order_price . '<br>';
  // echo '<br>' . $discount . '<br>';
  // echo '<br>' . $delivery_price . '<br>';

  $final_price = $order_price - $discount + $delivery_price;

  site_message();
?>

<h1>Оформление заказа</h1>

<div class="container-index">
  <div class="layer">
    <p><b>Ваш Баланс: </b><?= $client->balance ?></p>
    <p><b>Ваши бонусы: </b><?= $client->bonus_count ?></p>
    <p>Вернуться в <a href="basket_view.php">корзину</a>.</p>
  </div>

  <div class="layer-index">
    <form id="order-form" action="order_make.php" method="post">
      <!-- <h4>Заказ №<?= $order_max_id ?></h4> -->

      <div class="layer">
        <h4>Контактные данные</h4>

        <div class="form-group">
          <label for="contact_name">Фамилия Имя Отчество:</label>
          <input type="text" required name="contact_name" value="<?= $contact_name ?>">
        </div>

        <div class="form-group">
          <label for="contact_email">Электронная почта:</label>
          <input type="email" name="contact_email" value="<?= $contact_email ?>">
        </div>

        <div class="form-group">
          <label for="contact_phone">Мобильный телефон:</label>
          <input type="phone" name="contact_phone" value="<?= $contact_phone ?>">
        </div>
      </div>

      <div class="layer">
        <h4>Данные заказа</h4>

        <div class="form-group">
          <label for="way_to_receive">Способ получения:</label>
          <select name="way_to_receive" id="way_to_receive">
            <?php while ($way_to_receive = pg_fetch_object($query_ways_to_receive)) : ?>
              <option 
                value="<?= $way_to_receive->way_to_receive_name ?>"
                <?php if ($way_to_receive->way_to_receive_name == $way_to_receive_name) : ?>
                  selected
                <?php endif ?>
              >
                <?= $way_to_receive->way_to_receive_name ?>
              </option>
            <?php endwhile ?>
          </select>
        </div>

        <div class="form-group">
          <label for="delivery_address">
            <?php if (isset($point_of_issue) && $way_to_receive_name == 'Самовывоз') : ?>
              Адрес доставки (укажите адрес):
            <?php elseif (isset($is_pickup)) : ?>
              Адрес доставки (выберите пункт выдачи):
            <?php endif ?>
          </label>
          <?php if (isset($is_delivery)) : ?>
            <input type="text" id="delivery_address" name="delivery_address" value="<?= $delivery_address ?>">
          <?php elseif (isset($is_pickup)) : ?>
            <select name="point_of_issue" id="delivery_address">
              <?php while ($point_of_issue = pg_fetch_object($query_points_of_issue)) : ?>
                <option value="<?= $point_of_issue->address ?>"
                  <?php if ($point_of_issue->id == $point_of_issue_id) : ?>
                    selected
                  <?php endif ?>
                >
                  <?= $point_of_issue->address . " (" . $point_of_issue->name . ") " . $point_of_issue->work_time_start . "-" . $point_of_issue->work_time_end ?>
                </option>
              <?php endwhile ?>
            </select>
          <?php endif ?>
        </div>

        <div class="form-group">
          <label for="payment_method">Способ оплаты:</label>
          <select name="payment_method" id="payment_method">
            <?php while ($payment_method = pg_fetch_object($query_payment_methods)) : ?>
              <option value="<?= $payment_method->payment_method_name ?>"
                <?php if ($payment_method->id == $payment_method || $payment_method->payment_method_name == $payment_method) : ?>
                  selected
                <?php endif ?>
              >
                <?= $payment_method->payment_method_name ?>
              </option>
            <?php endwhile ?>
          </select>
        </div>

        <div class="form-group">
          <label for="date_of_receipt">Дата доставки:</label>
          <?php if (isset($is_delivery)) : ?>
            <input type="datetime-local" id="date_of_receipt" name="date_of_receipt" required min="<?= $min_date_str ?>" max="<?= $max_date_str ?>" value="<?= $date_of_receipt ?>">
          <?php elseif (isset($is_pickup)) : ?>
            <input type="date" name="date_of_receipt_date" id="date_of_receipt" required min="<?= $min_date_str ?>" max="<?= $max_date_str ?>" value="<?= $date_of_receipt ?>">
            <label for="date_of_receipt_time">Время доставки:</label>
            <select name="date_of_receipt_time" id="date_of_receipt_time">
              <?php foreach ($query_point_pickup_times[$point_of_issue_need->id] as $pickup_time) { ?>
                <option value="<?= $pickup_time ?>"><?= $pickup_time ?></option>
              <?php } ?>
            </select>
          <?php endif ?>
        </div>

      </div>

      <p><b>Стоимость заказа без скидки: </b><?= $order_price ?> руб.</p>
      <p><b>Бонусы за заказ: </b><?= $order_bonuses ?> руб.</p>
      <p><b>Стоимость доставки: </b><?= $delivery_price ?> руб.</p>

      <h3>Итого: <?= $final_price ?> руб.</h3>

      <!-- <input type="hidden" name="client_products> -->
      <!-- <?php foreach ($client_products as $key => $value) { ?>
        <input type="hidden" name="client_products['<?= $key ?>']" value="<?= $value ?>">
      <?php } ?> -->

      <?php while ($data = pg_fetch_assoc($query_client_products)) : ?>
        <?php foreach ($data as $key => $value) { ?>
          <?php if ($key != 'id') : ?>
            <input type="hidden" name="client_products[<?= $data['id'] ?>][<?= $key ?>]" value="<?= $value ?>">
          <?php endif ?>
        <?php } ?>
      <?php endwhile ?>

      <?php while ($data = pg_fetch_assoc($query_products)) : ?>
        <?php foreach ($data as $key => $value) { ?>
          <?php if ($key != 'id') : ?>
            <input type="hidden" name="products[<?= $data['id'] ?>][<?= $key ?>]" value="<?= $value ?>">
          <?php endif ?>
        <?php } ?>
      <?php endwhile ?>

      <?php while ($data = pg_fetch_assoc($query_properties_products)) : ?>
        <?php foreach ($data as $key => $value) { ?>
          <?php if ($key != 'id') : ?>
            <input type="hidden" name="properties[<?= $data['id'] ?>][<?= $key ?>]" value="<?= $value ?>">
          <?php endif ?>
        <?php } ?>
      <?php endwhile ?>

      <!-- <input type="hidden" name="order_id" value="<?= $order_max_id ?>"> -->

      <button class="btn" type="submit"
        <?php if (isset($no_points)) : ?>
          disabled
        <?php endif ?>
      >Подтвердить заказ</button>
    </form>

  </div>
</div>

<script>
  $select = document.getElementById('way_to_receive')
  if ($select) {
    $select.addEventListener('change', function() {
      $form = document.getElementById('order-form')

      $form.action = 'order_make_form.php'

      $form.submit()
    })
  }
  <?php if (isset($is_pickup)) : ?>
    $select = document.getElementById('delivery_address')
    if ($select) {
      $select.addEventListener('change', function() {
        $form = document.getElementById('order-form')

        $form.action = 'order_make_form.php'

        $form.submit()
      })
    }
  <?php endif ?>
</script>

<?php require 'footer.php' ?>