<?php
require_once 'config.php';
if (!isset($_SESSION['logado'])) {
    header('Location: login.php');
    exit;
}
?>