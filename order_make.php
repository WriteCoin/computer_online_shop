<?php try { ?>

<?php
  $title = 'Sschot';

  require 'connect.php';
  require 'header.php';

  if (!isset($_POST['contact_name']) || !(isset($client))) {
    die('Неверный запрос');
  }

  function header_ret() {
    die("Ошибка запроса");
    // header('Location: order_make_form.php');
  }

  $client_products_data = $get_post('client_products', []);
  $products_data = $get_post('products', []);

  $way_to_receive = $get_post('way_to_receive', '');
  if ($way_to_receive == 'Самовывоз') {
    $point_of_issue = $_POST['point_of_issue'];
    // echo '<br>' . $point_of_issue . '<br>';
    // $delivery_address = substr($delivery_address, 0, strpos($delivery_address, ' ('));
    $delivery_address = $point_of_issue;
  } elseif ($way_to_receive == 'Доставка') {
    $point_of_issue = null;
    $delivery_address = $_POST['delivery_address'];
  }
  $payment_method = $get_post('payment_method', '');
  if ($way_to_receive == 'Доставка') {
    $date_of_receipt = $_POST['date_of_receipt'];
    $date_of_receipt_date = get_date($date_of_receipt);
    $receipt_time = $date_of_receipt_date->format('Y-m-d H:i:00');
  } elseif ($way_to_receive == 'Самовывоз') {
    $date_of_receipt_date = $_POST['date_of_receipt_date'];
    $date_of_receipt_time = $_POST['date_of_receipt_time'];
    // echo '<br>' . $date_of_receipt_date . '<br>';
    // echo '<br>' . $date_of_receipt_time . '<br>';
    $date_of_receipt = $date_of_receipt_date . 'T' . $date_of_receipt_time;
    $receipt_time = $date_of_receipt_date . ' ' . $date_of_receipt_time;
  }

  if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
  } else {
    $query_order_max_id = pg_query($conn, 'SELECT MAX(id) FROM orders');
    $order_max_id = pg_fetch_object($query_order_max_id);
    // print_r($order_max_id);

    if ($order_max_id->max) {
      // print($order_max_id);
      // print_r($order_max_id);

      $order_id = $order_max_id->max + 1;
    } else {
      $order_id = 1;
    }
  }
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

    $query_last_client_order = pg_query_params($conn, 'SELECT MAX(id), reg_time FROM orders WHERE client_id = $1 GROUP BY reg_time', Array($client->id));
    $last_client_order = pg_fetch_object($query_last_client_order);

    if ($last_client_order) {
      $last_order_date = get_date($last_client_order->reg_time);
      $last_order_date_hours = $last_order_date->format('H');
      $last_order_date_day = $last_order_date->format('d');
      $last_order_date_year = $last_order_date->format('Y');
      $date_next_make = get_date('');
      $date_next_make->add(new DateInterval('PT' . COOLDOWN_MAKE_ORDER . 'H'));
      $date_next_make_hours = $date_next_make->format('H');
      $date_next_make_day = $date_next_make->format('d');
      $date_next_make_year = $date_next_make->format('Y');

      // echo $last_order_date_hours . '<br>';
      // echo $date_next_make_hours . '<br>';
      // echo $last_order_date_day . '<br>';
      // echo $date_next_make_day . '<br>';

      if ($date_next_make_year - $last_order_date_year == 0) {
        if ($date_next_make_day - $last_order_date_day == 0) {
          if ($date_next_make_hours - $last_order_date_hours < 2) {
            $_SESSION['op_message_error'] = 'Подождите некоторое время, чтобы оформить следующий заказ';
            header_ret();
          }
        }
      }
      // if ($last_order_date_hours < $date_next_make_hours || ($last_order_date_day < $date_next_make_day)) {
      //   $message_day = ($last_order_date_day != $date_next_make_day) ? ' следующего дня' : '';
      //   $message = "Следующий заказ можно оформить в " . $date_next_make->format('H:i') . $message_day . '.';
      //   $_SESSION['op_message_error'] = $message;
      //   header_ret();
      // }
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
    $query_products_sql = "SELECT * FROM products WHERE id IN ($products_ids_str)";
    $query_products = pg_query($conn, $query_products_sql);
    $properties_ids_str = implode(', ', $properties_ids);
    $query_properties_sql = "SELECT * FROM properties WHERE product_id IN ($properties_ids_str)";
    $query_properties = pg_query($conn, $query_properties_sql);

    // print_r($products_data);

    while ($product = pg_fetch_assoc($query_products)) {
      foreach ($product as $key => $value) {
        if ($key != 'id') {
          $id = $product['id'];
          // echo '<br>' . $product['id'] . '<br>';
          // echo '<br>' . $id . '<br>';
          // echo '<br>' . $key . '<br>';
          // echo $products_data['3']['product_name'];
          // echo $products_data[$id][$key];
          if ($products_data[$id][$key] != $value) {
            $_SESSION['op_message_error'] = 'К сожалению, при оформлении заказа товары были изменены. Их состояние вы можете просмотреть на <a href="index.php"> главной странице</a>.';
            $_SESSION['products_query'] = pg_query($conn, $query_products_sql);
            // print_r($_SESSION['products_query']);
            header_ret();
          }
        }
      }
    }
    while ($property = pg_fetch_assoc($query_properties)) {
      foreach ($property as $key => $value) {
        if ($key != 'id') {
          if ($properties_data[$property['id']][$key] != $value) {
            $_SESSION['op_message_error'] = 'К сожалению, при оформлении заказа товары были изменены. Их состояние вы можете просмотреть на <a href="index.php"> главной странице</a>.';
            $_SESSION['products_query'] = $pg_query($conn, $query_properties_sql);
            header_ret();
          }
        }
      }
    }
    $order_price = money_to_num(get_client_products_price());

    // echo get_client_products_bonuses() . '<br>';

    $order_bonuses = money_to_num(get_client_products_bonuses());

    $discount = get_price_discount($order_price);

    $delivery_price = get_price_delivery($order_price);

    $final_price = $order_price - $discount + $delivery_price;

    $order_id = order_add($order_id, $contact_name, $contact_email, $contact_phone, $way_to_receive, $payment_method, $delivery_address, $point_of_issue, $date_of_receipt, $final_price, $receipt_time);

    if (!$order_id) {
      die('Ошибка запроса');
    }
  } else {
    $final_price = $_POST['final_price'];
    $order_bonuses = $_POST['order_bonuses'];
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
        <input type="hidden" name="order_bonuses" value="<?= $order_bonuses ?>">

        <?php foreach ($_POST['client_products'] as $id => $client_product) { ?>
          <?php foreach ($client_product as $key => $value) { ?>
            <input type="hidden" name='client_products[<?= $id ?>][<?= $key ?>]' value="<?= $value ?>">
          <?php } ?>
        <?php } ?>

        <?php foreach ($_POST['products'] as $id => $product) { ?>
          <?php foreach ($product as $key => $value) { ?>
            <input type="hidden" name="products[<?= $id ?>][<?= $key ?>]" value="<?= $value ?>">
          <?php } ?>
        <?php } ?>

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
          // document.location = 'order_make_form.php';
        }
      }, 1000)
    }
  </script>

