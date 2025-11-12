<?php
// ===============================
// dashboard.php
// ===============================
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

$dataAtual = date("Y-m-d");
$data2021 = "2021-" . date("m-d");

// ===============================
// Função para buscar dados da API
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
// Função para médias + vazão 2021
// ===============================
function getDadosUltimosDias($idSistema, $dias = 7) {
    $chuvas = [];
    $vazoes = [];

    for ($i = 0; $i < $dias; $i++) {
        $data = date("Y-m-d", strtotime("-$i days"));
        $dados = getDadosApi($data);

        if (isset($dados["data"])) {
            foreach ($dados["data"] as $s) {
                if ($s["idSistema"] == $idSistema) {
                    if (isset($s["chuva"])) $chuvas[] = $s["chuva"];
                    if (isset($s["vazaoNatural"])) $vazoes[] = $s["vazaoNatural"];
                }
            }
        }
    }

    // === Vazão do último dia do mês de 2021 ===
    $ultimoDiaMesAtual = date("t");
    $mesAtual = date("m");
    $data2021 = "2021-$mesAtual-$ultimoDiaMesAtual";
    $dados2021 = getDadosApi($data2021);
    $vazao2021 = null;

    if (isset($dados2021["data"])) {
        foreach ($dados2021["data"] as $s) {
            if ($s["idSistema"] == $idSistema) {
                $vazao2021 = $s["vazaoNatural"] ?? null;
                break;
            }
        }
    }

    $chuvaMedia7d = !empty($chuvas) ? array_sum($chuvas) / count($chuvas) : null;
    $vazaoMedia7d = !empty($vazoes) ? array_sum($vazoes) / count($vazoes) : null;

    return [
        "chuva7dias" => round($chuvaMedia7d, 1),
        "vazao7dias" => round($vazaoMedia7d, 1),
        "vazao2021"  => $vazao2021
    ];
}

// ===============================
// Buscar dados
// ===============================
$dadosAtual = getDadosApi($dataAtual);
$dados2021 = getDadosApi($data2021);

if (!$dadosAtual || !isset($dadosAtual["data"])) die("<p>❌ Erro: não foi possível obter dados atuais da API.</p>");
if (!$dados2021 || !isset($dados2021["data"])) die("<p>⚠️ Erro: não foi possível obter dados de 2021 da API.</p>");

// ===============================
// Mapeamento
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
// Montagem dos dados
// ===============================
$sistemasAtual = array_filter($dadosAtual["data"], fn($s) => $s["idSistema"] != 74);
$sistemas2021 = array_filter($dados2021["data"], fn($s) => $s["idSistema"] != 74);

$labels = [];
$volumesAtual = [];
$volumes2021 = [];
$tabela = [];

foreach ($sistemasAtual as $sAtual) {
    $id = $sAtual["idSistema"];
    $nome = $nomesSistemas[$id] ?? "Sistema $id";

    $s2021 = null;
    foreach ($sistemas2021 as $s) {
        if ($s["idSistema"] == $id) { $s2021 = $s; break; }
    }

    $volAtual = $sAtual["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $vol2021 = $s2021["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $dif = $volAtual - $vol2021;

    $ultimos7 = getDadosUltimosDias($id, 7);

    $labels[] = $nome;
    $volumesAtual[] = $volAtual;
    $volumes2021[] = $vol2021;

    $tabela[] = [
        "sistema" => $nome,
        "vol_atual" => $volAtual,
        "vol_2021" => $vol2021,
        "dif" => $dif,
        "chuva7dias" => $ultimos7["chuva7dias"],
        "vazao7dias" => $ultimos7["vazao7dias"],
        "vazao2021"  => $ultimos7["vazao2021"]
    ];
}
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
    canvas { margin-top: 30px; }
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
        <th>Vazão (média 7 dias)</th>
        <th>Vazão 2021 (último dia do mês)</th>
    </tr>
    <?php foreach ($tabela as $linha): ?>
    <tr>
        <td><?= $linha["sistema"] ?></td>
        <td><?= number_format($linha["vol_atual"], 1, ",", ".") ?></td>
        <td><?= number_format($linha["vol_2021"], 1, ",", ".") ?></td>
        <td><?= number_format($linha["dif"], 1, ",", ".") ?></td>
        <td><?= number_format($linha["chuva7dias"], 1, ",", ".") ?></td>
        <td><?= number_format($linha["vazao7dias"], 1, ",", ".") ?></td>
        <td><?= $linha["vazao2021"] ? number_format($linha["vazao2021"], 1, ",", ".") : "-" ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<canvas id="graficoVolumes" height="100"></canvas>

<script>
// Dados PHP → JS
const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
const volumesAtual = <?= json_encode($volumesAtual); ?>;
const volumes2021 = <?= json_encode($volumes2021); ?>;

// Gráfico
const ctx = document.getElementById("graficoVolumes");
new Chart(ctx, {
    type: "bar",
    data: {
        labels: labels,
        datasets: [
            {
                label: "Volume Atual (%)",
                data: volumesAtual,
                backgroundColor: "rgba(54, 162, 235, 0.7)"
            },
            {
                label: "Volume 2021 (%)",
                data: volumes2021,
                backgroundColor: "rgba(255, 99, 132, 0.7)"
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: "Comparativo de Volume Atual vs 2021" },
            legend: { position: "bottom" }
        },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: "%" } }
        }
    }
});
</script>

</body>
</html>
