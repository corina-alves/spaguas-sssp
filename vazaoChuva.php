<?php
// ==========================
// boletim_chuva_vazao.php
// ==========================
date_default_timezone_set('America/Sao_Paulo');

// ---------------------------
// CONFIGURAÇÕES
// ---------------------------
if (!empty($_GET['date'])) {
    try { $data_base = new DateTime($_GET['date']); } 
    catch (Exception $e) { $data_base = new DateTime('now'); }
} else { $data_base = new DateTime('now'); }

$ano_ref = 2021;

// IDs dos sistemas principais
$ids_sistemas = [
    "Cantareira"   => 64,
    "Alto Tietê"   => 65,
    "Guarapiranga" => 66,
    "Rio Grande"   => 67,
    "Rio Claro"    => 68,
    "Cotia"        => 69,
    "São Lourenço" => 72,
];

// SIM
$id_sim_chuva = 75;
$id_sim_vazao = 75;

// ---------------------------
// FUNÇÃO PARA OBTER DADOS SABESP
// ---------------------------
function get_dados_data(DateTime $data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/" . $data->format('Y-m-d');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP script");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp===false || $http!==200) return null;
    $json = json_decode($resp,true);
    return $json['data'] ?? null;
}

// ---------------------------
// COLETAR DADOS DOS ÚLTIMOS 7 DIAS
// ---------------------------
$dias = [];
for($i=0;$i<7;$i++){ $d=clone $data_base; $d->modify("-{$i} days"); $dias[]=$d; }

$chuvas_sistemas=[]; $vazoes_sistemas=[];
foreach($ids_sistemas as $nome=>$id){ $chuvas_sistemas[$nome]=[]; $vazoes_sistemas[$nome]=[]; }

foreach($dias as $d){
    $dados = get_dados_data($d);
    if(!$dados) continue;
    foreach($dados as $s){
        foreach($ids_sistemas as $nome=>$id_sis){
            if(isset($s['idSistema']) && $s['idSistema']==$id_sis){
                if(isset($s['chuva'])) $chuvas_sistemas[$nome][]=(float)$s['chuva'];
                if(isset($s['vazaoNatural'])) $vazoes_sistemas[$nome][]=(float)$s['vazaoNatural'];
            }
        }
    }
}

// Dados base e referência
$dados_base = get_dados_data($data_base);
$ultimo_dia_mes_ref = (int) date('t', strtotime("{$ano_ref}-{$data_base->format('m')}-01"));
$data_ref = new DateTime("{$ano_ref}-{$data_base->format('m')}-{$ultimo_dia_mes_ref}");
$dados_ref = get_dados_data($data_ref);

// Helper
function obter_campo($dados_list,$id_sis,$campo){ 
    $rec = $dados_list ? array_filter($dados_list, fn($s)=>$s['idSistema']==$id_sis) : [];
    $rec = $rec ? array_values($rec)[0] : null;
    return $rec[$campo]??null;
}

// ---------------------------
// MONTAR LINHAS CHUVA
// ---------------------------
$linhas_chuva=[];
foreach($ids_sistemas as $nome=>$id_sis){
    $chuva_dia = obter_campo($dados_base,$id_sis,'chuva');
    $chuva_7d = !empty($chuvas_sistemas[$nome]) ? array_sum($chuvas_sistemas[$nome])/count($chuvas_sistemas[$nome]) : null;
    $chuva_mes = obter_campo($dados_base,$id_sis,'chuvaAcumuladaNoMes');
    $chuva_mes_ref = obter_campo($dados_ref,$id_sis,'chuvaAcumuladaNoMes');
    $chuva_media = obter_campo($dados_base,$id_sis,'chuvaMediaHistorica');
    $linhas_chuva[]=[
        "Sistema"=>$nome,
        "Chuva_dia"=>$chuva_dia,
        "Chuva_7d"=>$chuva_7d,
        "Chuva_mes"=>$chuva_mes,
        "Chuva_mes_ref"=>$chuva_mes_ref,
        "Chuva_media"=>$chuva_media,
        // "Excedente_7d"=>max($chuva_7d-$chuva_dia,0),
        "Excedente_clima"=>max($chuva_media-$chuva_mes_ref,0)
    ];
}
// SIM = média
$chuva_hoje=array_column($linhas_chuva,'Chuva_dia');
$chuva_7=array_column($linhas_chuva,'Chuva_7d');
$chuva_mes=array_column($linhas_chuva,'Chuva_mes');
$chuva_mes_ref=array_column($linhas_chuva,'Chuva_mes_ref');
$chuva_media=array_column($linhas_chuva,'Chuva_media');

