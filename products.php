<?php
  function getPostParam($key, $default) {
    global $data;
    if (isset($data[$key])) {
      return $data[$key];
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

  if ($is_add || $is_edit) {
    $subcategories_query = pg_query($conn, "SELECT * FROM subcategories");
    if (isset($data['subcategory_id'])) {
      $subcategory_id = $data['subcategory_id'];
      $query_subcategory = pg_query_params($conn, 'SELECT * FROM subcategories WHERE id = $1', Array($subcategory_id));
      $subcategory = pg_fetch_object($query_subcategory);
    } else {
      $subcategories_2query = pg_query($conn, "SELECT * FROM subcategories");
      $subcategory = pg_fetch_object($subcategories_2query);
    }
    
    $subcategory_name = $subcategory->subcategory_name;
    $query_property_types = pg_query_params($conn, 'SELECT * FROM property_types WHERE subcategory_id = $1', Array($subcategory->id));
    
    $id = getPostParam('id', '');
    $product_name = getPostParam('product_name', '');
    $product_desc = getPostParam('product_desc', '');
    $price = getPostParam('price', '0');
    $price = money_to_num($price);
    $quantity_in_stock = getPostParam('quantity_in_stock', '1');
    $additional_bonus_count = getPostParam('additional_bonus_count', '0');
    $additional_bonus_count = money_to_num($additional_bonus_count);
  }

  $MAX_PRICE = 500000;
  $MAX_QUANTITY_IN_STOCK = 100;
?>

<div class="layer-index">
  <h2><?= $title_product; ?></h2>

  <?php if ($is_add || $is_edit) : ?>
    <form class='change-product'>
      <input type="hidden" name="id" value="<?= $id; ?>">

      <div class="form-group">
        <label for="product_name">Название товара:</label>
        <input type="text" required name="product_name" id="product_name" value='<?= $product_name; ?>' placeholder="Ввведите название товара">
      </div>

      <?php if ($is_add) : ?>
        <input type="hidden" value="do_add_product" name="do_add_product">
      <?php elseif ($is_edit) : ?>
        <input type="hidden" value="do_edit_product" name="do_edit_product">
      <?php endif ?>
      
      <div class="form-group">
        <label for="subcategory_name">Категория товара:</label>
        <select id="subcategory_send" class="subcategory_send" data-action='index.php' name="subcategory_id" id="subcategory_name" required>
          <?php while ($subcategory = pg_fetch_object($subcategories_query)) : ?>
            <option value="<?= $subcategory->id; ?>" <?php if ($subcategory_name == $subcategory->subcategory_name) : ?> selected <?php endif ?> ><?= $subcategory->subcategory_name; ?></option>
          <?php endwhile ?>
        </select>
      </div>

      <div class="detail-product-props">
        <details>
          <summary>Характеристики</summary>
          <div class="prop-groups">
          <?php 
            while ($property_type = pg_fetch_object($query_property_types)) : 
              $query_measurement_unit = pg_query_params($conn, 'SELECT * FROM measurement_units WHERE id = $1', Array($property_type->measurement_unit_id));
              $measurement_unit = pg_fetch_object($query_measurement_unit);

              $query_data_type = pg_query_params($conn, 'SELECT * FROM data_types WHERE id = $1', Array($property_type->data_type_id));
              $data_type = pg_fetch_object($query_data_type);

              if ($id) {
                $query_property = pg_query_params($conn, 'SELECT * FROM properties WHERE product_id = $1 AND property_type_id = $2;', Array($id, $property_type->id));
                $property = pg_fetch_object($query_property);
                if ($property) {
                  if ($data_type->data_type_name == 'boolean') {
                    if ($property->property_value) {
                      $property_value = '1';
                    } else {
                      $property_value = '0';
                    }
                  } else {
                    $property_value = $property->property_value;
                  }
                } else {
                  $property_value = '';
                }
              } else {
                $property_value = '';
              }

              $property_key = 'property_type' . $property_type->id;
              if ($measurement_unit->measurement_unit_name == '') {
                $measurement_unit_name = '';
              } else {
                $measurement_unit_name = ', ' . $measurement_unit->measurement_unit_name;
              }
          ?>
            <div class="form-group">
              <label for="<?= $property_key; ?>"><?= $property_type->property_name; ?><?= $measurement_unit_name; ?></label>
              <?php if ($data_type->data_type_name == 'string') : ?>
                <input type="text" required id="<?= $property_key; ?>" name="<?= $property_key; ?>" value="<?= $property_value; ?>" required placeholder="Введите строку">
              <?php elseif ($data_type->data_type_name == 'integer') : ?>
                <input type="number" required id="<?= $property_key; ?>" name="<?= $property_key; ?>" value="<?= $property_value; ?>" required step='1' min='0' max='2147483647' placeholder="Введите число">
              <?php elseif ($data_type->data_type_name == 'real') : ?>
                <input type="number" required id="<?= $property_key; ?>" name="<?= $property_key; ?>" value="<?= $property_value; ?>" required step='0.01' min='0' max='2147483648' placeholder="Введите число (дробь)">
              <?php elseif ($data_type->data_type_name == 'boolean') : ?>

                <div class="radio-group">
                  <input type="radio" value="<?= $property_value; ?>" id="<?= $property_key; ?>" name="<?= $property_key; ?>" required>
                  <span>Да</span>
                </div>

                <div class="radio-group">
                  <input type="radio" value="<?= $property_value; ?>" id="<?= $property_key; ?>" name="<?= $property_key; ?>" required>
                  <span>Нет</span>
                </div>
              <?php endif ?>
            </div>
          <?php endwhile ?>
          </div>
        </details>
      </div>

      <div class="form-group">
        <label for="product_desc">Описание товара:</label>
        <textarea name="product_desc" id="product_desc" cols="30" rows="10" value='<?= $product_desc; ?>' placeholder="Введите описание товара"><?= $product_desc; ?></textarea>
      </div>

      <div class="form-group">
        <label for="price">Стоимость закупки:</label>
        <input type="number" name="price" id="price" step='0.01' min='1' max='<?= $MAX_PRICE; ?>' required value="<?= $price; ?>" placeholder="Введите стоимость товара">
      </div>

      <div class="form-group">
        <label for="quantity_in_stock">Количество на складе:</label>
        <input type="number" name="quantity_in_stock" id="quantity_in_stock" step='1' min='1' max='<?= $MAX_QUANTITY_IN_STOCK; ?>' required value="<?= $quantity_in_stock; ?>" placeholder="Введите количество на складе">
      </div>

      <div class="form-group">
        <label for="additional_bonus_count">Бонусы:</label>
        <input type="number" name="additional_bonus_count" id="additional_bonus_count" step='0.01' min='0' max='<?= $MAX_PRICE; ?>' required value="<?= $additional_bonus_count; ?>" placeholder="Введите количество бонусов">
      </div>

      <?php if ($is_add) : ?>
        <button class="btn" id='product-button1' name='add_product' type="submit" onClick="return window.confirm('Создать товар?');">Добавить товар</button>
      <?php elseif ($is_edit) : ?>
        <button class="btn" id='product-button1' name='update_product' type="submit" onClick="return confirm('Изменить товар?');">Изменить товар</button>
      <?php endif ?>
    </form>

  <?php else : ?>
    <?php while ($product = pg_fetch_object($products_query)) : ?>
      <div class="layer">
        <form class="form-product">

          <input type="hidden" name="id" value="<?= $product->id; ?>">

          <input type="hidden" name="product_name" value="<?= $product->product_name; ?>">
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
              <input type="hidden" name="<?= $property_key; ?>" value="<?= $property->id ?>">
              <p><b><?= $property_type->property_name; ?>: </b><?= $property_value; ?><i><?= $measurement_unit_name; ?></i></p>
            <?php endwhile ?>
            </div>
          </details>

          <input type="hidden" name="product_desc" value="<?= $product->product_desc; ?>">
          <p><b>Описание товара:</b></p><br>
          <p><?= $product->product_desc; ?></p>

          <?php
            $subcategory_query = pg_query_params($conn, 'SELECT * FROM subcategories WHERE id = $1', Array($product->subcategory_id));
            $subcategory = pg_fetch_object($subcategory_query);
          ?>

          <input type="hidden" name="subcategory_id" value="<?= $subcategory->id; ?>">
          <p><b>Категория: </b><a href="index.php?category=<?= $subcategory->subcategory_name; ?>"><?= $subcategory->subcategory_name; ?></a></p>

          <input type="hidden" name="price" value="<?= $product->price; ?>">
          <p><b>Цена: </b><?= $product->price; ?></p>

          <input type="hidden" name="quantity_in_stock" value="<?= $product->quantity_in_stock; ?>">
          <p><b>Количество на складе: </b><?= $product->quantity_in_stock; ?></p>

          <input type="hidden" name="additional_bonus_count" value="<?= $product->additional_bonus_count; ?>">
          <p><b>Бонус: </b><?= $product->additional_bonus_count; ?></p>

          <?php if (isset($client)) : ?>
            <button class="btn" id="product-button1" name="to_basket_product" type="submit">Добавить в корзину</button>
          <?php elseif (isset($moderator)) : ?>
            <button class="btn" id="product-button1" name="do_edit_product" onClick="alert('Изменение')">Изменить товар</button>
            <button class="btn" id="product-button2" name="do_delete_product" type="submit" onClick="return window.confirm('Вы действительно хотите удалить данный товар?');">Удалить товар</button>
          <?php endif ?>
        </form>
      </div>
    <?php endwhile; ?>
  <?php endif ?>

  <script type="text/javascript">
    const select = document.getElementById('subcategory_send');

    if (select) select.addEventListener('change', function() {
      const form = document.querySelector('.change-product');
      form.action = 'index.php';
      form.method = 'post'

      form.submit();
    })

    let confirmResult

    // const buttonOnSubmit = function(event) {
    //   if (!confirmResult) {
    //     event.preventDefault()
    //     window.history.back()
    //   }
    // }

    const buttonOnClick = function(event) {
      const form = this.parentElement
      let confirmStr
      let formAction
      let isDetail
      if (this.name === 'to_basket_product') {
        formAction = 'add_basket.php'
      } else if (this.name === 'do_edit_product') {
        formAction = 'index.php'
      } else if (this.name === 'do_delete_product') {
        formAction = 'delete_product.php'
        confirmStr = 'Вы действительно хотите удалить данный товар?'
      } else if (this.name === 'add_product' || this.name === 'update_product') {
        formAction = 'action_product.php'
        confirmStr = this.name === 'add_product' ? 'Создать товар?' : 'Изменить товар?'
        isDetail = true
      }

      if (confirmStr) {
        confirmResult = confirm(confirmStr)
        if (!confirmResult) {
          event.preventDefault()
          return
        }
      } else {
        confirmResult = true
      }

      if (isDetail) {
        const $details = document.querySelector('.detail-product-props details')
        if ($details) $details.setAttribute('open', 'open')
      }
      
      // event.preventDefault()
      // window.history.back()

      form.action = formAction
      form.method = 'post'
    }

    const button1 = document.querySelector('#product-button1');
    const button2 = document.querySelector('#product-button2');

    if (button1) {
      button1.onclick = buttonOnClick
      // button1.addEventListener("submit", buttonOnSubmit, true)
    }
    if (button2) {
      button2.onclick = buttonOnClick
      // button2.addEventListener("submit", buttonOnSubmit, true)
    }
  </script>

</div>