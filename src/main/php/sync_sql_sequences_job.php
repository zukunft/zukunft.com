<?php

define('ROOT_PATH_DIR', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);
const PHP_PATH_DIR = ROOT_PATH_DIR . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;

include_once PHP_PATH_DIR . 'cfg' . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'sql_sync_sequences.php';

$syncObj = new sync_sequences();
$syncObj->run();
