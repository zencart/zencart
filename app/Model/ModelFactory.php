<?php
namespace ZenCart\Model;

class ModelFactory
{

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function factory($table, $namespace = __NAMESPACE__)
    {
        $class = $namespace . '\\' . ucfirst(\base::camelize($table));
        return new $class;
    }

    public function getConnection()
    {
        return $this->db;
    }

}
