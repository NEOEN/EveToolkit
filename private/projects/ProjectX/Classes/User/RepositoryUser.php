<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 16:41
 */

namespace projects\ProjectX\Classes\User;


class RepositoryUser extends \Core\Parent\Repository
{
    protected $tableName = 'user';
    protected $parameter = ['id', 'name', 'email', 'created_at', 'updated_at'];

    public function __construct(\PDO $database)
    {
        parent::__construct($database);
    }

    public function getByNameOrEmail($data)
    {
        $user = new User();

        $query = 'SELECT '.$this->tableName.'.'.implode(','.$this->tableName.'.', $this->parameter);
        $query .= ' FROM '.$this->tableName;
        $query .= ' WHERE';
        $query .= ' '.$this->tableName.'.name = '.$this->db->quote($data, \PDO::PARAM_STR);
        $query .= ' OR '.$this->tableName.'.email = '.$this->db->quote($data, \PDO::PARAM_STR);
        $row = $this->db->query($query)->fetch();

        if ($row != null) {
            $user->readFromArray($row);
        }

        return $user;
    }

    public function findById($userId){
        $user = new User();

        $query = 'SELECT '.$this->tableName.'.'.implode(','.$this->tableName.'.', $this->parameter);
        $query .= ' FROM '.$this->tableName;
        $query .= ' WHERE';
        $query .= ' id=' . $this->db->quote($userId, \PDO::PARAM_INT);
        $row = $this->db->query($query)->fetch();

        if ($row != null) {
            $user->readFromArray($row);
        }

        return $user;
    }
}