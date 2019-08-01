<?php
namespace Sion\Controller;

use Sion\Config;

class BatchController extends AbstractController
{
  const ROOM_ID_SION_GROUP = 128601226;
  const ROOM_ID_SAKAKIBARA = 128524992;

  const ALERT_BEFORE_EXPIRE_DAY = 20; // SSLの有効期限切れ前にアラートを出す日数

  const ERROR_MSG_WEB = "[toall]\n%sのWEBアクセスができません。\n至急WEBサイトを確認してください！";
  const ERROR_MSG_SSL = "[toall]\n%sのSSL証明書の有効期限が残り%d日を切りました。\n証明書が更新されていませんので確認してください！";

  private $webCheckUrls = [
    'https://gbox.art/',
    'https://www.gbox.art/application/index/login/',
    'http://dragon.sc/login/form/',
    'http://graphicker.me/',
    'http://kirarito.co.jp/',
    'http://kirarito.co.jp/en/',
    'http://kirarito.co.jp/lpa/',
    'http://kirarito.co.jp/lpb/',
    'http://futarigurashi.kirarito.co.jp/index/',
  ];

  private $sslDomains = [
    ['check_domain' => 'gbox.art', 'ssl_domain' => 'gbox.art'],
    ['check_domain' => 'www.gbox.art', 'ssl_domain' => 'www.gbox.art'],
    ['check_domain' => 'mysql.gbox.art', 'ssl_domain' => 'mysql.gbox.art'],
    ['check_domain' => 'test1.gbox.art', 'ssl_domain' => '*.gbox.art'],
  ];

  public function __construct()
  {
    // appTypeを保持しておく
    $this->_appType = Config::$environment['app_type'];
  }

  /**
   * ５分に一回実行される処理
   */
  public function fiveMinutelyAction()
  {
    // WEBサイトをチェックする
    $this->_checkWebSites();
  }

  /**
   * 一日に一回実行される処理
   */
  public function dailyAction()
  {
    // SSLの日数をチェックする
    $this->_checkSSL();
  }

  // 送信先のルームIDを取得する
  private function _getRoomId()
  {
    $roomId = 0;
    switch ($this->_appType) {
      case Config::APP_TYPE_LOCAL:
        $roomId = self::ROOM_ID_SAKAKIBARA;
        break;
      case Config::APP_TYPE_DEVELOP:
      case Config::APP_TYPE_STAGING:
      case Config::APP_TYPE_LIVE:
        $roomId = self::ROOM_ID_SION_GROUP;
        break;
    }

    return $roomId;
  }

  // WEBサイトをチェックする
  private function _checkWebSites()
  {
    $roomId = $this->_getRoomId();

    foreach ($this->webCheckUrls as $url) {
      $status = $this->_webAccess($url);
      if (!$status) {
        // チェックが通らなかった場合、エラーメッセージを送る
        $errorMsg = sprintf(self::ERROR_MSG_WEB, $url);
        $this->_response($roomId, $errorMsg);
      }
    }
  }

  // WEBサイトの生存確認
  private function _webAccess($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); // URLを指定する
    curl_setopt($ch, CURLOPT_HEADER, true); // ヘッダ情報を付加
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // リダイレクトの最大回数
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 出力しない
    curl_setopt($ch, CURLOPT_USERAGENT, "Sion"); // ユーザーエージェントをセットする
    $ret = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($this->_appType == Config::APP_TYPE_LOCAL) {
      error_log($url. ' -> '. $info['http_code']);
    }

    $status = false;
    if ($info['http_code'] == 200) {
      $status = true;
    } else {
      if ($this->_appType == Config::APP_TYPE_LOCAL) {
        error_log($ret);
      }
    }
    
    return $status;
  }

  // SSLの有効期限を確認
  private function _checkSSL()
  {
    $roomId = $this->_getRoomId();

    $alertTime = 60 * 60 * 24 * self::ALERT_BEFORE_EXPIRE_DAY;
    $nowTime = time();

    foreach ($this->sslDomains as $domain) {
      $expireDate = $this->_getSSLExpireDate($domain['check_domain'], $domain['ssl_domain']);
      $expireTime = strtotime($expireDate);

      if ($this->_appType == Config::APP_TYPE_LOCAL) {
        error_log($domain['check_domain']. ' -> '. $expireDate);
      }

      // 期限切れが近づいていないかチェックする
      if ($expireTime <= $nowTime + $alertTime) {
        
        // チェックが通らなかった場合、エラーメッセージを送る
        $errorMsg = sprintf(self::ERROR_MSG_SSL, $domain['check_domain'], self::ALERT_BEFORE_EXPIRE_DAY);
        $this->_response($roomId, $errorMsg);

        if ($this->_appType == Config::APP_TYPE_LOCAL) {
          error_log($domain['check_domain']. ' will be expired soon!');
        }
      }
    }
  }

  // SSLの有効期限を取得する
  private function _getSSLExpireDate($checkDomain, $sslDomain)
  {
    $streamContext = stream_context_create([
      'ssl' => ['capture_peer_cert' => true]
    ]);

    $resource = stream_socket_client(
      'ssl://' . $checkDomain . ':443', // 接続するソケットのアドレス
      $errno, // 接続に失敗した場合にシステムレベルのエラー番号が設定される
      $errstr, // 接続に失敗した場合にシステムレベルのエラーメッセージが設定される
      30, // connect() システムコールがタイムアウトとなるまでの秒数
      STREAM_CLIENT_CONNECT, // クライアントソケット接続を開く
      $streamContext
    );

    $cont = stream_context_get_params($resource);
    $parsed = openssl_x509_parse($cont['options']['ssl']['peer_certificate']);

    if ($parsed['subject']['CN'] == $sslDomain) {
      return date('Y/m/d', $parsed['validTo_time_t']);
    } else {
      return null;
    }
  }
}
