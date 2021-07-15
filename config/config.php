<?php

return [
    'settings' => [
        'displayErrorDetails' => getenv('DISPLAY_ERROR_DETAILS'),
        'addContentLengthHeader' => getenv('ADD_CONTENT_LENGTH_HEADER'),
    ],
    'db' => [
        'adapter' => getenv('DATABASE_ADAPTER'),
        'isDefaultTableAdapter' => getenv('IS_DEFAULT_TABLE_ADAPTER'),
        'params' => [
            'host' => getenv('PHINX_DATABASE_HOST'),
            'username' => getenv('PHINX_DATABASE_USERNAME'),
            'password' => getenv('PHINX_DATABASE_PASSWORD'),
            'dbname' => getenv('PHINX_DATABASE_NAME'),
            'driver_options' => [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"],
        ],
    ],
];
