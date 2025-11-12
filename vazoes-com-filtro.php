<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

// ======================
// CONFIGURAÇÕES
// ======================
$anoRef = 2021;
$cacheTime = 3600; // 1h
$cacheDir = __DIR__;
$sistemas = [
    "Cantareira" => 64,
    "Alto Tietê" => 65,
    "Guarapiranga" => 66,
    "Cotia" => 67,
    "Rio Grande" => 68,
    "Rio Claro" => 69,
    "São Lourenço" => 72,
];
$idSim = 75; // Sistema Integrado Metropolitano

// ======================
// RECEBE DATA VIA GET
// ======================
if (!empty($_GET['date'])) {
    try {
        $dataBase = new DateTime($_GET['date']);
    } catch (Exception $e) {
        $dataBase = new DateTime();
    }
} else {
    $dataBase = new DateTime();
}

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
    // curl_setopt($ch, CURLOPT_PROXY, "10.200.12.140:80"); // proxy opcional
    $r = curl_exec($ch);
    curl_close($ch);

    if (!$r) return null;
    $data = json_decode($r, true);
    return $data["data"] ?? null;
}

function getCampo($dados, $id, $campo) {
    if (!is_array($dados)) return null;
    foreach ($dados as $d) {
        if (isset($d["idSistema"]) && $d["idSistema"] == $id) {
            return $d[$campo] ?? null;
        }
    }
    return null;
}

// ======================
// CACHE (1h) — usa nome com a data
// ======================
$cacheFileDate = str_replace("-", "", $dataBase->format('Y-m-d'));
$cacheFile = "$cacheDir/cache_vazao_$cacheFileDate.json";

if (isset($_POST['clear_cache'])) {
    array_map('unlink', glob("$cacheDir/cache_vazao_*.json"));
    echo "<div style='background:#c8f7c5;padding:10px;text-align:center;font-weight:bold;'>Cache limpo com sucesso.</div>";
}

