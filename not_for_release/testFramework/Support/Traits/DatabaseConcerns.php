<?php

namespace Tests\Support\Traits;

use Illuminate\Database\Capsule\Manager as Capsule;

trait DatabaseConcerns
{

    protected  $configPath = '/not_for_release/testFramework/Support/DatabaseConfigure';

    public function setup(): void
    {

        define('DIR_FS_ROOT', getcwd());
        define('USE_PCONNECT', false);

        $this->user = $this->detectUser();
        echo "\n" . "Found User = " . $this->user . "\n";

        if (!isset($this->databaseFixtures)) {
            return;
        }
        $this->loadDatabaseConfigures($this->detectUser());
        parent::setup();

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => DB_TYPE,
            'host'      => DB_SERVER,
            'database'  => DB_DATABASE,
            'username'  => DB_SERVER_USERNAME,
            'password'  => DB_SERVER_PASSWORD,
            'charset'   => DB_CHARSET,
            // do not pass prefix; this is included in the table definition
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $this->db = new \queryFactory();
        $GLOBALS['db'] = $this->db;
        if (!$this->db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false)) {

        }

        $this->getPdoConnection(DB_DATABASE);
        $this->prepareDatabase();
    }

    protected function prepareDatabase()
    {
        foreach($this->databaseFixtures as $fixture) {
            $this->loadFixture($fixture);
        }
    }

    protected function loadFixture($fixture)
    {
        $f = '\\Tests\\Support\\DatabaseFixtures\\' . ucfirst($fixture) . 'Fixture';
        $class = new $f;
        $class->createTable($this->pdoConnection);
        $class->seeder($this->pdoConnection);
    }

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

    public function loadDatabaseConfigures($user)
    {
        $f = DIR_FS_ROOT . $this->configPath . '/' . $user . '.configure.dusk.php';
        echo "\n" . "Found DB Config = " . $f . "\n";

        if (!file_exists( $f)) {
            echo 'Could not find database configure';
            die(1);
        }
        require($f);
    }
}
