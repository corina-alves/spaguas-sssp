<?php
// Dados dos reservatórios
$reservatorios = [
 ["nome" => "Cantareira", "atual" => 28.6, "mesano2021" => 30.7, "chuva" => 0.0, "acumulado_mes" => 51.4, "media_historica" => 79.5], 
    ["nome" => "Alto Tietê", "atual" => 25.2, "mesano2021" => 40.5, "chuva" =>0.0, "acumulado_mes" =>46.6, "media_historica" => 57.6],
    ["nome" => "Guarapiranga", "atual" => 48.1, "mesano2021" => 45.5, "chuva" => 0.0, "acumulado_mes" => 45.2, "media_historica" => 76.1],
    ["nome" => "Cotia", "atual" => 50.6, "mesano2021" => 50.3, "chuva" => 0.0, "acumulado_mes" => 35.6, "media_historica" => 75.0],
    ["nome" => "Rio Grande", "atual" => 53.6, "mesano2021" => 75.4, "chuva" => 0.0, "acumulado_mes" => 28.8, "media_historica" => 91.7],
    ["nome" => "Rio Claro", "atual" => 19.0, "mesano2021" => 37.3, "chuva" => 0.0, "acumulado_mes" => 91.8, "media_historica" => 136.7 ],
    ["nome" => "São Lourenço", "atual" => 46.5, "mesano2021" => 51.1, "chuva" => 0.0, "acumulado_mes" => 51.6, "media_historica" => 110.5 ],
    ["nome" => "SIM", "atual" => 31.7, "mesano2021" =>38.5, "chuva" => '-', "acumulado_mes" => '-', "media_historica" => '-'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gráfico de Reservatórios</title>
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
<div class="atualizacao">Atualizado em: <!--?= date("d/m/Y") ?-->17/09/2025</div> 
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
                position: 'bottom',
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
</script>

 <script>
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


<script>// as projeções
async function projecoes(url) {
    const resposta = await fetch(url);
    const texto = await resposta.text();
    const linhas = texto.split("\n").map(l => l.trim()).filter(l => l.length);

    const cabecalho = linhas[0].split(";");
    const dados = linhas.slice(1).map(l => l.split(";"));

    return { cabecalho, dados };
}

async function criarGraficoLinha() {
    const { cabecalho, dados } = await projecoes("serie_diaria.csv");
    const labels = dados.map(l => l[0]);
    const cores = {
        "QN 100 MLT":  "#021d58",
        "QN 70 MLT":   "#cc0505",
        "QN (20-25)":  "#046e1f",
        "QN (2021)":   "#0cb130",
        "QN (2014)":   "#df5c11ff",
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
