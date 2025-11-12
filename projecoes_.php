
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gráfico de Reservatórios</title>

</head>
<body>
<div class="container">
<div class="col-lg-12">
<div class="row ">

   <div class="container p-5">
    <div class="atualizacao">Atualizado em: <!--?= date("d/m/Y") ?-->22/10/2025</div> 

        <h4 class="text-center">Projeções de Volume do SIM</h4>
       
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
