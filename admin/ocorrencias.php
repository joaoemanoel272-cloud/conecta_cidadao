<?php

require "../config/database.php";
require "../config/auth.php";

exigirLogin();

$usuario = usuarioAtual();

if ($usuario["tipo"] !== "admin") {
    http_response_code(403);
    die("Acesso negado.");
}


$status = $_GET["status"] ?? "";

$prioridade = $_GET["prioridade"] ?? "";

$busca = trim($_GET["busca"] ?? "");


$sql = "
    SELECT
        o.*,
        u.nome AS usuario_nome,
        c.nome AS categoria_nome,
        c.icone
    FROM ocorrencias o
    INNER JOIN usuarios u
        ON u.id = o.usuario_id
    INNER JOIN categorias c
        ON c.id = o.categoria_id
    WHERE 1=1
";

$params = [];


if ($status !== "") {

    $sql .= "
        AND o.status = ?
    ";

    $params[] = $status;
}


if ($prioridade !== "") {

    $sql .= "
        AND o.prioridade = ?
    ";

    $params[] = $prioridade;
}


if ($busca !== "") {

    $sql .= "
        AND (
            o.titulo LIKE ?
            OR o.bairro LIKE ?
            OR o.endereco LIKE ?
        )
    ";

    $termo = "%".$busca."%";

    $params[] = $termo;
    $params[] = $termo;
    $params[] = $termo;
}


$sql .= "
    ORDER BY o.criado_em DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$ocorrencias = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Ocorrências - Administração</title>

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
📋 Ocorrências
</h1>

<p>
Gerencie todos os problemas registrados pelos cidadãos.
</p>

</div>


<div class="formulario">

<form method="GET">

<div class="filtros">

<input
type="text"
name="busca"
placeholder="Pesquisar..."
value="<?= proteger($busca) ?>"
>


<select name="status">

<option value="">
Todos os status
</option>

<option value="registrada"
<?= $status === "registrada" ? "selected" : "" ?>>
Registrada
</option>

<option value="em_analise"
<?= $status === "em_analise" ? "selected" : "" ?>>
Em análise
</option>

<option value="aprovada"
<?= $status === "aprovada" ? "selected" : "" ?>>
Aprovada
</option>

<option value="em_execucao"
<?= $status === "em_execucao" ? "selected" : "" ?>>
Em execução
</option>

<option value="resolvida"
<?= $status === "resolvida" ? "selected" : "" ?>>
Resolvida
</option>

<option value="cancelada"
<?= $status === "cancelada" ? "selected" : "" ?>>
Cancelada
</option>

</select>


<select name="prioridade">

<option value="">
Todas as prioridades
</option>

<option value="baixa"
<?= $prioridade === "baixa" ? "selected" : "" ?>>
Baixa
</option>

<option value="media"
<?= $prioridade === "media" ? "selected" : "" ?>>
Média
</option>

<option value="alta"
<?= $prioridade === "alta" ? "selected" : "" ?>>
Alta
</option>

<option value="critica"
<?= $prioridade === "critica" ? "selected" : "" ?>>
Crítica
</option>

</select>


<button class="botao" type="submit">
🔎 Filtrar
</button>

</div>

</form>

</div>


<div class="tabela-container">

<table>

<thead>

<tr>

<th>ID</th>
<th>Problema</th>
<th>Cidadão</th>
<th>Bairro</th>
<th>Prioridade</th>
<th>Status</th>
<th>Data</th>
<th>Ação</th>

</tr>

</thead>

<tbody>

<?php if (!$ocorrencias): ?>

<tr>

<td colspan="8">

Nenhuma ocorrência encontrada.

</td>

</tr>

<?php endif; ?>


<?php foreach ($ocorrencias as $o): ?>

<tr>

<td>
#<?= $o["id"] ?>
</td>

<td>

<?= proteger($o["icone"]) ?>

<?= proteger($o["titulo"]) ?>

</td>

<td>
<?= proteger($o["usuario_nome"]) ?>
</td>

<td>
<?= proteger($o["bairro"]) ?>
</td>

<td>
<?= ucfirst($o["prioridade"]) ?>
</td>

<td>

<span class="status status-<?= $o["status"] ?>">

<?= ucfirst(
    str_replace(
        "_",
        " ",
        $o["status"]
    )
) ?>

</span>

</td>

<td>

<?= date(
    "d/m/Y H:i",
    strtotime($o["criado_em"])
) ?>

</td>

<td>

<a
href="ocorrencia.php?id=<?= $o["id"] ?>"
class="botao"
>
Gerenciar
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