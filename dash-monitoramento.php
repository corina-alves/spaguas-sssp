<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("America/Sao_Paulo");

// CONFIGURAÇÃO
$ano_ref = 2021;
$cacheTime = 3600; // 1h
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

$ids_sistemas = [
    "Cantareira"   => 64,
    "Alto Tietê"   => 65,
    "Guarapiranga" => 66,
    "Cotia"        => 67,
    "Rio Grande"   => 68,
    "Rio Claro"    => 69,
    "São Lourenço" => 72,
    "SIM"          => 75
];

// DATA
$data = !empty($_GET['data']) ? preg_replace('/[^0-9\-]/','', $_GET['data']) : date("Y-m-d");
$data_ref = $ano_ref . '-' . date('m-d', strtotime($data));

// SISTEMAS SELECIONADOS
$selecionados = !empty($_GET['sistema']) && is_array($_GET['sistema']) ? $_GET['sistema'] : array_keys($ids_sistemas);

// FUNÇÃO PARA CACHE
function buscarAPIComCache($data, $cacheDir, $cacheTime) {
    $cacheFile = "{$cacheDir}/{$data}.json";
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        $raw = file_get_contents($cacheFile);
    } else {
        $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/{$data}";
        $opts = ["http" => ["method" => "GET", "header" => "User-Agent: PHP\r\n", "timeout" => 15]];
        $context = stream_context_create($opts);
        $raw = @file_get_contents($url, false, $context);
        if ($raw !== false) file_put_contents($cacheFile, $raw);
    }
    if ($raw === false) return null;
    $resp = json_decode($raw, true);
    return $resp['data'] ?? null;
}

// BUSCA DADOS
$dadosAtual = buscarAPIComCache($data, $cacheDir, $cacheTime);
$dadosRef   = buscarAPIComCache($data_ref, $cacheDir, $cacheTime);

// FILTRA PELOS IDS
function filtrarDados($dados, $ids_sistemas) {
    $filtrados = [];
    foreach ($ids_sistemas as $nome => $id) $filtrados[$nome] = null;
    if (!$dados) return $filtrados;
    foreach ($dados as $item) {
        foreach ($ids_sistemas as $nome => $idWanted) {
            if ((int)$item['idSistema'] === (int)$idWanted) {
                $filtrados[$nome] = $item;
                break;
            }
        }
    }
    return $filtrados;
}

$filtradosAtual = filtrarDados($dadosAtual, $ids_sistemas);
$filtradosRef   = filtrarDados($dadosRef, $ids_sistemas);

// MONTA ARRAYS
$reservatoriosAtual = $reservatoriosRef = [];
$vazoesAtual = $vazoesRef = [];
$chuvasAtual = $chuvasRef = [];

foreach ($ids_sistemas as $nome => $id) {
    if (!in_array($nome, $selecionados)) continue;
    $item = $filtradosAtual[$nome]; $itemRef = $filtradosRef[$nome];

    $reservatoriosAtual[] = ['nome'=>$nome,'valor_pct'=>$item['volumeUtilArmazenadoPorcentagem'] ?? null,'valor_hm3'=>$item['volumeUtilArmazenadoHm3'] ?? null,'date'=>$item['date'] ?? null];
    $vazoesAtual[] = ['nome'=>$nome,'vazao_natural'=>$item['vazaoNatural'] ?? null,'vazao_produzida'=>$item['vazaoProduzida'] ?? null,'vazao_captada'=>$item['vazaoCaptada'] ?? null,'date'=>$item['date'] ?? null];
    $chuvasAtual[] = ['nome'=>$nome,'chuva'=>$item['chuva'] ?? null,'chuva_mes'=>$item['chuvaAcumuladaNoMes'] ?? null,'date'=>$item['date'] ?? null];

    $reservatoriosRef[] = ['nome'=>$nome,'valor_pct'=>$itemRef['volumeUtilArmazenadoPorcentagem'] ?? null,'valor_hm3'=>$itemRef['volumeUtilArmazenadoHm3'] ?? null,'date'=>$itemRef['date'] ?? null];
    $vazoesRef[] = ['nome'=>$nome,'vazao_natural'=>$itemRef['vazaoNatural'] ?? null,'vazao_produzida'=>$itemRef['vazaoProduzida'] ?? null,'vazao_captada'=>$itemRef['vazaoCaptada'] ?? null,'date'=>$itemRef['date'] ?? null];
    $chuvasRef[] = ['nome'=>$nome,'chuva'=>$itemRef['chuva'] ?? null,'chuva_mes'=>$itemRef['chuvaAcumuladaNoMes'] ?? null,'date'=>$itemRef['date'] ?? null];
}

