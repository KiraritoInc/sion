<?php
// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 各種ファイルをオートロードしておく
spl_autoload_register(function ($className) {
  $className = str_replace('\\', '/', $className);
  require_once __DIR__ . '/../module/'. $className. '.php';
});

// 実行！
$module = new Sion\Module();
$module->batchRun('daily');