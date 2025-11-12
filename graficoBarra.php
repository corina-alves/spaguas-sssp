<?php
$apiKey = "6D3BCD8A-8B65-4D1C-B2C7-F5E41912D7E7"; // coloque sua chave aqui

// Lista dos sistemas que a API retorna
$sistemas = ["cantareira","alto-tiete","guarapiranga","cotia","rio-grande","rio-claro","sao-lourenco","sim"];

$dados = [];

// Data de hoje
$inicio = date("Y-m-d");
$fim = date("Y-m-d");

// Para cada sistema, buscar os dados
foreach ($sistemas as $sistemaNome) {
    $url = "https://ssdapi.sabesp.com.br/api/ssd/sistemas/$sistemaNome/dados/$inicio/$fim";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "x-api-key: $apiKey"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response !== false) {
        $json = json_decode($response, true);
        if (isset($json['data'][0])) {
            $registro = $json['data'][0];
            $dados[] = [
                "sistema" => ucfirst($sistemaNome),
                "volumeOperacional_porcentagem" => $registro["volumeOperacional_porcentagem"] ?? 0
            ];
        }
    }
}

// Converte para JSON para usar no gráfico
$json = json_encode($dados);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Gráfico Reservatórios</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Chart.js -->
   
    <!-- Plugin de rótulos -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <!-- Plugin de anotações -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.4.0"></script>
</head>
<body>
  <!-- <h2>Volumes Úteis dos Sistemas (API Sabesp)</h2> -->
   <div class="container">
   <div class="row">
    <div class="col-lg-8">
  <canvas id="grafico" ></canvas>
</div>
</div>
</div>
  <script>
    // Passa os dados do PHP para JS
    const dados = <?php echo $json; ?>;
    console.log("Dados da API:", dados);

    const labels = dados.map(item => item.sistema);
    const valores = dados.map(item => item.volumeOperacional_porcentagem);

    const ctx = document.getElementById("grafico").getContext("2d");

    new Chart(ctx, {
      type: "bar",  // pode trocar para 'pie' se quiser pizza
      data: {
        labels: labels,
        datasets: [{
          label: "Volume Útil (%)",
          data: valores,
          backgroundColor: [
            "rgba(54, 162, 235, 0.6)"
            // "rgba(255, 99, 132, 0.6)",
            // "rgba(255, 206, 86, 0.6)",
            // "rgba(75, 192, 192, 0.6)",
            // "rgba(153, 102, 255, 0.6)",
            // "rgba(255, 159, 64, 0.6)",
            // "rgba(199, 199, 199, 0.6)"
          ]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: "top" },
          title: {
            display: true,
            text: "Volumes Úteis - Sabesp API"
          }
        }
      }
    });
  </script>
</body>
</html>
