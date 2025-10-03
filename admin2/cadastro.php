<?php include "auth.php"; include "../conexao.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Enviar Boletim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<header id="header" class="header sticky-top">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="logo"><img src="../img/logosssp.png"></div>
        <nav id="navbar" class="navbar">
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="painel.php">Painel</a></li>
                <li><a href="listagemBD.php">Listar Boletins</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="upload">
    <div class="container">
        <div class="col-lg-6 offset-lg-3">
            <h2>Cadastrar <strong>Boletim</strong></h2>
            <form method="post" enctype="multipart/form-data">
                <label for="nome" class="form-label">Nome do Boletim</label>
                <input class="form-control" type="text" name="nome" placeholder="Nome do boletim" required>

                <label for="tipo" class="form-label mt-3">Tipo de Boletim</label>
                <select class="form-control" name="tipo" required>
                    <option value="">Selecione...</option>
                    <option value="diario">Boletim Diário</option>
                    <option value="mensal">Boletim Mensal</option>
                    <option value="hidrologico">Boletim Hidrológico</option>
                </select>

                <div class="mb-3 mt-3">
                    <label for="formFile" class="form-label">Arquivo (PDF)</label>
                    <input class="form-control" id="formFile" type="file" name="pdf" accept="application/pdf" required>
                </div>

                <div class="col-auto mt-3">
                    <button type="submit" class="btn btn-primary mb-3">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
if ($_POST) {
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $arquivo = uniqid() . ".pdf";
    $pasta = "../uploads/" . $tipo . "/";

    // Cria a pasta do tipo de boletim, se não existir
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $caminho = $pasta . $arquivo;

    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $caminho)) {
        $stmt = $conn->prepare("INSERT INTO boletins (nome, tipo, arquivo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $tipo, $arquivo);
        $stmt->execute();
        echo "<p style='color:green;text-align:center;'>Boletim enviado com sucesso! Redirecionando...</p>";
        echo "<script>setTimeout(() => { window.location.href = 'listagemBD.php'; }, 2000);</script>";
    } else {
        echo "<p style='color:red;text-align:center;'>Erro ao enviar o arquivo.</p>";
    }
}
?>
</body>
</html>