<?php
  require 'connect.php';
  $title = 'Изменить заказы';
  require 'header.php';

  if (!isset($operator)) {
    die('Неверный запрос');
  }
  
  // if (isset($_SESSION['query_orders'])) {
  //   $query_orders = $_SESSION['query_orders'];
  //   unset($_SESSION['query_orders']);
  // } else {
  //   $query_orders = pg_query($conn, 'SELECT * FROM orders');
  // }

  $client_id = query_escape($get_post('client_id', 0));
  $reg_date = query_escape($get_post('reg_date', ''));
  $receipt_date = query_escape($get_post('receipt_date', ''));
  $order_number = query_escape($get_post('order_number', 0));

  $client_cond = ($client_id) ? "orders.client_id = $client_id" : "";
  $reg_date_cond = ($reg_date) ? "orders.reg_date = '$reg_date'" : "";
  $receipt_date_cond = ($receipt_date) ? "orders.date_of_receipt = '$receipt_date'" : "";
  $order_number_cond = ($order_number) ? "orders.order_number = $order_number" : "";

  $cond = '';
  if ($client_cond) {
    $cond = $cond . $client_cond;
  }
  if ($reg_date_cond) {
    $cond = ($cond) ? $cond . " AND " . $reg_date_cond : $reg_date_cond;
  }
  if ($receipt_date_cond) {
    $cond = ($cond) ? $cond . " AND " . $receipt_date_cond : $receipt_date_cond;
  }
  if ($order_number_cond) {
    $cond = ($cond) ? $cond . " AND " . $order_number_cond : $order_number_cond;
  }

  $query_orders_sql = "SELECT * FROM orders WHERE $cond";
  $query_orders = pg_query($conn, $query_orders_sql);

  $min_date = get_date('');

  $max_date = get_date('');
  $max_date->add(new DateInterval('P' . MAX_MAKE_ORDER_DAYS . 'D'));
?>

<h1>Изменить заказы</h1>

