<?php
session_start();

$host = '186.209.113.107';
$user = 'dema5738_menuzap';
$pass = 'Dema@1973';
$dbname = 'dema5738_menuzap';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

$site_url = 'http://' . $_SERVER['HTTP_HOST'] . '/menuzap';

// Função auxiliar para redirecionar e sair
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Função para checar acesso admin
function checkAdmin() {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
        redirect($GLOBALS['site_url'] . '/admin/login.php');
    }
}

// Função para checar acesso garçom ou admin
function checkGarcom() {
    if (!isset($_SESSION['usuario_id'])) {
        redirect($GLOBALS['site_url'] . '/admin/login.php');
    }
}

// Pegar opções globais do restaurante para usar no frontend
function getRestauranteInfo($pdo) {
    $stmt = $pdo->query("SELECT * FROM restaurante LIMIT 1");
    $info = $stmt->fetch();
    return $info ?: [
        'nome' => 'MenuZap',
        'ativo' => 1,
        'cor_primaria' => '#ef4444',
        'cor_secundaria' => '#dc2626',
        'mensagem_boasvindas' => '',
        'horario_funcionamento' => '',
        'logo' => null
    ];
}
?>
