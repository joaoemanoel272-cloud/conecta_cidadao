<?php

$host = "localhost";
$db = "minha_cidade";
$user = "root";
$pass = "SENHAbancodedados2026";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    die(
        "Erro ao conectar com o banco: "
        . $e->getMessage()
    );

}