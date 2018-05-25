<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 14:05
 */

namespace Core\Parent;

class ObjectList implements \Iterator, \Countable {

    /**
     * protected vars
     */
    protected $objects = [];
    protected $pos = 0;

    /**
     * add a Object to the intern Array
     *
     * @param $object
     */
    public function add($object) {
        $this->objects[] = $object;
    }

    public function current() {
        return $this->objects[$this->pos];
    }

    public function key() {
        return $this->pos;
    }

    public function next() {
        $this->pos++;
    }

    public function rewind() {
        $this->pos = 0;
    }

    public function valid() {
        if ($this->pos < count($this->objects)) {
            return true;
        }

        return false;
    }

    public function count() {
        return count($this->objects);
    }

}