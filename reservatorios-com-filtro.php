<?php 
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

$dataSelecionada = new DateTime($_GET['date'] ?? 'today');
$dataOntem = new DateTime('-1 day');

$cacheDir = __DIR__ . "/cache";
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

function getDadosApi($data) {
    global $cacheDir;
    $file = "$cacheDir/dados_" . $data->format('Y-m-d') . ".json";

    // Se houver cache válido
    if (file_exists($file) && time() - filemtime($file) < 21600) { // 6h
        return json_decode(file_get_contents($file), true);
    }

    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/" . $data->format('Y-m-d');

    // --------------------------
    // cURL com proxy (se necessário)
    // --------------------------
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
   // === CONFIGURAÇÃO DE PROXY (se precisar) ===
     curl_setopt($ch, CURLOPT_PROXY, "10.200.12.140:80"); // endereço e porta do proxy

    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($res) file_put_contents($file, $res);

    return $res ? json_decode($res, true) : null;
}
$dadosAtual = getDadosApi($dataSelecionada) ?: getDadosApi($dataOntem);
$data2021 = new DateTime("2021-" . $dataSelecionada->format('m-d'));
$dados2021 = getDadosApi($data2021);

$nomes = [64=>"Cantareira",65=>"Alto Tietê",66=>"Guarapiranga",67=>"Cotia",68=>"Rio Grande",69=>"Rio Claro",72=>"São Lourenço",75=>"SIM"];
$tabela = $labels = $volAtual = $vol2021 = [];

