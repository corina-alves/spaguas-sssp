
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
<div class="container">
<div class="col-lg-12">
<div class="row ">

   <div class="container p-5">
    <h2 style="text-align:center; margin-top:30px;"> <strong>Projeções </strong>do Sistema Metropolitano Integrado</h2>
        <h4 style="text-align:center; margin-bottom:30px;"><i><?= $data_base->format('d/m/Y') ?> | Ano de referência: <?= $ano_ref ?></i></h4>
        <!-- <h4 class="text-center">Projeções de Volume do SIM</h4>
       <div class="atualizacao">Atualizado em: <?= date("d/m/Y") ?></div>  -->
          <canvas id="grafico_linha"></canvas>

</div>

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
