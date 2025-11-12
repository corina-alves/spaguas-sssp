<?php
// URL da API da Sabesp
// $url = "https://mananciais.sabesp.com.br/api/Mananciais/Boletins/Mananciais/2025-09-28";
$dataHoje = date("Y-m-d");

// Define a URL da API com a data do dia
$url = "https://mananciais.sabesp.com.br/api/Mananciais/Boletins/Mananciais/$dataHoje";

// Puxa os dados da API
$json = file_get_contents($url);
$data = json_decode($json, true);

// Cria array de ETAs
$dados_sistema = [
    0 => "Cantareira",
    1 => "Alto Tietê",
    2 => "Guarapiranga",
    3 => "Cotia",
    4 => "Rio Grande",
    5 => "Rio Claro",
    17 => "São Lourenço"
];

if (isset($data["ReturnObj"]["dadosSistemas"])) {
    foreach ($data["ReturnObj"]["dadosSistemas"] as $item) {
        $dados_sistema[] = [
            "nome"      => $item["Nome"],                // Nome da ETA
            "retirada"  => $item["VazaoRetirada"],       // Vazão retirada
            "produzida" => $item["VazaoProduzida"],      // Vazão produzida
            "data"      => $item["Data"]                 // Data
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>ETAs - Vazão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .card {
            border-radius: 12px;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.15);
            margin-bottom: 15px;
        }
        .seta-verde {
            color: green;
            font-size: 18px;
            margin-left: 5px;
        }
        .seta-vermelha {
            color: red;
            font-size: 18px;
            margin-left: 5px;
        }
        .seta-preta {
            color: black;
            font-size: 18px;
            margin-left: 5px;
        }
    </style>
</head>
<body class="container py-4">

    <h3 class="mb-4">Dados de Vazão das ETAs</h3>
    <div class="row">
        <?php foreach ($dados_sistema as $e): ?>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6><?= $e["nome"] ?></h6>
                    <p>Retirada: <?= number_format($e["retirada"], 2, ',', '.') ?> m³/s</p>
                    <p>
                        Produzida: <?= number_format($e["produzida"], 2, ',', '.') ?> m³/s
                        <?php
                        if ($e["produzida"] > $e["retirada"]) {
                            echo '<i class="fa-solid fa-arrow-up seta-verde"></i>';
                        } elseif ($e["produzida"] < $e["retirada"]) {
                            echo '<i class="fa-solid fa-arrow-down seta-vermelha"></i>';
                        } else {
                            echo '<i class="fa-solid fa-minus seta-preta"></i>';
                        }
                        ?>
                    </p>
                    <small class="text-muted"><?= date("d/m/Y", strtotime($e["data"])) ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
