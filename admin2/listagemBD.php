<?php include "auth.php"; ?>
<?php include "../conexao.php"; ?>
<?php
$por_pagina = 5;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina - 1) * $por_pagina;

$busca = isset($_GET['busca']) ? trim($conn->real_escape_string($_GET['busca'])) : '';

$where = '';
// Verifica se a busca é uma data no formato dd/mm/yyyy
if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $busca)) {
    // Converte para yyyy-mm-dd
    $partes = explode('/', $busca);
    $data_convertida = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    $where = "WHERE data_upload = '$data_convertida'";
} elseif (!empty($busca)) {
    // Busca por nome do arquivo
    $where = "WHERE nome LIKE '%$busca%' OR arquivo LIKE '%$busca%'";
}

// Total de registros com ou sem filtro
$total_query = $conn->query("SELECT COUNT(*) as total FROM boletins $where");
$total_result = $total_query->fetch_assoc();
$total = $total_result['total'];
$total_paginas = ceil($total / $por_pagina);

// Consulta paginada
$sql = "SELECT * FROM boletins $where ORDER BY data_upload DESC LIMIT $inicio, $por_pagina";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Listagem de Boletins</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" integrity="sha512-1cK78a1o+ht2JcaW6g8OXYwqpev9+6GqOkz9xmBN9iUUhIndKtxwILGWYOSibOKjLsEdjyjZvYDq/cZwNeak0w==" crossorigin="anonymous" referrerpolicy="no-referrer" /> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" integrity="sha512-cn16Qw8mzTBKpu08X0fwhTSv02kK/FojjNLz0bwp2xJ4H+yalwzXKFw/5cLzuBZCxGWIA+95X4skzvo8STNtSg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/animations.min.css" integrity="sha512-GKHaATMc7acW6/GDGVyBhKV3rST+5rMjokVip0uTikmZHhdqFWC7fGBaq6+lf+DOS5BIO8eK6NcyBYUBCHUBXA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.3.0/css/glightbox.min.css" integrity="sha512-T+KoG3fbDoSnlgEXFQqwcTC9AdkFIxhBlmoaFqYaIjq2ShhNwNao9AKaLUPMfwiBPL0ScxAtc+UYbHAgvd+sjQ==" crossorigin="anonymous" referrerpolicy="no-referrer" /> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.css" integrity="sha512-pmAAV1X4Nh5jA9m+jcvwJXFQvCBi3T17aZ1KWkqXr7g/O2YMvO8rfaa5ETWDuBvRq6fbDjlw4jHL44jNTScaKg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css">

</head>
<body class="container py-5">

<h1 class="mb-4">Boletins Diários</h1>

<!-- Formulário de busca -->
<form method="GET" class="mb-4 d-flex">
    <input type="text" name="busca" class="form-control me-2" placeholder="Buscar por nome ou data (dd/mm/aaaa)" value="<?= htmlspecialchars($busca) ?>">
    <button type="submit" class="btn btn-primary">Buscar</button>
    <a href="listagemBD.php" class="btn btn-secondary ms-2">Limpar</a>
</form>

<a href="https://drive.google.com/drive/folders/0B4yicqLa_Dj8YTE5ZDUyNTItMjkzYS00ZGJlLTg2M2ItZTI0ZjRjODQ3ZDNk?resourcekey=0-n6Kjkz-jNDVJdgI1dJ1A1A" class="btn btn-outline-primary mb-3" target="_blank">
    Boletins Anteriores
</a>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Data de Publicação</th>
            <th>Visualizar</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($row['data_upload'])) ?></td>
                <td><a href="../uploads/<?= $row['arquivo'] ?>" target="_blank">Visualizar</a></td>
                <td>
                    <a href="editarBD.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Editar</a>
                    <a href="excluirBD.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir?');"><i class="bi bi-trash"></i> Excluir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">Nenhum boletim encontrado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Paginação -->
<nav>
    <ul class="pagination">
        <?php if ($pagina > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>">&laquo;</a>
        </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
            <a class="page-link" href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>

        <?php if ($pagina < $total_paginas): ?>
        <li class="page-item">
            <a class="page-link" href="?pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>">&raquo;</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>

</body>
</html>
