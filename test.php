<?php
  header('Refresh: 5');

  $time = '18:00';
  // settype($time, 'float');
  $work_time = preg_split('/:/', $time);
  // print_r($work_time);
  // echo gettype($work_time[0]);

  $today = getdate();
  // print_r(new DateTime());

  // $timeZone = 'Europe/Tallinn';
  
  function get_date($date_str) {
    $timeZone = 'T';
    $dateTime = new DateTime($date_str);
    $dateTime->setTimeZone(new DateTimeZone($timeZone));
    $dateTime->add(new DateInterval('PT10H'));
    return $dateTime;
  }

  $dateTime = get_date('');

  // print_r($dateTime);
  // echo "<br>";

  // function day_to_input($today) {
  //   $mon = ($today['mon'] < 10) ? '0' . $today['mon'] : $today['mon'];
  //   $day = ($today['mday'] < 10) ? '0' . $today['mday'] : $today['mday'];
  //   return $today['year'] . '-' . $mon . '-' . $day . 'T' ;
  // }

  // function days_add($date_str, $days, $format = 'Y-m-d') {
  //   $date = new DateTime($date_str);
  //   $date->add(new DateInterval('P' . $days . 'D'));
  //   return $date->format($format);
  // }

  // $today_str = day_to_input($today);
  // $newday_str = days_add($today_str, 30, 'Y-m-d[T]h:mm');

  // $current_date = new DateTime($today_str);
  // $new_date = clone $current_date;
  // $new_date->add(new DateInterval('P10D'));
  // $newday_str = $new_date->format('Y-m-d');
  // echo $today_str . '<br>';
  // echo $newday_str . '<br>';

  if (isset($_GET['datetime'])) {
    $datetime = $_GET['datetime'];
    // echo $datetime;
  } else {
    $datetime = $dateTime->format('Y-m-dTH:i');
  }
  echo $datetime;
?>

<form method="get">
  <input type="datetime-local" name="datetime" value="<?= $datetime ?>">
  <button type="submit">Отправить</button>
</form>
