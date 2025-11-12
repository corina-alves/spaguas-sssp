<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

// Datas
$dataAtual = date("Y-m-d");
$dataOntem = date("Y-m-d", strtotime("-1 day"));
function getDadosApi($data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resposta = curl_exec($ch);
    curl_close($ch);
    return json_decode($resposta, true);
}
$dadosTeste = getDadosApi($dataAtual);
$dadosOntem = getDadosApi($dataOntem);
if (!isset($dadosTeste["data"]) || empty($dadosTeste["data"])) {
    $dataAtual = date("Y-m-d", strtotime("-1 day"));
}
$mesAtual = date("m", strtotime($dataAtual));
$data2021 = "2021-" . date("m-d", strtotime($dataAtual));

function getDadosUltimosDias($idSistema, $dias = 7) {
    $chuvas = [];
    $vazoes = [];
    for ($i = 0; $i < $dias; $i++) {
        $data = date("Y-m-d", strtotime("-$i days"));
        $dados = getDadosApi($data);
        if (isset($dados["data"])) {
            foreach ($dados["data"] as $s) {
                if ($s["idSistema"] == $idSistema) {
                    if (isset($s["chuva"])) $chuvas[] = $s["chuva"];
                    if (isset($s["vazaoNatural"])) $vazoes[] = $s["vazaoNatural"];
                }
            }
        }
    }
    $chuvaMedia7d = !empty($chuvas) ? array_sum($chuvas) / count($chuvas) : null;
    $vazaoMedia7d = !empty($vazoes) ? array_sum($vazoes) / count($vazoes) : null;
    return [
        "chuva7dias" => round($chuvaMedia7d, 1),
        "vazao7dias" => round($vazaoMedia7d, 1),
    ];
}

// Busca de dados
$dadosAtual = getDadosApi($dataAtual);
$dados2021 = getDadosApi($data2021);
if (!$dadosAtual || !isset($dadosAtual["data"])) die("<p>❌ Erro: não foi possível obter dados atuais da API.</p>");
if (!$dados2021 || !isset($dados2021["data"])) die("<p>⚠️ Erro: não foi possível obter dados de 2021 da API.</p>");

$nomesSistemas = [
   75 => "Sistema Integrado Metropolitano (SIM)", 64 => "Cantareira", 65 => "Alto Tietê", 66 => "Guarapiranga",
    67 => "Cotia", 68 => "Rio Grande", 69 => "Rio Claro",
    72 => "São Lourenço"
];

$sistemasAtual = array_filter($dadosAtual["data"], fn($s) => $s["idSistema"] != 74);
$sistemas2021 = array_filter($dados2021["data"], fn($s) => $s["idSistema"] != 74);

$labels = $volumesAtual = $volumes2021 = $difVol = $tabela = [];

