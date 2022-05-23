<?php
  $title = 'Пополнить баланс';
  require 'header.php';
  require 'connect.php';

  if (!isset($client)) {
    die('Неверный запрос');
  }

  // $full_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

  site_message();
?>

<h1>Пополнение баланса</h1>

<div class="container-index">
  <div class="layer">
    <p><b>Баланс: </b><?= $client->balance ?></p>
    <p>На <a href="index.php">главную</a>.</p>
    <br><br>
    <a href="#" onclick="history.replaceState({}, '', document.referrer); history.back(); return false;">Вернуться назад</a>
    <!-- <a href="#" onclick="history.back(); return false;">Вернуться назад</a> -->
    <!-- <form action="history_back.php" method="post">
      <input type="hidden" name="back-url" value="<?= $_SERVER['HTTP_REFERER'] ?>">
      <a id="back-link" href="#" >Вернуться в преисподнею</a>
    </form> -->
    <!-- <a href="#" id="back_link">Вернуться в преисподнею</a> -->
    <!-- <a href="history_back.php">Вернуться в преисподнею</a> -->
  </div>
  <div class="layer">
    <form action="add_balance.php" method="post">
      <div class="form-group">
        <label for="new_balance">Введите сумму:</label>
        <input type="number" name="new_balance" id="new_balance" step="0.01" min="1" max="<?= MAX_ADDED_BALANCE ?>" required value="1">
      </div>
      <button class="btn" type="submit">Пополнить</button>
    </form>
  </div>
</div>

<!-- <script>
  const $back_link = document.getElementById('back-link')
  console.log($back_link)
  if ($back_link) {
    $back_link.addEventListener('click', function() {
      // <?php
      //   $url = $_SERVER['REQUEST_URI'];
      //   $url = explode('?', $url);
      //   $url = $url[0]; 
      //   $_SERVER['HTTP_REFERER'] = $url;
      // ?> 
      // this.parentElement.submit()
    })
  }
</script> -->

<?php require 'footer.php'; ?>