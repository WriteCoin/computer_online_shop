<?php
  require 'connect.php';

  $data = $_POST;

  if (isset($data['id'])) {
    $product_id = $data['id'];
    $product_name = $data['product_name'];
    $product_desc = $data['product_desc'];
    if (isset($data['image_path'])) {
      $image_path = $data['image_path'];
    } else {
      $image_path = '';
    }
    $subcategory_id = $data['subcategory_id'];
    $price = $data['price'];
    $quantity_in_stock = $data['quantity_in_stock'];
    $additional_bonus_count = $data['additional_bonus_count'];

    if (isset($data['add_product'])) {
      $query_new_product = pg_query_params($conn, 'INSERT INTO products(product_name, product_desc, image_path, subcategory_id, price, quantity_in_stock, additional_bonus_count) VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id;', Array($product_name, $product_desc, $image_path, $subcategory_id, $price, $quantity_in_stock, $additional_bonus_count));
      $product = pg_fetch_object($query_new_product);
      $product_id = $product->id;

      $query_property_types = pg_query_params($conn, 'SELECT * FROM property_types WHERE subcategory_id = $1', Array($subcategory_id));
      while ($property_type = pg_fetch_object($query_property_types)) {
        if (isset($data['property_type' . $property_type->id])) {
          $property_value = $data['property_type' . $property_type->id];
          $query_new_property = pg_query_params($conn, 'INSERT INTO properties(property_type_id, product_id, property_value) VALUES ($1, $2, $3);', Array($property_type->id, $product_id, $property_value));
        }
      }

      $_SESSION['op_message'] = 'Товар добавлен';
    } elseif (isset($data['update_product'])) {
      $query_old_product = pg_query_params($conn, 'SELECT * FROM products WHERE id = $1', Array($product_id));
      $old_product = pg_fetch_object($query_old_product);
      $old_subcategory_id = $old_product->subcategory_id;

      $query_new_product = pg_query_params($conn, 'UPDATE products SET product_name = $1, product_desc = $2, image_path = $3, subcategory_id = $4, price = $5, quantity_in_stock = $6, additional_bonus_count = $7;', array($product_name, $product_desc, $image_path, $subcategory_id, $price, $quantity_in_stock, $additional_bonus_count));

      $query_old_property_data = pg_query_params($conn, 'SELECT properties.id AS property_id, properties.property_type_id, properties.product_id, properties.property_value, property_types.subcategory_id FROM property_types INNER JOIN properties ON property_types.id = properties.property_type_id WHERE property_types.subcategory_id = $1 AND properties.product_id = $2;', Array($old_subcategory_id, $product_id));
      while ($property_data = pg_fetch_object($query_old_property_data)) {
        if (isset($data['property_type' . $property_data->property_type_id])) {
          $property_value = $data['property_type' . $property_data->property_type_id];

          $query_new_property_type_id = pg_query_params($conn, 'SELECT properties.id as property_id, property_types.id as property_type_id FROM property_types INNER JOIN properties ON property_types.id = properties.property_type_id WHERE subcategory_id = $1 AND product_id = $2 AND properties.id = $3;', Array($subcategory_id, $product_id, $property_data->property_id));
          $new_property_type_id = pg_fetch_object($query_new_property_type_id);

          $query_new_property = pg_query_params($conn, 'UPDATE properties SET property_type_id = $new_property_type_id->property_type_id, product_id = $product_id, property_value = $1 WHERE id = $2;', Array($property_value, $property_data->property_id));
        }
      }

      $_SESSION['op_message'] = 'Товар изменен';
    }
  } elseif (!isset($data['id'])) {
    $_SESSION['op_message_error'] = 'Нелепый сбой: id товара не найден.';
  }
  
  header('Location: index.php');
?>