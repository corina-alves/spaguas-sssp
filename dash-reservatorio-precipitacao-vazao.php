<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

$dataAtual = date("Y-m-d");
$mesAtual = date("m");
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

// ====================================================================
// Função para calcular médias e pegar a vazão do último dia do mês 2021
// ====================================================================
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
table {border-collapse: collapse; width:100%; max-width:100px; margin-bottom:30px; font-size:14px;}
th, td {border:1px solid #ddd; padding:8px; text-align:center;}
th {background:#0b6a4a; color:white;}
tr:last-child {font-weight:bold; background:#e9f9ee;}
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
</head>
<body>
<!-- <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg,#004aad,#0077b6);">
  <div class="container">
    <a class="navbar-brand" href="#">💧 Monitoramento Hidrológico</a>
    <span class="text-white ms-3">Dashboard — Vazões & Chuvas</span>
  </div>
</nav> -->
<div class="container mt-4">
  <!-- Abas -->
  <ul class="nav nav-tabs" id="dashTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="sistema-tab" data-bs-toggle="tab" data-bs-target="#sistema" type="button" role="tab" aria-controls="vazoes" aria-selected="true">Reservatorios</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link " id="vazoes-tab" data-bs-toggle="tab" data-bs-target="#vazoes" type="button" role="tab" aria-controls="vazoes" aria-selected="true">Vazões</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="chuvas-tab" data-bs-toggle="tab" data-bs-target="#chuvas" type="button" role="tab" aria-controls="chuvas" aria-selected="false">Chuvas</button>
    </li>
       <li class="nav-item" role="presentation">
      <button class="nav-link" id="projecoes-tab" data-bs-toggle="tab" data-bs-target="#projecoes" type="button" role="tab" aria-controls="projecoes" aria-selected="false">Projeções</button>
    </li>
    <li class="nav-item ms-auto">
      <small class="text-muted" style="margin-top:10px;">Atualizado em: <?php echo date('d/m/Y H:i'); ?></small>
    </li>
  </ul>

  <div class="tab-content">
    <!-- RESERVATORIOS -->
    <div class="tab-pane fade" id="sistema" role="tabpanel" aria-labelledby="sistema-tab">
      <div class="card mt-3">
        <h2 style="text-align:center; margin-top:30px;">Acompanhamento dos <strong>Reservatórios </strong></h2>
<h4 style="text-align:center; margin-bottom:30px;"><i><?= date("d/m/Y", strtotime($dataAtual)); ?> | Ano de referência: <?= $data2021 = "2021-" . date("m-d") ?></i></h4>        <div class="card-body">
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
        </div>
      </div>
    </div>

    <!-- VAZÕES -->
    <div class="tab-pane fade show active" id="vazoes" role="tabpanel" aria-labelledby="vazoes-tab">
      <div class="card mt-3">
                <h2 style="text-align:center; margin-top:30px;">Acompanhamento das <strong>Vazões Naturais </strong></h2>
<h4 style="text-align:center; margin-bottom:30px;"><i><?= date("d/m/Y", strtotime($dataAtual)); ?> | Ano de referência: <?= $data2021 = "2021-" . date("m-d") ?></i></h4>        <div class="card-body">
  
        <div class="card-body">
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

    <!-- CHUVAS -->
    <div class="tab-pane fade" id="chuvas" role="tabpanel" aria-labelledby="chuvas-tab">
      <div class="card mt-3">
        <div class="card-body">
          <?php
          // INCLUDE DO SEU ARQUIVO DE CHUVA (o código que você enviou)
          if (file_exists(__DIR__ . '/precipitacao.php')) {
              include __DIR__ . '/precipitacao.php';
              // Observação: chuva_sistemas.php já gera HTML (tabela + canvas + script Chart.js)
          } else {
              echo '<div class="alert alert-warning">Arquivo <strong>precipitacao.php</strong> não encontrado.</div>';
          }
          ?>
        </div>
      </div>
    </div>

    <!-- CHUVAS -->
    <div class="tab-pane fade" id="projecoes" role="tabpanel" aria-labelledby="projecoes-tab">
      <div class="card mt-3">
        <div class="card-body">
          <?php
          // INCLUDE DO SEU ARQUIVO DE CHUVA (o código que você enviou)
          if (file_exists(__DIR__ . '/projecoes.php')) {
              include __DIR__ . '/projecoes.php';
              // Observação: chuva_sistemas.php já gera HTML (tabela + canvas + script Chart.js)
          } else {
              echo '<div class="alert alert-warning">Arquivo <strong>projecoes.php</strong> não encontrado. Coloque aqui o arquivo com seu código de chuva.</div>';
          }
          ?>
        </div>
      </div>
    </div>

  </div>
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

// ================== GRÁFICO VOLUME ==================
Chart.register(ChartDataLabels);
new Chart(document.getElementById("grafSistema"), { 
    type: 'bar',
    data: {
        labels: labels,
        datasets: 
        [
            {label: "Volume do Mês de Ano/2021 (%)", type: "bar",data: vol2021,backgroundColor: "#1d73f3" },
            {label: "Volume Atual (%)",type: "bar",data: volAtual,backgroundColor: "#0448a1"}
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: "top" },
            title: {display: true,text: "Acompanhamento do Volume Útil <?= date('d/m/Y') ?>."
            },
            datalabels: 
            {display: true, anchor: 'end', align: 'top', color: '#000',font: { weight: 'bold', size: 11 },formatter: (value) => value !== null ? value.toFixed(1) + '%' : ''},
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
            title:{ display:true, text:"Acompanhamento Chuvas e Vazões do Dia <?= date('d/m/Y') ?> e últimos 7 dias" },
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
