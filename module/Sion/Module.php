<?php
namespace Sion;

use Sion\Controller\IndexController;

Class Module
{
  private $_receive;

  public function __construct()
  {
    // 送られてきたデータを取り出して保持しておく
    $raw = file_get_contents('php://input');
    $this->_receive = json_decode($raw, true);
  }

  public function run()
  {
    // データが無ければ終了させる
    if (empty($this->_receive)) {
      return;
    }

    // 自分の発言は無視する
    $sionAccountId = Config::$chatwork['sion_account_id'];
    if ($this->_receive['webhook_event']['account_id'] == $sionAccountId) {
      return;
    }

    // コントローラー実行
    $indexController = new IndexController($this->_receive);
    $indexController->indexAction();
  }
}
