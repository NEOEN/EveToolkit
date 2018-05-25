<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 21.05.2018
 * Time: 12:58
 */

namespace projects\ProjectX\Classes\Esi;


class EsiRepository extends \Core\Parent\Repository
{
    protected $tableName = 'esi';
    protected $parameter = ['id', 'user_id', 'character_id', 'refresh_token', 'access_token', 'token_type', 'expire_time', 'created_at', 'updated_at'];

    public function __construct(\PDO $database)
    {
        parent::__construct($database);
    }

    public function save(Esi $esi)
    {
        if ($esi->id) {
            return $this->update($esi);
        }

        return $this->insert($esi);
    }

    protected function insert(Esi $esi)
    {
        $query = 'INSERT INTO '.$this->tableName.' SET';
        $query .= ' user_id='.$this->db->quote($esi->userId, \PDO::PARAM_INT).',';
        $query .= ' character_id='.$this->db->quote($esi->characterId, \PDO::PARAM_INT).',';
        $query .= ' refresh_token='.$this->db->quote($esi->refreshToken, \PDO::PARAM_STR);
        $query .= ' access_token='.$this->db->quote($esi->accessToken, \PDO::PARAM_STR) . ',';
        $query .= ' token_type='.$this->db->quote($esi->tokenType, \PDO::PARAM_STR) . ',';
        $query .= ' expire_time='.$this->db->quote($esi->expireTime, \PDO::PARAM_STR);
        $this->db->exec($query);

        $esi->id = $this->db->lastInsertId();

        return $esi;
    }

    protected function update(Esi $esi)
    {
        $query = 'UPDATE '.$this->tableName.' SET';
        $query .= ' refresh_token='.$this->db->quote($esi->refreshToken, \PDO::PARAM_STR) . ',';
        $query .= ' access_token='.$this->db->quote($esi->accessToken, \PDO::PARAM_STR) . ',';
        $query .= ' token_type='.$this->db->quote($esi->tokenType, \PDO::PARAM_STR) . ',';
        $query .= ' expire_time='.$this->db->quote($esi->expireTime, \PDO::PARAM_STR);
        $query .= ' WHERE';
        $query .= ' '.$this->tableName.'.user_id='.$this->db->quote($esi->userId, \PDO::PARAM_INT);
        $query .= ' AND '.$this->tableName.'.character_id='.$this->db->quote($esi->characterId, \PDO::PARAM_INT);
        $this->db->exec($query);

        return $esi;
    }

    public function getFromHttpRequest()
    {
        $esi = new Esi();

    }

    public function getByUserIdAndCharacterId($userId, $characterId)
    {
        $esi = new Esi();

        $query = 'SELECT '.$this->tableName.'.'.implode(','.$this->tableName.'.', $this->parameter);
        $query .= ' FROM '.$this->tableName;
        $query .= ' WHERE';
        $query .= ' '.$this->tableName.'.user_id='.$this->db->quote($userId, \PDO::PARAM_INT);
        $query .= ' AND '.$this->tableName.'.character_id='.$this->db->quote($characterId, \PDO::PARAM_INT);
        $row = $this->db->query($query)->fetch();

        if ($row != null) {
            $esi->readFromArray($row);
        }

        return $esi;
    }

    public function getByUserId($userId){
        $esiList = new EsiList();

        $query = 'SELECT '.$this->tableName.'.'.implode(','.$this->tableName.'.', $this->parameter);
        $query .= ' FROM '.$this->tableName;
        $query .= ' WHERE';
        $query .= ' '.$this->tableName.'.user_id='.$this->db->quote($userId, \PDO::PARAM_INT);
        $rows = $this->db->query($query)->fetchAll();

        foreach($rows as $row){
            $esi = new Esi();
            $esi->readFromArray($row);
            $esiList->add($esi);
        }

        return $esiList;
    }
}