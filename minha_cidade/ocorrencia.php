<?php

require "config/database.php";
require "config/auth.php";

exigirLogin();


// ==========================================
// PEGAR ID DA OCORRÊNCIA
// ==========================================

$id = intval($_GET["id"] ?? 0);


if ($id <= 0) {

    die("Ocorrência inválida.");

}


// ==========================================
// BUSCAR OCORRÊNCIA
// ==========================================

$sql = "

    SELECT

        o.*,

        u.nome AS usuario_nome,

        c.nome AS categoria_nome,

        c.icone AS categoria_icone

    FROM ocorrencias o

    INNER JOIN usuarios u
        ON u.id = o.usuario_id

    INNER JOIN categorias c
        ON c.id = o.categoria_id

    WHERE o.id = ?

    LIMIT 1

";


$stmt = $pdo->prepare($sql);

$stmt->execute([$id]);

$ocorrencia = $stmt->fetch();


if (!$ocorrencia) {

    http_response_code(404);

    die("Ocorrência não encontrada.");

}


// ==========================================
// VERIFICAR ACESSO
// ==========================================
//
// Cidadão só pode visualizar a própria
// ocorrência.
//
// Admin pode visualizar qualquer uma.
// ==========================================

$usuario = usuarioAtual();


if (
    $usuario["tipo"] !== "admin"
    &&
    $ocorrencia["usuario_id"] != $usuario["id"]
) {

    http_response_code(403);

    die("Você não possui permissão para visualizar esta ocorrência.");

}


// ==========================================
// BUSCAR HISTÓRICO
// ==========================================

$sqlHistorico = "

    SELECT

        h.*,

        u.nome AS usuario_nome

    FROM historico_ocorrencias h

    INNER JOIN usuarios u
        ON u.id = h.usuario_id

    WHERE h.ocorrencia_id = ?

    ORDER BY h.criado_em ASC

";


$stmtHistorico =
    $pdo->prepare($sqlHistorico);

$stmtHistorico->execute([$id]);

$historico =
    $stmtHistorico->fetchAll();


// ==========================================
// STATUS
// ==========================================

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


$iconeStatus = [

    "registrada"
        => "📝",

    "em_analise"
        => "🔎",

    "aprovada"
        => "👍",

    "em_execucao"
        => "🔧",

    "resolvida"
        => "✅",

    "cancelada"
        => "❌"

];


$statusTexto =
    $nomesStatus[
        $ocorrencia["status"]
    ] ?? $ocorrencia["status"];


$statusIcone =
    $iconeStatus[
        $ocorrencia["status"]
    ] ?? "📌";


// ==========================================
// PRIORIDADE
// ==========================================

$nomesPrioridade = [

    "baixa" =>
        "Baixa",

    "media" =>
        "Média",

    "alta" =>
        "Alta",

    "critica" =>
        "Crítica"

];


$prioridadeTexto =
    $nomesPrioridade[
        $ocorrencia["prioridade"]
    ] ?? $ocorrencia["prioridade"];


// ==========================================
// FOTO
// ==========================================

$temFoto =
    !empty($ocorrencia["foto"]);


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

Ocorrência #<?= $ocorrencia["id"] ?>

-
Minha Cidade

</title>


<link
    rel="stylesheet"
    href="assets/style.css"
>


<!-- =====================================
     LEAFLET
===================================== -->

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>


<style>

/* =========================================
   CONTAINER
========================================= */

.ocorrencia-container {

    max-width:
        1100px;

    margin:
        30px auto;

}


/* =========================================
   CABEÇALHO
========================================= */

.topo-ocorrencia {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        20px;

    margin-bottom:
        25px;

}


.numero {

    color:
        #667085;

    font-size:
        14px;

}


.topo-ocorrencia h1 {

    margin:
        5px 0;

}


/* =========================================
   GRID PRINCIPAL
========================================= */

.grid-ocorrencia {

    display:
        grid;

    grid-template-columns:
        2fr 1fr;

    gap:
        25px;

}


/* =========================================
   CARDS
========================================= */

.card-ocorrencia {

    background:
        white;

    border:
        1px solid #e5e7eb;

    border-radius:
        15px;

    padding:
        25px;

    margin-bottom:
        25px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.04);

}


/* =========================================
   TÍTULO
========================================= */

.titulo-ocorrencia {

    margin-top:
        0;

    font-size:
        28px;

}


/* =========================================
   DESCRIÇÃO
========================================= */

.descricao {

    color:
        #475467;

    line-height:
        1.7;

    white-space:
        pre-wrap;

}


/* =========================================
   INFORMAÇÕES
========================================= */

.info-grid {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        20px;

}


.info-item {

    border-bottom:
        1px solid #eee;

    padding-bottom:
        12px;

}


.info-item small {

    display:
        block;

    color:
        #667085;

    margin-bottom:
        5px;

}


.info-item strong {

    font-size:
        15px;

}


/* =========================================
   STATUS
========================================= */

