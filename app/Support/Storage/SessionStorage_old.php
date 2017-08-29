<?php

namespace App\Support\Storage;

use Countable;
use App\Support\Storage\Contracts\StorageInterface;

class SessionStorage implements StorageInterface, Countable
{
    protected $bucket;

    public function __construct($bucket = 'default')
    {
        if(!isset($_SESSION[$bucket])){
            $_SESSION[$bucket] = [];
        }
        $this->bucket = $bucket;
    }

    public function count()
    {
        return count($this->all());
    }

    public function get($index)
    {
        if(!$this->exists($index)){
            return null;
        }
        return $_SESSION[$this->bucket][$index];
    }

    public function set($index, $value)
    {
        $_SESSION[$this->bucket][$index] = $value;
    }

    public function all()
    {
        return $_SESSION[$this->bucket];
    }

    public function exists($index)
    {
        return isset($_SESSION[$this->bucket][$index]);
    }

    public function unsetItem($index)
    {
        if(!$this->exists($index)){
            unset($_SESSION[$this->bucket][$index]);
        }
    }

    public function clear($index){
        unset($_SESSION[$this->bucket]);
    }
}