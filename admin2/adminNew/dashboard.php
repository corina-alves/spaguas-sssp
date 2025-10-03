<?php
session_start();
if (empty($_SESSION['user'])) {
    header('Location: login.php'); exit;
}
include('../conexao.php');
// busca boletins
$res = $conn->query('SELECT id, titulo, descricao, nome_arquivo, criado_em FROM boletins ORDER BY criado_em DESC');
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Dashboard - SSSP</title></head>
<body>
<h2>Dashboard</h2>
<p>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['user']); ?> | <a href="logout.php">Sair</a></p>
<p><a href="inserir_boletim.php">Inserir novo boletim</a></p>
<table border="1" cellpadding="6">
<tr><th>Título</th><th>Descrição</th><th>Arquivo</th><th>Data</th><th>Ação</th></tr>
<?php while ($row = $res->fetch_assoc()): ?>
<tr>
  <td><?php echo htmlspecialchars($row['titulo']); ?></td>
  <td><?php echo nl2br(htmlspecialchars($row['descricao'])); ?></td>
  <td><?php echo htmlspecialchars($row['nome_arquivo']); ?></td>
  <td><?php echo $row['criado_em']; ?></td>
  <td><a href="view_boletim.php?id=<?php echo $row['id']; ?>" target="_blank">Ver / Baixar</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>