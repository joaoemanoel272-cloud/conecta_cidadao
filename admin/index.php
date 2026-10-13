<?php

require "../config/database.php";
require "../config/auth.php";

exigirLogin();

$usuario = usuarioAtual();

if ($usuario["tipo"] !== "admin") {
    http_response_code(403);
    die("Acesso negado.");
}


// TOTAL

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM ocorrencias
");

$total = $stmt->fetchColumn();


// REGISTRADAS

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM ocorrencias
    WHERE status = 'registrada'
");

$registradas = $stmt->fetchColumn();


// EM ANÁLISE

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM ocorrencias
    WHERE status = 'em_analise'
");

$analise = $stmt->fetchColumn();


// EM EXECUÇÃO

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM ocorrencias
    WHERE status = 'em_execucao'
");

$execucao = $stmt->fetchColumn();


// RESOLVIDAS

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM ocorrencias
    WHERE status = 'resolvida'
");

$resolvidas = $stmt->fetchColumn();


// USUÁRIOS

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM usuarios
");

$usuarios = $stmt->fetchColumn();


// ÚLTIMAS OCORRÊNCIAS

$stmt = $pdo->query("
    SELECT
        o.id,
        o.titulo,
        o.status,
        o.prioridade,
        o.criado_em,
        u.nome AS usuario_nome,
        c.nome AS categoria_nome,
        c.icone
    FROM ocorrencias o
    INNER JOIN usuarios u
        ON u.id = o.usuario_id
    INNER JOIN categorias c
        ON c.id = o.categoria_id
    ORDER BY o.criado_em DESC
    LIMIT 10
");

$ultimas = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Admin - Minha Cidade</title>

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
Dashboard administrativa
</h1>

<p>
Visão geral do funcionamento da cidade.
</p>

</div>


<div class="cards">


<div class="card">

<div class="card-icone">📋</div>

<h3>Total de ocorrências</h3>

<div class="card-numero">
<?= $total ?>
</div>

</div>


<div class="card">

<div class="card-icone">📝</div>

<h3>Registradas</h3>

<div class="card-numero">
<?= $registradas ?>
</div>

</div>


<div class="card">

<div class="card-icone">🔎</div>

<h3>Em análise</h3>

<div class="card-numero">
<?= $analise ?>
</div>

</div>


<div class="card">

<div class="card-icone">🔧</div>

<h3>Em execução</h3>

<div class="card-numero">
<?= $execucao ?>
</div>

</div>


<div class="card">

<div class="card-icone">✅</div>

<h3>Resolvidas</h3>

<div class="card-numero">
<?= $resolvidas ?>
</div>

</div>


<div class="card">

<div class="card-icone">👥</div>

<h3>Usuários</h3>

<div class="card-numero">
<?= $usuarios ?>
</div>

</div>


</div>


<div class="tabela-container">

<h2>
📋 Últimas ocorrências
</h2>

<table>

<thead>

<tr>

<th>ID</th>
<th>Problema</th>
<th>Cidadão</th>
<th>Categoria</th>
<th>Prioridade</th>
<th>Status</th>
<th>Ação</th>

</tr>

</thead>

<tbody>

<?php foreach ($ultimas as $item): ?>

<tr>

<td>
#<?= $item["id"] ?>
</td>

<td>
<?= proteger($item["icone"]) ?>
<?= proteger($item["titulo"]) ?>
</td>

<td>
<?= proteger($item["usuario_nome"]) ?>
</td>

<td>
<?= proteger($item["categoria_nome"]) ?>
</td>

<td>
<?= ucfirst($item["prioridade"]) ?>
</td>

<td>

<span class="status status-<?= $item["status"] ?>">

<?= ucfirst(
    str_replace(
        "_",
        " ",
        $item["status"]
    )
) ?>

</span>

</td>

<td>

<a
class="botao"
href="ocorrencia.php?id=<?= $item["id"] ?>"
>
Ver
</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</main>

</body>

</html>