<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'sssp';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Erro na conexão: ' . $conn->connect_error);
}
// definir charset
$conn->set_charset('utf8mb4');
?>