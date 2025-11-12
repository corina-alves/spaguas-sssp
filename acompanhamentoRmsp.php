<?php
header("Content-Type: text/html; charset=utf-8");

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
    $resposta = curl_exec($ch);
    curl_close($ch);
    return json_decode($resposta, true);
}


// Função para somar dados de chuva e vazão dos últimos N dias
// Função para calcular a média de chuva e vazão natural dos últimos N dias
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

    // ===== Vazão natural no último dia do mês de 2021 =====
    $ultimoDiaMesAtual = date("t"); // número do último dia do mês atual
    $mesAtual = date("m");
    $ano2021 = "2021";

    // Exemplo: se hoje for 2025-10-19, pega 2021-10-31
    $data2021 = "$ano2021-$mesAtual-$ultimoDiaMesAtual";
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

    // Cálculo da média (igual ao Python)
    $chuvaMedia7d = !empty($chuvas) ? array_sum($chuvas) / count($chuvas) : null;
    $vazaoMedia7d = !empty($vazoes) ? array_sum($vazoes) / count($vazoes) : null;

    return [
        "chuva7dias" => round($chuvaMedia7d, 1),
        "vazao7dias" => round($vazaoMedia7d, 1),
        "vazao2021"  => $vazao2021
    ];
}
// Buscar dados
$dadosAtual = getDadosApi($dataAtual);
$dados2021 = getDadosApi($data2021);

if (!$dadosAtual || !isset($dadosAtual["data"])) {
    die("<p>❌ Erro: não foi possível obter dados atuais da API.</p>");
}
if (!$dados2021 || !isset($dados2021["data"])) {
    die("<p>⚠️ Erro: não foi possível obter dados de 2021 da API.</p>");
}

// Mapas de nomes
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

