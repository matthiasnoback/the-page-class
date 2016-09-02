<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;

/**
 * @var $connection Connection
 */
global $connection;
/**
 * @var $mysqlError DBALException
 */
global $mysqlError;
/**
 * @var $allResults array
 */
global $allResults;
$allResults = [];

function mysql_connect() {
    global $connection;

    $dbPath = realpath('../data') . '/cms.sqlite';
    unlink($dbPath);
    $connection = DriverManager::getConnection([
        'driver' => 'pdo_sqlite',
        'path' => $dbPath
    ]);

    createSchemaIfNotExists($connection);

    return true;
}

function createSchemaIfNotExists(Connection $connection) {
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

    $sqls = $schema->toSql($connection->getDatabasePlatform());
    foreach ($sqls as $sql) {
        $connection->executeQuery($sql);
    }
    
    $connection->insert('content', [
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
    $connection->insert('content', [
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

function mysql_select_db() {
    return true;
}

function mysql_query($sql) {
    global $connection;
    global $allResults;

    try {
        $stmt = $connection->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        reset($rows);

        $allResults[spl_object_hash($stmt)] = $rows;
    } catch (DBALException $exception) {
        global $mysqlError;
        $mysqlError = $exception;

        return false;
    }

    return $stmt;
}

/**
 * @param Statement $stmt
 * @return int
 */
function mysql_num_rows($stmt) {
    global $allResults;

    assert('$stmt instanceof ' . Statement::class);

    return count($allResults[spl_object_hash($stmt)]);
}

/**
 * @param Statement $stmt
 * @return array
 */
function mysql_fetch_assoc($stmt) {
    global $allResults;

    assert('$stmt instanceof ' . Statement::class);

    if (!isset($allResults[spl_object_hash($stmt)])) {
        return false;
    }

    $row = current($allResults[spl_object_hash($stmt)]);

    if (!next($allResults[spl_object_hash($stmt)])) {
        unset($allResults[spl_object_hash($stmt)]);
    }

    return $row;
}

function mysql_error() {
    global $mysqlError;

    return $mysqlError->getMessage();
}
