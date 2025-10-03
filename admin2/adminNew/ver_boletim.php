<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
include('../conexao.php');
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { die('Boletim inválido'); }
$sql = 'SELECT titulo, descricao, caminho, nome_arquivo FROM boletins WHERE id = ? LIMIT 1';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) { die('Boletim não encontrado'); }
$row = $res->fetch_assoc();
$path = __DIR__ . '/../' . $row['caminho'];
if (!file_exists($path)) { die('Arquivo não encontrado'); }
// Forçar download / exibir inline
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="'.basename($row['nome_arquivo']).'"');
readfile($path);
exit;
?>