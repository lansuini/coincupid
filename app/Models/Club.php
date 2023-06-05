<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'im_groupid',
        'face_url',
        'introduction',
        'notification',
        'award',
        'uid',
        'created',
        '7_day_award',
        'today_award',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [];

    protected $table = 'club';

    public $timestamps = false;

    protected $connection = 'Master';

    public function mergeFace($images, $savePath)
    {
        $defaultImage = resource_path('images/test_default.png');
        // $savePath = storage_path('club/mergeface/' . $id . '.jpg');
        $nimages = [];
        $sizes = [];
        for ($i = 0; $i < 6; $i++) {
            if (isset($images[$i]) && !empty($images[$i])) {
                $path = $images[$i];
            } else {
                $path = $defaultImage;
            }

            $size = getimagesize($path);
            //创建图片对象
            // $image_1 = imagecreatefrompng($path);
            if ($size[2] == 1) {
                $nimages[] = imagecreatefromgif($path);
            } else if ($size[2] == 2) {
                $nimages[] = imagecreatefromjpeg($path);
            } else if ($size[2] == 3) {
                $nimages[] = imagecreatefrompng($path);
            }

            $sizes[] = $size;
        }
        // $w = $size[0];
        // $h = $size[1];
        // $image_2 = imagecreatefrompng($path);
        //创建真彩画布
        //imagecreatetruecolor(int $width, int $height)--新建一个真彩色图像
        $b = 6;
        $bigH = $bigW = 41 * $b;
        $smallH = $smallW = 20 * $b;
        $l = 1 * $b;

        $baseImage = imagecreatetruecolor($bigW + $smallW + $l, $bigW + $smallW + $l);
        $color = imagecolorallocate($baseImage, 255, 255, 255); // white color
        imagefill($baseImage, 0, 0, $color); // fill

        imagecopyresampled($baseImage, $nimages[0], 0, 0, 0, 0, $bigW, $bigH, $sizes[0][0], $sizes[0][1]); // left 1
        imagecopyresampled($baseImage, $nimages[1], $bigW + $l, 0, 0, 0, $smallW, $smallH, $sizes[1][0], $sizes[1][1]); // right 1
        imagecopyresampled($baseImage, $nimages[2], $bigW + $l, $smallW + $l, 0, 0, $smallW, $smallH, $sizes[2][0], $sizes[2][1]); // right 2
        imagecopyresampled($baseImage, $nimages[3], $bigW + $l, 2 * $smallW + $l * 2, 0, 0, $smallW, $smallH, $sizes[3][0], $sizes[3][1]); // right 3
        imagecopyresampled($baseImage, $nimages[4], 0, $bigH  + $l, 0, 0, $smallW, $smallH, $sizes[4][0], $sizes[4][1]); // bottom 1
        imagecopyresampled($baseImage, $nimages[5], $smallW + $l, $bigH  + $l, 0, 0, $smallW, $smallH, $sizes[5][0], $sizes[5][1]); // bottom 2
        // header('Content-type: image/jpeg');
        imagejpeg($baseImage, $savePath);
        // imagejpeg($image_3);
    }
}
