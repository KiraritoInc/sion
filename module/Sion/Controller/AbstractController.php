<?php
namespace Sion\Controller;

use Sion\Config;
use Sion\Model\HRequestsModel;

abstract class AbstractController
{
  protected $_receive;
  protected $_webhookSettingId;
  protected $_webhookEventType;
  protected $_webhookEventTime;
  protected $_fromAccountId;
  protected $_toAccountId;
  protected $_accountId;
  protected $_roomId;
  protected $_messageId;
  protected $_body;
  protected $_sendTime;
  protected $_updateTime;
  protected $_appType;

  protected function __construct($receive)
  {
    $this->_receive = $receive;

    // 必要なデータを取り出しておく
    $this->_webhookSettingId = $this->_receive['webhook_setting_id'];
    $this->_webhookEventType = $this->_receive['webhook_event_type'];
    $this->_webhookEventTime = $this->_receive['webhook_event_time'];
    $webhookEvent = $this->_receive['webhook_event'];
    $this->_fromAccountId = array_key_exists('from_account_id', $webhookEvent) ? $webhookEvent['from_account_id'] : 0;
    $this->_toAccountId   = array_key_exists('to_account_id', $webhookEvent)   ? $webhookEvent['to_account_id']   : 0;
    $this->_accountId     = array_key_exists('account_id', $webhookEvent)      ? $webhookEvent['account_id']      : 0;
    $this->_roomId        = array_key_exists('room_id', $webhookEvent)         ? $webhookEvent['room_id']         : 0;
    $this->_messageId     = array_key_exists('message_id', $webhookEvent)      ? $webhookEvent['message_id']      : 0;
    $this->_body          = array_key_exists('body', $webhookEvent)            ? $webhookEvent['body']            : '';
    $this->_sendTime      = array_key_exists('send_time', $webhookEvent)       ? $webhookEvent['send_time']       : 0;
    $this->_updateTime    = array_key_exists('update_time', $webhookEvent)     ? $webhookEvent['update_time']     : 0;

    // それぞれの値を保存する（ログとして）
    $id = $this->_addLog();

    // appTypeを保持しておく
    $this->_appType = Config::$environment['app_type'];
  }

  // ログとして受け取ったデータを保存しておく
  private function _addLog()
  {
    $hRequestsModel = new HRequestsModel();
    $insertData = array(
      'webhook_setting_id' => $this->_webhookSettingId,
      'webhook_event_type' => $this->_webhookEventType,
      'webhook_event_time' => $this->_webhookEventTime,
      'from_account_id'    => $this->_fromAccountId,
      'to_account_id'      => $this->_toAccountId,
      'account_id'         => $this->_accountId,
      'room_id'            => $this->_roomId,
      'message_id'         => $this->_messageId,
      'body'               => $this->_body,
      'send_time'          => $this->_sendTime,
      'update_time'        => $this->_updateTime,
    );
    return $hRequestsModel->insert($insertData);
  }

  // 発言する
  protected function _response($roomId, $body)
  {
    $apiToken = Config::$chatwork['api_token'];
    $headers = ['X-ChatWorkToken: '. $apiToken];
    $option = ['body' => $body];

    $ch = curl_init('https://api.chatwork.com/v2/rooms/'.$roomId.'/messages');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($option));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);
  }
}
