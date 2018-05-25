<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 25.05.2018
 * Time: 20:27
 */

namespace projects\ProjectX\Classes\EveTyp;


class EveTypRepository extends \Core\Parent\Repository
{
    protected $tableName = 'eve_typ';
    protected $parameter = ['id', 'data', 'type', 'created_at', 'updated_at'];

    public function __construct(\PDO $database)
    {
        parent::__construct($database);
    }
}