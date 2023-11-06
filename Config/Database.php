<?php

namespace Config;

class Database
{
    public const default = 'mysql';

    public const connections = [
        "mysql" => [
            'driver' => 'mysql',
            'url' => 'localhost',
            'port' => 3306,
            'database' => 'permission',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8mb4',
        ]
    ];
}
