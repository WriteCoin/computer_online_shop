<?php
  $title = 'Мои заказы';
  require 'header.php';
  require 'connect.php';

  if (!isset($client)) {
    die('Неверный запрос');
  }

  $query_orders = pg_query_params($conn, 'SELECT * FROM orders WHERE client_id = $1', Array($client->id));
?>

<h1>Список моих заказов</h1>

<div class="container-index">
  <div class="layer">
    <p>Вернуться на <a href="index.php">главную</a>.</p>
  </div>


  <?php if (!pg_num_rows($query_orders)) : ?>
    <div class="layer-index">
      <p><i>У вас пока нет заказов</i></p>
    </div>
  <?php endif ?>

  <br>

  <?php 
    while ($order_client = pg_fetch_object($query_orders)) :
      $query_products = pg_query_params($conn, 'SELECT * FROM products_in_orders WHERE order_id = $1', Array($order_client->id));

      $query_way_to_receive = pg_query($conn, "SELECT * FROM ways_to_receive WHERE id = $order_client->way_to_receive_id");
      $way_to_receive = pg_fetch_object($query_way_to_receive);

      $query_payment_method = pg_query($conn, "SELECT * FROM payment_methods WHERE id = $order_client->payment_method_id");
      $payment_method = pg_fetch_object($query_payment_method);

      $query_point_of_issue = pg_query($conn, "SELECT * FROM points_of_issue WHERE id = $order_client->point_of_issue_id");
      if (!$query_point_of_issue) {
        $point_of_issue = pg_fetch_object($query_point_of_issue);
      }

      $query_order_status = pg_query($conn, "SELECT * FROM order_statuses WHERE id = $order_client->order_status_id");
      $order_status = pg_fetch_object($query_order_status);

  ?>
    <div class="layer-index">
      <form class="form-product" method="post">
        <h2>Заказ № <?= $order_client->order_number ?></h2>
  
        <input type="hidden" name='id' value='<?= $order_client->id ?>'>
  
        <div class="layer">
          <h3>Контактные данные:</h3>
          <p>
            <table class="table">
              <tr>
                <td><b>Имя Фамилия Отчество</b></td>
                <td><?= $order_client->contact_name ?></td>
              </tr>
              <tr>
                <td><b>E-mail</b></td>
                <td><?= $order_client->contact_email ?></td>
              </tr>
              <tr>
                <td><b>Номер мобильного телефона</b></td>
                <td><?= $order_client->contact_phone ?></td>
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
                <td><?= $order_client->delivery_address ?></td>
              </tr>
              <?php if (isset($point_of_issue) && $way_to_receive->way_to_receive_name == 'Самовывоз') : ?>
                <tr>
                  <td><b>Пункт выдачи заказа</b></td>
                  <td><?= $point_of_issue->address . ' (' . $point_of_issue->name . ') ' . $point_of_issue->work_time_start . '-' . $point_of_issue->work_time_end ?></td>
                </tr>
              <?php endif ?>
              <tr>
                <td><b>Дата регистрации заказа</b></td>
                <td><?= $order_client->reg_date . ' ' . $order_client->reg_time ?></td>
              </tr>
              <tr>
                <td><b>Дата получения заказа</b></td>
                <td><?= $order_client->date_of_receipt . ' ' . $order_client->receipt_time ?></td>
              </tr>
              <tr>
                <td><b>Фактическая дата получения заказа</b></td>
                <td><?= $order_client->actual_date_of_receipt . ' ' . $order_client->actual_receipt_time ?></td>
              </tr>
              <tr>
                <td><b>Статус заказа (устанавливается оператором)</b></td>
                <td><?= $order_status->order_status_name ?></td>
              </tr>
            </table>
          </p>
        </div>
  
        <p><b>Цена заказа: </b><?= $order_client->price ?></p>
  
        <p>
          <details>
            <summary>Чек</summary>
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
                      Unnamed
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
  
        <button class="btn" type="submit" disabled formaction="refund.php">Вернуть заказ</button>
      </form>
    </div>
  <?php endwhile ?>
</div>


<?php require 'footer.php'; ?>