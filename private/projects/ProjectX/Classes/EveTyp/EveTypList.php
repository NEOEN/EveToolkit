<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 25.05.2018
 * Time: 20:22
 */

namespace projects\ProjectX\Classes\EveTyp;


class EveTypList extends \Core\Parent\ObjectList
{

    /**
     * @param EveTyp $object
     */
    public function add($object){
        if(!$object instanceOf EveTyp){
            trigger_error('Kein EveTypObjekt');
        }
        $this->objects[$object->Id] = $object;
    }

    /**
     * @param EveTyp->id
     *
     * @return EveTyp
     */
    public function getByTypId($index){
        if( isset($this->objects[$index])){
            return $this->objects[$index];
        }

        return new EveTyp();
    }
}