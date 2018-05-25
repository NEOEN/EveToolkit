<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 13:08
 */

namespace projects\ProjectX\Classes\News;


class News
{

    public $id;
    protected $data;
    public $type;
    public $createdAt;
    public $updatedAt;
    protected $title;

    /**
     * Reads the return from the database to the object (row)
     *
     * @param array $data
     */
    public function readFromArray($data) {
        if (is_array($data)) {
            $this->id = $data['id'];
            $this->data = json_decode($data['data'], true);
            $this->type = $data['type'];
            $this->createdAt = $data['created_at'];
            $this->updatedAt = $data['updated_at'];
        } else {
            trigger_error('Not an Array: ' . @$GLOBALS['system']['absolute_url'] . @$_SERVER['REQUEST_URI'], E_USER_WARNING);
        }
    }

    public function __get($key){
        switch($key){
            case 'title':
                return $this->getTitle();
                break;
            case 'content':
                return $this->getContent();
                break;
        }

        return null;
    }

    public function __set($key, $value){
        switch($key){
            case 'title':
                $this->data['title'] = $value;
                break;
            case 'content':
                $this->data['content'] = $value;
                break;
        }
    }

    public function getTitle(){
        return $this->data['title'];
    }

    public function getContent(){
        return $this->data['content'];
    }
}