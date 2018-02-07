<?php

namespace App\Components\Card\Type;

use App\Components\Card\Card;
use Imagick;
use ImagickPixel;

class Demo extends Card
{
    public function __construct($data = [])
    {
        $this->bg = resource_path('background/grid.png');

        $this->config = [
            [
                'type' => 'textBr',
                'content' => '测试内容1测试内容1测试内容1',
                'row' => 4,
                'font' => 'siyuanhei_regular',
                'size' => 50,
                'position' => [
                    'gravity' => Imagick::GRAVITY_NORTHEAST,
                    'x' => 200,
                    'y' => 200,
                ],
            ], [
                'type' => 'img',
                'content' => resource_path('background/avatar.jpg'),
                'position' => [
                    'gravity' => Imagick::GRAVITY_NORTHEAST,
                    'x' => 100,
                    'y' => 100,
                ],
                'resizeImage' => [100, 100, Imagick::FILTER_UNDEFINED, 1]
            ], [
                'type' => 'circle',
                'content' => resource_path('background/avatar.jpg'),
                'position' => [
                    'gravity' => Imagick::GRAVITY_NORTHEAST,
                    'x' => 300,
                    'y' => 100,
                ],
                'radius' => 50
            ],
        ];
    }
}
