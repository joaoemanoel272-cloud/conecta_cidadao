<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function estaLogado()
{
    return isset($_SESSION["usuario"]);
}


function exigirLogin()
{
    if (!estaLogado()) {

        header("Location: login.php");
        exit;

    }
}


function exigirAdmin()
{
    exigirLogin();

    if ($_SESSION["usuario"]["tipo"] !== "admin") {

        http_response_code(403);

        die("Acesso negado.");

    }
}


function usuarioAtual()
{
    return $_SESSION["usuario"];
}


function proteger($valor)
{
    return htmlspecialchars(
        $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}