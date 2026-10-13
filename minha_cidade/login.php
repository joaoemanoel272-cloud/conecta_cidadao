<?php

require "config/database.php";

require "config/auth.php";


if (estaLogado()) {

    header(
        "Location: dashboard.php"
    );

    exit;

}


$erro = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim($_POST["email"]);

    $senha =
        $_POST["senha"];


    $stmt = $pdo->prepare("
        SELECT *
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([
        $email
    ]);


    $usuario =
        $stmt->fetch();


    if (
        $usuario &&
        password_verify(
            $senha,
            $usuario["senha"]
        )
    ) {

        unset(
            $usuario["senha"]
        );


        $_SESSION["usuario"] =
            $usuario;


        header(
            "Location: dashboard.php"
        );

        exit;

    }


    $erro =
        "E-mail ou senha incorretos.";

}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<title>Login</title>

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
Entrar
</h1>


<?php if ($erro): ?>

<p style="color:red;">
<?= proteger($erro) ?>
</p>

<?php endif; ?>


<form method="POST">

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
Entrar
</button>

</form>


<p>

Ainda não possui conta?

<a href="cadastro.php">
Criar conta
</a>

</p>

</div>

</main>

</body>

</html>