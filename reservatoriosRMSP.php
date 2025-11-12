<?php
header("Content-Type: text/html; charset=utf-8");

// =============================
// 🔄 BUSCAR DADOS DA API ATUAL
// =============================
$dataAtual = date("Y-m-d");
$urlAtual = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$dataAtual";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $urlAtual);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$respostaAtual = curl_exec($ch);
curl_close($ch);

$dadosAtual = json_decode($respostaAtual, true);

// =============================
// 🔄 BUSCAR DADOS DE 2021 AUTOMATICAMENTE
// =============================
$data2021 = "2021-" . date("m-d");
$url2021 = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data2021";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url2021);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$resposta2021 = curl_exec($ch);
curl_close($ch);

$dados2021 = json_decode($resposta2021, true);

if (!$dadosAtual || !isset($dadosAtual["data"])) {
    die("<p>❌ Erro: não foi possível obter dados atuais da API.</p>");
}
if (!$dados2021 || !isset($dados2021["data"])) {
    die("<p>❌ Erro: não foi possível obter dados de 2021.</p>");
}
// =============================
//  MAPA DE NOMES DOS SISTEMAS
// =============================
$nomesSistemas = [
    64 => "Cantareira",
    65 => "Alto Tietê",
    66 => "Guarapiranga",
    67 => "Cotia",
    68 => "Rio Grande",
    69 => "Rio Claro",
    72 => "São Lourenço",
    75 => "SIM",
    // 74 => "Cantareira Velho" — 🔹 REMOVIDO
];

// =============================
// REMOVER “CANTAREIRA VELHO” (id 74)
// =============================
$sistemasAtual = array_filter($dadosAtual["data"], fn($s) => $s["idSistema"] != 74);
$sistemas2021 = array_filter($dados2021["data"], fn($s) => $s["idSistema"] != 74);

