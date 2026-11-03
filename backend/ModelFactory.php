<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/DataBase.php';
require_once __DIR__ . '/CategorieClass.php';
require_once __DIR__ . '/ProductsClass.php';
require_once __DIR__ . '/UsersClass.php';

final class ModelFactory
{
    private static ?PDO $pdo = null;
    private static function connection(): PDO
    {
        return self::$pdo ??= DataBase::connect();
    }
    public static function categories(): Categorie 
    {
         return new Categorie(self::connection());      
    }
    public static function products(): Products 
    { 
        return new Products(self::connection()); 
    }
    public static function users(): User 
    { 
        return new User(self::connection()); 
    }
}