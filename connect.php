<?php
  require 'connect_info.php';

  // echo extension_loaded('pgsql') ? 'yes':'no';

  function pdo_test($host, $port, $dbname, $user, $password) {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    try {
      $conn = new PDO($dsn);
      if ($conn) {
        echo "Connected to the <strong>$db</strong> database successfully!";
        
        $res = $conn->query("select id, category_name from categories");

        while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
          echo($row['id'].'-'.$row['category_name']);
        }
      }
    } catch (PDOException $e) {
      echo "Ошибка подключения:\n";
      echo $e->getMessage();
    }
  }

  $secure_data = fn($data) => strip_tags(htmlspecialchars($data));

  $get_post = fn($str_data, $default) => isset($_POST[$str_data]) ? $_POST[$str_data] : $default;

  $get_GET = fn($str_data, $default) => isset($_GET[$str_data]) ? $_GET[$str_data] : $default;

  function getPostParam($key, $default) {
    global $data, $secure_data;
    if (isset($data[$key])) {
      return $secure_data($data[$key]);
    } else {
      return $default;
    }
  }

  function money_to_num($str) {
    // $str = substr($str, 0, strlen($str) - 1);
    $str = str_replace(' ', '', $str);
    $str = str_replace(chr(194), '', $str);
    $str = str_replace(chr(160), '', $str);
    return floatval($str);
  }

  function get_date($date_str) {
    $timeZone = 'T';
    $dateTime = new DateTime($date_str);
    $dateTime->setTimeZone(new DateTimeZone($timeZone));
    $dateTime->add(new DateInterval('PT10H'));
    return $dateTime;
  }

  function get_pickup_times($work_time_start, $work_time_end) {
    $interval = 30;
    $date_work_time_end = new DateTime($work_time_end);
    $work_time_end_hour = $date_work_time_end->format('H');
    $work_time_end_minutes = $date_work_time_end->format('i');
    $date_work_time_start = new DateTime($work_time_start);
    $result = [];
    while ($date_work_time_start->format('H') < $work_time_end_hour) {
      $date_work_time_start->add(new DateInterval('PT' . $interval . 'M'));
      $result[] = $date_work_time_start->format('H:i');
      if ($work_time_end_hour - $date_work_time_start->format('H') <= 1 && abs($work_time_end_minutes - $date_work_time_start->format('H')) <= $interval) {
        break;
      }
    }
    return $result;
  }

  $MAX_PRICE = 500000;
  $MAX_QUANTITY_IN_STOCK = 100;

  const MAX_ADDED_BALANCE = 50000;

  const MAX_MAKE_ORDER_DAYS = 15;

  const MAX_ORDERS_FOR_TIME = 10;

  const DELIVERY_PRICE_ADD = 700;

  const COOLDOWN_MAKE_ORDER = 2;

  function query_escape($data) {
    return strip_tags(htmlspecialchars($data));
  }

  function get_client_products_price() {
    global $client, $conn;
    $query = pg_query_params($conn, 'SELECT SUM(products.price * client_products.quantity) FROM client_products INNER JOIN products ON client_products.product_id = products.id INNER JOIN clients ON clients.id = client_products.client_id WHERE client_id = $1', Array($client->id));
    $obj = pg_fetch_object($query);
    return $obj->sum;
  }

  function get_client_products_bonuses() {
    global $client, $conn;
    $query = pg_query_params($conn, 'SELECT SUM(products.additional_bonus_count * client_products.quantity) FROM client_products INNER JOIN products ON client_products.product_id = products.id INNER JOIN clients ON clients.id = client_products.client_id WHERE client_id = $1', Array($client->id));
    $obj = pg_fetch_object($query);
    return $obj->sum;
  }

  function get_order_price($order_id) {
    global $conn;
    $query = pg_query_params($conn, 'SELECT SUM(products.price * products_in_orders.quantity) FROM products_in_orders INNER JOIN products ON products_in_orders.product_id = $1 INNER JOIN orders ON orders.id = products_in_orders.order_id WHERE orders.id = $1', Array($order_id));
    $obj = pg_fetch_object($query);
    return $obj->sum;
  }

  function get_order_bonuses($order_id) {
    global $conn;
    $query = pg_query_params($conn, 'SELECT SUM(products.additional_bonus_count * products_in_orders.quantity) FROM products_in_orders INNER JOIN products ON products_in_orders.product_id = $1 INNER JOIN orders ON orders.id = products_in_orders.order_id WHERE orders.id = $1', Array($order_id));
    $obj = pg_fetch_object($query);
    return $obj->sum;
  }

  function get_price_with_delivery($price) {
    if ($pirce <= DELIVERY_PRICE_ADD) {
      $price += ($price / 2);
    } else {
      $price += DELIVERY_PRICE_ADD;
    }
    return $price;
  }

  function get_price_delivery($price) {
    return $price <= DELIVERY_PRICE_ADD ? ($price / 2) : DELIVERY_PRICE_ADD;
  }

  function get_price_discount($price) {
    global $client;
    $bonus_count = money_to_num($client->bonus_count);
    if ($bonus_count > ($price / 2)) {
      $discount = ($price / 2);
    } else {
      $discount = $bonus_count;
    }
    return $discount;
  }

  function order_add($order_id, $contact_name, $contact_email, $contact_phone, $way_to_receive, $payment_method, $delivery_address, $point_of_issue, $date_of_receipt, $price, $receipt_time) {
    global $client, $conn;

    $order_number = $order_id;
    $client_id = $client->id;
    // print(gettype($way_to_receive));
    // print(gettype($payment_method));
    // print(gettype($point_of_issue));
    $way_to_receive_id = db_get_id_params('ways_to_receive', 'way_to_receive_name', $way_to_receive);
    $payment_method_id = db_get_id_params('payment_methods', 'payment_method_name', $payment_method);
    $point_of_issue_id = (gettype($point_of_issue) == 'string') ? db_get_id_params('points_of_issue', 'address', $point_of_issue) : 1;
    $reg_date_obj = get_date('');
    $reg_date = $reg_date_obj->format('Y-m-dTH:i:00');
    $reg_time = $reg_date_obj->format('Y-m-d H:i:00');
    $order_status_id = db_get_id_params('order_statuses', 'order_status_name', 'В процессе подтверждения оператором');

    $query_insert_order = pg_query_params($conn, 'INSERT INTO orders(contact_name, order_number, contact_email, contact_phone, client_id, way_to_receive_id, payment_method_id, delivery_address, point_of_issue_id, reg_date, date_of_receipt, actual_date_of_receipt, order_status_id, price, reg_time, receipt_time, actual_receipt_time) VALUES ($1, $12, $2, $3, $4, $5, $6, $7, $8, $9, $10, $13, $11, $14, $15, $16, $17)', Array($contact_name, $contact_email, $contact_phone, $client_id, $way_to_receive_id, $payment_method_id, $delivery_address, $point_of_issue_id, $reg_date, $date_of_receipt, $order_status_id, $order_number, $date_of_receipt, $price, $reg_time, $receipt_time, $receipt_time));

    $query_new_order = pg_query($conn, "SELECT MAX(id) FROM orders");
    $new_order = pg_fetch_object($query_new_order);

    return $new_order->max;
  }

  function order_delete($order_id) {
    global $conn;

    $query_delete_products_in_order = pg_query_params($conn, 'DELETE FROM products_in_orders WHERE order_id = $1', Array($order_id));

    $query_delete_order = pg_query_params($conn, 'DELETE FROM orders WHERE id = $1', Array($order_id));
  }

  function order_add_client_products($order_id, $client_products) {
    global $client, $conn;

    $client_id = $client->id;

    // $order_products = [];

    foreach ($client_products as $id => $client_product) {
      $product_id = $client_product['product_id'];
      $quantity = $client_product['quantity'];

      $query_product_price = pg_query_params($conn, "SELECT price FROM products WHERE id = $1", Array($product_id));
      $product_price = pg_fetch_object($query_product_price);
      $price = $product_price->price;

      $query_new_product_of_order = pg_query_params($conn, 'INSERT INTO products_in_orders(order_id, product_id, quantity, price) VALUES ($1, $2, $3, $4)', Array($order_id, $product_id, $quantity, $price));
      // $new_product_of_order = pg_fetch_assoc($query_new_product_of_order);
      // $order_products[$new_product_of_order['id']] = $new_product_of_order;
    }

    // return $order_products;
  }

  $conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

  if (!$conn) {
    die('Could not connect');
  }

  require 'insert_db.php';

  session_start();

  require 'set_user.php';
?>