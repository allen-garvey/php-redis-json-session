<?php

require_once('redis_json_session_handler.php');

$handler = new RedisJsonSessionHandler();
session_set_save_handler($handler, true);
session_start();

$_SESSION['test'] = 'hello';

echo $_SESSION['test'];
