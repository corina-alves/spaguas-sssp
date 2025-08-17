<?php require_once 'inc/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Boletins</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <h1>Boletins Publicados</h1>
  <ul>
    <?php
    $sql = "SELECT * FROM boletins ORDER BY data_upload DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()):
    ?>
    <li>
      <a href="uploads/<?= htmlspecialchars($row['arquivo']) ?>" target="_blank">
        <?= htmlspecialchars($row['titulo']) ?> (<?= date('d/m/Y', strtotime($row['data_upload'])) ?>)
      </a>
    </li>
    <?php endwhile; ?>
  </ul>
</body>
</html>
