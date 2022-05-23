<?php
  require 'connect.php';

  $title = "Товар";

  $id = $get_GET('id', '');

  if (!empty($id)) {
    $query_product = pg_query_params($conn, "SELECT * FROM products WHERE id = $1", Array($id));
    $product = pg_fetch_object($query_product);
    $title = empty($product) ? $title : $product->product_name;
  }

  require 'header.php';
?>

<h1>Обзор товара</h1>

<div class="container-index">
  <div class="layer">
    <p>На <a href="index.php">главную</a>.</p>
    <br><br>
    <a href="#" onclick="history.replaceState({}, '', document.referrer); history.back(); return false;">Вернуться назад</a>
  </div>

  <div class="layer-index">
    <?php if (empty($id)) : ?>
      <p><i>В вашем запросе отсутствует идентификатор товара</i></p>
    <?php else : ?>

    <?php if (empty($product)) : ?>
      <p><i>Нет данных о товаре</i></p>
    <?php else : ?>

    <div class="layer">
      <h4><?= $product->product_name; ?></h4>

      <details>
        <summary>Характеристики</summary>
        <div class="prop-groups">
        <?php
          $query_properties = pg_query_params($conn, 'SELECT * FROM properties WHERE product_id = $1', Array($product->id));
          while ($property = pg_fetch_object($query_properties)) : 
            $query_property_type = pg_query_params($conn, 'SELECT * FROM property_types WHERE id = $1', Array($property->property_type_id));
            $property_type = pg_fetch_object($query_property_type);

            $query_measurement_unit = pg_query_params($conn, 'SELECT * FROM measurement_units WHERE id = $1', Array($property_type->measurement_unit_id));
            $measurement_unit = pg_fetch_object($query_measurement_unit);

            $query_data_type = pg_query_params($conn, 'SELECT * FROM data_types WHERE id = $1', Array($property_type->data_type_id));
            $data_type = pg_fetch_object($query_data_type);

            if ($data_type->data_type_name == 'boolean') {
              if ($property->property_value) {
                $property_value = 'Да';
              } else {
                $property_value = 'Нет';
              }
            } else {
              $property_value = $property->property_value;
            }

            $property_key = 'property_type' . $property_type->id;
            if ($measurement_unit->measurement_unit_name == '') {
              $measurement_unit_name = '';
            } else {
              $measurement_unit_name = ', ' . $measurement_unit->measurement_unit_name;
            }
        ?>
          <p><b><?= $property_type->property_name; ?>: </b><?= $property_value; ?><i><?= $measurement_unit_name; ?></i></p>
        <?php endwhile ?>
        </div>
      </details>

      <p><b>Описание товара:</b></p><br>
      <p><?= $product->product_desc; ?></p>

      <p><b>Изображение товара:</b></p><br>
      <?php $show_img = base64_encode($product->image_path); ?>
      <img src="data:image/jpeg;base64, <?php echo $show_img; ?>" alt="Изображение отсутствует">

      <?php
        $subcategory_query = pg_query_params($conn, 'SELECT * FROM subcategories WHERE id = $1', Array($product->subcategory_id));
        $subcategory = pg_fetch_object($subcategory_query);
      ?>

      <p><b>Категория: </b><a href="index.php?category=<?= $subcategory->subcategory_name; ?>"><?= $subcategory->subcategory_name; ?></a></p>

      <p><b>Цена: </b><?= $product->price; ?></p>

      <p><b>Количество на складе: </b><?= $product->quantity_in_stock; ?></p>

      <p><b>Бонус: </b><?= $product->additional_bonus_count; ?></p>

    </div>

    <?php endif ?>

    <?php endif ?>
  </div>

</div>


<?php require 'footer.php'; ?>