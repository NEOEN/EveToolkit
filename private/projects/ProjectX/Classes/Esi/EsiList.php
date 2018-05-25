<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 24.05.2018
 * Time: 21:28
 */

namespace projects\ProjectX\Classes\Esi;


class EsiList extends \Core\Parent\ObjectList
{
    /**
     * @param News $object
     */
    public function add($object){
        if(!$object instanceOf Esi){
            trigger_error('Kein EsiObjekt');
        }
        $this->objects[] = $object;
    }

    /**
     * @param $index
     *
     * @return News
     */
    public function getIndex($index){
        if( isset($this->objects[$index])){
            return $this->objects[$index];
        }

        return new Esi();
    }
}