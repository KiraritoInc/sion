<?php
namespace Sion\Controller;

class IndexController extends AbstractController
{
  public function __construct($receive)
  {
    parent::__construct($receive);
  }

  public function indexAction()
  {
    // 発言する
    if (!empty($this->_body)) {
      $this->_response($this->_roomId, $this->_body);
    }
  }
}