foreach ($sistemasAtual as $sAtual) {
    $idSistema = $sAtual["idSistema"];
    $nome = $nomesSistemas[$idSistema] ?? "Sistema $idSistema";
    $s2021 = null;
    foreach ($sistemas2021 as $s) if ($s["idSistema"] == $idSistema) { $s2021 = $s; break; }

    $volAtual = $sAtual["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $vol2021 = $s2021["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $dif = $volAtual - $vol2021;
    $sOntem = null;
    foreach ($dadosOntem["data"] as $s) if ($s["idSistema"] == $idSistema) { $sOntem = $s; break; }
    $volOntem = $sOntem["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $difDiaria = round($volAtual - $volOntem, 1);

    $tabela[] = [
        "sistema" => $nome,
        "vol_atual" => $volAtual,
        "vol_2021" => $vol2021,
        "dif" => $dif,
        "dif_diaria" => $difDiaria,
        "chuva" => $sAtual["chuva"] ?? 0,
        "chuvaAcumuladaNoMes" => $sAtual["chuvaAcumuladaNoMes"] ?? 0,
        "chuvaMediaHistoricaAtual" => $sAtual["chuvaMediaHistorica"] ?? 0,
        "chuva_2021" => $s2021["chuva"] ?? 0,
        "chuvaAcumuladaNoMes_2021" => $s2021["chuvaAcumuladaNoMes"] ?? 0
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Acompanhamento dos reservatórios da RMSP</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">

<style>
body { font-family: "aptos", sans-serif; margin: 30px; }
.card { border: solid 0px #ccc; box-shadow: 2px 2px 8px #ccc; margin-bottom: 15px; font-size: 0.9em; background: #FFF; }
.card h6 { font-weight: bold; }
.container { font-size: 0.9em; }
</style>
</head>
<body>

  <header id="header" class="header  sticky-top" style="border: solid 0px #FFF;"><?php include "nav.php";?></header>
<div class="container mt-4">
<div class="row">
<?php foreach ($tabela as $linha):
    $dif = $linha["dif"];
    $cor = $dif >= 0 ? 'text-success' : 'text-danger';
    $seta = $dif >= 0 ? '↑' : '↓';

?>
<div class="col">
  <div class="card" style="background: <?= $dif >= 0 ? '#19875433' : '#ffffffff' ?>;">
    <div class="card-body text-center">
      <h6><?= htmlspecialchars($linha["sistema"]) ?></h6>
      <p style="font-size:1.2em;"><?= number_format($linha["vol_atual"],1,',','.') ?>%</p>
      <div class="<?= $cor ?>" style="font-weight:bold;"><?= $seta ?> <?= number_format(abs($dif),1,',','.') ?>%</div>
      
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<h4 class="text-center">Boletim Diário dos Reservatórios</h4>

<div class="mt-4 p-3 border rounded bg-light">
<?php
echo "<h5>📊 Análise dos Sistemas Produtores da Rede Metropolitana de São Paulo (RMSP)</h5>";
echo "<p><i> Volume Útil (%) em (" . date("d/m/Y") . ")</i></p>";
foreach ($tabela as $linha) {
    $nome = $linha["sistema"];
    $vol = $linha["vol_atual"];
    $dif = $linha["dif"];
    $difDiaria = $linha["dif_diaria"];
    if ($vol >= 60) $emoji = "🟢";
    elseif ($vol >= 40) $emoji = "🟡";
    elseif ($vol >= 30) $emoji = "🟠";
    elseif ($vol >= 20) $emoji = "🔴";
    else $emoji = "🟣";
    $seta = $dif >= 0 ? "⬆️" : "⬇️";
    $sinal = $dif >= 0 ? "+" : "";
    echo "$emoji <b>$nome</b>: " . number_format($vol,1,',','.') . "% ($sinal" . number_format($difDiaria,1,',','.') . ");<br>";
}
echo "<br>";
    echo"<strong>Legenda:</strong><br>";
    echo "🟢 E0 - Normal <br>";
    echo "🟡 E1 - Atenção<br/>";
    echo "🟠 E2 - Alerta<br>";
    echo "🔴 E3 - Crítico <br/>";
    echo "🟣 E4 - Emergencial";

// Comparações
$reducoes = array_filter($tabela, fn($t) => $t['dif'] < 0);
usort($reducoes, fn($a,$b) => $a['dif'] <=> $b['dif']);
$ganhos = array_filter($tabela, fn($t) => $t['dif'] > 0);
usort($ganhos, fn($a,$b) => $b['dif'] <=> $a['dif']);

$maiorReducao = $reducoes[0] ?? null;
$segundaReducao = $reducoes[1] ?? null;
$maiorGanho = $ganhos[0] ?? null;
$segundoGanho = $ganhos[1] ?? null;

$sim = array_filter($tabela, fn($t) => $t["sistema"] == "Sistema Integrado Metropolitano (SIM)");
$sim = reset($sim);
$reduSim = number_format($sim["dif"], 1, ',', '.');
echo "<br>";
echo "<br><b>📝 Comparação com o ano de 2021</b><br><br>";
echo "⬇️ O <strong>SIM</strong> apresenta variação de $reduSim% em relação a 2021.<br><br>";

if ($maiorReducao) {
    echo "⚠️ <strong>Maiores reduções:</strong><br>";
    echo "⬇️ {$maiorReducao['sistema']}  maior redução em relação ao ano de 2021(" . number_format($maiorReducao['dif'],1,',','.') . "%)<br>";
    if ($segundaReducao)
        echo "⬇️ {$segundaReducao['sistema']} segunda maior redução em relação ao ano de 2021 (" . number_format($segundaReducao['dif'],1,',','.') . "%)<br>";
}

if ($maiorGanho) {
    echo "<br>🟢 Maiores ganhos:<br>";
    echo "⬆️ <b>{$maiorGanho['sistema']}</b> (" . number_format($maiorGanho['dif'],1,',','.') . "%)<br>";
    if ($segundoGanho)
        echo "⬆️ <b>{$segundoGanho['sistema']}</b> (" . number_format($segundoGanho['dif'],1,',','.') . "%)<br>";
}
?>
</div>
</div>
</body>
</html>