$linhas_chuva[]=[
    "Sistema"=>"SIM",
    "Chuva_dia"=>array_sum($chuva_hoje)/count($chuva_hoje),
    "Chuva_7d"=>array_sum($chuva_7)/count($chuva_7),
    "Chuva_mes"=>array_sum($chuva_mes)/count($chuva_mes),
    "Chuva_mes_ref"=>array_sum($chuva_mes_ref)/count($chuva_mes_ref),
    "Chuva_media"=>array_sum($chuva_media)/count($chuva_media),
    // "Excedente_7d"=>array_sum($chuva_7)/count($chuva_7)-array_sum($chuva_hoje)/count($chuva_hoje),
    "Excedente_clima"=>array_sum($chuva_media)/count($chuva_media)-array_sum($chuva_mes_ref)/count($chuva_mes_ref)
];

// ---------------------------
// MONTAR LINHAS VAZÃO
// ---------------------------
$linhas_vazao=[];
foreach($ids_sistemas as $nome=>$id_sis){
    $vazao_dia = obter_campo($dados_base,$id_sis,'vazaoNatural');
    $vazao_7d = !empty($vazoes_sistemas[$nome]) ? array_sum($vazoes_sistemas[$nome])/count($vazoes_sistemas[$nome]) : null;
    $vazao_mes = obter_campo($dados_base,$id_sis,'vazaoNaturalNoMes');
    $vazao_mes_ref = obter_campo($dados_ref,$id_sis,'vazaoNaturalNoMes');
    $vazao_media = obter_campo($dados_base,$id_sis,'vazaoNaturalMediaHistorica');
    $linhas_vazao[]=[
        "Sistema"=>$nome,
        "Vazao_dia"=>$vazao_dia,
        "Vazao_7d"=>$vazao_7d,
        "Vazao_mes"=>$vazao_mes,
        "Vazao_mes_ref"=>$vazao_mes_ref,
        "Vazao_media"=>$vazao_media,
        // "Excedente_7d"=>max($vazao_7d-$vazao_dia,0),
        "Excedente_clima"=>max($vazao_media-$vazao_mes_ref,0)
    ];
}
// SIM
$vazao_dia=array_column($linhas_vazao,'Vazao_dia');
$vazao_7=array_column($linhas_vazao,'Vazao_7d');
$vazao_mes=array_column($linhas_vazao,'Vazao_mes');
$vazao_mes_ref=array_column($linhas_vazao,'Vazao_mes_ref');
$vazao_media=array_column($linhas_vazao,'Vazao_media');

$linhas_vazao[]=[
    "Sistema"=>"SIM",
    "Vazao_dia"=>array_sum($vazao_dia)/count($vazao_dia),
    "Vazao_7d"=>array_sum($vazao_7)/count($vazao_7),
    "Vazao_mes"=>array_sum($vazao_mes)/count($vazao_mes),
    "Vazao_mes_ref"=>array_sum($vazao_mes_ref)/count($vazao_mes_ref),
    "Vazao_media"=>array_sum($vazao_media)/count($vazao_media),
    // "Excedente_7d"=>array_sum($vazao_7)/count($vazao_7)-array_sum($vazao_dia)/count($vazao_dia),
    "Excedente_clima"=>array_sum($vazao_media)/count($vazao_media)-array_sum($vazao_mes_ref)/count($vazao_mes_ref)
];

$json_chuva = json_encode($linhas_chuva, JSON_UNESCAPED_UNICODE);
$json_vazao = json_encode($linhas_vazao, JSON_UNESCAPED_UNICODE);
?>

