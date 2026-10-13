<?php

require "../config/database.php";
require "../config/auth.php";

exigirLogin();

$usuario = usuarioAtual();

if ($usuario["tipo"] !== "admin") {
    http_response_code(403);
    die("Acesso negado.");
}


$id = intval($_GET["id"] ?? 0);


if ($id <= 0) {
    die("Ocorrência inválida.");
}


// ==========================================
// ATUALIZAR STATUS
// ==========================================

$mensagem = "";

$erro = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $novoStatus =
        $_POST["status"] ?? "";

    $observacao =
        trim(
            $_POST["observacao"] ?? ""
        );


    $statusPermitidos = [

        "registrada",
        "em_analise",
        "aprovada",
        "em_execucao",
        "resolvida",
        "cancelada"

    ];


    if (
        !in_array(
            $novoStatus,
            $statusPermitidos
        )
    ) {

        $erro =
            "Status inválido.";

    } else {


        try {

            $pdo->beginTransaction();


            // BUSCAR STATUS ATUAL

            $stmt = $pdo->prepare("
                SELECT status
                FROM ocorrencias
                WHERE id = ?
                FOR UPDATE
            ");

            $stmt->execute([$id]);

            $dados =
                $stmt->fetch();


            if (!$dados) {

                throw new Exception(
                    "Ocorrência não encontrada."
                );

            }


            $statusAnterior =
                $dados["status"];


            // ATUALIZAR

            $stmt = $pdo->prepare("
                UPDATE ocorrencias

                SET
                    status = ?,
                    atualizado_em = NOW()

                WHERE id = ?
            ");

            $stmt->execute([
                $novoStatus,
                $id
            ]);


            // HISTÓRICO

            $stmt = $pdo->prepare("
                INSERT INTO historico_ocorrencias

                (
                    ocorrencia_id,
                    usuario_id,
                    status_anterior,
                    status_novo,
                    observacao
                )

                VALUES
                (?, ?, ?, ?, ?)
            ");

            $stmt->execute([

                $id,

                $usuario["id"],

                $statusAnterior,

                $novoStatus,

                $observacao !== ""
                    ? $observacao
                    : "Status atualizado pelo administrador."

            ]);


            $pdo->commit();


            $mensagem =
                "Status atualizado com sucesso.";


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro =
                $e->getMessage();

        }

    }

}


// ==========================================
// BUSCAR OCORRÊNCIA
// ==========================================

$stmt = $pdo->prepare("

    SELECT

        o.*,

        u.nome AS usuario_nome,

        u.email AS usuario_email,

        c.nome AS categoria_nome,

        c.icone AS categoria_icone

    FROM ocorrencias o

    INNER JOIN usuarios u
        ON u.id = o.usuario_id

    INNER JOIN categorias c
        ON c.id = o.categoria_id

    WHERE o.id = ?

");

$stmt->execute([$id]);

$o = $stmt->fetch();


if (!$o) {
    die("Ocorrência não encontrada.");
}


// ==========================================
// HISTÓRICO
// ==========================================

$stmt = $pdo->prepare("

    SELECT

        h.*,

        u.nome AS usuario_nome

    FROM historico_ocorrencias h

    INNER JOIN usuarios u
        ON u.id = h.usuario_id

    WHERE h.ocorrencia_id = ?

    ORDER BY h.criado_em ASC

");

$stmt->execute([$id]);

$historico =
    $stmt->fetchAll();


// ==========================================
// STATUS
// ==========================================

$nomesStatus = [

    "registrada" => "Registrada",

    "em_analise" => "Em análise",

    "aprovada" => "Aprovada",

    "em_execucao" => "Em execução",

    "resolvida" => "Resolvida",

    "cancelada" => "Cancelada"

];

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>
Gerenciar ocorrência #<?= $o["id"] ?>
</title>

<link rel="stylesheet" href="style.css">

<link
rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

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
Ocorrência #<?= $o["id"] ?>
</h1>

<p>
<?= proteger($o["titulo"]) ?>
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


<div class="detalhes">


<!-- ======================================
     ESQUERDA
====================================== -->

<div>


<div class="card">

<h2>
📄 <?= proteger($o["titulo"]) ?>
</h2>

<p>
<?= nl2br(
    proteger(
        $o["descricao"]
    )
) ?>
</p>

</div>


<div class="card">

<h2>
📍 Localização
</h2>

<p>
<strong>Endereço:</strong>
<?= proteger($o["endereco"]) ?>
</p>

<p>
<strong>Bairro:</strong>
<?= proteger($o["bairro"]) ?>
</p>


<?php if (
    $o["latitude"] !== null
    &&
    $o["longitude"] !== null
): ?>

<div
id="map"
class="mapa"
></div>

<?php endif; ?>

</div>


<?php if (!empty($o["foto"])): ?>

<div class="card">

<h2>
📷 Foto
</h2>

<img
src="../uploads/<?= proteger($o["foto"]) ?>"
style="
width:100%;
max-height:500px;
object-fit:cover;
border-radius:10px;
"
>

</div>

<?php endif; ?>


<div class="card">

<h2>
📜 Histórico
</h2>


<div class="timeline">

<?php foreach ($historico as $h): ?>

<div class="timeline-item">

<h4>

<?= $nomesStatus[
    $h["status_novo"]
] ?? $h["status_novo"] ?>

</h4>

<p>
<?= proteger(
    $h["observacao"]
) ?>
</p>

<p>
👤 <?= proteger(
    $h["usuario_nome"]
) ?>
</p>

<small>

<?= date(
    "d/m/Y H:i",
    strtotime(
        $h["criado_em"]
    )
) ?>

</small>

</div>

<?php endforeach; ?>

</div>

</div>

</div>


<!-- ======================================
     DIREITA
====================================== -->

<div>


<div class="card">

<h2>
🚦 Status
</h2>

<p>

Atual:

<strong>

<?= $nomesStatus[
    $o["status"]
] ?>

</strong>

</p>


<form method="POST">

<label>
Novo status
</label>


<select name="status">

<?php foreach (
    $nomesStatus
    as $valor => $nome
): ?>

<option
value="<?= $valor ?>"
<?= $o["status"] === $valor
    ? "selected"
    : "" ?>
>

<?= $nome ?>

</option>

<?php endforeach; ?>

</select>


<label>
Observação
</label>

<textarea
name="observacao"
placeholder="Explique o que foi feito..."
></textarea>


<button
class="botao"
type="submit"
>

🔄 Atualizar status

</button>

</form>

</div>


<div class="card">

<h2>
👤 Cidadão
</h2>

<p>

<strong>
Nome:
</strong>

<br>

<?= proteger(
    $o["usuario_nome"]
) ?>

</p>


<p>

<strong>
E-mail:
</strong>

<br>

<?= proteger(
    $o["usuario_email"]
) ?>

</p>

</div>


<div class="card">

<h2>
ℹ️ Informações
</h2>

<div class="info">

<div class="info-item">

<small>
Categoria
</small>

<strong>

<?= proteger(
    $o["categoria_icone"]
) ?>

<?= proteger(
    $o["categoria_nome"]
) ?>

</strong>

</div>


<div class="info-item">

<small>
Prioridade
</small>

<strong>

<?= ucfirst(
    $o["prioridade"]
) ?>

</strong>

</div>


<div class="info-item">

<small>
Data
</small>

<strong>

<?= date(
    "d/m/Y H:i",
    strtotime(
        $o["criado_em"]
    )
) ?>

</strong>

</div>

</div>

</div>


<a
href="ocorrencias.php"
class="botao botao-secundario"
>

← Voltar para ocorrências

</a>


</div>

</div>

</main>


<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>


<?php if (
    $o["latitude"] !== null
    &&
    $o["longitude"] !== null
): ?>

<script>

const latitude =
<?= json_encode(
    floatval(
        $o["latitude"]
    )
) ?>;

const longitude =
<?= json_encode(
    floatval(
        $o["longitude"]
    )
) ?>;


const map =
L.map("map")
.setView(
    [latitude, longitude],
    17
);


L.tileLayer(
    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
    {
        maxZoom: 19,
        attribution:
        "&copy; OpenStreetMap contributors"
    }
).addTo(map);


L.marker([
    latitude,
    longitude
])
.addTo(map)
.bindPopup(
    "📍 <?= htmlspecialchars(
        $o["titulo"],
        ENT_QUOTES,
        "UTF-8"
    ) ?>"
)
.openPopup();

</script>

<?php endif; ?>

</body>

</html>