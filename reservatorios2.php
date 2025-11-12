<?php
// Dados dos reservatórios (simulação)

$reservatorios = [
  ["nome" => "Cantareira", "atual" => 32.0, "anterior" => 55.3, "chuva" => 0.0, "acumulado_mes" => 0.0, "media_historica" => 79.5], 
    ["nome" => "Alto Tietê", "atual" => 27.4, "anterior" => 50.9, "chuva" => 0.0, "acumulado_mes" =>1.6, "media_historica" => 57.6],
    ["nome" => "Guarapiranga", "atual" => 50.2, "anterior" => 40.7, "chuva" => 0.0, "acumulado_mes" => 1.6, "media_historica" => 76.1],
    ["nome" => "Cotia", "atual" => 54.9, "anterior" => 44.3, "chuva" => 0.0, "acumulado_mes" => 0.6, "media_historica" => 75.0],
    ["nome" => "Rio Grande", "atual" => 55.3, "anterior" => 67.6, "chuva" => 0.0, "acumulado_mes" => 3.8, "media_historica" => 91.7],
    ["nome" => "Rio Claro", "atual" => 21.8, "anterior" => 27.0, "chuva" => 0.2, "acumulado_mes" => 10.8, "media_historica" => 136.7 ],
    ["nome" => "São Lourenço", "atual" => 49.9, "anterior" => 54.0, "chuva" => 0.0, "acumulado_mes" => 1.6, "media_historica" => 110.5 ],
    ["nome" => "SIM", "atual" => 34.6, "anterior" =>53.1, "chuva" => '-', "acumulado_mes" => '-', "media_historica" => '-'],

];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservatórios RMSP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <link href="assets/css/style.css" rel="stylesheet">
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

    </style>
</head>
<body>
<?php include "navbar.php";?>

<div class="container">
<h4 class="text-center">Sistema Produtores da RMSP</h4>
<div class="atualizacao">Atualizado em: <!--?= date("d/m/Y") ?-->10/09/2025</div> 
<div class="row">
<div class="col-lg-12">
<div class="row ">
    <?php foreach ($reservatorios as $r): ?>
    <div class="col">
        <div class="card box">
        <div class="card-body">
            <h6 class="text-center"><?= $r["nome"] ?></h6>
            <h6 class="text-center"> <?= $r["atual"] ?>%</h6>
        </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<canvas id="grafico_barra"></canvas>

</div>
<div class="col-lg-12">
    <table class="table table-hover">
        <tr class="table-light">
            <th>Sistema</th>
            <th>Volume Atual (%)</th>
            <th>Volume Ano Anterior (%)</th>
            <th>Diferença (%)</th>
            <th>Chuva(mm)</th>
            <th>Acumulado no Mês (mm)</th>
            <th>Média Histórica (mm)</th>
        </tr>
        <?php foreach ($reservatorios as $r): ?>
        <tr>
            <td><?= $r["nome"] ?></td>
            <td><?= $r["atual"] ?></td>
            <td><?= $r["anterior"] ?></td>
            <td><?= round($r["atual"] - $r["anterior"], 2) ?></td>
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

<script>// script de reservatórios
const ctx = document.getElementById('grafico_barra');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($reservatorios, 'nome')) ?>,
        datasets: [
            {
                label: 'Volume Atual (%)',
                data: <?= json_encode(array_column($reservatorios, 'atual')) ?>,
                backgroundColor: 'rgba(32, 149, 228, 0.7)',
               
            },
         
            {
                label: 'Volume Ano Anterior (%)',
                data: <?= json_encode(array_column($reservatorios, 'anterior')) ?>,
                backgroundColor: 'rgba(4, 18, 206, 0.7)',
                
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
                padding: 20, // afasta o texto da caixinha da legenda
                boxWidth: 20,
                
            }
        },
        datalabels: {
            anchor: 'end',
            align: 'end',
            color: '#000',
            font: {
                weight: 'bold',
                margin: 80
            },
            formatter: function(value) {
                return value + '%';
            }
        }
     
    },
    layout: {
        padding: {
            top: 60,// <<< aumenta este valor para criar mais espaço entre legenda e gráfico
         
        }
    },
    scales: {
        y: { beginAtZero: true }
    }

    },
    plugins: [ChartDataLabels] // ativa o plugin
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
