#!/usr/bin/env php
<?php
// Actually this is a simple example of a script
require __DIR__ . '/bootstrap.php';

$pinged = file_get_contents('8.8.8.8');

if ($pinged) {
    echo 'success';
} else {
    echo 'failed';
}

echo PHP_EOL;
