<?php

require "../config/database.php";
require "../config/auth.php";

exigirLogin();

$usuario = usuarioAtual();

if ($usuario["tipo"] !== "admin") {
    die("Acesso negado.");
}


$mensagem = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = intval($_POST["id"]);

    $tipo = $_POST["tipo"];

    if (
        in_array(
            $tipo,
            ["cidadao", "servidor", "admin"]
        )
    ) {

        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET tipo = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $tipo,
            $id
        ]);

        $mensagem =
            "Usuário atualizado.";
    }
}


$stmt = $pdo->query("
    SELECT
        u.*,
        (
            SELECT COUNT(*)
            FROM ocorrencias o
            WHERE o.usuario_id = u.id
        ) AS total_ocorrencias
    FROM usuarios u
    ORDER BY u.id DESC
");

$usuarios =
    $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Usuários</title>

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
👥 Usuários
</h1>

<p>
Gerencie os níveis de acesso.
</p>

</div>


<?php if ($mensagem): ?>

<div class="alerta alerta-sucesso">
<?= proteger($mensagem) ?>
</div>

<?php endif; ?>


<div class="tabela-container">

<table>

<thead>

<tr>

<th>ID</th>
<th>Nome</th>
<th>E-mail</th>
<th>Tipo</th>
<th>Ocorrências</th>
<th>Ação</th>

</tr>

</thead>

<tbody>

<?php foreach ($usuarios as $u): ?>

<tr>

<td>
#<?= $u["id"] ?>
</td>

<td>
<?= proteger($u["nome"]) ?>
</td>

<td>
<?= proteger($u["email"]) ?>
</td>

<td>

<form method="POST">

<input
type="hidden"
name="id"
value="<?= $u["id"] ?>"
>

<select name="tipo">

<option
value="cidadao"
<?= $u["tipo"] === "cidadao"
? "selected"
: "" ?>
>
Cidadão
</option>

<option
value="servidor"
<?= $u["tipo"] === "servidor"
? "selected"
: "" ?>
>
Servidor
</option>

<option
value="admin"
<?= $u["tipo"] === "admin"
? "selected"
: "" ?>
>
Administrador
</option>

</select>

</td>

<td>
<?= $u["total_ocorrencias"] ?>
</td>

<td>

<button
class="botao"
type="submit"
>

Salvar

</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</main>

</body>

</html>