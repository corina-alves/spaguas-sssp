<?php
header("Content-Type: text/html; charset=utf-8");

// =====================================
// CONFIGURAÇÕES INICIAIS
// =====================================
date_default_timezone_set("America/Sao_Paulo");
$dataAtual = date("Y-m-d");

// API SABESP
$API_URL = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/";
$API_KEY = "x-api-key: 6D3BCD8A-8B65-4D1C-B2C7-F5E41912D7E7"; // substitua pela sua chave válida

// =====================================
// FUNÇÃO PARA BUSCAR DADOS DA API
// =====================================
function buscarDadosAPI($data) {
    global $API_URL, $API_KEY;
    $url = $API_URL . $data;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$API_KEY]);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// =====================================
// FUNÇÃO PARA CALCULAR MÉDIA DE 7 E 14 DIAS
// =====================================
function calcularMediaDias($dias) {
    global $dataAtual;

    $dadosSomados = [];
    $contagens = [];

    for ($i = 0; $i < $dias; $i++) {
        $dataBusca = date("Y-m-d", strtotime("-$i days", strtotime($dataAtual)));
        $dadosDia = buscarDadosAPI($dataBusca);

        if (!empty($dadosDia)) {
            foreach ($dadosDia as $item) {
                $nome = $item["Sistema"];
                $chuva = floatval(str_replace(",", ".", $item["Chuva"]));
                $vazao = floatval(str_replace(",", ".", $item["VazaoNatural"]));

                if (!isset($dadosSomados[$nome])) {
                    $dadosSomados[$nome] = ["chuva" => 0, "vazao" => 0];
                    $contagens[$nome] = 0;
                }

                $dadosSomados[$nome]["chuva"] += $chuva;
                $dadosSomados[$nome]["vazao"] += $vazao;
                $contagens[$nome]++;
            }
        }
    }

    $medias = [];
    foreach ($dadosSomados as $sistema => $valores) {
        $medias[$sistema] = [
            "chuva" => $valores["chuva"] / $contagens[$sistema],
            "vazao" => $valores["vazao"] / $contagens[$sistema]
        ];
    }

    return $medias;
}

// =====================================
// BUSCAR MÉDIAS DOS ÚLTIMOS 7 E 14 DIAS
// =====================================
$media7d = calcularMediaDias(7);
$media14d = calcularMediaDias(14);

// =====================================
// FORMATAR DADOS PARA GRÁFICO E TABELA
// =====================================
$labels = array_keys($media7d);
$chuva7d = array_column($media7d, "chuva");
$vazao7d = array_column($media7d, "vazao");

$chuva14d = array_column($media14d, "chuva");
$vazao14d = array_column($media14d, "vazao");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Monitoramento - Chuva e Vazão Natural</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">

  <h3 class="text-center mb-4">Boletim de Monitoramento - Chuva e Vazão Natural</h3>

  <!-- ============================= -->
  <!-- ABAS -->
  <!-- ============================= -->
  <ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="7d-tab" data-bs-toggle="tab" data-bs-target="#tab7d" type="button" role="tab">Últimos 7 dias</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="14d-tab" data-bs-toggle="tab" data-bs-target="#tab14d" type="button" role="tab">Últimos 14 dias</button>
    </li>
  </ul>

  <div class="tab-content p-3 border border-top-0 bg-white" id="myTabContent">

    <!-- ABA 7 DIAS -->
    <div class="tab-pane fade show active" id="tab7d" role="tabpanel">
      <h5 class="mb-3 text-primary">Médias dos últimos 7 dias</h5>
      <table class="table table-striped table-sm text-center align-middle">
        <thead class="table-primary">
          <tr>
            <th>Sistema</th>
            <th>Chuva Média (mm)</th>
            <th>Vazão Média (m³/s)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($media7d as $sistema => $valores): ?>
          <tr>
            <td><?= htmlspecialchars($sistema) ?></td>
            <td><?= number_format($valores["chuva"], 1, ",", ".") ?></td>
            <td><?= number_format($valores["vazao"], 1, ",", ".") ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <canvas id="grafico7dias" height="120"></canvas>
    </div>

    <!-- ABA 14 DIAS -->
    <div class="tab-pane fade" id="tab14d" role="tabpanel">
      <h5 class="mb-3 text-warning">Médias dos últimos 14 dias</h5>
      <table class="table table-striped table-sm text-center align-middle">
        <thead class="table-warning">
          <tr>
            <th>Sistema</th>
            <th>Chuva Média (mm)</th>
            <th>Vazão Média (m³/s)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($media14d as $sistema => $valores): ?>
          <tr>
            <td><?= htmlspecialchars($sistema) ?></td>
            <td><?= number_format($valores["chuva"], 1, ",", ".") ?></td>
            <td><?= number_format($valores["vazao"], 1, ",", ".") ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <canvas id="grafico14dias" height="120"></canvas>
    </div>

  </div>
</div>

<!-- ============================= -->
<!-- GRÁFICOS CHART.JS -->
<!-- ============================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
  const chuva7d = <?= json_encode($chuva7d); ?>;
  const vazao7d = <?= json_encode($vazao7d); ?>;
  const chuva14d = <?= json_encode($chuva14d); ?>;
  const vazao14d = <?= json_encode($vazao14d); ?>;

  // ===== GRÁFICO 7 DIAS =====
  new Chart(document.getElementById("grafico7dias"), {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Chuva Média (mm)",
          data: chuva7d,
          borderColor: "rgba(0, 123, 255, 1)",
          borderWidth: 2,
          fill: false,
          tension: 0.3
        },
        {
          label: "Vazão Média (m³/s)",
          data: vazao7d,
          borderColor: "rgba(40, 167, 69, 1)",
          borderWidth: 2,
          fill: false,
          tension: 0.3
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "top" },
        title: { display: true, text: "Médias dos Últimos 7 Dias" }
      },
      scales: { y: { beginAtZero: true } }
    }
  });

  // ===== GRÁFICO 14 DIAS =====
  new Chart(document.getElementById("grafico14dias"), {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Chuva Média (mm)",
          data: chuva14d,
          borderColor: "rgba(255, 193, 7, 1)",
          borderWidth: 2,
          fill: false,
          tension: 0.3
        },
        {
          label: "Vazão Média (m³/s)",
          data: vazao14d,
          borderColor: "rgba(220, 53, 69, 1)",
          borderWidth: 2,
          fill: false,
          tension: 0.3
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "top" },
        title: { display: true, text: "Médias dos Últimos 14 Dias" }
      },
      scales: { y: { beginAtZero: true } }
    }
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
