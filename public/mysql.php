<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;

class ConnectionSingleton
{
    /**
     * @var ConnectionSingleton|null
     */
    private static $instance;

    /**
     * @var Connection|null
     */
    private $connection;

    private $results = [];
    private $latestErrorMessage;

    /**
     * @return ConnectionSingleton
     */
    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function keepError(\Exception $error)
    {
        $this->latestErrorMessage = $error->getMessage();
    }

    public function latestErrorMessage()
    {
        return $this->latestErrorMessage;
    }

    public function keepResults(Statement $statement, array $results)
    {
        $this->results[spl_object_hash($statement)] = $results;
    }

    public function countResults(Statement $statement)
    {
        if (!isset($this->results[spl_object_hash($statement)])) {
            return 0;
        }

        return count($this->results[spl_object_hash($statement)]);
    }

    private function __construct()
    {
    }

    public function connection()
    {
        if ($this->connection === null) {
            $dbPath = realpath('../data') . '/cms.sqlite';
            $this->connection = DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'path' => $dbPath
            ]);

            dropAndCreateSchema($this->connection);
        }

        return $this->connection;
    }

    public function fetchAssoc(Statement $statement)
    {
        if (!isset($this->results[spl_object_hash($statement)])
            || count($this->results[spl_object_hash($statement)]) == 0) {
            return false;
        }

        $row = array_shift($this->results[spl_object_hash($statement)]);

        if (count($this->results[spl_object_hash($statement)]) == 0) {
            unset($this->results[spl_object_hash($statement)]);
        }

        return $row;
    }
}

function dropAndCreateSchema(Connection $connection)
{
    $schema = new Schema();
    $contentTable = $schema->createTable('content');
    $contentTable->addColumn('id', 'integer')->setAutoincrement(true);
    $contentTable->addColumn('skip_to_first_subpage', 'boolean')->setNotnull(false);
    $contentTable->addColumn('uri', 'string')->setNotnull(false);
    $contentTable->addColumn('menu_name', 'string')->setNotnull(false);
    $contentTable->addColumn('title', 'string')->setNotnull(false);
    $contentTable->addColumn('description', 'string')->setNotnull(false);
    $contentTable->addColumn('keywords', 'string')->setNotnull(false);
    $contentTable->addColumn('node', 'integer')->setNotnull(false);
    $contentTable->addColumn('parent_id', 'integer')->setNotnull(false);
    $contentTable->addColumn('available_for_guests', 'boolean')->setNotnull(false);
    $contentTable->addColumn('available_for_users', 'boolean')->setNotnull(false);
    $contentTable->addColumn('available_for_admins', 'boolean')->setNotnull(false);
    $contentTable->addColumn('priority', 'integer')->setNotnull(false);
    $contentTable->addColumn('show_in_menu', 'boolean')->setNotnull(false);
    $contentTable->addColumn('show_contents', 'boolean')->setNotnull(false);
    $contentTable->addColumn('contents', 'text')->setNotnull(false);
    $contentTable->setPrimaryKey(['id']);

    $schemaSynchronizer = new SingleDatabaseSynchronizer($connection);
    $schemaSynchronizer->dropAllSchema();
    $schemaSynchronizer->createSchema($schema);

    (new FixtureFactory($connection))->populateDatabase();
}

class FixtureFactory
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function populateDatabase()
    {
        $this->connection->insert('content', [
            'uri' => '',
            'menu_name' => 'Home',
            'title' => 'Home',
            'parent_id' => 0,
            'available_for_guests' => true,
            'available_for_users' => true,
            'available_for_admins' => true,
            'priority' => 1,
            'show_in_menu' => true,
            'show_contents' => true,
            'contents' => 'This is the homepage!'
        ]);
        $this->connection->insert('content', [
            'uri' => 'about-me',
            'menu_name' => 'About me',
            'title' => 'About me',
            'parent_id' => 0,
            'available_for_guests' => true,
            'available_for_users' => true,
            'available_for_admins' => true,
            'priority' => 2,
            'show_in_menu' => true,
            'show_contents' => true,
            'contents' => 'I like to write code with PHP'
        ]);
    }
}

function mysql_connect()
{
    return true;
}

function mysql_select_db()
{
    return true;
}

function mysql_query($sql)
{
    try {
        $stmt = ConnectionSingleton::get()->connection()->query($sql);
        ConnectionSingleton::get()->keepResults($stmt, $stmt->fetchAll(\PDO::FETCH_ASSOC));
    } catch (DBALException $exception) {
        ConnectionSingleton::get()->keepError($exception);
        return false;
    }

    return $stmt;
}

function mysql_num_rows($stmt)
{
    return ConnectionSingleton::get()->countResults($stmt);
}

function mysql_fetch_assoc($stmt)
{
    return ConnectionSingleton::get()->fetchAssoc($stmt);
}

function mysql_error()
{
    return ConnectionSingleton::get()->latestErrorMessage();
}
