<?php
/**
 * Created by PhpStorm.
 * User: yuzhan
 * Date: 2017/6/13
 * Time: 下午12:54
 */

namespace App\Library\Model;

use \Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class QueryWrapper
{
    protected $class;
    protected $data;

    public function __construct($data, $class)
    {
        $this->data = $data;
        $this->class = $class;
    }

    public function __call($name, $arguments)
    {
        $ret = call_user_func_array([$this->data, $name], $arguments);

        if ($ret instanceof Builder) {
            return new static($ret, $this->class);
        } else if ($ret instanceof Collection) {
            return $ret->map(function($row) {
                if ($row instanceof \stdClass) {
                    return new $this->class($row);
                } else {
                    return $row;
                }
            });
        } else if (is_scalar($ret)) {
            return $ret;
        } else {
            return new $this->class($ret);
        }
    }
}
