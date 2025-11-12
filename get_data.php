<?php
header('Content-Type: application/json; charset=utf-8');

$arquivo = "dados_reservatorios.csv";
$dataSelecionada = $_GET["data"] ?? date("Y-m-d");

$dados = [];
if (($handle = fopen($arquivo, "r")) !== FALSE) {
    $cabecalho = fgetcsv($handle, 1000, ";");
    while (($linha = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $registro = array_combine($cabecalho, $linha);

        // compara a data da linha com a escolhida
        if ($registro["data"] == $dataSelecionada) {
            $dados[] = $registro;
        }
    }
    fclose($handle);
}

echo json_encode($dados, JSON_UNESCAPED_UNICODE);