<?php
  else :
    try {
      // echo $final_price . '<br>';
      // echo money_to_num($client->balance) - $final_price . '<br>';
      // echo $order_bonuses . '<br>';
      // die('ost');

      if ($payment_method == 'Онлайн') {
        if ($client->balance < $final_price) {
          $_SESSION['op_message_error'] = 'Недостаточно баланса для оплаты';
          $query_delete_order = pg_query_params($conn, "DELETE FROM orders WHERE id = $1", Array($order_id));
          header_ret();
        }
      }

      // print_r($client_products_data);

      // foreach ($client_products_data as $id => $client_product) {
      //   print_r($client_product);
      // }
  
      $order_products = order_add_client_products($order_id, $client_products_data);
  
      foreach ($products_data as $id => $product) {
        $query_quantity = pg_query_params($conn, 'SELECT quantity FROM client_products WHERE client_id = $1 AND product_id = $2', Array($client->id, $id));
        $quantity = pg_fetch_object($query_quantity)->quantity;
        $query_update_product_quantity = pg_query_params($conn, "UPDATE products SET quantity_in_stock = $1 WHERE id = $2", Array($product['quantity_in_stock'] - $quantity, $id));
      }
      $query_delete_products_client = pg_query_params($conn, "DELETE FROM client_products WHERE client_id = $1", Array($client->id));
      
      if ($payment_method == 'Онлайн') {
        $query_update_balance = pg_query_params($conn, 'UPDATE clients SET balance = $1 WHERE id = $2', Array(money_to_num($client->balance) - $final_price, $client->id));
      }
  
      $query_update_bonus_count = pg_query_params($conn, 'UPDATE clients SET bonus_count = $1 WHERE id = $2', Array(money_to_num($client->bonus_count) + $order_bonuses, $client->id));
  
      $_SESSION['op_message'] = 'Заказ оформлен. Его состояние вы можете отследить в разделе <a href="my_orders.php">Мои заказы</a>.';
      header('Location: index.php');
    } catch (Error $err) {
      pg_query($conn, "DELETE FROM products_in_orders WHERE order_id = $order_id");
      pg_query($conn, "DELETE FROM orders WHERE id = $order_id");
      pg_query($conn, "UPDATE clients SET balance = $client->balance");
      header_ret();
    }
    
  endif
?>

<?php require 'footer.php'; ?>

<?php } catch (Error $ex) {
    echo "Произошла ошибка:<br>";
    echo $ex . "<br>";
  } catch (Throwable $ex) {
    echo "Ошибка при выполнении программы:<br>";
    echo $ex . "<br>";
  }
?>