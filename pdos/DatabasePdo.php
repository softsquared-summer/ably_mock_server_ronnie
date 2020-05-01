<?php
//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "3.34.80.36";
        $DB_NAME = "Ably";
        $DB_USER = "root";
        $DB_PW = "jjw969587!";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}
?>