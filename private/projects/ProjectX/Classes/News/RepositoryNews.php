<?php
/**
 * Created by PhpStorm.
 * User: Lamer
 * Date: 19.05.2018
 * Time: 13:13
 */

namespace projects\ProjectX\Classes\News;


class RepositoryNews extends \Core\Parent\Repository
{

    protected $tableName = 'news';
    protected $parameter = ['id', 'data', 'type', 'created_at', 'updated_at'];

    public function __construct(\PDO $database)
    {
        parent::__construct($database);
    }

    public function getAll()
    {
        $newsList = new NewsList();

        $query = 'SELECT ' . $this->tableName . '.' . implode(',' . $this->tableName . '.', $this->parameter);
        $query .= ' FROM ' . $this->tableName;
        $query .= ' ORDER BY created_at DESC';
        $stmt = $this->db->query($query);

        while($row = $stmt->fetch()){
            $news = new News();
            $news->readFromArray($row);
            $newsList->add($news);
        }

        return $newsList;
    }
}