<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 24.05.2018
 * Time: 23:24
 */

namespace projects\ProjectX\Classes\Character;


class CharacterEve
{

    public $id;
    public $corporationId;
    public $name;
    public $imagePath;
    public $createdAt;
    public $updatedAt;

    /**
     * Reads the return from the database to the object (row)
     *
     * @param array $data
     */
    public function readFromArray($data)
    {
        if (is_array($data)) {
            $this->id = $data['id'];
            $this->corporationId = $data['corporation_id'];
            $this->name = $data['name'];
            $this->imagePath = $data['image_path'];
            $this->createdAt = $data['created_at'];
            $this->updatedAt = $data['updated_at'];
        } else {
            trigger_error('Not an Array: '.@$GLOBALS['system']['absolute_url'].@$_SERVER['REQUEST_URI'], E_USER_WARNING);
        }
    }
}