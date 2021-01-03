<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "15.165.106.131";
        $DB_NAME = "CoupangEats";
        $DB_USER = "jieun";
        $DB_PW = "1234";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("set names utf8"); // 포스트맨에서 ?? 로 한글 출력될때 
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}