<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');
$dataSelecionada = $_GET['data'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Dashboard Hidrológico Integrado</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>
<style>
  body { background:#f7f9fb; }
  .tab-content { background:white; border-radius:8px; padding:20px; }
</style>
</head>
<body>

<div class="container mt-4">
  <h2 class="text-center mb-4">Dashboard Hidrológico Integrado</h2>

  <!-- Filtro de Data -->
  <form method="get" class="mb-4 d-flex align-items-center justify-content-center">
    <label for="data" class="fw-bold me-2">Selecione a data:</label>
    <input type="date" id="data" name="data" class="form-control w-auto me-2" value="<?= htmlspecialchars($dataSelecionada) ?>">
    <button class="btn btn-primary">Atualizar</button>
  </form>

  <!-- Abas -->
  <ul class="nav nav-tabs" id="abas" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabVolume">Volumes</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabVazao">Vazões</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabChuva">Precipitação</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabProjecao">Projeções</button></li>
  </ul>

  <!-- Conteúdo das Abas -->
  <div class="tab-content mt-3">
    <div class="tab-pane fade show active" id="tabVolume">
      <div id="grafSistema">dd</div>
    </div>
    <div class="tab-pane fade" id="tabVazao">
      <div id="graficoVazao">Carregando gráfico de vazão...</div>
    </div>
    <div class="tab-pane fade" id="tabChuva">
      <div id="graficoChuva">Carregando gráfico de chuva...</div>
    </div>
    <div class="tab-pane fade" id="tabProjecao">
      <div><canvas id="graficoProjecao"></canvas></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
Chart.register(ChartDataLabels);
Chart.register(window['chartjs-plugin-annotation']);

const dataSelecionada = '<?= $dataSelecionada ?>';

// ============ Função para carregar conteúdo via AJAX =============
async function carregarGrafico(endpoint, destino) {
  const res = await fetch(endpoint + '?data=' + dataSelecionada);
  const html = await res.text();
  document.getElementById(destino).innerHTML = html;
}

// ============ Carregar cada gráfico em sua aba =============
carregarGrafico('precipitacao.php', 'grafSistema');
carregarGrafico('vazoes-com-filtro.php', 'graficoVazao');
carregarGrafico('precipitacao.php', 'graficoChuva');

// ============ Projeções (exemplo direto no index) =============
async function criarGraficoProjecao() {
  const resposta = await fetch("serie_diaria.csv");
  const texto = await resposta.text();
  const linhas = texto.trim().split("\n");
  const cabecalho = linhas[0].split(",");
  const dados = linhas.slice(1).map(l => l.split(","));
  dados.sort((a,b) => new Date(a[0]) - new Date(b[0]));

  const labels = dados.map(l => new Date(l[0]).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }));
  const cores = {
    "QN 100 MLT": "#021d58", "QN 70 MLT": "#cc0505", "QN (20/25)": "#046e1f",
    "QN (2021)": "#0cb130", "QN (2014)": "#df5c11ff", "QN (2020)": "#df11b2ff", "Real": "#000000"
  };

  const datasets = cabecalho.slice(1).map((nome, idx) => ({
    label: nome,
    data: dados.map(l => parseFloat(l[idx + 1]) || null),
    borderColor: cores[nome] || `hsl(${Math.random()*360},60%,40%)`,
    borderWidth: nome === "Real" ? 3 : 2,
    fill: false,
    tension: 0.2,
    pointRadius: 0
  }));

  new Chart(document.getElementById('graficoProjecao'), {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top' },
        title: { display: true, text: 'Projeção de Volume (%)' },
        tooltip: { mode: 'index', intersect: false },
        datalabels: { display: false }
      },
      scales: {
        x: {
          title: { display: true, text: 'Data' },
          ticks: { autoSkip: true, maxTicksLimit: 20, maxRotation: 45, minRotation: 45 }
        },
        y: { title: { display: true, text: 'Volume (%)' }, min: 20, max: 100, ticks: { stepSize: 10 } }
      }
    }
  });
}

criarGraficoProjecao();
</script>
</body>
</html>
