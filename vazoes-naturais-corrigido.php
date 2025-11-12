<?php
// ===============================
// dashboard.php (corrigido)
// ===============================
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

$dataAtual = date("Y-m-d");
$data2021 = "2021-" . date("m-d");

// ===============================
// Função: buscar dados da API
// ===============================
function getDadosApi($data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resposta = curl_exec($ch);
    curl_close($ch);
    return json_decode($resposta, true);
}

// ===============================
// Função: obter chuvas e vazões diárias (últimos 7 dias)
// ===============================
function getSerieUltimosDias($idSistema, $dias = 7) {
    $serie = [];

    for ($i = $dias - 1; $i >= 0; $i--) {
        $data = date("Y-m-d", strtotime("-$i days"));
        $dados = getDadosApi($data);

        $chuva = null;
        $vazao = null;

        if (isset($dados["data"])) {
            foreach ($dados["data"] as $s) {
                if ($s["idSistema"] == $idSistema) {
                    $chuva = $s["chuva"] ?? null;
                    $vazao = $s["vazaoNatural"] ?? null;
                    break;
                }
            }
        }

        $serie[] = [
            "data" => $data,
            "chuva" => $chuva,
            "vazao" => $vazao
        ];
    }

    return $serie;
}

// ===============================
// Buscar dados atuais e 2021
// ===============================
$dadosAtual = getDadosApi($dataAtual);
$dados2021 = getDadosApi($data2021);

if (!$dadosAtual || !isset($dadosAtual["data"])) die("<p>❌ Erro ao obter dados atuais.</p>");
if (!$dados2021 || !isset($dados2021["data"])) die("<p>⚠️ Erro ao obter dados de 2021.</p>");

// ===============================
// Sistemas
// ===============================
$nomesSistemas = [
    64 => "Cantareira",
    65 => "Alto Tietê",
    66 => "Guarapiranga",
    67 => "Cotia",
    68 => "Rio Grande",
    69 => "Rio Claro",
    72 => "São Lourenço",
    75 => "SIM"
];

// ===============================
// Montagem tabela e séries
// ===============================
$tabela = [];
$seriesChuvas = [];

foreach ($dadosAtual["data"] as $sAtual) {
    $id = $sAtual["idSistema"];
    if (!isset($nomesSistemas[$id])) continue;

    $nome = $nomesSistemas[$id];
    $volAtual = $sAtual["volumeUtilArmazenadoPorcentagem"] ?? 0;

    // Buscar dados de 2021
    $s2021 = null;
    foreach ($dados2021["data"] as $s) {
        if ($s["idSistema"] == $id) { $s2021 = $s; break; }
    }
    $vol2021 = $s2021["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $dif = $volAtual - $vol2021;

    // Série de 7 dias
    $serie = getSerieUltimosDias($id, 7);
    $chuvas = array_column($serie, "chuva");
    $chuvaMedia7d = array_sum(array_filter($chuvas)) / max(count(array_filter($chuvas)), 1);

    $tabela[] = [
        "sistema" => $nome,
        "vol_atual" => $volAtual,
        "vol_2021" => $vol2021,
        "dif" => $dif,
        "chuva7dias" => round($chuvaMedia7d, 1)
    ];

    $seriesChuvas[$nome] = $serie;
}

// ===============================
// Datas para o gráfico
// ===============================
$labelsDias = array_map(fn($d) => date("d/m", strtotime($d["data"])), reset($seriesChuvas));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard Mananciais</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body { font-family: Arial, sans-serif; background: #f6f8fa; margin: 20px; color: #333; }
    h1 { text-align: center; color: #1b4b72; }
    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #1b4b72; color: #fff; }
    tr:nth-child(even) { background: #f2f2f2; }
    canvas { margin-top: 40px; }
</style>
</head>
<body>

<h1>📊 Dashboard dos Mananciais</h1>
<p style="text-align:center">Atualizado em <?= date("d/m/Y", strtotime($dataAtual)); ?></p>

<table>
<tr>
    <th>Sistema</th>
    <th>Volume Atual (%)</th>
    <th>Volume 2021 (%)</th>
    <th>Diferença (%)</th>
    <th>Chuva (média 7 dias)</th>
</tr>
<?php foreach ($tabela as $linha): ?>
<tr>
    <td><?= $linha["sistema"] ?></td>
    <td><?= number_format($linha["vol_atual"], 1, ",", ".") ?></td>
    <td><?= number_format($linha["vol_2021"], 1, ",", ".") ?></td>
    <td><?= number_format($linha["dif"], 1, ",", ".") ?></td>
    <td><?= number_format($linha["chuva7dias"], 1, ",", ".") ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- GRÁFICO DE CHUVA (7 dias) -->
<canvas id="graficoChuvas" height="120"></canvas>

<script>
const labels = <?= json_encode($labelsDias); ?>;
const series = <?= json_encode($seriesChuvas, JSON_UNESCAPED_UNICODE); ?>;

const datasets = Object.entries(series).map(([nome, dias]) => ({
    label: nome,
    data: dias.map(d => d.chuva ?? 0),
    fill: false,
    tension: 0.2,
    borderWidth: 2
}));

new Chart(document.getElementById('graficoChuvas'), {
    type: 'line',
    data: { labels, datasets },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Chuva dos Últimos 7 Dias (mm)', font: { size: 16, weight: 'bold' } },
            legend: { position: 'bottom' }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'mm' } }
        }
    }
});
</script>

</body>
</html>