$labels = [];
// =============================
// 📊 MONTAR ARRAY $reservatorios
// =============================
$reservatorios = [];
foreach ($sistemasAtual as $s) {
    $idSistema = $s["idSistema"];
    $nome = $nomesSistemas[$idSistema] ?? "Sistema $idSistema";
    $match = array_values(array_filter($sistemas2021, fn($x) => $x["idSistema"] === $idSistema));
    $s2021 = $match[0] ?? [];

    $reservatorios[] = [
        "idSistema" => $idSistema,
        "atual" => round($s["volumeUtilArmazenadoPorcentagem"], 1),
        "mesano2021" => round($s2021["volumeUtilArmazenadoPorcentagem"] ?? 0, 1),
        "chuva" => round($s["chuva"], 1),
        "acumulado_mes" => round($s["chuvaAcumuladaNoMes"], 1),
        "media_historica" => round($s["chuvaMediaHistorica"], 1)
    ];
  $labels[] = $nome;
    $volumesAtual[] = $volAtual;
    $volumes2021[] = $vol2021;
    $difVol[] = $diferenca;

    $tabela[] = [
        "sistema" => $nome,
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gráfico de Reservatórios - SABESP API</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.4.0"></script>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #e9e9e9ff; padding: 8px; text-align: center; }
th { background: #f0f0f0; }
.box { background: #f0f0f0; }
h4, h6 { font-weight: bold; }
.legenda-protocolo ul {
  list-style: none; padding: 0; margin: 10px auto;
  display: flex; justify-content: center; gap: 20px;
}
.legenda-protocolo li { font-size: 12px; display: flex; align-items: center; gap: 8px; }
.legenda-protocolo .cor { width: 30px; height: 15px; }
.e1 { background: yellow; } .e2 { background: orange; }
.e3 { background: red; } .e4 { background: purple; }
</style>
</head>
<body>

<div class="container">
<h4 class="text-center">Sistemas Produtores da RMSP</h4>
<div class="atualizacao">Atualizado em: <?= date("d/m/Y") ?></div> 

<div class="row">
<?php foreach ($reservatorios as $r): 
    $diff = $r["atual"] - $r["mesano2021"];
    $seta = $diff > 0 ? '<span style="color:green;">&#9650;</span>' :
            ($diff < 0 ? '<span style="color:red;">&#9660;</span>' :
            '<span style="color:black;">&#8212;</span>');
?>
  <div class="col">
    <div class="card box">
      <div class="card-body text-center">
        <h6><?= $r["idSistema"] ?></h6>
        <h6><?= $r["atual"] ?>%</h6>
        <p><strong>(<?= $seta ?> <?= number_format($diff, 1, ',', '.') ?>%)</strong></p>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="row">
  <div class="col-lg-10">
    <canvas id="grafico_barra"></canvas>
    <div class="legenda-protocolo mt-3">
      <ul>
        <li><span class="cor e1"></span> E1 - Atenção</li>
        <li><span class="cor e2"></span> E2 - Alerta</li>
        <li><span class="cor e3"></span> E3 - Crítico</li>
        <li><span class="cor e4"></span> E4 - Emergência</li>
      </ul>
    </div>
  </div>
  <div class="col-lg-2">
    <div class="pie" style="margin-top:190px;">
      <h5 class="text-center">Volume total</h5>
      <canvas id="graficoPizza"></canvas>
    </div>
  </div>
</div>

<!-- Tabela -->
<div class="col-lg-12 mt-4">
  <table class="table table-hover">
    <tr class="table-light">
      <th>Sistema</th>
      <th>Volume Atual (%)</th>
      <th>Volume Ano 2021 (%)</th>
      <th>Diferença (%)</th>
      <th>Chuva (mm)</th>
      <th>Acumulado no Mês (mm)</th>
      <th>Média Histórica (mm)</th>
    </tr>
    <?php foreach ($reservatorios as $r): ?>
    <tr>
      <td><?= $r["sistema"] ?></td>
      <td><?= $r["atual"] ?></td>
      <td><?= $r["mesano2021"] ?></td>
      <td><?= round($r["atual"] - $r["mesano2021"], 1) ?></td>
      <td><?= $r["chuva"] ?></td>
      <td><?= $r["acumulado_mes"] ?></td>
      <td><?= $r["media_historica"] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
</div>

<script>
const nomes = <?= json_encode(array_column($reservatorios, "idSistema"), JSON_UNESCAPED_UNICODE); ?>;
const atual = <?= json_encode(array_column($reservatorios, "atual")); ?>;
const ano2021 = <?= json_encode(array_column($reservatorios, "mesano2021")); ?>;

new Chart(document.getElementById("grafico_barra"), {
  type: "bar",
  data: {
    labels: nomes,
    datasets: [
      {
        label: "Volume 2021 (%)",
        data: ano2021,
        backgroundColor: "rgba(4, 18, 206, 0.7)"
      },
      {
        label: "Volume Atual (%)",
        data: atual,
        backgroundColor: "rgba(32, 149, 228, 0.7)"
      }
    ]
  },
  options: {
    plugins: {
      legend: { position: "bottom" },
      datalabels: {
        anchor: "end",         // posição da etiqueta (em cima)
        align: "end",
        color: "#000",         // cor do texto
        font: { weight: "bold" },
        formatter: (value) => value + "%" // mostra o valor + símbolo %
      },
      annotation: {
        annotations: {
          e1: { type: "line", yMin: 60, yMax: 60, borderColor: "yellow", borderWidth: 2 },
          e2: { type: "line", yMin: 40, yMax: 40, borderColor: "orange", borderWidth: 2 },
          e3: { type: "line", yMin: 30, yMax: 30, borderColor: "red", borderWidth: 2 },
          e4: { type: "line", yMin: 20, yMax: 20, borderColor: "purple", borderWidth: 2 }
        }
      }
    },
    scales: { y: { beginAtZero: true } }
  },
  plugins: [ChartDataLabels] // 🔹 Ativa o plugin DataLabels
});
</script>
</body>
</html>
