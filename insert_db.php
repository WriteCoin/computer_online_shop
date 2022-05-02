<?php
  function db_get_id($table, $key, $value) {
    global $conn;
    if (gettype($value) == 'string') {
      $value = "'$value'";
    }
    $query = pg_query($conn, "SELECT id FROM $table WHERE $key = $value");
    // echo 'get id: ' . "SELECT id FROM $table WHERE $key = $value;<br>";
    return pg_fetch_object($query)->id;
  }

  function db_clear($table) {
    global $conn;
    $query = pg_query($conn, "DELETE FROM $table");
    return $query;
  }

  function db_is_clear($table) {
    global $conn;
    $query = pg_query($conn, "SELECT * FROM $table");
    return !(pg_fetch_object($query));
  }

  function add_subcategory($name, $category_name) {
    global $conn, $subcategories_table, $categories_table;
    // echo 'add subcategory: ' . "INSERT INTO $subcategories_table(subcategory_name, category_id) VALUES ('$name', " . db_get_id($categories_table, 'category_name', $category_name) . ");<br>";
    return pg_query($conn, "INSERT INTO $subcategories_table(subcategory_name, category_id) VALUES ('$name', " . db_get_id($categories_table, 'category_name', $category_name) . ");");
  }

  function add_property_type($name, $measurement_unit_name, $data_type_name, $subcategory_name) {
    global $conn, $property_types_table, $measurement_units_table, $data_types_table, $subcategories_table;
    return pg_query($conn, "INSERT INTO $property_types_table(property_name, measurement_unit_id, data_type_id, subcategory_id) VALUES ('$name', " . db_get_id($measurement_units_table, 'measurement_unit_name', $measurement_unit_name) . ", " . db_get_id($data_types_table, 'data_type_name', $data_type_name) . ", " . db_get_id($subcategories_table, 'subcategory_name', $subcategory_name) . ");");
  }

  function add_product($name, $desc, $subcategory_name, $price, $quantity_in_stock, $additional_bonus_count) {
    global $conn, $subcategories_table, $products_table;
    return pg_query($conn, "INSERT INTO $products_table(product_name, product_desc, subcategory_id, price, quantity_in_stock, additional_bonus_count) VALUES ('$name', '$desc', " . db_get_id($subcategories_table, 'subcategory_name', $subcategory_name) . ", '$price', $quantity_in_stock, $additional_bonus_count);");
  }

  $property_types_table = 'property_types';
  $data_types_table = 'data_types';
  $measurement_units_table = 'measurement_units';
  $subcategories_table = 'subcategories';
  $categories_table = 'categories';
  $products_table = 'products';

  $personal_computers_name = 'Персональные компьютеры';
  $laptops_name = 'Ноутбуки';
  $software_name = 'Программное обеспечение';

  $processors_name = 'Процессоры';
  $motherboards_name = 'Материнские платы';
  $videocards_name = 'Видеокарты';
  $ram_name = 'Оперативная память';
  $enclosures_name = 'Корпуса';
  $ssd_drives_name = 'SSD накопители';
  $hard_drives_name = 'Жесткие диски';

  $monitors_name = 'Мониторы';
  $keyboards_name = 'Клавиатуры';
  $mouses_name = 'Мыши';
  $mouse_pads_name = 'Коврики для мыши';
  $webcams_name = 'Веб-камеры';
  $headphones_name = 'Наушники';

  if (db_is_clear($property_types_table) && db_is_clear($data_types_table) && db_is_clear($measurement_units_table) && db_is_clear($subcategories_table) && db_is_clear($categories_table)) {

    pg_query($conn, "INSERT INTO $categories_table(category_name) VALUES
      ('Компьютеры, ноутбуки и ПО'),
      ('Комплектующие для ПК'),
      ('Периферия и аксессуары');
    ");

    add_subcategory($personal_computers_name, 'Компьютеры, ноутбуки и ПО');
    add_subcategory($laptops_name, 'Компьютеры, ноутбуки и ПО');
    add_subcategory($software_name, 'Компьютеры, ноутбуки и ПО');

    add_subcategory($processors_name, 'Комплектующие для ПК');
    add_subcategory($motherboards_name, 'Комплектующие для ПК');
    add_subcategory($videocards_name, 'Комплектующие для ПК');
    add_subcategory($ram_name, 'Комплектующие для ПК');
    add_subcategory($enclosures_name, 'Комплектующие для ПК');
    add_subcategory($ssd_drives_name, 'Комплектующие для ПК');
    add_subcategory($hard_drives_name, 'Комплектующие для ПК');

    add_subcategory($monitors_name, 'Периферия и аксессуары');
    add_subcategory($keyboards_name, 'Периферия и аксессуары');
    add_subcategory($mouses_name, 'Периферия и аксессуары');
    add_subcategory($mouse_pads_name, 'Периферия и аксессуары');
    add_subcategory($webcams_name, 'Периферия и аксессуары');
    add_subcategory($headphones_name, 'Периферия и аксессуары');

    pg_query($conn, "INSERT INTO $measurement_units_table(measurement_unit_name) VALUES
      ('мес.'),
      ('МГц'),
      ('МБ'),
      ('ГБ'),
      ('ТБ'),
      ('Гбит/с'),
      ('мм'),
      ('″'),
      ('Гц'),
      ('ppi'),
      ('Кд/м²'),
      ('%'),
      ('Мп'),
      ('Вт*ч'),
      ('ч'),
      ('Вт'),
      ('кг'),
      ('г'),
      ('°'),
      ('Мбайт/сек'),
      ('об/мин'),
      (''),
      ('dpi'),
      ('дБ'),
      ('шт');
    ");

    pg_query($conn, "INSERT INTO $data_types_table(data_type_name) VALUES
      ('string'),
      ('integer'),
      ('real'),
      ('boolean');
    ");

    add_property_type('ПК: Гарантия', 'мес.', 'integer', $personal_computers_name);
    add_property_type('Линейка', '', 'string', $personal_computers_name);
    add_property_type('Форм-фактор корпуса', '', 'string', $personal_computers_name);
    add_property_type('ПК: Основной цвет корпуса', '', 'string', $personal_computers_name);
    add_property_type('Операционная система', '', 'string', $personal_computers_name);
    add_property_type('Модель процессора', '', 'string', $personal_computers_name);
    add_property_type('Количество ядер процессора', '', 'integer', $personal_computers_name);
    add_property_type('Частота процессора', 'МГц', 'integer', $personal_computers_name);
    add_property_type('Сокет материнской платы', '', 'string', $personal_computers_name);
    add_property_type('Общее количество слотов оперативной памяти', '', 'integer', $personal_computers_name);
    add_property_type('Общий объем оперативной памяти', 'ГБ', 'integer', $personal_computers_name);
    add_property_type('Тип видеокарты', '', 'string', $personal_computers_name);
    add_property_type('Модель видеокарты', '', 'string', $personal_computers_name);
    add_property_type('Конфигурация накопителей', '', 'string', $personal_computers_name);
    add_property_type('ПК Габариты: длина', 'мм', 'integer', $personal_computers_name);
    add_property_type('ПК Габариты: ширина', 'мм', 'integer', $personal_computers_name);
    add_property_type('ПК Габариты: высота', 'мм', 'integer', $personal_computers_name);

    add_property_type('Ноутбук: Гарантия', 'мес.', 'integer', $laptops_name);
    add_property_type('Модель ноутбука', '', 'string', $laptops_name);
    add_property_type('Операционная система ноутбука', '', 'string', $laptops_name);
    add_property_type('Конструктивное исполнение', '', 'string', $laptops_name);
    add_property_type('Подсветка клавиш', '', 'boolean', $laptops_name);
    add_property_type('Диагональ экрана (дюйм)', "″", 'real', $laptops_name);
    add_property_type('Разрешение экрана', '', 'string', $laptops_name);
    add_property_type('Максимальная частота обновления экрана', 'Гц', 'integer', $laptops_name);
    add_property_type('Плотность пикселей', 'ppi', 'integer', $laptops_name);
    add_property_type('Производитель процессора', '', 'string', $laptops_name);
    add_property_type('Линейка процессора', '', 'string', $laptops_name);
    add_property_type('Количество ядер процессора ноутбука', '', 'string', $laptops_name);
    add_property_type('Максимальное число потоков', '', 'integer', $laptops_name);
    add_property_type('Объем оперативной памяти', 'ГБ', 'integer', $laptops_name);
    add_property_type('Максимальный объем оперативной памяти', 'ГБ', 'integer', $laptops_name);
    add_property_type('Вид графического ускорителя', '', 'string', $laptops_name);
    add_property_type('Общий объем твердотельных накопителей (SSD)', 'ГБ', 'integer', $laptops_name);
    add_property_type('Общий объем жестких дисков (HDD)', 'ГБ', 'integer', $laptops_name);
    add_property_type('Веб-камера', 'Мп', 'integer', $laptops_name);
    add_property_type('Порт Ethernet', 'Гбит/с', 'string', $laptops_name);
    add_property_type('Емкость аккумулятора', 'Вт*ч', 'integer', $laptops_name);
    add_property_type('Приблизительное время автономной работы', 'ч', 'integer', $laptops_name);
    add_property_type('Выходная мощность адаптера питания', 'Вт', 'integer', $laptops_name);
    add_property_type('Ноутбук Габариты: Глубина', 'мм', 'integer', $laptops_name);
    add_property_type('Ноутбук Габариты: Ширина', 'мм', 'integer', $laptops_name);
    add_property_type('Ноутбук Габариты: Толщина', 'мм', 'real', $laptops_name);
    add_property_type('Вес ноутбука', 'кг', 'real', $laptops_name);

    add_property_type('ПО: Страна-производитлеь', '', 'string', $software_name);
    add_property_type('ПО: Модель', '', 'string', $software_name);
    add_property_type('ПО: Состав программ', '', 'string', $software_name);
    add_property_type('ПО: Язык', '', 'string', $software_name);
    add_property_type('ПО: Тип поставки', '', 'string', $software_name);
    add_property_type('ПО: Количество лицензий', '', 'integer', $software_name);
    add_property_type('ПО: Срок действия лицензии', '', 'string', $software_name);
    add_property_type('ПО: Поддерживаемые операционные системы', '', 'string', $software_name);
    add_property_type('ПО: Минимальный объем оперативной памяти', 'ГБ', 'real', $software_name);
    add_property_type('ПО: Свободное пространство на жестком диске', 'ГБ', 'real', $software_name);

    add_property_type('Процессор: Гарантия', 'мес.', 'integer', $processors_name);
    add_property_type('Процессор: Страна-производитель', '', 'string', $processors_name);
    add_property_type('Процессор: Модель', '', 'string', $processors_name);
    add_property_type('Сокет', '', 'string', $processors_name);
    add_property_type('Количество производительных ядер', '', 'integer', $processors_name);
    add_property_type('Процессор: Максимальное число потоков', '', 'integer', $processors_name);
    add_property_type('Базовая частота процессора', 'МГц', 'integer', $processors_name);
    add_property_type('Максимально поддерживаемый объем памяти', 'ГБ', 'integer', $processors_name);
    add_property_type('Поддержка 64-битного набора команд', '', 'string', $processors_name);

    add_property_type('Материнская плата: Гарантия', 'мес.', 'integer', $motherboards_name);
    add_property_type('Материнская плата: Страна-производитель', '', 'string', $motherboards_name);
    add_property_type('Модель материнской платы', '', 'string', $motherboards_name);
    add_property_type('Форм-фактор', '', 'string', $motherboards_name);
    add_property_type('Сокет процессора', '', 'string', $motherboards_name);
    add_property_type('Чипсет', '', 'string', $motherboards_name);
    add_property_type('Количество слотов памяти', '', 'integer', $motherboards_name);
    add_property_type('Максимальный объем памяти', 'ГБ', 'integer', $motherboards_name);
    add_property_type('Тип и количество портов SATA', '', 'string', $motherboards_name);
    add_property_type('Материнская плата: Версия PCI Express', '', 'string', $motherboards_name);
    add_property_type('Звук', '', 'string', $motherboards_name);
    add_property_type('Чипсет сетевого адаптера', '', 'string', $motherboards_name);

    add_property_type('Видеокарта: Гарантия', 'мес.', 'integer', $videocards_name);
    add_property_type('Видеокарта: Страна-производитель', '', 'string', $videocards_name);
    add_property_type('Объем видеопамяти', 'ГБ', 'integer', $videocards_name);
    add_property_type('Микроархитектура', '', 'string', $videocards_name);
    add_property_type('Максимальная температура процессора (C)', '°', 'integer', $videocards_name);
    add_property_type('Видеоразъемы', '', 'string', $videocards_name);
    add_property_type('Максимальное энергопотребление', 'Вт', 'real', $videocards_name);
    add_property_type('Рекомендуемый блок питания', 'Вт', 'integer', $videocards_name);
    add_property_type('Длина видеокарты', 'мм', 'integer', $videocards_name);

    add_property_type('Оперативная память: Гарантия', 'мес.', 'integer', $ram_name);
    add_property_type('Оперативная память: Страна-производитель', '', 'string', $ram_name);
    add_property_type('Оперативная память: Модель', '', 'string', $ram_name);
    add_property_type('Тип памяти', '', 'string', $ram_name);
    add_property_type('Форм-фактор памяти', '', 'string', $ram_name);
    add_property_type('Тактовая частота', 'МГц', 'integer', $ram_name);
    add_property_type('Конструкция: высота', 'мм', 'integer', $ram_name);

    add_property_type('Корпус: Гарантия', 'мес.', 'integer', $enclosures_name);
    add_property_type('Корпус: Страна-производитель', '', 'string', $enclosures_name);
    add_property_type('Корпус: Модель', '', 'string', $enclosures_name);
    add_property_type('Типоразмер корпуса', '', 'string', $enclosures_name);
    add_property_type('Ориентация материнской платы', '', 'string', $enclosures_name);
    add_property_type('Корпус Габариты: Длина', 'мм', 'integer', $enclosures_name);
    add_property_type('Корпус Габариты: Ширина', 'мм', 'integer', $enclosures_name);
    add_property_type('Корпус Габариты: Высота', 'мм', 'integer', $enclosures_name);
    add_property_type('Вес корпуса', 'кг', 'real', $enclosures_name);
    add_property_type('Основной цвет корпуса', '', 'string', $enclosures_name);
    add_property_type('Материал корпуса', '', 'string', $enclosures_name);
    add_property_type('Толщина металла', 'мм', 'real', $enclosures_name);
    add_property_type('Фиксация боковых панелей', '', 'string', $enclosures_name);
    add_property_type('Комплектация', '', 'string', $enclosures_name);

    add_property_type('SSD: Гарантия', 'мес.', 'integer', $ssd_drives_name);
    add_property_type('SSD: Страна-производитель', '', 'string', $ssd_drives_name);
    add_property_type('Тип SSD', '', 'string', $ssd_drives_name);
    add_property_type('Модель SSD', '', 'string', $ssd_drives_name);
    add_property_type('Объем накопителя', 'ГБ', 'integer', $ssd_drives_name);
    add_property_type('Физический интерфейс', '', 'string', $ssd_drives_name);
    add_property_type('Максимальная скорость последовательной записи', 'Мбайт/сек', 'integer', $ssd_drives_name);
    add_property_type('Максимальная скорость последовательного чтения', 'Мбайт/сек', 'integer', $ssd_drives_name);
    add_property_type('SSD Габариты: Ширина', 'мм', 'real', $ssd_drives_name);
    add_property_type('SSD Габариты: Длина', 'мм', 'real', $ssd_drives_name);
    add_property_type('SSD Габариты: Толщина (мм)', 'мм', 'real', $ssd_drives_name);
    add_property_type('SSD Габариты: Вес', 'г', 'integer', $ssd_drives_name);

    add_property_type('Жесткий диск: Гарантия', 'мес.', 'integer', $hard_drives_name);
    add_property_type('Жесткий диск: Страна-производитель', '', 'string', $hard_drives_name);
    add_property_type('Жесткий диск: Модель', '', 'string', $hard_drives_name);
    add_property_type('Объем HDD', 'ТБ', 'real', $hard_drives_name);
    add_property_type('Объем кэш-памяти', 'МБ', 'integer', $hard_drives_name);
    add_property_type('Скорость вращения шпинделя', 'об/мин', 'integer', $hard_drives_name);
    add_property_type('Максимальная скорость передачи данных', 'Мбайт/сек', 'integer', $hard_drives_name);
    add_property_type('Интерфейс HDD', '', 'string', $hard_drives_name);
    add_property_type('HDD Габариты: Ширина', 'мм', 'real', $hard_drives_name);
    add_property_type('HDD Габариты: Длина', 'мм', 'real', $hard_drives_name);
    add_property_type('HDD Габариты: Толщина', 'мм', 'real', $hard_drives_name);

    add_property_type('Монитор: Гарантия', 'мес.', 'integer', $monitors_name);
    add_property_type('Монитор: Страна-производитель', '', 'string', $monitors_name);
    add_property_type('Монитор: Модель', '', 'string', $monitors_name);
    add_property_type('Монитор: Основной цвет', '', 'string', $monitors_name);
    add_property_type('Изогнутый экран', '', 'boolean', $monitors_name);
    add_property_type('Монитор: Диагональ экрана (дюйм)', '″', 'real', $monitors_name);
    add_property_type('Монитор: Максимальное разрешение', '', 'string', $monitors_name);
    add_property_type('Монитор: Соотношение сторон', '', 'string', $monitors_name);
    add_property_type('Монитор: Покрытие экрана', '', 'string', $monitors_name);
    add_property_type('Размер видимой части экрана', 'мм', 'string', $monitors_name);
    add_property_type('Монитор: Яркость', 'Кд/м²', 'integer', $monitors_name);
    add_property_type('Монитор: Плотность пикселей', 'ppi', 'integer', $monitors_name);
    add_property_type('Монитор: Максимальная частота обновления экрана', 'Гц', 'integer', $monitors_name);
    add_property_type('Монитор: Видео разъемы', '', 'string', $monitors_name);
    add_property_type('Монитор: Потребляемая мощность при работе', 'Вт', 'integer', $monitors_name);
    add_property_type('Монитор: Ширина без подставки', 'мм', 'real', $monitors_name);
    add_property_type('Монитор: Высота без подставки', 'мм', 'real', $monitors_name);
    add_property_type('Монитор: Вес без подставки', 'кг', 'real', $monitors_name);
    add_property_type('Монитор: Ширина с подставкой', 'мм', 'real', $monitors_name);
    add_property_type('Монитор: Минимальная высота с подставкой', 'мм', 'real', $monitors_name);
    add_property_type('Монитор: Максимальная высота с подставкой', 'мм', 'real', $monitors_name);
    add_property_type('Монитор: Толщина с подставкой', 'мм', 'real', $monitors_name);
    add_property_type('Монитор: Вес с подставкой', 'кг', 'real', $monitors_name);

    add_property_type('Клавиатура: Гарантия', 'мес.', 'integer', $keyboards_name);
    add_property_type('Клавиатура: Страна-производитель', '', 'string', $keyboards_name);
    add_property_type('Клавиатура: Модель', '', 'string', $keyboards_name);
    add_property_type('Тип клавиатуры', '', 'string', $keyboards_name);
    add_property_type('Клавиатура: Основной цвет', '', 'string', $keyboards_name);
    add_property_type('Низкопрофильные клавиши', '', 'boolean', $keyboards_name);
    add_property_type('Клавиатура: Подсветка клавиш', '', 'boolean', $keyboards_name);
    add_property_type('Общее количество клавиш', '', 'integer', $keyboards_name);
    add_property_type('Бесшумные клавиши', '', 'boolean', $keyboards_name);
    add_property_type('Цифровой блок', '', 'boolean', $keyboards_name);
    add_property_type('Клавиша функции (Fn)', '', 'boolean', $keyboards_name);
    add_property_type('Программируемые клавиши', '', 'boolean', $keyboards_name);
    add_property_type('Клавиатура: Материал корпуса', '', 'string', $keyboards_name);
    add_property_type('Защита от попадания воды', '', 'boolean', $keyboards_name);
    add_property_type('Формат клавиатуры', '', 'string', $keyboards_name);
    add_property_type('Клавиатура: Тип подключения', '', 'string', $keyboards_name);
    add_property_type('Интерфейс подключения', '', 'string', $keyboards_name);
    add_property_type('Клавиатура: Тип питания', '', 'string', $keyboards_name);
    add_property_type('Клавиатура: Комплектация', '', 'string', $keyboards_name);

    add_property_type('Мышь: Гарантия', 'мес.', 'integer', $mouses_name);
    add_property_type('Мышь: Страна-производитель', '', 'string', $mouses_name);
    add_property_type('Мышь: Тип', '', 'string', $mouses_name);
    add_property_type('Мышь: Модель', '', 'string', $mouses_name);
    add_property_type('Мышь: Основной цвет', '', 'string', $mouses_name);
    add_property_type('Мышь: Подсветка', '', 'boolean', $mouses_name);
    add_property_type('Мышь: Стилизация', '', 'boolean', $mouses_name);
    add_property_type('Общее количество кнопок', '', 'integer', $mouses_name);
    add_property_type('Программируемые кнопки', '', 'boolean', $mouses_name);
    add_property_type('Максимальное разрешение датчика', 'dpi', 'integer', $mouses_name);
    add_property_type('Тип сенсора мыши', '', 'string', $mouses_name);
    add_property_type('Режим работы датчика', 'dpi', 'integer', $mouses_name);
    add_property_type('Мышь: Материал изготовления', '', 'string', $mouses_name);
    add_property_type('Мышь: Материал покрытия', '', 'string', $mouses_name);
    add_property_type('Мышь: Хват', '', 'string', $mouses_name);
    add_property_type('Мышь: Бесшумные кнопки', '', 'boolean', $mouses_name);
    add_property_type('Мышь: Тип подключения', '', 'string', $mouses_name);
    add_property_type('Мышь: Интерфейс подключения', '', 'string', $mouses_name);
    add_property_type('Мышь: Длина кабеля', 'м', 'real', $mouses_name);
    add_property_type('Мышь: Ширина', 'мм', 'real', $mouses_name);
    add_property_type('Мышь: Высота', 'мм', 'real', $mouses_name);
    add_property_type('Мышь: Длина', 'мм', 'real', $mouses_name);
    add_property_type('Мышь: Вес', 'г', 'integer', $mouses_name);

    add_property_type('Коврик для мыши: Гарантия', 'мес.', 'integer', $mouse_pads_name);
    add_property_type('Коврик для мыши: Страна-производитель', '', 'string', $mouse_pads_name);
    add_property_type('Игровой коврик', '', 'boolean', $mouse_pads_name);
    add_property_type('Подставка под запястье', '', 'boolean', $mouse_pads_name);
    add_property_type('Коврик для мыши: Модель', '', 'string', $mouse_pads_name);
    add_property_type('Коврик для мыши: Основной цвет', '', 'string', $mouse_pads_name);
    add_property_type('Коврик для мыши: Подсветка', '', 'boolean', $mouse_pads_name);
    add_property_type('Размер коврика', '', 'string', $mouse_pads_name);
    add_property_type('Коврик для мыши: Материал покрытия', '', 'string', $mouse_pads_name);
    add_property_type('Коврик для мыши: Материал основания', '', 'string', $mouse_pads_name);
    add_property_type('Коврик для мыши: Длина', 'мм', 'real', $mouse_pads_name);
    add_property_type('Коврик для мыши: Ширина', 'мм', 'real', $mouse_pads_name);
    add_property_type('Коврик для мыши: Толщина', 'мм', 'real', $mouse_pads_name);
    add_property_type('Коврик для мыши: Беспроводная зарядка', '', 'boolean', $mouse_pads_name);

    add_property_type('Веб-камера: Гарантия', 'мес.', 'integer', $webcams_name);
    add_property_type('Веб-камера: Страна-производитель', '', 'string', $webcams_name);
    add_property_type('Веб-камера: Модель', '', 'string', $webcams_name);
    add_property_type('Веб-камера: Основной цвет', '', 'string', $webcams_name);
    add_property_type('Веб-камера: Тип матрицы', '', 'string', $webcams_name);
    add_property_type('Веб-камера: Число мегапикселей матрицы', 'Мп', 'real', $webcams_name);
    add_property_type('Веб-камера: Разрешение (видео)', '',' string', $webcams_name);
    add_property_type('Веб-камера: Угол обзора (градус)', '°', 'integer', $webcams_name);
    add_property_type('Веб-камера: Разрешение (фото)', '', 'string', $webcams_name);
    add_property_type('Веб-камера: Микрофон', '', 'boolean', $webcams_name);
    add_property_type('Веб-камера: Динамик', '', 'boolean', $webcams_name);
    add_property_type('Веб-камера: Тип подключения', '', 'string', $webcams_name);
    add_property_type('Веб-камера: Интерфейс', '', 'string', $webcams_name);
    add_property_type('Веб-камера: Длина кабеля', 'см', 'integer', $webcams_name);
    add_property_type('Веб-камера: Подсветка', '', 'boolean', $webcams_name);
    add_property_type('Веб-камера: Длина', 'мм', 'integer', $webcams_name);
    add_property_type('Веб-камера: Ширина', 'мм', 'integer', $webcams_name);
    add_property_type('Веб-камера: Толщина', 'мм', 'integer', $webcams_name);
    add_property_type('Веб-камера: Вес', 'г', 'integer', $webcams_name);

    add_property_type('Наушники: Гарантия', 'мес.', 'integer', $headphones_name);
    add_property_type('Наушники: Страна-производитель', '', 'string', $headphones_name);
    add_property_type('Наушники: Тип', '', 'string', $headphones_name);
    add_property_type('Наушники: Модель', '', 'string', $headphones_name);
    add_property_type('Наушники: Основной цвет', '', 'string', $headphones_name);
    add_property_type('Наушники: Метод крепления', '', 'string', $headphones_name);
    add_property_type('Наушники: тип акустического оформления', '', 'string', $headphones_name);
    add_property_type('Минимальная воспроизводимая частота', 'Гц', 'integer', $headphones_name);
    add_property_type('Максимальная воспроизводимая частота', 'Гц', 'integer', $headphones_name);
    add_property_type('Чувствительность', 'дБ', 'integer', $headphones_name);
    add_property_type('Наушники: Микрофон', '', 'boolean', $headphones_name);
    add_property_type('Система активного шумоподавления', '', 'boolean', $headphones_name);
    add_property_type('Регулятор громкости', '', 'boolean', $headphones_name);
    add_property_type('Функциональные клавиши', '', 'boolean', $headphones_name);
    add_property_type('Приложение для управления', '', 'boolean', $headphones_name);
    add_property_type('Управление со смартфона', '', 'boolean', $headphones_name);
    add_property_type('Поддвержка беспроводной зарядки', '', 'boolean', $headphones_name);
    add_property_type('Наушники для спорта', '', 'boolean', $headphones_name);

    add_product('Процессор AMD FX-4350 OEM', 'Четырехъядерный процессор AMD FX-4350 с AM3-сокетом построен на архитектуре Piledriver и ядре Vishera по 32-нанометровому техпроцессу. Из особенностей стоит отметить возможность увеличения тактовой частоты с 4200 до 4300 МГц в автоматическом режиме, а также разблокированный x21-множитель, позволяющий разгонять ядро еще больше. Этому не в меньшей мере способствует кэш третьего уровня, равный 8192 Кбайт, и максимальная температура корпуса – 71 °C при TDP на уровне 125 ватт.', $processors_name, '2599.00', 9, 500);

    
  }

  // db_clear($property_types_table);
  // db_clear($data_types_table);
  // db_clear($measurement_units_table);
  // db_clear($subcategories_table);
  // db_clear($categories_table);
?>