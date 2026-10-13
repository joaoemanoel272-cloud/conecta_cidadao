<?php

require "config/database.php";
require "config/auth.php";

exigirLogin();

$usuario = usuarioAtual();


// ==========================================
// TOTAL DE OCORRÊNCIAS DO USUÁRIO
// ==========================================

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM ocorrencias
    WHERE usuario_id = ?
");

$stmt->execute([
    $usuario["id"]
]);

$total = $stmt->fetchColumn();


// ==========================================
// EM ANÁLISE
// ==========================================

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM ocorrencias
    WHERE usuario_id = ?
    AND status IN (
        'registrada',
        'em_analise',
        'aprovada'
    )
");

$stmt->execute([
    $usuario["id"]
]);

$analise = $stmt->fetchColumn();


// ==========================================
// EM EXECUÇÃO
// ==========================================

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM ocorrencias
    WHERE usuario_id = ?
    AND status = 'em_execucao'
");

$stmt->execute([
    $usuario["id"]
]);

$execucao = $stmt->fetchColumn();


// ==========================================
// RESOLVIDAS
// ==========================================

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM ocorrencias
    WHERE usuario_id = ?
    AND status = 'resolvida'
");

$stmt->execute([
    $usuario["id"]
]);

$resolvidas = $stmt->fetchColumn();


// ==========================================
// OCORRÊNCIAS DO USUÁRIO
// ==========================================

