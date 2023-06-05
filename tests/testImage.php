<?php
$outputPath = 'test.jpeg';
// (41 + 2 + 20 + 2) * 3
$path = 'https://fastly.jsdelivr.net/npm/@vant/assets/cat.jpeg';
$path = './test_default.png';
$size = getimagesize($path);
//创建图片对象
// $image_1 = imagecreatefrompng($path);
if ($size[2] == 1) {
    $image_1 = imagecreatefromgif($path);
} else if ($size[2] == 2) {
    $image_1 = imagecreatefromjpeg($path);
} else if ($size[2] == 3) {
    $image_1 = imagecreatefrompng($path);
}

$w = $size[0];
$h = $size[1];
// $image_2 = imagecreatefrompng($path);
//创建真彩画布
//imagecreatetruecolor(int $width, int $height)--新建一个真彩色图像
$b = 6;
$bigH = $bigW = 41 * $b;
$smallH = $smallW = 20 * $b;
$l = 1 * $b;


$image_3 = imageCreatetruecolor($bigW + $smallW + $l, $bigW + $smallW + $l);
$color = imagecolorallocate($image_3, 255, 255, 255); // white color
imagefill($image_3, 0, 0, $color); // fill

imagecopyresampled($image_3, $image_1, 0, 0, 0, 0, $bigW, $bigH, $w, $h); // left 1
imagecopyresampled($image_3, $image_1, $bigW + $l, 0, 0, 0, $smallW, $smallH, $w, $h); // right 1
imagecopyresampled($image_3, $image_1, $bigW + $l, $smallW + $l, 0, 0, $smallW, $smallH, $w, $h); // right 2
imagecopyresampled($image_3, $image_1, $bigW + $l, 2 * $smallW + $l * 2, 0, 0, $smallW, $smallH, $w, $h); // right 3
imagecopyresampled($image_3, $image_1, 0, $bigH  + $l, 0, 0, $smallW, $smallH, $w, $h); // bottom 1
imagecopyresampled($image_3, $image_1, $smallW + $l, $bigH  + $l, 0, 0, $smallW, $smallH, $w, $h); // bottom 2
// header('Content-type: image/jpeg');
imagejpeg($image_3, $outputPath);
// imagejpeg($image_3);