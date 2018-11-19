<?php
namespace Sion\Model;

class HRequestsTable extends AbstractTable
{
  private $_tableConf = array(
    'name' => 'h_requests',
  );

  public function __construct()
  {
    parent::__construct($this->_tableConf);
  }
}
