<?php
$urls = [
  "https://www.google.com",
  "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/2021-10-13"
];

foreach ($urls as $url) {
  echo "Testando $url ...\n";
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false, // só diagnóstico
  ]);
  $res = curl_exec($ch);
  $err = curl_error($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  echo "HTTP code: {$info['http_code']}\n";
  if ($err) echo "Erro: $err\n";
  echo "\n";
}