foreach (($dadosAtual['data'] ?? []) as $sAtual) {
    if ($sAtual['idSistema']==74) continue;
    $id = $sAtual['idSistema'];
    $nome = $nomes[$id] ?? "Sistema $id";
    $s21 = current(array_filter($dados2021['data'] ?? [], fn($s)=>$s['idSistema']==$id));
    $volA = $sAtual['volumeUtilArmazenadoPorcentagem'] ?? 0;
    $vol21 = $s21['volumeUtilArmazenadoPorcentagem'] ?? 0;
    $dif = round($volA-$vol21,1);
    $labels[] = $nome; $volAtual[] = $volA; $vol2021[] = $vol21;
    $tabela[] = [
        'sistema'=>$nome,
        'vol_atual'=>$volA,
        'vol_2021'=>$vol21,
        'dif'=>$dif,
        'chuva'=>$sAtual['chuva']??0,
        'chuvaMes'=>$sAtual['chuvaAcumuladaNoMes']??0,
        'chuvaHist'=>$sAtual['chuvaMediaHistorica']??0,
        'chuva_2021'=>$s21['chuva']??0,
        'chuvaMes_2021'=>$s21['chuvaAcumuladaNoMes']??0
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Reservatórios dos sistemas Produtores - <?= $dataSelecionada->format('d/m/Y') ?></title>
<link href="assets/img/logo/logo.png" rel="icon">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>
<link rel="stylesheet" href="assets/css/aptos.css">
<style>
#loader{position:fixed;top:0;left:0;width:100%;height:100%;background:#fff;z-index:9999;display:flex;align-items:center;justify-content:center;flex-direction:column;}
#loader p{animation:pulse 1.5s infinite;}@keyframes pulse{0%,100%{opacity:.5}50%{opacity:1}}
table {border-collapse: collapse; width:100%; max-width:1100px; margin-bottom:30px; font-size:14px;}
th, td {border:1px solid #ddd; padding:8px; text-align:center;}
th {background:#0b6a4a; color:white;}
tr:last-child {font-weight:bold; background:#e9f9ee;}
</style>
</head>
<body>
<?php include "navbar.php"; ?>

<div id="loader"><div class="spinner-border text-primary" style="width:4rem;height:4rem"></div><p>Carregando dados...</p></div>

<div class="container">
  <div class="card">
    <div class="card-body">
      <h2 style="text-align:center; margin-top:30px;">Comparativo de <strong>Volumes</strong> Sistemas Produtores (%)</h2>
      <h5 style="text-align:center; margin-bottom:30px; font-size:16px;">
        Dados de <?= $dataSelecionada->format('d/m/Y') ?> | Ano de Referência: <strong>2021</strong>
      </h5>
<!-- Formulário de seleção de data -->
<div class="row justify-content-center my-3">
  <div class="col-md-6">
    <form method="GET" class="d-flex  align-items-center mb-3 gap-2">
      <label for="date"class="me-2 fw-bold text-nowrap">Selecione a data:</label>
        <input type="date" id="date" name="date" class="form-control" value="<?= $dataSelecionada->format('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
          <button class="btn btn-success position-relative">Buscar<span id="btnSpinner" class="spinner-border spinner-border-sm text-light position-absolute top-50 start-50 translate-middle d-none"></span></button>
    </form>
  </div>
</div>


<!--div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
<?php foreach($tabela as $l): $c=$l['dif']>=0?'bg-success':'bg-danger';$s=$l['dif']>=0?'↑':'↓'; ?>
<div class="col">
<div class="card <?= $c ?> text-center shadow-sm h-100">
<div class="card-body">
<h6 class="card-title"><?= $l['sistema'] ?></h6>
<h4><?= number_format($l['vol_atual'],1,',','.') ?>%</h4>
<small><?= $s ?> <?= number_format(abs($l['dif']),1,',','.') ?>% vs 2021</small>
</div></div></div>
<?php endforeach; ?>
</div-->
 <div class="row">
    <?php foreach ($tabela as $l): 
        $dif = $l["dif"]; // diferença entre atual e 2021, ou você pode calcular dia a dia
        $c = $dif >= 0 ? 'text-success' : 'text-danger';
        $s = $dif >= 0 ? '↑' : '↓';
    ?>
    <div class="col">
      <div class="card style="background: <?= $dif >= 0 ? '#198754aa' : '#dc3545aa' ?>;>
        <div class="card-body text-center">
          <h6 class="card-title"><?= htmlspecialchars($l["sistema"]) ?></h6>
          <p class="card-text" style="font-size:1em;">
            <?= number_format($l["vol_atual"],1,',','.') ?> %
          </p>
          <div class="mt-1 <?= $c ?>" style="font-weight:bold;">
            <?= $s ?> <?= number_format(abs($dif),1,',','.') ?> %
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

<div class="row mt-4">
<div class="col-lg-9"><canvas id="grafSistema"></canvas>
  <div class="legenda-protocolo mt-3">
        <ul>
            <li><span class="cor e1"></span> E1 - Atenção</li>
            <li><span class="cor e2"></span> E2 - Alerta</li>
            <li><span class="cor e3"></span> E3 - Crítico</li>
            <li><span class="cor e4"></span> E4 - Emergência</li>
        </ul>
    </div>
</div>
<div class="col-lg-3"><canvas id="graficoPizza" style="margin-top:100px;"></canvas></div>
</div>

<div class="row mt-4">
<table class="table table-striped table-bordered mt-3">
<thead class="">
<!-- <tr>
  <th colspan="9" class="text-center bg-success text-white">Comparativo de Volumes dos Sistemas Produtores - <?= $dataSelecionada->format('d/m/Y') ?> ao 2021
</tr> -->
<tr>
  <th colspan="3" class="text-center bg-primary text-white">Volume (m³/s)</th>
  <th colspan="1" class="text-center bg-primary text-white">Dif. dos Volumes (%)</th>
  <th colspan="6" class="text-center bg-primary text-white">Precipitação (mm)</th>
</tr>
<tr style="font-size:12px;">
<th>Sistema</th>
<th>Volume Atual</th>
<th>Volume Mês 2021</th>
<th>Diferença</th>
<th>Chuva Diária Atual </th>
<th>Chuva Mes Acumulada Mês Atual</th>
<th>Chuva Média Histórica Atual</th>
<th>Chuva Mês 2021</th>
<th>Chuva Acumulada Mes 2021</th>
</tr></thead>
<tbody>
<?php foreach($tabela as $l): ?>
<tr>
<td><?= $l['sistema'] ?></td>
<td><?= number_format($l['vol_atual'],1,',','.') ?></td>
<td><?= number_format($l['vol_2021'],1,',','.') ?></td>
<td><?= number_format($l['dif'],1,',','.') ?></td>
<td><?= number_format($l['chuva'],1,',','.') ?></td>
<td><?= number_format($l['chuvaMes'],1,',','.') ?></td>
<td><?= number_format($l['chuvaHist'],1,',','.') ?></td>
<td><?= number_format($l['chuva_2021'],1,',','.') ?></td>
<td><?= number_format($l['chuvaMes_2021'],1,',','.') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<script>
window.addEventListener("load",()=>document.getElementById("loader").style.display="none");
document.querySelector("form").addEventListener("submit",function(){this.querySelector("button").disabled=true;this.querySelector("#btnSpinner").classList.remove("d-none");});

const labels=<?= json_encode($labels,JSON_UNESCAPED_UNICODE) ?>;
const volAtual=<?= json_encode($volAtual) ?>;
const vol2021=<?= json_encode($vol2021) ?>;

new Chart(document.getElementById("grafSistema"),{
type:'bar',
data:{labels:labels,datasets:[{label:"Volume 2021 (%)",data:vol2021,backgroundColor:"#1d73f3"},{label:"Volume Atual (%)",data:volAtual,backgroundColor:"#0448a1"}]},
options:{responsive:true,plugins:{tooltip:{mode:'index',intersect:false},legend:{position:"top"},datalabels:{display:true,anchor:'end',align:'end',color:'#181717ff',font:{weight:'bold',size:11},formatter:v=>v?v.toFixed(1)+'%':''},annotation:{annotations:{atencao60:{type:'line',yMin:60,yMax:60,borderColor:'yellow',borderWidth:2},critico40:{type:'line',yMin:40,yMax:40,borderColor:'orange',borderWidth:2},critico30:{type:'line',yMin:30,yMax:30,borderColor:'red',borderWidth:2},emergencia:{type:'line',yMin:20,yMax:20,borderColor:'purple',borderWidth:2}}}},interaction:{mode:'index',intersect:false},scales:{y:{beginAtZero:true,title:{display:true,text:"Volume (%)"}}}},
plugins:[ChartDataLabels]
});

const pesos={"Cantareira":50.50,"Alto Tietê":28.80,"Guarapiranga":8.80,"Rio Grande":5.80,"São Lourenço":4.60,"Rio Claro":0.70,"Cotia":0.80};
new Chart(document.getElementById("graficoPizza"),{type:'doughnut',data:{labels:Object.keys(pesos),datasets:[{data:Object.values(pesos),backgroundColor:['#0077cc','#1d73f3','#084391','#0ca73a','#726f6f','#e00e0e','#5e4040'],borderColor:'#fff',borderWidth:1}]},options:{plugins:{tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${ctx.parsed.toFixed(2)}%`}},legend:{position:'right',labels:{boxWidth:12,boxHeight:12,font:{size:12}}}}}});
</script>
</body>
</html>
