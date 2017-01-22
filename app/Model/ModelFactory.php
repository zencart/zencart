<?php
namespace App\Model;

class ModelFactory
{

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function factory($table, $namespace = __NAMESPACE__)
    {
        $table = str_replace(DB_PREFIX, '' , $table);
        $class = $namespace . '\\' . ucfirst(\base::camelize($table));
        return new $class;
    }

    public function getConnection()
    {
        return $this->db;
    }

}
