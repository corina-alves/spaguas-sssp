<?php include_once "./config/dados_vazoes.php";?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8" />
<title>Vazão por Sistema - e comparações com ano de 2021</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
table {border-collapse: collapse; width:100%;  margin-bottom:30px;}
th, td {border:1px solid #ddd; padding:8px; text-align:center;}
th {background:#0b6a4a; color:white;}
tr:last-child {font-weight:bold; background:#e9f9ee;}
</style>
<link href="assets/img/logo/logo.png" rel="icon">
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>  
  <link href="assets/cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/cdn/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

</head>

<body>
<div class="container">
    <h2 style="text-align:center; margin-top:30px;">Comparativo de <strong>Vazões</strong> Sistemas Produtores (m³/s)</h2>
        <h4 style="text-align:center; margin-bottom:30px;"><i><?= $data_base->format('d/m/Y') ?> | Ano de referência: <?= $ano_ref ?></i></h4>
            <canvas id="graficoVazao" height="140"></canvas>
                <table>
                    <thead>
                        <tr>
                            <?php foreach(array_keys($linhas[0]) as $h): ?>
                                <th><?= htmlspecialchars($h) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($linhas as $r): ?>
                        <tr>
                            <?php foreach($r as $k=>$v): ?>
                                <td><?= ($k=='Sistema') ? htmlspecialchars($v) : number_format($v,1,',','.') ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
 </div>

<script>
const dados = <?= $json_dados ?>;
const sistemas = dados.map(d => d.Sistema);
const vazaoDia = dados.map(d => d.Vazao_dia);
const vazao7d = dados.map(d => d.Vazao_7d);
const vazaoMes = dados.map(d => d.Vazao_mes);
const vazaoMesRef = dados.map(d => d.Vazao_mes_ref);
const vazaoClima = dados.map(d => d.Vazao_media_clima);

const ctx = document.getElementById('graficoVazao').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: sistemas,
        datasets: [
            {
                label: 'Vazão do dia <?= $data_base->format('d/m/Y') ?> (m³/s)',
                data: vazaoDia,
                backgroundColor: '#53b7ffff'
            },
            {
                label: 'Vazão Média 7 dias (m³/s)',
                data: vazao7d,
                backgroundColor: '#0a74c0ff'
            },
            {
                label: 'Vazão mês <?= $data_base->format('Y/m') ?> (m³/s)',
                data: vazaoMes,
                backgroundColor: '#026332ff'
            },
            {
                label: 'Vazão do Último dia do mês <?= $ano_ref?> (m³/s)',
                data: vazaoMesRef,
                backgroundColor: '#d3b50cff'
            },
            {
                label: 'Média climatológica <?= $data_base->format('Y/m') ?>(m³/s)',
                data: vazaoClima,
                backgroundColor: '#f8a60dff'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            },
            title: {
                display: true,
             
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Sistemas'
                },
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Vazão (m³/s)'
                }
            }
        }
    }
});
</script>

<!-- <script>
const dados = <?= $json_dados ?>;
const sistemas = dados.map(d=>d.Sistema);
const vazaoDia = dados.map(d=>d.Vazao_dia);
const excedente7 = dados.map(d=>d.Excedente_7d);
const vazaoMes = dados.map(d=>d.Vazao_mes);
const vazaoMesRef = dados.map(d=>d.Vazao_mes_ref);
const excedenteClima = dados.map(d=>d.Excedente_clima);

const ctx = document.getElementById('graficoVazao').getContext('2d');
new Chart(ctx, {
    type:'bar',
    data:{
        labels:sistemas,
        datasets:[
            {label:'Vazão do dia (m³/s)', data:vazaoDia, backgroundColor:'#1f77b4', stack:'semana'},
            {label:'Excedente últimos 7 dias (m³/s)', data:excedente7, backgroundColor:'#7fb3d5', stack:'semana'},
            {label:'Vazão no mês <?= $data_base->format('Y') ?> (m³/s)', data:vazaoMes, backgroundColor:'#2ca02c'},
            {label:'Vazão no mês <?= $ano_ref ?> (m³/s)', data:vazaoMesRef, backgroundColor:'#ff7f0e', stack:'clima'},
            {label:'Excedente climatológica (m³/s)', data:excedenteClima, backgroundColor:'#ffbb78', stack:'clima'}
        ]
    },
    options:{
        responsive:true,
        plugins:{legend:{position:'top'}},
        scales:{
            x:{stacked:false,ticks:{maxRotation:45,minRotation:45}},
            y:{beginAtZero:true,title:{display:true,text:'Vazão (m³/s)'}}
        }
    }
});
</script> -->

</body>
</html>