foreach ($sistemasAtual as $sAtual) {
    $idSistema = $sAtual["idSistema"];
    $nome = $nomesSistemas[$idSistema] ?? "Sistema $idSistema";

    // Dados do mesmo sistema em 2021
    $s2021 = null;
    foreach ($sistemas2021 as $s) {
        if ($s["idSistema"] == $idSistema) { $s2021 = $s; break; }
    }

    $volAtual = $sAtual["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $vol2021 = $s2021["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $chuvaAtual = $sAtual["chuva"] ?? 0;
    $chuvaAcumMesAtual = $sAtual["chuvaAcumuladaNoMes"] ?? 0;
    $chuvaMediaHistoricaAtual = $sAtual["chuvaMediaHistorica"] ?? 0;
    $diferenca = $volAtual - $vol2021;


    // Últimos 7 dias
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
        // "vazao2021"  => $ultimos7["vazao2021"] // 🔹 novo campo adicionado à tabela
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Acompanhamento dos reservatórios da RMSP: <?= date("d/m/Y") ?> e 2021</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Plugin de rótulos -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <!-- Plugin de anotações -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>

<style>
body { font-family: "aptos", sans-serif; margin: 30px; }
h2 { color: #003366; text-align: center; margin-bottom: 20px; }
.card{border:solid 1px #ccc; box-shadow:2px 2px 8px #ccc; margin:0px; margin-bottom:15px; font-size:0.9em;}
.card h6 { font-weight: bold; font-size:1em; }
table { font-size:0.8em; }
canvas { margin-top: 40px; }
.tabela-container, .grafico-container { width: 100%; background: #fff; padding: 20px; border-radius:5px; box-shadow: 0 0 10px #ffffffff; margin-bottom: 0px; }
   .legenda-protocolo ul {
    list-style: none;
    padding: 0;
    margin: 10px auto;
    display: flex;
    flex-direction: row; /* horizontal */
    justify-content: center; /* centraliza */
    gap: 20px; /* espaço entre os itens */
}

.legenda-protocolo li {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.legenda-protocolo .cor {
    display: inline-block;
    width: 30px;
    height: 15px;
}

.legenda-protocolo .e1 { background: yellow; }
.legenda-protocolo .e2 { background: orange; }
.legenda-protocolo .e3 { background: red; }
.legenda-protocolo .e4 { background: purple;}
    </style>
</style>
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
<div class="tabela-container">
<!-- cards -->
<div class="container mb-4">
  <div class="row">
    <?php foreach ($tabela as $linha): 
        $dif = $linha["dif"]; // diferença entre atual e 2021, ou você pode calcular dia a dia
        $cor = $dif >= 0 ? 'text-success' : 'text-danger';
        $seta = $dif >= 0 ? '↑' : '↓';
    ?>
    <div class="col">
      <div class="card style="background: <?= $dif >= 0 ? '#198754aa' : '#dc3545aa' ?>;>
        <div class="card-body text-center">
          <h6 class="card-title"><?= htmlspecialchars($linha["sistema"]) ?></h6>
          <p class="card-text" style="font-size:1.2em;">
            <?= number_format($linha["vol_atual"],1,',','.') ?> %
          </p>
          <div class="mt-1 <?= $cor ?>" style="font-weight:bold;">
            <?= $seta ?> <?= number_format(abs($dif),1,',','.') ?> %
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="row">
    <div class="col-lg-10">
<canvas id="grafSistema" width="900" height="400"></canvas>
   <div class="legenda-protocolo mt-3">
        <ul>
            <li><span class="cor e1"></span> E1 - Atenção</li>
            <li><span class="cor e2"></span> E2 - Alerta</li>
            <li><span class="cor e3"></span> E3 - Crítico</li>
            <li><span class="cor e4"></span> E4 - Emergência</li>
        </ul>
    </div>
</div>
<div class="col-lg-2" style="margin-top:190px;">
<canvas id="graficoPizza"></canvas>
</div>
</div>

<hr/>
<!-- tabela de volumes e chuva ano atual e do ano de 2021-->
<table class="table table-hover">
<thead>
<tr>

<th>Sistema</th>
<th>Volume Atual (%)</th>
<th>Volume 2021 (%)</th>
<th>Diferença (%)</th>
<th>Chuva Diária (mm)</th>
<th>Chuva Acum. Mês Atual (mm)</th>
<th>Chuva Média Histórica Atual (mm)</th>
<th>Chuva 2021 (mm)</th>
<th>Chuva Acum. Mês 2021 (mm)</th>

</tr>
</thead>
<tbody>
<?php foreach ($tabela as $linha): ?>
<tr>
<td><?= htmlspecialchars($linha["sistema"]) ?></td>
<td><?= number_format($linha["vol_atual"],1,',','.') ?></td>
<td><?= number_format($linha["vol_2021"],1,',','.') ?></td>
<td><?= number_format($linha["dif"],1,',','.') ?></td>
<td><?= number_format($linha["chuva"],1,',','.') ?></td>
<td><?= number_format($linha["chuvaAcumuladaNoMes"],1,',','.') ?></td>
<td><?= number_format($linha["chuvaMediaHistoricaAtual"],1,',','.') ?></td>
<td><?= number_format($linha["chuva_2021"],1,',','.') ?></td>
<td><?= number_format($linha["chuvaAcumuladaNoMes_2021"],1,',','.') ?></td>

</tr>
<?php endforeach; ?>
</tbody>
</table>


<!-- <canvas id="grafVazaoChuva" width="900" height="400"></canvas> -->
<hr/>
   <div class="container p-5">
        <h4 class="text-center">Projeções de Volume do SIM</h4>
          <canvas id="grafico_linha"></canvas>
    </div>
<!-- tabela -->
<!-- <table class="table table-hover">
<thead>
<tr>
<th>Sistema</th>
<th>Volume Atual (%)</th>
<th>Volume 2021 (%)</th>
<th>Diferença (%)</th>
<th>Chuva (mm)</th>
<th>Chuva Acum. Mês (mm)</th>
<th>Chuva Média Hist. (mm)</th>
<th>Chuva 2021 (mm)</th>
<th>Chuva Acum. Mês 2021 (mm)</th>
<th>Vazão Natural (m³/s)</th>
<th>Vazão Natural Mês (m³/s)</th>
<th>Vazão Nat. Média Hist. (m³/s)</th>
<th>Chuva últimos 7 dias (mm)</th>
<th>Vazão últimos 7 dias (m³/s)</th>
</tr>
</thead>
<tbody>
<?php foreach ($tabela as $linha): ?>
<tr>
    <td><?= htmlspecialchars($linha["sistema"]) ?></td>
    <td><?= number_format($linha["vol_atual"],1,',','.') ?></td>
    <td><?= number_format($linha["vol_2021"],1,',','.') ?></td>
    <td><?= number_format($linha["dif"],1,',','.') ?></td>
    <td><?= number_format($linha["chuva"],1,',','.') ?></td>
    <td><?= number_format($linha["chuvaAcumuladaNoMes"],1,',','.') ?></td>
    <td><?= number_format($linha["chuvaMediaHistoricaAtual"],1,',','.') ?></td>
    <td><?= number_format($linha["chuva_2021"],1,',','.') ?></td>
    <td><?= number_format($linha["chuvaAcumuladaNoMes_2021"],1,',','.') ?></td>
    <td><?= number_format($linha["vazaoNatural"],1,',','.') ?></td>
    <td><?= number_format($linha["vazaoNaturalNoMes"],1,',','.') ?></td>
    <td><?= number_format($linha["vazaoNaturalMediaHistorica"],1,',','.') ?></td>
    <td><?= number_format($linha["chuva7dias"],1,',','.') ?></td>
    <td><?= number_format($linha["vazao7dias"],1,',','.') ?></td>
</tr>
    <?php endforeach; ?>
</tbody>
</table> -->

<!-- <canvas id="grafChuva" width="900" height="400"></canvas> -->
<hr/>
<!-- <canvas id="grafBarraEmpilhada" width="900" height="400"></canvas> -->
</div>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.umd.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
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

// ================== GRÁFICO VOLUME ==================
new Chart(document.getElementById("grafSistema"), { 
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: "Volume do Mês de Ano/2021 (%)",
                type: "bar",
                data: vol2021,
                backgroundColor: "#1d73f3"
            },
            {
                label: "Volume Atual (%)",
                type: "bar",
                data: volAtual,
                backgroundColor: "#0448a1"
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: "top" },
            title: {
                display: true,
                text: "Acompanhamento do Volume Útil <?= date('d/m/Y') ?>."
            },
            datalabels: {
                display: true,
                anchor: 'end',      // posição acima da barra
                align: 'end',       // alinhamento superior
                color: '#000',
                font: { weight: 'bold', size: 11 },
                formatter: (value) => value !== null ? value.toFixed(1) + '%' : ''
            },
            annotation: {
                annotations: {
                    atencao60: { type: 'line', yMin: 60, yMax: 60, borderColor: 'yellow', borderWidth: 2 },
                    critico40: { type: 'line', yMin: 40, yMax: 40, borderColor: 'orange', borderWidth: 2 },
                    critico30: { type: 'line', yMin: 30, yMax: 30, borderColor: 'red', borderWidth: 2 },
                    emergencia: { type: 'line', yMin: 20, yMax: 20, borderColor: 'purple', borderWidth: 2 }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Volume Util (%)' },
                ticks: { stepSize: 10 }
            }
        }
    },
    plugins: [ChartDataLabels] // 🔹 Ativa labels no topo
});

// -- ----------------- GRÁFICO PIZZA -----------------
const Cantareira = 50.50; const AltoTiete = 28.80; const Guarapiranga = 8.80;const RioGrande = 5.80; const SaoLourenco = 4.60; const RioClaro = 0.70; const Cotia = 0.80;
const ctx2 = document.getElementById('graficoPizza').getContext('2d');
const graficoPizza = new Chart(ctx2, {
    //   type: 'pie',
    type: 'doughnut',
      data: {
        labels: ['Cantareira','Alto Tietê','Guarapiranga','Rio Grande','São Lourenço','Rio Claro','Cotia'],
        datasets: [{
          data: [Cantareira, AltoTiete, Guarapiranga, RioGrande, SaoLourenco, RioClaro, Cotia],
          backgroundColor: [
            '#0077cc', '#e0e0e0', '#084391ff', '#0ca73aff', '#726f6fff', '#e00e0eff', '#5e4040ff'
          ],
          borderColor: '#ffffff',
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
             // display: false 🔹 Desativa a legenda
             position: 'right',
            labels: {
                boxWidth: 12,   // 🔹 largura da barrinha (padrão ~40)
                boxHeight: 12,  // 🔹 altura da barrinha
                padding: 5,     // espaço entre texto e barrinha
                font: {
                size: 12     // tamanho da fonte da legenda
                    }
      }

          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.label || '';
                let valor = context.parsed || 0;
                return label + ': ' + valor.toFixed(2) + '%';
              }
            }
          }
        }
      }
    });

// as projeções
async function projecoes(url) {
    const resposta = await fetch(url);
    const texto = await resposta.text();
    const linhas = texto.split("\n").map(l => l.trim()).filter(l => l.length);
    const cabecalho = linhas[0].split(";");
    const dados = linhas.slice(1).map(l => l.split(";"));

    return { cabecalho, dados };
}

async function criarGraficoLinha() {
    const { cabecalho, dados } = await projecoes("serie_diaria.csv");
    const labels = dados.map(l => l[0]);
    const cores = {
        "QN 100 MLT":  "#021d58",
        "QN 70 MLT":   "#cc0505",
        "QN (20-25)":  "#046e1f",
        "QN (2021)":   "#0cb130",
        "QN (2014)":   "#df5c11ff",
        "Real": "#000000" // preto forte
    };

    const datasets = cabecalho.slice(1).map((nome, idx) => {
        const isObservado = nome === "Real"; // checar se é a série a destacar

           return {
            label: nome === "Real" ? "Real" : nome,
            data: dados.map(l => parseFloat(l[idx+1]) || null),
            borderColor: cores[nome] || `hsl(${Math.random()*360}, 50%, 30%)`,
            borderWidth: isObservado ? 4 : 2,   // linha mais grossa
            pointBackgroundColor: isObservado ? "#000000" : cores[nome],
            pointRadius: isObservado ? 4 : 1,  // pontos maiores (o observado)
    
            fill: false
        }
    });

    new Chart(document.getElementById("grafico_linha"), {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                title: { display: true, text: 'Séries Diárias' },
                legend: { position: 'top' },
                
            },
            // scales: {
            //     x: { title: { display: true, text: 'Data' } },
            //     y: { title: { display: true, text: 'Valor' } }
            // }
            scales: {
    x: { title: { display: true, text: 'Data' } },
    y: { 
        title: { display: true, text: 'Volume' },
        min: 25,
        max: 45,
        ticks: {
            stepSize: 5
        }
    }
}
        }
    });
}

