<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 16:36
 */

namespace projects\ProjectX\Classes\User;


class User
{

    public $id;
    public $name;
    public $email;
    /**
     * Reads the return from the database to the object (row)
     *
     * @param array $data
     */
    public function readFromArray($data) {
        if (is_array($data)) {
            $this->id = $data['id'];
            $this->name = $data['name'];
            $this->email = $data['email'];
            $this->createdAt = $data['created_at'];
            $this->updatedAt = $data['updated_at'];
        } else {
            trigger_error('Not an Array: ' . @$GLOBALS['system']['absolute_url'] . @$_SERVER['REQUEST_URI'], E_USER_WARNING);
        }
    }
}