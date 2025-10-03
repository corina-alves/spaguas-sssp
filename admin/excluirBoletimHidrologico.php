<?php
include "../conexao.php";

$id = $_GET['id'] ?? '';

// Buscar o arquivo para excluir do diretório
$sql = "SELECT arquivo FROM up_boletim_hidro WHERE id = $id";
$result = $conn->query($sql);
$dado = $result->fetch_assoc();

if ($dado) {
    $arquivo = '../up_boletim_Hidro/' . $dado['arquivo'];
    if (file_exists($arquivo)) {
        unlink($arquivo); // Remove o arquivo do servidor
    }

    $conn->query("DELETE FROM up_boletim_hidro WHERE id = $id");
}

header("Location: listagemBoletimHidrologico.php"); // Volta pra listagem
exit();
?>
