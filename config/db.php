<?php
class DB {
    public static function connect() {
        $host = "localhost";
        $db = "food_ordering";
        $user = "root";
        $pass = "";

        try {
            return new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (Exception $e) {
            die("DB Connection Failed");
        }
    }
}