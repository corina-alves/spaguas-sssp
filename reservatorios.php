<?php 
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

// ===============================
// CONFIGURAÇÕES
// ===============================
$dataAtual = date("Y-m-d");
$cacheDir = __DIR__ . "/cache";
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

// ===============================
// Função: Busca dados da API com cache
// ===============================
function getDadosApi($data, $forcarAtualizacao = false) {
    global $cacheDir;
    $cacheFile = "$cacheDir/dados_$data.json";
    $tempoExpira = 1800; // 30 minutos

    // Usa o cache se estiver válido
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $tempoExpira && !$forcarAtualizacao) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        // === CONFIGURAÇÃO DE PROXY (opcional) ===
        // CURLOPT_PROXY => "10.200.12.140:80"
    ]);
    $resposta = curl_exec($ch);
    curl_close($ch);

    if ($resposta && ($dados = json_decode($resposta, true))) {
        // Se veio dado válido, salva no cache
        file_put_contents($cacheFile, $resposta);
        return $dados;
    }

    // Caso falhe, retorna cache antigo se existir
    if (file_exists($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    return null;
}

// ===============================
// Buscar dados com fallback automático
// ===============================
$tentativas = 0;
$maxDiasRetrocesso = 5; // tenta até 5 dias atrás se API não respondeu
$dataUsada = $dataAtual;
$dadosAtual = null;

while ($tentativas < $maxDiasRetrocesso) {
    $dadosAtual = getDadosApi($dataUsada);
    if (isset($dadosAtual["data"]) && !empty($dadosAtual["data"])) {
        break;
    }
    $tentativas++;
    $dataUsada = date("Y-m-d", strtotime("-$tentativas day"));
}

if (!$dadosAtual) {
    die("<div style='text-align:center;padding:2em;font-family:sans-serif;color:#a00'>
        ⚠️ Não foi possível obter dados recentes da API.<br>Tente novamente mais tarde.
    </div>");
}

echo "<script>console.log('📅 Dados carregados da data: $dataUsada');</script>";

// ===============================
// Dados comparativos 2021
// ===============================
$data2021 = "2021-" . date("m-d", strtotime($dataUsada));
$dados2021 = getDadosApi($data2021);

// ===============================
// Montagem dos dados
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

$sistemasAtual = array_filter($dadosAtual["data"] ?? [], fn($s) => $s["idSistema"] != 74);
$sistemas2021 = array_filter($dados2021["data"] ?? [], fn($s) => $s["idSistema"] != 74);

$tabela = [];
$labels = [];
$volAtual = [];
$vol2021 = [];
$dif = [];

foreach ($sistemasAtual as $sAtual) {
    $id = $sAtual["idSistema"];
    $nome = $nomesSistemas[$id] ?? "Sistema $id";
    $s2021 = null;

    foreach ($sistemas2021 as $s) {
        if ($s["idSistema"] == $id) { $s2021 = $s; break; }
    }

    $volA = $sAtual["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $vol21 = $s2021["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $chuva = $sAtual["chuva"] ?? 0;
    $chuvaMes = $sAtual["chuvaAcumuladaNoMes"] ?? 0;
    $chuvaHist = $sAtual["chuvaMediaHistorica"] ?? 0;
    $difVol = round($volA - $vol21, 1);

    $labels[] = $nome;
    $volAtual[] = $volA;
    $vol2021[] = $vol21;
    $dif[] = $difVol;

    $tabela[] = [
        "sistema" => $nome,
        "vol_atual" => $volA,
        "vol_2021" => $vol21,
        "dif" => $difVol,
        "chuva" => $chuva,
        "chuvaAcumuladaNoMes" => $chuvaMes,
        "chuvaMediaHistorica" => $chuvaHist,
        "chuva_2021" => $s2021["chuva"] ?? 0,
        "chuvaAcumuladaNoMes_2021" => $s2021["chuvaAcumuladaNoMes"] ?? 0
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Reservatórios RMSP - <?= date("d/m/Y") ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>
 <link rel="stylesheet" href="assets/css/aptos.css">


<style>
#loader p { animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100%{opacity:0.5}50%{opacity:1} }
.annotation-label {
  font-size: 12px; font-weight: bold;
  color: #333; background: rgba(255,255,255,0.8);
  padding: 2px 4px; border-radius: 4px;
}
</style>
</head>
<body>
 <!--?php include "navbar.php"; ?--> 

<div id="loader" style="position:fixed;top:0;left:0;width:100%;height:100%;display:flex;justify-content:center;align-items:center;flex-direction:column;background:#fff;z-index:9999">
  <div class="spinner-border text-primary" style="width:4rem;height:4rem" role="status"></div>
  <p class="mt-3 fw-bold text-secondary">Carregando dados...</p>
</div>

<div class="container mt-4">
  <!-- Cards -->
  <!-- <div class="row mb-4">
     <?php foreach ($tabela as $linha): 
        $dif = $linha["dif"]; // diferença entre atual e 2021, ou você pode calcular dia a dia
        $cor = $dif >= 0 ? 'text-success' : 'text-danger';
        $seta = $dif >= 0 ? '↑' : '↓';
    ?> 
    <div class="col">
      <div class="card style="background: <?= $dif >= 0 ? '#198754aa' : '#dc3545aa' ?>;>
        <div class="card-body text-center">
          <h6 class="card-title"><?= htmlspecialchars($linha["sistema"]) ?></h6>
          <p class="card-text" style="font-size:1em;">
            <?= number_format($linha["vol_atual"],1,',','.') ?> %
          </p>
          <div class="mt-1 <?= $cor ?>" style="font-weight:bold;">
            <?= $seta ?> <?= number_format(abs($dif),1,',','.') ?> %
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div-->

  <!-- Gráficos -->
  <!-- <div class="row">
    <div class="col-lg-9"> -->
      <canvas id="grafSistema"></canvas>

         <div class="legenda-protocolo mt-3">
        <ul>
            <li><span class="cor e1"></span> E1 - Atenção</li>
            <li><span class="cor e2"></span> E2 - Alerta</li>
            <li><span class="cor e3"></span> E3 - Crítico</li>
            <li><span class="cor e4"></span> E4 - Emergência</li>
        </ul>
    </div>
    <!-- </div>
    <div class="col-lg-3">
      <canvas id="graficoPizza" style="margin-top:190px;"></canvas>
    </div>
  </div> -->

  <hr>
<style>
  td{
    text-align:center
  }
</style>
  <!-- Tabela -->
  <!-- <table class="table table-hover mt-3">
    <thead>
      <tr>
        <th>Sistema</th>
        <th>Vol Atual (%)</th>
        <th>Vol 2021 (%)</th>
        <th>Dif (%)</th>
        <th>Chuva Diária (mm)</th>
        <th>Chuva Acum. Mês Atual (mm)</th>
        <th>Chuva Méd. Hist. Atual (mm)</th>
        <th>Chuva 2021(mm)</th>
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
        <td><?= number_format($linha["chuvaMediaHistorica"],1,',','.') ?></td>
        <td><?= number_format($linha["chuva_2021"],1,',','.') ?></td>
        <td><?= number_format($linha["chuvaAcumuladaNoMes_2021"],1,',','.') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table> -->
</div>

<script>
window.addEventListener("load", () => {
  const loader = document.getElementById("loader");
  loader.style.opacity = "0";
  setTimeout(() => loader.style.display = "none", 400);
});

const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
const volAtual = <?= json_encode($volAtual) ?>;
const vol2021 = <?= json_encode($vol2021) ?>;

// --- PESOS FIXOS dos sistemas graficoPizza---
const pesos = {
  "Cantareira": 50.50,
  "Alto Tietê": 28.80,
  "Guarapiranga": 8.80,
  "Rio Grande": 5.80,
  "São Lourenço": 4.60,
  "Rio Claro": 0.70,
  "Cotia": 0.80
};

const pizzaLabels = Object.keys(pesos);
const pizzaData = Object.values(pesos);

new Chart(document.getElementById("grafSistema"), {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [
      { label: "Volume 2021 (%)", data: vol2021, backgroundColor: "#1d73f3" },
      { label: "Volume Atual (%)", data: volAtual, backgroundColor: "#0448a1" }
    ]
  },
  options: {
    interaction: {           // 👇 adiciona interação agrupada
      mode: 'index',
      intersect: false
    },
    plugins: {
      legend: { position: "top" },
      title: { display: true, text: "Acompanhamento do Volume útil - <?= date('d/m/Y') ?>" },
      datalabels: {
        display: true,
        anchor: 'end',
        align: 'end',
        color: '#000',
        font: { weight: 'bold', size: 11 },
        formatter: v => v ? v.toFixed(1) + '%' : ''
      },
      tooltip: {             // 👇 exibe todos os valores juntos
        enabled: true,
        callbacks: {
          label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y.toFixed(1)}%`
        }
      },
      annotation: {
          annotations: {
                    atencao60: { type: 'line', yMin: 60, yMax: 60, borderColor: 'yellow', borderWidth: 2 },
                    critico40: { type: 'line', yMin: 40, yMax: 40, borderColor: 'orange', borderWidth: 2 },
                    critico30: { type: 'line', yMin: 30, yMax: 30, borderColor: 'red', borderWidth: 2 },
                    emergencia: { type: 'line', yMin: 20, yMax: 20, borderColor: 'purple', borderWidth: 2 }
                }
        // annotations: {
        //   emergencia: { type: 'box', yMin: 0, yMax: 10, backgroundColor: 'rgba(255,0,0,0.2)', label: { content: 'Emergência', enabled: true, position: 'start', font: { weight: 'bold' } } },
        //   critico:    { type: 'box', yMin: 10, yMax: 20, backgroundColor: 'rgba(255,165,0,0.2)', label: { content: 'Crítico', enabled: true, position: 'start', font: { weight: 'bold' } } },
        //   alerta:     { type: 'box', yMin: 20, yMax: 40, backgroundColor: 'rgba(255,255,0,0.2)', label: { content: 'Alerta', enabled: true, position: 'start', font: { weight: 'bold' } } },
        //   atencao:    { type: 'box', yMin: 40, yMax: 60, backgroundColor: 'rgba(0,255,0,0.15)', label: { content: 'Atenção', enabled: true, position: 'start', font: { weight: 'bold' } } }
        // }
      }
    },
    scales: { y: { beginAtZero: true, title: { display: true, text: "Volume (%)" } } }
  },
  plugins: [ChartDataLabels]
});

// --- Gráfico de pizza com PESOS fixos ---
new Chart(document.getElementById("graficoPizza"), {
  type: 'doughnut',
  data: {
    labels: pizzaLabels,
    datasets: [{
      data: pizzaData,
      backgroundColor: [
            '#0077cc', '#e0e0e0', '#084391ff', '#0ca73aff', '#726f6fff', '#e00e0eff', '#5e4040ff'
          ],
      borderColor: '#fff',
      borderWidth: 1
    }]
  },
  options: {
    plugins: {
      legend: { position: 'right', labels: { boxWidth: 12, boxHeight: 12, font: { size: 12 } } },
    //   title: { display: true, text: 'Participação no Total da RMSP (%)' },
      tooltip: {
        callbacks: {
          label: c => c.label + ': ' + c.parsed.toFixed(2) + '%'
        }
      }
    }
  }
});
</script>

</body>
</html>
