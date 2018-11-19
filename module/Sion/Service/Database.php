<?php
namespace Sion\Service;

class Database
{
  private static $_instance;
  private $_pdo;

  // 外からNewさせない
  private function __construct($hostName, $dbName, $dbUser, $dbPassword)
  {
    // PDOを作成してセットする
    $dsn = 'mysql:host='. $hostName. ';dbname='. $dbName. ';charset=utf8';
    $this->_pdo = new \PDO($dsn, $dbUser, $dbPassword);
    $this->_pdo->query('SET NAMES utf8');
  }

  public static function getInstance($hostName, $dbName, $dbUser, $dbPassword)
  {
    // インスタンスを作成、あれば作成しない
    if (!isset(self::$_instance)) {
      self::$_instance = new self($hostName, $dbName, $dbUser, $dbPassword);
    }
    return self::$_instance;
  }

  public function getPdo()
  {
    return $this->_pdo;
  }
}
