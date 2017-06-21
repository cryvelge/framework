<?php

namespace App\Library\Model;

use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class Model implements \ArrayAccess, \JsonSerializable, Arrayable
{
    protected static $table;
    protected static $columns = [];
    protected static $serializeColumns = [];
    protected static $useSoftDelete = true;
    protected static $softDeleteKey = 'deleted_at';

    protected $data;
    protected $oldData = [];
    protected $saved = false;
    protected $changed = false;

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->data = $data;
        } else if ($data instanceof \stdClass) {
            $this->data = (array)$data;
            $this->saved = true;
        } else if (is_null($data)) {
            $this->data = [];
        }
    }

    public function __get($name)
    {
        if ($this->hasColumn($name)) {
            return $this->realGet($name);
        } else {
            throw new UndefinedProtertyException(static::class, $name);
        }
    }

    public function __set($name, $value)
    {
        if ($this->hasColumn($name)) {
            $this->realSet($name, $value);
        } else {
            throw new UndefinedProtertyException(static::class, $name);
        }
    }

    public function getOldValue($name)
    {
        if ($this->hasColumn($name)) {
            return $this->getOld($name);
        } else {
            throw new UndefinedProtertyException(static::class, $name);
        }
    }

    public function save()
    {
        if (!$this->saved) {
            $ret = static::create($this->data);
            $this->saved = true;
        } else if ($this->changed) {
            $ret = static::where('id', $this->data['id'])->update(array_only($this->data, array_keys($this->oldData)));
        } else {
            $ret = true;
        }
        $this->oldData = [];
        $this->changed = false;
        return (bool)$ret;
    }

    public function update($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
        return $this->save();
    }

    public function destroy()
    {
        if ($this->saved) {
            if (static::$useSoftDelete) {
                $this->deleted_at = Carbon::now()->toDateTimeString();
                $this->save();
            } else {
                static::where('id', $this->id)->delete();
            }
            $this->saved = false;
        }

        return true;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function toArray()
    {
        return $this->data;
    }

    public static function __callStatic($name, $arguments)
    {
        if (is_null(static::$table)) {
            $reflection = new \ReflectionClass(static::class);
            $className = $reflection->getShortName();
            static::$table = Str::snake(Str::plural($className));
        }

        $wrapper = new QueryWrapper(DB::table(static::$table), static::class);
        return call_user_func_array([$wrapper, $name], $arguments);
    }

    private function hasColumn($name)
    {
        return array_search($name, static::$columns) !== false;
    }

    private function realGet($name)
    {
        $data = $this->data[$name] ?? null;

        if (array_search($name, static::$serializeColumns) !== false) {
            $data = json_decode($data, true);
        }

        return $data;
    }

    private function getOld($name)
    {
        $data = $this->oldData[$name];

        if (array_search($name, static::$serializeColumns) !== false) {
            $data = json_decode($this->oldData[$name], true);
        }

        return $data;
    }

    private function realSet($name, $value)
    {
        if (array_search($name, static::$serializeColumns) !== false) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($this->data[$name] !== $value && !isset($this->oldData[$name])) {
            $this->oldData[$name] = $this->data[$name];
            $this->changed = true;
        }

        $this->data[$name] = $value;
    }
}
