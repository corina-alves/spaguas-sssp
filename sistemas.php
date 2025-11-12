<?php
// Dados dos reservatórios
$reservatorios = [
  ["nome" => "Cantareira", "atual" => 27.7, "mesano2021" => 29.7, "chuva" => 0.0, "acumulado_mes" => 0.0, "media_historica" => 130.5], 
    ["nome" => "Alto Tietê", "atual" => 24.5, "mesano2021" => 39.8, "chuva" =>0.0, "acumulado_mes" =>0.0, "media_historica" => 107.6],
    ["nome" => "Guarapiranga", "atual" => 46.9, "mesano2021" => 44.7, "chuva" => 0.0, "acumulado_mes" => 0.0, "media_historica" =>114.3],
    ["nome" => "Cotia", "atual" => 49.1, "mesano2021" => 48.7, "chuva" => 0.0, "acumulado_mes" => 0.0, "media_historica" => 120.1],
    ["nome" => "Rio Grande", "atual" => 52.5, "mesano2021" => 75.4, "chuva" => 0.0, "acumulado_mes" => 0.0, "media_historica" => 131.9],
    ["nome" => "Rio Claro", "atual" => 16.3, "mesano2021" => 35.8, "chuva" => 0.2, "acumulado_mes" => 0.4, "media_historica" => 173.3 ],
    ["nome" => "São Lourenço", "atual" => 44.9, "mesano2021" => 49.6, "chuva" => 0.0, "acumulado_mes" => 0.0, "media_historica" => 143.7 ],
    ["nome" => "SIM", "atual" => 30.8, "mesano2021" =>37.7, "chuva" => '-', "acumulado_mes" => '-', "media_historica" => '-'],
];

