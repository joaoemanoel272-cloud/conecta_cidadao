<?php

require "config/database.php";
require "config/auth.php";

exigirLogin();


// ==========================================
// BUSCAR CATEGORIAS
// ==========================================

$stmt = $pdo->query("
    SELECT *
    FROM categorias
    ORDER BY nome
");

$categorias = $stmt->fetchAll();


// ==========================================
// VARIÁVEIS
// ==========================================

$erro = "";


// ==========================================
// PROCESSAR FORMULÁRIO
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo =
        trim($_POST["titulo"] ?? "");

    $categoria =
        intval($_POST["categoria_id"] ?? 0);

    $descricao =
        trim($_POST["descricao"] ?? "");

    $endereco =
        trim($_POST["endereco"] ?? "");

    $bairro =
        trim($_POST["bairro"] ?? "");

    $prioridade =
        $_POST["prioridade"] ?? "media";

    $latitude =
        $_POST["latitude"] ?? "";

    $longitude =
        $_POST["longitude"] ?? "";


    // ======================================
    // VALIDAR
    // ======================================

    if (
        empty($titulo) ||
        empty($categoria) ||
        empty($descricao) ||
        empty($endereco) ||
        empty($bairro)
    ) {

        $erro =
            "Preencha todos os campos obrigatórios.";

    }


    elseif (
        !in_array(
            $prioridade,
            [
                "baixa",
                "media",
                "alta",
                "critica"
            ]
        )
    ) {

        $erro =
            "Prioridade inválida.";

    }


    else {

        // ==================================
        // LATITUDE
        // ==================================

        if ($latitude !== "") {

            $latitude =
                floatval($latitude);

        } else {

            $latitude = null;

        }


        // ==================================
        // LONGITUDE
        // ==================================

        if ($longitude !== "") {

            $longitude =
                floatval($longitude);

        } else {

            $longitude = null;

        }


        // ==================================
        // FOTO
        // ==================================

        $nomeFoto = null;


        if (
            isset($_FILES["foto"]) &&
            $_FILES["foto"]["error"]
            === UPLOAD_ERR_OK
        ) {


            $arquivo =
                $_FILES["foto"];


            // Tamanho máximo: 5 MB

            if (
                $arquivo["size"]
                > 5 * 1024 * 1024
            ) {

                $erro =
                    "A foto deve ter no máximo 5 MB.";

            }


            else {

                $tipo =
                    mime_content_type(
                        $arquivo["tmp_name"]
                    );


                $tiposPermitidos = [

                    "image/jpeg" => "jpg",

                    "image/png" => "png",

                    "image/webp" => "webp"

                ];


                if (
                    !isset(
                        $tiposPermitidos[$tipo]
                    )
                ) {

                    $erro =
                        "Formato de imagem inválido.";

                }


                else {

                    // Criar nome único

                    $nomeFoto =
                        uniqid(
                            "ocorrencia_",
                            true
                        )
                        . "."
                        . $tiposPermitidos[$tipo];


                    $destino =
                        __DIR__
                        . "/uploads/"
                        . $nomeFoto;


                    if (
                        !move_uploaded_file(
                            $arquivo["tmp_name"],
                            $destino
                        )
                    ) {

                        $erro =
                            "Não foi possível salvar a foto.";

                    }

                }

            }

        }


        // ==================================
        // SALVAR NO BANCO
        // ==================================

        if ($erro === "") {

            try {


                $sql = "

                    INSERT INTO ocorrencias

                    (
                        usuario_id,
                        categoria_id,
                        titulo,
                        descricao,
                        endereco,
                        bairro,
                        latitude,
                        longitude,
                        prioridade,
                        status,
                        foto
                    )

                    VALUES

                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'registrada',
                        ?
                    )

                ";


                $stmt =
                    $pdo->prepare($sql);


                $stmt->execute([

                    usuarioAtual()["id"],

                    $categoria,

                    $titulo,

                    $descricao,

                    $endereco,

                    $bairro,

                    $latitude,

                    $longitude,

                    $prioridade,

                    $nomeFoto

                ]);


                // ==================================
                // PEGAR ID
                // ==================================

                $ocorrenciaId =
                    $pdo->lastInsertId();


                // ==================================
                // CRIAR HISTÓRICO
                // ==================================

                $sqlHistorico = "

                    INSERT INTO
                    historico_ocorrencias

                    (
                        ocorrencia_id,
                        usuario_id,
                        status_anterior,
                        status_novo,
                        observacao
                    )

                    VALUES

                    (
                        ?,
                        ?,
                        NULL,
                        'registrada',
                        ?
                    )

                ";


                $stmtHistorico =
                    $pdo->prepare(
                        $sqlHistorico
                    );


                $stmtHistorico->execute([

                    $ocorrenciaId,

                    usuarioAtual()["id"],

                    "Ocorrência registrada pelo cidadão."

                ]);


                // ==================================
                // REDIRECIONAR
                // ==================================

                header(
                    "Location: ocorrencia.php?id="
                    . $ocorrenciaId
                );

                exit;


            } catch (PDOException $e) {

                $erro =
                    "Erro ao salvar a ocorrência: "
                    . $e->getMessage();

            }

        }

    }

}

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
Registrar ocorrência - Minha Cidade
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

