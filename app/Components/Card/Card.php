<?php

namespace App\Components\Card;

use App\Components\Card\Jobs\SendCard;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use Exception;
use Splunk;
use Log;

class Card
{
    /**
     * @var Imagick
     */
    public $image;

    /**
     * @var ImagickDraw
     */
    protected $draw;

    /**
     * @var string
     */
    protected $bg;

    /**
     * @var array
     */
    protected $config;

    /**
     * 绘制卡片
     *
     * @return Imagick
     */
    public function create()
    {
        if (is_file($bg)) {
            $this->image = new Imagick($bg);
        } else {
            $tempfile = tempnam(sys_get_temp_dir(), 'magick-');
            copy($bg, $tempfile);
            $this->image = new Imagick($tempfile);
            unlink($tempfile);
        }
        $this->draw = new ImagickDraw();
        while ($task = array_pop($this->config)) {
            $type = $task['type'];
            unset($task['type']);
            $name = 'draw' . ucfirst($type);
            $this->$name($task);
        }
        $this->image->drawImage($this->draw);
        $this->image->setImageFormat('jpeg');
        return $this->image;
    }

    /**
     * 绘制文字
     *
     * @param array $data
     */
    public function drawText($data)
    {
        $content = $data['content'];
        unset($data['content']);

        $gravity = $data['position']['gravity'];

        if (in_array($gravity, [Imagick::GRAVITY_NORTHEAST, Imagick::GRAVITY_SOUTHEAST, Imagick::GRAVITY_EAST])) {
            $char = mb_substr($content, -1, 1);
        } else {
            $char = mb_substr($content, 0, 1);
        }

        $fontData = static::getFontData($data['font'], $data['size'], $char);

        if (in_array($gravity, [Imagick::GRAVITY_NORTH, Imagick::GRAVITY_SOUTH])) {
            $x = $data['position']['x'];
        } elseif (in_array($gravity, [Imagick::GRAVITY_NORTHEAST, Imagick::GRAVITY_SOUTHEAST, Imagick::GRAVITY_EAST])) {
            $x = $data['position']['x'] - ceil($fontData['textWidth'] - $fontData['boundingBox']['x2']);
        } else {
            $x = $data['position']['x'] - floor($fontData['boundingBox']['x1']);
        }

        $fontData = static::getFontData($data['font'], $data['size'], $content);

        if (in_array($gravity, [Imagick::GRAVITY_WEST, Imagick::GRAVITY_EAST])) {
            $y = $data['position']['y'];
        } elseif (in_array($gravity, [Imagick::GRAVITY_NORTHWEST, Imagick::GRAVITY_NORTHEAST, Imagick::GRAVITY_NORTH])) {
            $y = $data['position']['y'] + ceil($fontData['boundingBox']['y2'] - $fontData['ascender']);
        } else {
            $y = $data['position']['y'] - ceil($fontData['boundingBox']['y1'] - $fontData['descender']);
        }

        $this->draw->setGravity($gravity);
        unset($data['position']);

        $this->draw->setFont($this->getFont($data['font']));
        $this->draw->setFontSize($data['size']);
        unset($data['font']);
        unset($data['size']);

        foreach ($data as $key => $value) {
            if($key == 'setFillColor') {
                $value = new ImagickPixel($value);
            }
            if(!is_array($value)) {
                $value = [$value];
            }

            call_user_func_array([$this->draw, $key], $value);
        }

        $this->draw->annotation($x, $y, $content);
    }

    /**
     * 绘制图片
     *
     * @param array $data
     */
    public function drawImg($data)
    {
        $content = $data['content'];

        if (!empty($content)) {
            if(is_object($content) && $content instanceof Imagick) {
                $img = $content;
            }else {
                if (is_file($content)) {
                    $img = new Imagick($content);
                } else {
                    $tempfile = tempnam(sys_get_temp_dir(), 'magick-');
                    copy($content, $tempfile);
                    $img = new Imagick($tempfile);
                    unlink($tempfile);
                }
            }
        } else {
            $img = new Imagick();
        }
        unset($data['content']);

        $gravity = $data['position']['gravity'];
        $x = $data['position']['x'];
        $y = $data['position']['y'];
        $this->draw->setGravity($gravity);
        unset($data['position']);

        foreach ($data as $key => $value) {
            call_user_func_array([$img, $key], $value);
        }
        $this->draw->composite(Imagick::COMPOSITE_ATOP, $x, $y, $img->getImageWidth(), $img->getImageHeight(), $img);
    }

    /**
     * 裁剪圆形
     * @param $data
     */
    public function drawCircle($data)
    {
        $content = $data['content'];
        $radius = $data['radius'];
        $color = $data['color'] ?? 'none';
        unset($data['content']);
        unset($data['radius']);
        unset($data['color']);

        if (!empty($content)) {
            if (is_file($content)) {
                $img = new Imagick($content);
            } else {
                $tempfile = tempnam(sys_get_temp_dir(), 'magick-');
                copy($content, $tempfile);
                $img = new Imagick($tempfile);
                unlink($tempfile);
            }
        } else {
            $img = new Imagick();
            $img->newImage(2 * $radius + 1, 2 * $radius + 1, $color);
        }
        unset($data['content']);

        $circleData = [
            'radius' => $radius,
            'setFillColor' => '#000000',
        ];
        $circle = $this->getCircleBackground($circleData);

        $temp = new ImagickDraw();
        $temp->composite(Imagick::COMPOSITE_SRCIN, 0, 0, 2 * $radius + 1, 2 * $radius + 1, $img);

        $circle->drawImage($temp);
        $circle->setImageFormat("png");

        $gravity = $data['position']['gravity'];
        $x = $data['position']['x'];
        $y = $data['position']['y'];
        $this->draw->setGravity($gravity);
        unset($data['position']);

        foreach ($data as $key => $value) {
            call_user_func_array([$circle, $key], $value);
        }

        $this->draw->composite(Imagick::COMPOSITE_ATOP, $x, $y, $circle->getImageWidth(), $circle->getImageHeight(), $circle);
    }

