<?php
namespace Sion\Model;

use Sion\Config;
use Sion\Service\Database;

abstract class AbstractTable
{
  private $_config;
  private $_pdo;
  private $_tableConf;

  public function __construct($tableConf)
  {
    $this->_tableConf = $tableConf;

    // コンフィグから設定を読む
    $hostName   = Config::$database['hostname'];
    $dbName     = Config::$database['dbname'];
    $dbUser     = Config::$database['username'];
    $dbPassword = Config::$database['password'];

    // PDOを用意する
    $database = Database::getInstance($hostName, $dbName, $dbUser, $dbPassword);
    $this->_pdo = $database->getPdo();
  }

  // セレクトのSQLを実行して配列で取得する
  private function _dbSelect($sql)
  {
    try {
      $sth = $this->_pdo->prepare($sql);
      $sth->execute();
      return $sth->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      echo "Error: " . $e->getMessage();
      die();
    }
  }

  // InsertのSQLを実行する
  private function _dbInsert($insertData)
  {
    // 作成日を追加する
    $insertData['create_date'] = date('Y-m-d H:i:s');
    
    // SQLのキー部分の文字列を作成
    $keyStr = implode(',', array_keys($insertData));
    
    // SQLのVALUES部分の文字列を作成
    $placeholderArray = array();
    foreach (array_keys($insertData) as $key) {
      $placeholderArray[] = ':'. $key;
    }
    $valStr = implode(',', $placeholderArray);
    
    // SQLを作成
    $sql = "INSERT INTO {$this->_tableConf['name']} ({$keyStr}) VALUES ({$valStr})";
    try {
      $sth = $this->_pdo->prepare($sql);
      $sth->execute($insertData);
      $sth->fetchAll(\PDO::FETCH_ASSOC);
      return $this->_pdo->lastInsertId('id');
    } catch (\PDOException $e) {
      echo "Error: " . $e->getMessage();
      die();
    }
  }

  // UpdateのSQLを実行する
  private function _dbUpdate($whereArray, $setArray)
  {
    // SQLのSET部分の文字列を作成
    $sqlSetArray = array();
    foreach (array_keys($setArray) as $key) {
      $sqlSetArray[] = "{$key} = :{$key}";
    }
    $setStr = implode(',', $sqlSetArray);
    
    // SQLのWHERE部分の文字列を作成
    $sqlWhereArray = array();
    foreach ($whereArray as $key => $value) {
      $sqlWhereArray[] = "{$key} = {$value}";
    }
    $whereStr = implode(',', $sqlWhereArray);
    
    // SQLを作成
    $sql = "UPDATE {$this->_tableConf['name']} SET {$setStr} WHERE {$whereStr}";
    try {
      $sth = $this->_pdo->prepare($sql);
      $sth->execute($setArray);
      return true;
    } catch (\PDOException $e) {
      echo "Error: " . $e->getMessage();
      die();
    }
  }

  // データを一件追加する汎用関数
  public function insert($data)
  {
    return $this->_dbInsert($data);
  }

  // 全件取得する汎用関数（あまり良くない）
  public function selectAll()
  {
    $sql = 'SELECT * FROM '. $this->_tableConf['name']. ' WHERE delete_flg = 0';
    return $this->_dbSelect($sql);
  }
}