.form-container {

    max-width:
        900px;

    margin:
        30px auto;

}


.form-card {

    background:
        white;

    padding:
        30px;

    border-radius:
        15px;

    border:
        1px solid #e5e7eb;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.05);

}


.form-card h1 {

    margin-top:
        0;

}


.form-description {

    color:
        #667085;

    line-height:
        1.6;

}


.campo {

    margin-bottom:
        20px;

}


.campo label {

    display:
        block;

    font-weight:
        bold;

    margin-bottom:
        7px;

}


.campo input,
.campo select,
.campo textarea {

    width:
        100%;

    padding:
        13px;

    border:
        1px solid #ccd2dd;

    border-radius:
        8px;

    font-size:
        15px;

}


.campo textarea {

    min-height:
        140px;

    resize:
        vertical;

}


.linha {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        20px;

}


#map {

    width:
        100%;

    height:
        450px;

    border-radius:
        12px;

    margin-top:
        10px;

}


.instrucao-mapa {

    background:
        #eef4ff;

    border-left:
        4px solid #2457d6;

    padding:
        12px;

    margin-top:
        10px;

    border-radius:
        6px;

    color:
        #2457d6;

}


.coordenadas {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        15px;

    margin-top:
        15px;

}


.erro {

    background:
        #fee2e2;

    color:
        #991b1b;

    padding:
        15px;

    border-radius:
        8px;

    margin-bottom:
        20px;

}


.botao-enviar {

    width:
        100%;

    padding:
        15px;

    background:
        #2457d6;

    color:
        white;

    border:
        none;

    border-radius:
        8px;

    font-size:
        16px;

    font-weight:
        bold;

    cursor:
        pointer;

}


.botao-enviar:hover {

    background:
        #1947bb;

}


@media(max-width:700px) {

    .linha {

        grid-template-columns:
            1fr;

    }


    .coordenadas {

        grid-template-columns:
            1fr;

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
    usuarioAtual()["tipo"]
    === "admin"
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


<div class="form-container">


<div class="form-card">


<h1>

📢 Registrar problema

</h1>


<p class="form-description">

Encontrou um problema na cidade?

Informe os detalhes abaixo.
Você também pode marcar exatamente
onde o problema está localizado
no mapa.

</p>



<?php if ($erro !== ""): ?>

<div class="erro">

<?= proteger($erro) ?>

</div>

<?php endif; ?>



<form
method="POST"
enctype="multipart/form-data"
>


<!-- =================================
     TÍTULO
================================= -->

<div class="campo">

<label>

Título do problema *

</label>


<input
type="text"
name="titulo"
placeholder="Ex: Buraco grande na rua"
value="<?= proteger(
    $_POST["titulo"] ?? ""
) ?>"
required
>

</div>



<!-- =================================
     CATEGORIA
================================= -->

<div class="campo">

<label>

Categoria *

</label>


<select
name="categoria_id"
required
>

<option value="">

Selecione uma categoria

</option>


<?php foreach (
    $categorias
    as $categoria
): ?>


<option
value="<?= $categoria["id"] ?>"

<?php

if (
    isset(
        $_POST["categoria_id"]
    )
    &&
    $_POST["categoria_id"]
    == $categoria["id"]
) {

    echo "selected";

}

?>

>

<?= proteger(
    $categoria["icone"]
) ?>

<?= proteger(
    $categoria["nome"]
) ?>

</option>


<?php endforeach; ?>

</select>

</div>



<!-- =================================
     DESCRIÇÃO
================================= -->

<div class="campo">

<label>

Descrição *

</label>


<textarea
name="descricao"
placeholder="Explique o problema com o máximo de detalhes possível..."
required
><?= proteger(
    $_POST["descricao"] ?? ""
) ?></textarea>

</div>



<!-- =================================
     ENDEREÇO
================================= -->

<div class="linha">


<div class="campo">

<label>

Endereço *

</label>


<input
type="text"
name="endereco"
placeholder="Rua, número ou ponto de referência"
value="<?= proteger(
    $_POST["endereco"] ?? ""
) ?>"
required
>

