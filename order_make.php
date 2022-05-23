<?php
  $title = 'Sschot';

  require 'connect.php';
  require 'header.php';

  if (!isset($_POST['contact_name']) || !(isset($client))) {
    die('Неверный запрос');
  }

  function header_ret() {
    header('Location: order_make_form.php');
  }
  
  $order_id = $_POST['order_id'];
  if (!isset($_POST['is_pay'])) {
    $contact_name = $_POST['contact_name'];
    $contact_email = $_POST['contact_email'];
    if (!preg_match("/[0-9a-z_]+@[0-9a-z_^\.]+\.[a-z]{2,3}/i", $contact_email)) {
      $_SESSION['op_message_error'] = "Неверно введен контактный Email. Пожалуйста, введите данные снова.";
      header_ret();
    }
    $contact_phone = $_POST['contact_phone'];
    if (!preg_match("/^[0-9]{10,11}+$/", $contact_phone)) {
      $_SESSION['op_message_error'] = "Телефон задан в неверном формате. Пожалуйста, введите данные снова.";
      header_ret();
    }
    $way_to_receive = $_POST['way_to_receive'];
    if ($way_to_receive == 'Самовывоз') {
      $point_of_issue = $_POST['point_of_issue'];
      // echo '<br>' . $point_of_issue . '<br>';
      // $delivery_address = substr($delivery_address, 0, strpos($delivery_address, ' ('));
      $delivery_address = $point_of_issue;
    } else {
      $point_of_issue = null;
      $delivery_address = $_POST['delivery_address'];
    }
    $payment_method = $_POST['payment_method'];
    if ($way_to_receive == 'Доставка') {
      $date_of_receipt = $_POST['date_of_receipt'];
    } else {
      $date_of_receipt_date = $_POST['date_of_receipt_date'];
      $date_of_receipt_time = $_POST['date_of_receipt_time'];
      // echo '<br>' . $date_of_receipt_date . '<br>';
      // echo '<br>' . $date_of_receipt_time . '<br>';
      $date_of_receipt = $date_of_receipt_date . 'T' . $date_of_receipt_time;
    }

    $query_last_client_order = pg_query_params($conn, 'SELECT MAX(id), reg_date FROM orders WHERE client_id = $1 GROUP BY reg_date', Array($client->id));
    $last_client_order = pg_fetch_object($query_last_client_order);

    if ($last_client_order) {
      $last_order_date = get_date($last_client_order->reg_date);
      $last_order_date_hours = $last_order_date->format('H');
      $last_order_date_day = $last_order_date->format('d');
      $date_next_make = get_date('');
      $date_next_make->add(new DateInterval('PT' . COOLDOWN_MAKE_ORDER . 'H'));
      $date_next_make_hours = $date_next_make->format('H');
      $date_next_make_day = $date_next_make->format('d');

      if ($last_order_date_hours < $date_next_make_hours || ($last_order_date_day < $date_next_make_day)) {
        $message_day = ($last_order_date_day != $date_next_make_day) ? ' следующего дня' : '';
        $message = "Следующий заказ можно оформить в " . $date_next_make->format('H:i') . $message_day . '.';
        $_SESSION['op_message_error'] = $message;
        header_ret();
      }
    }

    if ($way_to_receive == 'Самовывоз') {
      $query_point_of_issue = pg_query_params($conn, 'SELECT * FROM points_of_issue WHERE address = $1', Array($delivery_address));
      $point_of_issue = pg_fetch_object($query_point_of_issue);

      $statuses_cond = "'В процессе подтверждения оператором', 'Готовится'";

      $date_of_receipt_date = get_date($date_of_receipt);
      $date_of_receipt_time = $date_of_receipt_date->format('H:i');

      $query_count_orders = pg_query_params($conn, 'SELECT COUNT(*) FROM orders INNER JOIN order_statuses ON orders.order_status_id = order_statuses.id WHERE make_time(date_part(\'hour\', orders.date_of_receipt)::int, date_part(\'hour\', orders.date_of_receipt)::int, 0::double precision) = $1 AND order_statuses.order_status_name IN ($2) AND orders.point_of_issue_id = $3', Array($date_of_receipt_time, $statuses_cond, $point_of_issue->id));

      if ($query_count_orders) {
        $count_orders_obj = pg_fetch_object($query_count_orders);
    
        if ($count_orders_obj->count >= MAX_ORDERS_FOR_TIME) {
          $_SESSION['op_message_error'] = 'Пункт выдачи заказа уже недоступен';
          header_ret(); 
        }
      }
    }

    $client_products_data = $_POST['client_products'];
    $products_data = $_POST['products'];
    $products_ids = [];
    foreach ($products_data as $key => $value) {
      $products_ids[] = $key;
    }
    $properties_data = $_POST['properties'];
    $properties_ids = [];
    foreach ($properties_data as $key => $value) {
      $properties_ids[] = $key;
    }

    $query_client_products = pg_query_params($conn, 'SELECT * FROM client_products WHERE client_id = $1', Array($client->id));
    $products_ids_str = implode(', ', $products_ids);
    $query_products = pg_query($conn, "SELECT * FROM products WHERE id IN ($products_ids_str)");
    $properties_ids_str = implode(', ', $properties_ids);
    $query_properties = pg_query($conn, "SELECT * FROM properties WHERE product_id IN ($properties_ids_str)");

    while ($product = pg_fetch_assoc($query_products)) {
      foreach ($product as $key => $value) {
        if ($products_data[$key] != $value) {
          $_SESSION['op_message_error'] = 'К сожалению, при оформлении заказа товары были изменены. Их состояние вы можете просмотреть на <a href="index.php"> главной странице</a>.';
          $_SESSION['products_query'] = $query_products;
          header_ret();
        }
      }
    }
    while ($property = pg_fetch_asoc($query_properties)) {
      foreach ($property as $key => $value) {
        if ($properties_data[$key] != $value) {
          $_SESSION['op_message_error'] = 'К сожалению, при оформлении заказа товары были изменены. Их состояние вы можете просмотреть на <a href="index.php"> главной странице</a>.';
          $_SESSION['products_query'] = $query_products;
          header_ret();
        }
      }
    }
    $order_price = money_to_num(get_client_products_price());

    $order_bonuses = money_to_num(get_client_products_bonuses());

    $discount = get_price_discount($order_price);

    $delivery_price = get_price_delivery($order_price);

    $final_price = $order_price - $discount + $delivery_price;

    $order_id = order_add($order_id, $contact_name, $contact_email, $contact_phone, $way_to_receive, $payment_method, $delivery_address, $point_of_issue, $date_of_receipt, $final_price);
  } else {
    $final_price = $_POST['final_price'];
  }