.status-grande {

    padding:
        18px;

    border-radius:
        12px;

    text-align:
        center;

    font-weight:
        bold;

    font-size:
        18px;

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


/* =========================================
   PRIORIDADE
========================================= */

.prioridade {

    display:
        inline-block;

    padding:
        7px 12px;

    border-radius:
        20px;

    font-weight:
        bold;

    font-size:
        13px;

}


.prioridade-baixa {

    background:
        #dcfce7;

    color:
        #166534;

}


.prioridade-media {

    background:
        #fef3c7;

    color:
        #92400e;

}


.prioridade-alta {

    background:
        #ffedd5;

    color:
        #c2410c;

}


.prioridade-critica {

    background:
        #fee2e2;

    color:
        #991b1b;

}


/* =========================================
   FOTO
========================================= */

.foto-ocorrencia {

    width:
        100%;

    max-height:
        500px;

    object-fit:
        cover;

    border-radius:
        12px;

    display:
        block;

}


/* =========================================
   MAPA
========================================= */

#map {

    width:
        100%;

    height:
        450px;

    border-radius:
        12px;

}


.sem-localizacao {

    padding:
        30px;

    background:
        #f5f7fb;

    border-radius:
        10px;

    text-align:
        center;

    color:
        #667085;

}


/* =========================================
   HISTÓRICO
========================================= */

.timeline {

    position:
        relative;

    margin-top:
        20px;

}


.timeline-item {

    position:
        relative;

    padding:
        0 0 25px 35px;

    border-left:
        2px solid #dbe1ea;

}


.timeline-item:last-child {

    border-left:
        2px solid transparent;

}


.timeline-bolinha {

    position:
        absolute;

    left:
        -9px;

    top:
        0;

    width:
        16px;

    height:
        16px;

    border-radius:
        50%;

    background:
        #2457d6;

    border:
        3px solid white;

    box-shadow:
        0 0 0 1px #2457d6;

}


.timeline-item h4 {

    margin:
        0 0 5px 0;

}


.timeline-item p {

    margin:
        5px 0;

    color:
        #667085;

}


.timeline-data {

    font-size:
        12px;

    color:
        #98a2b3;

}


/* =========================================
   BOTÃO
========================================= */

.botao-voltar {

    display:
        inline-block;

    padding:
        11px 16px;

    border-radius:
        8px;

    background:
        #eef2f7;

    color:
        #172033;

    text-decoration:
        none;

    font-weight:
        bold;

}


.botao-voltar:hover {

    background:
        #e2e8f0;

}


/* =========================================
   RESPONSIVO
========================================= */

@media(max-width:800px) {

    .grid-ocorrencia {

        grid-template-columns:
            1fr;

    }


    .info-grid {

        grid-template-columns:
            1fr;

    }


    .topo-ocorrencia {

        flex-direction:
            column;

        align-items:
            flex-start;

    }

}

</style>

</head>


<body>


<!-- =====================================
     HEADER
===================================== -->

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


<?php if (
    $usuario["tipo"] === "admin"
): ?>

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


<div class="ocorrencia-container">


<!-- =====================================
     TOPO
===================================== -->

<div class="topo-ocorrencia">

<div>

<div class="numero">

OCORRÊNCIA
#<?= $ocorrencia["id"] ?>

</div>


<h1>

<?= proteger(
    $ocorrencia["titulo"]
) ?>

</h1>

</div>


<a
    href="dashboard.php"
    class="botao-voltar"
>

← Voltar

</a>

</div>



<!-- =====================================
     GRID
===================================== -->

<div class="grid-ocorrencia">


<!-- =====================================
     COLUNA PRINCIPAL
===================================== -->

<div>


<!-- =====================================
     DESCRIÇÃO
===================================== -->

<div class="card-ocorrencia">

<h2>
📄 Descrição
</h2>


<p class="descricao">

<?= proteger(
    $ocorrencia["descricao"]
) ?>

</p>

</div>



<!-- =====================================
     LOCALIZAÇÃO
===================================== -->

<div class="card-ocorrencia">

<h2>
📍 Localização
</h2>


<div class="info-grid">


<div class="info-item">

<small>
Endereço
</small>

<strong>

<?= proteger(
    $ocorrencia["endereco"]
) ?>

</strong>

</div>


<div class="info-item">

<small>
Bairro
</small>

<strong>

<?= proteger(
    $ocorrencia["bairro"]
) ?>

</strong>

</div>

</div>


<br>


<?php

$temCoordenadas =
    $ocorrencia["latitude"] !== null
    &&
    $ocorrencia["longitude"] !== null;

?>


<?php if ($temCoordenadas): ?>


<div id="map"></div>


<?php else: ?>


<div class="sem-localizacao">

📍

Esta ocorrência não possui
uma localização marcada no mapa.

</div>


<?php endif; ?>


</div>



<!-- =====================================
     FOTO
===================================== -->

<?php if ($temFoto): ?>


<div class="card-ocorrencia">

<h2>
📷 Foto
</h2>


<img
    class="foto-ocorrencia"
    src="uploads/<?= proteger(
        $ocorrencia["foto"]
    ) ?>"
    alt="Foto da ocorrência"
>


</div>


<?php endif; ?>



<!-- =====================================
     HISTÓRICO
