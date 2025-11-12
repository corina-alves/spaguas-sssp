<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

$dataAtual = date("Y-m-d");
$mesAtual = date("m");
$data2021 = "2021-" . date("m-d");

// Função para buscar dados da API

function getDadosApi($data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // === CONFIGURAÇÃO DE PROXY ===
    //curl_setopt($ch, CURLOPT_PROXY, "10.200.12.140:80"); // endereço e porta do proxy

    $resposta = curl_exec($ch);
    curl_close($ch);
    return json_decode($resposta, true);
}

// Função para calcular médias e pegar a vazão do último dia do mês 2021
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

    // === Pega vazão natural no último dia do mês correspondente de 2021 ===
    $ultimoDiaMesAtual = date("t"); // último dia do mês atual
    $mesAtual = date("m");
    $data2021 = "2021-$mesAtual-$ultimoDiaMesAtual"; // exemplo: 2021-10-31
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
        "vazao2021"  => $vazao2021 // 🔹 retorna a vazão do último dia do mês de 2021
    ];
}

// ===============================
// Buscar dados atuais e de 2021
// ===============================
$dadosAtual = getDadosApi($dataAtual);
$dados2021 = getDadosApi($data2021);

if (!$dadosAtual || !isset($dadosAtual["data"])) {
    die("<p>❌ Erro: não foi possível obter dados atuais da API.</p>");
}
if (!$dados2021 || !isset($dados2021["data"])) {
    die("<p>⚠️ Erro: não foi possível obter dados de 2021 da API.</p>");
}

// ===============================
// Mapeamento e estruturação
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

$sistemasAtual = array_filter($dadosAtual["data"], fn($s) => $s["idSistema"] != 74);
$sistemas2021 = array_filter($dados2021["data"], fn($s) => $s["idSistema"] != 74);

$labels = [];
$volumesAtual = [];
$volumes2021 = [];
$difVol = [];
$tabela = [];

