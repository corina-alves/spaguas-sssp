<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

// ======================
// CONFIGURAÇÕES
// ======================
$anoRef = 2021;
$cacheFile = __DIR__ . "/cache_vazao.json";
$cacheTime = 100; // 1h de cache

$sistemas = [
    "Cantareira" => 64,
    "Alto Tietê" => 65,
    "Guarapiranga" => 66,
    "Cotia" => 67,
    "Rio Grande" => 68,
    "Rio Claro" => 69,
    "São Lourenço" => 72,
];
$idSim = 75; // Sistema Integrado Metropolitano (SIM)

// ======================
// FUNÇÕES
// ======================
function get_api(DateTime $data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/" . $data->format('Y-m-d');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    // 🔹 Proxy (ative se necessário)
    // curl_setopt($ch, CURLOPT_PROXY, "10.200.12.140:80");

    $r = curl_exec($ch);
    curl_close($ch);

    if (!$r) return null;
    $data = json_decode($r, true);
    return $data["data"] ?? null;
}

function getCampo($dados, $id, $campo) {
    foreach ($dados as $d) {
        if ($d["idSistema"] == $id) return $d[$campo] ?? null;
    }
    return null;
}

// ======================
// CACHE
// ======================
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $dados = json_decode(file_get_contents($cacheFile), true);
} else {
    $dataBase = new DateTime();
    $anoAtual = $dataBase->format('Y');

    // últimos 7 dias
    $dias = [];
    for ($i = 0; $i < 7; $i++) $dias[] = (clone $dataBase)->modify("-$i day");

    // coleta diária
    $vazoes = [];
    foreach (array_merge($sistemas, ["SIM" => $idSim]) as $nome => $id) $vazoes[$nome] = [];

    foreach ($dias as $d) {
        $resp = get_api($d);
        if (!$resp) continue;
        $map = [];
        foreach ($resp as $i) $map[$i["idSistema"]] = $i;
        foreach (array_merge($sistemas, ["SIM" => $idSim]) as $nome => $id)
            $vazoes[$nome][] = $map[$id]["vazaoNatural"] ?? null;
    }

    // dados atuais e referência
    $dadosBase = get_api($dataBase);
    $ultimoDiaRef = cal_days_in_month(CAL_GREGORIAN, $dataBase->format("m"), $anoRef);
    $dataRef = "$anoRef-" . $dataBase->format("m") . "-$ultimoDiaRef";
    $dadosRef = get_api(new DateTime($dataRef));

    // montar tabela
    $dados = [];
    foreach (array_merge($sistemas, ["SIM" => $idSim]) as $nome => $id) {
        $vazaoDia = getCampo($dadosBase, $id, "vazaoNatural");
        $vals7d = array_filter($vazoes[$nome]);
        $vazao7d = count($vals7d) ? array_sum($vals7d) / count($vals7d) : null;
        $vazaoMes = getCampo($dadosBase, $id, "vazaoNaturalNoMes");
        $vazaoMesRef = getCampo($dadosRef, $id, "vazaoNaturalNoMes");
        $vazaoMediaHist = getCampo($dadosBase, $id, "vazaoNaturalMediaHistorica");

        $dados[] = [
            "Sistema" => $nome,
            "Vazão natural do dia (m³/s)" => round($vazaoDia, 1),
            "Vazão natural últimos 7 dias (m³/s)" => round($vazao7d, 1),
            "Vazão natural no mês $anoAtual (m³/s)" => round($vazaoMes, 1),
            "Vazão natural no mês $anoRef (m³/s)" => round($vazaoMesRef, 1),
            "Vazão natural média climatológica (m³/s)" => round($vazaoMediaHist, 1)
        ];
    }

    file_put_contents($cacheFile, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Acompanhamento das Vazões Naturais dos Sistemas Produtores</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {font-family: Arial, Helvetica, sans-serif; padding:20px; background:#fafafa;}
table {border-collapse: collapse; width:100%; max-width:1100px; margin-bottom:30px; font-size:14px;}
th, td {border:1px solid #ddd; padding:8px; text-align:center;}
th {background:#0b6a4a; color:white;}
tr:last-child {font-weight:bold; background:#e9f9ee;}
/* === Tela de carregamento === */
#telaCarregando {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.9);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #333;
  transition: opacity 0.5s ease;
}
.spinner {
  border: 6px solid #ddd;
  border-top: 6px solid #0074D9;
  border-radius: 50%;
  width: 50px; height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
</head>
<body>

<?php include_once "navbar.php"; ?>

<div class="container">
<div class="card">
  <h2 style="text-align:center; margin-top:30px;">Acompanhamento de <strong> Vazões </strong>Naturais dos Sistemas Produtores (m³/s)</h2>
  <h4 style="text-align:center; margin-bottom:30px;"><?= date('d/m/Y') ?> | Ano de referência: <?= $anoRef ?></h4>

  <div id="telaCarregando">
    <div class="spinner"></div>
    <div>Carregando dados...</div>
  </div>

  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Sistema</th>
        <th>Vazão Natural (m³/s)</th>
        <th>Vazão últimos 7 dias (m³/s)</th>
        <th>Vazão Natural Mês (m³/s)</th>
        <th>Vazão 2021 (m³/s)</th>
        <th>Vazão Média Histórica (m³/s)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($dados as $linha): ?>
        <tr>
          <?php foreach($linha as $val) echo "<td>".htmlspecialchars($val)."</td>"; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <canvas id="grafico" style="margin-top:30px;"></canvas>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  document.getElementById('telaCarregando').style.display = 'flex';
});
window.addEventListener("load", () => {
  const tela = document.getElementById('telaCarregando');
  tela.style.opacity = '0';
  setTimeout(() => tela.style.display = 'none', 500);
});

const dados = <?= json_encode($dados, JSON_UNESCAPED_UNICODE) ?>;
const labels = dados.map(d => d["Sistema"]);
const vazaoDia = dados.map(d => d["Vazão natural do dia (m³/s)"]);
const vazao7d = dados.map(d => d["Vazão natural últimos 7 dias (m³/s)"]);
const vazaoMes = dados.map(d => d["Vazão natural no mês <?= date('Y') ?> (m³/s)"]);
const vazaoMesRef = dados.map(d => d["Vazão natural no mês <?= $anoRef ?> (m³/s)"]);
const vazaoClima = dados.map(d => d["Vazão natural média climatológica (m³/s)"]);

new Chart(document.getElementById("grafico"), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      { label: "Vazão do dia (<?= date('d/m/Y')?>)", data: vazaoDia, backgroundColor:"#1f77b4" },
      { label: "Média últimos 7 dias", data: vazao7d, backgroundColor:"#7fb3d5" },
      { label: "Média mês <?= date('m/Y')?>", data: vazaoMes, backgroundColor:"#227542" },
      { label: "Média mês <?= $anoRef ?>", data: vazaoMesRef, backgroundColor:"#ffbb78" },
      { label: "Média Histórica", data: vazaoClima, backgroundColor:"#ff7f0e" }
    ]
  },
options: {
    responsive: true,
    scales: {
        y: {
            beginAtZero: true,
            title: { display: true, text: 'm³/s' }
        }
    },
    plugins: {
        legend: { position: 'top' },
        title: { display: true, text: 'Comparativo de Vazões Naturais' },
        datalabels: {
            anchor: 'end',
            align: 'top',
            color: '#000',
            font: { weight: 'bold', size: 11 },
            formatter: function(value) {
                return value !== 0 ? value.toFixed(1) : ''; // mostra só se > 0
            }
        }
    }
},
plugins: [ChartDataLabels]
});
</script>
</body>
</html>
