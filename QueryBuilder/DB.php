<?php

namespace QueryBuilder;

use Config\Database;
use PDO;
use PDOException;
use QueryBuilder\Src\Getter;

class DB
{
    private static $instance = null;

    public static function getInstance()
    {
        if (!self::$instance) {
            try {
                $pdo = match (Database::default) {
                    'mysql' => new PDO ("mysql:host=" . (Database::connections['mysql']['url'] ?? null) . "; 
                                             dbname=" . (Database::connections['mysql']['database'] ?? null) . "; 
                                             charset=" . (Database::connections['mysql']['charset'] ?? null),
                                             (Database::connections['mysql']['username'] ?? null), (Database::connections['mysql']['username'] ?? null)),
                    'default' => false
                };
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
            self::$instance = new Getter($pdo);
        }
        return self::$instance;
    }

    public static function __callStatic($method, $arguments)
    {
        $instance = self::getInstance();
        return call_user_func_array([$instance, $method], $arguments);
    }


}