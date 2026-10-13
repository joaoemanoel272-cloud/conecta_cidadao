<?php

require "config/database.php";
require "config/auth.php";

$total = $pdo
    ->query("SELECT COUNT(*) FROM ocorrencias")
    ->fetchColumn();

$resolvidas = $pdo
    ->query("
        SELECT COUNT(*)
        FROM ocorrencias
        WHERE status = 'resolvida'
    ")
    ->fetchColumn();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<title>Minha Cidade</title>

<link
rel="stylesheet"
href="assets/style.css"
>

</head>

<body>

<header>

<div class="logo">
    <img src="assets/logo.png" alt="Minha Cidade">
</div>

<nav>

<a href="index.php">
Início
</a>

<?php if (estaLogado()): ?>

<a href="dashboard.php">
Meu painel
</a>

<a href="nova_ocorrencia.php">
Registrar problema
</a>

<?php if (usuarioAtual()["tipo"] === "admin"): ?>

<a href="admin/index.php">
Administração
</a>

<?php endif; ?>

<a href="logout.php">
Sair
</a>

<?php else: ?>

<a href="login.php">
Entrar
</a>

<a href="cadastro.php">
Criar conta
</a>

<?php endif; ?>

</nav>

</header>


<main>

<section class="hero">

<span class="tag">
PARTICIPAÇÃO CIDADÃ
</span>

<h1>
Ajude a melhorar<br>
a sua cidade.
</h1>

<p>
Registre problemas urbanos,
acompanhe as soluções e ajude
a administração pública a entender
onde os serviços são necessários.
</p>

<?php if (!estaLogado()): ?>

<a
class="botao"
href="cadastro.php"
>
Começar agora
</a>

<?php else: ?>

<a
class="botao"
href="nova_ocorrencia.php"
>
Registrar problema
</a>

<?php endif; ?>

</section>


<section class="estatisticas">

<div class="card">

<h3>
Ocorrências
</h3>

<strong>
<?= $total ?>
</strong>

</div>


<div class="card">

<h3>
Resolvidas
</h3>

<strong>
<?= $resolvidas ?>
</strong>

</div>


<div class="card">

<h3>
Participação
</h3>

<strong>
👥
</strong>

</div>


<div class="card">

<h3>
Transparência
</h3>

<strong>
🔎
</strong>

</div>

</section>

</main>


<footer>

Conecta Cidadão © 2026

</footer>

</body>

</html>