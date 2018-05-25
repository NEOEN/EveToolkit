<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 14:02
 */

namespace projects\ProjectX\Classes\News;


class NewsList extends \Core\Parent\ObjectList
{

    /**
     * @param News $object
     */
    public function add($object){
        if(!$object instanceOf News){
            trigger_error('Kein NewsObjekt');
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

        return new News();
    }
}