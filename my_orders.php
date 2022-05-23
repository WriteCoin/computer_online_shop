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

  <div class="layer-index">

    <?php if (!pg_num_rows($query_orders)) : ?>
      <p><i>У вас пока нет заказов</i></p>
    <?php endif ?>

    <br>

    <?php 
      while ($order_client = pg_fetch_object($query_orders)) :
        $query_products = pg_query_params($conn, 'SELECT * FROM products_in_orders WHERE order_id = $1', Array($order_client->id));

        $query_way_to_receive = pg_query($conn, "SELECT * FROM ways_to_receive WHERE id = $order_client->way_to_receive_id");
        $way_to_receive = pg_fetch_object($query_way_to_receive);

        $query_payment_method = pg_query($conn, "SELECT * FROM payment_methods WHERE id = $order_client->payment_method_id");
        $payment_method = pg_fetch_object($query_way_to_receive);

        $query_point_of_issue = pg_query($conn, "SELECT * FROM points_of_issue WHERE id = $order_client->point_of_issue_id");
        if (!$query_point_of_issue) {
          $point_of_issue = pg_fetch_object($query_way_to_receive);
        }

        $query_order_status = pg_query($conn, "SELECT * FROM order_statuses WHERE id = $order_client->order_status_id");
        $order_status = pg_fetch_object($query_way_to_receive);

    ?>
      <div class="layer">
        <form class="form-product" method="post">
          <h3>Заказ № <?= $order_client->order_number ?></h3>

          <input type="hidden" name='id' value='<?= $order_client->id ?>'>

          <p><b>Контактные данные:</b>
            <table class="table">
              <tr>
                <td>Имя Фамилия Отчество</td>
                <td><?= $order_client->contact_name ?></td>
              </tr>
              <tr>
                <td>E-mail</td>
                <td><?= $order_client->contact_email ?></td>
              </tr>
              <tr>
                <td>Номер мобильного телефона</td>
                <td><?= $order_client->contact_phone ?></td>
              </tr>
            </table>
          </p>

          <p><b>Данные заказа:</b>
            <table class="table">
              <tr>
                <td>Способ получения</td>
                <td><?= $way_to_receive->way_to_receive_name ?></td>
              </tr>
              <tr>
                <td>Способ оплаты</td>
                <td><?= $payment_method->payment_method_name ?></td>
              </tr>
              <tr>
                <td>Адрес доставки</td>
                <td><?= $order_client->delivery_address ?></td>
              </tr>
              <?php if (isset($point_of_issue)) : ?>
                <tr>
                  <td>Пункт выдачи заказа</td>
                  <td><?= $point_of_issue->address . ' (' . $point_of_issue->name . ') ' . $point_of_issue->work_time_start . '-' . $point_of_issue->work_time_end ?></td>
                </tr>
              <?php endif ?>
              <tr>
                <td>Дата регистрации заказа</td>
                <td><?= $order_client->reg_date ?></td>
              </tr>
              <tr>
                <td>Дата получения заказа</td>
                <td><?= $order_client->date_of_receipt ?></td>
              </tr>
              <tr>
                <td>Фактическая дата получения заказа</td>
                <td><?= $order_client->actual_date_of_receipt ?></td>
              </tr>
              <tr>
                <td>Статус заказа (устанавливается оператором)</td>
                <td><?= $order_status->order_status_name ?></td>
              </tr>
            </table>
          </p>

          <p><b>Цена заказа: </b><?= $order_client->price ?></p>

          <details>
            <summary>Чек</summary>
            <table class="table">
              <?php while ($order_product = pg_fetch_object($query_products)) : 
                $query_product_name = pg_query_params($conn, "SELECT product_name FROM products WHERE id = $1", Array($order_product->product_id));
                $product_name = ($query_product_name) ? pg_fetch_object($query_product_name)->name : '';
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
                    <?= $order_product->price ?>
                  </td>
                </tr>
              <?php endwhile ?>
            </table>
          </details>

          <button class="btn" type="submit" formaction="refund.php">Вернуть заказ</button>
        </form>
      </div>
    <?php endwhile ?>
  </div>
</div>


<?php require 'footer.php'; ?>