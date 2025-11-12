<?php  
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

$nomesSistemas = [
  64 => 'Cantareira',
  65 => 'Alto Tietê',
  66 => 'Guarapiranga',
  67 => 'Cotia',
  68 => 'Rio Grande',
  69 => 'Rio Claro',
  72 => 'São Lourenço',
  75 => 'SIM'
];

// ======= FILTRO POR DATA =======
$dataSelecionada = $_GET['data'] ?? date('Y-m-d');
$anoAtual = date('Y', strtotime($dataSelecionada));
$mesAtual = date('m', strtotime($dataSelecionada));
$ano2021 = 2021;
$diasNoMes = date('t', strtotime($dataSelecionada));
function getDadosApi($data) {
  global $cacheDir;
  $cacheFile = "$cacheDir/dados_$data.json";
  $tempoExpira = 1800;

  if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $tempoExpira) {
      return json_decode(file_get_contents($cacheFile), true);
  }

  $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
  $ch = curl_init();
  curl_setopt_array($ch, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 20
  ]);
  $resposta = curl_exec($ch);
  curl_close($ch);

  if ($resposta && ($dados = json_decode($resposta, true))) {
      file_put_contents($cacheFile, $resposta);
      return $dados;
  }
  return null;
}

// ======= ARRAYS =======
$volumeAtual = $volume2021Mes = $chuvaAtualMes = $chuva2021Mes = [];
$vazaoAtualMes = $vazao2021Mes = $vazaoCaptadaAtual = $vazaoCaptadaMin = $vazaoCaptadaMedia = [];

// ======= DADOS ATUAIS =======
$dadosDia = getDadosApi($dataSelecionada);
if (!empty($dadosDia['data'])) {
  foreach ($nomesSistemas as $id => $nome) {
      foreach ($dadosDia['data'] as $s) {
          if ($s['idSistema'] == $id) {
              $volumeAtual[$nome] = number_format(floatval($s['volumeUtilArmazenadoPorcentagem'] ?? 0),1);
              $chuvaAtualMes[$nome] = $nome === 'SIM' ? null : number_format(floatval($s['chuvaAcumuladaNoMes'] ?? 0),1);
              $vazaoAtualMes[$nome] = number_format(floatval($s['vazaoNatural'] ?? 0),1);
              $vazaoCaptadaAtual[$nome] = number_format(floatval($s['vazaoCaptada'] ?? 0),1);
              $vazaoCaptadaMin[$nome] = number_format(floatval($s['vazaoRetiradaNoMes'] ?? 0),1);
              $vazaoCaptadaMedia[$nome] = number_format(floatval($s['vazaoProduzidaNoMes'] ?? 0),1);
              break;
          }
      }
  }
}

