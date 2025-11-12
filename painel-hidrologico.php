<?php 
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

$nomesSistemas = [64 => 'Cantareira', 65 => 'Alto Tietê', 66 => 'Guarapiranga', 67 => 'Cotia', 68 => 'Rio Grande', 69 => 'Rio Claro', 72 => 'São Lourenço', 75 => 'SIM'];

function getDadosApi($data) {
    global $cacheDir;
    $cacheFile = "$cacheDir/dados_$data.json";
    $tempoExpira = 1800;

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $tempoExpira) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
    $ch = curl_init();
    curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 20]);
    $resposta = curl_exec($ch);
    curl_close($ch);

    if ($resposta && ($dados = json_decode($resposta, true))) {
        file_put_contents($cacheFile, $resposta);
        return $dados;
    }
    return null;
}

$anoAtual = date('Y');
$mesAtual = date('m');
$ano2021 = 2021;
$diasNoMes = date('t');

$volumeAtual = [];
$volume2021Mes = [];
$chuvaAtualMes = [];
$chuva2021Mes = [];
$vazaoAtualMes = [];
$vazao2021Mes = [];
$vazaoCaptadaAtual = [];
$vazaoCaptadaMin = [];
$vazaoCaptadaMedia = [];

for ($dia = $diasNoMes; $dia >= 1; $dia--) {
    $dataAtualStr = sprintf('%04d-%02d-%02d', $anoAtual, $mesAtual, $dia);
    $dadosDia = getDadosApi($dataAtualStr);
    if (!empty($dadosDia['data'])) {
        foreach ($nomesSistemas as $id => $nome) {
            foreach ($dadosDia['data'] as $s) {
                if ($s['idSistema'] == $id) {
                    $volumeAtual[$nome] = number_format(floatval($s['volumeUtilArmazenadoPorcentagem'] ?? 0),1);
                    $chuvaAtualMes[$nome] = $nome === 'SIM' ? null : number_format(floatval($s['chuvaAcumuladaNoMes'] ?? 0),1);
                    $vazaoAtualMes[$nome] = number_format(floatval($s['vazaoDefluente'] ?? 0),1);
                    $vazaoCaptadaAtual[$nome] = number_format(floatval($s['vazaoCaptada'] ?? 0),1);
                    $vazaoCaptadaMin[$nome] = number_format(floatval($s['vazaoRetiradaNoMes'] ?? 0),1);
                    $vazaoCaptadaMedia[$nome] = number_format(floatval($s['vazaoProduzidaNoMes'] ?? 0),1);
                    break;
                }
            }
        }
        break;
    }
}

