<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 21.05.2018
 * Time: 12:52
 */

namespace projects\ProjectX\Classes\Esi;


class Esi
{
    public $id;
    public $userId;
    public $characterId;
    public $refreshToken;
    public $accessToken;
    public $tokenType;
    public $expireTime;
    public $cratedAt;
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
            $this->userId = $data['user_id'];
            $this->characterId = $data['character_id'];
            $this->refreshToken = $data['refresh_token'];
            $this->accessToken = $data['access_token'];
            $this->tokenType = $data['token_type'];
            $this->expireTime = $data['expire_time'];
            $this->createdAt = $data['created_at'];
            $this->updatedAt = $data['updated_at'];
        } else {
            trigger_error('Not an Array: '.@$GLOBALS['system']['absolute_url'].@$_SERVER['REQUEST_URI'], E_USER_WARNING);
        }
    }

}