criarGraficoLinha();

// ================== GRÁFICO VAZÃO + CHUVA ==================
// new Chart(document.getElementById("grafVazaoChuva"), {
//     type: 'bar',
//     data: {
//         labels: labels,
//         datasets: [
//             { label:"Vazão do Dia (m³/s)", type:"bar", data:vazaoNatural, backgroundColor:"#da6314cf", yAxisID:'y' },
//             {
//                 label:"Vazão últimos 7 dias (m³/s)",
//                 type:"bar",
//                 data:vazao7dias,
//                 backgroundColor:"#f0cd09ff",
//                 yAxisID:'y'
//             },
//             { label:"Vazão do Mês (m³/s)", type:"bar", data:vazaoNaturalNoMes, backgroundColor:"rgba(255,159,64,0.7)", yAxisID:'y' },
//             { label:"Vazão Mês Ano de 2021 (m³/s)", type:"bar", data:vazaoNaturalNoMes2021, backgroundColor:"rgba(11,75,5,0.73)", yAxisID:'y' },
//             { label:"Vazão Natural Méd. Histórica (m³/s)", type:"bar", data:vazaoNaturalMediaHistorica, backgroundColor:"#ffcc00aa", yAxisID:'y' }
//         ]
//     },
//     options: {
//         responsive:true,
//         interaction:{ mode:'index', intersect:false },
//         plugins:{
//             legend:{ position:"top" },
//             title:{ display:true, text:"Acompanhamento Chuvas e Vazões do Dia <?= date('d/m/Y') ?> e últimos 7 dias" },
//             datalabels: {
//                 display: true,
//                 anchor: 'end',
//                 align: 'end',
//                 color: '#000',
//                 font: { weight: 'bold', size: 10 },
//                 formatter: (value) => value !== null ? value.toFixed(1) : ''
//             }
//         },
//         scales:{
//             y:{
//                 type:'linear',
//                 position:'left',
//                 title:{ display:true, text:"Vazão (m³/s)" },
//                 beginAtZero:true,
//                 grid:{ drawOnChartArea:false }
//             }
//         }
//     },
//     plugins: [ChartDataLabels] // 🔹 Ativa labels no topo
// });

