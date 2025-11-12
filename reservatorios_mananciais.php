<!-- 
<!--?php
header("Content-Type: application/json");

$url = "https://ssdapi.sabesp.com.br/api/ssd/represas";
$apiKey = "6D3BCD8A-8B65-4D1C-B2C7-F5E41912D7E7"; // sua chave

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "x-api-key: $apiKey"
]);

$resposta = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["erro" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);
echo $resposta;
?-->
<!--!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Histórico - Volume Útil das Represas</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    #grafico { max-width: 1000px; margin: auto; }
  </style>
</head>
<body>
  <h1>Evolução Diária das Represas</h1>
  <canvas id="grafico"></canvas>

  <script>
    async function carregarGrafico() {
      try {
        const resposta = await fetch("represas_historico.php");
        const dados = await resposta.json();

        if (!dados.data || dados.data.length === 0) {
          alert("Nenhum histórico encontrado.");
          return;
        }

        // exemplo genérico: ajustar conforme campos reais
        const datas = dados.data.map(item => item.data); 
        const cantareira = dados.data.map(item => item.cantareira); 
        const altoTietê = dados.data.map(item => item.altoTiete);

        const ctx = document.getElementById('grafico').getContext('2d');
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: datas,
            datasets: [
              {
                label: 'Cantareira',
                data: cantareira,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderWidth: 2,
                tension: 0.3
              },
              {
                label: 'Alto Tietê',
                data: altoTietê,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderWidth: 2,
                tension: 0.3
              }
            ]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: 'top' }
            },
            scales: {
              y: {
                beginAtZero: true,
                max: 100,
                title: { display: true, text: "Volume Útil (%)" }
              },
              x: {
                title: { display: true, text: "Data" }
              }
            }
          }
        });
      } catch (erro) {
        console.error("Erro ao buscar histórico:", erro);
      }
    }

    carregarGrafico();
  </script>
</body>
</html>
 -->

<?php
// Proxy simples para evitar CORS
if (isset($_GET['api'])) {
    header("Content-Type: application/json");
    echo file_get_contents("https://mananciais.sabesp.com.br/api/Mananciais/ResumoSistemas/2025-09-08");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reservatórios RMSP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #e9e9e9ff; padding: 8px; text-align: center; }
    th { background: #f0f0f0; }
    .box { background:#f0f0f0; }
    h6 { font-weight: bold; }
    h4 { font-weight: bold; }
  </style>
</head>
<body>
<div class="container">
  <h4 class="text-center">Sistema Produtores da RMSP</h4>
  <div class="atualizacao">Atualizado em: <span id="data"></span></div>

  <div class="row" id="cards"></div>
  <canvas id="grafico_barra"></canvas>

  <div class="col-lg-12 mt-4">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>Sistema</th>
          <th>Volume Atual (%)</th>
          <th>Volume Ano Anterior (%)</th>
          <th>Diferença (%)</th>
          <th>Chuva (mm)</th>
          <th>Acumulado no Mês (mm)</th>
          <th>Média Histórica (mm)</th>
        </tr>
      </thead>
      <tbody id="tabela"></tbody>
    </table>
  </div>
</div>

<script>
document.getElementById("data").textContent = new Date().toLocaleDateString("pt-BR");

async function carregarReservatorios() {
  const url = "reservatorios_mananciais.php?api=1"; // proxy em PHP
  const resp = await fetch(url);
  const reservatorios = await resp.json();

  console.log("DEBUG dados da API:", reservatorios); // 🔍 debug

  // ==== Preencher cards ====
  const cards = document.getElementById("cards");
  cards.innerHTML = "";
  reservatorios.forEach(r => {
    cards.innerHTML += `
      <div class="col">
        <div class="card box">
          <div class="card-body">
            <h6 class="text-center">${r.nome}</h6>
            <h6 class="text-center">${r.volumePercentualAtual}%</h6>
          </div>
        </div>
      </div>`;
  });

  // ==== Preencher tabela ====
  const tbody = document.getElementById("tabela");
  tbody.innerHTML = "";
  reservatorios.forEach(r => {
    const diff = (r.volumePercentualAtual - r.volumePercentualAnterior).toFixed(2);
    tbody.innerHTML += `
      <tr>
        <td>${r.nome}</td>
        <td>${r.volumePercentualAtual}</td>
        <td>${r.volumePercentualAnterior}</td>
        <td>${diff}</td>
        <td>${r.pluvioDia}</td>
        <td>${r.pluvioAcumulado}</td>
        <td>${r.pluvioMedia}</td>
      </tr>`;
  });

  // ==== Gráfico ====
  const ctx = document.getElementById('grafico_barra');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: reservatorios.map(r => r.nome),
      datasets: [
        {
          label: 'Volume Atual (%)',
          data: reservatorios.map(r => r.volumePercentualAtual),
          backgroundColor: 'rgba(32, 149, 228, 0.7)'
        },
        {
          label: 'Volume Ano Anterior (%)',
          data: reservatorios.map(r => r.volumePercentualAnterior),
          backgroundColor: 'rgba(4, 18, 206, 0.7)'
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', align: 'left' },
        datalabels: {
          anchor: 'end',
          align: 'end',
          color: '#000',
          font: { weight: 'bold' },
          formatter: value => value + '%'
        }
      },
      scales: { y: { beginAtZero: true } }
    },
    plugins: [ChartDataLabels]
  });
}

carregarReservatorios();
</script>
</body>
</html>
