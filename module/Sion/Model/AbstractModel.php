<?php
namespace Sion\Model;

abstract class AbstractModel
{
  protected $_table;

  public function __construct($table)
  {
    $this->_table = $table;
  }

  public function insert($data)
  {
    return $this->_table->insert($data);
  }
}