===================================== -->

<div class="card-ocorrencia">

<h2>
📜 Histórico
</h2>


<?php if (
    count($historico) === 0
): ?>


<p>

Nenhuma atualização registrada.

</p>


<?php else: ?>


<div class="timeline">


<?php foreach (
    $historico
    as $item
): ?>


<div class="timeline-item">


<div class="timeline-bolinha">
</div>


<h4>

<?php

$novoStatus =
    $nomesStatus[
        $item["status_novo"]
    ] ?? $item["status_novo"];

echo $iconeStatus[
    $item["status_novo"]
] ?? "📌";

?>

<?= proteger(
    $novoStatus
) ?>

</h4>


<p>

<?= proteger(
    $item["observacao"]
    ?? ""
) ?>

</p>


<p>

👤

<?= proteger(
    $item["usuario_nome"]
) ?>

</p>


<div class="timeline-data">

<?= date(
    "d/m/Y H:i",
    strtotime(
        $item["criado_em"]
    )
) ?>

</div>


</div>


<?php endforeach; ?>


</div>


<?php endif; ?>


</div>


</div>



<!-- =====================================
     SIDEBAR
===================================== -->

<div>


<!-- =====================================
     STATUS
===================================== -->

<div class="card-ocorrencia">

<h2>
Status
</h2>


<div
class="status-grande status-<?= $ocorrencia["status"] ?>"
>

<?= $statusIcone ?>

<?= proteger(
    $statusTexto
) ?>

</div>

</div>



<!-- =====================================
     INFORMAÇÕES
===================================== -->

<div class="card-ocorrencia">

<h2>
ℹ️ Informações
</h2>


<div class="info-grid">


<div class="info-item">

<small>
Categoria
</small>

<strong>

<?= proteger(
    $ocorrencia["categoria_icone"]
) ?>

<?= proteger(
    $ocorrencia["categoria_nome"]
) ?>

</strong>

</div>


<div class="info-item">

<small>
Prioridade
</small>


<span
class="prioridade prioridade-<?= $ocorrencia["prioridade"] ?>"
>

<?= proteger(
    $prioridadeTexto
) ?>

</span>

</div>


<div class="info-item">

<small>
Registrado por
</small>

<strong>

<?= proteger(
    $ocorrencia["usuario_nome"]
) ?>

</strong>

</div>


<div class="info-item">

<small>
Data
</small>

<strong>

<?= date(
    "d/m/Y",
    strtotime(
        $ocorrencia["criado_em"]
    )
) ?>

</strong>

</div>


<div class="info-item">

<small>
Horário
</small>

<strong>

<?= date(
    "H:i",
    strtotime(
        $ocorrencia["criado_em"]
    )
) ?>

</strong>

</div>


<?php if (
    $ocorrencia["atualizado_em"]
): ?>


<div class="info-item">

<small>
Última atualização
</small>

<strong>

<?= date(
    "d/m/Y H:i",
    strtotime(
        $ocorrencia["atualizado_em"]
    )
) ?>

</strong>

</div>


<?php endif; ?>


</div>

</div>



<!-- =====================================
     COORDENADAS
===================================== -->

<?php if ($temCoordenadas): ?>


<div class="card-ocorrencia">

<h2>
🌎 Coordenadas
</h2>


<div class="info-item">

<small>
Latitude
</small>

<strong>

<?= $ocorrencia["latitude"] ?>

</strong>

</div>


<br>


<div class="info-item">

<small>
Longitude
</small>

<strong>

<?= $ocorrencia["longitude"] ?>

</strong>

</div>


</div>


<?php endif; ?>


</div>


</div>


</div>


</main>



<footer>

Minha Cidade © 2026

</footer>



<!-- =====================================
     LEAFLET
===================================== -->

<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>


<?php if ($temCoordenadas): ?>


<script>

// =======================================
// COORDENADAS VINDAS DO PHP
// =======================================

const latitude =
    <?= json_encode(
        floatval(
            $ocorrencia["latitude"]
        )
    ) ?>;


const longitude =
    <?= json_encode(
        floatval(
            $ocorrencia["longitude"]
        )
    ) ?>;


// =======================================
// CRIAR MAPA
// =======================================

const map =
    L.map("map")
     .setView(
        [
            latitude,
            longitude
        ],
        17
     );


// =======================================
// OPENSTREETMAP
// =======================================

L.tileLayer(
    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
    {

        maxZoom:
            19,

        attribution:
            "&copy; OpenStreetMap contributors"

    }
).addTo(map);


// =======================================
// MARCADOR
// =======================================

const marcador =
    L.marker([
        latitude,
        longitude
    ])
    .addTo(map);


// =======================================
// POPUP
// =======================================

marcador
    .bindPopup(
        `
        <b>
            <?= htmlspecialchars(
                $ocorrencia["titulo"],
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </b>

        <br><br>

        📍
        <?= htmlspecialchars(
            $ocorrencia["bairro"],
            ENT_QUOTES,
            "UTF-8"
        ) ?>

        `
    )
    .openPopup();

</script>


<?php endif; ?>


</body>

</html>