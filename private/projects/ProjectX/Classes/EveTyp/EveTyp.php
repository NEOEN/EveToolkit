<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 25.05.2018
 * Time: 20:21
 */

namespace projects\ProjectX\Classes\EveTyp;


class EveTyp
{
    public $id;
    public $name;

    /**
     * Reads the return from the database to the object (row)
     *
     * @param array $data
     */
    public function readFromArray($data) {
        if (is_array($data)) {
            $this->id = $data['id'];
            $this->name = $data['name'];
        } else {
            trigger_error('Not an Array: ' . @$GLOBALS['system']['absolute_url'] . @$_SERVER['REQUEST_URI'], E_USER_WARNING);
        }
    }
}