$sistema = [
    ["nomeSistema" => "Cantareira", "volAnoAtual" => 20, "chuvaAtualAcum"=> 29.0, "volMesAno2021" =>20, "chuvaAcumMesAno2021"=> 166.0],
    ["nomeSistema" => "Alto Tietê", "volAnoAtual" => 28.8, "chuvaAtualAcum"=> 28.5, "volMesAno2021" =>39.8, "chuvaAcumMesAno2021"=> 101.1],
    ["nomeSistema" => "Guarapiranga", "volAnoAtual" => 8.8, "chuvaAtualAcum"=> 38.0, "volMesAno2021" =>44.7, "chuvaAcumMesAno2021"=> 98.2],
    ["nomeSistema" => "Cotia", "volAnoAtual" => 0.8, "chuvaAtualAcum"=> 35.4, "volMesAno2021" =>48.7, "chuvaAcumMesAno2021"=> 87.0],
    ["nomeSistema" => "Rio Grande", "volAnoAtual" => 5.8, "chuvaAtualAcum"=> 31.6, "volMesAno2021" =>75.4, "chuvaAcumMesAno2021"=> 107.6],
    ["nomeSistema" => "Rio Claro", "volAnoAtual" => 0.7, "chuvaAtualAcum"=> 61.8, "volMesAno2021" =>35.8, "chuvaAcumMesAno2021"=> 120.6 ],
    ["nomeSistema" => "São Lourenço", "volAnoAtual" => 4.6, "chuvaAtualAcum"=> 56.4, "volMesAno2021" =>49.6, "chuvaAcumMesAno2021"=> 176.0 ],
];


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gráfico de Reservatórios</title>
    <link href="assets/img/logo/logo.png" rel="icon">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Plugin de rótulos -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <!-- Plugin de anotações -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.4.0"></script>
    <style>
   body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #e9e9e9ff; padding: 8px; text-align: center; }
        th { background: #f0f0f0; }
        .box{background:#f0f0f0;}
        h6{
         font-weight: bold;
        }
        h4{
            font-weight:bold;
        }
   .legenda-protocolo ul {
    list-style: none;
    padding: 0;
    margin: 10px auto;
    display: flex;
    flex-direction: row; /* horizontal */
    justify-content: center; /* centraliza */
    gap: 20px; /* espaço entre os itens */
}

.legenda-protocolo li {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.legenda-protocolo .cor {
    display: inline-block;
    width: 30px;
    height: 15px;
}

.legenda-protocolo .e1 { background: yellow; }
.legenda-protocolo .e2 { background: orange; }
.legenda-protocolo .e3 { background: red; }
.legenda-protocolo .e4 { background: purple;}
    </style>
</head>
<body>
<?php include "navbar.php";?>
<div class="container">
<h4 class="text-center">Sistema Produtores da RMSP</h4>
<div class="atualizacao">Atualizado em: <!--?= date("d/m/Y") ?-->03/10/2025</div> 
<div class="col-lg-12">
<div class="row ">

    <?php foreach ($reservatorios as $r): ?>
        <?php 
          $diff = $r["atual"] - $r["mesano2021"];
            if ($diff > 0) {
                $seta = '<span style="color:green;">&#9650;</span>'; // seta para cima
            } elseif ($diff < 0) {
                $seta = '<span style="color:red;">&#9660;</span>'; // seta para baixo
            } else {
                $seta = '<span style="color:black;">&#8212;</span>'; // traço quando igual
            }
        ?>
        <div class="col">
            <div class="card box">
                <div class="card-body text-center">
                    <h6><?= $r["nome"] ?></h6>
                    <h6><?= $r["atual"] ?>% </h6>
                    <p>
                    <strong>(<?= $seta ?> <?php  $diff = $r["atual"] - $r["mesano2021"];?><?= $diff ?>% )</strong>
                    </p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="row">
    <div class="col-lg-10">
    <canvas id="grafico_barra"></canvas>
    <!--legendas dos estagios de protocolo de escassez-->
    <div class="legenda-protocolo mt-3">
        <ul>
            <li><span class="cor e1"></span> E1 - Atenção</li>
            <li><span class="cor e2"></span> E2 - Alerta</li>
            <li><span class="cor e3"></span> E3 - Crítico</li>
            <li><span class="cor e4"></span> E4 - Emergência</li>
        </ul>
    </div>
</div>
<div class="col-lg-2">

 <div class="pie" style="margin-top:190px;">
    <h5 class="text-center">Volume total</h5>
<canvas id="graficoPizza"></canvas>
</div>
</div>

</div>
    <!--tabela com volumes dos reservatorios-->
<div class="col-lg-12">
    <table class="table table-hover">
        <tr class="table-light">
            <th>Sistema</th>
            <th>Volume Atual (%)</th>
            <th>Volume Ano 2021 (%)</th>
            <th>Diferença (%)</th>
            <th>Chuva(mm)</th>
            <th>Acumulado no Mês (mm)</th>
            <th>Média Histórica (mm)</th>
        </tr>
        <?php foreach ($reservatorios as $r): ?>
        <tr>
            <td><?= $r["nome"] ?></td>
            <td><?= $r["atual"] ?></td>
            <td><?= $r["mesano2021"] ?></td>
            <td><?= round($r["atual"] - $r["mesano2021"], 2) ?></td>
            <td><?= $r["chuva"]?></td>
            <td><?= $r["acumulado_mes"]?></td>
            <td><?=$r["media_historica"]?></td>
        </tr>
        <?php endforeach; ?>
    </table>
   </div>

   <div class="container p-5">
        <h4 class="text-center">Projeções de Volume do SIM</h4>
       
          <canvas id="grafico_linha"></canvas>


</div>
   <div class="container p-5">
        <h4 class="text-center">Grafico de chuvas</h4>
       
          <canvas id="Grafico_chuva"></canvas>

</div>
<script>
// script de reservatórios
const ctx = document.getElementById('grafico_barra');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($reservatorios, 'nome')) ?>,
        datasets: [
       
            {
                label: 'Volume Ano 2021 (%)',
                data: <?= json_encode(array_column($reservatorios, 'mesano2021')) ?>,
                backgroundColor: 'rgba(4, 18, 206, 0.7)',
            },
                 {
                label: 'Volume Atual (%)',
                data: <?= json_encode(array_column($reservatorios, 'atual')) ?>,
                backgroundColor: 'rgba(32, 149, 228, 0.7)',
            },
  
        ]
    },
    
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                align: 'left',
                labels: {
                    padding: 20,
                    boxWidth: 20,
                }
            },
            datalabels: {
                anchor: 'end',
                align: 'end',
                color: '#000',
                font: { weight: 'bold' },
                formatter: function(value) {
                    return value + '%';
                }
            },
            annotation: {
                annotations: {
                    atencao60: {
                        type: 'line',
                        yMin: 60,
                        yMax: 60,
                        borderColor: 'yellow',
                        borderWidth: 2,
                        drawTime: 'beforeDatasetsDraw',
                        label: {
                            // content: 'Atenção (40%)',
                            // enabled: true,
                            // position: 'end',
                            // backgroundColor: 'orange',
                            // color: 'black'
                        }
                    },
                    critico30: {
                        type: 'line',
                        yMin: 40,
                        yMax: 40,
                        borderColor: 'orange',
                        borderWidth: 2,
                        drawTime: 'beforeDatasetsDraw',
         
                    },
                    critico20: {
                        type: 'line',
                        yMin: 30,
                        yMax: 30,
                        borderColor: 'red',
                        borderWidth: 2,
                        drawTime: 'beforeDatasetsDraw',

                    },
                    emergencia: {
                        type: 'line',
                        yMin: 20,
                        yMax: 20,
                        borderColor: 'purple',
                        borderWidth: 2,
                        drawTime: 'beforeDatasetsDraw',

                    }
                }
            }
        },
        layout: {
            padding: { top: 60 }
        },
        scales: {
            y: { beginAtZero: true }
        }
    },
    plugins: [ChartDataLabels]
});
// script do grafico pizza
    const Cantareira = 50.50;
    const AltoTiete = 28.80;
    const Guarapiranga = 8.80;
    const RioGrande = 5.80;
    const SaoLourenco = 4.60;
    const RioClaro = 0.70;
    const Cotia = 0.80;

    const ctx2 = document.getElementById('graficoPizza').getContext('2d');
    const graficoPizza = new Chart(ctx2, {
    //   type: 'pie',
    type: 'doughnut',
      data: {
        labels: [
          'Cantareira',
          'Alto Tietê',
          'Guarapiranga',
          'Rio Grande',
          'São Lourenço',
          'Rio Claro',
          'Cotia'
        ],
        datasets: [{
          data: [Cantareira, AltoTiete, Guarapiranga, RioGrande, SaoLourenco, RioClaro, Cotia],
          backgroundColor: [
            '#0077cc', '#e0e0e0', '#084391ff', '#0ca73aff', '#726f6fff', '#e00e0eff', '#5e4040ff'
          ],
          borderColor: '#ffffff',
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
             // display: false 🔹 Desativa a legenda
             position: 'right',
            labels: {
                boxWidth: 12,   // 🔹 largura da barrinha (padrão ~40)
                boxHeight: 12,  // 🔹 altura da barrinha
                padding: 5,     // espaço entre texto e barrinha
                font: {
                size: 12     // tamanho da fonte da legenda
                    }
      }

          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.label || '';
                let valor = context.parsed || 0;
                return label + ': ' + valor.toFixed(2) + '%';
              }
            }
          }
        }
      }
    });
