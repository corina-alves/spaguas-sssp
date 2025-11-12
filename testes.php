<?php
$apiKey = "6D3BCD8A-8B65-4D1C-B2C7-F5E41912D7E7";

$urlSistemas = "https://ssdapi.sabesp.com.br/api/ssd/sistemas/$idCantareira/dados/$inicio/$fim";

$ch = curl_init($urlSistemas);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "x-api-key: $apiKey"
]);
$response = curl_exec($ch);

if ($response === false) {
    echo "Erro cURL: " . curl_error($ch);
} else {
    // Mostra o JSON bruto
    header('Content-Type: application/json');
    echo $response;
}

curl_close($ch);
?>
