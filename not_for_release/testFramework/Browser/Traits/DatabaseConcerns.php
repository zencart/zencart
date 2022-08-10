<?php


namespace Tests\Browser\Traits;


trait DatabaseConcerns
{

    protected $pdoConnection = null;

    public function getPdoConnection(string $dbName = null)
    {
        $dsn = 'mysql:host=' . DB_SERVER;
        if (isset($dbName)) {
            $dsn .= ';dbname=' . $dbName;
        }
        try {
            $conn = new \PDO($dsn, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
        } catch (\PDOException $e) {
            $conn = null;
        }
        $this->pdoConnection = $conn;
    }

    protected function hasDatabase() : bool
    {
        $this->getPdoConnection(DB_DATABASE);
        if (!isset($this->pdoConnection)) {
            return false;
        }
        return true;
    }

    protected  function createDatabase()
    {
        $this->getPdoConnection(DB_DATABASE);
        $sql = "DROP DATABASE IF EXISTS " . DB_DATABASE;
        $this->executePDOQuery($sql);
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_DATABASE;
        $this->executePDOQuery($sql);
        $this->getPdoConnection(DB_DATABASE);
    }

    protected function populateDatabase()
    {
        $exec = "/usr/bin/mysql" . $this->buildMysqlCommandLine();
        $exec .= " < " . DIR_FS_ROOT .'/zc_install/sql/install/mysql_zencart.sql > /dev/null 2>&1';
        $output = null;
        exec($exec, $output);
        $exec = "/usr/bin/mysql" . $this->buildMysqlCommandLine();
        $exec .= " < " . DIR_FS_ROOT .'/zc_install/sql/demo/mysql_demo.sql > /dev/null 2>&1';
        $output = null;
        exec($exec, $output);
    }


    protected function createDummyAdminUser()
    {
        $this->getPdoConnection(DB_DATABASE);
        $name = 'Admin';
        $email = 'test@zencart.test';
        $password = password_hash('develop1', PASSWORD_DEFAULT);
        $profile = 1;
        $sql = "delete from admin";
        $this->executePDOQuery($sql);
        $sql = "INSERT INTO admin
                SET admin_name = :name,
                    admin_email = :email,
                    admin_pass = :password,
                    admin_profile = :profile,
                    pwd_last_change_date = now(),
                    last_modified = now()";
        $this->executePDOQuery($sql, [':name' => $name, ':email' => $email, ':password' => $password, 'profile' => $profile]);
    }


    protected function executePDOQuery($sql, $bindVars = [])
    {
        $statement = $this->pdoConnection->prepare($sql);
        $result = $statement->execute($bindVars);
        if (!$result) {
            echo $sql . ' FAILED';
            print_r($statement->errorInfo());
        }
        return $result;
    }

    protected function buildMysqlCommandLine()
    {
        $line = '';
        $line .= " -h" . DB_SERVER;
        $line .= " -u" . DB_SERVER_USERNAME;
        if (DB_SERVER_PASSWORD != "") {
            $line .= " -p" . DB_SERVER_PASSWORD;
        }
        $line .= " " . DB_DATABASE;
        return $line;
    }
}
