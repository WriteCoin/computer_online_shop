<?php
  require 'connect.php';

  if (!isset($moderator)) {
    die('Неверный запрос');
  }

  $data = $_POST;

  foreach ($data as $key => $value) {
    echo $key . " : " . $value . "<br>";
  }

  if (isset($data['id'])) {
    $product_id = $secure_data($data['id']);
    $product_name = $secure_data($data['product_name']);
    $product_desc = $secure_data($data['product_desc']);
    
    $subcategory_id = $secure_data($data['subcategory_id']);
    $price = $secure_data($data['price']);
    $quantity_in_stock = $secure_data($data['quantity_in_stock']);
    $additional_bonus_count = $secure_data($data['additional_bonus_count']);

    if (isset($data['add_product'])) {
      $query_new_product = pg_query_params($conn, 'INSERT INTO products(product_name, product_desc, image_path, subcategory_id, price, quantity_in_stock, additional_bonus_count) VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id;', Array($product_name, $product_desc, $image_path, $subcategory_id, $price, $quantity_in_stock, $additional_bonus_count));
      $product = pg_fetch_object($query_new_product);
      $product_id = $product->id;

      // if (isset($data['tmp_image_name'])) {
      //   $image_path = "products/image_path$product_id";
      //   move_uploaded_file($data['tmp_image_name'], $image_path);
      //   $query_update_product = pg_query_params($conn, 'UPDATE products SET image_path = $1 WHERE id = $2', Array($image_path, $product_id));
      // } else {
      //   $image_path = '';
      // }

      $query_property_types = pg_query_params($conn, 'SELECT * FROM property_types WHERE subcategory_id = $1', Array($subcategory_id));
      while ($property_type = pg_fetch_object($query_property_types)) {
        if (isset($data['property_type' . $property_type->id])) {
          $property_value = $data['property_type' . $property_type->id];
          $query_new_property = pg_query_params($conn, 'INSERT INTO properties(property_type_id, product_id, property_value) VALUES ($1, $2, $3);', Array($property_type->id, $product_id, $property_value));
        }
      }

      $_SESSION['op_message'] = 'Товар добавлен';
    } elseif (isset($data['update_product'])) {
      // if (isset($data['tmp_image_name'])) {
      //   $filename = $data['tmp_image_name'];
      //   echo $filename;
      //   $image_path = "products/image_path$product_id" . $filename;
      //   echo $image_path;
      //   move_uploaded_file($filename, $image_path);
      // } else {
      //   $image_path = '';
      // }
      // echo $image_path;

      $image_path = '';
      $query_old_product = pg_query_params($conn, 'SELECT * FROM products WHERE id = $1', Array($product_id));
      $old_product = pg_fetch_object($query_old_product);
      $old_subcategory_id = $old_product->subcategory_id;

      $query_new_product = pg_query_params($conn, 'UPDATE products SET product_name = $1, product_desc = $2, image_path = $3, subcategory_id = $4, price = $5, quantity_in_stock = $6, additional_bonus_count = $7;', Array($product_name, $product_desc, $image_path, $subcategory_id, $price, $quantity_in_stock, $additional_bonus_count));

      $query_old_property_data = pg_query_params($conn, 'SELECT properties.id AS property_id, properties.property_type_id, properties.product_id, properties.property_value, property_types.subcategory_id FROM property_types INNER JOIN properties ON property_types.id = properties.property_type_id WHERE property_types.subcategory_id = $1 AND properties.product_id = $2;', Array($old_subcategory_id, $product_id));
      $query_new_property_types = pg_query_params($conn, 'SELECT * FROM property_types WHERE subcategory_id = $1', Array($subcategory_id));
      while ($property_data = pg_fetch_object($query_old_property_data)) {
        print_r($property_data);
        echo '<br>';

        if (isset($data['property_type' . $property_data->property_type_id])) {
          $query_new_property_type_id = pg_query_params($conn, 'SELECT properties.id as property_id, property_types.id as property_type_id FROM property_types INNER JOIN properties ON property_types.id = properties.property_type_id WHERE subcategory_id = $1 AND product_id = $2 AND properties.id = $3;', Array($subcategory_id, $product_id, $property_data->property_id));
          $new_property_type_id = pg_fetch_object($query_new_property_type_id);

          $property_value = $data['property_type' . $property_data->property_type_id];
          
          echo 'property_id: ' . $property_data->property_id . "<br>";
          echo 'property_type_id: ' . $new_property_type_id->property_type_id . "<br>";
          echo 'property_value: ' . $property_value . "<br>";

          $query_new_property = pg_query_params($conn, 'UPDATE properties SET property_type_id = $1, product_id = $2, property_value = $3 WHERE id = $4;', Array($new_property_type_id->property_type_id, $product_id, $property_value, $property_data->property_id));
        } else {
          $new_property_type = pg_fetch_object($query_new_property_types);

          $property_value = $data['property_type' . $new_property_type->id];
          
          echo 'property_id: ' . $property_data->property_id . "<br>";
          echo 'property_type_id: ' . $new_property_type->id . "<br>";
          echo 'property_value: ' . $property_value . "<br>";

          $query_new_property = pg_query_params($conn, 'UPDATE properties SET property_type_id = $1, property_value = $2 WHERE product_id = $3 AND id = $4', Array($new_property_type->id, $property_value, $product_id, $property_data->property_id));
        }
      }


      $_SESSION['op_message'] = 'Товар изменен';
    }
  } elseif (!isset($data['id'])) {
    $_SESSION['op_message_error'] = 'Нелепый сбой: id товара не найден.';
  }
  
  header('Location: index.php');
?>