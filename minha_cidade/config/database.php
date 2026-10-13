<?php

$host = "localhost";
$db = "minha_cidade";
$user = "root";
$senha = "alunoifro";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $senha
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

?>