<?php

require __DIR__ . '/../vendor/autoload.php';

$dotEnv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotEnv->load();

date_default_timezone_set('Asia/Yerevan');