$usandoCache = false;
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $dados = json_decode(file_get_contents($cacheFile), true);
    $usandoCache = true;
} else {
    $anoAtual = $dataBase->format('Y');
    $dias = [];
    for ($i = 0; $i < 7; $i++) $dias[] = (clone $dataBase)->modify("-$i day");

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

    $dadosBase = get_api($dataBase);
    $ultimoDiaRef = cal_days_in_month(CAL_GREGORIAN, $dataBase->format("m"), $anoRef);
    $dataRef = "$anoRef-" . $dataBase->format("m") . "-$ultimoDiaRef";
    $dadosRef = get_api(new DateTime($dataRef));

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
<title>Vazão Natural dos Sistemas Produtores</title>
<link href="assets/img/logo/logo.png" rel="icon">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<style>
body {font-family: Arial, Helvetica, sans-serif; padding:20px; background:#fafafa;}
table {border-collapse: collapse; width:100%; max-width:1100px; margin-bottom:30px; font-size:14px;}
th, td {border:1px solid #ddd; padding:8px; text-align:center;}
th {background:#0b6a4a; color:white;}
tr:last-child {font-weight:bold; background:#e9f9ee;}
#telaCarregando {
  display: none; position: fixed; top: 0; left: 0;
  width: 100%; height: 100%; background: rgba(255,255,255,0.9);
  z-index: 9999; display: flex; flex-direction: column;
  align-items: center; justify-content: center; font-size: 18px; color: #333;
  transition: opacity 0.5s ease;
}
.spinner {
  border: 6px solid #ddd; border-top: 6px solid #0074D9;
  border-radius: 50%; width: 50px; height: 50px;
  animation: spin 1s linear infinite; margin-bottom: 10px;
}
@keyframes spin {0% {transform: rotate(0deg);}100% {transform: rotate(360deg);}}
</style>
</head>
<body>

<?php include_once "navbar.php"; ?>

<!-- Tela de carregamento -->
<!-- <div id="telaCarregando">
  <div class="spinner"></div>
  <div>Carregando dados...</div>
</div> -->

<script>
document.getElementById('telaCarregando').style.display = 'flex';
window.addEventListener("load", () => {
  const tela = document.getElementById('telaCarregando');
  tela.style.opacity = '0';
  setTimeout(() => tela.style.display = 'none', 500);
});
</script>

<div class="container">
  <div class="card">
    <div class="card-body">
      <h2 class="text-center mt-3">Acompanhamento da <strong>Vazão</strong> Natural - Sistemas Produtores (m³/s)</h2>
      <h5 class="text-center mb-3">
        Dados de <?= $dataBase->format('d/m/Y') ?> | Ano de referência: <?= $anoRef ?>
      </h5>
      <!-- <p class="text-center text-muted small mb-1">
        <?= $usandoCache ? "📁 Dados do cache local (atualizados há menos de 1h)" : "🔄 Dados atualizados diretamente da API Sabesp" ?>
      </p>
      <p class="text-center text-muted small">
        Última atualização do cache: <?= file_exists($cacheFile) ? date("d/m/Y H:i", filemtime($cacheFile)) : "—" ?>
      </p> -->

      <div class="row justify-content-center my-3">
        <div class="col-md-6">
          <form method="GET" class="d-flex align-items-center gap-2">
            <label for="date" class="me-2 fw-bold text-nowrap">Selecione a data:</label>
            <input type="date" id="date" name="date" class="form-control" value="<?= $dataBase->format('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
            <button type="submit" class="btn btn-success">Buscar</button>
          </form>
        </div>
      </div>

      <!-- <form method="POST" class="text-center mb-3">
        <button name="clear_cache" class="btn btn-danger btn-sm">Limpar cache</button>
      </form> -->

<?php if (empty($dados)): ?>
  <div class="alert alert-warning text-center">
    ⚠️ Não foi possível obter dados para <?= $dataBase->format('d/m/Y') ?>. Tente outra data.
  </div>
<?php else: ?>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th colspan="6" class="bg-success text-white">Acompnhamento da Vazão Natural</th>
        <!-- <th colspan="3" class="bg-success text-white">Ano <?= date('Y') ?></th>
        <th colspan="2" class="bg-success text-white">Ano <?= $anoRef ?></th> -->
      </tr>
      <tr>
        <th class="bg-primary text-white">Sistema</th>
        <th class="bg-primary text-white">Vazão Natural (m³/s)</th>
        <th class="bg-primary text-white">Vazão últimos 7 dias (m³/s)</th>
        <th class="bg-primary text-white">Vazão Natural Mês (m³/s)</th>
        <th class="bg-primary text-white">Vazão Mês Referente (m³/s)</th>
        <th class="bg-primary text-white">Vazão Média Histórica (m³/s)</th>
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
</div>
  <canvas id="grafico" style="margin-top:30px;"></canvas>

<?php endif; ?>

    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const tela = document.getElementById("telaCarregando");

  // Mostra a tela de carregamento ao clicar em qualquer link
  document.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", function(e) {
      const href = this.getAttribute("href");
      // Ignora links de âncora, javascript ou sem destino
      if (!href || href.startsWith("#") || href.startsWith("javascript:")) return;

      e.preventDefault(); // Impede navegação imediata
      tela.style.display = "flex"; // Mostra o carregando
      tela.style.opacity = "1";
      document.body.style.cursor = "wait";

      // Dá um pequeno tempo pro spinner renderizar antes de sair da página
      setTimeout(() => { window.location = href; }, 150);
    });
  });
});
</script>

<script>
const dados = <?= json_encode($dados ?? [], JSON_UNESCAPED_UNICODE) ?>;
if (dados.length) {
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
        { label: "Vazão do dia (<?= $dataBase->format('d/m/Y')?>)", data: vazaoDia, backgroundColor:"#1f77b4" },
        { label: "Média últimos 7 dias", data: vazao7d, backgroundColor:"#7fb3d5" },
        { label: "Média mês <?= $dataBase->format('m/Y')?>", data: vazaoMes, backgroundColor:"#227542" },
        { label: "Média mês <?= $anoRef ?>", data: vazaoMesRef, backgroundColor:"#ffbb78" },
        { label: "Média Histórica", data: vazaoClima, backgroundColor:"#ff7f0e" }
      ]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'm³/s' } }
      },
      plugins: {
        legend: { position: 'top' },
        title: { display: true, text: 'Comparativo de Vazões Naturais' },
        datalabels: {
          anchor: 'end', align: 'top', color: '#000',
          font: { weight: 'bold', size: 11 },
          formatter: v => v ? v.toFixed(1) : ''
        }
      }
    },
    plugins: [ChartDataLabels]
  });
}
</script>
</body>
</html>