</div>



<div class="campo">

<label>

Bairro *

</label>


<input
type="text"
name="bairro"
placeholder="Ex: Centro"
value="<?= proteger(
    $_POST["bairro"] ?? ""
) ?>"
required
>

</div>


</div>



<!-- =================================
     PRIORIDADE
================================= -->

<div class="campo">

<label>

Prioridade

</label>


<select
name="prioridade"
>

<option value="baixa">
🟢 Baixa
</option>

<option
value="media"
selected
>
🟡 Média
</option>

<option value="alta">
🟠 Alta
</option>

<option value="critica">
🔴 Crítica
</option>

</select>

</div>



<!-- =================================
     MAPA
================================= -->

<div class="campo">

<label>

📍 Localização do problema

</label>


<div
class="instrucao-mapa"
>

<b>
Clique no mapa
</b>
para marcar exatamente
onde o problema está.

</div>


<div id="map"></div>


<div class="coordenadas">


<div>

<label>

Latitude

</label>


<input
type="text"
id="latitude"
name="latitude"
readonly
>

</div>


<div>

<label>

Longitude

</label>


<input
type="text"
id="longitude"
name="longitude"
readonly
>

</div>


</div>

</div>



<!-- =================================
     FOTO
================================= -->

<div class="campo">

<label>

📷 Foto do problema

</label>


<input
type="file"
name="foto"
accept="image/jpeg,image/png,image/webp"
>


<p style="color:#667085;">

Formatos aceitos:
JPG, PNG ou WEBP.

Máximo:
5 MB.

</p>

</div>



<!-- =================================
     BOTÃO
================================= -->

<button
type="submit"
class="botao-enviar"
>

🚀 Registrar ocorrência

</button>


</form>


</div>


</div>


</main>



<footer>

Minha Cidade © 2026

</footer>



<!-- =====================================
     LEAFLET JS
===================================== -->

<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>



<script>

// =======================================
// CRIAR MAPA
// =======================================

const map =
    L.map("map")
     .setView(
        [-8.7619, -63.9039],
        13
     );


// =======================================
// MAPA OPENSTREETMAP
// =======================================

L.tileLayer(
    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
    {

        maxZoom: 19,

        attribution:
            "&copy; OpenStreetMap contributors"

    }
).addTo(map);


// =======================================
// MARCADOR
// =======================================

let marcador = null;


// =======================================
// CLIQUE NO MAPA
// =======================================

map.on(
    "click",
    function(event) {


        const latitude =
            event.latlng.lat;


        const longitude =
            event.latlng.lng;


        // -------------------------------
        // COLOCAR LATITUDE
        // -------------------------------

        document
            .getElementById("latitude")
            .value =
            latitude.toFixed(7);


        // -------------------------------
        // COLOCAR LONGITUDE
        // -------------------------------

        document
            .getElementById("longitude")
            .value =
            longitude.toFixed(7);


        // -------------------------------
        // REMOVER MARCADOR ANTIGO
        // -------------------------------

        if (marcador !== null) {

            map.removeLayer(
                marcador
            );

        }


        // -------------------------------
        // CRIAR NOVO MARCADOR
        // -------------------------------

        marcador =
            L.marker([
                latitude,
                longitude
            ])
            .addTo(map);


        marcador
            .bindPopup(
                `
                <b>📍 Local selecionado</b>
                <br>
                Latitude:
                ${latitude.toFixed(7)}
                <br>
                Longitude:
                ${longitude.toFixed(7)}
                `
            )
            .openPopup();

    }
);

</script>


</body>

</html>