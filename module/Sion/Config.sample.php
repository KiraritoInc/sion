<?php
namespace Sion;

Class Config
{
  // チャットワーク用の設定
  public static $chatwork = array(
    'api_token' => 'Please input your API token.', // ex: 67a7102568079c06e57607..........
    'sion_account_id' => 'Please input your account id.', // ex: 3586...
  );

  // データベース設定
  public static $database = array(
    'hostname' => 'Please input your database host name.', // ex: localhost, 127.0.0.1, and so on
    'dbname'   => 'Please input your database name.',
    'username' => 'Please input your database user name.',
    'password' => 'Please input your database user password.',
  );
}