<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Boletim Chuva e Vazão</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {font-family: Arial,sans-serif; padding:20px; background:#fafafa;}
table {border-collapse: collapse; width:100%; max-width:1100px; margin-bottom:30px;}
th, td {border:1px solid #ddd; padding:8px; text-align:center;}
th {background:#0b6a4a; color:white;}
tr:last-child {font-weight:bold; background:#e9f9ee;}
</style>
</head>
<body>

<h2>Boletim — Base: <?= $data_base->format('Y-m-d') ?></h2>

<h3>Chuva por Sistema</h3>
<table>
<thead>
<tr>
<?php foreach(array_keys($linhas_chuva[0]) as $h): ?><th><?= htmlspecialchars($h) ?></th><?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($linhas_chuva as $r): ?>
<tr>
<?php foreach($r as $k=>$v): ?><td><?= ($k=='Sistema')?htmlspecialchars($v):number_format($v,1,',','.') ?></td><?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h3>Vazão por Sistema</h3>
<table>
<thead>
<tr>
<?php foreach(array_keys($linhas_vazao[0]) as $h): ?><th><?= htmlspecialchars($h) ?></th><?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($linhas_vazao as $r): ?>
<tr>
<?php foreach($r as $k=>$v): ?><td><?= ($k=='Sistema')?htmlspecialchars($v):number_format($v,1,',','.') ?></td><?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h3 style="text-align:center;">Gráficos — Chuva e Vazão</h3>
<canvas id="graficoChuva" height="140"></canvas>
<canvas id="graficoVazao" height="140" style="margin-top:50px;"></canvas>

    <!-- <canvas id="graficoChuva" width="500" height="300" style="flex:1; max-width:600px;"></canvas>
    <canvas id="graficoVazao" width="500" height="300" style="flex:1; max-width:600px;"></canvas> -->
<script>
// === CHUVA ===
const dados_chuva = <?= $json_chuva ?>;
const sistemas_chuva = dados_chuva.map(d=>d.Sistema);
const chuvaDia = dados_chuva.map(d=>d.Chuva_dia);
const excedente7 = dados_chuva.map(d=>d.Excedente_7d);
const chuvaMes = dados_chuva.map(d=>d.Chuva_mes);
const chuvaMesRef = dados_chuva.map(d=>d.Chuva_mes_ref);
const excedenteClima = dados_chuva.map(d=>d.Excedente_clima);

new Chart(document.getElementById('graficoChuva').getContext('2d'), {
    type:'bar',
    data:{
        labels:sistemas_chuva,
        datasets:[
            {label:'Chuva do dia (mm)', data:chuvaDia, backgroundColor:'#1f77b4', stack:'semana'},
            {label:'Excedente últimos 7 dias (mm)', data:excedente7, backgroundColor:'#7fb3d5', stack:'semana'},
            {label:'Chuva no mês <?= $data_base->format('Y') ?> (mm)', data:chuvaMes, backgroundColor:'#2ca02c'},
            {label:'Chuva no mês <?= $ano_ref ?> (mm)', data:chuvaMesRef, backgroundColor:'#ff7f0e', stack:'clima'},
            {label:'Excedente climatológica (mm)', data:excedenteClima, backgroundColor:'#ffbb78', stack:'clima'}
        ]
    },
    options:{responsive:true, plugins:{legend:{position:'top'}}, scales:{x:{stacked:false}, y:{beginAtZero:true, title:{display:true,text:'Chuva (mm)'}}}}
});

// === VAZÃO ===
const dados_vazao = <?= $json_vazao ?>;
const sistemas_vazao = dados_vazao.map(d=>d.Sistema);
const vazaoDia = dados_vazao.map(d=>d.Vazao_dia);
const vazaoEx7 = dados_vazao.map(d=>d.Excedente_7d);
const vazaoMes = dados_vazao.map(d=>d.Vazao_mes);
const vazaoMesRef = dados_vazao.map(d=>d.Vazao_mes_ref);
const vazaoExClima = dados_vazao.map(d=>d.Excedente_clima);

new Chart(document.getElementById('graficoVazao').getContext('2d'), {
    type:'bar',
    data:{
        labels:sistemas_vazao,
        datasets:[
            {label:'Vazão do dia (m³/s)', data:vazaoDia, backgroundColor:'#1f77b4', stack:'semana'},
            {label:'Excedente últimos 7 dias (m³/s)', data:vazaoEx7, backgroundColor:'#7fb3d5', stack:'semana'},
            {label:'Vazão no mês <?= $data_base->format('Y') ?> (m³/s)', data:vazaoMes, backgroundColor:'#2ca02c'},
            {label:'Vazão no mês <?= $ano_ref ?> (m³/s)', data:vazaoMesRef, backgroundColor:'#ff7f0e', stack:'clima'},
            {label:'Excedente climatológica (m³/s)', data:vazaoExClima, backgroundColor:'#ffbb78', stack:'clima'}
        ]
    },
    options:{responsive:true, plugins:{legend:{position:'top'}}, scales:{x:{stacked:false}, y:{beginAtZero:true, title:{display:true,text:'Vazão (m³/s)'}}}}
});
</script>

</body>
</html>