</script>
<!--Grafico de chuva-->

<script>
const ctx_chu = document.getElementById('Grafico_chuva');
new Chart(ctx_chu, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($sistema, 'nomeSistema')) ?>,
        datasets: [
       
            {
                label: 'Chuva Acum. 2021 (mm)',
                data: <?= json_encode(array_column($sistema, 'chuvaAtualAcum')) ?>,
                backgroundColor: 'rgba(24, 93, 241, 0.72)',
            },
                 {
                label: 'Chuva Ano Atual (mm)',
                data: <?= json_encode(array_column($sistema, 'chuvaAcumMesAno2021')) ?>,
                backgroundColor: 'rgba(28, 183, 245, 0.68)',
            },
  
        ]
    },
    
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                align: 'left',
                labels: {
                    padding: 20,
                    boxWidth: 20,
                }
            },
            datalabels: {
                anchor: 'end',
                align: 'end',
                color: '#000',
                font: { weight: 'bold' },
                formatter: function(value) {
                    return value + 'mm';
                }
            },
         
        },
        layout: {
            padding: { top: 60 }
        },
        scales: {
            y: { beginAtZero: true }
        }
    },
    plugins: [ChartDataLabels]
});
</script>










<script>// as projeções
async function projecoes(url) {
    const resposta = await fetch(url);
    const texto = await resposta.text();
    const linhas = texto.split("\n").map(l => l.trim()).filter(l => l.length);

    const cabecalho = linhas[0].split(",");
    const dados = linhas.slice(1).map(l => l.split(","));

    return { cabecalho, dados };
}

async function criarGraficoLinha() {
    const { cabecalho, dados } = await projecoes("serie_diaria.csv");
    const labels = dados.map(l => l[0]);
    const cores = {
        "QN 100 MLT":  "#021d58",
        "QN 70 MLT":   "#cc0505",
        "QN (20/25)":  "#046e1f",
        "QN (2021)":   "#0cb130",
        "QN (2014)":   "#df5c11ff",
        "QN (2020)":   "#df11b2ff",
        "Real": "#000000" // preto forte
    
    
    };

    const datasets = cabecalho.slice(1).map((nome, idx) => {
        const isObservado = nome === "Real"; // checar se é a série a destacar

           return {
            label: nome === "Real" ? "Real" : nome,
            data: dados.map(l => parseFloat(l[idx+1]) || null),
            borderColor: cores[nome] || `hsl(${Math.random()*360}, 50%, 30%)`,
            borderWidth: isObservado ? 4 : 2,   // linha mais grossa
            pointBackgroundColor: isObservado ? "#000000" : cores[nome],
            pointRadius: isObservado ? 4 : 1,  // pontos maiores (o observado)
    
            fill: false
        }
    });

    new Chart(document.getElementById("grafico_linha"), {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                title: { display: true, text: 'Séries Diárias' },
                legend: { position: 'top' },
                
            },
            // scales: {
            //     x: { title: { display: true, text: 'Data' } },
            //     y: { title: { display: true, text: 'Valor' } }
            // }
            scales: {
    x: { title: { display: true, text: 'Data' } },
    y: { 
        title: { display: true, text: 'Volume' },
        min: 25,
        max: 45,
        ticks: {
            stepSize: 5
        }
    }
}
        }
    });
}

criarGraficoLinha();
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>

</body>
</html>