$stmt = $pdo->prepare("
    SELECT
        o.*,
        c.nome AS categoria_nome,
        c.icone

    FROM ocorrencias o

    INNER JOIN categorias c
        ON c.id = o.categoria_id

    WHERE o.usuario_id = ?

    ORDER BY o.criado_em DESC
");

$stmt->execute([
    $usuario["id"]
]);

$ocorrencias = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<title>
Minha Dashboard - Minha Cidade
</title>


<link
rel="stylesheet"
href="assets/style.css"
>


<style>

.dashboard-topo {

    display: flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap: 20px;

    margin-bottom:
        30px;

}


.dashboard-topo h1 {

    margin:
        0;

}


.dashboard-topo p {

    color:
        #667085;

}


.estatisticas {

    display:
        grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap:
        20px;

    margin-bottom:
        30px;

}


.stat-card {

    background:
        white;

    border:
        1px solid #e5e7eb;

    border-radius:
        15px;

    padding:
        22px;

}


.stat-card .icone {

    font-size:
        28px;

}


.stat-card h3 {

    color:
        #667085;

    font-size:
        14px;

    margin-bottom:
        8px;

}


.stat-card strong {

    font-size:
        32px;

}


.lista {

    background:
        white;

    border:
        1px solid #e5e7eb;

    border-radius:
        15px;

    padding:
        25px;

}


.ocorrencia {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        20px;

    padding:
        18px 0;

    border-bottom:
        1px solid #eee;

}


.ocorrencia:last-child {

    border-bottom:
        none;

}


.ocorrencia h3 {

    margin:
        0 0 7px 0;

}


.ocorrencia p {

    margin:
        4px 0;

    color:
        #667085;

}


.status {

    display:
        inline-block;

    padding:
        6px 10px;

    border-radius:
        20px;

    font-size:
        12px;

    font-weight:
        bold;

}


.status-registrada {

    background:
        #e8eefc;

    color:
        #2457d6;

}


.status-em_analise {

    background:
        #fff3cd;

    color:
        #856404;

}


.status-aprovada {

    background:
        #dbeafe;

    color:
        #1d4ed8;

}


.status-em_execucao {

    background:
        #e0e7ff;

    color:
        #4338ca;

}


.status-resolvida {

    background:
        #dcfce7;

    color:
        #166534;

}


.status-cancelada {

    background:
        #fee2e2;

    color:
        #991b1b;

}


.prioridade {

    font-size:
        12px;

    font-weight:
        bold;

}


.prioridade-baixa {

    color:
        #16834b;

}


.prioridade-media {

    color:
        #b7791f;

}


.prioridade-alta {

    color:
        #d94841;

}


.prioridade-critica {

    color:
        #991b1b;

}


.ver {

    text-decoration:
        none;

    color:
        #2457d6;

    font-weight:
        bold;

}


.sem-ocorrencias {

    text-align:
        center;

    padding:
        40px;

    color:
        #667085;

}


@media(max-width:800px) {

    .estatisticas {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .ocorrencia {

        align-items:
            flex-start;

        flex-direction:
            column;

    }

}


@media(max-width:600px) {

    .dashboard-topo {

        flex-direction:
            column;

        align-items:
            flex-start;

    }


    .estatisticas {

        grid-template-columns:
            1fr;

    }

}

</style>

</head>


<body>


<header>

<div class="logo">

🏙️ Minha Cidade

</div>


<nav>

<a href="index.php">
Início
</a>

<a href="dashboard.php">
Dashboard
</a>

<a href="nova_ocorrencia.php">
Registrar problema
</a>


<?php if ($usuario["tipo"] === "admin"): ?>

<a href="admin/index.php">
Admin
</a>

<?php endif; ?>


<a href="logout.php">
Sair
</a>

</nav>

</header>


<main>


<!-- =====================================
     TOPO
===================================== -->

<section class="dashboard-topo">

<div>

<h1>

Olá,
<?= proteger($usuario["nome"]) ?>
👋

</h1>

<p>

Aqui você pode acompanhar
todos os problemas que registrou.

</p>

</div>


<a
href="nova_ocorrencia.php"
class="botao"
>

+ Registrar problema

</a>

</section>



<!-- =====================================
     ESTATÍSTICAS
===================================== -->

<section class="estatisticas">


<div class="stat-card">

<div class="icone">
📋
</div>

<h3>
TOTAL
</h3>

<strong>

<?= $total ?>

</strong>

</div>


<div class="stat-card">

<div class="icone">
🔎
</div>

<h3>
EM ANÁLISE
</h3>

<strong>

<?= $analise ?>

</strong>

</div>


<div class="stat-card">

<div class="icone">
🔧
</div>

<h3>
EM EXECUÇÃO
</h3>

<strong>

<?= $execucao ?>

</strong>

</div>


<div class="stat-card">

<div class="icone">
✅
</div>

<h3>
RESOLVIDAS
</h3>

<strong>

<?= $resolvidas ?>

</strong>

</div>


</section>



<!-- =====================================
     LISTA
===================================== -->

<section class="lista">

<h2>

📋 Minhas ocorrências

</h2>


<?php if (count($ocorrencias) === 0): ?>


<div class="sem-ocorrencias">

<h3>
Você ainda não registrou nenhum problema.
</h3>

<p>
Sua primeira contribuição pode começar agora.
</p>


<a
href="nova_ocorrencia.php"
class="botao"
>

Registrar primeira ocorrência

</a>

</div>


<?php else: ?>


<?php foreach ($ocorrencias as $ocorrencia): ?>


<div class="ocorrencia">


<div>

<h3>

<?= proteger(
    $ocorrencia["icone"]
) ?>

<?= proteger(
    $ocorrencia["titulo"]
) ?>

</h3>


<p>

📍
<?= proteger(
    $ocorrencia["bairro"]
) ?>

</p>


<p>

<?= proteger(
    $ocorrencia["categoria_nome"]
) ?>

</p>


<p>

Prioridade:

<span
class="prioridade prioridade-<?= $ocorrencia["prioridade"] ?>"
>

<?= ucfirst(
    $ocorrencia["prioridade"]
) ?>

</span>

</p>


<p>

Data:

<?= date(
    "d/m/Y H:i",
    strtotime(
        $ocorrencia["criado_em"]
    )
) ?>

</p>

</div>



<div>

<span
class="status status-<?= $ocorrencia["status"] ?>"
>

<?php

$nomesStatus = [

    "registrada"
        => "Registrada",

    "em_analise"
        => "Em análise",

    "aprovada"
        => "Aprovada",

    "em_execucao"
        => "Em execução",

    "resolvida"
        => "Resolvida",

    "cancelada"
        => "Cancelada"

];

echo $nomesStatus[
    $ocorrencia["status"]
];

?>

</span>


<br><br>


<a
class="ver"
href="ocorrencia.php?id=<?= $ocorrencia["id"] ?>"
>

Ver detalhes →

</a>

</div>


</div>


<?php endforeach; ?>


<?php endif; ?>


</section>


</main>


<footer>

Minha Cidade © 2026

</footer>


</body>

</html>