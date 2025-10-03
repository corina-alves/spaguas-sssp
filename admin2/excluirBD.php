<?php
include "../conexao.php";

$id = $_GET['id'] ?? '';

// Buscar o arquivo para excluir do diretório
$sql = "SELECT arquivo FROM boletins WHERE id = $id";
$result = $conn->query($sql);
$dado = $result->fetch_assoc();

if ($dado) {
    $arquivo = '../uploads/' . $dado['arquivo'];
    if (file_exists($arquivo)) {
        unlink($arquivo); // Remove o arquivo do servidor
    }

    $conn->query("DELETE FROM boletins WHERE id = $id");
}

header("Location: listagemBD.php"); // Volta pra listagem
exit();
?>
