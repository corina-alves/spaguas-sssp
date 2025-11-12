<?php include "conexao.php";?>
<?php

// $sql = "SELECT ano, mes, vazao_defluente, vazao_afluente, perc_mlt, variacao_volume, volume_util 
//         FROM sistema_cantareira
//         WHERE ano between 2015 and 2025
//         ORDER BY ano, mes ASC";
// $result = $conn->query($sql);

// $meses = [];
// $vazao_defluente = [];
// $vazao_afluente = [];
// $perc_mlt = [];
// $variacao_volume = [];
// $volume_util = [];

// while($row = $result->fetch_assoc()) {
//     $meses[] = $row["mes"];
//     $vazao_defluente[] = $row["vazao_defluente"];
//     $vazao_afluente[] = $row["vazao_afluente"];
//     $perc_mlt[] = $row["perc_mlt"];
//     $variacao_volume[] = $row["variacao_volume"];
//     $volume_util[] = $row["volume_util"];
// }
$sql = "SELECT ano, mes, volume_util
        FROM sistema_cantareira
        WHERE ano BETWEEN 2015 AND 2025
        ORDER BY ano, mes";
$result = $conn->query($sql);

$anosMeses = [];
$volumes = [];

while($row = $result->fetch_assoc()) {
    $anosMeses[] = $row["mes"] . "/" . $row["ano"];
    $volumes[] = $row["volume_util"];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resumo - Sistema Cantareira</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #004b7c; color: #fff; }
        .chart-container { width: 80%; margin: auto; background: #fff; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>

<h2>Resumo - Sistema Cantareira (2025)</h2>

<!-- <table>
    <tr>
        <th>Mês</th>
        <th>Vazão Defluente (m³/s)</th>
        <th>Vazão Afluente (m³/s)</th>
        <th>% da MLT</th>
        <th>Variação Volume Útil (%)</th>
        <th>Volume Útil (%)</th>
    </tr>
    <?php foreach($meses as $i => $mes): ?>
    <tr>
        <td><?= $mes ?></td>
        <td><?= $vazao_defluente[$i] ?></td>
        <td><?= $vazao_afluente[$i] ?></td>
        <td><?= $perc_mlt[$i] ?></td>
        <td><?= $variacao_volume[$i] ?></td>
        <td><?= $volume_util[$i] ?></td>
    </tr>
    <?php endforeach; ?>
</table> -->

<div class="chart-container">
    <canvas id="chartHistorico"></canvas>
</div>

<script>
const ctx2 = document.getElementById('chartHistorico').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: <?= json_encode($anosMeses) ?>,
        datasets: [{
            label: 'Volume Útil (%) - Último dia do mês',
            data: <?= json_encode($volumes) ?>,
            backgroundColor: '#2e86c1'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: { beginAtZero: true, max: 100 }
        }
    }
});
</script>

</body>
</html>