// ===============================
// Monta tabela com todos os dados
// ===============================
foreach ($sistemasAtual as $sAtual) {
    $idSistema = $sAtual["idSistema"];
    $nome = $nomesSistemas[$idSistema] ?? "Sistema $idSistema";

    $s2021 = null;
    foreach ($sistemas2021 as $s) {
        if ($s["idSistema"] == $idSistema) { 
            $s2021 = $s; 
            break; 
        }
    }

    $volAtual = $sAtual["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $vol2021 = $s2021["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $chuvaAtual = $sAtual["chuva"] ?? 0;
    $chuvaAcumMesAtual = $sAtual["chuvaAcumuladaNoMes"] ?? 0;
    $chuvaMediaHistoricaAtual = $sAtual["chuvaMediaHistorica"] ?? 0;
    $diferenca = $volAtual - $vol2021;

    $ultimos7 = getDadosUltimosDias($idSistema, 7);

    $labels[] = $nome;
    $volumesAtual[] = $volAtual;
    $volumes2021[] = $vol2021;
    $difVol[] = $diferenca;

    $tabela[] = [
        "sistema" => $nome,
        "vol_atual" => $volAtual,
        "vol_2021" => $vol2021,
        "dif" => $diferenca,
        "chuva" => $chuvaAtual,
        "chuvaAcumuladaNoMes" => $chuvaAcumMesAtual,
        "chuvaMediaHistoricaAtual" => $chuvaMediaHistoricaAtual,
        "chuva_2021" => $s2021["chuva"] ?? 0,
        "chuvaAcumuladaNoMes_2021" => $s2021["chuvaAcumuladaNoMes"] ?? 0,
        "chuvaMediaHistorica" => $s2021["chuvaMediaHistorica"] ?? 0,
        "vazaoAfluente" => $sAtual["vazaoAfluente"] ?? 0,
        "vazaoNatural" => $sAtual["vazaoNatural"] ?? 0,
        "vazaoNaturalNoMes" => $sAtual["vazaoNaturalNoMes"] ?? 0,
        "vazaoNaturalMediaHistorica" => $sAtual["vazaoNaturalMediaHistorica"] ?? 0,
        "vazaoNatural_2021" => $s2021["vazaoNatural"] ?? 0,
        "vazaoNaturalNoMes_2021" => $s2021["vazaoNaturalNoMes"] ?? 0,
        "vazaoNaturalMediaHistorica_2021" => $s2021["vazaoNaturalMediaHistorica"] ?? 0,
        "chuva7dias" => $ultimos7["chuva7dias"],
        "vazao7dias" => $ultimos7["vazao7dias"],
        "vazao2021"  => $ultimos7["vazao2021"] // 🔹 vazão do último dia do mês em 2021
    ];
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Hidrológico — Vazões & Chuvas</title>
<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Chart.js (compartilhado) -->
<link href="assets/img/logo/logo.png" rel="icon">
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>  
  <link href="assets/cdn/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<style>
body { background:#f7f9fb; font-family: Arial, Helvetica, sans-serif; padding-bottom:60px; }
.navbar-brand { font-weight:700; }
.card { box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
table {border-collapse: collapse;  margin-bottom:30px; font-size:14px;}
th, td {border:1px solid #ddd; padding:8px; text-align:center;}
th {background:#0b6a4a; color:white;}
tr:last-child {font-weight:bold; background:#e9f9ee;}

</style>
</head>
<body>
   
<div class="container mt-4">
    <div class="card mt-3">
        <div class="card-body">
    <h2 style="text-align:center; margin-top:30px;">Comparativo de <strong>Vazões</strong> Sistemas Produtores (m³/s)</h2>
        <h4 style="text-align:center; margin-bottom:30px;"><i><?= date("d/m/Y", strtotime($dataAtual)); ?> | Ano de referência: <?= $data2021 = "2021-" ?></i></h4>
    <!-- VAZÕES -->
            <canvas id="grafVazao" width="900" height="400"></canvas>
            <!-- tabela -->
                <table class="table table-hover">
                    <thead>
                    <tr>
                    <th>Sistema</th>
                    <th>Vazão Natural (m³/s)</th>
                    <th>Vazão Natural Mês (m³/s)</th>
                    <th>Vazão Nat. Média Hist. (m³/s)</th>
                    <th>Vazão últimos 7 dias (m³/s)</th>
                    <th>Vazão 2021 (m³/s) (último dia do Mês)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tabela as $linha): ?>
                        <tr>
                            <td><?= htmlspecialchars($linha["sistema"]) ?></td>
                            <td><?= number_format($linha["vazaoNatural"],1,',','.') ?></td>
                            <td><?= number_format($linha["vazaoNaturalNoMes"],1,',','.') ?></td>
                            <td><?= number_format($linha["vazaoNaturalMediaHistorica"],1,',','.') ?></td>
                            <td><?= number_format($linha["vazao7dias"],1,',','.') ?></td>
                            <td><?= $linha["vazao2021"] ? number_format($linha["vazao2021"], 1, ",", ".") : "-" ?></td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
    </div>
    </div>
</div>  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Plugin de rótulos -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <!-- Plugin de anotações -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>

<script>
    const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
    const volAtual = <?= json_encode(array_column($tabela,"vol_atual")); ?>;// cvolume atual
    const vol2021 = <?= json_encode(array_column($tabela,"vol_2021")); ?>;// volume de 2021
    const diferenca = <?= json_encode(array_column($tabela,"dif")); ?>; // diferença de volume
    const chuvaAtual = <?= json_encode(array_column($tabela,"chuva")); ?>;
    const chuvaMesAtual = <?= json_encode(array_column($tabela,"chuvaAcumuladaNoMes")); ?>;
    const chuvaMediaHistorica = <?= json_encode(array_column($tabela,"chuvaMediaHistorica")); ?>;
    const chuvaAcumMes2021 = <?= json_encode(array_column($tabela,"chuvaAcumuladaNoMes_2021")); ?>;
    const chuva2021 = <?= json_encode(array_column($tabela,"chuva_2021")); ?>;
    const vazaoNatural = <?= json_encode(array_column($tabela,"vazaoNatural")); ?>;
    const vazaoNaturalNoMes = <?= json_encode(array_column($tabela,"vazaoNaturalNoMes")); ?>;
    const vazaoNaturalMediaHistorica = <?= json_encode(array_column($tabela,"vazaoNaturalMediaHistorica")); ?>;
    const vazaoNaturalNoMes2021 = <?= json_encode(array_column($tabela,"vazaoNaturalNoMes_2021")); ?>;
    const chuva7dias = <?= json_encode(array_column($tabela,"chuva7dias")); ?>;
    const vazao7dias = <?= json_encode(array_column($tabela,"vazao7dias")); ?>;

    // ================== GRÁFICO VAZÃO + CHUVA ==================
new Chart(document.getElementById("grafVazao"), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            { label:"Vazão do Dia (m³/s)", type:"bar", data:vazaoNatural, backgroundColor:"#da6314cf", yAxisID:'y' },
            {
                label:"Vazão últimos 7 dias (m³/s)",
                type:"bar",
                data:vazao7dias,
                backgroundColor:"#f0cd09ff",
                yAxisID:'y'
            },
            { label:"Vazão do Mês (m³/s)", type:"bar", data:vazaoNaturalNoMes, backgroundColor:"rgba(255,159,64,0.7)", yAxisID:'y' },
            { label:"Vazão Mês Ano de 2021 (m³/s)", type:"bar", data:vazaoNaturalNoMes2021, backgroundColor:"rgba(11,75,5,0.73)", yAxisID:'y' },
            { label:"Vazão Natural Méd. Histórica (m³/s)", type:"bar", data:vazaoNaturalMediaHistorica, backgroundColor:"#ffcc00aa", yAxisID:'y' }
        ]
    },
    options: {
        responsive:true,
        interaction:{ mode:'index', intersect:false },
        plugins:{
            legend:{ position:"top" },
            // title:{ display:true, text:"Acompanhamento Chuvas e Vazões do Dia <?= date('d/m/Y') ?> e últimos 7 dias" },
            datalabels: {
                display: true,
                anchor: 'end',
                align: 'top',
                color: '#000',
                font: { weight: 'bold', size: 10 },
                formatter: (value) => value !== null ? value.toFixed(1) : ''
            }
        },
        scales:{
            y:{
                type:'linear',
                position:'left',
                title:{ display:true, text:"Vazão (m³/s)" },
                beginAtZero:true,
                grid:{ drawOnChartArea:false }
            }
        }
    },
    plugins: [ChartDataLabels] // 🔹 Ativa labels no topo
});
</script>
</body>
</html>
