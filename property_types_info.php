<?php
  $title = 'Типы характеристик';
  require __DIR__ . '/header.php';
  require 'connect.php';
  require __DIR__ . '/side_info.php';

  $data = $_GET;
  if (isset($data['category'])) {
    $subcategory_name = htmlspecialchars($data['category']);
    $subcategory_query = pg_query_params($conn, 'SELECT * FROM subcategories WHERE subcategory_name = $1', Array($subcategory_name));
    $all_categories = false;
  } else {
    $subcategory_query = pg_query($conn, "SELECT * FROM subcategories");
    $all_categories = true;
  }
?>

<div class="container">
  <h1><?= $title; ?></h1>
  <div class="layer">
    <p>Вернуться на <a href="index.php">главную</a>.</p>
  </div>
  <div class="layer">

    <?php while ($subcategory = pg_fetch_object($subcategory_query)) : ?>
      <?php if ($all_categories) : ?>
        <details>
          <summary><b><a href="index.php?category=<?= $subcategory->subcategory_name; ?>"><?= $subcategory->subcategory_name; ?></a></b></summary>
      <?php else : ?>
        <p><b>Типы характеристик для категории <a href="index.php?category=<?= $subcategory->subcategory_name; ?>"><?= $subcategory->subcategory_name; ?></a></b></p>
      <?php endif; 
        $property_types_query = pg_query_params($conn, 'SELECT * FROM property_types WHERE subcategory_id = $1', Array($subcategory->id));
      ?>
        <table class="table">
          <thead>
            <tr>
              <td><b>Наименование</b></td>
              <td><b>Ед. измерения</b></td>
              <td><b>Тип данных</b></td>
            </tr>
          </thead>
          <tbody>
            <?php
              while ($property_type = pg_fetch_object($property_types_query)) :
                $measurement_unit_query = pg_query_params($conn, 'SELECT * FROM measurement_units WHERE id = $1', Array($property_type->measurement_unit_id));
                $measurement_unit = pg_fetch_object($measurement_unit_query);
        
                $data_type_query = pg_query_params($conn, 'SELECT * FROM data_types WHERE id = $1', Array($property_type->data_type_id));
                $data_type = pg_fetch_object($data_type_query);
            ?>
              <tr>
                <td><?= $property_type->property_name; ?></td>
                <td><?= $measurement_unit->measurement_unit_name; ?></td>
                <td><?= $data_type->data_type_name; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php if ($all_categories) : ?></details><?php endif; ?>
    <?php endwhile ?>

  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>