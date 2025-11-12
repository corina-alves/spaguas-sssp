<!--?php
$apiKey = "6D3BCD8A-8B65-4D1C-B2C7-F5E41912D7E7";
$sistemaNome = "cantareira";

$inicio = date("Y-m-d");
$fim = date("Y-m-d");

$url = "https://ssdapi.sabesp.com.br/api/ssd/sistemas/";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "x-api-key: $apiKey"
]);
$response = curl_exec($ch);

if ($response === false) {
    echo "Erro cURL: " . curl_error($ch);
} else {
    header('Content-Type: application/json');
    echo $response;
}

curl_close($ch);
?-->
<?php
$apiKey = "6D3BCD8A-8B65-4D1C-B2C7-F5E41912D7E7";
$sistemas = ["cantareira","alto-tiete","guarapiranga","cotia","rio-grande","rio-claro","sao-lourenco","sim"];

$inicio = date("Y-m-d"); // últimos 7 dias
$fim = date("Y-m-d");

$resultados = [];

foreach ($sistemas as $sistemaNome) {
    $url = "https://ssdapi.sabesp.com.br/api/ssd/sistemas/$sistemaNome/dados/$inicio/$fim";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "x-api-key: $apiKey"
    ]);
    $response = curl_exec($ch);

    if ($response !== false) {
        $dados = json_decode($response, true);
        $resultados[$sistemaNome] = $dados;
    } else {
        $resultados[$sistemaNome] = ["erro" => curl_error($ch)];
    }

    curl_close($ch);
}

header('Content-Type: application/json');
echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>