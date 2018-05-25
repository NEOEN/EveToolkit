<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 24.05.2018
 * Time: 23:25
 */

namespace projects\ProjectX\Classes\Character;


class CharacterEveRepository extends \Core\Parent\Repository
{
    protected $tableName = 'character_eve';
    protected $parameter = ['id','corporation_id','name','image_path','created_at', 'updated_at'];

    public function __construct(\PDO $database)
    {
        parent::__construct($database);
    }

    public function save(CharacterEve $character){
        if($this->hasId($character->id)){
            return $this->update($character);
        }

        return $this->insert($character);
    }

    protected function insert(CharacterEve $characterEve)
    {
        $query = 'INSERT INTO '.$this->tableName.' SET';
        $query .= ' id='.$this->db->quote($characterEve->id, \PDO::PARAM_INT).',';
        $query .= ' corporation_id='.$this->db->quote($characterEve->corporationId, \PDO::PARAM_INT).',';
        $query .= ' name='.$this->db->quote($characterEve->name, \PDO::PARAM_STR).',';
        $query .= ' image_path='.$this->db->quote($characterEve->imagePath, \PDO::PARAM_STR);
        $this->db->exec($query);

        return $characterEve;
    }

    protected function update(CharacterEve $characterEve)
    {
        $query = 'UPDATE '.$this->tableName.' SET';
        $query .= ' corporation_id='.$this->db->quote($characterEve->corporationId, \PDO::PARAM_INT).',';
        $query .= ' name='.$this->db->quote($characterEve->name, \PDO::PARAM_STR).',';
        $query .= ' image_path='.$this->db->quote($characterEve->imagePath, \PDO::PARAM_STR);
        $query .= ' WHERE';
        $query .= ' '.$this->tableName.'.id='.$this->db->quote($characterEve->id, \PDO::PARAM_INT);
        $this->db->exec($query);

        return $characterEve;
    }

    public function hasId($characterId){
        $query = 'SELECT id FROM ' . $this->tableName;
        $query .= ' WHERE id=' . $this->db->quote($characterId, \PDO::PARAM_INT);
        $row = $this->db->query($query)->fetch();

        return $row['id'] == $characterId;
    }

    public function getById($id){
        $characterEve = new CharacterEve();

        $query = 'SELECT '.$this->tableName.'.'.implode(','.$this->tableName.'.', $this->parameter);
        $query .= ' FROM '.$this->tableName;
        $query .= ' WHERE';
        $query .= ' id=' . $this->db->quote($id, \PDO::PARAM_INT);
        $row = $this->db->query($query)->fetch();

        if($row != null){
            $characterEve->readFromArray($row);
        }

        return $characterEve;
    }
}