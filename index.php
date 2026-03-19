<?php
$mesa = isset($_GET['mesa']) ? $_GET['mesa'] : '';
$url = 'cardapio.php';
if ($mesa) {
    $url .= '?mesa=' . urlencode($mesa);
}
header("Location: $url");
exit;
