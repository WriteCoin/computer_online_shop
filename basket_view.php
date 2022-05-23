<?php
  $title = "Корзина";

  require __DIR__ . '/header.php';
  require __DIR__ . '/connect.php';

  if (!isset($client)) {
    die('Неверный запрос');
  }

  // $products = $get_post('products', []);

  if (isset($_POST["products"])) {
    $products = $_POST["products"];
    foreach ($products as $product_id => $quantity) {
      pg_query_params($conn, 'UPDATE client_products SET quantity = $3 WHERE client_id = $1 AND product_id = $2', Array($client->id, $product_id, $quantity));
    }
  } else {
    $products = [];
  }

  $query_client_products = pg_query_params($conn, 'SELECT * FROM client_products WHERE client_id = $1', Array($client->id));

  $final_price = 0;
  $final_bonus_count = 0;

  $prices = [];

  site_message();
?>

<h1>Товары в корзине</h1>

<div class="container-index">
  <div class="layer">
    <p><b>Ваш Баланс: </b><?= $client->balance ?></p>
    <p><b>Ваши бонусы: </b><?= $client->bonus_count ?></p>
    <p>Вернуться на <a href="index.php">главную</a>.</p>
  </div>

  <div class="layer-index">

    <?php if (pg_num_rows($query_client_products)) : ?>
      <form action="order_make_form.php" method="post">
        <input type="hidden" id="final-price-input" name="final_price">
        <input type="hidden" id="final-bonus-input" name="final_bonus_count">
        <p><b>Итоговая цена заказа: </b><span class="final-price-text"></span></p>
        <p><b>Приобретаемые бонусы: </b><span class="final-bonus-text"></span></p>
        <button class="btn" type="submit">Оформить заказ</button>
        <br>
        <p><i>При оплате онлайн, пополните баланс</i></p>
      </form>
      <a href="add_balance_form.php">Пополнить баланс</a>
      <br>
    <?php else : ?>
      <p><i>Ваша корзина пуста</i></p>
    <?php endif ?>

    <br>

    <?php 
      while ($product_of_client = pg_fetch_object($query_client_products)) :
        $query_product = pg_query_params($conn, 'SELECT * FROM products WHERE id = $1', Array($product_of_client->product_id));
        $product = pg_fetch_object($query_product);
        $products[$product->id] = isset($products[$product->id]) ? $products[$product->id] : $product_of_client->quantity;
        $quantity = $products[$product->id];
        $price = money_to_num($product->price) * $quantity;
        $bonus_count = money_to_num($product->additional_bonus_count) * $quantity;
        $final_price += $price;
        $final_bonus_count += $bonus_count;
    ?>
      <div class="layer">
        <form class="form-product" method="post">
          <input type="hidden" name='id' value='<?= $product->id ?>'>

          <h3><a href="product_view.php?id=<?= $product->id ?>"><?= $product->product_name ?></a></h3>

          <?php $show_img = base64_encode($product->image_path); ?>
          <img src="data:image/jpeg;base64, <?php echo $show_img; ?>" alt="Изображение отсутствует">

          <p><b>Количество на складе: </b><?= $product->quantity_in_stock; ?></p>

          <p><b>Цена за единицу товара: </b><?= $product->price; ?></p>

          <div class="form-group">
            <label for="quantity">Количество:</label>
            <input type="number" name="products[<?= $product->id ?>]" id="quantity" step='1' min='1' max='<?= $product->quantity_in_stock ?>' required value='<?= $quantity ?>'>
          </div>

          <p>
            <button class="btn" type="submit" formaction="basket_view.php">Отправить</button>
            <button class="btn" type="reset">Отмена</button>
          </p>

          <p><b>Цена: </b><?= $price ?></p>

          <!-- <input type="hidden" name="prices[<?= $product->id ?>]" value="<?= $price ?>"> -->
          <!-- <?php $prices[$product->id] = $price; ?> -->

          <p><b>Бонусы: </b><?= $bonus_count ?></p>

          <button class="btn" type="submit" formaction="remove_basket.php" onclick="return window.confirm('Удалить товар из корзины?');">Удалить из корзины</button>
        </form>
      </div>
    <?php endwhile ?>
  </div>
</div>

<script>
  const span = document.querySelector('.final-price-text')
  if (span) {
    span.innerText = <?= $final_price ?>
  }
  const spanBonus = document.querySelector('.final-bonus-text')
  if (spanBonus) {
    spanBonus.innerText = <?= $final_bonus_count ?>
  }
  const priceInput = document.getElementById('#final-price-input')
  if (priceInput) {
    priceInput.setAttribute('value', <?= $final_price ?>)
  }
  const bonusInput = document.getElementById('#final-bonus-input')
  if (bonusInput) {
    bonusInput.setAttribute('value', <?= $final_bonus_count ?>)
  }
</script>

<?php require __DIR__ . '/footer.php'; ?>