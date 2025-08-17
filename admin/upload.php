<?php
require_once '../inc/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $titulo = $_POST['titulo'];
    $nome = basename($_FILES['arquivo']['name']);
    $destino = '../uploads/' . $nome;

    if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
        $sql = "INSERT INTO boletins (titulo, arquivo) VALUES ('$titulo', '$nome')";
        $conn->query($sql);
        $msg = "Boletim enviado com sucesso!";
    } else {
        $msg = "Erro ao enviar o arquivo.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Upload</title></head>
<body>



  <h2>Publicar Boletim</h2>
  <form method="post" enctype="multipart/form-data">
    <label>Título: <input type="text" name="titulo" required></label><br>
    <label>Arquivo PDF: <input type="file" name="arquivo" accept="application/pdf" required></label><br>
    <button type="submit">Enviar</button>
  </form>
  <?php if (isset($msg)) echo "<p>$msg</p>"; ?>
  <a href="dashboard.php">Voltar</a>
</body>
</html>