for ($dia = 1; $dia <= $diasNoMes; $dia++) {
    $data2021Str = sprintf('%04d-%02d-%02d', $ano2021, $mesAtual, $dia);
    $dados2021 = getDadosApi($data2021Str);
    if (!empty($dados2021['data'])) {
        foreach ($nomesSistemas as $id => $nome) {
            foreach ($dados2021['data'] as $s) {
                if ($s['idSistema'] == $id) {
                    $volume2021Mes[$nome] = number_format(floatval($s['volumeUtilArmazenadoPorcentagem'] ?? 0),1);
                    $chuva2021Mes[$nome] = $nome === 'SIM' ? null : number_format(floatval($s['chuvaAcumuladaNoMes'] ?? 0),1);
                    $vazao2021Mes[$nome] = number_format(floatval($s['vazaoDefluente'] ?? 0),1);
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
<title>Dashboard Hidrológico - <?= date('d/m/Y') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-4 text-center">Dashboard Hidrológico - Comparativo do Mês</h2>

  <div class="row mb-4">
  <?php foreach ($volumeAtual as $nome => $vol): ?>
    <div class="col-md-3 mb-3">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h6 class="card-title fw-bold"><?= htmlspecialchars($nome) ?></h6>
          <p>Vol. Atual: <strong><?= $vol ?>%</strong></p>
          <p>Chuva Acumulada: <strong><?= $chuvaAtualMes[$nome] ?? '0.0' ?> mm</strong></p>
          <p>Vazão Atual: <strong><?= $vazaoAtualMes[$nome] ?? '0.0' ?> m³/s</strong></p>
          <p>Vazão Captada: <strong><?= $vazaoCaptadaAtual[$nome] ?? '0.0' ?> m³/s</strong></p>
          <p>Vazão Captada Média: <strong><?= $vazaoCaptadaMedia[$nome] ?? '0.0' ?> m³/s</strong></p>
          <p>Vazão Captada Mínima: <strong><?= $vazaoCaptadaMin[$nome] ?? '0.0' ?> m³/s</strong></p>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>

  <h4>Volume (%)</h4>
  <canvas id="grafVolume"></canvas>

  <h4 class="mt-4">Chuva (mm)</h4>
  <canvas id="grafChuva"></canvas>

  <h4 class="mt-4">Vazão Defluente (m³/s)</h4>
  <canvas id="grafVazao"></canvas>

  <h4 class="mt-4">Vazão Captada (m³/s) - Atual / Média / Mínima</h4>
  <canvas id="grafVazaoCaptada"></canvas>
</div>

<script>
const sistemas = <?= json_encode(array_keys($volumeAtual)) ?>;
const volumeAtual = <?= json_encode(array_values($volumeAtual)) ?>;
const volume2021 = <?= json_encode(array_values($volume2021Mes)) ?>;
const chuvaAtual = <?= json_encode(array_values(array_filter($chuvaAtualMes, fn($v) => $v !== null))) ?>;
const chuva2021 = <?= json_encode(array_values(array_filter($chuva2021Mes, fn($v) => $v !== null))) ?>;
const vazaoAtual = <?= json_encode(array_values($vazaoAtualMes)) ?>;
const vazao2021 = <?= json_encode(array_values($vazao2021Mes)) ?>;
const vazaoCaptadaAtualJS = <?= json_encode(array_values($vazaoCaptadaAtual)) ?>;
const vazaoCaptadaMediaJS = <?= json_encode(array_values($vazaoCaptadaMedia)) ?>;
const vazaoCaptadaMinJS = <?= json_encode(array_values($vazaoCaptadaMin)) ?>;

function criarGraficoBarras(ctx, label, dadosAtual, dados2021, unidade) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ctx.id === 'grafChuva' ? Object.keys(chuvaAtual) : sistemas,
            datasets: [
                { label: 'Atual', data: dadosAtual, backgroundColor: '#007bff' },
                { label: 'Mesmo Mês 2021', data: dados2021, backgroundColor: '#ff8800' }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: label + ' (' + unidade + ')' },
                datalabels: { display: true, color: '#000', anchor: 'end', align: 'top', formatter: v => parseFloat(v).toFixed(1) }
            },
            scales: { y: { beginAtZero: true, title: { display: true, text: unidade } } }
        },
        plugins: [ChartDataLabels]
    });
}

criarGraficoBarras(document.getElementById('grafVolume'), 'Volume Útil', volumeAtual, volume2021, '%');
criarGraficoBarras(document.getElementById('grafChuva'), 'Chuva Acumulada', chuvaAtual, chuva2021, 'mm');
criarGraficoBarras(document.getElementById('grafVazao'), 'Vazão Defluente', vazaoAtual, vazao2021, 'm³/s');

new Chart(document.getElementById('grafVazaoCaptada'), {
    type: 'bar',
    data: {
        labels: sistemas,
        datasets: [
            { label: 'Atual', data: vazaoCaptadaAtualJS.map(v => parseFloat(v)), backgroundColor: '#007bff' },
            { label: 'Média', data: vazaoCaptadaMediaJS.map(v => parseFloat(v)), backgroundColor: '#28a745' },
            { label: 'Mínima', data: vazaoCaptadaMinJS.map(v => parseFloat(v)), backgroundColor: '#dc3545' }
]
},
options: {
responsive: true,
plugins: {
legend: { position: 'top' },
title: { display: true, text: 'Vazão Captada (m³/s) - Atual / Média / Mínima' },
datalabels: { display: true, color: '#000', anchor: 'end', align: 'top', formatter: v => parseFloat(v).toFixed(1) }
},
scales: { y: { beginAtZero: true, title: { display: true, text: 'm³/s' } } }
},
plugins: [ChartDataLabels]
});
</script>
</body>
</html>