<?php


// for ($i = 0; $i < 24; $i++) {
//     $si = $i < 10 ? '0' . $i : $i;
//     echo "delete from gold_backup where post_time between '2022-12-30 {$i}:00:00' and '2022-12-30 {$i}:29:59';" . PHP_EOL;
//     echo "delete from gold_backup where post_time between '2022-12-30 {$i}:30:00' and '2022-12-30 {$i}:59:59';" . PHP_EOL;
// }

// echo PHP_EOL;

// $date = '2022-12-30';
// $q = 0;
// for ($i = 0; $i < 24; $i++) {
//     for ($j = 0; $j < 6; $j++) {
//         $si = $i < 10 ? '0' . $i : $i;
//         $start = date('i:s', 10 * 60 * $j);
//         $end = date('i:s', 10 * 60 * ($j + 1) - 1);
//         // $sql = "delete from `gold_backup` where `post_time` <= '{$date} {$si}:{$end}'";
        
//         $sql = "insert into `gold_backup` select * from `gold` where `post_time` between '{$date} {$si}:{$start}' and '{$date} {$si}:{$end}'";
//         if ($i == 0 && $j == 0) {
//             echo $sql . PHP_EOL;
//         } else if ($i == 23 && $j == 5) {
//             echo PHP_EOL . $sql . PHP_EOL;
//         } else {
//             echo '.';
//         }

//         $q++;
//     }
// }

// echo "Q:" . $q . PHP_EOL;

$serverPostLogGet = function ($clientId, $date) {
    $sql = "select z.h, (IF(a.c is null, 0, a.c)  + IF(b.c is null, 0, b.c) ) as cnt, IF(a.c is null, 0, a.c) as succ_cnt, IF(b.c is null, 0, b.c) as fail_cnt from 
    (
        select 0 as h union
        select 1 as h union
        select 2 as h union
        select 3 as h union
        select 4 as h union
        select 5 as h union
        select 6 as h union
        select 7 as h union
        select 8 as h union
        select 9 as h union
        select 10 as h union
        select 11 as h union
        select 12 as h union
        select 13 as h union
        select 14 as h union
        select 15 as h union
        select 16 as h union
        select 17 as h union
        select 18 as h union
        select 19 as h union
        select 20 as h union
        select 21 as h union
        select 22 as h union
        select 23 as h
    ) z left join
    (
        select hour(created) as h, count(*) as c from server_post_log_{$clientId}
        where created >= '{$date} 00:00:00' and  created <= '{$date} 23:59:59' 
        and error_code is null
        group by hour(created)
    ) a on z.h = a.h left join
    (
        select hour(created) as h, count(*) as c from server_post_log_{$clientId}
        where created >= '{$date} 00:00:00' and  created <= '{$date} 23:59:59' 
        and error_code is not null
        group by hour(created)
    ) b on z.h = b.h";


    return $sql;
};

$serverRequestLogGet = function ($clientId, $date) {
    $sql = "select z.h, (IF(a.c is null, 0, a.c)  + IF(b.c is null, 0, b.c) ) as cnt, IF(a.c is null, 0, a.c) as succ_cnt, IF(b.c is null, 0, b.c) as fail_cnt from 
    (
        select 0 as h union
        select 1 as h union
        select 2 as h union
        select 3 as h union
        select 4 as h union
        select 5 as h union
        select 6 as h union
        select 7 as h union
        select 8 as h union
        select 9 as h union
        select 10 as h union
        select 11 as h union
        select 12 as h union
        select 13 as h union
        select 14 as h union
        select 15 as h union
        select 16 as h union
        select 17 as h union
        select 18 as h union
        select 19 as h union
        select 20 as h union
        select 21 as h union
        select 22 as h union
        select 23 as h
    ) z left join
    (
        select hour(created) as h, count(*) as c from server_request_log_{$clientId}
        where created >= '{$date} 00:00:00' and  created <= '{$date} 23:59:59' 
        and is_success = 1
        group by hour(created)
    ) a on z.h = a.h left join
    (
        select hour(created) as h, count(*) as c from server_request_log_{$clientId}
        where created >= '{$date} 00:00:00' and  created <= '{$date} 23:59:59' 
        and is_success = 0
        group by hour(created)
    ) b on z.h = b.h";
    return $sql;
};
// echo $serverPostLogGet(8, '2023-01-05');
echo $serverRequestLogGet(8, '2023-01-05');
echo PHP_EOL;
