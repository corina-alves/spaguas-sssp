<?php
header("Content-Type: text/html; charset=utf-8");

// =============================
// CONFIGURAÇÕES INICIAIS
// =============================
date_default_timezone_set("America/Sao_Paulo");
$dataAtual = date("Y-m-d");
$anoAtual = date("Y");
$anoRef = 2021;

// IDs dos sistemas
$ids_sistemas = [
    "Cantareira" => 64,
    "Alto Tietê" => 65,
    "Guarapiranga" => 66,
    "Rio Grande" => 67,
    "Rio Claro" => 68,
    "Cotia" => 69,
    "São Lourenço" => 72
];

// =============================
// FUNÇÃO: BUSCAR DADOS API SABESP
// =============================
function getDadosApi($data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/{$data}";
    $res = @file_get_contents($url);
    if (!$res) return null;
    $json = json_decode($res, true);
    return $json["data"] ?? null;
}

// =============================
// COLETA DOS DADOS DOS ÚLTIMOS 7 DIAS
// =============================
$vazoes_por_sistema = [];
for ($i = 0; $i < 7; $i++) {
    $data = date("Y-m-d", strtotime("-{$i} days"));
    $dados = getDadosApi($data);
    if (!$dados) continue;

    foreach ($dados as $s) {
        foreach ($ids_sistemas as $nome => $id) {
            if ($s["idSistema"] == $id && isset($s["vazaoNatural"])) {
                $vazoes_por_sistema[$nome][] = $s["vazaoNatural"];
            }
        }
    }
}

// =============================
// MÉDIA DAS VAZÕES DOS ÚLTIMOS 7 DIAS (CORRIGIDO)
// =============================
$vazao7dias = [];
foreach ($vazoes_por_sistema as $sistema => $valores) {
    $vals_validos = array_values(array_filter($valores, function($v) {
        return $v !== null && $v !== '' && is_numeric($v);
    }));
    $ultimos7 = array_slice($vals_validos, -7);
    $media7d = count($ultimos7) > 0 ? array_sum($ultimos7) / count($ultimos7) : null;
    $vazao7dias[$sistema] = $media7d !== null ? round($media7d, 2) : null;
}

// =============================
// DADOS ATUAIS E DE REFERÊNCIA
// =============================
$dados_atual = getDadosApi($dataAtual);
$dados_ref = getDadosApi($anoRef . "-" . date("m-d"));

// Arrays para o gráfico
$labels = array_keys($ids_sistemas);
$vazaoNatural = [];
$vazaoNaturalNoMes = [];
$vazaoNaturalNoMes2021 = [];
$vazaoNaturalMediaHistorica = [];

foreach ($ids_sistemas as $nome => $id) {
    $dadosSis = null;
    foreach ($dados_atual as $s) {
        if ($s["idSistema"] == $id) {
            $dadosSis = $s;
            break;
        }
    }

    $dadosRef = null;
    foreach ($dados_ref as $r) {
        if ($r["idSistema"] == $id) {
            $dadosRef = $r;
            break;
        }
    }

    $vazaoNatural[] = $dadosSis["vazaoNatural"] ?? null;
    $vazaoNaturalNoMes[] = $dadosSis["vazaoNaturalAcumuladaNoMes"] ?? null;
    $vazaoNaturalNoMes2021[] = $dadosRef["vazaoNaturalAcumuladaNoMes"] ?? null;
    $vazaoNaturalMediaHistorica[] = $dadosSis["vazaoNaturalMediaHistorica"] ?? null;
}

// =============================
// JSON PARA O JAVASCRIPT
// =============================
$json_labels = json_encode($labels, JSON_UNESCAPED_UNICODE);
$json_vazaoNatural = json_encode($vazaoNatural, JSON_UNESCAPED_UNICODE);
$json_vazaoNaturalNoMes = json_encode($vazaoNaturalNoMes, JSON_UNESCAPED_UNICODE);
$json_vazaoNaturalNoMes2021 = json_encode($vazaoNaturalNoMes2021, JSON_UNESCAPED_UNICODE);
$json_vazaoNaturalMediaHistorica = json_encode($vazaoNaturalMediaHistorica, JSON_UNESCAPED_UNICODE);
$json_vazao7dias = json_encode(array_values($vazao7dias), JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Monitoramento de Vazões - Últimos 7 dias</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: Arial, sans-serif; margin: 30px; background: #f5f5f5; }
h2 { text-align: center; }
canvas { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
</style>
</head>
<body>

<h2>Acompanhamento Chuvas e Vazões do Dia <?= date('d/m/Y') ?> e Últimos 7 Dias</h2>
<canvas id="grafVazaoChuva"></canvas>

<script>
const labels = <?= $json_labels ?>;
const vazaoNatural = <?= $json_vazaoNatural ?>;
const vazaoNaturalNoMes = <?= $json_vazaoNaturalNoMes ?>;
const vazaoNaturalNoMes2021 = <?= $json_vazaoNaturalNoMes2021 ?>;
const vazaoNaturalMediaHistorica = <?= $json_vazaoNaturalMediaHistorica ?>;
const vazao7dias = <?= $json_vazao7dias ?>;

new Chart(document.getElementById("grafVazaoChuva"), {
data: {
labels: labels,
datasets: [
{label:"Vazão do Dia (m³/s)", type:"bar", data:vazaoNatural, backgroundColor:"#da6314cf", yAxisID:'y'},
{label:"Vazão do Mês (m³/s)", type:"bar", data:vazaoNaturalNoMes, backgroundColor:"rgba(255,159,64,0.7)", yAxisID:'y'},
{label:"Vazão Mês Ano de 2021 (m³/s)", type:"bar", data:vazaoNaturalNoMes2021, backgroundColor:"rgba(11,75,5,0.73)", yAxisID:'y'},
{label:"Vazão Natural Méd. Histórica (m³/s)", type:"bar", data:vazaoNaturalMediaHistorica, backgroundColor:"#ffcc00aa", yAxisID:'y'},
{
  label:"Vazão Média Últimos 7 Dias (m³/s)",
  type:"bar",
  data:vazao7dias,
  backgroundColor:"#009900",
  borderWidth:2,
  tension:0.3,
  fill:false,
  yAxisID:'y'
}
]
},
options:{
responsive:true,
interaction:{mode:'index',intersect:false},
plugins:{
legend:{position:"top"},
title:{display:true,text:"Acompanhamento Chuvas e Vazões - <?= date('d/m/Y') ?>"}
},
scales:{
y:{type:'linear',position:'left',title:{display:true,text:"Vazão (m³/s)"},beginAtZero:true,grid:{drawOnChartArea:false}}
}
}
});
</script>

</body>
</html>
