<?php

require "../config/database.php";
require "../config/auth.php";

exigirLogin();

$usuario = usuarioAtual();

if ($usuario["tipo"] !== "admin") {
    die("Acesso negado.");
}


// ==========================================
// POR STATUS
// ==========================================

$stmt = $pdo->query("

    SELECT
        status,
        COUNT(*) AS total

    FROM ocorrencias

    GROUP BY status

    ORDER BY total DESC

");

$status =
    $stmt->fetchAll();


// ==========================================
// POR CATEGORIA
// ==========================================

$stmt = $pdo->query("

    SELECT
        c.nome,
        c.icone,
        COUNT(o.id) AS total

    FROM categorias c

    LEFT JOIN ocorrencias o
        ON o.categoria_id = c.id

    GROUP BY
        c.id,
        c.nome,
        c.icone

    ORDER BY total DESC

");

$categorias =
    $stmt->fetchAll();


// ==========================================
// POR BAIRRO
// ==========================================

$stmt = $pdo->query("

    SELECT
        bairro,
        COUNT(*) AS total

    FROM ocorrencias

    GROUP BY bairro

    ORDER BY total DESC

    LIMIT 20

");

$bairros =
    $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Relatórios</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<header class="admin-header">

<div class="admin-logo">
    <img src="../assets/logo.png" alt="Minha Cidade">
    <span>Administração</span>
</div>

<nav class="admin-nav">

<a href="index.php">Dashboard</a>
<a href="ocorrencias.php">Ocorrências</a>
<a href="usuarios.php">Usuários</a>
<a href="categorias.php">Categorias</a>
<a href="relatorios.php">Relatórios</a>
<a href="../dashboard.php">Site</a>
<a href="../logout.php">Sair</a>

</nav>

</header>


<main class="admin-container">

<div class="admin-title">

<h1>
📊 Relatórios
</h1>

<p>
Dados gerais das ocorrências da cidade.
</p>

</div>


<div class="cards">

<?php foreach ($status as $s): ?>

<div class="card">

<div class="card-icone">
📌
</div>

<h3>

<?= ucfirst(
    str_replace(
        "_",
        " ",
        $s["status"]
    )
) ?>

</h3>

<div class="card-numero">

<?= $s["total"] ?>

</div>

</div>

<?php endforeach; ?>

</div>


<div class="detalhes">


<div class="tabela-container">

<h2>
📂 Por categoria
</h2>

<table>

<thead>

<tr>

<th>Categoria</th>
<th>Total</th>

</tr>

</thead>

<tbody>

<?php foreach ($categorias as $c): ?>

<tr>

<td>

<?= proteger(
    $c["icone"]
) ?>

<?= proteger(
    $c["nome"]
) ?>

</td>

<td>
<?= $c["total"] ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>


<div class="tabela-container">

<h2>
🏘️ Por bairro
</h2>

<table>

<thead>

<tr>

<th>Bairro</th>
<th>Total</th>

</tr>

</thead>

<tbody>

<?php foreach ($bairros as $b): ?>

<tr>

<td>
<?= proteger(
    $b["bairro"]
) ?>
</td>

<td>
<?= $b["total"] ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</main>

</body>

</html>