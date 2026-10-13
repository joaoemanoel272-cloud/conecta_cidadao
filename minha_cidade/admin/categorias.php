<?php

require "../config/database.php";
require "../config/auth.php";

exigirLogin();

$usuario = usuarioAtual();

if ($usuario["tipo"] !== "admin") {
    die("Acesso negado.");
}


$mensagem = "";

$erro = "";


// ==========================================
// ADICIONAR
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["acao"])
    &&
    $_POST["acao"] === "adicionar"
) {

    $nome =
        trim($_POST["nome"] ?? "");

    $icone =
        trim($_POST["icone"] ?? "📌");


    if ($nome === "") {

        $erro =
            "Digite o nome da categoria.";

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO categorias
            (nome, icone)
            VALUES (?, ?)
        ");

        $stmt->execute([
            $nome,
            $icone
        ]);

        $mensagem =
            "Categoria criada.";

    }

}


// ==========================================
// EXCLUIR
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["acao"])
    &&
    $_POST["acao"] === "excluir"
) {

    $id =
        intval($_POST["id"]);


    try {

        $stmt = $pdo->prepare("
            DELETE FROM categorias
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $mensagem =
            "Categoria excluída.";

    } catch (PDOException $e) {

        $erro =
            "Não é possível excluir uma categoria que possui ocorrências.";

    }

}


// ==========================================
// LISTAR
// ==========================================

$stmt = $pdo->query("

    SELECT
        c.*,

        (
            SELECT COUNT(*)
            FROM ocorrencias o
            WHERE o.categoria_id = c.id
        ) AS total

    FROM categorias c

    ORDER BY c.nome

");

$categorias =
    $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Categorias</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<header class="admin-header">

<div class="admin-logo">
🏛️ Minha Cidade
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
📂 Categorias
</h1>

<p>
Gerencie os tipos de problemas da cidade.
</p>

</div>


<?php if ($mensagem): ?>

<div class="alerta alerta-sucesso">
<?= proteger($mensagem) ?>
</div>

<?php endif; ?>


<?php if ($erro): ?>

<div class="alerta alerta-erro">
<?= proteger($erro) ?>
</div>

<?php endif; ?>


<div class="formulario">

<h2>
➕ Nova categoria
</h2>

<form method="POST">

<input
type="hidden"
name="acao"
value="adicionar"
>

<label>
Nome
</label>

<input
type="text"
name="nome"
placeholder="Ex: Iluminação pública"
required
>


<label>
Ícone
</label>

<input
type="text"
name="icone"
placeholder="💡"
value="📌"
>


<button
class="botao"
type="submit"
>

Criar categoria

</button>

</form>

</div>


<div class="tabela-container">

<table>

<thead>

<tr>

<th>ID</th>
<th>Ícone</th>
<th>Nome</th>
<th>Ocorrências</th>
<th>Ação</th>

</tr>

</thead>

<tbody>

<?php foreach ($categorias as $c): ?>

<tr>

<td>
#<?= $c["id"] ?>
</td>

<td style="font-size:25px">

<?= proteger(
    $c["icone"]
) ?>

</td>

<td>
<?= proteger(
    $c["nome"]
) ?>
</td>

<td>
<?= $c["total"] ?>
</td>

<td>

<form
method="POST"
onsubmit="
return confirm(
'Excluir esta categoria?'
);
"
>

<input
type="hidden"
name="acao"
value="excluir"
>

<input
type="hidden"
name="id"
value="<?= $c["id"] ?>"
>

<button
class="botao botao-perigo"
type="submit"
>

Excluir

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