?>

<?php if (!isset($_POST['is_pay']) && $payment_method == 'Онлайн') : ?>

  <p>До остановки транзакции: <p id="transaction-time"></p> сек.</p>

  <div class="container">
    <div class="layer">
      <form action="order_make.php" method="post">
        <p align=center>Вам предъявлен счет</p>
        <p><b><?= $final_price ?> ₽</b></p>
        <input type="hidden" name="contact_name" value="<?= $_POST['contact_name'] ?>">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="is_pay" value="1">
        <input type="hidden" name="payment_method" value="<?= $payment_method ?>">
        <input type="hidden" name="final_price" value="<?= $final_price ?>">
        <button type="submit">Оплатить</button>
      </form>
    </div>
  </div>

  <script>
    let start_time = 15
    const time_elem = document.getElementById('transaction-time')
    if (time_elem) {
      time_elem.innerHTML = start_time.toString()
      setInterval(function() {
        time_elem.innerHTML = (--start_time).toString()
        if (start_time <= 0) {
          document.location = 'order_make_form.php';
        }
      }, 1000)
    }
  </script>

<?php
  else :
    if ($payment_method == 'Онлайн' && $client->balance < $final_price) {
      $_SESSION['op_message_error'] = 'Недостаточно баланса для оплаты';
      header_ret();
    }

    try {
      $order_products = order_add_client_products($order_id, $client_products_data);

      $_SESSION['op_message'] = 'Заказ оформлен. Его состояние вы можете отследить в разделе <a href="my_orders.php">Мои заказы</a>.';
      header('Location: index.php');
    } catch (Error $ex) {
      $_SESSION['op_message_error'] = 'Ошибка запроса';
      order_delete($order_id);
      header_ret();
    }
    
  endif
?>

<?php require 'footer.php'; ?>