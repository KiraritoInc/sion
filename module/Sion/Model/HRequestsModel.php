<?php
namespace Sion\Model;

class HRequestsModel extends AbstractModel
{
  public function __construct()
  {
    $table = new HRequestsTable();
    parent::__construct($table);
  }
}
