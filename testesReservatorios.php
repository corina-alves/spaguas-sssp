<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Reservatórios SABESP</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <h2>Resumo dos Reservatórios - 08/09/2025</h2>

  <table border="1" id="tabela"></table>

  <canvas id="grafico" width="600" height="300"></canvas>

  <script>
    async function carregarDados() {
      const url = "https://cors-anywhere.herokuapp.com/https://mananciais.sabesp.com.br/api/Mananciais/ResumoSistemas/2025-09-08";
      const resposta = await fetch(url);
      const dados = await resposta.json();

      console.log(dados); // 👈 inspecionar estrutura no console

      // Aqui assumo que os dados vêm em dados.Sistemas
      const sistemas = dados.Sistemas || dados; // fallback caso seja array direto

      // Montar tabela
      const tabela = document.getElementById("tabela");
      tabela.innerHTML = "<tr><th>Sistema</th><th>Volume (%)</th></tr>";

      const labels = [];
      const volumes = [];

      sistemas.forEach(item => {
        tabela.innerHTML += `
          <tr>
            <td>${item.Nome}</td>
            <td>${item.VolumePorcentagem.toFixed(1)}</td>
      
          </tr>
        `;

        labels.push(item.Nome);
        volumes.push(item.VolumePorcentagem);
      });

      // Criar gráfico de barras
      new Chart(document.getElementById("grafico"), {
        type: "bar",
        data: {
          labels: labels,
          datasets: [{
            label: "Volume (%)",
            data: volumes,
            backgroundColor: "rgba(54, 162, 235, 0.6)"
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: true },
            tooltip: { enabled: true }
          }
        }
      });
    }

    carregarDados();
  </script>
</body>
</html>
