<?php
/**
 * Dashboard UGRHI – Chuva Acumulada (corrigido)
 * Compatível com API do DAEE (https://cth.daee.sp.gov.br/sibh/api/v2/measurements/now)
 * Exibe acumulado de chuva em 24h, média por UGRHI e média histórica estimada.
 * Inclui cache local para reduzir carga da API.
 */

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

// CONFIGURAÇÃO
$apiBase = 'https://apps.spaguas.sp.gov.br/sibh/api/v2/measurements/now';
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir);
$cacheTTL = 1800; // 1 hora de cache
$historyDays = 30; // média histórica (últimos 30 dias)

// parâmetros da API
$apiParams = [
  'station_type_id' => 1,
  'hours' => 24,
  'show_all' => 'true',
  'serializer' => 'complete',
  'public' => 'true'
];
$apiParamsHistoric = [
  'station_type_id' => 1,
  'hours' => 24 * $historyDays,
  'show_all' => 'true',
  'serializer' => 'complete',
  'public' => 'true'
];

function buildUrl($base, $params) {
  return $base . '?' . http_build_query($params);
}

function fetchJsonWithCache($url, $cacheKey, $ttl, $cacheDir) {
  $cacheFile = "$cacheDir/" . md5($cacheKey) . '.json';
  if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
    return json_decode(file_get_contents($cacheFile), true);
  }
  $data = @file_get_contents($url);
  if (!$data) return null;
  file_put_contents($cacheFile, $data);
  return json_decode($data, true);
}

function extractMeasurementInfo($item) {
  $ugrhi_name = $item['ugrhi_name'] ?? 'Sem UGRHI';
  $ugrhi_id   = $item['ugrhi_id'] ?? null;
  $value      = isset($item['value']) ? floatval($item['value']) : null;
  $datetime   = $item['max_date'] ?? $item['min_date'] ?? null;
  $max_value   = $item['max_value'] ?? $item['max_value'] ?? null;

  return [
    'ugrhi_name' => $ugrhi_name,
    'ugrhi_id' => $ugrhi_id,
    'value' => $value,
    'datetime' => $datetime,
    'raw' => $item,
    'max_value' => $max_value
  ];
}

function aggregateByUGRHI($items) {
  $agg = [];
  foreach ($items as $it) {
    $u = $it['ugrhi_name'];
    if (!isset($agg[$u])) $agg[$u] = ['ugrhi_name' => $u, 'count' => 0, 'sum' => 0];
    if (!is_null($it['value'])) {
      $agg[$u]['sum'] += $it['value'];

      $agg[$u]['count']++;
    }
  }
  foreach ($agg as &$a) {
    $a['mean'] = $a['count'] ? $a['sum'] / $a['count'] : 0;
  }
  return $agg;
}

// --- Buscar dados da API ---
$mainUrl = buildUrl($apiBase, $apiParams);
$historicUrl = buildUrl($apiBase, $apiParamsHistoric);

$dataNow = fetchJsonWithCache($mainUrl, 'now_24h', $cacheTTL, $cacheDir);
$dataHistoric = fetchJsonWithCache($historicUrl, 'historic_' . $historyDays . 'd', $cacheTTL, $cacheDir);

$itemsNow = isset($dataNow['measurements']) ? array_map('extractMeasurementInfo', $dataNow['measurements']) : [];
$itemsHistoric = isset($dataHistoric['measurements']) ? array_map('extractMeasurementInfo', $dataHistoric['measurements']) : [];

$aggNow = aggregateByUGRHI($itemsNow);
$aggHistoric = aggregateByUGRHI($itemsHistoric);

// --- Calcular médias históricas ---
foreach ($aggNow as $u => &$d) {
  $max_value = $aggHistoric[$u]['mean'] ?? 0;
  $d['max_value'] = $max_value;
}

$dados = array_values($aggNow);
usort($dados, fn($a, $b) => $b['mean'] <=> $a['mean']);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard de Chuva por UGRHI</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
  .cards { display: flex; flex-wrap: wrap; gap: 15px; }
  .card { background: white; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 15px; flex: 1 1 200px; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
  th, td { padding: 8px 12px; border-bottom: 1px solid #ddd; }
  th { background: #1e5aa8; color: white; }
</style>
</head>
<body>
<h2>Dashboard de Chuva por UGRHI (últimas 24h)</h2>
<div class="cards">
<?php foreach (array_slice($dados, 0, 6) as $u): ?>
  <div class="card">
    <h3><?= htmlspecialchars($u['ugrhi_name']) ?></h3>
    <p><strong>Chuva média 24h:</strong> <?= number_format($u['mean'], 1) ?> mm</p>
    <p><strong>Média histórica:</strong> <?= number_format($u['max_value'], 1) ?> mm</p>

  </div>
<?php endforeach; ?>
</div>

<canvas id="graficoUGRHI" style="max-width: 800px; height: 400px; margin-top: 30px;"></canvas>

<table>
  <tr><th>UGRHI</th><th>Chuva média 24h (mm)</th><th>Média histórica (mm)</th></tr>
  <?php foreach ($dados as $u): ?>
  <tr>
    <td><?= htmlspecialchars($u['ugrhi_name']) ?></td>
    <td><?= number_format($u['mean'], 1) ?></td>
    <td><?= number_format($u['max_value'], 1) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<script>
const ugrhiLabels = <?= json_encode(array_column($dados, 'ugrhi_name')) ?>;
const chuvaAtual = <?= json_encode(array_column($dados, 'mean')) ?>;
const chuvaHistorica = <?= json_encode(array_column($dados, 'max_value')) ?>;

const ctx = document.getElementById('graficoUGRHI');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ugrhiLabels,
    datasets: [
      {
        label: 'Chuva Média 24h (mm)',
        data: chuvaAtual,
        backgroundColor: 'rgba(30,90,168,0.8)'
      },
      {
        label: 'Média Histórica (mm)',
        data: chuvaHistorica,
        backgroundColor: 'rgba(100,100,100,0.4)'
      }
    ]
  },
  options: {
    responsive: true,
    scales: { y: { beginAtZero: true } },
    plugins: {
      legend: { position: 'top' },
      title: { display: true, text: 'Comparativo de Chuvas por UGRHI' }
    }
  }
});
</script>
</body>
</html>