// ----------------- GRÁFICO CHUVA -----------------
// new Chart(document.getElementById("grafChuva"),{
// data:{
// labels:labels,
// datasets:[
// {label:"Chuva Atual (mm)", type:"bar", data:chuvaAtual, backgroundColor:"#03c0fab3", yAxisID:'y1'},
// {label:"Chuva acum. do mês Atual (mm)", type:"bar", data:chuvaMesAtual, backgroundColor:"#0e59e4ff", yAxisID:'y1'},
// {label:"Chuva Média Histórica (mm)", type:"bar", data:chuvaMediaHistorica, backgroundColor:"#0082fc99", yAxisID:'y1'},
// {label:"Chuva acum. Mês 2021 (mm)", type:"bar", data:chuvaAcumMes2021, backgroundColor:"#0c830c99", yAxisID:'y1'},
// {label:"Chuva últimos 7 dias (mm)", type:"bar", data:chuva7dias, backgroundColor:"#ffcc00aa", yAxisID:'y1'}
// ]
// },
// options:{
// responsive:true,
// interaction:{mode:'index',intersect:false},
// plugins:{legend:{position:"top"},title:{display:true,text:"Chuva RMSP dia <?= date('d/m/Y') ?> e últimos 7 dias"}}, 
// scales:{y1:{type:'linear',position:'left',title:{display:true,text:"Chuva (mm)"},beginAtZero:true}}
// }
// });