// ======= DADOS 2021 =======
for ($dia = 1; $dia <= $diasNoMes; $dia++) {
  $data2021Str = sprintf('%04d-%02d-%02d', $ano2021, $mesAtual, $dia);
  $dados2021 = getDadosApi($data2021Str);
  if (!empty($dados2021['data'])) {
      foreach ($nomesSistemas as $id => $nome) {
          foreach ($dados2021['data'] as $s) {
              if ($s['idSistema'] == $id) {
                  $volume2021Mes[$nome] = number_format(floatval($s['volumeUtilArmazenadoPorcentagem'] ?? 0),1);
                  $chuva2021Mes[$nome] = $nome === 'SIM' ? null : number_format(floatval($s['chuvaAcumuladaNoMes'] ?? 0),1);
                  $vazao2021Mes[$nome] = number_format(floatval($s['vazaoNatural'] ?? 0),1);
                  break;
              }
          }
      }
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Dashboard Hidrológico - <?= date('d/m/Y', strtotime($dataSelecionada)) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- IMPORTAÇÃO DOS PLUGINS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>
</head>
<body>
  <div class="container mt-4">
<div class="row">
<h2 class="mb-4 text-center">Situação Atual</h2>

<form method="get" class="mb-4 d-flex align-items-center">
  <div class="col-lg-2  mb-3  ">
  <label for="dataFiltro" class="me-2 fw-bold">Selecione a Data:</label>
  </div>
  <div class="col-lg-8  mb-3  ">
  <input type="date" id="dataFiltro" name="data" class="form-control me-2" value="<?= htmlspecialchars($dataSelecionada) ?>">
  </div>
  <div class="col-lg-2  mb-3  ">
  <button type="submit" class="me-2  btn btn-primary">Buscar</button>
</form></div>
</div>

<!-- <div class="container text-center">
  <div class="row">
    <div class="col-sm-2">
        <canvas id="graficoPizza"></canvas>
    </div>
    <?php foreach ($volumeAtual as $nome => $vol): ?>
    <div class="col-sm-10">
      
      <div class="row">
        <div class="col">
           <div class="card text-center shadow-sm">
      <div class="card-body">
        <h6 class="card-title fw-bold"><?= htmlspecialchars($nome) ?></h6>
        <p>Vol. Atual: <strong><?= $vol ?>%</strong></p>
      </div>
    </div>
        </div>
      
      </div><?php endforeach; ?>
    </div>
    
  </div>
</div>
</div> -->
<div class="row mb-4">
  <div class="col-sm-3">
      <canvas id="graficoPizza"></canvas>
  </div>
<div class="col-sm-9">

<div class="row">
   <?php foreach ($volumeAtual as $nome => $vol): ?>
  <div class="col-sm-2" style="margin-bottom: 15px;"  style="background: <?= $dif >= 0 ? '#19875433' : '#ffffffff' ?>;">
    <div class="card text-center shadow-sm" >
      <div class="card-body">
        <h6 class="card-title fw-bold"><?= htmlspecialchars($nome) ?></h6>
        <p>Volume: <strong><?= $vol ?>%</strong></p>
        </div>
        </div>
  </div>
  <?php endforeach; ?>
</div>
  </div>
</div>

<!-- <div class="row mb-4">
 <?php foreach ($volumeAtual as $nome => $vol): ?>
<div class="col mb-3">
      
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <h6 class="card-title fw-bold"><?= htmlspecialchars($nome) ?></h6>
        <p>Vol. Atual: <strong><?= $vol ?>%</strong></p>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div> -->

<div class="row">
  <div class="col-lg-6"><h4>Volume (%)</h4><canvas id="grafVolume"></canvas></div>
  <div class="col-lg-6"><h4>Chuva (mm)</h4><canvas id="grafChuva"></canvas></div>
  <div class="col-lg-6"><h4>Vazão Natural (m³/s)</h4><canvas id="grafVazao"></canvas></div>
  <div class="col-lg-6"><h4>Vazão Captada (m³/s)</h4><canvas id="grafVazaoCaptada"></canvas></div>
  <div class="col-8 mt-4"><canvas id="grafico_linha"></canvas></div>
</div>

<script>
Chart.register(ChartDataLabels);
Chart.register(window['chartjs-plugin-annotation']);

const sistemas = <?= json_encode(array_keys($volumeAtual)) ?>;
const volumeAtualJS = <?= json_encode(array_values($volumeAtual)) ?>;
const volume2021JS = <?= json_encode(array_values($volume2021Mes)) ?>;
const chuvaAtualJS = <?= json_encode(array_values(array_filter($chuvaAtualMes, fn($v) => $v !== null))) ?>;
const chuva2021JS = <?= json_encode(array_values(array_filter($chuva2021Mes, fn($v) => $v !== null))) ?>;
const vazaoAtualJS = <?= json_encode(array_values($vazaoAtualMes)) ?>;
const vazao2021JS = <?= json_encode(array_values($vazao2021Mes)) ?>;
const vazaoCaptadaAtualJS = <?= json_encode(array_values($vazaoCaptadaAtual)) ?>;
const vazaoCaptadaMediaJS = <?= json_encode(array_values($vazaoCaptadaMedia)) ?>;

// ============================
// FUNÇÃO BASE
// ============================

const pesos={"Cantareira":50.50,"Alto Tietê":28.80,"Guarapiranga":8.80,"Rio Grande":5.80,"São Lourenço":4.60,"Rio Claro":0.70,"Cotia":0.80};
new Chart(document.getElementById("graficoPizza"),{type:'doughnut',data:{labels:Object.keys(pesos),datasets:[{data:Object.values(pesos),backgroundColor:['#0077cc','#1d73f3','#084391','#0ca73a','#726f6f','#e00e0e','#5e4040'],borderColor:'#fff',borderWidth:1}]},options:{plugins:{tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${ctx.parsed.toFixed(2)}%`}},legend:{position:'right',labels:{boxWidth:12,boxHeight:12,font:{size:12}}}}}});

function criarGraficoBase(ctx, titulo, labels, datasets, unidade, opcoesExtras = {}) {
  new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top' },
        title: { display: true, text: titulo },
        datalabels: {
          display: true,
          color: '#000',
          anchor: 'end',
          align: 'top',
          formatter: v => parseFloat(v).toFixed(1)
        },
        ...opcoesExtras.plugins
      },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: unidade } }
      }
    },
    plugins: [ChartDataLabels]
  });
}

// ============================
// GRÁFICO DE VOLUME COM ESTÁGIOS
// ============================
function graficoVolume() {
  const ctx = document.getElementById('grafVolume');
  const datasets = [
    { label: 'Atual', data: volumeAtualJS, backgroundColor: '#007bff' },
    { label: 'Mesmo Mês 2021', data: volume2021JS, backgroundColor: '#ff8800' }
  ];

  const estagios = {
    annotation: {
      annotations: {
        atencao60: { type: 'line', yMin: 60, yMax: 60, borderColor: 'yellow', borderWidth: 2, label: { content: 'Atenção 60%', enabled: true, backgroundColor: 'rgba(255,255,0,0.2)', color: '#000' } },
        alerta40: { type: 'line', yMin: 40, yMax: 40, borderColor: 'orange', borderWidth: 2, label: { content: 'Alerta 40%', enabled: true, backgroundColor: 'rgba(255,165,0,0.2)', color: '#000' } },
        critico30: { type: 'line', yMin: 30, yMax: 30, borderColor: 'red', borderWidth: 2, label: { content: 'Crítico 30%', enabled: true, backgroundColor: 'rgba(255,0,0,0.2)', color: '#fff' } },
        emergencia20: { type: 'line', yMin: 20, yMax: 20, borderColor: 'purple', borderWidth: 2, label: { content: 'Emergência 20%', enabled: true, backgroundColor: 'rgba(128,0,128,0.2)', color: '#fff' } }
      }
    }
  };

  criarGraficoBase(ctx, 'Volume Útil (%)', sistemas, datasets, '%', { plugins: estagios });
}

// ============================
// GRÁFICO DE CHUVA
// ============================
function graficoChuva() {
  const ctx = document.getElementById('grafChuva');
  const datasets = [
    { label: 'Atual', data: chuvaAtualJS, backgroundColor: '#4da6ff' },
    { label: 'Mesmo Mês 2021', data: chuva2021JS, backgroundColor: '#ffa64d' }
  ];
  criarGraficoBase(ctx, 'Chuva Acumulada (mm)', sistemas.filter(s => s !== 'SIM'), datasets, 'mm');
}

// ============================
// GRÁFICO DE VAZÃO NATURAL
// ============================
function graficoVazaoNatural() {
  const ctx = document.getElementById('grafVazao');
  const datasets = [
    { label: 'Atual', data: vazaoAtualJS, backgroundColor: '#009933' },
    { label: 'Mesmo Mês 2021', data: vazao2021JS, backgroundColor: '#cc6600' }
  ];
  criarGraficoBase(ctx, 'Vazão Natural (m³/s)', sistemas, datasets, 'm³/s');
}

// ============================
// GRÁFICO DE VAZÃO CAPTADA
// ============================
function graficoVazaoCaptada() {
  const ctx = document.getElementById('grafVazaoCaptada');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: sistemas,
      datasets: [
        { label: 'Atual', data: vazaoCaptadaAtualJS.map(v => parseFloat(v)), backgroundColor: '#007bff' },
        { label: 'Média', data: vazaoCaptadaMediaJS.map(v => parseFloat(v)), backgroundColor: '#28a745' }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top' },
        title: { display: true, text: 'Vazão Captada (m³/s)' },
        datalabels: {
          display: true,
          color: '#000',
          anchor: 'end',
          align: 'top',
          formatter: v => parseFloat(v).toFixed(1)
        }
      },
      scales: { y: { beginAtZero: true, title: { display: true, text: 'm³/s' } } }
    },
    plugins: [ChartDataLabels]
  });
}

// ============================
// GRÁFICO DE PROJEÇÕES (tooltips com todos os dados)
// ============================
async function graficoProjecoes() {
  try {
    const resposta = await fetch("serie_diaria.csv");
    const texto = await resposta.text();
    const linhas = texto.split("\n").map(l => l.trim()).filter(l => l.length);
    const cabecalho = linhas[0].split(",");
    const dados = linhas.slice(1).map(l => l.split(","));
    const labels = dados.map(l => l[0]);

    // Paleta de cores personalizada
    const cores = {
      "QN 100 MLT": "#003366",
      "QN 70 MLT": "#ff3333",
      "QN (20/25)": "#00b359",
      "QN (2021)": "#0099cc",
      "QN (2014)": "#cc6600",
      "QN (2020)": "#9933cc",
      "Real": "#000000"
    };

    // Criação dos datasets
    const datasets = cabecalho.slice(1).map((nome, idx) => ({
      label: nome,
      data: dados.map(l => parseFloat(l[idx + 1]) || null),
      borderColor: cores[nome] || `hsl(${Math.random() * 360}, 50%, 40%)`,
      borderWidth: nome === "Real" ? 4 : 2,
      tension: 0.3, // suavização das linhas
      pointRadius: nome === "Real" ? 3 : 0,
      fill: false
    }));

    // Criação do gráfico
    new Chart(document.getElementById("grafico_linha"), {
      type: "line",
      data: { labels, datasets },
      options: {
        responsive: true,
        interaction: {
          mode: "index", // mostra todos os datasets do mesmo índice
          intersect: false
        },
        plugins: {
          title: {
            display: true,
            text: "Projeções de Volume – Séries Diárias",
            font: { size: 18 }
          },
          legend: {
            position: "top",
            labels: { usePointStyle: true, pointStyle: "line" }
          },
          tooltip: {
            mode: "index",
            intersect: false,
            callbacks: {
              label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y?.toFixed(1)}%`
            }
          },
          datalabels: { display: false } // sem rótulos sobre os pontos
        },
        scales: {
        //   x: {
        //     title: { display: true, text: "Data" },
        //     ticks: {
        //       maxTicksLimit: 10,
        //       callback: (val, idx) => (idx % 5 === 0 ? labels[idx] : "")
        //     }
        //   },
        
          y: {
            title: { display: true, text: "Volume (%)" },
            beginAtZero: false,
            suggestedMin: 25,
            suggestedMax: 45,
            ticks: { stepSize: 10 }
          }
        }
      }
    });
  } catch (e) {
    console.error("Erro ao carregar o gráfico de projeções:", e);
  }
}

// ============================
// CHAMADAS DOS GRÁFICOS
// ============================
graficoVolume();
graficoChuva();
graficoVazaoNatural();
graficoVazaoCaptada();
graficoProjecoes();


</script>

</body>
</html>
