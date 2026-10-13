<?php

require "config/database.php";

require "config/auth.php";


if (estaLogado()) {

    header("Location: dashboard.php");

    exit;

}


$erro = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome =
        trim($_POST["nome"]);

    $email =
        trim($_POST["email"]);

    $senha =
        $_POST["senha"];


    if (
        empty($nome) ||
        empty($email) ||
        empty($senha)
    ) {

        $erro =
            "Preencha todos os campos.";

    }

    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $erro =
            "E-mail inválido.";

    }

    elseif (
        strlen($senha) < 6
    ) {

        $erro =
            "A senha precisa ter pelo menos 6 caracteres.";

    }

    else {

        try {

            $senhaHash =
                password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );


            $sql = "
                INSERT INTO usuarios
                (
                    nome,
                    email,
                    senha
                )
                VALUES
                (?, ?, ?)
            ";


            $stmt =
                $pdo->prepare($sql);


            $stmt->execute([
                $nome,
                $email,
                $senhaHash
            ]);


            header(
                "Location: login.php"
            );

            exit;


        } catch (PDOException $e) {

            $erro =
                "Esse e-mail já está cadastrado.";

        }

    }

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Criar conta</title>

<link
rel="stylesheet"
href="assets/style.css"
>

</head>

<body>

<header>

<div class="logo">
🏙️ Minha Cidade
</div>

</header>


<main>

<div class="card">

<h1>
Criar conta
</h1>

<?php if ($erro): ?>

<p style="color:red;">
<?= proteger($erro) ?>
</p>

<?php endif; ?>


<form method="POST">

<label>
Nome
</label>

<input
type="text"
name="nome"
required
>


<label>
E-mail
</label>

<input
type="email"
name="email"
required
>


<label>
Senha
</label>

<input
type="password"
name="senha"
required
>


<button>
Criar conta
</button>

</form>

</div>

</main>

</body>

</html>