<div class="container-index">
  <div class="layer">
    <p>Вернуться к списку ссылок на <a href="client_orders.php">заказы клиентов</a>.</p>
  </div>

  <?php if (!pg_num_rows($query_orders)) : ?>
    <div class="layer">
      <p><i>Заказов не найдено</i></p>
    </div>
  <?php endif ?>

  <?php while ($order = pg_fetch_object($query_orders)) :
    $query_products = pg_query_params($conn, 'SELECT * FROM products_in_orders WHERE order_id = $1', Array($order->id));

    $query_way_to_receive = pg_query($conn, "SELECT * FROM ways_to_receive WHERE id = $order->way_to_receive_id");
    $way_to_receive = pg_fetch_object($query_way_to_receive);
    $way_to_receive_name = $way_to_receive->way_to_receive_name;

    $query_payment_method = pg_query($conn, "SELECT * FROM payment_methods WHERE id = $order->payment_method_id");
    $payment_method = pg_fetch_object($query_payment_method);
    $payment_method_name = $payment_method->payment_method_name;

    $query_point_of_issue = pg_query($conn, "SELECT * FROM points_of_issue WHERE id = $order->point_of_issue_id");
    if (!$query_point_of_issue) {
      $point_of_issue = pg_fetch_object($query_point_of_issue);
    }

    $query_order_status = pg_query($conn, "SELECT * FROM order_statuses WHERE id = $order->order_status_id");
    $order_status = pg_fetch_object($query_order_status);
    $order_status_name = $order_status->order_status_name;

    $is_pickup = isset($point_of_issue) && $way_to_receive_name == 'Самовывоз';
    $is_delivery = $way_to_receive_name == 'Доставка';

    $is_edit = !($order_status_name == 'Выполнен' || $order_status_name == 'Отменен');

    if ($is_edit) {
  
      if ($is_pickup) {
        $date_format = 'Y-m-dTH:i';
  
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
        }
  
        $query_point_ids_str = count($query_point_ids) ? implode(', ', $query_point_ids) : '';
  
        // echo '<br>' . $query_point_ids_str . '<br>';
  
        $query_points_of_issue = $query_point_ids_str ? pg_query($conn, "SELECT * FROM points_of_issue WHERE id IN ($query_point_ids_str)") : [];
  
        if (!$query_points_of_issue) {
          $no_points = true;
        }
      } elseif ($is_delivery) {
        $date_format = 'Y-m-d';
  
        $min_date = get_date('');
        $min_date->add(new DateInterval('P1D'));
      }
  
      $min_date_str = $min_date->format($date_format);
      $max_date_str = $max_date->format($date_format);

      $query_order_statuses = pg_query($conn, "SELECT * FROM order_statuses");
    }

  ?>
    <div class="layer-index">
    <form class="form-product" method="post">
        <h2>Заказ № <?= $order->order_number ?></h2>

        <input type="hidden" name='id' value='<?= $order->id ?>'>

        <?php if (!$is_edit) : ?>
          <div class="layer">
            <h3>Контактные данные:</h3>
            <p>
              <table class="table">
                <tr>
                  <td><b>Имя Фамилия Отчество</b></td>
                  <td><?= $order->contact_name ?></td>
                </tr>
                <tr>
                  <td><b>E-mail</b></td>
                  <td><?= $order->contact_email ?></td>
                </tr>
                <tr>
                  <td><b>Номер мобильного телефона</b></td>
                  <td><?= $order->contact_phone ?></td>
                </tr>
              </table>
            </p>
          </div>
          
          <div class="layer">
            <h3>Данные заказа:</h3>
            <p>
              <table class="table">
                <tr>
                  <td><b>Способ получения</b></td>
                  <td><?= $way_to_receive->way_to_receive_name ?></td>
                </tr>
                <tr>
                  <td><b>Способ оплаты</b></td>
                  <td><?= $payment_method->payment_method_name ?></td>
                </tr>
                <tr>
                  <td><b>Адрес доставки</b></td>
                  <td><?= $order->delivery_address ?></td>
                </tr>
                <?php if ($is_pickup) : ?>
                  <tr>
                    <td><b>Пункт выдачи заказа</b></td>
                    <td><?= $point_of_issue->address . ' (' . $point_of_issue->name . ') ' . $point_of_issue->work_time_start . '-' . $point_of_issue->work_time_end ?></td>
                  </tr>
                <?php endif ?>
                <tr>
                  <td><b>Дата регистрации заказа</b></td>
                  <td><?= $order->reg_date . ' ' . $order->reg_time ?></td>
                </tr>
                <tr>
                  <td><b>Дата получения заказа</b></td>
                  <td><?= $order->date_of_receipt . ' ' . $order->receipt_time ?></td>
                </tr>
                <tr>
                  <td><b>Фактическая дата получения заказа</b></td>
                  <td><?= $order->actual_date_of_receipt . ' ' . $order->actual_receipt_time ?></td>
                </tr>
                <tr>
                  <td><b>Статус заказа</b></td>
                  <td><?= $order->order_status_name ?></td>
                </tr>
              </table>
            </p>
          </div>
        <?php else : ?>
          <div class="layer">
            <h3>Контактные данные</h3>
  
            <div class="form-group">
              <label for="contact_name">Фамилия Имя Отчество:</label>
              <input type="text" required name="contact_name" value="<?= $order->contact_name ?>">
            </div>
  
            <div class="form-group">
              <label for="contact_email">Электронная почта:</label>
              <input type="email" name="contact_email" value="<?= $order->contact_email ?>">
            </div>
  
            <div class="form-group">
              <label for="contact_phone">Мобильный телефон:</label>
              <input type="phone" name="contact_phone" value="<?= $order->contact_phone ?>">
            </div>
          </div>
          
          <div class="layer">
            <h3>Данные заказа:</h3>
  
            <p><b>Способ получения: </b><?= $way_to_receive_name ?></p>
  
            <!-- <div class="form-group">
              <label for="way_to_receive">Способ получения:</label>
              <select name="way_to_receive" id="way_to_receive">
                <?php while ($way_to_receive_obj = pg_fetch_object($query_ways_to_receive)) : ?>
                  <option 
                    value="<?= $way_to_receive_obj->way_to_receive_name ?>"
                    <?php if ($way_to_receive_obj->id == $way_to_receive->id) : ?>
                      selected
                    <?php endif ?>
                  >
                    <?= $way_to_receive_obj->way_to_receive_name ?>
                  </option>
                <?php endwhile ?>
              </select>
            </div> -->
  
            <div class="form-group">
              <label for="delivery_address">Адрес доставки</label>
              <?php if ($is_pickup) : ?>
                <?php if (!$no_points) : ?>
                  <select name="point_of_issue" id="delivery_address">
                    <?php while ($point_of_issue_obj = pg_fetch_object($query_points_of_issue)) : ?>
                      <option value="<?= $point_of_issue_obj->address ?>"
                        <?php if ($point_of_issue_obj->id == $point_of_issue->id) : ?>
                          selected
                        <?php endif ?>
                      >
                        <?= $point_of_issue_obj->address . " (" . $point_of_issue_obj->name . ") " . $point_of_issue_obj->work_time_start . "-" . $point_of_issue_obj->work_time_end ?>
                      </option>
                    <?php endwhile ?>
                  </select>
                <?php else : ?>
                  <p><b>Адрес доставки: </b>
                  <?= $point_of_issue->address . " (" . $point_of_issue->name . ") " . $point_of_issue->work_time_start . "-" . $point_of_issue->work_time_end ?>
                  </p>
                <?php endif ?>
              <?php elseif ($is_delivery) : ?>
                <input type="text" id="delivery_address" name="delivery_address" value="<?= $delivery_address ?>">
              <?php endif ?>
            </div>
  
            <p><b>Способ оплаты: </b><?= $payment_method_name ?></p>
  
            <!-- <div class="form-group">
              <label for="payment_method">Способ оплаты:</label>
              <select name="payment_method" id="payment_method">
                <?php while ($payment_method_obj = pg_fetch_object($query_payment_methods)) : ?>
                  <option value="<?= $payment_method_obj->payment_method_name ?>"
                    <?php if ($payment_method_obj->payment_method_name == $payment_method->payment_method_name || $payment_method_obj->id == $payment_method->id) : ?>
                      selected
                    <?php endif ?>
                  >
                    <?= $payment_method_obj->payment_method_name ?>
                  </option>
                <?php endwhile ?>
              </select>
            </div> -->
  
            <p><b>Дата регистрации заказа: </b><?= $order->reg_date . ' ' . $order->reg_time ?></p>
  
            <?php if ($is_pickup) : ?>
              <?php if (!$no_points) : ?>
                <div class="form-group">
                  <label for="date_of_receipt">Дата доставки:</label>
                  <input type="date" name="date_of_receipt_date" id="date_of_receipt" required min="<?= $min_date_str ?>" max="<?= $max_date_str ?>" value="<?= $order->date_of_receipt ?>">
                </div>
              <?php else : ?>
                <p><b>Дата доставки: </b><?= $order->date_of_receipt . ' ' . $order->receipt_time ?></p>
              <?php endif ?>
            <?php elseif ($is_delivery) : ?>
              <input type="datetime-local" id="date_of_receipt" name="date_of_receipt" required min="<?= $min_date_str ?>" max="<?= $max_date_str ?>" value="<?= $order->date_of_receipt ?>">
            <?php endif ?>
  
            <?php if ($is_pickup) : ?>
              <?php if (!$no_points) : ?>
                <label for="date_of_receipt_time">Время доставки:</label>
                <select name="date_of_receipt_time" id="date_of_receipt_time">
                  <?php foreach ($query_point_pickup_times[$point_of_issue_need->id] as $pickup_time) { ?>
                    <option value="<?= $pickup_time ?>"><?= $pickup_time ?></option>
                  <?php } ?>
                </select>
              <?php endif ?>
            <?php endif ?>

            <div class="form-group">
              <label for="order_status">Статус заказа</label>
              <select name="order_status" id="order_status">
                <?php while ($order_status_obj = pg_fetch_object($query_order_statuses)) : ?>
                  <option 
                    value="<?= $order_status_obj->order_status_name ?>"
                    <?php if ($order_status_obj->id == $order_status->id) : ?>
                      selected
                    <?php endif ?>
                  >
                    <?= $order_status_obj->order_status_name ?>
                  </option>
                <?php endwhile ?>
              </select>
            </div>
          </div>
        <?php endif ?>
  
        <p><b>Цена заказа: </b><?= $order->price ?></p>
  
        <p>
          <details>
            <summary>Товары в заказе</summary>
            <table class="table">
              <?php while ($order_product = pg_fetch_object($query_products)) : 
                $query_product_name = pg_query_params($conn, "SELECT product_name FROM products WHERE id = $1", Array($order_product->product_id));
                $product_name = ($query_product_name) ? pg_fetch_object($query_product_name)->product_name : '';
              ?>
                <tr>
                  <td>
                    <?php if ($product_name) : ?>
                      <a href="product_view.php?id=<?= $order_product->product_id ?>"><?= $product_name ?></a>
                    <?php else : ?>
                      Без названия
                    <?php endif ?>
                    <?= $order_product->quantity ?> ед.
                  </td>
                  <td>
                    <?= money_to_num($order_product->price) * $order_product->quantity ?>
                  </td>
                </tr>
              <?php endwhile ?>
            </table>
          </details>
        </p>
  
        <button class="btn" type="submit" formaction="order_change.php">Отправить данные</button>
      </form>
    </div>
  <?php endwhile ?>
</div>

<?php require 'footer.php'; ?>