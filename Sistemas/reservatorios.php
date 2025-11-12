<?php
$dados = json_decode(file_get_contents("cache/dados.api.php"), true);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Boletim Diário</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; }
table { border-collapse: collapse; width: 100%; background: #fff; }
th, td { border: 1px solid #ddd; padding: 8px; }
th { background: #0074D9; color: white; }
</style>
</head>
<body>
<h2>Boletim Diário - Sistemas</h2>
<table>
<tr><th>Sistema</th><th>Volume (%)</th><th>Pluviometria (mm)</th></tr>
<?php foreach ($dados as $sistema): ?>
<tr>
  <td><?= htmlspecialchars($sistema["Nome"]) ?></td>
  <td><?= htmlspecialchars($sistema["VolUtil"]) ?></td>
  <td><?= htmlspecialchars($sistema["PluviometriaDia"]) ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