    /**
     * 绘制多行文本
     *
     * @param $data
     */
    public function drawTextBr($data)
    {
        $tmp = $data['content'];
        unset($data['content']);
        $count = mb_strlen($tmp);
        $start = 0;
        $row = $data['row'];
        unset($data['row']);
        $content = "";
        while ($count > $row) {
            $content .= mb_substr($tmp, $start, $row)."\n";
            $count -= $row;
            $start += $row;

        }
        $content .= mb_substr($tmp, $start);

        $gravity = $data['position']['gravity'];

        if (in_array($gravity, [Imagick::GRAVITY_NORTHEAST, Imagick::GRAVITY_SOUTHEAST, Imagick::GRAVITY_EAST])) {
            $line = implode("\n", array_map(function($line) {
                return mb_substr($line, -1, 1);
            }, explode("\n", $content)));
        } else {
            $line = implode("\n", array_map(function($line) {
                return mb_substr($line, 0, 1);
            }, explode("\n", $content)));
        }

        $fontData = static::getFontData($data['font'], $data['size'], $line);

        if (in_array($gravity, [Imagick::GRAVITY_NORTH, Imagick::GRAVITY_SOUTH])) {
            $x = $data['position']['x'];
        } elseif (in_array($gravity, [Imagick::GRAVITY_NORTHEAST, Imagick::GRAVITY_SOUTHEAST, Imagick::GRAVITY_EAST])) {
            $x = $data['position']['x'] - ceil($fontData['textWidth'] - $fontData['boundingBox']['x2']);
        } else {
            $x = $data['position']['x'] - floor($fontData['boundingBox']['x1']);
        }

        if (in_array($gravity, [Imagick::GRAVITY_SOUTHWEST, Imagick::GRAVITY_SOUTHEAST, Imagick::GRAVITY_SOUTH])) {
            $line = array_last(explode("\n", $content));
        } else {
            $line = array_first(explode("\n", $content));
        }

        $fontData = static::getFontData($data['font'], $data['size'], $line);

        if (in_array($gravity, [Imagick::GRAVITY_WEST, Imagick::GRAVITY_EAST])) {
            $y = $data['position']['y'];
        } elseif (in_array($gravity, [Imagick::GRAVITY_NORTHWEST, Imagick::GRAVITY_NORTHEAST, Imagick::GRAVITY_NORTH])) {
            $y = $data['position']['y'] + ceil($fontData['boundingBox']['y2'] - $fontData['ascender']);
        } else {
            $y = $data['position']['y'] - ceil($fontData['boundingBox']['y1'] - $fontData['descender']);
        }

        $this->draw->setGravity($gravity);
        unset($data['position']);

        $this->draw->setFont($this->getFont($data['font']));
        $this->draw->setFontSize($data['size']);
        unset($data['font']);
        unset($data['size']);

        foreach ($data as $key => $value) {
            if($key == 'setFillColor') {
                $value = new ImagickPixel($value);
            }
            if(!is_array($value)) {
                $value = [$value];
            }

            call_user_func_array([$this->draw, $key], $value);
        }

        $this->draw->annotation($x, $y, $content);
    }

    /**
     * 绘制圆形
     *
     * @param $data
     * @return Imagick
     */
    public function getCircleBackground($data)
    {
        $radius = $data['radius'];
        unset($data['radius']);

        $circle = new Imagick();
        $circle->newImage(2 * $radius + 1, 2 * $radius + 1, new ImagickPixel('none'));
        $draw = new ImagickDraw();

        foreach ($data as $key => $value) {
            if($key == 'setFillColor') {
                $value = new ImagickPixel($value);
            }
            if(!is_array($value)) {
                $value = [$value];
            }
            call_user_func_array([$draw, $key], $value);
        }

        $draw->circle($radius, $radius, 2 * $radius, $radius);
        $circle->drawImage($draw);
        $circle->setImageFormat("png");
        return $circle;
    }

    /**
     * 释放内存
     */
    public function clear()
    {
        $this->image->clear();
    }

    /**
     * 立即发送
     *
     * @param $uid
     */
    public function send($uid)
    {
        dispatch(new SendCard($this, $uid));
    }

    /**
     * 定时发送
     *
     * @param $uid
     * @param $time
     */
    public function sendAt($uid, $time)
    {
        dispatch(new SendCard($this, $uid, $time));
    }

    /**
     * 保存到文件
     *
     * @param $path
     */
    public function save($path)
    {
        $image = $this->create();
        $image->writeImage($path);
        $this->clear();
    }

    /**
     * 获取字体
     *
     * @param string $font
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getFont($font = 'siyuanhei_light')
    {
        $re =  config('font.'.$font);
        if(empty($re)) {
            $re = config('font.siyuanhei_light');
        }
        return $re;
    }

    /**
     * 获取字体信息
     *
     * @param $font
     * @param $size
     * @param string $char
     * @return array
     */
    public static function getFontData($font, $size, $char = '0')
    {
        $im = new Imagick();
        $draw = new ImagickDraw();
        $draw->setFont((new self)->getFont($font));
        $draw->setFontSize($size);

        return $im->queryFontMetrics($draw, $char);
    }
}
