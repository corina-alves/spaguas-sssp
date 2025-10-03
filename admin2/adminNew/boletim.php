<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
include('../conexao.php');
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($titulo === '' || empty($_FILES['arquivo']['name'])) {
        $mensagem = 'Título e arquivo são obrigatórios.';
    } else {
        $uploaddir = __DIR__ . '/../uploads/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);

        $file = $_FILES['arquivo'];
        // validar tipo (apenas pdf)
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'pdf') { $mensagem = 'Apenas arquivos PDF são permitidos.'; }
        else {
            $nomeSalvo = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $file['name']);
            $caminho = $uploaddir . $nomeSalvo;
            if (move_uploaded_file($file['tmp_name'], $caminho)) {
                $sql = 'INSERT INTO boletins (titulo, descricao, nome_arquivo, caminho) VALUES (?, ?, ?, ?)';
                $stmt = $conn->prepare($sql);
                $relPath = 'uploads/' . $nomeSalvo; // caminho relativo para servir
                $stmt->bind_param('ssss', $titulo, $descricao, $file['name'], $relPath);
                $stmt->execute();
                $stmt->close();
                $mensagem = 'Boletim inserido com sucesso.';
            } else {
                $mensagem = 'Erro ao mover o arquivo.';
            }
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Inserir Boletim</title></head>
<body>
<h2>Inserir Boletim</h2>
<p><a href="dashboard.php">Voltar</a></p>
<?php if ($mensagem) echo '<p>'.htmlspecialchars($mensagem).'</p>';?>
<form method="post" enctype="multipart/form-data">
    <label>Título<br><input type="text" name="titulo"></label><br>
    <label>Descrição<br><textarea name="descricao" rows="4"></textarea></label><br>
    <label>Arquivo (PDF)<br><input type="file" name="arquivo" accept="application/pdf"></label><br>
    <button type="submit">Enviar</button>
</form>
</body>
</html>