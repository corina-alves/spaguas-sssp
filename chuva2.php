<?php
// chuva_sistemas.php - versão completa
date_default_timezone_set('America/Sao_Paulo');

// ---------------------------
// CONFIGURAÇÃO DE DATAS
// ---------------------------
if (!empty($_GET['date'])) {
    try {
        $data_base = new DateTime($_GET['date']);
    } catch (Exception $e) {
        error_log("Data inválida recebida: {$_GET['date']}. Usando hoje.");
        $data_base = new DateTime('now');
    }
} else {
    $data_base = new DateTime('now');
}

$ano_ref = 2021;

// ---------------------------
// SISTEMAS
// ---------------------------
$ids_sistemas = [
    "Cantareira"   => 64,
    "Alto Tietê"   => 65,
    "Guarapiranga" => 66,
    "Rio Grande"   => 67,
    "Rio Claro"    => 68,
    "Cotia"        => 69,
    "São Lourenço" => 72,
    "SIM"          => 74, // média dos sistemas
];

// ---------------------------
// FUNÇÃO PARA OBTER DADOS
// ---------------------------
function get_dados_data(DateTime $data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/" . $data->format('Y-m-d');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP script");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($resp === false || $http !== 200) {
        error_log("Erro ao acessar {$data->format('Y-m-d')}: HTTP {$http} - {$err}");
        return null;
    }
    $json = json_decode($resp, true);
    return $json['data'] ?? null;
}

// ---------------------------
// COLETAR DADOS DOS ÚLTIMOS 7 DIAS
// ---------------------------
$dias = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $data_base;
    $d->modify("-{$i} days");
    $dias[] = $d;
}

$chuvas_sistemas = [];
foreach ($ids_sistemas as $nome => $id) $chuvas_sistemas[$nome] = [];

foreach ($dias as $d) {
    $dados = get_dados_data($d);
    if (!$dados) continue;
    foreach ($dados as $s) {
        foreach ($ids_sistemas as $nome => $id_sis) {
            if (isset($s['idSistema']) && $s['idSistema'] == $id_sis && isset($s['chuva']) && $s['chuva'] !== null) {
                $chuvas_sistemas[$nome][] = floatval($s['chuva']);
            }
        }
    }
}

// ---------------------------
// DADOS ATUAIS E REFERÊNCIA
// ---------------------------
$dados_base = get_dados_data($data_base);
$ultimo_dia_mes_ref = (int) date('t', strtotime("{$ano_ref}-{$data_base->format('m')}-01"));
$data_ref = new DateTime("{$ano_ref}-{$data_base->format('m')}-{$ultimo_dia_mes_ref}");
$dados_ref = get_dados_data($data_ref);

// ---------------------------
// MONTAR TABELA
// ---------------------------
$linhas = [];
foreach ($ids_sistemas as $nome => $id_sis) {
    if ($id_sis == 74) continue; // ignora SIM por enquanto

    $chuva_dia = count($chuvas_sistemas[$nome]) > 0 ? $chuvas_sistemas[$nome][0] : null;
    $chuva_7d = count($chuvas_sistemas[$nome]) > 0 ? array_sum($chuvas_sistemas[$nome]) : null;

    $dados_sis_base = null;
    if (is_array($dados_base)) {
        foreach ($dados_base as $s) if (isset($s['idSistema']) && $s['idSistema'] == $id_sis) { $dados_sis_base = $s; break; }
    }
    $chuva_mes = $dados_sis_base['chuvaAcumuladaNoMes'] ?? null;
    $chuva_media_climatologica = $dados_sis_base['chuvaMediaHistorica'] ?? null;

    $dados_sis_ref = null;
    if (is_array($dados_ref)) {
        foreach ($dados_ref as $s) if (isset($s['idSistema']) && $s['idSistema'] == $id_sis) { $dados_sis_ref = $s; break; }
    }
    $chuva_mes_ref = $dados_sis_ref['chuvaAcumuladaNoMes'] ?? null;

    $linhas[] = [
        "Sistema" => $nome,
        "Chuva do dia (mm)" => $chuva_dia,
        "Chuva últimos 7 dias (mm)" => $chuva_7d,
        "Chuva acumulada mês " . $data_base->format('Y') . " (mm)" => $chuva_mes,
        "Chuva acumulada mês " . $ano_ref . " (mm)" => $chuva_mes_ref,
        "Chuva média climatológica (mm)" => $chuva_media_climatologica
    ];
}

// ---------------------------
// MÉDIA SIM
// ---------------------------
$linha_sim = [
    "Sistema" => "SIM",
    "Chuva do dia (mm)" => 0,
    "Chuva últimos 7 dias (mm)" => 0,
    "Chuva acumulada mês " . $data_base->format('Y') . " (mm)" => 0,
    "Chuva acumulada mês " . $ano_ref . " (mm)" => 0,
    "Chuva média climatológica (mm)" => 0
];
$contagem = ["dia"=>0, "7d"=>0, "mes"=>0, "mes_ref"=>0, "clima"=>0];