// MÉDIAS
function media_ignora_null($arr, $key) {
    $vals = [];
    foreach ($arr as $r) if (isset($r[$key]) && $r[$key]!==null) $vals[] = (float)$r[$key];
    return count($vals)?array_sum($vals)/count($vals):0;
}

$mediaVolumeAtual = round(media_ignora_null($reservatoriosAtual,'valor_pct'),2);
$mediaVolumeRef   = round(media_ignora_null($reservatoriosRef,'valor_pct'),2);
$mediaVazaoAtual  = round(media_ignora_null($vazoesAtual,'vazao_natural'),2);
$mediaVazaoRef    = round(media_ignora_null($vazoesRef,'vazao_natural'),2);
$mediaChuvaAtual  = round(media_ignora_null($chuvasAtual,'chuva'),2);
$mediaChuvaRef    = round(media_ignora_null($chuvasRef,'chuva'),2);

$updatedHuman = date("d/m/Y H:i");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Monitoramento dos Reservatorios</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- 2) plugin de annotation (carregue depois do Chart.js) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/3.1.0/chartjs-plugin-annotation.min.js"></script>

<style>
body { background:#f4f7fb; font-family: "Segoe UI", Roboto, Arial, sans-serif; }
/* .card { border-radius:12px; box-shadow:0 2px 8px rgba(16,24,40,0.06); } */
.section-title { border-left:6px solid #1e5aa8; padding-left:12px; margin-top:28px; margin-bottom:12px; color:#134e85; }
.chart-container { height:320px; }
table td, table th { vertical-align: middle; }
.small-muted { font-size:0.9rem; color:#6b7280; }
</style>
</head>
<body class="container py-4">

<header class="mb-3">
<h1 class="mb-0 text-center">Acompanhamento dos Reservatórios</h1>
<div class="small-muted text-center">Data: <?= $data ?> · Atualizado em <?= $updatedHuman ?></div>

<!-- FILTRO -->
<form method="get" class="mb-3 d-flex gap-2 align-items-center">
  <div class="col-lg-2"><label class="small-muted mb-0" for="sistema">Filtrar sistema:</label></div>
  <div class="col-lg-6"><input type="date" name="data" value="<?= $data ?>" class="form-control form-control-sm"></div>
 <div class="col-lg-3"> <button class="btn btn-primary btn-sm" type="submit">Aplicar</button></div>
</form>
</header>

<!-- CARDS -->
<div class="row g-3 mb-3">
<div class="col-lg-2" style="background:#ddd; padding:5px; border-radius:5px; text-align:center;">
    <canvas id="graficoPizza"></canvas>
</div>
<div class="col"><div class="card p-3 text-center">
<h6>Chuva média (dia)</h6>
<h2><?= number_format($mediaChuvaAtual,2,',','.') ?> mm</h2>
<div class="small-muted">2021: <?= number_format($mediaChuvaRef,2,',','.') ?> mm</div>
</div></div>
<div class="col"><div class="card p-3 text-center">
<h6>Vazão natural média</h6>
<h2><?= number_format($mediaVazaoAtual,2,',','.') ?> m³/s</h2>
<div class="small-muted">2021: <?= number_format($mediaVazaoRef,2,',','.') ?> m³/s</div>
</div></div>
<div class="col"><div class="card p-3 text-center">
<h6>Volume útil médio</h6>
<h2><?= number_format($mediaVolumeAtual,2,',','.') ?> %</h2>
<div class="small-muted">2021: <?= number_format($mediaVolumeRef,2,',','.') ?> %</div>
</div></div>
</div>

<!-- TABELA: RESERVATÓRIOS -->
<h3 class="section-title">Reservatórios</h3>
<div class="row">
    <div class="col-md-6">
            <h6 class="mb-3">Volume Útil Médio</h6> 
                <canvas id="chartVolume" class="chart-container"></canvas>
    </div>

<div class="col-md-6">
<div id="mapaCantareira" style="height:350px; border-radius:12px; margin-bottom:30px;"></div>

</div>
 </div>
<div class="table-responsive mb-4">
  <table class="table table-sm table-striped">
    <thead>
      <tr>
        <th>Reservatórios</th>
        <th class="text-end">Volume útil (%)</th>
        <th class="text-end">Volume útil (%) 2021</th>
        <th class="text-end">Volume (hm³)</th>
        <th class="text-end">Volume (hm³) 2021</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reservatoriosAtual as $i => $r): 
        $rRef = $reservatoriosRef[$i]; ?>
        <tr>
          <td><?= htmlspecialchars($r['nome']) ?></td>
          <td class="text-end"><?= $r['valor_pct'] !== null ? number_format($r['valor_pct'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $rRef['valor_pct'] !== null ? number_format($rRef['valor_pct'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $r['valor_hm3'] !== null ? number_format($r['valor_hm3'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $rRef['valor_hm3'] !== null ? number_format($rRef['valor_hm3'],2,',','.') : '-' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</div>
<div class="row">
    <div class="col-lg-6 mb-3">
      <h6>Vazões (m³/s)</h6>
      <canvas id="chartVazoes" class="chart-container"></canvas>
  </div>
  <div class="col-lg-6">
<!-- TABELA: VAZÕES -->
<!-- <h3 class="section-title">Vazões</h3> -->
<div class="table-responsive mb-4">
  <table class="table table-sm table-striped">
    <thead>
      <tr>
        <th>Sistema</th>
        <th class="text-end">Vazão natural (m³/s)</th>
        <th class="text-end">Vazão natural 2021 (m³/s)</th>
        <th class="text-end">Produzida (m³/s)</th>
        <th class="text-end">Produzida 2021 (m³/s)</th>
        <th class="text-end">Captada (m³/s)</th>
        <th class="text-end">Captada 2021 (m³/s)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($vazoesAtual as $i => $v): 
        $vRef = $vazoesRef[$i]; ?>
        <tr>
          <td><?= htmlspecialchars($v['nome']) ?></td>
          <td class="text-end"><?= $v['vazao_natural'] !== null ? number_format($v['vazao_natural'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $vRef['vazao_natural'] !== null ? number_format($vRef['vazao_natural'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $v['vazao_produzida'] !== null ? number_format($v['vazao_produzida'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $vRef['vazao_produzida'] !== null ? number_format($vRef['vazao_produzida'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $v['vazao_captada'] !== null ? number_format($v['vazao_captada'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $vRef['vazao_captada'] !== null ? number_format($vRef['vazao_captada'],2,',','.') : '-' ?></td>
          <!-- <td><?= $v['date'] ? date('d/m/Y', strtotime($v['date'])) : '-' ?></td>
          <td><?= $vRef['date'] ? date('d/m/Y', strtotime($vRef['date'])) : '-' ?></td> -->
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
</div>

<div class="row">
  <h3 class="section-title">Precipitação</h3>
  <div class="col-lg-6 mb-3">
<!-- TABELA: PRECIPITAÇÃO -->

<div class="table-responsive mb-4">
  <table class="table table-sm table-striped">
    <thead>
      <tr>
        <th>Sistema</th>
        <th class="text-end">Chuva (mm)</th>
        <th class="text-end">Chuva 2021 (mm)</th>
        <th class="text-end">Chuva acumulada no mês (mm)</th>
        <th class="text-end">Chuva acumulada 2021 (mm)</th>
  
      </tr>
    </thead>
    <tbody>
      <?php foreach ($chuvasAtual as $i => $c): 
        $cRef = $chuvasRef[$i]; ?>
        <tr>
          <td><?= htmlspecialchars($c['nome']) ?></td>
          <td class="text-end"><?= $c['chuva'] !== null ? number_format($c['chuva'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $cRef['chuva'] !== null ? number_format($cRef['chuva'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $c['chuva_mes'] !== null ? number_format($c['chuva_mes'],2,',','.') : '-' ?></td>
          <td class="text-end"><?= $cRef['chuva_mes'] !== null ? number_format($cRef['chuva_mes'],2,',','.') : '-' ?></td>

        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  </div>
</div>
  <div class="col-lg-6">
          <h6>Chuva (mm)</h6>
      <canvas id="chartChuva" class="chart-container"></canvas>
   


</div>
</div>
<!-- GRÁFICOS -->
<div class="row">
  <!-- <div class="col-lg-4 mb-3">
    <div class="card p-3">
      <h6>Reservatórios (%)</h6>
      <canvas id="chartVolume" class="chart-container"></canvas>
    </div>
  </div> -->
  <!-- <div class="col-lg-4 mb-3">
    <div class="card p-3">
      <h6>Vazões (m³/s)</h6>
      <canvas id="chartVazoes" class="chart-container"></canvas>
    </div>
  </div> -->
  <!-- <div class="col-lg-4 mb-3">
    <div class="card p-3">
      <h6>Chuva (mm)</h6>
      <canvas id="chartChuva" class="chart-container"></canvas>
    </div>
  </div> -->
</div>

<script>
function normalize(arr){return arr.map(v=>v===null?null:Number(v));}
function labels(arr){return arr.map(r=>r.nome);}

// VOLUME
// new Chart(document.getElementById('chartVolume'),{
//     type:'bar',
//     data:{
//         labels:labels(<?= json_encode($reservatoriosAtual) ?>),
//         datasets:[
//             {label:'Atual (%)',data:normalize(<?= json_encode(array_column($reservatoriosAtual,'valor_pct')) ?>),backgroundColor:'#1e5aa8'},
//             {label:'2021 (%)',data:normalize(<?= json_encode(array_column($reservatoriosRef,'valor_pct')) ?>),backgroundColor:'#0869e7ff'}
//         ]
//     },
      
//    options: {
//     responsive: true,
//     animation: {
//       duration: 1200, // suaviza a entrada
//       easing: 'easeOutQuart'
//     },
//     plugins: {
//       legend: { position: 'top' },
//       annotation: {
//         annotations: {
//           emergenciaBox: {
//             type: 'box',
//             yMin: 0,
//             yMax: 20,
//             backgroundColor: 'rgba(128,0,128,0.15)',
//             label: {
//               content: 'Emergência',
//               enabled: true,
//               position: 'start',
//               color: '#660066',
//               font: { weight: 'bold' }
//             }
//           },
//           criticoBox: {
//             type: 'box',
//             yMin: 20,
//             yMax: 30,
//             backgroundColor: 'rgba(255,0,0,0.15)',
//             label: {
//               content: 'Crítico',
//               enabled: true,
//               position: 'start',
//               color: '#b30000',
//               font: { weight: 'bold' }
//             }
//           },
//           atencaoBox: {
//             type: 'box',
//             yMin: 30,
//             yMax: 40,
//             backgroundColor: 'rgba(255,165,0,0.15)',
//             label: {
//               content: 'Atenção',
//               enabled: true,
//               position: 'start',
//               color: '#b36b00',
//               font: { weight: 'bold' }
//             }
//           },
//           alertaBox: {
//             type: 'box',
//             yMin: 40,
//             yMax: 60,
//             backgroundColor: 'rgba(255,255,0,0.15)',
//             label: {
//               content: 'Alerta',
//               enabled: true,
//               position: 'start',
//               color: '#999900',
//               font: { weight: 'bold' }
//             }
//           },
//           normalBox: {
//             type: 'box',
//             yMin: 60,
//             yMax: 100,
//             backgroundColor: 'rgba(0,255,0,0.1)',
//             label: {
//               content: 'Normal',
//               enabled: true,
//               position: 'start',
//               color: '#008000',
//               font: { weight: 'bold' }
//             }
//           },
//           // Linhas de referência
//           linha60: { type: 'line', yMin: 60, yMax: 60, borderColor: 'yellow', borderWidth: 2 },
//           linha40: { type: 'line', yMin: 40, yMax: 40, borderColor: 'orange', borderWidth: 2 },
//           linha30: { type: 'line', yMin: 30, yMax: 30, borderColor: 'red', borderWidth: 2 },
//           linha20: { type: 'line', yMin: 20, yMax: 20, borderColor: 'purple', borderWidth: 2 }
//         }
//       }
//     },
//     scales: {
//       y: {
//         beginAtZero: true,
//         ticks: { callback: v => v + '%' }
//       }
//     }
//   }
// });

  // garante que DOM e scripts já estejam prontos
  document.addEventListener('DOMContentLoaded', function () {

    // --- dados vindos do PHP (mantive sua chamada json_encode) ---
    const labelsDados = labels(<?= json_encode($reservatoriosAtual) ?>);
    const dadosAtual = normalize(<?= json_encode(array_column($reservatoriosAtual,'valor_pct')) ?>);
    const dadosRef   = normalize(<?= json_encode(array_column($reservatoriosRef,'valor_pct')) ?>);

    // (Opcional) — registra o plugin explicitamente se necessário
    // normalmente o include via CDN já registra, mas em alguns ambientes é preciso:
    if (typeof chartjsPluginAnnotation !== 'undefined') {
      // nome do global pode variar; tente registrar se existir
      try { Chart.register(chartjsPluginAnnotation); } catch(e){ /* ignora */ }
    } else if (typeof window['chartjs-plugin-annotation'] !== 'undefined') {
      try { Chart.register(window['chartjs-plugin-annotation']); } catch(e){ /* ignora */ }
    }
    // --- configuração do gráfico ---
    const ctx = document.getElementById('chartVolume').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labelsDados,
        datasets: [
          { label: 'Atual (%)', data: dadosAtual, backgroundColor: '#1e5aa8' },
          { label: '2021 (%)',  data: dadosRef,   backgroundColor: '#0869e7' }
        ]
      },
      options: {
        responsive: true,
        animation: { duration: 900, easing: 'easeOutQuart' },
        plugins: {
          legend: { position: 'top' },
          annotation: {
            // certifique-se que o plugin foi carregado (senão esta opção é ignorada)
            annotations: {
              emergenciaBox: {
                type: 'box', yMin: 0, yMax: 20,
                backgroundColor: 'rgba(128,0,128,0.12)',
                label: { content: 'Emergência', enabled: true, color:'#660066', font:{weight:'bold'} }
              },
              criticoBox: {
                type: 'box', yMin: 20, yMax: 30,
                backgroundColor: 'rgba(255,0,0,0.12)',
                label: { content: 'Crítico', enabled: true, color:'#9b0000', font:{weight:'bold'} }
              },
              atencaoBox: {
                type: 'box', yMin: 30, yMax: 40,
                backgroundColor: 'rgba(255,165,0,0.12)',
                label: { content: 'Atenção', enabled: true, color:'#a65a00', font:{weight:'bold'} }
              },
              alertaBox: {
                type: 'box', yMin: 40, yMax: 60,
                backgroundColor: 'rgba(255,255,0,0.12)',
                label: { content: 'Alerta', enabled: true, color:'#7f7f00', font:{weight:'bold'} }
              },
              normalBox: {
                type: 'box', yMin: 60, yMax: 100,
                backgroundColor: 'rgba(0,200,0,0.08)',
                label: { content: 'Normal', enabled: true, color:'#006600', font:{weight:'bold'} }
              },
              // linhas de referência
              linha60: { type: 'line', yMin: 60, yMax: 60, borderColor: 'yellow', borderWidth: 2 },
              linha40: { type: 'line', yMin: 40, yMax: 40, borderColor: 'orange', borderWidth: 2 },
              linha30: { type: 'line', yMin: 30, yMax: 30, borderColor: 'red', borderWidth: 2 },
              linha20: { type: 'line', yMin: 20, yMax: 20, borderColor: 'purple', borderWidth: 2 }
            }
          }
        },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => v + '%' } }
        }
      }
    });

  });



// VAZÕES
new Chart(document.getElementById('chartVazoes'),{
    type:'line',
    data:{
        labels:labels(<?= json_encode($vazoesAtual) ?>

        ),
        datasets:[
            {label:'Vazão Natural Atual',data:normalize(<?= json_encode(array_column($vazoesAtual,'vazao_natural')) ?>),borderColor:'rgba(30,90,168,0.85)',backgroundColor:'rgba(30,90,168,0.2)',fill:true,tension:0.3},
            {label:'Vazão Natural 2021',data:normalize(<?= json_encode(array_column($vazoesRef,'vazao_natural')) ?>),borderColor:'rgba(100,100,100,0.7)',backgroundColor:'rgba(100,100,100,0.2)',fill:true,tension:0.3}
        ]
    },
    options:{responsive:true,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true}}}
});

// CHUVA
new Chart(document.getElementById('chartChuva'),{
    type:'bar',
    data:{
        labels:labels(<?= json_encode($chuvasAtual) ?>),
        datasets:[
            {label:'Chuva Atual (mm)',data:normalize(<?= json_encode(array_column($chuvasAtual,'chuva')) ?>),backgroundColor:'rgba(30,90,168,0.85)'},
            {label:'Chuva 2021 (mm)',data:normalize(<?= json_encode(array_column($chuvasRef,'chuva')) ?>),backgroundColor:'rgba(100,100,100,0.5)'}
            // {label:'Chuva Acumulada (mm)',data:normalize(<?= json_encode(array_column($chuvasAtual,'chuva_mes')) ?>),backgroundColor:'rgba(100,100,100,0.5)'}

        ]
    },
    options:{responsive:true,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true}}}
});

const pesos={"Cantareira":50.50,"Alto Tietê":28.80,"Guarapiranga":8.80,"Rio Grande":5.80,"São Lourenço":4.60,"Rio Claro":0.70,"Cotia":0.80};
new Chart(document.getElementById("graficoPizza"),{type:'doughnut',data:{labels:Object.keys(pesos),datasets:[{data:Object.values(pesos),backgroundColor:['#0077cc','#1d73f3','#084391','#0ca73a','#726f6f','#e00e0e','#5e4040'],borderColor:'#fff',borderWidth:1}]},options:{plugins:{tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${ctx.parsed.toFixed(2)}%`}},legend:{position:'right',labels:{boxWidth:12,boxHeight:12,font:{size:12}}}}}});

</script>
<!-- ======================
 MAPA DO SISTEMA CANTAREIRA
========================= -->

<script>
// Garante que o mapa só será carregado depois que a página estiver pronta
document.addEventListener("DOMContentLoaded", function () {

  // Cria o mapa centralizado na região do Cantareira
  const map = L.map('mapaCantareira').setView([-23.1, -46.6], 9);

  // Adiciona camada base (OpenStreetMap)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contrib.'
  }).addTo(map);

  // Polígono do Sistema Cantareira (aproximado)
  const cantareiraCoords = [
    [-23.00, -46.82],
    [-22.95, -46.55],
    [-23.22, -46.45],
    [-23.28, -46.78]
  ];
  const cantareiraLayer = L.polygon(cantareiraCoords, {
    color: "#1e5aa8",
    weight: 2,
    fillColor: "#1e5aa8",
    fillOpacity: 0.3
  }).addTo(map);
  cantareiraLayer.bindPopup("<b>Sistema Cantareira</b><br>Área aproximada");

  // Pontos dos principais reservatórios
  const reservatorios = [
    { nome: "Jaguari-Jacareí", lat: -22.92, lon: -46.41 },
    { nome: "Cachoeira", lat: -23.00, lon: -46.40 },
    { nome: "Atibainha", lat: -23.15, lon: -46.40 },
    { nome: "Paiva Castro", lat: -23.35, lon: -46.65 }
  ];

  reservatorios.forEach(r => {
    L.marker([r.lat, r.lon])
      .addTo(map)
      .bindPopup(`<b>${r.nome}</b><br>Sistema Cantareira`);
  });

  // Legenda
  const legend = L.control({ position: 'bottomright' });
  legend.onAdd = function () {
    const div = L.DomUtil.create('div', 'info legend');
    div.innerHTML = `
      <b>Camadas</b><br>
      <i style="background:#1e5aa8;width:12px;height:12px;display:inline-block;margin-right:4px;"></i> Sistema Cantareira<br>
      <i style="background:#0077cc;width:12px;height:12px;display:inline-block;margin-right:4px;"></i> Reservatórios
    `;
    return div;
  };
  legend.addTo(map);

});
</script>

</body>
</html>

