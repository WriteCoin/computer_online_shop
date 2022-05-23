<?php
  require 'connect.php';

  // function get_pickup_times($work_time_start, $work_time_end) {
  //   $date_work_time_end = new DateTime($work_time_end);
  //   $work_time_end_hour = $date_work_time_end->format('H');
  //   $date_work_time_start = new DateTime($work_time_start);
  //   $result = [];
  //   while ($date_work_time_start->format('H') < $work_time_end_hour) {
  //     $date_work_time_start->add(new DateInterval('PT30M'));
  //     $result[] = $date_work_time_start->format('H:i');
  //   }
  //   return $result;
  // }

  $times = get_pickup_times('10:00', '19:30');
  foreach ($times as $time) {
    // echo $time . '<br>';
  }

  $arr = [];
  $str = count($arr) ? implode(', ', $arr) : '';
  $test_query = pg_query($conn, "SELECT * FROM points_of_issue WHERE id IN ($str)");
  while ($data = pg_fetch_object($test_query)) {
    print_r($data);
  }

?>