// ----------------- GRÁFICO VAZÕES EMPILHADAS -----------------
// new Chart(document.getElementById("grafBarraEmpilhada"), {
// type:"bar",
// data:{
// labels:labels,
// datasets:[
// {label:"Vazão Média Histórica (m³/s)", data:vazaoNaturalMediaHistorica, backgroundColor:"rgba(54,162,235,0.6)", stack:"vazao", yAxisID:"y"},
// {label:"Vazão do Mês (m³/s)", data:vazaoNaturalNoMes, backgroundColor:"rgba(75,192,192,0.7)", stack:"vazao", yAxisID:"y"},
// {label:"Vazão do Dia (m³/s)", data:vazaoNatural, backgroundColor:"rgba(0,123,255,0.9)", stack:"vazao", yAxisID:"y"},
// {label:"Vazão do Mês 2021 (m³/s)", data:vazaoNaturalNoMes2021, backgroundColor:"hsla(46,94%,51%,0.7)", stack:"vazao", yAxisID:"y"},
// {label:"Vazão últimos 7 dias (m³/s)", data:vazao7dias, backgroundColor:"rgba(0,200,0,0.7)", stack:"vazao", yAxisID:"y"}
// ]
// },
// options:{
// responsive:true,
// plugins:{legend:{position:"top"},title:{display:true,text:"Vazão e Chuva (<?= date('d/m/Y') ?>)"}},
// scales:{y:{beginAtZero:true,title:{display:true,text:"Vazão (m³/s)"},stacked:true}}
// }
// });
</script>

</body>
</html>