foreach ($linhas as $linha) {
    $linha_sim["Chuva do dia (mm)"] += $linha["Chuva do dia (mm)"] ?? 0; if($linha["Chuva do dia (mm)"]!==null) $contagem["dia"]++;
    $linha_sim["Chuva últimos 7 dias (mm)"] += $linha["Chuva últimos 7 dias (mm)"] ?? 0; if($linha["Chuva últimos 7 dias (mm)"]!==null) $contagem["7d"]++;
    $linha_sim["Chuva acumulada mês " . $data_base->format('Y') . " (mm)"] += $linha["Chuva acumulada mês " . $data_base->format('Y') . " (mm)"] ?? 0; if($linha["Chuva acumulada mês " . $data_base->format('Y') . " (mm)"]!==null) $contagem["mes"]++;
    $linha_sim["Chuva acumulada mês " . $ano_ref . " (mm)"] += $linha["Chuva acumulada mês " . $ano_ref . " (mm)"] ?? 0; if($linha["Chuva acumulada mês " . $ano_ref . " (mm)"]!==null) $contagem["mes_ref"]++;
    $linha_sim["Chuva média climatológica (mm)"] += $linha["Chuva média climatológica (mm)"] ?? 0; if($linha["Chuva média climatológica (mm)"]!==null) $contagem["clima"]++;
}
foreach ($contagem as $k=>$c) if($c>0) {
    switch($k){
        case "dia": $linha_sim["Chuva do dia (mm)"]/=$c; break;
        case "7d": $linha_sim["Chuva últimos 7 dias (mm)"]/=$c; break;
        case "mes": $linha_sim["Chuva acumulada mês " . $data_base->format('Y') . " (mm)"] /= $c; break;
        case "mes_ref": $linha_sim["Chuva acumulada mês " . $ano_ref . " (mm)"] /= $c; break;
        case "clima": $linha_sim["Chuva média climatológica (mm)"] /= $c; break;
    }
}
$linhas[] = $linha_sim;

// ---------------------------
// CALCULAR EXCEDENTES E MONTAR JSON
// ---------------------------
$df_resultado = [];
foreach ($linhas as $r) {
    $chuva_hoje = floatval($r["Chuva do dia (mm)"] ?? 0);
    $chuva_7d = floatval($r["Chuva últimos 7 dias (mm)"] ?? 0);
    $chuva_mes_2025 = floatval($r["Chuva acumulada mês " . $data_base->format('Y') . " (mm)"] ?? 0);
    $chuva_mes_ref = floatval($r["Chuva acumulada mês " . $ano_ref . " (mm)"] ?? 0);
    $chuva_media = floatval($r["Chuva média climatológica (mm)"] ?? 0);

    $df_resultado[] = [
        "Sistema"=>$r["Sistema"],
        "Chuva_hoje"=>$chuva_hoje,
        "Chuva_7d"=>$chuva_7d,
        "Chuva_mes_2025"=>$chuva_mes_2025,
        "Chuva_mes_ref"=>$chuva_mes_ref,
        "Chuva_media_clima"=>$chuva_media,
        "Excedente_7dias"=>max($chuva_7d-$chuva_hoje,0),
        "Excedente_climatologica"=>max($chuva_media-$chuva_mes_ref,0)
    ];
}

$json_dados = json_encode($df_resultado, JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8" />
<title>Chuva por Sistema - 7 dias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {font-family:Arial,Helvetica,sans-serif; padding:20px; background:#fafafa;color:#333;}
table {border-collapse: collapse; width:100%; max-width:1100px; margin-bottom:30px; font-size:12px; }
th,td{border:1px solid #ddd; padding:8px; text-align:center;}
th{background:#0b6a4a; color:white;}
tr:last-child{font-weight:bold; background:#e9f9ee;}
</style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
        
           
<h2>Chuva por Sistema — Últimos 7 dias (base: <?= $data_base->format('Y-m-d') ?>)</h2>

<table>
<thead><tr><?php foreach(array_keys($linhas[0]) as $h): ?><th><?= htmlspecialchars($h) ?></th><?php endforeach; ?></tr></thead>
<tbody>
<?php foreach($linhas as $r): ?><tr>
<?php foreach($r as $k=>$v): ?><td><?= ($k==='Sistema')?htmlspecialchars($v):(is_null($v)?'-':number_format((float)$v,1,',','.')) ?></td><?php endforeach; ?>
</tr><?php endforeach; ?>
</tbody>
</table>
 </div>
 <div class="col-lg-6">
<h2 style="text-align:center;">Comparativo de Chuvas por Sistema (mm)</h2>
<canvas id="graficoChuvas"></canvas>
</div>
</div>
</div>
<script>
const dados = <?= $json_dados ?>;
const sistemas = dados.map(d=>d.Sistema);
const chuvaDia = dados.map(d=>d.Chuva_hoje);
const chuva7Dias = dados.map(d=>d.Chuva_7d);
const chuvaMes2025 = dados.map(d=>d.Chuva_mes_2025);
const chuvaMesRef = dados.map(d=>d.Chuva_mes_ref);
const mediaClima = dados.map(d=>d.Excedente_climatologica);

const ctx = document.getElementById('graficoChuvas').getContext('2d');
new Chart(ctx,{
    type:'bar',
    data:{
        labels:sistemas,
        datasets:[
            {label:'Chuva do dia (mm)', data:chuvaDia, backgroundColor:'#1f77b4'},
            {label:'Chuva em 7 dias (mm)', data:chuva7Dias, backgroundColor:'#7fb3d5'},
            {label:'Chuva acumulada mês <?= $data_base->format('Y') ?> (mm)', data:chuvaMes2025, backgroundColor:'#2ca02c'},
            {label:'Chuva acumulada mês <?= $ano_ref ?> (mm)', data:chuvaMesRef, backgroundColor:'#ff7f0e'},
            {label:'Média climatológica (mm)', data:mediaClima, backgroundColor:'#ffbb78'}
        ]
    },
    options:{
        responsive:true,
        scales:{
            x:{stacked:false, ticks:{font:{size:12}}},
            y:{stacked:false, beginAtZero:true, title:{display:true,text:'Chuva (mm)', font:{size:14}}}
        },
        plugins:{
            legend:{position:'top', labels:{font:{size:12}}},
            tooltip:{callbacks:{label:function(ctx){return ctx.dataset.label+': '+ctx.parsed.y.toFixed(1)+' mm';}}}
        }
    }
});
</script>
</body>
</html>
